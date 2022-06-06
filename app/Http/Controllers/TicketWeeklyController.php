<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TicketWeekly;
use App\Models\Order;
use Illuminate\Support\Str;
use DB;
use Carbon\Carbon;

class TicketWeeklyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $db_dates = TicketWeekly::pluck('week')->toArray();
        $data = TicketWeekly::all();
        // dd($data);
        $date_today = Carbon::now()->format('Y-m-d');
        $start_of_this_week = Carbon::now()->startOfWeek()->format('Y-m-d');
        $end_of_this_week = Carbon::now()->endOfWeek()->format('Y-m-d');
        // dd($end_of_week);

         /* 
             ?previous two month records insert query !important comment
         */
        // for ($i = 26; $i > 0; $i--) {
        //     $model = new TicketWeekly();
        //     $previous_week_start = Carbon::now()->startOfWeek()->subWeeks($i)->format('Y-m-d');
        //     if (in_array($previous_week_start, $db_dates) == false) {
        //         $model->week = $previous_week_start;
        //         $model->save();
        //     }
        // }

        $latest = TicketWeekly::latest()->first();
        if (isset($latest)) {
            if ($latest->week != $start_of_this_week) {
                $model = new TicketWeekly();
                $model->week = $start_of_this_week;
                $model->save();
                $this->refresh_weekly();
            }
        }

        $data = TicketWeekly::orderBy('id', 'desc')->get();
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
    public function refresh_weekly()
    {
        $latest = TicketWeekly::latest()->first();

        $volume = 0;
        $rebills = 0;
        $rebill_per = 0;
        $avg_day = 0;
        $filled_per = 0;
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
        $week_end_ticket = Carbon::parse($latest->week)->endOfWeek()->format('Y-m-d');

        $query = Order::where('orders.time_stamp', '>=', $latest->week)
            ->where('orders.time_stamp', '<=', $week_end_ticket)
            ->where(['prepaid_match' => 'No', 'is_test_cc' => 0, 'orders.campaign_id' => 2])
            ->join('order_products', 'orders.order_id', 'order_products.order_id')
            ->addSelect(DB::raw('SUM(orders.order_total) as revenue'))
            ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c)%" then 0 end) as volume'))
            ->addSelect(DB::raw('COUNT(case when order_products.name not like "%(c)%" then 0 end) as rebills'))
                // ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c2)%" then 0 end) as cycle_2'))
                // ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c3+)%" then 0 end) as cycle_3_plus'))
            ->addSelect(DB::raw('SUM(case when orders.order_status = 6 then orders.order_total else 0 end) as refund'))
            ->addSelect(DB::raw('COUNT(case when orders.is_chargeback = 1 then 0 end) as CBs'))
            ->addSelect(DB::raw('SUM(case when orders.is_chargeback = 1 then orders.order_total else 0 end) as CB_currency'))
            ->groupBy('orders.acquisition_month')->first();

        if ($query) {
            $volume = $query['volume'];
                // $COGS = $volume;
            $rebills = $query['rebills'];
            $refund = $query['refund'];
            $revenue = $query['revenue'];
            $CBs = $query['CBs'];
            $CB_currency = $query['CB_currency'];
        }

        $fulfillment = $volume * -18;

        if ($revenue != 0) {
            $refund_rate = ($refund / $revenue) * 100;
            $CB_per = ($CBs / $revenue) * 100;
            $processing = 0.2 * $revenue;
        }
            // if ($rebills != 0) {
            //     $cycle_2_per = ($query['cycle_2'] / $rebills) * 100;
            // }

            // $net = $revenue - $refund - $CBs + $fulfillment + $processing + $cpa;
        $net = $revenue - $refund - $CBs - $processing + $cpa;

        if ($volume != 0) {
            $rebill_per = ($rebills / $volume) * 100;
            $avg_ticket = $revenue / $volume;
            $clv = $net / $volume;
        }
            // if ($cycle_2 != 0) {
            //     $cycle_3_plus_per = ($query['cycle_3_plus'] / $cycle_2) * 100;
            // }

        $latest->volume = $volume;
        $latest->rebills = $rebills;
        $latest->rebill_per = $rebill_per;
            // $latest->cycle_2 = $query['cycle_2'];
            // $latest->cycle_2_per = $cycle_2_per;
            // $latest->cycle_3_plus = $query['cycle_3_plus'];
            // $latest->cycle_3_plus_per = $cycle_3_plus_per;
            // $latest->avg_day = $avg_day / 30;
        $latest->avg_ticket = $avg_ticket;
        $latest->revenue = $revenue;
        $latest->refund = $refund;
            // $latest->filled_per = $filled_per;
        $latest->refund_rate = (($refund_rate > 0) ? -$refund_rate : $refund_rate);
        $latest->CBs = $CBs;
        $latest->CB_per = (($CB_per > 0) ? -$CB_per : $CB_per);
        $latest->CB_currency = $CB_currency;
            // $latest->COGS = $COGS;
            // $latest->fulfillment = $fulfillment;
        $latest->processing = $processing;
            // $latest->cpa = ??
            // $latest->cpa_avg = $cpa / $volume??
        $latest->net = $net;
        $latest->clv = $clv;
        $latest->save();

        $data = TicketWeekly::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function refresh_all_weekly()
    {
        $data = TicketWeekly::orderBy('id', 'desc')->get();

        foreach ($data as $ticket) {

            $volume = 0;
            $rebills = 0;
            $rebill_per = 0;
            $avg_day = 0;
            $filled_per = 0;
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
            $week_end_ticket = Carbon::parse($ticket->week)->endOfWeek()->format('Y-m-d');

            $query = Order::where('orders.time_stamp', '>=', $ticket->week)
                ->where('orders.time_stamp', '<=', $week_end_ticket)
                ->where(['prepaid_match' => 'No', 'is_test_cc' => 0, 'orders.campaign_id' => 2])
                ->join('order_products', 'orders.order_id', 'order_products.order_id')
                ->addSelect(DB::raw('SUM(orders.order_total) as revenue'))
                ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c)%" then 0 end) as volume'))
                ->addSelect(DB::raw('COUNT(case when order_products.name not like "%(c)%" then 0 end) as rebills'))
                // ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c2)%" then 0 end) as cycle_2'))
                // ->addSelect(DB::raw('COUNT(case when order_products.name like "%(c3+)%" then 0 end) as cycle_3_plus'))
                ->addSelect(DB::raw('SUM(case when orders.order_status = 6 then orders.order_total else 0 end) as refund'))
                ->addSelect(DB::raw('COUNT(case when orders.is_chargeback = 1 then 0 end) as CBs'))
                ->addSelect(DB::raw('SUM(case when orders.is_chargeback = 1 then orders.order_total else 0 end) as CB_currency'))
                ->groupBy('orders.acquisition_month')->first();

            if ($query) {
                $volume = $query['volume'];
                // $COGS = $volume;
                $rebills = $query['rebills'];
                $refund = $query['refund'];
                $revenue = $query['revenue'];
                $CBs = $query['CBs'];
                $CB_currency = $query['CB_currency'];
            }

            $fulfillment = $volume * -18;

            if ($revenue != 0) {
                $refund_rate = ($refund / $revenue) * 100;
                $CB_per = ($CBs / $revenue) * 100;
                $processing = 0.2 * $revenue;
            }
            // if ($rebills != 0) {
            //     $cycle_2_per = ($query['cycle_2'] / $rebills) * 100;
            // }

            // $net = $revenue - $refund - $CBs + $fulfillment + $processing + $cpa;
            $net = $revenue - $refund - $CBs - $processing + $cpa;

            if ($volume != 0) {
                $rebill_per = ($rebills / $volume) * 100;
                $avg_ticket = $revenue / $volume;
                $clv = $net / $volume;
            }
            // if ($cycle_2 != 0) {
            //     $cycle_3_plus_per = ($query['cycle_3_plus'] / $cycle_2) * 100;
            // }

            $ticket->volume = $volume;
            $ticket->rebills = $rebills;
            $ticket->rebill_per = $rebill_per;
            // $ticket->cycle_2 = $query['cycle_2'];
            // $ticket->cycle_2_per = $cycle_2_per;
            // $ticket->cycle_3_plus = $query['cycle_3_plus'];
            // $ticket->cycle_3_plus_per = $cycle_3_plus_per;
            // $ticket->avg_day = $avg_day / 30;
            $ticket->avg_ticket = $avg_ticket;
            $ticket->revenue = $revenue;
            $ticket->refund = $refund;
            // $ticket->filled_per = $filled_per;
            $ticket->refund_rate = (($refund_rate > 0) ? -$refund_rate : $refund_rate);
            $ticket->CBs = $CBs;
            $ticket->CB_per = (($CB_per > 0) ? -$CB_per : $CB_per);
            $ticket->CB_currency = $CB_currency;
            // $ticket->COGS = $COGS;
            // $ticket->fulfillment = $fulfillment;
            $ticket->processing = $processing;
            // $ticket->cpa = ??
            // $ticket->cpa_avg = $cpa / $volume??
            $ticket->net = $net;
            $ticket->clv = $clv;
            $ticket->save();
        }

        $data = TicketWeekly::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $data]);
    }
}
