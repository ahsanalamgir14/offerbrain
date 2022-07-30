<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Session;
use Carbon\Carbon;
use App\Models\Campaign;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CampaignBuilderController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();

            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if ($start_date != null && $end_date != null) {
            $start_date = Carbon::parse($start_date)->startOfDay();
            $end_date = Carbon::parse($end_date)->endOfDay();
        }

        DB::enableQueryLog();
        $data = DB::table('campaigns')->where(['campaigns.user_id' => 2])->whereNull('campaigns.is_active')
        // $data = DB::table('campaigns')->where(['campaigns.user_id' => Auth::id()])->whereNull('campaigns.is_active')
            ->leftJoin('orders', 'orders.user_id', 'campaigns.user_id')
            ->where('orders.time_stamp', '>=', $start_date)
            ->where('orders.time_stamp', '<=', $end_date)
            ->where('orders.is_test_cc', 0)
            ->whereRaw('FIND_IN_SET(orders.campaign_id, campaigns.tracking_campaign_ids) != 0')
            ->select('orders.user_id', 'orders.main_product_id', 'campaigns.user_id', 'campaigns.id', 'campaigns.campaign_id', 'campaigns.name', 'campaigns.tracking_networks', 'campaigns.tracking_campaigns', 'campaigns.cycle_product_ids', 'campaigns.created_at')
            ->addSelect(DB::raw('Round(SUM(case when FIND_IN_SET(orders.affid, campaigns.tracking_network_ids) != 0 and orders.order_status = 2 then orders.order_total else 0 end) - sum(case when FIND_IN_SET(orders.affid, campaigns.tracking_network_ids) != 0 and orders.order_status = 2 and orders.amount_refunded_to_date > 0 then orders.amount_refunded_to_date else 0 end), 2) as revenue'))
            ->addSelect(DB::raw('Round(SUM(case when FIND_IN_SET(orders.affid, campaigns.tracking_network_ids) != 0 and orders.order_status = 6 then orders.order_total else 0 end) + sum(case when FIND_IN_SET(orders.affid, campaigns.tracking_network_ids) != 0 and orders.order_status = 2 and orders.amount_refunded_to_date > 0 then orders.amount_refunded_to_date else 0 end), 2) as refund'))
            ->addSelect(DB::raw("count(case when FIND_IN_SET(orders.affid, campaigns.tracking_network_ids) != 0 and orders.order_status = 2 then 1 end) as initials"))
            ->addSelect(DB::raw("count(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.order_status = 2 and (orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[1]')) or orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[2]')) or orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[3]'))) then 1 end) as rebills"))
            ->addSelect(DB::raw("count(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[1]')) and orders.order_status = 2 then 1 end) as c1"))
            ->addSelect(DB::raw("SUM(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[1]')) and orders.order_status = 2 then orders.order_total else 0 end) - SUM(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[1]')) and orders.order_status = 2  and orders.amount_refunded_to_date > 0 then orders.amount_refunded_to_date else 0 end) as c1_revenue"))
            ->addSelect(DB::raw("count(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[2]')) and orders.order_status = 2 then 1 end) as c2"))
            ->addSelect(DB::raw("SUM(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[2]')) and orders.order_status = 2 then orders.order_total else 0 end) - SUM(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[2]')) and orders.order_status = 2  and orders.amount_refunded_to_date > 0 then orders.amount_refunded_to_date else 0 end) as c2_revenue"))
            ->addSelect(DB::raw("count(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[3]')) and orders.order_status = 2 then 1 end) as c3"))
            ->addSelect(DB::raw("SUM(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[3]')) and orders.order_status = 2 then orders.order_total else 0 end) - SUM(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[3]')) and orders.order_status = 2  and orders.amount_refunded_to_date > 0 then orders.amount_refunded_to_date else 0 end) as c3_revenue"))
            ->addSelect(DB::raw("count(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[1]')) then 1 end) as total_c1"))
            ->addSelect(DB::raw("count(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[2]')) then 1 end) as total_c2"))
            ->addSelect(DB::raw("count(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[3]')) then 1 end) as total_c3"))
            ->addSelect(DB::raw("count(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[1]')) and orders.order_status = 7 then 1 end) as c1_declines"))
            ->addSelect(DB::raw("count(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[2]')) and orders.order_status = 7 then 1 end) as c2_declines"))
            ->addSelect(DB::raw("count(case when FIND_IN_SET(orders.parent_affid, campaigns.tracking_network_ids) != 0 and orders.main_product_id = (SELECT JSON_EXTRACT(campaigns.cycle_product_ids, '$[3]')) and orders.order_status = 7 then 1 end) as c3_declines"))
            ->addSelect(DB::raw('count(case when FIND_IN_SET(orders.affid, campaigns.tracking_network_ids) != 0 and orders.is_chargeback = 1 then 0 end) as CBs'))
            ->addSelect(DB::raw('SUM(case when FIND_IN_SET(orders.affid, campaigns.tracking_network_ids) != 0 and orders.is_chargeback = 1 then orders.order_total else 0 end) as CB_currency'))
            ->groupBy('campaigns.campaign_id')->get();

        return response()->json(['status' => true, 'data' => $data, 'Query' => DB::getQueryLog()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['campaign_id'] = rand(100000, 999999);
        $data['tracking_campaign_ids'] = [];
        $data['tracking_network_ids'] = [];
        $data['upsell_product_ids'] = [];
        $data['downsell_product_ids'] = [];
        $data['cycle_product_ids'] = [];
        $db_campaign_ids = Campaign::all()->pluck('campaign_id')->toArray();
        $data['user_id'] = 2;
        // $data['user_id'] = $request->user()->id;
        $data['created_at'] = Carbon::now();
        if (in_array($data['campaign_id'], $db_campaign_ids)) {
            return response()->json(['status' => false, 'message' => 'Please click again to save']);
        } else {
            Campaign::create($data);
            return response()->json(['status' => true, 'message' => 'New campaign created']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Campaign::where(['campaign_id' => $id, 'user_id' => 2])->first();
        return response()->json(['status' => true, 'data' => $data]);
        // return Campaign::where(['campaign_id'=>$id, 'user_id'=> Auth::id()])->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $campaign_id)
    {
        $data = $request->all();
        $data['tracking_campaign_ids'] = [];
        $data['tracking_network_ids'] = [];
        $data['upsell_product_ids'] = [];
        $data['downsell_product_ids'] = [];
        $data['cycle_product_ids'] = [];
        $data['user_id'] = 2;
        // $data['user_id'] = $request->user()->id;
        Campaign::where(['user_id' => 2, 'campaign_id' => $campaign_id])->update($data);
        //Campaign::where(['user_id'=>Auth::id(), 'campaign_id'=>$id])->update($data);
        $data['created_at'] = Carbon::now();
        // $campaign = Campaign::where(['user_id'=>Auth::id(), 'campaign_id'=>$id])->first();
        $campaign = Campaign::where(['user_id' => 2, 'campaign_id' => $campaign_id])->first();
        if ($campaign) {
            $campaign->update($data);
        }
        return response()->json(['status' => true, 'message' => 'Campaign Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete_campaign(Request $request)
    {
        $id = $request->id;
        DB::table('campaigns')->where('campaign_id', $id)->delete();
        return response()->json(['status' => true, 'message' => '<b>1</b> Campaign Deleted Successfully']);
    }

    public function campaign_builder_options()
    {
        DB::statement("SET SQL_MODE=''");
        //production
        $data['products'] = DB::table('products')->select('product_id', 'name', 'price', DB::raw("CONCAT('#', product_id,' - ',name,' - $',price ) AS full_name"))->where(['user_id' => Auth::id()])->groupBy('product_id')->get();
        $data['campaigns'] = DB::table('campaigns')->select('id', 'campaign_id', 'gateway_id', 'name')->where(['user_id' => Auth::id()])->whereNotNull('is_active')->groupBy('campaign_id')->get();
        $data['networks'] = DB::table('networks')->select('id', 'network_affiliate_id', 'network_id', 'name')->where(['user_id' => Auth::id()])->groupBy('network_affiliate_id')->get();

        //local
        // $data['products'] = DB::table('products')->select('product_id', 'name', 'price', DB::raw("CONCAT('#', product_id,' - ',name,' - $',price ) AS full_name"))->where(['user_id' => 2])->groupBy('product_id')->get();
        // $data['campaigns'] = DB::table('campaigns')->select('id', 'campaign_id', 'gateway_id', 'name')->where(['user_id' => 2])->whereNotNull('is_active')->groupBy('campaign_id')->get();
        // $data['networks'] = DB::table('networks')->select('id', 'network_affiliate_id', 'network_id', 'name')->where(['user_id' => 2])->groupBy('network_affiliate_id')->get();

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function campaign_view_data(Request $request)
    {
        DB::enableQueryLog();
        // $campaign = Campaign::where(['name' => $request->name, 'user_id' => 2])->first();
        $campaign = Campaign::where(['name' => $request->name, 'user_id' => Auth::id()])->first();
        $tracking_campaign_ids = array_column($campaign->tracking_campaigns, 'campaign_id');
        $tracking_network_ids = array_column($campaign->tracking_networks, 'network_affiliate_id');
        $cycle_product_ids = array_column($campaign->cycle_products, 'product_id');

        // $query = DB::table('orders')->where(['orders.user_id' => 2, 'orders.prepaid_match' => 'No', 'orders.is_test_cc' => 0])
        $query = DB::table('orders')->where(['orders.user_id' => Auth::id(), 'orders.prepaid_match' => 'No', 'orders.is_test_cc' => 0])
            ->whereIn('orders.campaign_id', $tracking_campaign_ids)
            ->whereIn('orders.affiliate', $tracking_network_ids)
            ->select('orders.acquisition_month as month', 'orders.acquisition_year as year', 'order_products.name')
            // ->where('orders.time_stamp', '>=', $start_day)
            // ->where('orders.time_stamp', '<=', $end_day)
            ->leftJoin('order_products', 'orders.order_id', 'order_products.order_id')
            ->addSelect(DB::raw('Round(SUM(case when orders.order_status = 2 then orders.order_total else 0 end), 2) as revenue'))
            ->addSelect(DB::raw('Round(SUM(case when orders.order_status = 6 then orders.order_total else 0 end), 2) as refund'))
            ->addSelect(DB::raw('count(case when order_products.name like "%(c)%" then 1 else 0 end) as initials'))
            ->selectRaw('count(case when orders.is_chargeback = 1 then 0 end) as CBs')
            ->addSelect(DB::raw('SUM(case when orders.is_chargeback = 1 then orders.order_total else 0 end) as CB_currency'));

        foreach ($cycle_product_ids as $i => $cycle_product) {
            if ($i == 0) {
                $query->selectRaw('count(case when order_products.product_id = ' . $cycle_product_ids[$i] . ' then 0 end) as rebills');
            }
            if ($i == 1) {
                $query->selectRaw('count(case when order_products.product_id = ' . $cycle_product_ids[$i] . ' then 0 end) as cycle_2');
            }
            if ($i == 2) {
                $query->selectRaw('count(case when order_products.product_id = ' . $cycle_product_ids[$i] . ' then 0 end) as cycle_3_plus');
            }
        }
        $query->groupBy('orders.acquisition_month');
        $data = $query->get();
        return response()->json(['status' => true, 'data' => $data, 'Query' => DB::getQueryLog()]);
    }
}