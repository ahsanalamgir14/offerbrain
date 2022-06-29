<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class CustomerController extends Controller
{
    public function index1(Request $request)
    {
        $pageno = isset($request->page) ? $request->page : 1;
        $no_of_records_per_page = isset($request->per_page) ? $request->per_page : 25;
        $query = DB::table('customers')->select('id', 'email', 'first_name', 'last_name', 'phone', 'addresses', 'deleted_at');
        $total_rows = $query->count('id');

        if ($request->search != '') {
            $query->Where('email', 'like', '%' . $request->search . '%')
                ->orWhere('first_name', 'like', '%' . $request->search . '%')
                ->orWhere('last_name', 'like', '%' . $request->search . '%');
        }
            // dd($pageno);
        $data = $query->orderBy('id', 'asc')->SimplePaginate($no_of_records_per_page); //data is ok 
        // $total_rows = $query->count('id');
        $total_pages = ceil($total_rows / $data->perPage());
        $pag['count'] = $total_rows;
        $pag['total_pages'] = $total_pages;
        $pag['pageno'] = $pageno;
        $pag['rows_per_page'] = $no_of_records_per_page;
        return response()->json(['status' => true, 'data' => $data, 'pag' => $pag]);
    }


    public function index(Request $request)
    {
        // return Auth::id();
        $is_count = $request->customer_id;
        DB::statement("SET SQL_MODE=''");
        DB::enableQueryLog();
        $pageno = isset($request->page) ? $request->page : 1;
        $no_of_records_per_page = isset($request->per_page) ? $request->per_page : 25;

        if ($is_count == 0 && $is_count != 1 && $is_count != '') {
            ini_set('memory_limit', '512M');
            set_time_limit(0);
            // $query = Customer::doesnthave('customers')
            $query = Customer::where('user_id', Auth::id())
                ->select('id', 'user_id', 'email', 'first_name', 'last_name', 'phone', 'addresses', 'deleted_at')
                ->addSelect(DB::raw('0 as orders_count'));
                $total_rows = $query->count('customers.id');
            } else {
                $query = DB::table('customers')
                ->select('customers.id', 'customers.user_id', 'customers.email', 'customers.first_name', 'customers.last_name', 'customers.phone', 'customers.addresses', 'customers.deleted_at')
                ->where('customers.user_id', Auth::id())
                ->join('orders', function ($join) {
                    $join->on('orders.customer_id', '=', 'customers.id');
                })
                ->addSelect(DB::raw('COUNT(orders.id) as orders_count'))
                ->groupBy('customers.id');
            $total_rows = $query->get()->count('customers.id');
        }

        if ($request->search != '') {
            $query->Where('customers.email', 'like', '%' . $request->search . '%')
                ->orWhere('customers.first_name', 'like', '%' . $request->search . '%')
                ->orWhere('customers.last_name', 'like', '%' . $request->search . '%');
        }
        $data = $query->orderBy('customers.id', 'desc')->SimplePaginate($no_of_records_per_page);

        $total_pages = ceil($total_rows / $data->perPage());
        $pag['count'] = $total_rows;
        $pag['total_pages'] = $total_pages;
        $pag['pageno'] = $pageno;
        $pag['rows_per_page'] = $no_of_records_per_page;
        // dd(DB::getQueryLog());
        return response()->json(['status' => true, 'data' => $data, 'pag' => $pag]);
    }

    public function get_customer_detail(Request $request)
    {
        $customerData = Customer::findOrFail($request->id);
        $customerAddress = json_decode($customerData->addresses);
        return response()->json(['status' => true, 'data' => $customerData, 'address_data' => $customerAddress]);
    }
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
     * @param  \App\Models\customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function destroy_customers(Request $request)
    {
        $ids = $request->all();
        $total_records = count($ids);
        DB::table('customers')->whereIn('id', $ids)->delete();
        if ($total_records <= 1) {
            return response()->json(['status' => true, 'message' => '<b>1</b> Customer Deleted Successfully']);
        } else {
            return response()->json(['status' => true, 'message' => $total_records . ' Customers Deleted Successfully']);
        }
    }

    public static function refresh_customers()
    {
        // ini_set('memory_limit', '512M');
        // set_time_limit(0);
        $setting = Setting::where('key', 'customer_last_page')->first();
        $users = User::orderBy('id','desc')->get();
        foreach($users as $user){
            $created = 0;
            $updated = 0;
            $db_customers = Customer::pluck('id')->toArray();
            $username = $user->sticky_api_username;
            $password = Crypt::decrypt($user->sticky_api_key);
            $url = $user->sticky_url.'/api/v2/contacts';
            $previousPage = $setting->value;

            $api_data = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $previousPage]);
            $response['customers'] = $api_data['data'];
            $last_page = $api_data['last_page'];

            if ($response['customers']) {
                foreach ($response['customers'] as $result) {

                    $result['customer_id'] = $result['id'];
                    $result['custom_fields'] = json_encode($result['custom_fields']);
                    $result['addresses'] = json_encode($result['addresses']);
                    $result['notes'] = json_encode($result['notes']);
                    $result['user_id'] = $user->id;

                    if (in_array($result['id'], $db_customers)) {
                        $updated++;
                        $customer = Customer::where(['customer_id' => $result['id']])->first();
                        $customer->update($result);
                    } else {
                        $created++;
                        Customer::create($result);
                    }
                }
                if ($last_page > $previousPage) {
                    $previousPage++;
                    for ($previousPage; $previousPage <= $last_page; $previousPage++) {

                        $response['customers'] = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $previousPage])['data'];

                        foreach ($response['customers'] as $result) {

                            $result['customer_id'] = $result['id'];
                            $result['custom_fields'] = json_encode($result['custom_fields']);
                            $result['addresses'] = json_encode($result['addresses']);
                            $result['notes'] = json_encode($result['notes']);
                            $result['user_id'] = $user->id;

                            if (in_array($result['id'], $db_customers)) {
                                $updated++;
                                $customer = Customer::where(['customer_id' => $result['id']])->first();
                                $customer->update($result);
                            } else {
                                $created++;
                                Customer::create($result);
                            }
                            $response = null;
                        }
                        Setting::where('key', 'customer_last_page')->update(['value' => $previousPage]);
                    }
                }
            }
        }
        return response()->json(['status' => true, 'new customers created' => $created, 'Updated customers' => $updated]);
    }

    public static function refresh_user_customers(Request $request)
    {
        // return Auth::id();
        $created = 0;
        $updated = 0;
        $customer_last_page = Setting::where(['key' => 'customer_last_page', 'user_id' => Auth::id()])->first();
        $db_customers = Customer::where(['user_id' => Auth::id()])->pluck('customer_id')->toArray();
        $user = User::find($request->user()->id);
        $username = $user->sticky_api_username;
        $password = Crypt::decrypt($user->sticky_api_key);
        $url = $user->sticky_url . '/api/v2/contacts';
        if ($customer_last_page) {
            $page = $customer_last_page->value;
        } else {
            $page = 1;
        }

        $api_data = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page]);
        $response['customers'] = $api_data['data'];
        $last_page = $api_data['last_page'];    

        if ($response['customers']) {
            foreach ($response['customers'] as $result) {

                $result['customer_id'] = $result['id'];
                $result['user_id'] = Auth::id();
                $result['custom_fields'] = json_encode($result['custom_fields']);
                $result['addresses'] = json_encode($result['addresses']);
                $result['notes'] = json_encode($result['notes']);

                if (in_array($result['id'], $db_customers)) {
                    $updated++;
                    $customer = Customer::where(['customer_id' => $result['id'], 'user_id' => Auth::id()])->first();
                    $customer->update($result);
                } else {
                    $created++;
                    Customer::create($result);
                }
            }
            if ($last_page > $page) {
                // $page++;
                for ($page; $page <= $last_page; $page++) {

                    $response['customers'] = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page])['data'];
                    foreach ($response['customers'] as $result) {
                        $result['customer_id'] = $result['id'];
                        $result['user_id'] = Auth::id();
                        $result['custom_fields'] = json_encode($result['custom_fields']);
                        $result['addresses'] = json_encode($result['addresses']);
                        $result['notes'] = json_encode($result['notes']);

                        if (in_array($result['id'], $db_customers)) {
                            $updated++;
                            $customer = Customer::where(['customer_id' => $result['id'], 'user_id' => Auth::id()])->first();
                            $customer->update($result);
                        } else {
                            $created++;
                            Customer::create($result);
                        }
                        $response = null;
                    }
                    $setting = Setting::where(['key'=> 'customer_last_page', 'user_id' => Auth::id()])->first();
                    if($setting){
                        $setting->update(['value' => $page, 'user_id' => Auth::id()]);
                    }else{
                        Setting::create(['key'=> 'customer_last_page', 'value' => $page, 'user_id' => Auth::id()]);
                    }
                }
            }
        }
        return response()->json(['status' => true, 'user_id' => Auth::id(), 'new_customers' => $created, 'updated_customers' => $updated]);
    }
    public function getCustomersForGraph(Request $request){
        $start_date = Carbon::parse($request->start_date)->startOfDay();
        $end_date = Carbon::parse($request->end_date)->endOfDay();
        $get_date_series = $this->get_date_series($start_date, $end_date);
        $days = count($get_date_series);
        $label = [];

        $customer_query = DB::table('customers')->select('id','created_at')->where('user_id',1)
        ->where('created_at', '>=', $start_date)
        ->where('created_at', '<=', $end_date)->get();
        $order_query = DB::table('orders')->select('id','time_stamp')->where('user_id',1)
        ->where('time_stamp', '>=', $start_date)
        ->where('time_stamp', '<=', $end_date)->get();
        
        if($days == 0 && $days <= 1){
            $users = $customer_query->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('h');
            });
            $orders = $order_query->groupBy(function($date) {
                return Carbon::parse($date->time_stamp)->format('h');
            });
            $label = ['1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM','8PM' ,'9PM', '10PM', '11PM', '12AM', '13AM', '14AM', '15AM', '16AM', '17AM', '18AM', '19AM', '20AM', '21AM', '22AM', '23AM', '00PM'];
            $count = 24;
        } else if($days >= 7 && $days <= 14){
            $users = $customer_query->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('d');
            });
            $orders = $order_query->groupBy(function($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });
            
            $label = $get_date_series;
            $count = $days;
        } else if($days > 14 && $days < 30){
            $users = $customer_query->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('d');
            });
            $orders = $order_query->groupBy(function($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });
            $label = $get_date_series;
            $count = $days;
        } else if($days >= 29 && $days <= 31){
            $users = $customer_query->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('d');
            });
            $orders = $order_query->groupBy(function($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });
            $label = $get_date_series;
            $count = $days;
        } else if($days > 31 && $days < 365){
            $users = $customer_query->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('m');
            });
            $orders = $order_query->groupBy(function($date) {
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
        for($i = 0; $i < $count; $i++){
            if(!empty($usermcount[$i])){
                $userArr[$i] = $usermcount[$i]; 
            }else{
                $userArr[$i] = 0;
            }
            if(!empty($ordermcount[$i])){
                $orderArr[$i] = $ordermcount[$i]; 
            }else{
                $orderArr[$i] = 0;
            }
        }
        return response()->json(['status' => true, 'customer' => $userArr, 'order' => $orderArr, 'label' => $label]);
    }
    public function getOrdersForGraph(Request $request){
        $start_date = Carbon::parse($request->start_date)->startOfDay();
        $end_date = Carbon::parse($request->end_date)->endOfDay();
        $get_date_series = $this->get_date_series($start_date, $end_date);
        $days = count($get_date_series);
        $label = [];
        
        $query = DB::table('orders')
        ->where('time_stamp', '>=', $start_date)
        ->where('time_stamp', '<=', $end_date)
        ->select('id','time_stamp','order_status','is_chargeback','is_refund',
            DB::raw('(CASE WHEN order_status = 7 THEN 1 ELSE 0 END) AS decline'),
            DB::raw('(CASE WHEN is_chargeback = 1 THEN 1 ELSE 0 END) AS chargeback'),
            DB::raw('(CASE WHEN is_refund = 1 THEN 1 ELSE 0 END) AS refund'))
        ->where('user_id',1)->get();

        if($days >= 0 && $days <= 1){
            $orders = $query->groupBy(function($date) {
                return Carbon::parse($date->time_stamp)->format('h');
            });
            $label = ['1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM','8PM' ,'9PM', '10PM', '11PM', '12AM', '13AM', '14AM', '15AM', '16AM', '17AM', '18AM', '19AM', '20AM', '21AM', '22AM', '23AM', '00PM'];
            $count = 24;
        } else if($days >= 7 && $days <= 14){
            $orders = $query->groupBy(function($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });
            $label = $get_date_series;
            $count = $days;
        } else if($days > 14 && $days < 30){
            $orders = $query->groupBy(function($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });
            $label = $get_date_series;
            $count = $days;
        } else if($days >= 29 && $days <= 31){
            $orders = $query->groupBy(function($date) {
                return Carbon::parse($date->time_stamp)->format('d');
            });
            $label = $get_date_series;
            $count = $days;
        } else if($days > 31 && $days < 365){
            $orders = $query->groupBy(function($date) {
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
            foreach($order as $value){
                if($value->decline == 1){
                    $decline = $decline + 1;
                }
                if($value->chargeback == 1){
                    $chargeback = $chargeback + 1;
                }
                if($value->refund == 1){
                    $refund = $refund + 1;
                }
            }
            $declinecount[(int)$key] = $decline;
            $chargebackcount[(int)$key] = $chargeback;
            $refundcount[(int)$key] = $refund;
        }
        
        for($i = 0; $i < $count; $i++){
            if(!empty($declinecount[$i])){
                $declineArr[$i] = $declinecount[$i]; 
            }else{
                $declineArr[$i] = 0;
            }
            if(!empty($chargebackcount[$i])){
                $chargebackArr[$i] = $chargebackcount[$i]; 
            }else{
                $chargebackArr[$i] = 0;
            }
            if(!empty($refundcount[$i])){
                $refundArr[$i] = $refundcount[$i]; 
            }else{
                $refundArr[$i] = 0;
            }
        }
        return response()->json(['status' => true, 'declineArr' => $declineArr, 'chargebackArr' => $chargebackArr, 'refundArr' => $refundArr, 'label' => $label]);
    }
    public function get_date_series($start_date, $end_date){
        $dates = array();
        $current = strtotime($start_date);
        $date2 = strtotime($end_date);
        $stepVal = '+1 day';
        while( $current <= $date2 ) {
            $dates[] = date('d-M', $current);
            $current = strtotime($stepVal, $current);
        }
        return $dates;
    }
}
