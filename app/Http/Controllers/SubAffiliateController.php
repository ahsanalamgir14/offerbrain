<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\SubAffiliate;
use App\Models\Network;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class SubAffiliateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // return $request->sub1;
        $AffArray = explode(",", $request->affiliate_id);
        $start_date = $request->start_date;
        $end_date = $request->end_date;
       $sub_affiliates = SubAffiliate::where('sub1', 'LIKE', '%'.$request->sub1.'%')
       ->where('sub2', 'LIKE', '%'.$request->sub2.'%')->where('sub3', 'LIKE', '%'.$request->sub3.'%')
       ->where('date','>=',$start_date)->where('date','<=',$end_date)->whereIn('affid', $AffArray)->get();
       $data  = $sub_affiliates;
       return response()->json(['status' => true, 'data' => $data]);
    }

    public function pull_cron_sub_affiliates(){
        $start_date =  Carbon::now()->startOfDay()->format('Y-m-d');
        $end_date =  Carbon::now()->endOfDay()->format('Y-m-d');
        $data = [];
        $new_sub_affiliates = 0;
        $updated_sub_affiliates = 0;
        $not_updated_sub_affiliates = 0;
        $key = "X-Eflow-API-Key";
        // $user = User::find(2);
        $user = User::find(Auth::id());
        
        // $user = User::find($request->user()->id);
        $username = $user->sticky_api_username;
        if ($user->everflow_api_key) {
            $value = Crypt::decrypt($user->everflow_api_key);
        } else {
            return response()->json(['status' => false, 'message' => 'No API key found for Everflow']);
        }
        // return $value;
        $networks =  Network::where(['user_id' => Auth::id()])->get();
        foreach($networks as $network){
            $filterId = (string) $network->network_affiliate_id;
            if($filterId != null && $filterId != ""){
                $url = 'https://api.eflow.team/v1/networks/reporting/entity/table/export';
                $api_data = Http::withHeaders([$key => $value])->accept('application/json')->post(
                    $url,
                    [
                        "from" => $start_date,
                        "to" => $end_date,
                        "timezone_id" => 67,
                        "currency_id" => "USD",
                        "format" => "json",
                        "columns" => [
                            [
                                "column" => "sub1"
                            ],
                            [
                                "column" => "sub2"
                            ],
                            [
                                "column" => "sub3"
                            ],
                            [
                                "column" => "date"
                            ],
        
                        ],
                        "query" => [
                            "filters" => [
                                [
                                    "filter_id_value" => $filterId,
                                    "resource_type" => "affiliate"
                                ]
                            ]
                        ]
                    ]
                )->body();
                $sub_affiliates = explode("\n", $api_data);
                foreach($sub_affiliates as $sub_affiliate){
                    $obj = json_decode($sub_affiliate);
                    $netAff = $network->network_affiliate_id;
                    if($obj != null){
                    $obj->affid =  $netAff;
                    $obj->user_id =  Auth::id();
                    $subAffNotChanged = SubAffiliate::where(['user_id' => Auth::id(), 'affid' => $netAff, 'sub1'=> $obj->sub1, 'sub2'=> $obj->sub2, 'sub3'=> $obj->sub3 ])
                    ->where( 'ROAS', '=',$obj->ROAS)
                    ->where('total_conversions','=', $obj->total_conversions)
                    ->where('CV', '=',$obj->CV)
                    ->where('CPA', '=',$obj->CPA)
                    ->where('RPA', '=',$obj->RPA)
                    ->where( 'revenue', '=',$obj->revenue)
                    ->where('gross_sales', '=',$obj->gross_sales)
                    ->first();

                    $subAffChanged = SubAffiliate::where(['user_id' => Auth::id(), 'affid' => $netAff, 'sub1'=> $obj->sub1, 'sub2'=> $obj->sub2, 'sub3'=> $obj->sub3 ])
                    ->where( 'ROAS', '!=',$obj->ROAS)
                    ->where('total_conversions','!=', $obj->total_conversions)
                    ->where('CV', '!=',$obj->CV)
                    ->where('CPA', '!=',$obj->CPA)
                    ->where('RPA', '!=',$obj->RPA)
                    ->where( 'revenue', '!=',$obj->revenue)
                    ->where('gross_sales', '!=',$obj->gross_sales)
                    ->first();
                    if($subAffChanged){
                        $subAffChanged->update((array)$obj);
                        $updated_sub_affiliates++;
                       }
                       else if($subAffNotChanged){
                        $not_updated_sub_affiliates++;
                       }
                       else{
                        SubAffiliate::create((array)$obj);
                        $new_sub_affiliates++;
                       }

                    }
                   
                }   
            }  
        }     
        return response()->json(['status' => true, 'new_affiliates' => $new_sub_affiliates, 'updated_affiliates' => $updated_sub_affiliates, 'not_updated_affiliates' => $not_updated_sub_affiliates]);
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
            $affiliate_id = $request->affiliate_id;
            // dd($affiliate_id);
            $start_date = Carbon::parse($request->start_date)->startOfDay();
            $end_date = Carbon::parse($request->end_date)->endOfDay();

            for ($i = 0; $i < count($sub_affiliates); $i++) {
                $query = DB::table('orders')
                    ->where('time_stamp', '>=', $start_date)
                    ->where('time_stamp', '<=', $end_date)
                    ->whereIn('affiliate', $affiliate_id)
                    ->where('order_status', '=', 2);
                if ($sub_affiliates[$i][0] && $sub_affiliates[$i][0] != '') {
                    $query->where('c1', '=', $sub_affiliates[$i][0]);
                }
                // if ($sub_affiliates[$i][1] && $sub_affiliates[$i][1] != '') {
                //     $query->where('c2', '=', $sub_affiliates[$i][1]);
                // }
                // if ($sub_affiliates[$i][2] && $sub_affiliates[$i][2] != '') {
                //     $query->where('c3', '=', $sub_affiliates[$i][2]);
                // }
                $query->addSelect(DB::raw('ROUND(SUM(order_total), 2) as gross_revenue'));
                $response[] = $query->pluck('gross_revenue')->toArray();
            }
            return response()->json(['status' => true, 'data' => $response]);
        }
    }

    public function get_EF_key(Request $request)
    {
        $key = User::where(['id' => 2])->pluck('everflow_api_key')->first();
        // $key = User::where(['id' => Auth::id()])->pluck('everflow_api_key')->first();
        $key = Crypt::decrypt($key);
        return response()->json(['status' => true, 'key' => $key]);
    }
}
