<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\OldNetwork;
use Illuminate\Http\Request;

class OldNetworkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = OldNetwork::all();
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
     * @param  \App\Models\OldNetwork  $network
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $network = OldNetwork::where(['network_id' => $id])->first();
        return response()->json(['status' => true, 'data' => $network]);
    }

    /**
     * Show the form for editing the specified resource.
     *s
     * @param  \App\Models\OldNetwork  $network
     * @return \Illuminate\Http\Response
     */
    public function edit(OldNetwork $network)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OldNetwork  $network
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OldNetwork $network)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OldNetwork  $network
     * @return \Illuminate\Http\Response
     */
    public function destroy(OldNetwork $network)
    {
        //
    }

    public function pull_networks()
    {
        $new_networks = 0;
        $updated_networks = 0;
        $db_network_ids = OldNetwork::all()->pluck('network_id')->toArray();
        $key = "X-Eflow-API-Key";
        $value = "nH43mlvTSCuYUOgOXrRA";
        $url = 'https://api.eflow.team/v1/networks';
        // !needs code to be changed when more network (currently there is only one network in API response)
        $network = json_decode(Http::withHeaders([$key => $value])->accept('application/json')->get($url)->body());
        // dd($api_data);
        // $networks = $api_data->networks;
        // $paging = $api_data->paging;

        if ($network) {
            // foreach ($networks as $network) {
            if (in_array($network->network_id, $db_network_ids)) {
                $update = network::where(['network_id' => $network->network_id])->first();
                $update->update((array)$network);
                $updated_networks++;
            } else {
                network::create((array)$network);
                $new_networks++;
            }
            // }
            return response()->json(
                [
                    'status' => true,
                    'data' => [
                        'New Networks: ' => $new_networks,
                        'Updates Networks: ' => $updated_networks
                    ]
                ]
            );
        }
    }
}
