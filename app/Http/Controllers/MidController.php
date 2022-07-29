<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Carbon\Carbon;
use App\Models\Mid;
use App\Models\User;
use App\Models\Order;
use App\Models\Decline;
use App\Models\Profile;
use App\Models\Campaign;
use App\Models\MidCount;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class MidController extends Controller
{
    public function index(Request $request)
    {
        DB::enableQueryLog();
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if ($start_date != null && $end_date != null) {
            $start_date = Carbon::parse($start_date)->startOfDay();
            $end_date = Carbon::parse($end_date)->endOfDay();
        }
        if (isset($request->search) && $request->search != '') {
            $query = $query->search($request->search, null, true, true);
        }
        if ($request->selected_mids) {
            $selected_mids = explode(",", $request->selected_mids);
            // $query = DB::table('mids')->where(['orders.user_id' => 2, 'mids.is_deleted' => 0])->whereIn('mids.gateway_id', $selected_mids);
            $query = DB::table('mids')->where(['orders.user_id' => Auth::id(), 'mids.is_deleted' => 0])->whereIn('mids.gateway_id', $selected_mids);
        } else {
            // $query = DB::table('mids')->where(['orders.user_id' => 2, 'mids.is_deleted' => 0]);
            $query = DB::table('mids')->where(['orders.user_id' => Auth::id(), 'mids.is_deleted' => 0]);
        }
        $query = $query->join('orders', function ($join) use ($start_date, $end_date) {
            $join->on('orders.gateway_id', '=', 'mids.gateway_id')
                // ->where('orders.user_id', '=', 2)
                ->where('orders.user_id', '=', Auth::id())
                ->where('orders.time_stamp', '>=', $start_date)
                ->where('orders.time_stamp', '<=', $end_date)
                ->where('orders.is_test_cc', 0);
        })
            ->select(DB::raw('mids.*'))
            ->addSelect(DB::raw('COUNT(orders.id) as total_count'))
            ->addSelect(DB::raw('Round(SUM(case when orders.order_status = 2 or orders.order_status = 8 then orders.order_total else 0 end) - sum(case when (orders.order_status = 2 or orders.order_status = 8) and orders.amount_refunded_to_date > 0 then orders.amount_refunded_to_date else 0 end), 2) as gross_revenue'))
            ->selectRaw("count(case when orders.order_status = 2 or orders.order_status = 8 then 1 end) as mid_count")
            ->selectRaw("count(case when orders.order_status = 7 then 1 end) as decline_per")
            ->selectRaw("count(case when orders.is_refund = 'yes' then 1 end) as refund_per")
            ->selectRaw("count(case when orders.is_void = 'yes' then 1 end) as void_per")
            ->selectRaw("count(case when orders.is_chargeback = 1 then 1 end) as chargeback_per")
            ->addSelect('mids.mid_group as group_name')
            // ->where('mids.user_id', '=', 2)
            ->where('mids.user_id', '=', Auth::id())
            ->where('orders.is_test_cc', 0)
            ->groupBy('mids.id');

        if ($request->product_id != null) {
            $nameArray = explode(",", $request->product_id);
            $query->join('order_products', 'orders.order_id', '=', 'order_products.order_id')->whereIn('order_products.name', $nameArray);
        }
        $data = $query->get();
        // dd(DB::getQueryLog());
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function products()
    {
        DB::statement("SET SQL_MODE=''");
        $products = DB::table('order_products')->select('id', 'name')->groupBy('name')->get();
        return response()->json(['status' => true, 'data' => $products]);
    }

    public function getProductForFilter(Request $request)
    {
        DB::statement("SET SQL_MODE=''");
        $start_date = Carbon::parse($request->start_date)->startOfDay();
        $end_date = Carbon::parse($request->end_date)->endOfDay();

        $products = DB::table('order_products')
            ->join('orders', 'orders.order_id', '=', 'order_products.order_id')
            ->where('orders.time_stamp', '>=', $start_date)
            ->where('orders.time_stamp', '<=', $end_date)
            ->select('order_products.id', 'order_products.name')->groupBy('order_products.name')->get();
        return response()->json(['status' => true, 'data' => $products]);
    }

    public function get_mid_count_detail(Request $request)
    {
        DB::enableQueryLog();
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if ($start_date != null && $end_date != null) {
            $start_date = Carbon::parse($start_date)->startOfDay();
            $end_date = Carbon::parse($end_date)->endOfDay();
        }
        $gateway_id = $request->gateway_id;
        $status = $request->status;

        $array = [];
        $query = DB::table('orders')
            ->where('orders.user_id', Auth::id())
            ->where('orders.gateway_id', $gateway_id)
            ->where('orders.time_stamp', '>=', $start_date)
            ->where('orders.time_stamp', '<=', $end_date)
            ->join('order_products', 'orders.order_id', '=', 'order_products.order_id')
            ->select('order_products.name as name')
            ->addSelect(DB::raw('COUNT(order_products.name) as total_count'));
        if ($request->type == 'initials') {
            $query->where('order_products.name', 'LIKE', '%(c)%');
        } else if ($request->type == 'subscr') {
            $query->where('orders.is_recurring', 1);
        } else {
            $query->where("orders.$request->type", $status);
        }
        if ($request->product != null) {
            $nameArray = explode(",", $request->product);
            $query->whereIn("order_products.name", $nameArray);
        }
        $details = $query->groupBy('order_products.name')->get();
            // dd(DB::getQueryLog());
        foreach ($details as $detail) {
            $data['name'] = $detail->name;
            $data['total_count'] = $detail->total_count;
            if ($request->total_count) {
                $data['percentage'] = round(($detail->total_count / $request->total_count) * 100, 2);
            } else {
                $data['percentage'] = 0;
            }
            array_push($array, $data);
        }
        return response()->json(['status' => true, 'data' => $array]);
    }

    public function show($alias)
    {
        $data = Profile::where(['alias' => $alias])->first();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function mids_order_total($id)
    {
        // DB::enableQueryLog();

        $mid = Mid::where(['id' => $id])->first();
        // date for 03/29/22 records
        $start_date = Carbon::now()->subDays(4)->startOfDay();
        $end_date = Carbon::now()->subDays(4)->endOfDay();
        // $daily_revenue = DB::table('orders')->whereBetween('acquisition_date', [$start_date, $end_date])->where(['order_status' => 2, 'gateway_id' => $mid->gateway_id])->sum('order_total');
        // $mid->daily_revenue = Order::whereBetween(DB::raw('DATE(acquisition_date)'), [$start_date, $end_date])->where(['order_status' => 2, 'gateway_id' => $mid->gateway_id])->select(
        //     DB::raw('sum(order_total) as order_total')
        // )->first();
        // $daily_revenue = DB::table('orders')->whereDate('acquisition_date', '>=', $start_date)->whereDate('acquisition_date', '<=', $end_date)->where(['order_status' => 2, 'gateway_id'=>$mid->gateway_id])->sum('order_total');
        // $daily_revenue = DB::table('orders')->whereBetween(DB::raw('DATE(acquisition_date)'), [$start_date, $end_date])->where(['order_status' => 2, 'gateway_id'=>$mid->gateway_id])->sum('order_total');
        $daily_revenue = DB::table('orders')->where('acquisition_date', '>=', $start_date)->where('acquisition_date', '<=', $end_date)->where(['order_status' => 2, 'gateway_id' => $mid->gateway_id])->sum('order_total');
        // dd(DB::getQueryLog());
        // return $daily_revenue;
        $mid->daily_revenue = round($daily_revenue, 2);
        return response()->json(['status' => true, 'data' => $mid]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mid  $mid
     * @return \Illuminate\Http\Response
     */
    public function edit(Mid $mid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mid  $mid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mid $mid)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mid  $mid
     * @return \Illuminate\Http\Response
     */
    public function destroy($alias)
    {
        $profile = Profile::where('alias', $alias)->first();
        DB::table('profiles')->where('alias', $alias)->update(['global_fields->mid_group' => '']);
        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v2/providers/payment/profiles/' . $profile->profile_id;
        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->put(
            $url,
            [
                "fields" => [
                    "global_fields" => [
                        "mid_group" => ''
                    ]
                ]
            ]
        )->getBody()->getContents());
        return response()->json(['status' => true, 'message' => 'Mid-group removed from' . $profile->alias]);
        // dd('die');
    }
    public function pull_payment_router_view(Request $request)
    {
        $new_gateways = 0;
        $updated_gateways = 0;
        // $affected = DB::table('mids')->update(['is_active' => 0]);
        $db_gateway_ids = Mid::all()->pluck('gateway_id')->toArray();
        // Mid::where('user_id', 2)->update(['is_active' => 0]);
        Mid::where('user_id', Auth::id())->update(['is_active' => 0]);
        // $user = User::find(2);
        $user = User::find($request->user()->id);
        $username = $user->sticky_api_username;
        $password = Crypt::decrypt($user->sticky_api_key);
        $url = $user->sticky_url . '/api/v1/payment_router_view';
        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post($url, ['payment_router_id' => 1, 'gateway_status' => 1])->getBody()->getContents());
        $router = $api_data;
        $gatewayArr = [];
        if ($router) {
            // foreach ($routers as $router) {
            $gateways = $router->gateways;

            foreach ($gateways as $gateway) {
                if ($gateway->gateway_status == 'Active') {
                    $is_active = 1;
                } else {
                    $is_active = 0;
                }
                array_push($gatewayArr, $gateway->gateway_id);
                if (in_array($gateway->gateway_id, $db_gateway_ids)) {
                    $update = Mid::where(['gateway_id' => $gateway->gateway_id])->first();
                    $update->router_id = $router->id;
                    // $update->user_id = 2;
                    $update->user_id = Auth::id();
                    $update->router_name = $router->name;
                    $update->router_date_in = $router->date_in;
                    $update->router_desc = $router->description;
                    $update->mid_group_setting_id = $router->mid_group_setting_id;
                    $update->mid_group_setting = $router->mid_group_setting;
                    $update->is_three_d_routed = $router->is_three_d_routed;
                        // $gateway->is_active = 1;
                    $update->initials = $gateway->initial_order_count;
                    $update->subscr = $gateway->rebill_order_count;
                    $update->is_strict_preserve = $router->is_strict_preserve;
                    $update->is_active = $is_active;
                    $update->save();
                    $updated_gateways++;
                } else {
                    $mid = new Mid();
                    $mid->router_id = $router->id;
                    // $mid->user_id = 2;
                    $mid->user_id = Auth::id();
                    $mid->router_name = $router->name;
                    $mid->router_date_in = $router->date_in;
                    $mid->router_desc = $router->description;
                    $mid->mid_group_setting_id = $router->mid_group_setting_id;
                    $mid->mid_group_setting = $router->mid_group_setting;
                    $mid->is_three_d_routed = $router->is_three_d_routed;
                        // $gateway->is_active = 1;
                    $mid->initials = $gateway->initial_order_count;
                    $mid->subscr = $gateway->rebill_order_count;
                    $mid->is_strict_preserve = $router->is_strict_preserve;
                    $mid->is_active = $is_active;
                    $mid->save();
                    $new_gateways++;
                }
            }
            // }
            Mid::whereNotIn('gateway_id', $gatewayArr)->where('user_id', Auth::id())->update(['is_deleted' => 1]);
        }
        // app(\App\Http\Controllers\ProfileController::class)->update_profiles();
        return response()->json(['status' => true, 'data' => ['new_mids' => $new_gateways, 'user_id' => Auth::id(), 'updated_mids' => $updated_gateways]]);
    }

    public function assign_mid_group(Request $request)
    {
        $mid = Mid::where('gateway_alias', $request->alias)->first();
        $mid->update(['mid_group' => $request->group_name]);
        return response()->json(['status' => true, 'message' => $request->group_name . ' Assigned as Mid-group to ' . $mid->gateway_alias]);
    }

    public function assign_bulk_group(Request $request)
    {
        // dd($request->all());
        $data = $request->all();
        $alias = array_column($data, 'gateway_alias');
        $total_mids = count($alias);
        if ($request->group_name == '') {
            DB::table('mids')->whereIn('gateway_alias', $alias)->update(['mid_group' => '']);
            $mids = DB::table('mids')->whereIn('gateway_alias', $alias)->get();
            return response()->json(['status' => true, 'message' => 'Unassigned group to ' . $total_mids . ' mids']);
        } else {
            DB::table('mids')->whereIn('gateway_alias', $alias)->update(['mid_group' => $request->group_name]);
            $mids = DB::table('mids')->whereIn('gateway_alias', $alias)->get();
            return response()->json(['status' => true, 'message' => $request->group_name . ' Assigned as Mid-group to ' . $total_mids . ' mids ']);
        }
    }

    public function get_first_mid()
    {
        $data = Profile::first();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function get_mids_decline_data()
    {
        $data = Mid::all();
        foreach ($data as $mid) {
            $data = [];
            $data['gateway_id'] = $mid->gateway_id;
            $data['gateway_alias'] = $mid->gateway_alias;
            $declined_orders = Order::with('product')->where(['gateway_id' => $mid->gateway_id, 'prepaid_match' => 'NO', 'is_test_cc' => 0, 'order_status' => 7]);
            $total_orders = $declined_orders->count();
            $data['decline_per'] = ($total_orders) / 100;
            $product_names = $declined_orders->get()->countBy('product.name')->toArray();
            foreach ($product_names as $name => $count) {
                $decline_data[] = (object)array(
                    'name' => $name,
                    'count' => $count,
                    'percentage' => round((($count / $total_orders) * 100), 2)
                );
            }
            $decline_data['total'] = $total_orders;
            $data['decline_data'] = $decline_data;
            $data['total_declined'] = $total_orders;
            $decline = Decline::create($data);
            $mid->decline_id = $decline->id;
            $mid->save();
            $decline_data = null;
        }
        return response()->json(['status' => true]);
    }

    public function get_mids_count_data()
    {
        $data = Mid::all();
        foreach ($data as $mid) {
            $data = [];
            $data['gateway_id'] = $mid->gateway_id;
            $data['gateway_alias'] = $mid->gateway_alias;
            $declined_orders = Order::with('product')->where(['gateway_id' => $mid->gateway_id, 'order_status' => 2]);
            $mid_count = $declined_orders->count();
            // $data['decline_per'] = ($total_orders) / 100;
            $product_names = $declined_orders->get()->countBy('product.name')->toArray();
            foreach ($product_names as $name => $count) {
                $mid_count_data[] = array(
                    'name' => $name,
                    'count' => $count,
                    'percentage' => round((($count / $mid_count) * 100), 2)
                );
            }
            $data['mid_count_data'] = $mid_count_data;
            $data['mid_count'] = $mid_count;
            $model = MidCount::create($data);
            $mid->mid_count_id = $model->id;
            $mid->save();
            $mid_count_data = null;
        }
        return response()->json(['status' => true]);
    }

    public function sub_affiliate_gross_revenue(Request $request)
    {
        if ($request->start_date != '' && $request->end_date != '') {
            $sub_affiliates = $request->data;
            $start_date = Carbon::parse($request->start_date)->startOfDay();
            $end_date = Carbon::parse($request->end_date)->endOfDay();

            $query = DB::table('orders')
                ->where('time_stamp', '>=', $start_date)
                ->where('time_stamp', '<=', $end_date)
                ->where('order_status', '=', 2)
                ->where('c1', '=', '8071')
                ->addSelect(DB::raw('ROUND(SUM(order_total), 2) as gross_revenue'));
            $response = $query->get();
            return response()->json(['status' => true, 'data' => $response]);
        }
    }

    public function get_active_mids()
    {
        $data = Mid::where(['is_deleted' => 0])->where('user_id', Auth::id())->get();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function reset_initials()
    {
        $date_now = Carbon::now();
        DB::table('settings')->where(['key' => 'order_limit_reset_date'])->update(['value' => $date_now]);
        DB::table('mids')->update(['initials' => 0, 'subscr' => 0]);
        return response()->json(['status' => true, 'message' => 'Initials Reset Successfully']);
    }
}
