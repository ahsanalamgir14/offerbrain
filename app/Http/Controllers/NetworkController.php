<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\Network;
use App\Models\Order;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use DB;

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

            // $query = Order::where('time_stamp', '>=', $start_date)
            //         ->where('time_stamp', '<=', $end_date)
            //         ->where('order_status', '=', 7)
            //         ->where('affiliate', '=', 1)
            //         ->select('order_status', 'order_total')
            //         ->addSelect(DB::raw('ROUND(SUM(order_total), 2) as gross_revenue'))->count();
            // dd($query);

            $query = DB::table('networks')
                ->select('networks.*')
                ->join('orders', function ($join) use ($start_date, $end_date) {
                    $join->on('orders.affiliate', '=', 'networks.network_affiliate_id')
                        ->where('orders.time_stamp', '>=', $start_date)
                        ->where('orders.time_stamp', '<=', $end_date)
                        ->where('orders.order_status', '=', 2)
                        ->select('orders.order_status', 'orders.order_total');
                })
                ->addSelect(DB::raw('ROUND(SUM(orders.order_total), 2) as gross_revenue'))
                ->groupBy('networks.network_affiliate_id');

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
            // dd(DB::getQueryLog());
        } else {
            $data['affiliates'] = Network::all();
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
        $affiliate = Network::where(['network_affiliate_id' => $id])->first();
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
        // $is_true = DB::table('affiliates')->where('id', $id)->delete();
        $is_true = Network::where('id', $id)->delete();
        if ($is_true) {
            return response()->json(['status' => true, 'message' => '<b>1</b> Network Deleted Successfully']);
        }
    }
    public function pull_affiliates()
    {
        $new_affiliates = 0;
        $updated_affiliates = 0;
        $db_network_affiliate_ids = Network::all()->pluck('network_affiliate_id')->toArray();
        $key = "X-Eflow-API-Key";
        $value = "nH43mlvTSCuYUOgOXrRA";
        $url = 'https://api.eflow.team/v1/networks/affiliates';
        $api_data = json_decode(Http::withHeaders([$key => $value])->accept('application/json')->get($url)->body());
        $affiliates = $api_data->affiliates;
        $paging = $api_data->paging;

        if ($affiliates) {
            foreach ($affiliates as $affiliate) {
                if (in_array($affiliate->network_affiliate_id, $db_network_affiliate_ids)) {
                    $update = Network::where(['network_affiliate_id' => $affiliate->network_affiliate_id])->first();
                    $update->update((array)$affiliate);
                    $updated_affiliates++;
                } else {
                    Network::create((array)$affiliate);
                    $new_affiliates++;
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
}
