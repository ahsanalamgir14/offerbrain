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
        // dd($request->all());
        // $data = Campaign::find(250);
        // return response()->json(['status' => true, 'data' => $data]);
        // dd($request->user()->id);
        $data = Campaign::where(['user_id' => 2])->whereNull('is_active')->get();
        // $data = Campaign::where(['user_id' => $request->user()->id])->get();
        return response()->json(['status' => true, 'data' => $data]);
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
        // dd($request->all());
        $data = $request->all();
        $campaign = new Campaign();
        $data['campaign_id'] = rand(100000, 999999);
        $db_campaign_ids = Campaign::all()->pluck('campaign_id')->toArray();
        $data['user_id'] = $request->user()->id;
        $data['created_at'] = Carbon::now();
        // if ($data['updated_at']) {
        //     $data['updated_at'] = $data['updated_at']['date'];
        //     $data['updator'] = serialize($data['updator']);
        // }
        // $data['creator'] = serialize($data['creator']);
        // $data['countries'] = serialize($data['countries']);
        // $data['offers'] = serialize($data['offers']);
        // $data['channel'] = serialize($data['channel']);
        // $data['payment_methods'] = serialize($data['payment_methods']);
        // if ($data['gateway']) {
            // $data['gateway'] = serialize($data['gateway']);
        // }
        // $data['alternative_payments'] = serialize($data['alternative_payments']);
        // $data['shipping_profiles'] = serialize($data['shipping_profiles']);
        // $data['return_profiles'] = serialize($data['return_profiles']);
        // $data['postback_profiles'] = serialize($data['postback_profiles']);
        // $data['coupon_profiles'] = serialize($data['coupon_profiles']);
        // $data['fraud_providers'] = serialize($data['fraud_providers']);
        // $data['volume_discounts'] = serialize($data['volume_discounts']);
        if (in_array($data['campaign_id'], $db_campaign_ids)) {
            return response()->json(['status' => false, 'message' => 'Please click again to save']);
        } else {
            $campaign->create($data);
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
    public function destroy($id)
    {
        //
    }

    public function campaign_builder_options()
    {
        DB::statement("SET SQL_MODE=''");
        //production
        // $data['products'] = DB::table('order_products')->select('id', 'name')->where(['user_id'=> Auth::id()])->groupBy('name')->get();
        // $data['campaigns'] = DB::table('campaigns')->select('id', 'campaign_id', 'gateway_id', 'name')->where(['user_id'=> Auth::id()])->groupBy('campaign_id')->get();
        // $data['networks'] = DB::table('networks')->select('id', 'network_affiliate_id', 'network_id', 'name')->where(['user_id'=> Auth::id()])->groupBy('network_affiliate_id')->get();

        //local
        $data['products'] = DB::table('order_products')->select('id', 'name')->where(['user_id' => 2])->groupBy('name')->get();
        $data['campaigns'] = DB::table('campaigns')->select('id', 'campaign_id', 'gateway_id', 'name')->where(['user_id' => 2])->whereNotNull('is_active')->groupBy('campaign_id')->get();
        $data['networks'] = DB::table('networks')->select('id', 'network_affiliate_id', 'network_id', 'name')->where(['user_id' => 2])->groupBy('network_affiliate_id')->get();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function campaign_view_data(Request $request)
    {
        $campaign = Campaign::where(['name' => $request->name, 'user_id' => 2])->first();
        // $campaign = Campaign::where(['name' => $request->name, 'user_id' => Auth::id()])->first();
        $tracking_campaign_ids = array_column($campaign->tracking_campaigns, 'campaign_id');
        $tracking_network_ids = array_column($campaign->tracking_networks, 'network_affiliate_id');
        $cycle_product_ids = array_column($campaign->tracking_networks, 'network_affiliate_id');
        // $upsell_product_ids = array_column($campaign->upsell_products, 'id');
        // $downsell_product_ids = array_column($campaign->downsell_products, 'id');

        $orders = DB::table('orders')->where(['orders.user_id' => 2, 'orders.prepaid_match' => 'No', 'orders.is_test_cc' => 0])
        // $orders = DB::table('orders')->where(['orders.user_id' => Auth::id(), 'orders.prepaid_match' => 'No', 'orders.is_test_cc' => 0])
            // ->whereIn('orders.campaign_id', $tracking_campaign_ids)
            // ->whereIn('orders.affiliate', $tracking_network_ids)
            ->select('orders.acquisition_month as month', 'orders.acquisition_year as year', 'order_products.name')
            // ->where('orders.time_stamp', '>=', $start_day)
            // ->where('orders.time_stamp', '<=', $end_day)
            ->leftJoin('order_products', 'orders.order_id', 'order_products.order_id')
            ->addSelect(DB::raw('Round(SUM(case when orders.order_status = 2 then orders.order_total else 0 end), 2) as revenue'))
            ->addSelect(DB::raw('Round(SUM(case when orders.order_status = 6 then orders.order_total else 0 end), 2) as refund'))
            ->addSelect(DB::raw('count(case when order_products.name like "%(c)%" then 1 else 0 end) as initials'))
            ->selectRaw('count(case when order_products.id  then 0 end) as rebills')
            ->selectRaw('count(case when order_products.name like "%(c2)%" then 0 end) as cycle_2')
            ->selectRaw('count(case when order_products.name like "%(c3)%" then 0 end) as cycle_3_plus')
            ->selectRaw('count(case when orders.is_chargeback = 1 then 0 end) as CBs')
            ->addSelect(DB::raw('SUM(case when orders.is_chargeback = 1 then orders.order_total else 0 end) as CB_currency'))
            ->groupBy('orders.acquisition_month');

        $data = $orders->get();
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
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function refresh_campaign_view(Request $request)
    {
        $table_name = Str::snake($request->name);
        $table_name = Str::of($table_name)->append('_view');
        $db_months = DB::table($table_name)->pluck('month')->toArray();
        $db_years = DB::table($table_name)->pluck('year')->toArray();
        // dd($db_months);

        if ($request->month && $request->year) {
            if ($request->month != "null" && $request->year != "null") {
                $table_data = DB::table($table_name)->where(['month' => $request->month, 'year' => $request->year])->get();
            } else if ($request->month != "null") {
                $table_data = DB::table($table_name)->where(['month' => $request->month])->get();
            } else if ($request->year != "null") {
                $table_data = DB::table($table_name)->where(['year' => $request->year])->get();
            }
        } else {
            $table_data = DB::table($table_name)->get();
        }

        // $months = DB::table('orders')->select('acquisition_month as month', 'acquisition_year as year')->where(['user_id' => 2])->groupBy('acquisition_month', 'acquisition_year')->get();

        // foreach ($months as $object) {
        //     if (!in_array($object->month, $db_months) && !in_array($object->year, $db_years)) {
        //         DB::table($table_name)->insert([
        //             'month' => $object->month,
        //             'year' => $object->year
        //         ]);
        //     }
        // }

        //add current month
        $current_month = Carbon::now()->format('F');
        $current_year = Carbon::now()->format('Y');
        // dd($current_month);
        if (!in_array($current_month, $db_months) && !in_array($current_year, $db_years)) {
            DB::table($table_name)->insert([
                'month' => $current_month,
                'year' => $current_year
            ]);
        }

        $db_months = DB::table($table_name)->pluck('month')->toArray();
        $db_years = DB::table($table_name)->pluck('year')->toArray();
        $campaign = Campaign::where(['name' => $request->name, 'user_id' => Auth::id()])->first();
        // dd($campaign->tracking_networks);
        $tracking_campaign_ids = array_column($campaign->tracking_campaigns, 'campaign_id');
        $tracking_network_ids = array_column($campaign->tracking_networks, 'network_affiliate_id');
        // return $tracking_campaign_ids;

        foreach ($table_data as $row) {
            $date = $row->month . ' ' . $row->year;
            $start_day = Carbon::parse($date)->startOfMonth();
            $end_day = Carbon::parse($date)->endOfMonth();

            $initials = 0;
            $rebills = 0;
            $cycle_2 = 0;
            $cycle_3_plus = 0;
            $cycle_1_per = 0;
            $cycle_2_per = 0;
            $cycle_3_plus_per = 0;
            $revenue = 0;
            $avg_ticket = 0;
            $refund = 0;
            $refund_rate = 0;
            $CBs = 0;
            $CB_per = 0;
            $fulfillment = 0;
            $processing = 0;
            $cpa = 0;
            $cpa_avg = 0;
            $net = 0;
            $clv = 0;

            $orders = DB::table('orders')->where(['orders.user_id' => Auth::id(), 'orders.prepaid_match' => 'No', 'orders.is_test_cc' => 0])->whereIn('orders.campaign_id', $tracking_campaign_ids)
                ->where('orders.time_stamp', '>=', $start_day)
                ->where('orders.time_stamp', '<=', $end_day)
                ->select('orders.order_id', 'orders.time_stamp', 'orders.acquisition_month', 'orders.acquisition_year');
            // dd($orders->get()->count());

            $join = $orders->join('order_products', 'orders.order_id', 'order_products.order_id')
                ->where(['orders.order_status' => 7])
                ->select('order_products.*')
                ->groupBy('orders.order_id');

            $decline = $orders->where(['orders.order_status' => 7])->get()->count();
            $initials = $join->where('order_products.name', 'LIKE', '%(c)%')->get()->count();
            $rebills = $join->where('order_products.name', 'LIKE', '%(CR1)%')->get()->count();
            $cycle_2 = $join->where('order_products.name', 'LIKE', '%(CR2)%')->get()->count();
            $cycle_3_plus = $join->where('order_products.name', 'LIKE', '%(CR+)%')->get()->count();
            $CBs = $orders->where(['orders.order_status' => 7, 'orders.is_chargeback' => 1])->get()->count();
            $net = $revenue + $refund + $CBs + $fulfillment + $processing + $cpa;

            if ($initials != 0) {
                $cycle_1_per = $rebills / $initials;
                $avg_ticket = $revenue / $initials;
                $fulfillment = -$initials;
                $clv = $net / $initials;
            }
            if ($rebills != 0) {
                $cycle_2_per = $cycle_2 / $rebills;
            }
            if ($cycle_2 != 0) {
                $cycle_3_plus_per = $cycle_3_plus / $cycle_2;
            }
            if ($revenue != 0) {
                $refund_rate = $refund / $revenue;
                $CB_per = $CBs / $revenue;
                $processing = -0.2 * $revenue;
            }

            $result = [];
            $result['initials'] = $initials;
            $result['rebills'] = $rebills;
            $result['cycle_2'] = $cycle_2;
            $result['cycle_3_plus'] = $cycle_3_plus;
            $result['cycle_1_per'] = $cycle_1_per;
            $result['cycle_2_per'] = $cycle_2_per;
            $result['cycle_3_plus_per'] = $cycle_3_plus_per;
            $result['revenue'] = $revenue;
            $result['avg_ticket'] = $avg_ticket;
            $result['refund'] = (($refund > 0) ? -$refund : $refund);
            $result['refund_rate'] = $refund_rate;
            $result['CBs'] = (($CBs > 0) ? -$CBs : $CBs);
            $result['CB_per'] = $CB_per;
            $result['fulfillment'] = $fulfillment;
            $result['processing'] = $processing;
            // $result['processing'] = $processing;
            // $result['cpa'] = ??
            // $result['cpa_avg'] = ??
            $result['net'] = $net;
            $result['clv'] = $clv;

            DB::table($table_name)->where(['month' => $row->month, 'year' => $row->year])->update($result);
        }
        return response()->json(['status' => true, 'data' => $table_data]);
    }
}