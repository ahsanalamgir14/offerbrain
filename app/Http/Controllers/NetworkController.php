<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use GuzzleHttp\Client;
use App\Models\Network;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Auth;

class NetworkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        DB::statement("SET SQL_MODE=''");
        if ($request->start_date != '' && $request->end_date != '') {
            $start_date = Carbon::parse($request->start_date)->startOfDay();
            $end_date = Carbon::parse($request->end_date)->endOfDay();
           
            $query = DB::table('networks')->where(['networks.user_id' => Auth::id()])
                ->select(
                    'networks.*',
                    'orders.id',
                    'orders.order_status',
                    'orders.order_total',
                    'orders.is_refund',
                    'orders.affid',
                    'networks.network_affiliate_id',
                    'orders.time_stamp'
                )
                ->leftJoin('orders', function ($join) use ($start_date, $end_date) {
                    $join->on('orders.affid', '=', 'networks.network_affiliate_id')
                        ->where('orders.user_id', Auth::id())
                        ->where('orders.time_stamp', '>=', $start_date)
                        ->where('orders.time_stamp', '<=', $end_date)
                        ->where('orders.is_test_cc', 0);
                })
                ->selectRaw('COUNT(case when orders.order_status = 2 then orders.id end) as total_count')
                ->selectRaw('ROUND(SUM(case when orders.order_status = 2 OR orders.order_status = 8 then orders.order_total end) - sum(case when orders.order_status = 2 OR orders.order_status = 8 AND orders.amount_refunded_to_date > 0 then orders.amount_refunded_to_date else 0 end), 2) as gross_revenue')
                ->selectRaw("ROUND(COUNT(case when orders.upsell_product_quantity > 1 then 0 end), 2) as upsell_per")
                ->selectRaw("ROUND(COUNT(case when orders.is_chargeback = 1 then 0 end) , 2) as chargeback_per")
                ->selectRaw("ROUND(COUNT(case when orders.is_refund = 'yes' then 0 end), 2) as refund_per")
                ->selectRaw('COUNT(case when orders.products NOT LIKE "%(c)%" then 0 end) as rebill_per')
                ->groupBy('networks.id')
                ->orderBy('networks.id', 'ASC');

            if ($request->fields != null) {
                $field_array = explode(',', $request->fields);
                $value_array = explode(',', $request->values);
                for ($i = 0; $i < count($value_array); $i++) {
                    if ($value_array[$i] != '') {
                        $query->where($field_array[$i], $value_array[$i]);
                    }
                }
            }
            $data['affiliates'] = $query->get();
        } else {
            $data['affiliates'] = Network::where('user_id', Auth::id())->get();
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
     * @param  \App\Models\Network  $affiliate
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $affiliate = Network::where(['user_id' => Auth::id(), 'network_affiliate_id' => $id])->first();
        return response()->json(['status' => true, 'data' => $affiliate]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Network  $affiliate
     * @return \Illuminate\Http\Response
     */
    public function edit(Network $affiliate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Network  $affiliate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Network $affiliate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Network  $affiliate
     * @return \Illuminate\Http\Response
     */
    public function destroy_affiliates(Request $request)
    {
        $id = $request->all();
        $is_true = Network::where('id', $id)->delete();
        if ($is_true) {
            return response()->json(['status' => true, 'message' => '<b>1</b> Network Deleted Successfully']);
        }
    }
    public function pull_affiliates(Request $request)
    {
        $new_affiliates = 0;
        $updated_affiliates = 0;
        $key = "X-Eflow-API-Key";
        $user = User::find($request->user()->id);
        $username = $user->sticky_api_username;
        if ($user->everflow_api_key) {
            $value = Crypt::decrypt($user->everflow_api_key);
        } else {
            return response()->json(['status' => false, 'message' => 'No API key found for Everflow']);
        }
        $url = 'https://api.eflow.team/v1/networks/affiliates';
        $api_data = json_decode(Http::withHeaders([$key => $value])->accept('application/json')->get($url)->body());
        $affiliates = $api_data->affiliates;
        $paging = $api_data->paging;

        if ($affiliates) {
            foreach ($affiliates as $affiliate) {
                $affiliate->user_id = Auth::id();
                $network = Network::where(['user_id' => Auth::id(), 'network_affiliate_id' => $affiliate->network_affiliate_id])->first();
                if ($network) {
                    $network->update((array)$affiliate);
                    $updated_affiliates++;
                } else {
                    Network::create((array)$affiliate);
                    $new_affiliates++;
                }
            }
            $networks = DB::table('networks')->select('id', 'network_affiliate_id', 'network_id', 'name')->where(['user_id' => Auth::id()])->groupBy('network_affiliate_id')->get();

            return response()->json(
                [
                    'status' => true,
                    'data' => [
                        'user_id' => Auth::id(),
                        'new_affiliates' => $new_affiliates,
                        'updated_affiliates' => $updated_affiliates,
                        'networks' => $networks
                    ]
                ]
            );
        }
    }
    public static function pull_affiliates_for_cron()
    {
        $users = User::orderBy('id', 'asc')->get();
        foreach ($users as $user) {
            $new_affiliates = 0;
            $updated_affiliates = 0;
            $key = "X-Eflow-API-Key";
            if ($user->everflow_api_key) {
                $value = Crypt::decrypt($user->everflow_api_key);

                $url = 'https://api.eflow.team/v1/networks/affiliates';
                $api_data = json_decode(Http::withHeaders([$key => $value])->accept('application/json')->get($url)->body());
                if (isset($api_data->affiliates)) {
                    $affiliates = $api_data->affiliates;
                }

                if (isset($affiliates) && $affiliates != '') {
                    foreach ($affiliates as $affiliate) {
                        $affiliate->user_id = $user->id;
                        $network = Network::where(['user_id' => $user->id, 'network_affiliate_id' => $affiliate->network_affiliate_id])->first();
                        if ($network) {
                            $network->update((array)$affiliate);
                            $updated_affiliates++;
                        } else {
                            Network::create((array)$affiliate);
                            $new_affiliates++;
                        }
                    }
                } else {
                    $new_affiliates = 0;
                    $updated_affiliates = 0;
                }
            }
        }
        return response()->json(
            [
                'status' => true,
                'data' => [
                    'New Affiliates: ' => $new_affiliates,
                    'Updates Affiliates: ' => $updated_affiliates
                ]
            ]
        );
    }
}
