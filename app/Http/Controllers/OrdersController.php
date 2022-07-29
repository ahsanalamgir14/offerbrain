<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Session;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Campaign;
use Carbon\CarbonPeriod;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Cache\RateLimiting\Limit;

class OrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $pageno = isset($request->pageno) ? $request->pageno : 0;
        $no_of_records_per_page = isset($request->per_page) ? $request->per_page : 25;

        $query = DB::table('orders')->select(
            'orders.id',
            'orders.order_id',
            'orders.created_by_employee_name',
            'orders.billing_first_name',
            'orders.billing_last_name',
            'orders.billing_street_address',
            'orders.order_total',
            'orders.acquisition_month',
            'orders.acquisition_year',
            'orders.c1',
            'orders.affid',
            'orders.trx_month',
            'orders.order_sales_tax_amount',
            'orders.decline_reason',
            'orders.is_cascaded',
            'orders.decline_reason_details',
            'orders.is_fraud',
            'orders.is_chargeback',
            'orders.chargeback_date',
            'orders.is_rma',
            'orders.rma_number',
            'orders.rma_reason',
            'orders.is_recurring',
            'orders.is_void',
            'orders.void_amount',
            'orders.void_date',
            'orders.is_refund',
            'orders.refund_amount',
            'orders.refund_date',
            'orders.order_confirmed',
            'orders.order_confirmed_date',
            'orders.acquisition_date',
            'orders.is_blacklisted',
            'orders.coupon_id',
            'orders.created_by_user_name',
            'orders.order_sales_tax',
            'orders.order_status',
            'orders.promo_code',
            'orders.recurring_date',
            'orders.response_code',
            'orders.return_reason',
            'orders.time_stamp',
        )
            ->where(['orders.user_id' => 2]); //dev mode
        // ->where(['orders.user_id' => $request->user()->id]);

        if ($start_date != null && $end_date != null) {
            $start_date = Carbon::parse($start_date)->startOfDay();
            $end_date = Carbon::parse($end_date)->endOfDay();
            $query->where('orders.time_stamp', '>=', $start_date);
            $query->where('orders.time_stamp', '<=', $end_date);
        }

        if (isset($request->gateway_id) && $request->gateway_id != "undefined") {
            $query->whereIn('orders.gateway_id', explode(',', $request->gateway_id));
        }
        if ($request->campaign_id != '') {
            $query->whereIn('orders.campaign_id', explode(',', $request->campaign_id));
        }
        if ($request->affiliate != '') {
            $query->where('orders.affiliate', $request->affiliate);
        }
        if ($request->sub_affiliate != '') {
            $query->where('orders.c1', $request->sub_affiliate)
                ->orWhere('orders.c2', $request->sub_affiliate)
                ->orWhere('orders.c3', $request->sub_affiliate);
        }
        if ($request->shipping_state != '') {
            $query->whereIn('orders.shipping_state', explode(',', $request->shipping_state));
        }

        if ($request->fields != null) {
            $field_array = explode(',', $request->fields);
            $value_array = explode(',', $request->values);
            for ($i = 0; $i < count($value_array); $i++) {
                if ($value_array[$i] != '' && $field_array[$i] != 'products' && $value_array[$i] != 7) {
                    $query->where('orders.' . $field_array[$i], $value_array[$i]);
                }
                if ($field_array[$i] == 'products') {
                    $query->where('orders.products', 'like', '%' . $value_array[$i] . '%');
                }
                if ($field_array[$i] == 'order_status' && $value_array[$i] == 7) {
                    $query->where('orders.order_status', 7);
                }
            }
        } else {
            $query->where('orders.order_status', '!=', 7);
        }
        if ($request->filteredProduct != '') {
            $query->join('order_products', 'orders.order_id', '=', 'order_products.order_id')->where('order_products.name', $request->filteredProduct);
        }

        $total_rows = $query->count('orders.id');

        $rows = $query->where('orders.order_status', '!=', 11)
            ->orderBy('orders.id', 'desc')->SimplePaginate($no_of_records_per_page);

        $total_pages = ceil($total_rows / $rows->perPage());

        $pag['count'] = $total_rows;
        $pag['total_pages'] = $total_pages;
        $pag['pageno'] = $pageno;
        $pag['rows_per_page'] = $no_of_records_per_page;
        return response()->json(['status' => true, 'data' => $rows, 'pag' => $pag]);
    }

    public function getDropDownContent()
    {
        DB::enableQueryLog();
        // $query = DB::table('orders')->where('id','>',0)->distinct();
        // $data['gateways'] = $query->get('gateway_descriptor');
        $data = DB::select("SELECT gateway_descriptor as aggregate from `orders` where `id` > 0")->distinct();

        // $data['country'] = $query->get('billing_country');
        // $data['state'] = $query->get('billing_state');
        // $data['card_type'] = $query->get('cc_type');
        // $data['campaigns'] = DB::table('campaigns')->select('id','name')->get();

        return response()->json($data);
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
    }

    public function show($id)
    {
        $order = Order::where(['order_id' => $id])->first();
        return $order;
        return Carbon::parse($order->updated_at)->format('Y-m-d');
    }

    public function edit($id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function destroy($id)
    {
    }

    public function get_product_detail(Request $request)
    {
        $data = DB::table('orders')->select('*')->find($request->id);
        $data->products = unserialize($data->products);
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function pull_orders_jan(Request $request)
    {
        // error_reporting(E_ALL);
        // ini_set('display_errors', 1);

        // ini_set('memory_limit', '512M');
        // set_time_limit(0);
        $new_orders = 0;
        $updated_orders = 0;
        $db_order_ids = Order::pluck('order_id')->toArray();

        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v1/order_find';

        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
            $url,
            [
                'start_date' => '12/01/2021',
                'end_date' => '12/31/2021',
                'campaign_id' => 'all',
                'criteria' => 'all'
            ]
        )->getBody()->getContents());

        $order_ids = $api_data->order_id;
        $total_orders = $api_data->total_orders;

        if ($total_orders < 50000) {

            $chunked_array = array_chunk($order_ids, 500);
            // dd($chunked_array);
            foreach ($chunked_array as $chucked_ids) {
                $order_view_api = 'https://thinkbrain.sticky.io/api/v1/order_view';
                $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                    ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                $results = $order_views->data;
                foreach ($results as $result) {
                    $result->user_id = 1;
                    $month = Carbon::parse($result->acquisition_date)->format('F');
                    $year = Carbon::parse($result->acquisition_date)->format('Y');
                    $result->acquisition_month = $month;
                    $result->acquisition_year = $year;
                    $result->trx_month = $month;
                    $result->billing_email = $result->email_address;
                    $result->billing_telephone = $result->customers_telephone;
                    $result->shipping_email = $result->email_address;
                    $result->shipping_telephone = $result->customers_telephone;
                    if (property_exists($result, 'employeeNotes')) {
                        $result->employeeNotes = serialize($result->employeeNotes);
                    }
                    $result->utm_info = serialize($result->utm_info);
                    if (property_exists($result, 'products')) {
                        $result->products = serialize($result->products);
                    }
                    $result->systemNotes = serialize($result->systemNotes);
                    $result->totals_breakdown = serialize($result->totals_breakdown);
                    if (in_array($result->order_id, $db_order_ids)) {
                        $updated_orders++;
                        $db_order = Order::where(['order_id' => $result->order_id])->first();
                        $db_order->update((array)$result);

                        $mass_assignment = $this->get_order_product_mass($result);
                        $order_product = OrderProduct::where(['order_id' => $db_order->order_id])->update($mass_assignment);
                    } else {
                        $new_orders++;
                        Order::create((array)$result);
                        $mass_assignment = $this->get_order_product_mass($result);
                        OrderProduct::create($mass_assignment);
                    }
                }
                $data = null;
                $results = null;
            }
            return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
        } else {
            return response()->json(['status' => false, 'message' => 'data exceeded 50000 records']);
        }
    }

    public function pull_user_orders(Request $request)
    {
        // ini_set('memory_limit', '512M');
        // set_time_limit(0);
        // return $request->user()->id;
        // return Auth::id();
        $new_orders = 0;
        $updated_orders = 0;
        $user = User::find($request->user()->id);
        // dd($user->id);
        $username = $user->sticky_api_username;
        $password = Crypt::decrypt($user->sticky_api_key);

        $start_date = '07/18/2022';
        $end_date = '07/18/2022';

        $db_order_ids = Order::where(['user_id' => Auth::id()])->pluck('order_id')->toArray();
        $url = $user->sticky_url . '/api/v1/order_find';
        // return $url;

        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
            $url,
            ['start_date' => $start_date, 'end_date' => $end_date, 'campaign_id' => 'all', 'criteria' => 'all']
        )->getBody()->getContents());

        $total_orders = $api_data->total_orders;
        if ($total_orders != 0) {
            $order_ids = $api_data->order_id;
            // return $order_ids;

            if ($total_orders < 50000) {
                $chunked_array = array_chunk($order_ids, 500);
                // return $chunked_array;
                foreach ($chunked_array as $chucked_ids) {
                    $order_view_api = $user->sticky_url . '/api/v1/order_view';
                    $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                        ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                    $results = $order_views->data;
                    foreach ($results as $result) {
                        // $result->user_id = $request->user()->id;
                        $result->user_id = $user->id;
                        $month = Carbon::parse($result->acquisition_date)->format('F');
                        $year = Carbon::parse($result->acquisition_date)->format('Y');
                        $result->acquisition_month = $month;
                        $result->acquisition_year = $year;
                        $result->trx_month = $month;
                        $result->billing_email = $result->email_address;
                        $result->billing_telephone = $result->customers_telephone;
                        $result->shipping_email = $result->email_address;
                        $result->shipping_telephone = $result->customers_telephone;
                        if (property_exists($result, 'employeeNotes')) {
                            $result->employeeNotes = serialize($result->employeeNotes);
                        }
                        $result->utm_info = serialize($result->utm_info);
                        if (property_exists($result, 'products')) {
                            $result->products = serialize($result->products);
                        }
                        if (property_exists($result, 'systemNotes')) {
                            $result->systemNotes = serialize($result->systemNotes);
                        }
                        $result->totals_breakdown = serialize($result->totals_breakdown);
                        if (in_array($result->order_id, $db_order_ids)) {
                            $updated_orders++;
                            $db_order = Order::where(['order_id' => $result->order_id, 'user_id' => Auth::id()])->first();
                            $db_order->update((array)$result);
                            $mass_assignment = $this->get_order_product_mass($result);
                            $order_product = OrderProduct::where(['order_id' => $db_order->order_id, 'user_id' => Auth::id()])->update($mass_assignment);
                        } else {
                            $new_orders++;
                            Order::create((array)$result);
                            $mass_assignment = $this->get_order_product_mass($result);
                            OrderProduct::create($mass_assignment);
                        }
                    }
                    $data = null;
                    $results = null;
                }
                return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
            } else {
                //orders > 50000
                $startDate = Carbon::createFromFormat('m/d/Y', $start_date);
                $endDate = Carbon::createFromFormat('m/d/Y', $end_date);
                $date_range = CarbonPeriod::create($startDate, $endDate);
                $date_range->toArray();

                foreach ($date_range as $day) {
                    $days[] = Carbon::parse($day)->format('m/d/Y');
                }
                foreach ($days as $key => $day) {
                    //Order_ids for a single day
                    $start_of_day = Carbon::parse($day)->startOfDay()->format('m/d/Y');
                    $end_of_day = Carbon::parse($day)->endOfDay()->format('m/d/Y');
                    $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
                        $url,
                        ['start_date' => $start_of_day, 'end_date' => $end_of_day, 'campaign_id' => 'all', 'criteria' => 'all']
                    )->getBody()->getContents());

                    $total_orders = $api_data->total_orders;
                    if ($total_orders != 0) {
                        $order_ids = $api_data->order_id;
                        //order_view and array of 500 api call
                        $chunked_array = array_chunk($order_ids, 500);
                        // dd($chunked_array);
                        foreach ($chunked_array as $chucked_ids) {
                            $order_view_api = $user->sticky_url . '/api/v1/order_view';
                            $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                                ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                            $results = $order_views->data;
                            foreach ($results as $result) {
                                // $result->user_id = $request->user()->id;
                                $result->user_id = $user->id;
                                $month = Carbon::parse($result->acquisition_date)->format('F');
                                $year = Carbon::parse($result->acquisition_date)->format('Y');
                                $result->acquisition_month = $month;
                                $result->acquisition_year = $year;
                                $result->trx_month = $month;
                                $result->billing_email = $result->email_address;
                                $result->billing_telephone = $result->customers_telephone;
                                $result->shipping_email = $result->email_address;
                                $result->shipping_telephone = $result->customers_telephone;
                                if (property_exists($result, 'employeeNotes')) {
                                    $result->employeeNotes = serialize($result->employeeNotes);
                                }
                                $result->utm_info = serialize($result->utm_info);
                                if (property_exists($result, 'products')) {
                                    $result->products = serialize($result->products);
                                }
                                if (property_exists($result, 'systemNotes')) {
                                    $result->systemNotes = serialize($result->systemNotes);
                                }
                                $result->totals_breakdown = serialize($result->totals_breakdown);
                                if (in_array($result->order_id, $db_order_ids)) {
                                    $updated_orders++;
                                    $db_order = Order::where(['order_id' => $result->order_id, 'user_id' => Auth::id()])->first();
                                    $db_order->update((array)$result);
                                    $mass_assignment = $this->get_order_product_mass($result);
                                    $order_product = OrderProduct::where(['order_id' => $db_order->order_id, 'user_id' => Auth::id()])->update($mass_assignment);
                                } else {
                                    $new_orders++;
                                    Order::create((array)$result);
                                    $mass_assignment = $this->get_order_product_mass($result);
                                    OrderProduct::create($mass_assignment);
                                }
                            }
                            $data = null;
                            $results = null;
                        }
                    }
                }
                // if ($key == 3) {
                return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
                // }
                // return response()->json(['status' => false, 'message' => 'data exceeded 50000 records']);
            }
        }
        return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
    }
    public function updateRecords()
    {
        $idArr = [];
        $data = DB::table('orders_bk')->select('order_id', 'affid', 'parent_affId', 'employeeNotes')
            ->where('user_id', 1)
            ->where('is_test_cc', 0)
            ->where('affid', '')
            ->get();

        foreach ($data as $order) {
            if (!empty($order->employeeNotes)) {
                $employeeNotes = $order->employeeNotes;
                if (strpos($employeeNotes, 'ID #') !== false) {
                    $parentId = substr($employeeNotes, strpos($employeeNotes, "ID #") + 1);
                    $parentId = preg_replace('/[^0-9]/', '', $parentId);
                    $affData = DB::table('orders_bk')->select('id', 'affid', 'employeeNotes')->where('order_id', $parentId)->first();
                    if (isset($affData->affid) && !empty($affData->affid)) {
                        DB::table('orders_bk')->where('order_id', $order->order_id)->update(['parent_affId' => $affData->affid]);
                    } else {
                        if (!empty($affData->employeeNotes)) {
                            if (strpos($affData->employeeNotes, 'ID #') !== false) {
                                $parentId = substr($affData->employeeNotes, strpos($affData->employeeNotes, "ID #") + 1);
                                $parentId = preg_replace('/[^0-9]/', '', $parentId);
                                $affData = DB::table('orders_bk')->select('id', 'affid', 'employeeNotes')->where('order_id', $parentId)->first();
                                if (isset($affData->affid) && !empty($affData->affid)) {
                                    DB::table('orders_bk')->where('order_id', $order->order_id)->update(['parent_affId' => $affData->affid]);
                                } else {
                                    if (!empty($affData->employeeNotes)) {
                                        if (strpos($affData->employeeNotes, 'ID #') !== false) {
                                            $parentId = substr($affData->employeeNotes, strpos($affData->employeeNotes, "ID #") + 1);
                                            $parentId = preg_replace('/[^0-9]/', '', $parentId);
                                            $affData = DB::table('orders_bk')->select('id', 'affid', 'employeeNotes')->where('order_id', $parentId)->first();
                                            if (isset($affData->affid) && !empty($affData->affid)) {
                                                DB::table('orders_bk')->where('order_id', $order->order_id)->update(['parent_affId' => $affData->affid]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function getAffid($orderAff, $orderEmployeeNotes, $user_id)
    {
        if (empty($orderAff)) {
            if (!empty($orderEmployeeNotes)) {
                $employeeNotes = $orderEmployeeNotes;
                if (strpos($employeeNotes, 'ID #') !== false) {
                    $parentId = substr($employeeNotes, strpos($employeeNotes, "ID #") + 1);
                    $parentId = preg_replace('/[^0-9]/', '', $parentId);
                    $affData = DB::table('orders')->select('id', 'affid', 'employeeNotes')->where('user_id', $user_id)->where('order_id', $parentId)->first();
                    if (isset($affData->affid) && !empty($affData->affid)) {
                        return $affData->affid;
                    } else {
                        if (!empty($affData->employeeNotes)) {
                            if (strpos($affData->employeeNotes, 'ID #') !== false) {
                                $employeeNotes = $affData->employeeNotes;
                                $parentId = substr($employeeNotes, strpos($employeeNotes, "ID #") + 1);
                                $parentId = preg_replace('/[^0-9]/', '', $parentId);
                                $affData = DB::table('orders')->select('id', 'affid', 'employeeNotes')->where('user_id', $user_id)->where('order_id', $parentId)->first();
                                if (isset($affData->affid) && !empty($affData->affid)) {
                                    return $affData->affid;
                                } else {
                                    if (!empty($affData->employeeNotes)) {
                                        if (strpos($affData->employeeNotes, 'ID #') !== false) {
                                            $employeeNotes = $affData->employeeNotes;
                                            $parentId = substr($employeeNotes, strpos($employeeNotes, "ID #") + 1);
                                            $parentId = preg_replace('/[^0-9]/', '', $parentId);
                                            $affData = DB::table('orders')->select('id', 'affid', 'employeeNotes')->where('user_id', $user_id)->where('order_id', $parentId)->first();
                                            if (isset($affData->affid) && !empty($affData->affid)) {
                                                return $affData->affid;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    public static function curentTime()
    {
        $data['currentDate'] = now();
        $data['todayStart'] = Carbon::now()->startOfDay()->format('m/d/Y');
        $data['todayEnd'] = Carbon::now()->endOfDay()->format('m/d/Y');
        return response()->json($data);
    }

    public static function pull_cron_orders_local()
    {
        $users = User::orderBy('id', 'asc')->get();
        foreach ($users as $user) {
            $password = Crypt::decrypt($user->sticky_api_key);
            $new_orders = 0;
            $updated_orders = 0;
            $username = $user->sticky_api_username;

            $start_date = '06/01/2022';
            $end_date = '06/30/2022';

            // $start_date = Carbon::now()->startOfDay()->format('m/d/Y');
            // $end_date = Carbon::now()->endOfDay()->format('m/d/Y');

            $db_order_ids = DB::table('orders')->select('order_id')
                ->where('user_id', $user->id)
                ->where('time_stamp', '>=', date("Y-m-d", strtotime($start_date)) . ' 00:00:00')
                ->where('time_stamp', '<=', date("Y-m-d", strtotime($end_date)) . ' 23:59:59')
                ->pluck('order_id')->toArray();

            $url = $user->sticky_url . '/api/v1/order_find';

            $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
                $url,
                ['start_date' => $start_date, 'end_date' => $end_date, 'campaign_id' => 'all', 'criteria' => 'all']
            )->getBody()->getContents());
            $total_orders = $api_data->total_orders;
            if ($total_orders != 0) {
                $order_ids = $api_data->order_id;
                if ($total_orders < 50000) {
                    $chunked_array = array_chunk($order_ids, 500);
                    foreach ($chunked_array as $chucked_ids) {
                        $order_view_api = $user->sticky_url . '/api/v1/order_view';
                        $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                            ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                        $results = $order_views->data;
                        foreach ($results as $order) {
                            $order->user_id = $user->id;
                            $month = Carbon::parse($order->acquisition_date)->format('F');
                            $year = Carbon::parse($order->acquisition_date)->format('Y');
                            $order->acquisition_month = $month;
                            $order->acquisition_year = $year;
                            $order->trx_month = $month;
                            $order->billing_email = $order->email_address;
                            $order->billing_telephone = $order->customers_telephone;
                            $order->shipping_email = $order->email_address;
                            $order->shipping_telephone = $order->customers_telephone;

                            if (property_exists($order, 'employeeNotes')) {
                                $order->employeeNotes = serialize($order->employeeNotes);
                            }
                            $order->utm_info = serialize($order->utm_info);
                            if (property_exists($order, 'products')) {
                                $order->products = serialize($order->products);
                            }
                            if (property_exists($order, 'systemNotes')) {
                                $order->systemNotes = serialize($order->systemNotes);
                            }
                            if (isset($order->employeeNotes) && strpos($order->employeeNotes, 'ID #') !== false) {
                                $order->parent_affid = self::getAffid($order->affid, $order->employeeNotes, $user->id);
                            }

                            $order->totals_breakdown = serialize($order->totals_breakdown);
                            if (!in_array($order->order_id, $db_order_ids)) {
                                $new_orders++;
                                Order::create((array)$order);
                                $order->products = unserialize($order->products);
                                $mass_assignment = self::get_product_order_mass($order, $user->id);

                                OrderProduct::create($mass_assignment);
                            } else {
                                // $updated_orders++;
                                // $db_order = Order::where(['order_id' => $order->order_id])->where('user_id',$user->id)->first();
                                // $db_order->update((array)$order);
                                // $order->products = unserialize($order->products);
                                // $mass_assignment = self::get_product_order_mass($order, $user->id);
                                // OrderProduct::where('order_id',$order->order_id)->where('user_id',$user->id)->update($mass_assignment);
                            }
                        }
                        $data = null;
                        $results = null;
                    }
                    return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
                } else {
                    $startDate = Carbon::createFromFormat('m/d/Y', $start_date);
                    $endDate = Carbon::createFromFormat('m/d/Y', $end_date);
                    $date_range = CarbonPeriod::create($startDate, $endDate);
                    $date_range->toArray();

                    foreach ($date_range as $day) {
                        $days[] = Carbon::parse($day)->format('m/d/Y');
                    }
                    foreach ($days as $key => $day) {
                        //Order_ids for a single day
                        $start_of_day = Carbon::parse($day)->startOfDay()->format('m/d/Y');
                        $end_of_day = Carbon::parse($day)->endOfDay()->format('m/d/Y');
                        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
                            $url,
                            ['start_date' => $start_of_day, 'end_date' => $end_of_day, 'campaign_id' => 'all', 'criteria' => 'all']
                        )->getBody()->getContents());

                        $total_orders = $api_data->total_orders;
                        if ($total_orders != 0) {
                            $order_ids = $api_data->order_id;
                            //order_view and array of 500 api call
                            $chunked_array = array_chunk($order_ids, 500);
                            foreach ($chunked_array as $chucked_ids) {
                                $order_view_api = $user->sticky_url . '/api/v1/order_view';
                                $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                                    ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                                $results = $order_views->data;
                                foreach ($results as $order) {
                                    $order->user_id = $user->id;
                                    $month = Carbon::parse($order->acquisition_date)->format('F');
                                    $year = Carbon::parse($order->acquisition_date)->format('Y');
                                    $order->acquisition_month = $month;
                                    $order->acquisition_year = $year;
                                    $order->trx_month = $month;
                                    $order->billing_email = $order->email_address;
                                    $order->billing_telephone = $order->customers_telephone;
                                    $order->shipping_email = $order->email_address;
                                    $order->shipping_telephone = $order->customers_telephone;

                                    if (property_exists($order, 'employeeNotes')) {
                                        $order->employeeNotes = serialize($order->employeeNotes);
                                    }
                                    $order->utm_info = serialize($order->utm_info);
                                    if (property_exists($order, 'products')) {
                                        $order->products = serialize($order->products);
                                    }
                                    if (property_exists($order, 'systemNotes')) {
                                        $order->systemNotes = serialize($order->systemNotes);
                                    }
                                    if (isset($order->employeeNotes) && strpos($order->employeeNotes, 'ID #') !== false) {
                                        $order->parent_affid = self::getAffid($order->affid, $order->employeeNotes, $user->id);
                                    }
                                    $order->totals_breakdown = serialize($order->totals_breakdown);
                                    if (!in_array($order->order_id, $db_order_ids)) {
                                        $new_orders++;
                                        Order::create((array)$order);
                                        $order->products = unserialize($order->products);
                                        $mass_assignment = self::get_product_order_mass($order, $user->id);

                                        OrderProduct::create($mass_assignment);
                                    }
                                    // else {
                                    //     $updated_orders++;
                                    //     $db_order = Order::where(['order_id' => $order->order_id])->where('user_id',$user->id)->first();
                                    //     $db_order->update((array)$order);
                                    //     $order->products = unserialize($order->products);
                                    //     $mass_assignment = self::get_product_order_mass($order, $user->id);

                                    //     OrderProduct::where(['order_id' => $db_order->order_id])->where('user_id',$user->id)->update($mass_assignment);
                                    // }
                                }
                                $data = null;
                                $results = null;
                            }
                        }
                    }
                }
            }
        }
        return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
    }
    // public static function pull_cron_orders($start_date, $end_date)
    public static function pull_cron_orders()
    {
        $users = User::orderBy('id', 'desc')->get();
        $new_orders = 0;
        $updated_orders = 0;
        foreach ($users as $user) {
            $password = Crypt::decrypt($user->sticky_api_key);
            $username = $user->sticky_api_username;

            $start_date = '07/05/2022';
            $end_date = '07/05/2022';

            $db_order_ids = DB::table('orders')->select('order_id')
                ->where('user_id', $user->id)
                ->where('time_stamp', '>=', date("Y-m-d", strtotime($start_date)) . ' 00:00:00')
                ->where('time_stamp', '<=', date("Y-m-d", strtotime($end_date)) . ' 23:59:59')
                ->pluck('order_id')->toArray();

            $url = $user->sticky_url . '/api/v1/order_find';

            $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
                $url,
                ['start_date' => $start_date, 'end_date' => $end_date, 'campaign_id' => 'all', 'criteria' => 'all']
            )->getBody()->getContents());
            $total_orders = $api_data->total_orders;

            if ($total_orders != 0) {
                $order_ids = $api_data->order_id;
                if ($total_orders < 50000) {
                    $chunked_array = array_chunk($order_ids, 500);
                    foreach ($chunked_array as $chucked_ids) {
                        $order_view_api = $user->sticky_url . '/api/v1/order_view';
                        $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                            ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                        $results = $order_views->data;
                        foreach ($results as $order) {
                            $order->user_id = $user->id;
                            $month = Carbon::parse($order->acquisition_date)->format('F');
                            $year = Carbon::parse($order->acquisition_date)->format('Y');
                            $order->acquisition_month = $month;
                            $order->acquisition_year = $year;
                            $order->trx_month = $month;
                            $order->billing_email = $order->email_address;
                            $order->billing_telephone = $order->customers_telephone;
                            $order->shipping_email = $order->email_address;
                            $order->shipping_telephone = $order->customers_telephone;

                            if (property_exists($order, 'employeeNotes')) {
                                $order->employeeNotes = serialize($order->employeeNotes);
                            }
                            $order->utm_info = serialize($order->utm_info);
                            if (property_exists($order, 'products')) {
                                $order->products = serialize($order->products);
                            }
                            if (property_exists($order, 'systemNotes')) {
                                $order->systemNotes = serialize($order->systemNotes);
                            }
                            if (isset($order->employeeNotes) && strpos($order->employeeNotes, 'ID #') !== false) {
                                $order->parent_affid = self::getAffid($order->affid, $order->employeeNotes, $user->id);
                            }

                            $order->totals_breakdown = serialize($order->totals_breakdown);
                            if (!in_array($order->order_id, $db_order_ids)) {
                                $new_orders++;
                                Order::create((array)$order);
                                $mass_assignment = self::get_product_order_mass($order, $user->id);
                                OrderProduct::create($mass_assignment);
                            } else {
                                $updated_orders++;
                                $db_order = Order::where(['order_id' => $order->order_id])->where('user_id', $user->id)->first();
                                $db_order->update((array)$order);
                                $mass_assignment = self::get_product_order_mass($order, $user->id);
                                OrderProduct::where(['order_id' => $db_order->order_id])->where('user_id', $user->id)->update($mass_assignment);
                            }
                        }
                        $data = null;
                        $results = null;
                    }
                } else {
                    $startDate = Carbon::createFromFormat('m/d/Y', $start_date);
                    $endDate = Carbon::createFromFormat('m/d/Y', $end_date);
                    $date_range = CarbonPeriod::create($startDate, $endDate);
                    $date_range->toArray();

                    foreach ($date_range as $day) {
                        $days[] = Carbon::parse($day)->format('m/d/Y');
                    }
                    foreach ($days as $key => $day) {
                        //Order_ids for a single day
                        $start_of_day = Carbon::parse($day)->startOfDay()->format('m/d/Y');
                        $end_of_day = Carbon::parse($day)->endOfDay()->format('m/d/Y');
                        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
                            $url,
                            ['start_date' => $start_of_day, 'end_date' => $end_of_day, 'campaign_id' => 'all', 'criteria' => 'all']
                        )->getBody()->getContents());

                        $total_orders = $api_data->total_orders;
                        if ($total_orders != 0) {
                            $order_ids = $api_data->order_id;
                            //order_view and array of 500 api call
                            $chunked_array = array_chunk($order_ids, 500);
                            foreach ($chunked_array as $chucked_ids) {
                                $order_view_api = $user->sticky_url . '/api/v1/order_view';
                                $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                                    ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                                $results = $order_views->data;
                                foreach ($results as $order) {
                                    $order->user_id = $user->id;
                                    $month = Carbon::parse($order->acquisition_date)->format('F');
                                    $year = Carbon::parse($order->acquisition_date)->format('Y');
                                    $order->acquisition_month = $month;
                                    $order->acquisition_year = $year;
                                    $order->trx_month = $month;
                                    $order->billing_email = $order->email_address;
                                    $order->billing_telephone = $order->customers_telephone;
                                    $order->shipping_email = $order->email_address;
                                    $order->shipping_telephone = $order->customers_telephone;

                                    if (property_exists($order, 'employeeNotes')) {
                                        $order->employeeNotes = serialize($order->employeeNotes);
                                    }
                                    $order->utm_info = serialize($order->utm_info);
                                    if (property_exists($order, 'products')) {
                                        $order->products = serialize($order->products);
                                    }
                                    if (property_exists($order, 'systemNotes')) {
                                        $order->systemNotes = serialize($order->systemNotes);
                                    }
                                    if (isset($order->employeeNotes) && strpos($order->employeeNotes, 'ID #') !== false) {
                                        $order->parent_affid = self::getAffid($order->affid, $order->employeeNotes, $user->id);
                                    }
                                    $order->totals_breakdown = serialize($order->totals_breakdown);
                                    if (!in_array($order->order_id, $db_order_ids)) {
                                        $new_orders++;
                                        Order::create((array)$order);
                                        $mass_assignment = self::get_product_order_mass($order, $user->id);
                                        OrderProduct::create($mass_assignment);
                                    } else {
                                        $updated_orders++;
                                        $db_order = Order::where(['order_id' => $order->order_id])->where('user_id', $user->id)->first();
                                        $db_order->update((array)$order);
                                        $mass_assignment = self::get_product_order_mass($order, $user->id);
                                        OrderProduct::where(['order_id' => $db_order->order_id])->where('user_id', $user->id)->update($mass_assignment);
                                    }
                                }
                                $data = null;
                                $results = null;
                            }
                        }
                    }
                }
            }
        }
        return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
    }
    public static function daily_order_history_cron($start_date, $end_date)
    // public static function daily_order_history_cron()
    {
        // $start_date = '2022-07-06 00:00:00';
        // $end_date = '2022-07-06 23:59:59';
        $endingDate = date('Y-m-d H:i:s', strtotime($end_date . ' -1 minutes'));
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $users = User::orderBy('id', 'desc')->get();

        foreach ($users as $user) {
            $new_orders = 0;
            $updated_orders = 0;
            $order_ids = [];
            $pending_orders = [];

            $username = $user->sticky_api_username;
            $password = Crypt::decrypt($user->sticky_api_key);

            $url = $user->sticky_url . '/api/v2/orders/histories?start_at=' . $start_date . '&end_at=' . $end_date;

            $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                ->get($url)->getBody()->getContents());

            if ($api_data->status == "SUCCESS") {
                $last_page = $api_data->last_page;
                $total = $api_data->total;
                $orders = $api_data->data;

                $order_ids = array_merge($order_ids, array_column($orders, 'order_id'));

                for ($i = 2; $i <= $last_page; $i++) {
                    $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                        ->get($url . '&page=' . $last_page)->getBody()->getContents());

                    $orders = $api_data->data;
                    $order_ids = array_merge($order_ids, array_column($orders, 'order_id'));
                }
                $order_ids = array_unique($order_ids);

                if ($total < 50000) {

                    $chunked_array = array_chunk($order_ids, 500);
                    foreach ($chunked_array as $chucked_ids) {
                        $order_view_api = $user->sticky_url . '/api/v1/order_view';
                        $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                            ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());
                        if (isset($order_views->data)) {
                            $results = $order_views->data;
                            foreach ($results as $order) {
                                $month = Carbon::parse($order->time_stamp)->format('F');
                                $year = Carbon::parse($order->time_stamp)->format('Y');
                                $order->acquisition_month = $month;
                                $order->acquisition_year = $year;
                                $order->trx_month = $month;
                                $order->billing_email = $order->email_address;
                                $order->billing_telephone = $order->customers_telephone;
                                $order->shipping_email = $order->email_address;
                                $order->shipping_telephone = $order->customers_telephone;
                                if (property_exists($order, 'employeeNotes')) {
                                    $order->employeeNotes = serialize($order->employeeNotes);
                                }
                                $order->utm_info = serialize($order->utm_info);
                                if (property_exists($order, 'products')) {
                                    $order->products = serialize($order->products);
                                }
                                $order->systemNotes = serialize($order->systemNotes);
                                $order->totals_breakdown = serialize($order->totals_breakdown);
                                $order->user_id = $user->id;
                                $db_order = Order::where(['order_id' => $order->order_id, 'user_id' => $user->id])->first();

                                if ($db_order) {
                                    $db_order->update((array)$order);
                                    $updated_orders++;
                                    $mass_assignment = self::get_product_order_mass($order, $user->id);
                                    OrderProduct::where(['order_id' => $db_order->order_id, 'user_id' => $user->id])->update($mass_assignment);
                                } else {
                                    if (isset($order->employeeNotes) && strpos($order->employeeNotes, 'ID #') !== false) {
                                        $order->parent_affid = self::getAffid($order->affid, $order->employeeNotes, $user->id);
                                    }
                                    array_push($pending_orders, $order->order_id);
                                    Order::create((array)$order);
                                    $new_orders++;
                                    $mass_assignment = self::get_product_order_mass($order, $user->id);
                                    OrderProduct::create($mass_assignment);
                                }
                            }
                        }
                        $data = null;
                        $results = null;
                        $order_ids = [];
                    }
                }
            }
        }
        Setting::where('key', '_last_date_for_history_api')->update(['value' => $endingDate]);
        return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
    }
    public static function get_product_order_mass($order, $user_id)
    {
        $order->products = unserialize($order->products);
        $result['order_id'] = $order->order_id;
        $result['user_id'] = $user_id;
        $result['product_id'] = $order->products[0]->product_id;
        $result['sku'] = $order->products[0]->sku;
        $result['price'] = $order->products[0]->price;
        $result['product_qty'] = $order->products[0]->product_qty;
        $result['name'] = $order->products[0]->name;
        $result['is_recurring'] = $order->products[0]->is_recurring;
        $result['is_terminal'] = $order->products[0]->is_terminal;
        $result['recurring_date'] = $order->products[0]->recurring_date;
        $result['subscription_id'] = $order->products[0]->subscription_id;
        $result['next_subscription_product'] = $order->products[0]->next_subscription_product;
        $result['next_subscription_product_id'] = $order->products[0]->next_subscription_product_id;
        $result['next_subscription_product_price'] = $order->products[0]->next_subscription_product_price;
        $result['next_subscription_qty'] = $order->products[0]->next_subscription_qty;
        $result['billing_model_discount'] = $order->products[0]->billing_model_discount;
        $result['is_add_on'] = $order->products[0]->is_add_on;
        $result['is_in_trial'] = $order->products[0]->is_in_trial;
        $result['step_number'] = $order->products[0]->step_number;
        $result['is_shippable'] = $order->products[0]->is_shippable;
        $result['is_full_refund'] = $order->products[0]->is_full_refund;
        $result['refund_amount'] = $order->products[0]->refund_amount;
        $result['on_hold'] = $order->products[0]->on_hold;
        $result['hold_date'] = $order->products[0]->hold_date;
        $result['time_stamp'] = $order->time_stamp;
        if (isset($order->products[0]->billing_model)) {
            $result['billing_model_id'] = $order->products[0]->billing_model->id;
            $result['billing_model_name'] = $order->products[0]->billing_model->name;
            $result['billing_model_description'] = $order->products[0]->billing_model->description;
        }
        if (isset($order->products[0]->offer)) {
            $result['offer_id'] = $order->products[0]->offer->id;
            $result['offer_name'] = $order->products[0]->offer->name;
        }
        return $result;
    }
    public function pull_user_order_history(Request $request)
    {
        $new_orders = 0;
        $updated_orders = 0;
        $order_ids = [];
        $pending_orders = [];

        $user = User::find($request->user()->id);
        // return $user->id;
        $username = $user->sticky_api_username;
        $password = Crypt::decrypt($user->sticky_api_key);
        $url = $user->sticky_url . '/api/v1/order_find';

        $starting_day = '2022-07-18';
        $ending_day = '2022-07-18';
        // $start_date = Carbon::parse($starting_day)->startOfDay();
        // $end_date = Carbon::parse($ending_day)->endOfDay();
        $date_range = CarbonPeriod::create($starting_day, $ending_day);
        $date_range->toArray();
        // dd($date_range);

        foreach ($date_range as $day) {
            $month_days[] = $day;
        }
        foreach ($month_days as $day) {
            $start_day = Carbon::parse($day)->startOfDay();
            $end_day = Carbon::parse($day)->endOfDay();

            $url = $user->sticky_url . '/api/v2/orders/histories?start_at=' . $start_day . '&end_at=' . $end_day;

            $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                ->get($url)->getBody()->getContents());

            if ($api_data->status == "SUCCESS") {
                $last_page = $api_data->last_page;
                $total = $api_data->total;
                $orders = $api_data->data;
                $order_ids = array_merge($order_ids, array_column($orders, 'order_id'));

                for ($i = 2; $i <= $last_page; $i++) {
                    $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                        ->get($url . '&page=' . $i)->getBody()->getContents());

                    $orders = $api_data->data;
                    $order_ids = array_merge($order_ids, array_column($orders, 'order_id'));
                }
                $order_ids = array_unique($order_ids);

                if ($total < 50000) {
                    $chunked_array = array_chunk($order_ids, 500);
                    foreach ($chunked_array as $chucked_ids) {
                        $order_view_api = $user->sticky_url . '/api/v1/order_view';
                        $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                            ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                        $results = $order_views->data;
                        foreach ($results as $result) {
                            $result->user_id = $user->id;
                            $month = Carbon::parse($result->time_stamp)->format('F');
                            $year = Carbon::parse($result->time_stamp)->format('Y');
                            $result->acquisition_month = $month;
                            $result->acquisition_year = $year;
                            $result->trx_month = $month;
                            $result->billing_email = $result->email_address;
                            $result->billing_telephone = $result->customers_telephone;
                            $result->shipping_email = $result->email_address;
                            $result->shipping_telephone = $result->customers_telephone;
                            if (property_exists($result, 'employeeNotes')) {
                                $result->employeeNotes = serialize($result->employeeNotes);
                            }
                            $result->utm_info = serialize($result->utm_info);
                            if (property_exists($result, 'products')) {
                                $result->products = serialize($result->products);
                            }
                            $result->systemNotes = serialize($result->systemNotes);
                            $result->totals_breakdown = serialize($result->totals_breakdown);
                            //update
                            $updated_orders++;
                            $db_order = Order::where(['order_id' => $result->order_id, 'user_id' => Auth::id()])->first();
                            if ($db_order) {
                                $db_order->update((array)$result);
                                $mass_assignment = $this->get_order_product_mass($result);
                                $order_product = OrderProduct::where(['order_id' => $db_order->order_id, 'user_id' => Auth::id()])->update($mass_assignment);
                            } else {
                                array_push($pending_orders, $result->order_id);
                                $new_orders++;
                                Order::create((array)$result);
                                $mass_assignment = $this->get_order_product_mass($result);
                                OrderProduct::create($mass_assignment);
                            }
                        }
                        $data = null;
                        $results = null;
                        $order_ids = [];
                    }
                } else {
                    return response()->json(['status' => false, 'user_id' => Auth::id(), 'message' => 'data exceeded 50000 records']);
                }
            }
        }
        return response()->json(['status' => true, 'user_id' => Auth::id(), 'New Orders' => $new_orders, 'Updated orders:' => $updated_orders, 'New Pending Orders: ' => $pending_orders]);
    }

    public static function pull_yesterday_cron_orders($start_date, $end_date)
    {
        // ini_set('memory_limit', '512M');
        // set_time_limit(0);
        $users = User::orderBy('id', 'desc')->get();
        foreach ($users as $user) {
            $password = Crypt::decrypt($user->sticky_api_key);
            $new_orders = 0;
            $updated_orders = 0;
            $username = $user->sticky_api_username;
            $start = Carbon::today();

            // $start_date = Carbon::yesterday()->startOfDay()->format('m/d/Y');
            // $end_date = Carbon::yesterday()->endOfDay()->format('m/d/Y');

            $db_order_ids = Order::where(['user_id' => $user->id])->pluck('order_id')->toArray();
            $url = $user->sticky_url . '/api/v1/order_find';

            $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
                $url,
                ['start_date' => $start_date, 'end_date' => $end_date, 'campaign_id' => 'all', 'criteria' => 'all']
            )->getBody()->getContents());
            $total_orders = $api_data->total_orders;
            if ($total_orders != 0) {
                $order_ids = $api_data->order_id;

                if ($total_orders < 50000) {
                    $chunked_array = array_chunk($order_ids, 500);
                    foreach ($chunked_array as $chucked_ids) {
                        $order_view_api = $user->sticky_url . '/api/v1/order_view';
                        $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                            ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                        $results = $order_views->data;
                        foreach ($results as $order) {
                            $order->user_id = $user->id;
                            $month = Carbon::parse($order->acquisition_date)->format('F');
                            $year = Carbon::parse($order->acquisition_date)->format('Y');
                            $order->acquisition_month = $month;
                            $order->acquisition_year = $year;
                            $order->trx_month = $month;
                            $order->billing_email = $order->email_address;
                            $order->billing_telephone = $order->customers_telephone;
                            $order->shipping_email = $order->email_address;
                            $order->shipping_telephone = $order->customers_telephone;
                            if (property_exists($order, 'employeeNotes')) {
                                $order->employeeNotes = serialize($order->employeeNotes);
                            }
                            $order->utm_info = serialize($order->utm_info);
                            if (property_exists($order, 'products')) {
                                $order->products = serialize($order->products);
                            }
                            if (property_exists($order, 'systemNotes')) {
                                $order->systemNotes = serialize($order->systemNotes);
                            }
                            $order->totals_breakdown = serialize($order->totals_breakdown);
                            if (in_array($order->order_id, $db_order_ids)) {
                                $updated_orders++;

                                $db_order = Order::where(['order_id' => $order->order_id])->where('user_id', $user->id)->first();

                                $db_order->update((array)$order);

                                $order->products = unserialize($order->products);
                                $mass_assignment['order_id'] = $order->order_id;
                                $mass_assignment['user_id'] = $user->id;
                                $mass_assignment['product_id'] = $order->products[0]->product_id;
                                $mass_assignment['sku'] = $order->products[0]->sku;
                                $mass_assignment['price'] = $order->products[0]->price;
                                $mass_assignment['product_qty'] = $order->products[0]->product_qty;
                                $mass_assignment['name'] = $order->products[0]->name;
                                $mass_assignment['is_recurring'] = $order->products[0]->is_recurring;
                                $mass_assignment['is_terminal'] = $order->products[0]->is_terminal;
                                $mass_assignment['recurring_date'] = $order->products[0]->recurring_date;
                                $mass_assignment['subscription_id'] = $order->products[0]->subscription_id;
                                $mass_assignment['next_subscription_product'] = $order->products[0]->next_subscription_product;
                                $mass_assignment['next_subscription_product_id'] = $order->products[0]->next_subscription_product_id;
                                $mass_assignment['next_subscription_product_price'] = $order->products[0]->next_subscription_product_price;
                                $mass_assignment['next_subscription_qty'] = $order->products[0]->next_subscription_qty;
                                $mass_assignment['billing_model_discount'] = $order->products[0]->billing_model_discount;
                                $mass_assignment['is_add_on'] = $order->products[0]->is_add_on;
                                $mass_assignment['is_in_trial'] = $order->products[0]->is_in_trial;
                                $mass_assignment['step_number'] = $order->products[0]->step_number;
                                $mass_assignment['is_shippable'] = $order->products[0]->is_shippable;
                                $mass_assignment['is_full_refund'] = $order->products[0]->is_full_refund;
                                $mass_assignment['refund_amount'] = $order->products[0]->refund_amount;
                                $mass_assignment['on_hold'] = $order->products[0]->on_hold;
                                $mass_assignment['hold_date'] = $order->products[0]->hold_date;
                                if (isset($order->products[0]->billing_model)) {
                                    $mass_assignment['billing_model_id'] = $order->products[0]->billing_model->id;
                                    $mass_assignment['billing_model_name'] = $order->products[0]->billing_model->name;
                                    $mass_assignment['billing_model_description'] = $order->products[0]->billing_model->description;
                                }
                                if (isset($order->products[0]->offer)) {
                                    $mass_assignment['offer_id'] = $order->products[0]->offer->id;
                                    $mass_assignment['offer_name'] = $order->products[0]->offer->name;
                                }

                                $order_product = OrderProduct::where(['order_id' => $db_order->order_id])->where('user_id', $user->id)->update($mass_assignment);
                            } else {
                                $new_orders++;
                                Order::create((array)$order);

                                $order->products = unserialize($order->products);
                                $mass_assignment['order_id'] = $order->order_id;
                                $mass_assignment['user_id'] = $user->id;
                                $mass_assignment['product_id'] = $order->products[0]->product_id;
                                $mass_assignment['sku'] = $order->products[0]->sku;
                                $mass_assignment['price'] = $order->products[0]->price;
                                $mass_assignment['product_qty'] = $order->products[0]->product_qty;
                                $mass_assignment['name'] = $order->products[0]->name;
                                $mass_assignment['is_recurring'] = $order->products[0]->is_recurring;
                                $mass_assignment['is_terminal'] = $order->products[0]->is_terminal;
                                $mass_assignment['recurring_date'] = $order->products[0]->recurring_date;
                                $mass_assignment['subscription_id'] = $order->products[0]->subscription_id;
                                $mass_assignment['next_subscription_product'] = $order->products[0]->next_subscription_product;
                                $mass_assignment['next_subscription_product_id'] = $order->products[0]->next_subscription_product_id;
                                $mass_assignment['next_subscription_product_price'] = $order->products[0]->next_subscription_product_price;
                                $mass_assignment['next_subscription_qty'] = $order->products[0]->next_subscription_qty;
                                $mass_assignment['billing_model_discount'] = $order->products[0]->billing_model_discount;
                                $mass_assignment['is_add_on'] = $order->products[0]->is_add_on;
                                $mass_assignment['is_in_trial'] = $order->products[0]->is_in_trial;
                                $mass_assignment['step_number'] = $order->products[0]->step_number;
                                $mass_assignment['is_shippable'] = $order->products[0]->is_shippable;
                                $mass_assignment['is_full_refund'] = $order->products[0]->is_full_refund;
                                $mass_assignment['refund_amount'] = $order->products[0]->refund_amount;
                                $mass_assignment['on_hold'] = $order->products[0]->on_hold;
                                $mass_assignment['hold_date'] = $order->products[0]->hold_date;
                                if (isset($order->products[0]->billing_model)) {
                                    $mass_assignment['billing_model_id'] = $order->products[0]->billing_model->id;
                                    $mass_assignment['billing_model_name'] = $order->products[0]->billing_model->name;
                                    $mass_assignment['billing_model_description'] = $order->products[0]->billing_model->description;
                                }
                                if (isset($order->products[0]->offer)) {
                                    $mass_assignment['offer_id'] = $order->products[0]->offer->id;
                                    $mass_assignment['offer_name'] = $order->products[0]->offer->name;
                                }

                                OrderProduct::create($mass_assignment);
                            }
                        }
                        $data = null;
                        $results = null;
                    }
                    return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
                } else {
                    $startDate = Carbon::createFromFormat('m/d/Y', $start_date);
                    $endDate = Carbon::createFromFormat('m/d/Y', $end_date);
                    $date_range = CarbonPeriod::create($startDate, $endDate);
                    $date_range->toArray();

                    foreach ($date_range as $day) {
                        $days[] = Carbon::parse($day)->format('m/d/Y');
                    }
                    foreach ($days as $key => $day) {
                        //Order_ids for a single day
                        $start_of_day = Carbon::parse($day)->startOfDay()->format('m/d/Y');
                        $end_of_day = Carbon::parse($day)->endOfDay()->format('m/d/Y');
                        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
                            $url,
                            ['start_date' => $start_of_day, 'end_date' => $end_of_day, 'campaign_id' => 'all', 'criteria' => 'all']
                        )->getBody()->getContents());

                        $total_orders = $api_data->total_orders;
                        if ($total_orders != 0) {
                            $order_ids = $api_data->order_id;
                            //order_view and array of 500 api call
                            $chunked_array = array_chunk($order_ids, 500);
                            // dd($chunked_array);
                            foreach ($chunked_array as $chucked_ids) {
                                $order_view_api = $user->sticky_url . '/api/v1/order_view';
                                $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                                    ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                                $results = $order_views->data;
                                foreach ($results as $order) {
                                    $order->user_id = $user->id;
                                    $month = Carbon::parse($order->acquisition_date)->format('F');
                                    $year = Carbon::parse($order->acquisition_date)->format('Y');
                                    $order->acquisition_month = $month;
                                    $order->acquisition_year = $year;
                                    $order->trx_month = $month;
                                    $order->billing_email = $order->email_address;
                                    $order->billing_telephone = $order->customers_telephone;
                                    $order->shipping_email = $order->email_address;
                                    $order->shipping_telephone = $order->customers_telephone;
                                    if (property_exists($order, 'employeeNotes')) {
                                        $order->employeeNotes = serialize($order->employeeNotes);
                                    }
                                    $order->utm_info = serialize($order->utm_info);
                                    if (property_exists($order, 'products')) {
                                        $order->products = serialize($order->products);
                                    }
                                    if (property_exists($order, 'systemNotes')) {
                                        $order->systemNotes = serialize($order->systemNotes);
                                    }
                                    $order->totals_breakdown = serialize($order->totals_breakdown);
                                    if (in_array($order->order_id, $db_order_ids)) {
                                        $updated_orders++;
                                        $db_order = Order::where(['order_id' => $order->order_id, 'user_id' => $user->id])->first();
                                        $db_order->update((array)$order);

                                        $order->products = unserialize($order->products);
                                        $mass_assignment['order_id'] = $order->order_id;
                                        $mass_assignment['product_id'] = $order->products[0]->product_id;
                                        $mass_assignment['sku'] = $order->products[0]->sku;
                                        $mass_assignment['price'] = $order->products[0]->price;
                                        $mass_assignment['product_qty'] = $order->products[0]->product_qty;
                                        $mass_assignment['name'] = $order->products[0]->name;
                                        $mass_assignment['is_recurring'] = $order->products[0]->is_recurring;
                                        $mass_assignment['is_terminal'] = $order->products[0]->is_terminal;
                                        $mass_assignment['recurring_date'] = $order->products[0]->recurring_date;
                                        $mass_assignment['subscription_id'] = $order->products[0]->subscription_id;
                                        $mass_assignment['next_subscription_product'] = $order->products[0]->next_subscription_product;
                                        $mass_assignment['next_subscription_product_id'] = $order->products[0]->next_subscription_product_id;
                                        $mass_assignment['next_subscription_product_price'] = $order->products[0]->next_subscription_product_price;
                                        $mass_assignment['next_subscription_qty'] = $order->products[0]->next_subscription_qty;
                                        $mass_assignment['billing_model_discount'] = $order->products[0]->billing_model_discount;
                                        $mass_assignment['is_add_on'] = $order->products[0]->is_add_on;
                                        $mass_assignment['is_in_trial'] = $order->products[0]->is_in_trial;
                                        $mass_assignment['step_number'] = $order->products[0]->step_number;
                                        $mass_assignment['is_shippable'] = $order->products[0]->is_shippable;
                                        $mass_assignment['is_full_refund'] = $order->products[0]->is_full_refund;
                                        $mass_assignment['refund_amount'] = $order->products[0]->refund_amount;
                                        $mass_assignment['on_hold'] = $order->products[0]->on_hold;
                                        $mass_assignment['hold_date'] = $order->products[0]->hold_date;
                                        if (isset($order->products[0]->billing_model)) {
                                            $mass_assignment['billing_model_id'] = $order->products[0]->billing_model->id;
                                            $mass_assignment['billing_model_name'] = $order->products[0]->billing_model->name;
                                            $mass_assignment['billing_model_description'] = $order->products[0]->billing_model->description;
                                        }
                                        if (isset($order->products[0]->offer)) {
                                            $mass_assignment['offer_id'] = $order->products[0]->offer->id;
                                            $mass_assignment['offer_name'] = $order->products[0]->offer->name;
                                        }

                                        $order_product = OrderProduct::where(['order_id' => $db_order->order_id, 'user_id' => $user->id])->update($mass_assignment);
                                    } else {
                                        $new_orders++;
                                        Order::create((array)$order);

                                        $order->products = unserialize($order->products);
                                        $mass_assignment['order_id'] = $order->order_id;
                                        $mass_assignment['product_id'] = $order->products[0]->product_id;
                                        $mass_assignment['sku'] = $order->products[0]->sku;
                                        $mass_assignment['price'] = $order->products[0]->price;
                                        $mass_assignment['product_qty'] = $order->products[0]->product_qty;
                                        $mass_assignment['name'] = $order->products[0]->name;
                                        $mass_assignment['is_recurring'] = $order->products[0]->is_recurring;
                                        $mass_assignment['is_terminal'] = $order->products[0]->is_terminal;
                                        $mass_assignment['recurring_date'] = $order->products[0]->recurring_date;
                                        $mass_assignment['subscription_id'] = $order->products[0]->subscription_id;
                                        $mass_assignment['next_subscription_product'] = $order->products[0]->next_subscription_product;
                                        $mass_assignment['next_subscription_product_id'] = $order->products[0]->next_subscription_product_id;
                                        $mass_assignment['next_subscription_product_price'] = $order->products[0]->next_subscription_product_price;
                                        $mass_assignment['next_subscription_qty'] = $order->products[0]->next_subscription_qty;
                                        $mass_assignment['billing_model_discount'] = $order->products[0]->billing_model_discount;
                                        $mass_assignment['is_add_on'] = $order->products[0]->is_add_on;
                                        $mass_assignment['is_in_trial'] = $order->products[0]->is_in_trial;
                                        $mass_assignment['step_number'] = $order->products[0]->step_number;
                                        $mass_assignment['is_shippable'] = $order->products[0]->is_shippable;
                                        $mass_assignment['is_full_refund'] = $order->products[0]->is_full_refund;
                                        $mass_assignment['refund_amount'] = $order->products[0]->refund_amount;
                                        $mass_assignment['on_hold'] = $order->products[0]->on_hold;
                                        $mass_assignment['hold_date'] = $order->products[0]->hold_date;
                                        if (isset($order->products[0]->billing_model)) {
                                            $mass_assignment['billing_model_id'] = $order->products[0]->billing_model->id;
                                            $mass_assignment['billing_model_name'] = $order->products[0]->billing_model->name;
                                            $mass_assignment['billing_model_description'] = $order->products[0]->billing_model->description;
                                        }
                                        if (isset($order->products[0]->offer)) {
                                            $mass_assignment['offer_id'] = $order->products[0]->offer->id;
                                            $mass_assignment['offer_name'] = $order->products[0]->offer->name;
                                        }

                                        OrderProduct::create($mass_assignment);
                                    }
                                }
                                $data = null;
                                $results = null;
                            }
                        }
                    }
                }
            }
        }
        return response()->json(['status' => true, 'Yesterday New Record in todays API' => $new_orders, 'Yesterday orders to be updated in orders table' => $updated_orders]);
    }

    public function get_order_product_mass($order)
    {
        $order->products = unserialize($order->products);
        $result['order_id'] = $order->order_id;
        $result['user_id'] = Auth::id();
        $result['product_id'] = $order->products[0]->product_id;
        $result['sku'] = $order->products[0]->sku;
        $result['price'] = $order->products[0]->price;
        $result['product_qty'] = $order->products[0]->product_qty;
        $result['name'] = $order->products[0]->name;
        $result['is_recurring'] = $order->products[0]->is_recurring;
        $result['is_terminal'] = $order->products[0]->is_terminal;
        $result['recurring_date'] = $order->products[0]->recurring_date;
        $result['subscription_id'] = $order->products[0]->subscription_id;
        $result['next_subscription_product'] = $order->products[0]->next_subscription_product;
        $result['next_subscription_product_id'] = $order->products[0]->next_subscription_product_id;
        $result['next_subscription_product_price'] = $order->products[0]->next_subscription_product_price;
        $result['next_subscription_qty'] = $order->products[0]->next_subscription_qty;
        $result['billing_model_discount'] = $order->products[0]->billing_model_discount;
        $result['is_add_on'] = $order->products[0]->is_add_on;
        $result['is_in_trial'] = $order->products[0]->is_in_trial;
        $result['step_number'] = $order->products[0]->step_number;
        $result['is_shippable'] = $order->products[0]->is_shippable;
        $result['is_full_refund'] = $order->products[0]->is_full_refund;
        $result['refund_amount'] = $order->products[0]->refund_amount;
        $result['on_hold'] = $order->products[0]->on_hold;
        $result['hold_date'] = $order->products[0]->hold_date;
        if (isset($order->products[0]->billing_model)) {
            $result['billing_model_id'] = $order->products[0]->billing_model->id;
            $result['billing_model_name'] = $order->products[0]->billing_model->name;
            $result['billing_model_description'] = $order->products[0]->billing_model->description;
        }
        if (isset($order->products[0]->offer)) {
            $result['offer_id'] = $order->products[0]->offer->id;
            $result['offer_name'] = $order->products[0]->offer->name;
        }
        return $result;
    }

    public static function pull_cron_orders_bk()
    {
        // ini_set('memory_limit', '512M');
        // set_time_limit(0);
        $new_orders = 0;
        $updated_orders = 0;
        $start = Carbon::today();
        $start_date = Carbon::now()->startOfDay()->format('m/d/Y');
        $end_date = Carbon::now()->endOfDay()->format('m/d/Y');
        $db_order_ids = Order::pluck('order_id')->toArray();

        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v1/order_find';

        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
            $url,
            [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'campaign_id' => 'all',
                'criteria' => 'all'
            ]
        )->getBody()->getContents());
        $order_ids = $api_data->order_id;
        $total_orders = $api_data->total_orders;
        // dd($total_orders);
        if ($total_orders < 50000) {
            $chunked_array = array_chunk($order_ids, 500);
            foreach ($chunked_array as $chucked_ids) {
                $order_view_api = 'https://thinkbrain.sticky.io/api/v1/order_view';
                $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                    ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                $results = $order_views->data;
                foreach ($results as $order) {

                    $month = Carbon::parse($order->time_stamp)->format('F');
                    $year = Carbon::parse($order->time_stamp)->format('Y');
                    $order->acquisition_month = $month;
                    $order->acquisition_year = $year;
                    $order->trx_month = $month;
                    $order->billing_email = $order->email_address;
                    $order->billing_telephone = $order->customers_telephone;
                    $order->shipping_email = $order->email_address;
                    $order->shipping_telephone = $order->customers_telephone;
                    if (property_exists($order, 'employeeNotes')) {
                        $order->employeeNotes = serialize($order->employeeNotes);
                    }
                    $order->utm_info = serialize($order->utm_info);
                    if (property_exists($order, 'products')) {
                        $order->products = serialize($order->products);
                    }
                    $order->systemNotes = serialize($order->systemNotes);
                    $order->totals_breakdown = serialize($order->totals_breakdown);
                    if (in_array($order->order_id, $db_order_ids)) {
                        $updated_orders++;
                        $db_order = Order::where(['order_id' => $order->order_id])->first();
                        $db_order->update((array)$order);

                        // $mass_assignment = $this->get_order_product_mass($order);

                        $order->products = unserialize($order->products);
                        $mass_assignment['order_id'] = $order->order_id;
                        $mass_assignment['product_id'] = $order->products[0]->product_id;
                        $mass_assignment['sku'] = $order->products[0]->sku;
                        $mass_assignment['price'] = $order->products[0]->price;
                        $mass_assignment['product_qty'] = $order->products[0]->product_qty;
                        $mass_assignment['name'] = $order->products[0]->name;
                        $mass_assignment['is_recurring'] = $order->products[0]->is_recurring;
                        $mass_assignment['is_terminal'] = $order->products[0]->is_terminal;
                        $mass_assignment['recurring_date'] = $order->products[0]->recurring_date;
                        $mass_assignment['subscription_id'] = $order->products[0]->subscription_id;
                        $mass_assignment['next_subscription_product'] = $order->products[0]->next_subscription_product;
                        $mass_assignment['next_subscription_product_id'] = $order->products[0]->next_subscription_product_id;
                        $mass_assignment['next_subscription_product_price'] = $order->products[0]->next_subscription_product_price;
                        $mass_assignment['next_subscription_qty'] = $order->products[0]->next_subscription_qty;
                        $mass_assignment['billing_model_discount'] = $order->products[0]->billing_model_discount;
                        $mass_assignment['is_add_on'] = $order->products[0]->is_add_on;
                        $mass_assignment['is_in_trial'] = $order->products[0]->is_in_trial;
                        $mass_assignment['step_number'] = $order->products[0]->step_number;
                        $mass_assignment['is_shippable'] = $order->products[0]->is_shippable;
                        $mass_assignment['is_full_refund'] = $order->products[0]->is_full_refund;
                        $mass_assignment['refund_amount'] = $order->products[0]->refund_amount;
                        $mass_assignment['on_hold'] = $order->products[0]->on_hold;
                        $mass_assignment['hold_date'] = $order->products[0]->hold_date;
                        if (isset($order->products[0]->billing_model)) {
                            $mass_assignment['billing_model_id'] = $order->products[0]->billing_model->id;
                            $mass_assignment['billing_model_name'] = $order->products[0]->billing_model->name;
                            $mass_assignment['billing_model_description'] = $order->products[0]->billing_model->description;
                        }
                        if (isset($order->products[0]->offer)) {
                            $mass_assignment['offer_id'] = $order->products[0]->offer->id;
                            $mass_assignment['offer_name'] = $order->products[0]->offer->name;
                        }
                        $order_product = OrderProduct::where(['order_id' => $db_order->order_id])->update($mass_assignment);
                    } else {
                        $new_orders++;
                        Order::create((array)$order);
                        // $mass_assignment = $this->get_order_product_mass($order);
                        $order->products = unserialize($order->products);
                        $mass_assignment['order_id'] = $order->order_id;
                        $mass_assignment['product_id'] = $order->products[0]->product_id;
                        $mass_assignment['sku'] = $order->products[0]->sku;
                        $mass_assignment['price'] = $order->products[0]->price;
                        $mass_assignment['product_qty'] = $order->products[0]->product_qty;
                        $mass_assignment['name'] = $order->products[0]->name;
                        $mass_assignment['is_recurring'] = $order->products[0]->is_recurring;
                        $mass_assignment['is_terminal'] = $order->products[0]->is_terminal;
                        $mass_assignment['recurring_date'] = $order->products[0]->recurring_date;
                        $mass_assignment['subscription_id'] = $order->products[0]->subscription_id;
                        $mass_assignment['next_subscription_product'] = $order->products[0]->next_subscription_product;
                        $mass_assignment['next_subscription_product_id'] = $order->products[0]->next_subscription_product_id;
                        $mass_assignment['next_subscription_product_price'] = $order->products[0]->next_subscription_product_price;
                        $mass_assignment['next_subscription_qty'] = $order->products[0]->next_subscription_qty;
                        $mass_assignment['billing_model_discount'] = $order->products[0]->billing_model_discount;
                        $mass_assignment['is_add_on'] = $order->products[0]->is_add_on;
                        $mass_assignment['is_in_trial'] = $order->products[0]->is_in_trial;
                        $mass_assignment['step_number'] = $order->products[0]->step_number;
                        $mass_assignment['is_shippable'] = $order->products[0]->is_shippable;
                        $mass_assignment['is_full_refund'] = $order->products[0]->is_full_refund;
                        $mass_assignment['refund_amount'] = $order->products[0]->refund_amount;
                        $mass_assignment['on_hold'] = $order->products[0]->on_hold;
                        $mass_assignment['hold_date'] = $order->products[0]->hold_date;
                        if (isset($order->products[0]->billing_model)) {
                            $mass_assignment['billing_model_id'] = $order->products[0]->billing_model->id;
                            $mass_assignment['billing_model_name'] = $order->products[0]->billing_model->name;
                            $mass_assignment['billing_model_description'] = $order->products[0]->billing_model->description;
                        }
                        if (isset($order->products[0]->offer)) {
                            $mass_assignment['offer_id'] = $order->products[0]->offer->id;
                            $mass_assignment['offer_name'] = $order->products[0]->offer->name;
                        }
                        OrderProduct::create($mass_assignment);
                    }
                }
                $data = null;
                $results = null;
            }
            return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
        } else {
            return response()->json(['status' => false, 'message' => 'data exceeded 50000 records']);
        }
    }

    public function pull_daily_order_find()
    {
        $new_orders = 0;
        $updated_orders = 0;
        $start = Carbon::today();
        // $end = Carbon::today()->endOfDay(); 
        $start_date = Carbon::now()->startOfDay()->format('m/d/Y');
        $end_date = Carbon::now()->endOfDay()->format('m/d/Y');
        // var_dump($end_date);die;

        $db_order_ids = Order::pluck('order_id')->toArray();

        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v1/order_find';

        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
            $url,
            [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'campaign_id' => 'all',
                'criteria' => 'all',
                'return_type' => 'order_view'
            ]
        )->getBody()->getContents());

        $order_ids = $api_data->order_id;
        // dd($order_ids);
        if (count($order_ids) < 500) {
            $api_orders = $api_data->data;
            foreach ($api_orders as $key => $order) {
                $orders_arr[] = (array)$order;
            }
            foreach ($orders_arr as $result) {

                $order = new Order();
                // $month = Carbon::parse($result['acquisition_date'])->format('F');
                // $year = Carbon::parse($result['acquisition_date'])->format('Y');
                // $result['acquisition_month'] = $month;
                // $result['acquisition_year'] = $year;
                // $result['trx_month'] = $month;
                // $result['billing_email'] = $result['email_address'];
                // $result['billing_telephone'] = $result['customers_telephone'];
                // $result['shipping_email'] = $result['email_address'];
                // $result['shipping_telephone'] = $result['customers_telephone'];
                // if(array_key_exists('employeeNotes', $result)){
                //     $result['employeeNotes'] = serialize($result['employeeNotes']);
                // }
                // $result['utm_info'] = serialize($result['utm_info']);
                // $result['products'] = serialize($result['products']);
                // $result['systemNotes'] = serialize($result['systemNotes']);
                // $result['totals_breakdown'] = serialize($result['totals_breakdown']);
                if (in_array($result['order_id'], $db_order_ids)) {
                    $updated_orders++;
                    // $order = Order::where(['order_id'=>$result['order_id']])->first();
                    // $order->update($result);
                } else {
                    $new_orders++;
                    // $order->create($result);
                }
            }
            return response()->json(['status' => true, 'New Record API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
        } else if (count($order_ids) < 50000) {

            $chunked_array = array_chunk($order_ids, 500);
            // dd($chunked_array);
            foreach ($chunked_array as $chucked_ids) {
                $order_view_api = 'https://thinkbrain.sticky.io/api/v1/order_view';
                $data[] = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                    ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                // dd($data);
                $data = (array)$data[0]->data;
                foreach ($data as $object) {
                    $results[] = (array)$object;
                }
                foreach ($results as $result) {

                    $order = new Order();
                    // $month = Carbon::parse($result['acquisition_date'])->format('F');
                    // $year = Carbon::parse($result['acquisition_date'])->format('Y');
                    // $result['acquisition_month'] = $month;
                    // $result['acquisition_year'] = $year;
                    // $result['trx_month'] = $month;
                    // $result['billing_email'] = $result['email_address'];
                    // $result['billing_telephone'] = $result['customers_telephone'];
                    // $result['shipping_email'] = $result['email_address'];
                    // $result['shipping_telephone'] = $result['customers_telephone'];
                    // if(array_key_exists('employeeNotes', $result)){
                    //     $result['employeeNotes'] = serialize($result['employeeNotes']);
                    // }
                    // $result['utm_info'] = serialize($result['utm_info']);
                    // $result['products'] = serialize($result['products']);
                    // $result['systemNotes'] = serialize($result['systemNotes']);
                    // $result['totals_breakdown'] = serialize($result['totals_breakdown']);
                    if (in_array($result['order_id'], $db_order_ids)) {
                        $updated_orders++;
                        // $order = Order::where(['order_id'=>$result['order_id']])->first();
                        // $order->update($result);
                    } else {
                        $new_orders++;
                        // $order->create($result);
                    }
                }
                $data = null;
                $results = null;
            }
            return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
        }
    }

    public function pull_yesterday_orders()
    {
        $new_orders = 0;
        $updated_orders = 0;
        $start = Carbon::yesterday();
        // $end = Carbon::today()->endOfDay(); 
        $start_date = $start->startOfDay()->format('m/d/Y');
        $end_date = $start->endOfDay()->format('m/d/Y');
        // var_dump($end_date);die;

        $db_order_ids = Order::pluck('order_id')->toArray();

        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v1/order_find';

        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
            $url,
            [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'campaign_id' => 'all',
                'criteria' => 'all',
            ]
        )->getBody()->getContents());

        $order_ids = $api_data->order_id;
        $total_orders = $api_data->total_orders;

        if ($total_orders < 50000) {

            $chunked_array = array_chunk($order_ids, 500);
            // dd($chunked_array);
            foreach ($chunked_array as $chucked_ids) {
                $order_view_api = 'https://thinkbrain.sticky.io/api/v1/order_view';
                $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                    ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                $results = $order_views->data;
                foreach ($results as $result) {

                    $month = Carbon::parse($result->time_stamp)->format('F');
                    $year = Carbon::parse($result->time_stamp)->format('Y');
                    $result->acquisition_month = $month;
                    $result->acquisition_year = $year;
                    $result->trx_month = $month;
                    $result->billing_email = $result->email_address;
                    $result->billing_telephone = $result->customers_telephone;
                    $result->shipping_email = $result->email_address;
                    $result->shipping_telephone = $result->customers_telephone;
                    if (property_exists($result, 'employeeNotes')) {
                        $result->employeeNotes = serialize($result->employeeNotes);
                    }
                    $result->utm_info = serialize($result->utm_info);
                    if (property_exists($result, 'products')) {
                        $result->products = serialize($result->products);
                    }
                    $result->systemNotes = serialize($result->systemNotes);
                    $result->totals_breakdown = serialize($result->totals_breakdown);
                    if (in_array($result->order_id, $db_order_ids)) {
                        $updated_orders++;
                        $db_order = Order::where(['order_id' => $result->order_id])->first();
                        $db_order->update((array)$result);

                        $mass_assignment = $this->get_order_product_mass($result);
                        $order_product = OrderProduct::where(['order_id' => $db_order->order_id])->update($mass_assignment);
                    } else {
                        $new_orders++;
                        Order::create((array)$result);
                        $mass_assignment = $this->get_order_product_mass($result);
                        OrderProduct::create($mass_assignment);
                    }
                }
                $data = null;
                $results = null;
            }
            return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
        } else {
            return response()->json(['status' => false, 'message' => 'data exceeded 50000 records']);
        }
    }

    public function order_history()
    {
        // ini_set('memory_limit', '512M');
        // set_time_limit(0);
        $new_orders = 0;
        $updated_orders = 0;
        $order_ids = [];
        $pending_orders = [];

        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";

        $starting_day = '2022-06-09';
        $ending_day = '2022-06-14';
        // $start_date = Carbon::parse($starting_day)->startOfDay();
        // $end_date = Carbon::parse($ending_day)->endOfDay();
        $date_range = CarbonPeriod::create($starting_day, $ending_day);
        $date_range->toArray();
        // dd($date_range);

        foreach ($date_range as $day) {
            $month_days[] = $day;
        }
        // dd($month_days);
        foreach ($month_days as $day) {
            $start_day = Carbon::parse($day)->startOfDay();
            $end_day = Carbon::parse($day)->endOfDay();

            $url = 'https://thinkbrain.sticky.io/api/v2/orders/histories?start_at=' . $start_day . '&end_at=' . $end_day;

            $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                ->get($url)->getBody()->getContents());

            if ($api_data->status == "SUCCESS") {
                $last_page = $api_data->last_page;
                $total = $api_data->total;
                $orders = $api_data->data;
                $order_ids = array_merge($order_ids, array_column($orders, 'order_id'));

                // dd($order_ids);
                for ($i = 2; $i <= $last_page; $i++) {
                    $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                        ->get($url . '&page=' . $i)->getBody()->getContents());

                    $orders = $api_data->data;
                    // dd($orders);
                    $order_ids = array_merge($order_ids, array_column($orders, 'order_id'));
                }
                $order_ids = array_unique($order_ids);

                if ($total < 50000) {
                    $chunked_array = array_chunk($order_ids, 500);
                    // dd($chunked_array);
                    foreach ($chunked_array as $chucked_ids) {
                        $order_view_api = 'https://thinkbrain.sticky.io/api/v1/order_view';
                        $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                            ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                        $results = $order_views->data;
                        foreach ($results as $result) {

                            $month = Carbon::parse($result->time_stamp)->format('F');
                            $year = Carbon::parse($result->time_stamp)->format('Y');
                            $result->acquisition_month = $month;
                            $result->acquisition_year = $year;
                            $result->trx_month = $month;
                            $result->billing_email = $result->email_address;
                            $result->billing_telephone = $result->customers_telephone;
                            $result->shipping_email = $result->email_address;
                            $result->shipping_telephone = $result->customers_telephone;
                            if (property_exists($result, 'employeeNotes')) {
                                $result->employeeNotes = serialize($result->employeeNotes);
                            }
                            $result->utm_info = serialize($result->utm_info);
                            if (property_exists($result, 'products')) {
                                $result->products = serialize($result->products);
                            }
                            $result->systemNotes = serialize($result->systemNotes);
                            $result->totals_breakdown = serialize($result->totals_breakdown);
                            //update
                            $updated_orders++;
                            $db_order = Order::where(['order_id' => $result->order_id])->first();
                            if ($db_order) {
                                $db_order->update((array)$result);
                                $mass_assignment = $this->get_order_product_mass($result);
                                $order_product = OrderProduct::where(['order_id' => $db_order->order_id])->update($mass_assignment);
                            } else {
                                array_push($pending_orders, $result->order_id);
                                $new_orders++;
                                Order::create((array)$result);
                                $mass_assignment = $this->get_order_product_mass($result);
                                OrderProduct::create($mass_assignment);
                            }
                            // dd('die');
                        }
                        $data = null;
                        $results = null;
                        $order_ids = [];
                        // $pending_orders = [];
                    }
                } else {
                    return response()->json(['status' => false, 'message' => 'data exceeded 50000 records']);
                }
            }
        }
        return response()->json(['status' => true, 'New Orders in todays API' => $new_orders, 'Updated orders:' => $updated_orders, 'New Pending Orders: ' => $pending_orders]);
    }
    public function daily_order_history()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $new_orders = 0;
        $updated_orders = 0;
        $order_ids = [];

        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $start_date = Carbon::now()->startOfDay();
        $end_date = Carbon::now()->endOfDay();
        $url = 'https://thinkbrain.sticky.io/api/v2/orders/histories?start_at=' . $start_date . '&end_at=' . $end_date;

        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
            ->get($url)->getBody()->getContents());

        if ($api_data->status == "SUCCESS") {
            $last_page = $api_data->last_page;
            $total = $api_data->total;
            $orders = $api_data->data;
            $order_ids = array_merge($order_ids, array_column($orders, 'order_id'));

            for ($i = 2; $i <= $last_page; $i++) {
                $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                    ->get($url . '&page=' . $i)->getBody()->getContents());

                $orders = $api_data->data;
                // dd($orders);
                $order_ids = array_merge($order_ids, array_column($orders, 'order_id'));
            }
            $order_ids = array_unique($order_ids);
            // dd($order_ids);

            if ($total < 50000) {

                $chunked_array = array_chunk($order_ids, 500);
                // dd($chunked_array);
                foreach ($chunked_array as $chucked_ids) {
                    $order_view_api = 'https://thinkbrain.sticky.io/api/v1/order_view';
                    $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                        ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                    $results = $order_views->data;
                    foreach ($results as $result) {

                        $month = Carbon::parse($result->time_stamp)->format('F');
                        $year = Carbon::parse($result->time_stamp)->format('Y');
                        $result->acquisition_month = $month;
                        $result->acquisition_year = $year;
                        $result->trx_month = $month;
                        $result->billing_email = $result->email_address;
                        $result->billing_telephone = $result->customers_telephone;
                        $result->shipping_email = $result->email_address;
                        $result->shipping_telephone = $result->customers_telephone;
                        if (property_exists($result, 'employeeNotes')) {
                            $result->employeeNotes = serialize($result->employeeNotes);
                        }
                        $result->utm_info = serialize($result->utm_info);
                        if (property_exists($result, 'products')) {
                            $result->products = serialize($result->products);
                        }
                        $result->systemNotes = serialize($result->systemNotes);
                        $result->totals_breakdown = serialize($result->totals_breakdown);
                        //update
                        $updated_orders++;
                        $db_order = Order::where(['order_id' => $result->order_id])->first();
                        $db_order->update((array)$result);

                        $mass_assignment = $this->get_order_product_mass($result);
                        OrderProduct::where(['order_id' => $db_order->order_id])->update($mass_assignment);
                    }
                    $data = null;
                    $results = null;
                }
                return response()->json(['status' => true, 'New Record in todays API' => $new_orders, 'Previous orders to be updated in orders table' => $updated_orders]);
            } else {
                return response()->json(['status' => false, 'message' => 'data exceeded 50000 records']);
            }
        }
    }

    public function insert_missing_history()
    {
        return view('insert-order-view');
    }

    public function insert_missing_result(Request $request)
    {

        $orders_array = $request->orders_array;
        $orders_array = str_replace(array('[', ']', '"', "'"), '', $orders_array);
        // var_dump($orders_array);die;
        $orders_array = explode(',', $orders_array);
        // dd($orders_array);
        $new_orders = 0;
        $updated_orders = 0;
        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $order_view_api = 'https://thinkbrain.sticky.io/api/v1/order_view';

        if (count($orders_array) < 50000) {

            $chunked_array = array_chunk($orders_array, 500);
            // dd($chunked_array);
            foreach ($chunked_array as $chucked_ids) {
                $order_views = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')
                    ->post($order_view_api, ['order_id' => $chucked_ids])->getBody()->getContents());

                if (count($orders_array) > 1) {
                    $results = $order_views->data;
                } else {
                    $results[0] = $order_views;
                }

                foreach ($results as $result) {
                    $month = Carbon::parse($result->time_stamp)->format('F');
                    $year = Carbon::parse($result->time_stamp)->format('Y');
                    $result->acquisition_month = $month;
                    $result->acquisition_year = $year;
                    $result->trx_month = $month;
                    $result->billing_email = $result->email_address;
                    $result->billing_telephone = $result->customers_telephone;
                    $result->shipping_email = $result->email_address;
                    $result->shipping_telephone = $result->customers_telephone;
                    if (property_exists($result, 'employeeNotes')) {
                        $result->employeeNotes = serialize($result->employeeNotes);
                    }
                    $result->utm_info = serialize($result->utm_info);
                    if (property_exists($result, 'products')) {
                        $result->products = serialize($result->products);
                    }
                    $result->systemNotes = serialize($result->systemNotes);
                    $result->totals_breakdown = serialize($result->totals_breakdown);
                    $db_order = Order::where(['order_id' => $result->order_id])->first();
                    if ($db_order) {
                        $updated_orders++;
                        $db_order->update((array)$result);
                        $mass_assignment = $this->get_order_product_mass($result);
                        $order_product = OrderProduct::where(['order_id' => $db_order->order_id])->update($mass_assignment);
                    } else {
                        // array_push($pending_orders, $result->order_id);
                        $new_orders++;
                        Order::create((array)$result);
                        $mass_assignment = $this->get_order_product_mass($result);
                        OrderProduct::create($mass_assignment);
                    }
                }
            }
        }
        $response['new_orders'] = $new_orders;
        $response['updated_orders'] = $updated_orders;
        return view('history-response-view', $response);
    }

    public function add_ip_details()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $order = new Order();
        Order::chunk(1000, function ($orders) {
            foreach ($orders as $order) {
                $ip_address = $order->ip_address;
                if ($ip_address != null) {
                    $url = 'http://ip-api.com/json/' . $ip_address;
                    $ip_details = json_decode(Http::get($url)->getBody()->getContents());
                    $order->ip_details = $ip_details;
                    $order->save();
                }
            }
        });
        return response()->json(['message' => 'IP Details are added in th correspond ips']);
    }
}
