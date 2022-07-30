<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GoldenTicket;
use App\Models\Formula;
use App\Models\Order;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DB;

class GoldenTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = GoldenTicket::get();
        return response()->json(['status' => true, 'data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if ($request->month && $request->year) {
            $golden_ticket = GoldenTicket::where(['month' => $request->month, 'year' => $request->year])->first();
            if ($golden_ticket) {
                return response()->json(['status' => false, 'message' => "Current Month is already added"], 200);
            } else {
                $model = new GoldenTicket();
                $model->month = $request->month;
                $model->year = $request->year;
                $model->save();
                return response()->json(['status' => true, 'message' => 'Current Month added successfully'], 500);
            }
        }
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

    public function refresh_golden_ticket(Request $request)
    {
        // $golden_tickets = GoldenTicket::where(['year'=>Carbon::now()->format('Y')-1])->get();
        if ($request->month && $request->year) {
            if ($request->month != "null" && $request->year != "null") {
                $golden_tickets = GoldenTicket::where(['month' => $request->month, 'year' => $request->year])->get();
            } else if ($request->month != "null") {
                $golden_tickets = GoldenTicket::where(['month' => $request->month])->get();
            } else if ($request->year != "null") {
                $golden_tickets = GoldenTicket::where(['year' => $request->year])->get();
            }
        } else {
            $golden_tickets = GoldenTicket::all();
        }

        foreach ($golden_tickets as $t_key => $ticket) {
            $date = $ticket->month . ' ' . $ticket->year;
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

            $orders = DB::table('orders')->where(['orders.prepaid_match' => 'No', 'orders.is_test_cc' => 0, 'orders.campaign_id' => 2])
                ->where('orders.time_stamp', '>=', $start_day)
                ->where('orders.time_stamp', '<=', $end_day)
                ->select('orders.order_id', 'orders.time_stamp', 'orders.acquisition_month', 'orders.acquisition_year');

            $join = $orders->join('order_products', 'orders.order_id', 'order_products.order_id')
                ->where(['orders.order_status' => 7, 'order_products.offer_name' => 'Golden Ticket Offer'])
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

            $ticket->initials = $initials;
            $ticket->rebills = $rebills;
            $ticket->cycle_2 = $cycle_2;
            $ticket->cycle_3_plus = $cycle_3_plus;
            $ticket->cycle_1_per = $cycle_1_per;
            $ticket->cycle_2_per = $cycle_2_per;
            $ticket->cycle_3_plus_per = $cycle_3_plus_per;
            $ticket->revenue = $revenue;
            $ticket->avg_ticket = $avg_ticket;
            $ticket->refund = (($refund > 0) ? -$refund : $refund);
            $ticket->refund_rate = $refund_rate;
            $ticket->CBs = (($CBs > 0) ? -$CBs : $CBs);
            $ticket->CB_per = $CB_per;
            $ticket->fulfillment = $fulfillment;
            $ticket->processing = $processing;
            // $ticket->processing = $processing;
            // $ticket->cpa = ??
            // $ticket->cpa_avg = ??
            $ticket->net = $net;
            $ticket->clv = $clv;
            $ticket->save();
        }
        return response()->json(['status' => true, 'data' => $golden_tickets]);
    }
}
