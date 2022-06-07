<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

        $is_count = $request->customer_id; 
        DB::statement("SET SQL_MODE=''");
        DB::enableQueryLog();
        $pageno = isset($request->page) ? $request->page : 1;
        $no_of_records_per_page = isset($request->per_page) ? $request->per_page : 25;
        
        if($is_count == 0 && $is_count != 1 && $is_count != ''){
            ini_set('memory_limit', '512M');
            set_time_limit(0);
            $query = Customer::doesnthave('customers')
            ->select('id', 'email', 'first_name', 'last_name', 'phone', 'addresses', 'deleted_at')
            ->addSelect(DB::raw('0 as orders_count'));
            $total_rows = $query->count('customers.id');
        } else {
            $query = DB::table('customers')
            ->select(DB::raw('customers.*'))
            ->join('orders', function ($join) {
                $join->on('orders.customer_id', '=','customers.id');
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
    public static function refresh_customers_bk()
    {
        // ini_set('memory_limit', '512M');
        // set_time_limit(0);
        $setting = Setting::first();
        $created = 0;
        $updated = 0;
        $db_customers = Customer::pluck('email')->toArray();
        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v2/contacts';
        $page = $setting->customer_last_page;

        $api_data = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page]);
        $response['customers'] = $api_data['data'];
        $last_page = $api_data['last_page'];

        if ($response['customers']) {
            foreach ($response['customers'] as $result) {

                $result['customer_id'] = $result['id'];
                $result['custom_fields'] = json_encode($result['custom_fields']);
                $result['addresses'] = json_encode($result['addresses']);
                $result['notes'] = json_encode($result['notes']);

                if (in_array($result['email'], $db_customers)) {
                    $updated++;
                    $customer = Customer::where(['email' => $result['email']])->first();
                    $customer->update($result);
                } else {
                    $created++;
                    Customer::create($result);
                }
            }
            if ($last_page > 1) {
                $page++;
                for ($page; $page <= $last_page; $page++) {

                    $response['customers'] = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page])['data'];

                    foreach ($response['customers'] as $result) {

                        $result['customer_id'] = $result['id'];
                        $result['custom_fields'] = json_encode($result['custom_fields']);
                        $result['addresses'] = json_encode($result['addresses']);
                        $result['notes'] = json_encode($result['notes']);

                        if (in_array($result['email'], $db_customers)) {
                            $updated++;
                            $customer = Customer::where(['email' => $result['email']])->first();
                            $customer->update($result);
                        } else {
                            $created++;
                            Customer::create($result);
                        }
                        $response = null;
                    }
                }
            }
        }
        Setting::update(['customer_last_page' => $last_page]);
        return response()->json(['status' => true, 'new customers created' => $created, 'Updated customers' => $updated]);
    }
    public static function refresh_customers()
    {
        // ini_set('memory_limit', '512M');
        // set_time_limit(0);
        $setting = Setting::where('key','customer_last_page')->first();
        $created = 0;
        $updated = 0;
        $db_customers = Customer::pluck('id')->toArray();
        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v2/contacts';
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
                    Setting::where('key','customer_last_page')->update(['value'=>$previousPage]);
                }
            }
        }
        return response()->json(['status' => true, 'new customers created' => $created, 'Updated customers' => $updated]);
    }
}
