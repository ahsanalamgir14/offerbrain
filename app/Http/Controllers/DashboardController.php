<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['customers'] = DB::table('customers')->where('user_id', Auth::id())->count();
        $data['orders'] = DB::table('orders')->where(['user_id' => Auth::id(), 'prepaid_match' => 'No', 'is_test_cc' => 0])
            ->where('order_status', 2)->whereMonth('created_at', Carbon::now()->month)->count();

        $data['decline_orders'] = DB::table('orders')->where(['user_id' => Auth::id(), 'prepaid_match' => 'No', 'is_test_cc' => 0])
            ->whereMonth('created_at', Carbon::now()->month)->where('order_status', 7)
            ->count();
        $data['refund_orders'] = DB::table('orders')->where(['user_id' => Auth::id(), 'prepaid_match' => 'No', 'is_test_cc' => 0])
            ->whereMonth('created_at', Carbon::now()->month)->where('order_status', 6)
            ->count();
        $data['chargeback_orders'] = DB::table('orders')->where(['user_id' => Auth::id(), 'prepaid_match' => 'No', 'is_test_cc' => 0])
            ->whereMonth('created_at', Carbon::now()->month)->where('is_chargeback', 1)
            ->count();
        return response()->json(['status' => 200, 'data' => $data]);
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
    public function user_data(Request $request)
    {
        return response()->json(['status' => true, 'name' => $request->user()->name, 'email' => $request->user()->email]);
    }

    public function getCustomersForGraph(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)->startOfDay();
        $end_date = Carbon::parse($request->end_date)->endOfDay();
        $get_date_series = $this->get_date_series($start_date, $end_date);
        $days = count($get_date_series);
        $label = [];

        $customer_query = DB::table('customers')->select('id', 'created_at')->where('user_id', 1)
            ->where('created_at', '>=', $start_date)
            ->where('created_at', '<=', $end_date)->get();
        $order_query = DB::table('orders')->select('id', 'time_stamp')->where('user_id', 1)
            ->where('time_stamp', '>=', $start_date)
            ->where('time_stamp', '<=', $end_date)->get();

        if ($days >= 0 && $days <= 1) {
            $users = $customer_query->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('h');
            });
            $orders = $order_query->groupBy(function ($date) {
                return Carbon::parse($date->time_stamp)->format('h');
            });
            $label = ['1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM', '12AM', '13AM', '14AM', '15AM', '16AM', '17AM', '18AM', '19AM', '20AM', '21AM', '22AM', '23AM', '00PM'];
            $count = 24;
        } else if ($days > 1 && $days <= 14) {
            $users = $customer_query->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('d');
            });
            $orders = $order_query->groupBy(function ($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });

            $label = $get_date_series;
            $count = $days;
        } else if ($days > 14 && $days < 30) {
            $users = $customer_query->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('d');
            });
            $orders = $order_query->groupBy(function ($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });
            $label = $get_date_series;
            $count = $days;
        } else if ($days >= 29 && $days <= 31) {
            $users = $customer_query->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('d');
            });
            $orders = $order_query->groupBy(function ($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });
            $label = $get_date_series;
            $count = $days;
        } else if ($days > 31 && $days < 365) {
            $users = $customer_query->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });
            $orders = $order_query->groupBy(function ($date) {
                return Carbon::parse($date->time_stamp)->format('m');
            });
            $label = ['JAN', 'FEB', 'MARCH', 'APRIL', 'MAY', 'JUN', 'JUL', 'AUG', 'SEPT', 'OCT', 'NOV', 'DEC'];
            $count = 12;
        }

        $usermcount = [];
        $userArr = [];
        $ordermcount = [];
        $orderArr = [];

        foreach ($users as $key => $value) {
            $usermcount[(int)$key] = count($value);
        }
        foreach ($orders as $key => $value) {
            $ordermcount[(int)$key] = count($value);
        }
        for ($i = 0; $i < $count; $i++) {
            if (!empty($usermcount[$i])) {
                $userArr[$i] = $usermcount[$i];
            } else {
                $userArr[$i] = 0;
            }
            if (!empty($ordermcount[$i])) {
                $orderArr[$i] = $ordermcount[$i];
            } else {
                $orderArr[$i] = 0;
            }
        }
        return response()->json(['status' => true, 'customer' => $userArr, 'order' => $orderArr, 'label' => $label]);
    }
    public function getOrdersForGraph(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)->startOfDay();
        $end_date = Carbon::parse($request->end_date)->endOfDay();
        $get_date_series = $this->get_date_series($start_date, $end_date);
        $days = count($get_date_series);
        $label = [];

        $query = DB::table('orders')
            ->where('time_stamp', '>=', $start_date)
            ->where('time_stamp', '<=', $end_date)
            ->select(
                'id',
                'time_stamp',
                'order_status',
                'is_chargeback',
                'is_refund',
                DB::raw('(CASE WHEN order_status = 7 THEN 1 ELSE 0 END) AS decline'),
                DB::raw('(CASE WHEN is_chargeback = 1 THEN 1 ELSE 0 END) AS chargeback'),
                DB::raw('(CASE WHEN is_refund = "yes" THEN 1 ELSE 0 END) AS refund')
            )
            ->where('user_id', 1)->get();

        if ($days >= 0 && $days <= 1) {
            $orders = $query->groupBy(function ($date) {
                return Carbon::parse($date->time_stamp)->format('h');
            });
            $label = ['1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM', '12AM', '13AM', '14AM', '15AM', '16AM', '17AM', '18AM', '19AM', '20AM', '21AM', '22AM', '23AM', '00PM'];
            $count = 24;
        } else if ($days > 1 && $days <= 14) {
            $orders = $query->groupBy(function ($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });
            $label = $get_date_series;
            $count = $days;
        } else if ($days > 14 && $days < 30) {
            $orders = $query->groupBy(function ($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });
            $label = $get_date_series;
            $count = $days;
        } else if ($days >= 29 && $days <= 31) {
            $orders = $query->groupBy(function ($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });
            $label = $get_date_series;
            $count = $days;
        } else if ($days > 31 && $days < 365) {
            $orders = $query->groupBy(function ($date) {
                return Carbon::parse($date->time_stamp)->format('m');
            });
            $label = ['JAN', 'FEB', 'MARCH', 'APRIL', 'MAY', 'JUN', 'JUL', 'AUG', 'SEPT', 'OCT', 'NOV', 'DEC'];
            $count = 12;
        }

        $declinecount = [];
        $chargebackcount = [];
        $refundcount = [];
        $declineArr = [];
        $chargebackArr = [];
        $refundArr = [];

        foreach ($orders as $key => $order) {
            $decline = 0;
            $chargeback = 0;
            $refund = 0;
            foreach ($order as $value) {
                if ($value->decline == 1) {
                    $decline = $decline + 1;
                }
                if ($value->chargeback == 1) {
                    $chargeback = $chargeback + 1;
                }
                if ($value->refund == 1) {
                    $refund = $refund + 1;
                }
            }
            $declinecount[(int)$key] = $decline;
            $chargebackcount[(int)$key] = $chargeback;
            $refundcount[(int)$key] = $refund;
        }

        for ($i = 0; $i < $count; $i++) {
            if (!empty($declinecount[$i])) {
                $declineArr[$i] = $declinecount[$i];
            } else {
                $declineArr[$i] = 0;
            }
            if (!empty($chargebackcount[$i])) {
                $chargebackArr[$i] = $chargebackcount[$i];
            } else {
                $chargebackArr[$i] = 0;
            }
            if (!empty($refundcount[$i])) {
                $refundArr[$i] = $refundcount[$i];
            } else {
                $refundArr[$i] = 0;
            }
        }
        return response()->json(['status' => true, 'declineArr' => $declineArr, 'chargebackArr' => $chargebackArr, 'refundArr' => $refundArr, 'label' => $label]);
    }
    public function get_date_series($start_date, $end_date)
    {
        $dates = array();
        $current = strtotime($start_date);
        $date2 = strtotime($end_date);
        $stepVal = '+1 day';
        while ($current <= $date2) {
            $dates[] = date('d-M', $current);
            $current = strtotime($stepVal, $current);
        }
        return $dates;
    }
}
