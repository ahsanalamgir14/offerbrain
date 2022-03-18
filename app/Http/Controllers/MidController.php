<?php

namespace App\Http\Controllers;

use App\Models\Mid;
use App\Models\Profile;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;

class MidController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Mid::select('*');
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $end_date = date('Y-m-d', strtotime($request->end_date));
        if ($start_date != null && $start_date != "1970-01-01" && $end_date != null && $end_date != "1970-01-01"){
            $query->whereBetween('created_on', [$start_date.' 00:00:00', $end_date.' 23:59:59']);
        }
        $data = $query->get();

        foreach ($data as $mid) {
            $mid->global_fields = Profile::where(['alias' => $mid->gateway_alias])->pluck('global_fields')->first();
            // $mid->mid_count = Order::where(['gateway_id'=>$mid->gateway_id])->count();
        }
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
     * @param  \App\Models\Mid  $mid
     * @return \Illuminate\Http\Response
     */
    public function show($alias)
    {
        $data = Profile::where(['alias' => $alias])->first();
        return response()->json(['status' => true, 'data' => $data]);
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
        return response()->json(['status' => true, 'message' => 'Unassigned Mid-group removed for' . $profile->alias]);
        // dd('die');
    }
    public function pull_payment_router_view()
    {
        // dd('die');
        // return response()->json(['status' => false, '']);
        $new_gateways = 0;
        $updated_gateways = 0;
        $db_gateway_alias = Mid::all()->pluck('gateway_alias')->toArray();
        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v1/payment_router_view';

        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post($url, ['payment_router_id' => 1])->getBody()->getContents());
        $routers = $api_data->data;
        // dd($routers);
        if ($routers) {
            //adding or updating page 1 campaigns
            foreach ($routers as $router) {
                $gateways = $router->gateways;

                foreach ($gateways as $gateway) {
                    // dd($gateway);
                    if (in_array($gateway->gateway_alias, $db_gateway_alias)) {
                        $update = Mid::where(['gateway_alias' => $gateway->gateway_alias])->first();
                        $gateway->router_id = $router->id;
                        $gateway->router_name = $router->name;
                        $gateway->router_date_in = $router->date_in;
                        $gateway->router_desc = $router->description;
                        $gateway->mid_group_setting_id = $router->mid_group_setting_id;
                        $gateway->mid_group_setting = $router->mid_group_setting;
                        $gateway->is_three_d_routed = $router->is_three_d_routed;
                        $gateway->is_strict_preserve = $router->is_strict_preserve;
                        $update->update((array)$gateway);
                        $updated_gateways++;
                    } else {
                        $mid = new Mid();
                        $gateway->router_id = $router->id;
                        $gateway->router_name = $router->name;
                        $gateway->router_date_in = $router->date_in;
                        $gateway->router_desc = $router->description;
                        $gateway->mid_group_setting_id = $router->mid_group_setting_id;
                        $gateway->mid_group_setting = $router->mid_group_setting;
                        $gateway->is_three_d_routed = $router->is_three_d_routed;
                        $gateway->is_strict_preserve = $router->is_strict_preserve;
                        $mid->create((array)$gateway);
                        $new_gateways++;
                    }
                }
            }
        }
        return response()->json(['status' => true, 'data' => ['new_mids' => $new_gateways, 'updated_mids' => $updated_gateways]]);
    }
    public function get_gateway_ids()
    {
        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v1/gateway_view';
        $gateways = Mid::pluck('gateway_id')->toArray();

        foreach ($gateways as $id) {
            $data = json_decode(Http::withBasicAuth($username, $password)->accept('application/json')->post($url, ['gateway_id' => $id])->getBody()->getContents());
            dd($data);
        }
    }

    public function assign_mid_group(Request $request)
    {
        // dd($request->group_name);
        $profile = Profile::where('alias', $request->alias)->first();
        DB::table('profiles')->where('alias', $request->alias)->update(['global_fields->mid_group' => $request->group_name]);
        return response()->json(['status' => true, 'message' => $request->group_name . ' Assigned as Mid-group to ' . $profile->alias]);
    }
    public function get_first_mid()
    {
        $data = Profile::first();
        return response()->json(['status' => true, 'data' => $data]);
    }
    public function refresh_mid_count()
    {
        $data = Mid::all();
        foreach ($data as $mid) {
            $mid->mid_count = Order::where(['gateway_id' => $mid->gateway_id])->count();
            $mid->save();
        }
        return response()->json(['status' => true, 'data' => ['message' => "Mid Counts Refreshed Successfully."]]);
    }

}
