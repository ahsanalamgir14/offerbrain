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
        $data = TicketWeekly::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $data]);
        /* 
            !implementation of the formulas in the whole database
         */
        $db_dates = TicketWeekly::pluck('week')->toArray();
        $data = TicketWeekly::all();
        // dd($data);
        $date_today = Carbon::now()->format('Y-m-d');
        $start_of_this_week = Carbon::now()->startOfWeek()->format('Y-m-d');
        $end_of_this_week = Carbon::now()->endOfWeek()->format('Y-m-d');
        // dd($end_of_week);

         /* 
             ?previous two month records insert query 
             for ($i = 19; $i > 0; $i--) {
                 $model = new TicketWeekly();
                 $previous_week_start = Carbon::now()->startOfWeek()->subWeeks($i)->format('Y-m-d');
                 if (in_array($previous_week_start, $db_dates) == false) {
                     $model->week = $previous_week_start;
                     $model->save();
                 }
             }
         */

        $latest = TicketWeekly::latest()->first();
        if (isset($latest)) {
            if ($latest->week != $start_of_this_week) {
                $model = new TicketWeekly();
                $model->week = $start_of_this_week;
                $model->save();
            }
        }

        /* 
            ?implementation of the formulas
         */

        foreach ($data as $t_key => $ticket) {

            $volume = 0;
            $rebill_condition1 = 0;
            $rebill_condition2 = 0;
            $rebill_per_condition2 = 0;
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
            $week_end_ticket = carbon::parse($ticket->week)->endOfWeek()->format('Y-m-d');
            // var_dump($ticket->week);
            // dd($week_end_ticket);
            Order::where(['prepaid_match' => 'NO', 'is_test_cc' => 0])->whereDate('acquisition_date', '>=', $ticket->week)->whereDate('acquisition_date', '<=', $week_end_ticket)->chunk(100, function ($orders) {
                foreach ($orders as $order) {
                    $order->products = unserialize($order->products);
                    $order->totals_breakdown = unserialize($order->totals_breakdown);
                    /*
                        !Calculation of initials, rebills, cycle_2, cycle_3 plus in golden ticket with order-status == "Declined"
                     */
                    if ((Str::contains($order->products[0]->name, ['(C)']) || Str::contains($order->products[0]->name, ['(I)'])) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                        $volume++;
                    }
                    if (Str::contains($order->products[0]->name, ['(R']) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                        $rebill_condition1++;
                    }
                    if (Str::contains($order->products[0]->name, ['(C1)']) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                        $rebill_condition2++;
                    }
                    if (Str::contains($order->products[0]->name, ['(I']) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                        $rebill_per_condition2++;
                    }
                    if ($order->is_chargeback == 1 && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                        $CBs++;
                    }
                    // if(Str::contains($order->products[0]->name, ['(CR2)']) && $order->products[0]->offer->name == 'Golden Ticket Offer' && $order->order_status== 7){
                    //     $cycle_2++;
                    // }
                    // if(Str::contains($order->products[0]->name, ['(CR+)']) && $order->products[0]->offer->name == 'Golden Ticket Offer' && $order->order_status== 7){
                    //     $cycle_3_plus++;
                    // }
                    // if($order->is_chargeback == 1 && $order->products[0]->offer->name == 'Golden Ticket Offer' && $order->order_status== 7){
                    //     $CBs++;
                    /*
                        !Calculation of Revenue 
                     */
                    if ($order->order_total != null && isset($order->products[0]->offer) && $order->products[0]->offer->name == 'Gizmo') {
                        if ($order->order_status == 7) {
                            $revenue += $order->order_total;
                        }
                        if ($order->order_status == 6) {
                        /* 
                            !calculation of refund with order-status == "void/refunded"
                             */
                            $refund += $order->order_total;
                        }
                    }
                }

            });
            $net = $revenue + $refund + $CBs + $fulfillment + $processing + $cpa;
            if ($rebill_condition2 != 0) {
                $rebills = $rebill_condition1 + $rebill_condition2;
                $rebill_per = $rebill_condition1 / $rebill_per_condition2;
            }
            if ($volume != 0) {
                $avg_ticket = $revenue / $volume;
                $clv = $net / $volume;
            }
            if ($revenue != 0) {
                $refund_rate = $refund / $revenue;
                $CB_per = $CBs / $revenue;
                $processing = -0.2 * $revenue;
            }
            // if()

            $ticket->volume = $volume;
            $ticket->rebills = $rebills;
            $ticket->rebill_per = $rebill_per;
            $ticket->avg_day = $avg_day;
            $ticket->filled_per = $filled_per;
            $ticket->avg_ticket = $avg_ticket;
            $ticket->revenue = $revenue;
            $ticket->refund = (($refund > 0) ? -$refund : $refund);
            $ticket->refund_rate = $refund_rate;
            $ticket->CBs = $CBs;
            $ticket->CB_per = $CB_per;
            $ticket->fulfillment = $fulfillment;
            $ticket->processing = $processing;
            // $ticket->cpa = ??
            // $ticket->cpa_avg = ??
            $ticket->net = $net;
            $ticket->clv = $clv;
            $ticket->save();
        }
        $data = TicketWeekly::orderBy('id', 'desc')->take(10)->get();
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
        $db_dates = TicketWeekly::pluck('week')->toArray();
        // $data = TicketWeekly::latest()->first();
        $date_today = Carbon::now()->format('Y-m-d');
        $start_of_this_week = Carbon::now()->startOfWeek()->format('Y-m-d');
        $end_of_this_week = Carbon::now()->endOfWeek()->format('Y-m-d');

        $latest = TicketWeekly::latest()->first();
        // if (isset($latest)) {
        //     if ($latest->week != $start_of_this_week) {
        //         $model = new TicketWeekly();
        //         $model->week = $start_of_this_week;
        //         $model->save();
        //     }
        // }

        /* 
            ?implementation of the formulas
         */

        $volume = 0;
        $rebill_condition1 = 0;
        $rebill_condition2 = 0;
        $rebill_per_condition2 = 0;
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
        $week_end_ticket = carbon::parse($latest->week)->endOfWeek()->format('Y-m-d');
        // var_dump($ticket->week);
        Order::where(['prepaid_match' => 'NO', 'is_test_cc' => 0])->whereDate('acquisition_date', '>=', $latest->week)->whereDate('acquisition_date', '<=', $week_end_ticket)->chunk(100, function ($orders) {
            dd($orders);
            foreach ($orders as $order) {
                $order->products = unserialize($order->products);
                $order->totals_breakdown = unserialize($order->totals_breakdown);
                /*
                    !Calculation of initials, rebills, cycle_2, cycle_3 plus in golden ticket with order-status == "Declined"
                 */
                if ((Str::contains($order->products[0]->name, ['(C)']) || Str::contains($order->products[0]->name, ['(I)'])) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                    $volume++;
                }
                if (Str::contains($order->products[0]->name, ['(R']) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                    $rebill_condition1++;
                }
                if (Str::contains($order->products[0]->name, ['(C1)']) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                    $rebill_condition2++;
                }
                if (Str::contains($order->products[0]->name, ['(I']) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                    $rebill_per_condition2++;
                }
                if ($order->is_chargeback == 1 && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                    $CBs++;
                }
                // if(Str::contains($order->products[0]->name, ['(CR2)']) && $order->products[0]->offer->name == 'Golden Ticket Offer' && $order->order_status== 7){
                //     $cycle_2++;
                // }
                // if(Str::contains($order->products[0]->name, ['(CR+)']) && $order->products[0]->offer->name == 'Golden Ticket Offer' && $order->order_status== 7){
                //     $cycle_3_plus++;
                // }
                // if($order->is_chargeback == 1 && $order->products[0]->offer->name == 'Golden Ticket Offer' && $order->order_status== 7){
                //     $CBs++;
                /*
                    !Calculation of Revenue 
                 */
                if ($order->order_total != null && isset($order->products[0]->offer) && $order->products[0]->offer->name == 'Gizmo') {
                    if ($order->order_status == 7) {
                        $revenue += $order->order_total;
                    }
                    if ($order->order_status == 6) {
                    /* 
                        !calculation of refund with order-status == "void/refunded"
                    */
                        $refund += $order->order_total;
                    }
                }
            }

        });
        $net = $revenue + $refund + $CBs + $fulfillment + $processing + $cpa;
        if ($rebill_condition2 != 0) {
            $rebills = $rebill_condition1 + $rebill_condition2;
            $rebill_per = $rebill_condition1 / $rebill_per_condition2;
        }
        if ($volume != 0) {
            $avg_ticket = $revenue / $volume;
            $clv = $net / $volume;
        }
        if ($revenue != 0) {
            $refund_rate = $refund / $revenue;
            $CB_per = $CBs / $revenue;
            $processing = -0.2 * $revenue;
        }
        // if()

        $latest->volume = $volume;
        $latest->rebills = $rebills;
        $latest->rebill_per = $rebill_per;
        $latest->avg_day = $avg_day;
        $latest->filled_per = $filled_per;
        $latest->avg_ticket = $avg_ticket;
        $latest->revenue = $revenue;
        $latest->refund = (($refund > 0) ? -$refund : $refund);
        $latest->refund_rate = $refund_rate;
        $latest->CBs = $CBs;
        $latest->CB_per = $CB_per;
        $latest->fulfillment = $fulfillment;
        $latest->processing = $processing;
        // $ticket->cpa = ??
        // $ticket->cpa_avg = ??
        $latest->net = $net;
        $latest->clv = $clv;
        $latest->save();

        $data = TicketWeekly::orderBy('id', 'desc')->take(10)->get();
        return response()->json(['status' => true, 'data' => $data]);
    }
}
