<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\TicketDaily;
use Illuminate\Support\Str;
use DB;
use Carbon\Carbon;

class TicketDailyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // DB::table('sticket_daily')->truncate();
        // return ;
        $db_dates = TicketDaily::pluck('date')->toArray();
        $date_today = Carbon::now()->format('Y-m-d');

        
            // ?previous two month records insert query 
/*         for ($i = 127; $i > 0; $i--) {
            $model = new TicketDaily();
            $date_of_the_day = Carbon::now()->subDays($i)->format('Y-m-d');
            if (in_array($date_of_the_day, $db_dates) == false) {
                $model->date = Carbon::now()->subDays($i)->format('Y-m-d');
                $model->save();
            }
        }
        return; */

        /* 
            ?check entry inserted for today 
         */
        $last = TicketDaily::latest()->first();
        if (isset($last)) {
            if ($last->date != $date_today) {
                $today = new TicketDaily();
                $today->date = Carbon::now();
                $today->save();
            }
        }

        $latest = TicketDaily::latest()->first();

        $start_day = Carbon::parse($latest->date)->startOfDay();
        $end_day = Carbon::parse($latest->date)->endOfDay();

        /* 
            ?formula implementation on the date today entry
         */
        $initials = 0;
        $decline = 0;
        $decline_per = 0;
        $scrub_per = 0;
        $EOT_declines = 0;
        $EOT_approved = 0;
        $EOT_per = 0;

        $orders = DB::table('orders')->where(['orders.prepaid_match' => 'No', 'orders.is_test_cc' => 0, 'orders.campaign_id' => 2])
            ->where('orders.time_stamp', '>=', $start_day)
            ->where('orders.time_stamp', '<=', $end_day)
            ->select('orders.order_id', 'orders.time_stamp', 'orders.acquisition_month', 'orders.acquisition_year');
        // dd($orders->get());

        $join = $orders->join('order_products', 'orders.order_id', 'order_products.order_id')
            ->select('order_products.*')
            ->groupBy('orders.order_id');

        $decline = $orders->where(['orders.order_status' => 7])->get()->count();
        // dd($join->pluck('order_products')->toArray());
        $initials = $join->where(['order_products.offer_name' => 'Golden Ticket Offer'])->where('order_products.name', 'LIKE', '%(c)%')->get()->count();
        // dd($initials);
        $EOT_approved = $join->where(['order_products.offer_name' => 'EssentialSweep'])->where('order_products.name', 'LIKE', '%(CR1)%')->get()->count();

        if ($initials != 0) {
            $decline_per = $decline / ($initials + $decline);
            $EOT_per = $EOT_approved / $initials;
        }

        $latest->initials = $initials;
        $latest->decline = $decline;
        $latest->decline_per = $decline_per;
        $latest->scrub_per = $scrub_per;
        $latest->EOT_declines = $EOT_declines;
        $latest->EOT_approved = $EOT_approved;
        $latest->EOT_per = $EOT_per;
        $latest->save();

        $data = TicketDaily::orderBy('id', 'desc')->get();
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
    public function refresh_daily()
    {
        // return response()->json(['status' => true, 'data' => []]);


        $db_dates = TicketDaily::pluck('date')->toArray();
        $date_today = Carbon::now()->format('Y-m-d');
        /* 
            ?check entry inserted for today 
        
        $last = TicketDaily::latest()->first();
        if (isset($last)) {
            if ($last->date != $date_today) {
                $today = new TicketDaily();
                $today->date = Carbon::now();
                $today->save();
            }
        } */

        $latest = TicketDaily::latest()->first();
        /* 
            ?formula implementation on the date today entry
         */
        $initials = 0;
        $decline = 0;
        $decline_per = 0;
        $scrub_per = 0;
        $EOT_declines = 0;
        $EOT_approved = 0;
        $EOT_per = 0;

        $latest = TicketDaily::latest()->first();
        $start_day = Carbon::parse($latest->date)->startOfDay();
        $end_day = Carbon::parse($latest->date)->endOfDay();

        /* 
            ?formula implementation on the date today entry
         */
        $initials = 0;
        $decline = 0;
        $decline_per = 0;
        $scrub_per = 0;
        $EOT_declines = 0;
        $EOT_approved = 0;
        $EOT_per = 0;

        $orders = DB::table('orders')->where(['orders.prepaid_match' => 'No', 'orders.is_test_cc' => 0, 'orders.campaign_id' => 2])
            ->where('orders.time_stamp', '>=', $start_day)
            ->where('orders.time_stamp', '<=', $end_day)
            ->select('orders.order_id', 'orders.time_stamp', 'orders.acquisition_month', 'orders.acquisition_year');
        // dd($orders->get());

        $join = $orders->join('order_products', 'orders.order_id', 'order_products.order_id')
            ->select('order_products.*')
            ->groupBy('orders.order_id');

        $decline = $orders->where(['orders.order_status' => 7])->get()->count();
        // dd($decline);
        $initials = $join->where(['order_products.offer_name' => 'Golden Ticket Offer'])->where('order_products.name', 'LIKE', '%(c)%')->get()->count();
        // dd($initials);
        $EOT_approved = $join->where(['order_products.offer_name' => 'EssentialSweep'])->where('order_products.name', 'LIKE', '%(CR1)%')->get()->count();

        if ($initials != 0) {
            $decline_per = $decline / ($initials + $decline);
            $EOT_per = $EOT_approved / $initials;
        }

        $latest->initials = $initials;
        $latest->decline = $decline;
        $latest->decline_per = $decline_per;
        $latest->scrub_per = $scrub_per;
        $latest->EOT_declines = $EOT_declines;
        $latest->EOT_approved = $EOT_approved;
        $latest->EOT_per = $EOT_per;
        $latest->save();

        $data = TicketDaily::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $data]);
    }
    public function refresh_all_daily_tickets()
    {
        $db_dates = TicketDaily::pluck('date')->toArray();
        $date_today = Carbon::now()->format('Y-m-d');
        /* 
            ?check entry inserted for today 
         */
        $last = TicketDaily::latest()->first();
        if (isset($last)) {
            if ($last->date != $date_today) {
                $today = new TicketDaily();
                $today->date = Carbon::now();
                $today->save();
            }
        }

        $data = TicketDaily::orderBy('id', 'desc')->get();
        /* 
            ?formula implementation on the date today entry
         */

        foreach ($data as $day) {
            // dd($day);
            $initials = 0;
            $decline = 0;
            $decline_per = 0;
            $scrub_per = 0;
            $EOT_declines = 0;
            $EOT_approved = 0;
            $EOT_per = 0;
            // $start_day = '2022-04-13 00:00:00';
            // $end_day = '2022-04-13 23:59:59';
            // $start_day = Carbon::parse('2022-02-03')->startOfDay();
            // $end_day = Carbon::parse('2022-02-03')->endOfDay();
            $start_day = Carbon::parse($day->date)->startOfDay();
            $end_day = Carbon::parse($day->date)->endOfDay();
            // dd($end_day);


            $initials = Order::where(['prepaid_match' => 'No', 'is_test_cc' => 0, 'order_status' => 2, 'campaign_id' => 2])
                ->where('time_stamp', '>=', $start_day)
                ->where('time_stamp', '<=', $end_day)
                ->select('order_id')->get()->count();
            // dd($initials);

            $declines = DB::table('orders')->where(['orders.prepaid_match' => 'No', 'orders.is_test_cc' => 0, 'orders.order_status' => 7, 'orders.campaign_id' => 2])
                ->where('orders.time_stamp', '>=', $start_day)
                ->where('orders.time_stamp', '<=', $end_day)
                ->select('orders.order_id', 'orders.time_stamp', 'orders.acquisition_month', 'orders.acquisition_year', 'orders.order_status')
                ->get()->count();
            // dd($orders->where(['orders.order_status'=>2])->get()->count());

            // $join = $orders->join('order_products', 'orders.order_id', 'order_products.order_id')
            //     ->select('order_products.*')
            //     ->groupBy('orders.order_id');
            
            // dd($join->get()->count());

            // $initials = $join->where(['orders.order_status' => 2])->where(['order_products.offer_name' => 'Golden Ticket Offer'])->where('order_products.name', 'LIKE', '%(c)%')->get()->count();
            // $initials = $orders->where(['orders.billing_cycle' => 0])->get()->count();
            // $initials = $orders->where(['orders.order_status' => 2])->get()->count();
            // dd($declines);
            $EOT_approved = $join->where(['order_products.offer_name' => 'EssentialSweep'])->where('order_products.name', 'LIKE', '%(CR1)%')->get()->count();


            if ($initials != 0) {
                $decline_per = ($decline / $initials) * 100;
                $EOT_per = $EOT_approved / $initials;
            }

            $day->initials = $initials;
            $day->decline = $decline;
            $day->decline_per = $decline_per;
            $day->scrub_per = $scrub_per;
            $day->EOT_declines = $EOT_declines;
            $day->EOT_approved = $EOT_approved;
            $day->EOT_per = $EOT_per;
            $day->save();
        }
        //  dd($day);
        $data = TicketDaily::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'data' => $data]);
    }
}
