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
    public function index(Request $request){
        // dd($request->all());
        // $data = Campaign::find(250);
        // return response()->json(['status' => true, 'data' => $data]);

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if ($start_date != null && $end_date != null) {
            $start_date = Carbon::parse($start_date)->startOfDay();
            $end_date = Carbon::parse($end_date)->endOfDay();
            // var_dump($start_date);
            // var_dump($end_date);
            // return $start_date; 
            // $start_date = '2022-05-31 00:00:00';
            // $end_date = '2022-05-31 23:59:59';
        }
        // return $end_date;

        // return Auth::id();

        DB::enableQueryLog();
        // $data = DB::table('campaigns')->where(['campaigns.user_id' => 2])->whereNull('campaigns.is_active')
        $data = DB::table('campaigns')->where(['campaigns.user_id' => Auth::id()])->whereNull('campaigns.is_active')
            ->leftJoin('orders', 'orders.user_id', 'campaigns.user_id')
            ->where('orders.time_stamp', '>=', $start_date)
            ->where('orders.time_stamp', '<=', $end_date)
            ->where('orders.is_test_cc', 0)
            ->whereRaw('FIND_IN_SET(orders.campaign_id, campaigns.tracking_campaign_ids) != 0')
            ->whereRaw('FIND_IN_SET(orders.affid, campaigns.tracking_network_ids) != 0')
            // ->whereIn('orders.affid', [10,3])
            ->select('orders.user_id', 'orders.main_product_id', 'campaigns.user_id', 'campaigns.id', 'campaigns.campaign_id', 'campaigns.name', 'campaigns.tracking_networks', 'campaigns.tracking_campaigns', 'campaigns.cycle_product_ids', 'campaigns.created_at')
            ->addSelect(DB::raw('Round(SUM(case when orders.order_status = 2 then orders.order_total else 0 end) - sum(case when orders.order_status = 2 and orders.amount_refunded_to_date > 0 then orders.amount_refunded_to_date else 0 end), 2) as revenue'))
            ->addSelect(DB::raw('Round(SUM(case when orders.order_status = 6 then orders.order_total else 0 end) + sum(case when orders.order_status = 2 and orders.amount_refunded_to_date > 0 then orders.amount_refunded_to_date else 0 end), 2) as refund'))
            // ->addSelect(DB::raw("count(case when orders.main_product_id = (SELECT JSON_UNQUOTE(JSON_EXTRACT(campaigns.cycle_product_ids, '$[0]'))) and orders.order_status = 2 then 1 end) as initials"))
            ->addSelect(DB::raw("count(case when orders.order_status = 2 then 1 end) as initials"))
            ->addSelect(DB::raw("count(case when orders.main_product_id in (SELECT JSON_UNQUOTE(JSON_EXTRACT(campaigns.cycle_product_ids, '$[1]', '$[2]', '$[3]'))) then 1 end) as rebills"))
            ->addSelect(DB::raw("count(case when orders.main_product_id = (SELECT JSON_UNQUOTE(JSON_EXTRACT(campaigns.cycle_product_ids, '$[1]'))) and orders.order_status = 2 then 1 end) as c1"))
            ->addSelect(DB::raw("count(case when orders.main_product_id = (SELECT JSON_UNQUOTE(JSON_EXTRACT(campaigns.cycle_product_ids, '$[2]'))) and orders.order_status = 2 then 1 end) as c2"))
            ->addSelect(DB::raw("count(case when orders.main_product_id = (SELECT JSON_UNQUOTE(JSON_EXTRACT(campaigns.cycle_product_ids, '$[3]'))) and orders.order_status = 2 then 1 end) as c3"))
            ->addSelect(DB::raw("count(case when orders.main_product_id = (SELECT JSON_UNQUOTE(JSON_EXTRACT(campaigns.cycle_product_ids, '$[1]'))) and orders.order_status = 7 then 1 end) as c1_declines"))
            ->addSelect(DB::raw("count(case when orders.main_product_id = (SELECT JSON_UNQUOTE(JSON_EXTRACT(campaigns.cycle_product_ids, '$[2]'))) and orders.order_status = 7 then 1 end) as c2_declines"))
            ->addSelect(DB::raw("count(case when orders.main_product_id = (SELECT JSON_UNQUOTE(JSON_EXTRACT(campaigns.cycle_product_ids, '$[3]'))) and orders.order_status = 7 then 1 end) as c3_declines"))
            ->addSelect(DB::raw('count(case when orders.is_chargeback = 1 then 0 end) as CBs'))
            ->addSelect(DB::raw('SUM(case when orders.is_chargeback = 1 then orders.order_total else 0 end) as CB_currency'))
            ->groupBy('campaigns.campaign_id')
            ->get();

        // dd(DB::getQueryLog());
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
        // $data['user_id'] = 2;
        $data['user_id'] = $request->user()->id;
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
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        return $request->id;
        $id = $request->id;
        // $total_records = count($ids);
        DB::table('campaigns')->where('campaign_id', $id)->delete();
        return response()->json(['status' => true, 'message' => '<b>1</b> Customer Deleted Successfully']);
    }

    public function delete_campaign(Request $request)
    {
        // return $request->id;
        $id = $request->id;
        // $total_records = count($ids);
        DB::table('campaigns')->where('campaign_id', $id)->delete();
        return response()->json(['status' => true, 'message' => '<b>1</b> Campaign Deleted Successfully']);
    }

    public function campaign_builder_options()
    {
        DB::statement("SET SQL_MODE=''");
        //production
        $data['products'] = DB::table('order_products')->select('product_id', 'name', 'price', DB::raw("CONCAT('#', product_id,' - ',name,' - $',price ) AS full_name"))->where(['user_id' => Auth::id()])->groupBy('name')->get();
        $data['campaigns'] = DB::table('campaigns')->select('id', 'campaign_id', 'gateway_id', 'name')->where(['user_id' => Auth::id()])->whereNotNull('is_active')->groupBy('campaign_id')->get();
        $data['networks'] = DB::table('networks')->select('id', 'network_affiliate_id', 'network_id', 'name')->where(['user_id' => Auth::id()])->groupBy('network_affiliate_id')->get();

        //local
        // $data['products'] = DB::table('order_products')->select('product_id', 'name', 'price', DB::raw("CONCAT('#', product_id,' - ',name,' - $',price ) AS full_name"))->where(['user_id' => 2])->groupBy('name')->get();
        // $data['campaigns'] = DB::table('campaigns')->select('id', 'campaign_id', 'gateway_id', 'name')->where(['user_id' => 2])->whereNotNull('is_active')->groupBy('campaign_id')->get();
        // $data['networks'] = DB::table('networks')->select('id', 'network_affiliate_id', 'network_id', 'name')->where(['user_id' => 2])->groupBy('network_affiliate_id')->get();

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function campaign_view_data(Request $request)
    {
        DB::enableQueryLog();
        // $campaign = Campaign::where(['name' => $request->name, 'user_id' => 2])->first();
        // return $campaign;
        $campaign = Campaign::where(['name' => $request->name, 'user_id' => Auth::id()])->first();
        $tracking_campaign_ids = array_column($campaign->tracking_campaigns, 'campaign_id');
        $tracking_network_ids = array_column($campaign->tracking_networks, 'network_affiliate_id');
        $cycle_product_ids = array_column($campaign->cycle_products, 'product_id');
        // $upsell_product_ids = array_column($campaign->upsell_products, 'id');
        // $downsell_product_ids = array_column($campaign->downsell_products, 'id');

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
        // dd($data);

        // $data = [
        //     [
        //         "month" => "April",
        //         "year" => "2022",
        //         "name" => "Meal Plan App (c)",
        //         "initials" => 1,
        //         "rebills" => 0,
        //         "cycle_2" => 0,
        //         "cycle_3_plus" => 0,
        //         "CBs" => 0,
        //         "revenue" => 82348.84,
        //         "refund" => 4150.56,
        //         "CB_currency"=>'2000'
        //     ],
        //     [
        //         "month" => "May",
        //         "year" => "2022",
        //         "name" => "Meal Plan App (c)",
        //         "initials" => 1,
        //         "rebills" => 0,
        //         "cycle_2" => 0,
        //         "cycle_3_plus" => 0,
        //         "CBs" => 0,
        //         "revenue" => 82348.84,
        //         "refund" => 4150.56,
        //         "CB_currency"=>'2000'
        //     ],
        //     [
        //         "month" => "June",
        //         "year" => "2022",
        //         "name" => "Meal Plan App (c)",
        //         "initials" => 1,
        //         "rebills" => 0,
        //         "cycle_2" => 0,
        //         "cycle_3_plus" => 0,
        //         "CBs" => 0,
        //         "revenue" => 82348.84,
        //         "refund" => 4150.56,
        //         "CB_currency"=>'2000'
        //     ]
        // ];

        // $decline = $orders->where(['orders.order_status' => 7])->get()->count();
        // $CBs = $orders->where(['orders.order_status' => 7, 'orders.is_chargeback' => 1])->get()->count();
        // $net = $revenue + $refund + $CBs + $fulfillment + $processing + $cpa;

        // if ($initials != 0) {
        //     $cycle_1_per = $rebills / $initials;
        //     $avg_ticket = $revenue / $initials;
        //     $fulfillment = -$initials;
        //     $clv = $net / $initials;
        // }
        // if ($rebills != 0) {
        //     $cycle_2_per = $cycle_2 / $rebills;
        // }
        // if ($cycle_2 != 0) {
        //     $cycle_3_plus_per = $cycle_3_plus / $cycle_2;
        // }
        // if ($revenue != 0) {
        //     $refund_rate = $refund / $revenue;
        //     $CB_per = $CBs / $revenue;
        //     $processing = -0.2 * $revenue;
        // }
        return response()->json(['status' => true, 'data' => $data, 'Query' => DB::getQueryLog()]);
    }
}