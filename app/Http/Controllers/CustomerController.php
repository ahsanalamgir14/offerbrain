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
    public function index(Request $request)
    {
        $is_count = $request->customer_id;
        if ($is_count == null) {
            $is_count = 0;
        }

        DB::statement("SET SQL_MODE=''");
        $pageno = isset($request->page) ? $request->page : 0;
        $no_of_records_per_page = isset($request->per_page) ? $request->per_page : 25;
        if ($is_count == 0 && $is_count != 1 && $is_count != '') {

            $query = Customer::where('customers.user_id', Auth::id())
                ->select(
                    'customers.id',
                    'customers.customer_id',
                    'customers.user_id',
                    'customers.email',
                    'customers.first_name',
                    'customers.last_name',
                    'customers.phone',
                    'customers.addresses',
                    'customers.deleted_at',
                    'orders.ip_address'
                )
                ->addSelect(DB::raw('0 as orders_count'))
                ->where('orders.user_id', Auth::id())
                ->join('orders', 'orders.customer_id', '=', 'customers.customer_id')
                ->orderBy('orders.ip_address', 'asc');

            $total_rows = $query->count('customers.id');
        } else {
            $query = DB::table('customers')
                ->select(
                    'customers.id',
                    'customers.user_id',
                    'customers.email',
                    'customers.first_name',
                    'customers.last_name',
                    'customers.phone',
                    'customers.customer_id',
                    'customers.addresses',
                    'customers.deleted_at',
                    'orders.id',
                    'orders.customer_id',
                    'orders.ip_address',
                    DB::raw('COUNT(CASE WHEN orders.customer_id = customers.customer_id AND customers.user_id = orders.user_id THEN 1 END) as orders_count')
                )
                ->where('customers.user_id', Auth::id())
                ->where('orders.user_id', Auth::id())
                ->join('orders', 'orders.customer_id', '=', 'customers.customer_id')
                ->groupBy('customers.id')
                ->orderBy('orders.ip_address', 'asc');

            $total_rows = $query->get()->count('customers.id');
        }

        if ($request->search != '') {
            $query->Where('customers.email', 'like', '%' . $request->search . '%')
                ->orWhere('customers.first_name', 'like', '%' . $request->search . '%')
                ->orWhere('customers.last_name', 'like', '%' . $request->search . '%');
        }
        $data = $query->orderBy('customers.id', 'asc')->SimplePaginate($no_of_records_per_page);
        $total_pages = ceil($total_rows / $data->perPage());
        $pag['count'] = $total_rows;
        $pag['total_pages'] = $total_pages;
        $pag['pageno'] = $pageno;
        $pag['rows_per_page'] = $no_of_records_per_page;
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
        $users = User::orderBy('id', 'asc')->get();
        foreach ($users as $user) {
            $setting = Setting::where('key', 'customer_last_page')->where('user_id', $user->id)->first();
            $created = 0;
            $updated = 0;
            $db_customers = Customer::pluck('customer_id')->where('user_id', $user->id)->toArray();
            $username = $user->sticky_api_username;
            $password = Crypt::decrypt($user->sticky_api_key);
            $url = $user->sticky_url . '/api/v2/contacts';
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
                        $customer = Customer::where(['customer_id' => $result['id']])->where('user_id', $user->id)->first();
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
                                $customer = Customer::where(['customer_id' => $result['id']])->where('user_id', $user->id)->first();
                                $customer->update($result);
                            } else {
                                $created++;
                                Customer::create($result);
                            }
                            $response = null;
                        }
                        Setting::where('key', 'customer_last_page')->where('user_id', $user->id)->update(['value' => $previousPage]);
                    }
                }
            }
        }
        return response()->json(['status' => true, 'new customers created' => $created, 'Updated customers' => $updated]);
    }

    public static function refresh_user_customers(Request $request)
    {
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
                    $setting = Setting::where(['key' => 'customer_last_page', 'user_id' => Auth::id()])->first();
                    if ($setting) {
                        $setting->update(['value' => $page, 'user_id' => Auth::id()]);
                    } else {
                        Setting::create(['key' => 'customer_last_page', 'value' => $page, 'user_id' => Auth::id()]);
                    }
                }
            }
        }
        return response()->json(['status' => true, 'user_id' => Auth::id(), 'new_customers' => $created, 'updated_customers' => $updated]);
    }
    public function getOrdersCount(Request $request)
    {
        $data = DB::table('orders')->select('id')->where('customer_id', $request->id)->count('id');
        return response()->json($data);
    }
}
