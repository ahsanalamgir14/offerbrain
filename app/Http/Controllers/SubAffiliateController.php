<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\SubAffiliate;
use Illuminate\Http\Request;

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
        $api_data = array_map('json_decode', Http::withHeaders([$key => $value])->accept('application/json')->post(
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
        )->body());
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
}