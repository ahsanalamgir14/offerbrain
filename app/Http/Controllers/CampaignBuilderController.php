<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;
use Carbon\Carbon;
use DB;
use Auth;
use Session;
// use Illuminate\Support\Facades\Auth;

class CampaignBuilderController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();

            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $data = Campaign::find(250);
        // return response()->json(['status' => true, 'data' => $data]);

        $data = Campaign::where(['user_id' => 1])->get();
        // $data = Campaign::where(['user_id' => $request->user()->id])->get();
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
        // dd($request->all());
        $data = $request->all();
        $campaign = new Campaign();
        $data['campaign_id'] = rand(100000, 999999);
        $db_campaign_ids = Campaign::all()->pluck('campaign_id')->toArray();
        $data['user_id'] = $request->user()->id;
        $data['created_at'] = Carbon::now();
        // if ($data['updated_at']) {
        //     $data['updated_at'] = $data['updated_at']['date'];
        //     $data['updator'] = serialize($data['updator']);
        // }
        // $data['creator'] = serialize($data['creator']);
        // $data['countries'] = serialize($data['countries']);
        // $data['offers'] = serialize($data['offers']);
        // $data['channel'] = serialize($data['channel']);
        // $data['payment_methods'] = serialize($data['payment_methods']);
        // if ($data['gateway']) {
            // $data['gateway'] = serialize($data['gateway']);
        // }
        // $data['alternative_payments'] = serialize($data['alternative_payments']);
        // $data['shipping_profiles'] = serialize($data['shipping_profiles']);
        // $data['return_profiles'] = serialize($data['return_profiles']);
        // $data['postback_profiles'] = serialize($data['postback_profiles']);
        // $data['coupon_profiles'] = serialize($data['coupon_profiles']);
        // $data['fraud_providers'] = serialize($data['fraud_providers']);
        // $data['volume_discounts'] = serialize($data['volume_discounts']);
        if (in_array($data['campaign_id'], $db_campaign_ids)) {
            return response()->json(['status' => false, 'message' => 'Please click again to save']);
        } else {
            $campaign->create($data);
            return response()->json(['status' => true, 'message' => 'New campaign created']);
        }
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

    public function campaign_builder_options()
    {
        DB::statement("SET SQL_MODE=''");
        $data['products'] = DB::table('order_products')->select('id', 'name')->groupBy('name')->get();
        $data['campaigns'] = DB::table('campaigns')->select('id', 'campaign_id', 'gateway_id', 'name')->groupBy('campaign_id')->get();
        $data['networks'] = DB::table('networks')->select('id', 'network_affiliate_id', 'network_id', 'name')->groupBy('network_affiliate_id')->get();
        return response()->json(['status' => true, 'data' => $data]);
    }
}
