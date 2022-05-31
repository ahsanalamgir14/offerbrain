<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TicketMonthly;
use App\Models\Order;
use Illuminate\Support\Str;
use DB;
use Carbon\Carbon;


class TicketMonthlyController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $data = TicketMonthly::orderBy('id', 'desc')->take(10)->get();
        // return response()->json(['status' => true, 'data' => $data]);
        $db_months = TicketMonthly::pluck('month')->toArray();
        $data = TicketMonthly::all();
        $date_today = Carbon::now()->format('Y-m-d');
        $current_month = Carbon::now()->format('F');
        $current_year = Carbon::now()->format('Y');
        // $end_of_this_week = Carbon::now()->endOfWeek()->format('Y-m-d');

         /* 
            ?previous two month records insert query !important
         */
        /*for ($i = 2; $i > 0; $i--) {
            $model = new TicketMonthly();
            $previous_month_date = Carbon::now()->subMonths($i)->format('Y-m-d');
            $previous_month = Carbon::parse($previous_month_date)->format('F');
            $previous_year = Carbon::parse($previous_month_date)->format('Y');
            if (in_array($previous_month, $db_months) == false) {
                $model->month = $previous_month;
                $model->year = $previous_year;
                $model->save();
            }
        } */
        $latest = TicketMonthly::orderBy('id', 'desc')->first();
        if (isset($latest)) {
            if ($latest->month != $current_month) {
                $model = new TicketMonthly();
                $model->month = $current_month;
                $model->year = $current_year;
                $model->save();
                $this->refresh_monthly();
            }
        }

        $data = TicketMonthly::orderBy('id', 'desc')->take(10)->get();
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
        //
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
    public function refresh_monthly()
    {
        $db_months = TicketMonthly::pluck('month')->toArray();
        $date_today = Carbon::now()->format('Y-m-d');
        $current_month = Carbon::now()->format('F');
        $current_year = Carbon::now()->format('Y');
        $latest = TicketMonthly::orderBy('id', 'desc')->first();

        if (isset($latest)) {
            if ($latest->month != $current_month) {
                $model = new TicketMonthly();
                $model->month = $current_month;
                $model->year = $current_year;
                $model->save();
            }
        }
        $initials = 0;
        $initial_condition_2 = 0;
        $rebills = 0;
        $cycle_1_per = 0;
        $cycle_2 = 0;
        $cycle_2_per = 0;
        $cycle_3_plus = 0;
        $cycle_3_plus_per = 0;
        // $avg_day = 0;
        // $filled_per = 0;
        $avg_ticket = 0;
        $revenue = 0;
        $refund = 0;
        $refund_rate = 0;
        $CBs = 0;
        $CB_per = 0;
        $CB_currency = 0;
        $fulfillment = 0;
        $processing = 0;
        $cpa = 0;
        $cpa_avg = 0;
        $net = 0;
        $clv = 0;

        $start_of_month = $latest->year . '-' . $latest->month . '-01';
        $start_of_month = Carbon::parse($start_of_month)->startOfMonth();
        $end_of_month = Carbon::parse($start_of_month)->endOfMonth();

        $query = Order::where('orders.time_stamp', '>=', $start_of_month)
            ->where('orders.time_stamp', '<=', $end_of_month)
            ->where(['prepaid_match' => 'No', 'is_test_cc' => 0, 'orders.campaign_id' => 2])
            ->join('order_products', 'orders.order_id', 'order_products.order_id')
        // ->select(DB::raw('COUNT(orders.order_id) as total_count'))
            ->addSelect(DB::raw('SUM(orders.order_total) as revenue'))
            ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c)%" then 0 end) as initials'))
            ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c1)%" then 0 end) as rebills'))
            ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c2)%" then 0 end) as cycle_2'))
            ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c3+)%" then 0 end) as cycle_3_plus'))
            ->addSelect(DB::raw('SUM(case when orders.order_status = 6 then orders.order_total else 0 end) as refund'))
            ->addSelect(DB::raw('COUNT(case when orders.is_chargeback = 1 then 0 end) as CBs'))
            ->addSelect(DB::raw('SUM(case when orders.is_chargeback = 1 then orders.order_total else 0 end) as CB_currency'))
            ->groupBy('orders.acquisition_month')->first();

        $initials = $query['initials'];
        $COGS = $initials;
        $rebills = $query['rebills'];
        $refund = $query['refund'];
        $revenue = $query['revenue'];
        $CBs = $query['CBs'];
        $CB_currency = $query['CB_currency'];
        $fulfillment = $initials * -18;

        if ($revenue != 0) {
            $refund_rate = ($refund / $revenue) * 100;
            $CB_per = ($CBs / $revenue) * 100;
        // dd($CB_per);
            $processing = 0.2 * $revenue;
        }
        if ($rebills != 0) {
            $cycle_2_per = ($query['cycle_2'] / $rebills) * 100;
        }

        // $net = $revenue - $refund - $CBs + $fulfillment + $processing + $cpa;
        $net = $revenue - $refund - $CBs - $processing - $COGS + $cpa;

        if ($initials != 0) {
            $cycle_1_per = ($rebills / $initials) * 100;
            $avg_ticket = $revenue / $initials;
            $clv = $net / $initials;
        }
        if ($cycle_2 != 0) {
            $cycle_3_plus_per = ($query['cycle_3_plus'] / $cycle_2) * 100;
        }

        $latest->initials = $initials;
        $latest->rebills = $rebills;
        $latest->cycle_1_per = $cycle_1_per;
        $latest->cycle_2 = $query['cycle_2'];
        $latest->cycle_2_per = $cycle_2_per;
        $latest->cycle_3_plus = $query['cycle_3_plus'];
        $latest->cycle_3_plus_per = $cycle_3_plus_per;
        // $latest->avg_day = $avg_day / 30;
        $latest->avg_ticket = $avg_ticket;
        $latest->revenue = $revenue;
        $latest->refund = $refund;
        // $latest->filled_per = $filled_per;
        $latest->refund_rate = (($refund_rate > 0) ? -$refund_rate : $refund_rate);
        $latest->CBs = $CBs;
        $latest->CB_per = (($CB_per > 0) ? -$CB_per : $CB_per);
        $latest->CB_currency = $CB_currency;
        $latest->COGS = $COGS;
        // $latest->fulfillment = $fulfillment;
        $latest->processing = $processing;
        // $latest->cpa = ??
        // $latest->cpa_avg = $cpa / $initials??
        $latest->net = $net;
        $latest->clv = $clv;
        $latest->save();

        $data = TicketMonthly::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function refresh_all_monthly()
    {
        DB::enableQueryLog();
        // $data = TicketMonthly::orderBy('id', 'desc')->take(10)->get();
        $db_months = TicketMonthly::pluck('month')->toArray();
        $data = TicketMonthly::orderBy('id', 'desc')->get();
        // $data = TicketMonthly::orderBy('id', 'desc')->take(2)->get();
        // dd($data);
        $date_today = Carbon::now()->format('Y-m-d');
        $current_month = Carbon::now()->format('F');
        $current_year = Carbon::now()->format('Y');
        // $latest = TicketMonthly::orderBy('id', 'desc')->first();

        foreach ($data as $t_key => $ticket) {

            $initials = 0;
            $initial_condition_2 = 0;
            $rebills = 0;
            $cycle_1_per = 0;
            $cycle_2 = 0;
            $cycle_2_per = 0;
            $cycle_3_plus = 0;
            $cycle_3_plus_per = 0;
            // $avg_day = 0;
            // $filled_per = 0;
            $avg_ticket = 0;
            $revenue = 0;
            $refund = 0;
            $refund_rate = 0;
            $CBs = 0;
            $CB_per = 0;
            $CB_currency = 0;
            $fulfillment = 0;
            $processing = 0;
            $cpa = 0;
            $cpa_avg = 0;
            $net = 0;
            $clv = 0;

            $start_of_month = $ticket->year . '-' . $ticket->month . '-01';
            // $start_of_month = '2022-April-01';
            $start_of_month = Carbon::parse($start_of_month)->startOfMonth();
            $end_of_month = Carbon::parse($start_of_month)->endOfMonth()->format('Y-m-d');

            $query = Order::where('orders.time_stamp', '>=', $start_of_month)
                ->where('orders.time_stamp', '<=', $end_of_month)
                ->where(['prepaid_match' => 'No', 'is_test_cc' => 0, 'orders.campaign_id' => 2])
                ->join('order_products', 'orders.order_id', 'order_products.order_id')
                // ->select(DB::raw('COUNT(orders.order_id) as total_count'))
                ->addSelect(DB::raw('SUM(orders.order_total) as revenue'))
                ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c)%" then 0 end) as initials'))
                ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c1)%" then 0 end) as rebills'))
                ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c2)%" then 0 end) as cycle_2'))
                ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c3+)%" then 0 end) as cycle_3_plus'))
                ->addSelect(DB::raw('SUM(case when orders.order_status = 6 then orders.order_total else 0 end) as refund'))
                ->addSelect(DB::raw('COUNT(case when orders.is_chargeback = 1 then 0 end) as CBs'))
                ->addSelect(DB::raw('SUM(case when orders.is_chargeback = 1 then orders.order_total else 0 end) as CB_currency'))
                // ->addSelect(DB::raw('SUM(case when order_products.name like "%(c3+)%" then 0 end) as cycle_3_plus'))
                // ->addSelect(DB::raw('(COUNT(case when order_products.name like "%(c1)%" then 0 end) / COUNT(case when order_products.name like "%(c)%" then 0 end)) * 100 as cycle_1_per'))
                // ->addSelect(DB::raw('(COUNT(case when order_products.name like "%(c2)%" then 0 end) / COUNT(case when order_products.name like "%(c1)%" then 0 end)) * 100 as cycle_2_per'))
                // ->addSelect(DB::raw('(COUNT(case when order_products.name like "%(c3+)%" then 0 end) / COUNT(case when order_products.name like "%(c2)%" then 0 end)) * 100 as cycle_3_per'))
                ->groupBy('orders.acquisition_month')->first();
            // dd($query);
            $initials = $query['initials'];
            $COGS = $initials;
            $rebills = $query['rebills'];
            $refund = $query['refund'];
            $revenue = $query['revenue'];
            $CBs = $query['CBs'];
            $CB_currency = $query['CB_currency'];

            $fulfillment = $initials * -18;

            if ($revenue != 0) {
                $refund_rate = ($refund / $revenue) * 100;
                $CB_per = ($CBs / $revenue) * 100;
                // dd($CB_per);
                $processing = 0.2 * $revenue;
            }
            if ($rebills != 0) {
                $cycle_2_per = ($query['cycle_2'] / $rebills) * 100;
            }

            // $net = $revenue - $refund - $CBs + $fulfillment + $processing + $cpa;
            $net = $revenue - $refund - $CBs - $processing - $COGS + $cpa;

            if ($initials != 0) {
                $cycle_1_per = ($rebills / $initials) * 100;
                $avg_ticket = $revenue / $initials;
                $clv = $net / $initials;
            }
            if ($cycle_2 != 0) {
                $cycle_3_plus_per = ($query['cycle_3_plus'] / $cycle_2) * 100;
            }

            $ticket->initials = $initials;
            $ticket->rebills = $rebills;
            $ticket->cycle_1_per = $cycle_1_per;
            $ticket->cycle_2 = $query['cycle_2'];
            $ticket->cycle_2_per = $cycle_2_per;
            $ticket->cycle_3_plus = $query['cycle_3_plus'];
            $ticket->cycle_3_plus_per = $cycle_3_plus_per;
            // $ticket->avg_day = $avg_day / 30;
            $ticket->avg_ticket = $avg_ticket;
            $ticket->revenue = $revenue;
            $ticket->refund = $refund;
            // $ticket->filled_per = $filled_per;
            $ticket->refund_rate = (($refund_rate > 0) ? -$refund_rate : $refund_rate);
            $ticket->CBs = $CBs;
            $ticket->CB_per = (($CB_per > 0) ? -$CB_per : $CB_per);
            $ticket->CB_currency = $CB_currency;
            $ticket->COGS = $COGS;
            // $ticket->fulfillment = $fulfillment;
            $ticket->processing = $processing;
            // $ticket->cpa = ??
            // $ticket->cpa_avg = $cpa / $initials??
            $ticket->net = $net;
            $ticket->clv = $clv;
            $ticket->save();
        }
        $data = TicketMonthly::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $data]);
    }
}