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
        $data = TicketMonthly::orderBy('id', 'desc')->take(10)->get();
        return response()->json(['status' => true, 'data' => $data]);
        // dd($data);
        // $data = Order::take(10)->get();
        // dd($data);
        // DB::table('sticket_monthly')->truncate();
        // dd('die');
        // $data = TicketMonthly::orderBy('id', 'desc')->get();
        // return response()->json(['status' => true, 'data' => $data]);

        /* 
            !implementation of the formulas in the whole database
         */
        $db_months = TicketMonthly::pluck('month')->toArray();
        $data = TicketMonthly::all();
        // dd($data);
        $date_today = Carbon::now()->format('Y-m-d');
        $current_month = Carbon::now()->format('F');
        $current_year = Carbon::now()->format('Y');
        // dd($current_year);

        // $end_of_this_week = Carbon::now()->endOfWeek()->format('Y-m-d');

         /* 
            ?previous two month records insert query
         */
        for ($i = 2; $i > 0; $i--) {
            $model = new TicketMonthly();
            $previous_month_date = Carbon::now()->subMonths($i)->format('Y-m-d');
            $previous_month = Carbon::parse($previous_month_date)->format('F');
            $previous_year = Carbon::parse($previous_month_date)->format('Y');
            if (in_array($previous_month, $db_months) == false) {
                $model->month = $previous_month;
                $model->year = $previous_year;
                $model->save();
            }
        }
        $latest = TicketMonthly::orderBy('id', 'desc')->first();
        // dd($latest);
        if (isset($latest)) {
            if ($latest->month != $current_month) {
                $model = new TicketMonthly();
                $model->month = $current_month;
                $model->year = $current_year;
                $model->save();
            }
        }
        // dd('die');
        /* 
            ?implementation of the formulas
         */
        $initial_condition_1 = 0;
        Order::where(['prepaid_match' => 'NO', 'is_test_cc' => 0, 'order_status' => 7])->whereDate('acquisition_date', '>=', '2021-07-01 00:00:00')->whereDate('acquisition_date', '<=', '2022-01-31 00:00:00')->chunk(1000, function ($orders) use ($initial_condition_1) {
            // dd($orders);
            foreach ($orders as $key => $order) {
                $order->products = isset($order->products) && $order->products != [] && $order->products != '' ? unserialize($order->products) : null;
                    // var_dump($order);
                    // $order->totals_breakdown = unserialize($order->totals_breakdown);
                    // dd('die');
                if (isset($order->products) == true && $order->products != null) {
                    if (Str::contains($order->products[0]->name, ['(I)']) && $order->products[0]->offer->name == 'Gizmo') {
                        $initial_condition_1++;
                    }
                }
            }
        });

        foreach ($data as $t_key => $ticket) {

            $initials = 0;
            $initial_condition_2 = 0;
            $rebills = 0;
            $cycle_1_condition_1 = 0;
            $cycle_1_condition_2 = 0;
            $cycle_1_per = 0;
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
            // dd($ticket->month);
            /* 
                !remove time limit that is added to remove the error in products in the array
             */
            $start_of_month = $ticket->year . '-' . $ticket->month . '-01';
            $end_of_month = Carbon::parse($start_of_month)->endOfMonth()->format('Y-m-d');
            // dd($end_of_month);

            Order::where(['prepaid_match' => 'NO', 'is_test_cc' => 0])->whereDate('acquisition_date', '>=', $start_of_month)->whereDate('acquisition_date', '<=', $end_of_month)->chunk(100, function ($orders) {
                foreach ($orders as $order) {
                    $order->products = unserialize($order->products);
                    if (isset($order->products) && $order->products != null) {
                        /*
                            !Calculation of initials, rebills, cycle_2, cycle_3 plus in golden ticket with order-status == "Declined"
                         */
                        if (Str::contains($order->products[0]->name, ['(C)']) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                            $initial_condition_2++;
                        }
                        if (Str::contains($order->products[0]->name, ['(R']) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                            $rebills++;
                        }
                        if (isset($order->products[0]->offer) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                            if (Str::contains($order->products[0]->name, ['(R'])) {
                                $cycle_1_condition_1++;
                            } else if (Str::contains($order->products[0]->name, ['(I'])) {
                                $cycle_1_condition_2++;
                            }
                        }
                        if ($order->is_chargeback == 1 && isset($order->products[0]->offer) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                            $CBs++;
                        }
                        /*
                            !Calculation of Revenue
                         */
                        if ($order->order_total != null && isset($order->products[0]->offer) && $order->products[0]->offer->name == 'Gizmo') {
                            if ($order->order_status == 7) {
                                $revenue += $order->order_total;
                                if ($order->is_chargeback == 1) {
                                    $CB_currency++;
                                }
                            }
                            if ($order->order_status == 6) {
                            /* 
                                !calculation of refund with order-status == "void/refunded"
                                 */
                                $refund += $order->order_total;
                            }
                        }
                    }
                }
            });
            $initials = $initial_condition_1 + $initial_condition_2;
            $net = $revenue + $refund + $CBs + $fulfillment + $processing + $cpa;
            $fulfillment = $initials * -18;
            if ($rebills != 0) {
                $avg_ticket = $revenue / $rebills;
            }
            if ($cycle_1_condition_2 != 0) {
                $cycle_1_per = $cycle_1_condition_1 / $cycle_1_condition_2;
                $clv = $net / $initials;
            }
            if ($revenue != 0) {
                $refund_rate = $refund / $revenue;
                $CB_per = $CBs / $revenue;
                $processing = -0.2 * $revenue;
            }

            $ticket->initials = $initials;
            $ticket->rebills = $rebills;
            $ticket->cycle_1_per = $cycle_1_per;
            $ticket->avg_day = $avg_day; //yet to calculate
            $ticket->avg_ticket = $avg_ticket;
            $ticket->revenue = $revenue;
            $ticket->refund = (($refund > 0) ? -$refund : $refund);
            $ticket->filled_per = $filled_per; // yet to calculate
            $ticket->refund_rate = $refund_rate;
            $ticket->CBs = $CBs;
            $ticket->CB_per = $CB_per;
            $ticket->CB_currency = $CB_currency;
            $ticket->fulfillment = $fulfillment;
            $ticket->processing = $processing;
            // $ticket->cpa = ??
            // $ticket->cpa_avg = $cpa / $initials??
            $ticket->net = $net;
            $ticket->clv = $clv;
            $ticket->save();
        }
        // die;
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
        // $data = TicketMonthly::all();
        // dd($data);
        $date_today = Carbon::now()->format('Y-m-d');
        $current_month = Carbon::now()->format('F');
        $current_year = Carbon::now()->format('Y');
        // $end_of_this_week = Carbon::now()->endOfWeek()->format('Y-m-d');

        $latest = TicketMonthly::orderBy('id', 'desc')->first();
        // dd($latest);
        if (isset($latest)) {
            if ($latest->month != $current_month) {
                $model = new TicketMonthly();
                $model->month = $current_month;
                $model->year = $current_year;
                $model->save();
            }
        }
        // dd('die');
        /* 
            ?implementation of the formulas
         */
        $initial_condition_1 = 0;
        Order::where(['prepaid_match' => 'NO', 'is_test_cc' => 0, 'order_status' => 7])->whereDate('acquisition_date', '>=', '2021-07-01 00:00:00')->whereDate('acquisition_date', '<=', '2022-01-31 00:00:00')->chunk(1000, function ($orders) use ($initial_condition_1) {
            // dd($orders);
            foreach ($orders as $key => $order) {
                $order->products = isset($order->products) && $order->products != [] && $order->products != '' ? unserialize($order->products) : null;
                    // var_dump($order);
                    // $order->totals_breakdown = unserialize($order->totals_breakdown);
                    // dd('die');
                if (isset($order->products) == true && $order->products != null) {
                    if (Str::contains($order->products[0]->name, ['(I)']) && $order->products[0]->offer->name == 'Gizmo') {
                        $initial_condition_1++;
                    }
                }
            }
        });

        $initials = 0;
        $initial_condition_2 = 0;
        $rebills = 0;
        $cycle_1_condition_1 = 0;
        $cycle_1_condition_2 = 0;
        $cycle_1_per = 0;
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
        // dd($ticket->month);
        /* 
            !remove time limit that is added to remove the error in products in the array
         */
        $start_of_month = $latest->year . '-' . $latest->month . '-01';
        $end_of_month = Carbon::parse($start_of_month)->endOfMonth()->format('Y-m-d');
        // dd($end_of_month);

        Order::where(['prepaid_match' => 'NO', 'is_test_cc' => 0])->whereDate('acquisition_date', '>=', $start_of_month)->whereDate('acquisition_date', '<=', $end_of_month)->chunk(100, function ($orders) {
            foreach ($orders as $order) {
                $order->products = unserialize($order->products);
                if (isset($order->products) && $order->products != null) {
                    /*
                        !Calculation of initials, rebills, cycle_2, cycle_3 plus in golden ticket with order-status == "Declined"
                     */
                    if (Str::contains($order->products[0]->name, ['(C)']) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                        $initial_condition_2++;
                    }
                    if (Str::contains($order->products[0]->name, ['(R']) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                        $rebills++;
                    }
                    if (isset($order->products[0]->offer) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                        if (Str::contains($order->products[0]->name, ['(R'])) {
                            $cycle_1_condition_1++;
                        } else if (Str::contains($order->products[0]->name, ['(I'])) {
                            $cycle_1_condition_2++;
                        }
                    }
                    if ($order->is_chargeback == 1 && isset($order->products[0]->offer) && $order->products[0]->offer->name == 'Gizmo' && $order->order_status == 7) {
                        $CBs++;
                    }
                    /*
                        !Calculation of Revenue
                     */
                    if ($order->order_total != null && isset($order->products[0]->offer) && $order->products[0]->offer->name == 'Gizmo') {
                        if ($order->order_status == 7) {
                            $revenue += $order->order_total;
                            if ($order->is_chargeback == 1) {
                                $CB_currency++;
                            }
                        }
                        if ($order->order_status == 6) {
                        /* 
                            !calculation of refund with order-status == "void/refunded"
                             */
                            $refund += $order->order_total;
                        }
                    }
                }
            }
        });
        $initials = $initial_condition_1 + $initial_condition_2;
        $net = $revenue + $refund + $CBs + $fulfillment + $processing + $cpa;
        $fulfillment = $initials * -18;
        if ($rebills != 0) {
            $avg_ticket = $revenue / $rebills;
        }
        if ($cycle_1_condition_2 != 0) {
            $cycle_1_per = $cycle_1_condition_1 / $cycle_1_condition_2;
            $clv = $net / $initials;
        }
        if ($revenue != 0) {
            $refund_rate = $refund / $revenue;
            $CB_per = $CBs / $revenue;
            $processing = -0.2 * $revenue;
        }

        $latest->initials = $initials;
        $latest->rebills = $rebills;
        $latest->cycle_1_per = $cycle_1_per;
        $latest->avg_day = $avg_day; //yet to calculate
        $latest->avg_ticket = $avg_ticket;
        $latest->revenue = $revenue;
        $latest->refund = (($refund > 0) ? -$refund : $refund);
        $latest->filled_per = $filled_per; // yet to calculate
        $latest->refund_rate = $refund_rate;
        $latest->CBs = $CBs;
        $latest->CB_per = $CB_per;
        $latest->CB_currency = $CB_currency;
        $latest->fulfillment = $fulfillment;
        $latest->processing = $processing;
        // $latest->cpa = ??
        // $latest->cpa_avg = $cpa / $initials??
        $latest->net = $net;
        $latest->clv = $clv;
        $latest->save();
        
        // die;
        $data = TicketMonthly::orderBy('id', 'desc')->take(10)->get();
        return response()->json(['status' => true, 'data' => $data]);
    }
}
