<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Auth;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v1/order_view';

        $response['orders'] = Http::withBasicAuth($username, $password)
            ->accept('application/json')
            ->get('https://thinkbrain.sticky.io/api/v2/orders/histories', [
                'start_at' => '2021-12-16 23:59:59',
                'end_at' => '2021-12-18 00:00:00'
            ])['data'];

        foreach ($response['orders'] as $order) {
            $order_ids[] = $order['order_id'];
        }        
            // dd($order_ids);die;

        $data = json_decode(Http::asForm()->withBasicAuth($username, $password)
            ->accept('application/json')
            ->post('https://thinkbrain.sticky.io/api/v1/order_view', [
                'order_id' => $order_ids
            ])->getBody()->getContents());

        $data = $data->data;
            // dd($data[0]->data);die;
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function user_data(Request $request)
    {
        // return response()->json(['status'=>true, 'name'=>'', 'email'=>'']);
        return response()->json(['status'=>true, 'name'=>$request->user()->name, 'email'=>$request->user()->email]);
    }
}
