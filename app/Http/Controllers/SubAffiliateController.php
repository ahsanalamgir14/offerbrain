<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\SubAffiliate;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use DB;

class SubAffiliateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [];
        $key = "X-Eflow-API-Key";
        $value = "nH43mlvTSCuYUOgOXrRA";
        $url = 'https://api.eflow.team/v1/networks/reporting/entity/table/export';
        $api_data = Http::withHeaders([$key => $value])->accept('application/json')->post(
            $url,
            [
                "from" => "2022-03-01",
                "to" => "2022-03-03",
                "timezone_id" => 67,
                "currency_id" => "USD",
                "format" => "json",
                "columns" => [
                    [
                        "column" => "sub1"
                    ],

                ],
                "query" => [
                    "filters" => [
                        [
                            "filter_id_value" => "1",
                            "resource_type" => "affiliate"
                        ]
                    ]
                ]
            ]
        )->body();
        return $api_data;
        array_push($data, $api_data);
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
     * @param  \App\Models\SubAffiliate  $subAffiliate
     * @return \Illuminate\Http\Response
     */
    public function show(SubAffiliate $subAffiliate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SubAffiliate  $subAffiliate
     * @return \Illuminate\Http\Response
     */
    public function edit(SubAffiliate $subAffiliate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SubAffiliate  $subAffiliate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SubAffiliate $subAffiliate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SubAffiliate  $subAffiliate
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubAffiliate $subAffiliate)
    {
        //
    }

    public function sub_affiliate_gross_revenue(Request $request)
    {
        $response = [];
        if ($request->start_date != '' && $request->end_date != '') {
            $sub_affiliates = $request->data;
<<<<<<< Updated upstream
            $affiliate_id = $request->affiliate_id;
=======
>>>>>>> Stashed changes
            $start_date = Carbon::parse($request->start_date)->startOfDay();
            $end_date = Carbon::parse($request->end_date)->endOfDay();

            for ($i = 0; $i < count($sub_affiliates); $i++) {
                $query = DB::table('orders')
                    ->where('time_stamp', '>=', $start_date)
                    ->where('time_stamp', '<=', $end_date)
                    ->where('affiliate', '<=', $affiliate_id)
                    ->where('order_status', '=', 2);
                if ($sub_affiliates[$i][0] && $sub_affiliates[$i][0] != '') {
                    $query->where('c1', '=', $sub_affiliates[$i][0]);
                }
                if ($sub_affiliates[$i][1] && $sub_affiliates[$i][1] != '') {
                    $query->where('c2', '=', $sub_affiliates[$i][1]);
                }
                if ($sub_affiliates[$i][2] && $sub_affiliates[$i][2] != '') {
                    $query->where('c3', '=', $sub_affiliates[$i][2]);
                }
                $query->addSelect(DB::raw('ROUND(SUM(order_total), 2) as gross_revenue'));
                $response[] = $query->pluck('gross_revenue')->toArray();
            }
            return response()->json(['status' => true, 'data' => $response]);
        }
    }
}
