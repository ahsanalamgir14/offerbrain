<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Campaign;
use App\Models\GoldenTicket;
use DB;
use Carbon\Carbon;


class CampaignsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Campaign::all();
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
    public function get_campaigns(Request $request)
    {

        $data = Campaign::all()->pluck('name')->toArray();
        return response()->json(['status' => true, 'data' => $data]);
    }
    public function refresh_campaigns(Request $request)
    {
        $new_campaigns = 0; 
        $updated_campaigns = 0;
        $db_campaign_ids = Campaign::all()->pluck('campaign_id')->toArray();
        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v2/campaigns';
        $page = 1;

        $api_data = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page]);

        $last_page = $api_data['last_page'];
        $campaigns = $api_data['data'];

        if ($campaigns) {
            //adding or updating page 1 campaigns
            foreach ($campaigns as $result) {
                $campaign = new Campaign();
                $result['campaign_id'] = $result['c_id'];
                $result['created_at'] = $result['created_at']['date'];
                if ($result['updated_at']) {
                    $result['updated_at'] = $result['updated_at']['date'];
                    // $result['updator'] = serialize($result['updator']);
                }
                // $result['creator'] = serialize($result['creator']);
                // $result['countries'] = serialize($result['countries']);
                // $result['offers'] = serialize($result['offers']);
                // $result['channel'] = serialize($result['channel']);
                // $result['payment_methods'] = serialize($result['payment_methods']);
                // if ($result['gateway']) {
                //     $result['gateway'] = serialize($result['gateway']);
                // }
                // $result['alternative_payments'] = serialize($result['alternative_payments']);
                // $result['shipping_profiles'] = serialize($result['shipping_profiles']);
                // $result['return_profiles'] = serialize($result['return_profiles']);
                // $result['postback_profiles'] = serialize($result['postback_profiles']);
                // $result['coupon_profiles'] = serialize($result['coupon_profiles']);
                // $result['fraud_providers'] = serialize($result['fraud_providers']);
                // $result['volume_discounts'] = serialize($result['volume_discounts']);
                if (in_array($result['campaign_id'], $db_campaign_ids)) {
                    $campaign->where(['campaign_id' => $result['campaign_id']])->get();
                    $campaign->update($result);
                    $updated_campaigns++;
                } else {
                    $campaign->create($result);
                    $new_campaigns++;
                }
            }
            // for more pages get data and save
            if ($last_page > 1) {
                $page++;
                for ($page; $page <= $last_page; $page++) {
                    // var_dump('loop', $page);
                    $other_campaigns = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page])['data'];

                    foreach ($other_campaigns as $result) {
                        $campaign = new Campaign();
                        $result['campaign_id'] = $result['c_id'];
                        $result['created_at'] = $result['created_at']['date'];
                        if ($result['updated_at']) {
                            $result['updated_at'] = $result['updated_at']['date'];
                            // $result['updator'] = serialize($result['updator']);
                        }
                        // $result['creator'] = serialize($result['creator']);
                        // $result['countries'] = serialize($result['countries']);
                        // $result['offers'] = serialize($result['offers']);
                        // $result['channel'] = serialize($result['channel']);
                        // $result['payment_methods'] = serialize($result['payment_methods']);
                        // if ($result['gateway']) {
                        //     $result['gateway'] = serialize($result['gateway']);
                        // }
                        // $result['alternative_payments'] = serialize($result['alternative_payments']);
                        // $result['shipping_profiles'] = serialize($result['shipping_profiles']);
                        // $result['return_profiles'] = serialize($result['return_profiles']);
                        // $result['postback_profiles'] = serialize($result['postback_profiles']);
                        // $result['coupon_profiles'] = serialize($result['coupon_profiles']);
                        // $result['fraud_providers'] = serialize($result['fraud_providers']);
                        // $result['volume_discounts'] = serialize($result['volume_discounts']);
                        if (in_array($result['campaign_id'], $db_campaign_ids)) {
                            $campaign->where(['campaign_id' => $result['campaign_id']])->get();
                            $campaign->update($result);
                            $updated_campaigns++;
                        } else {
                            $campaign->create($result);
                            $new_campaigns++;
                        }
                    }
                }
            }
        }
        return response()->json(['status' => true, 'New campaigns:'=> $new_campaigns, 'Updated Campaigns:'=>$updated_campaigns]);
    }
    public function get_campaign_columns(Request $request)
    {

        $campaigns = Campaign::all()->pluck('name')->toArray();
        $key = array_search($request->campaign_name, $campaigns);
        /*  
            todo: important use in future for dynamic data
            $data = DB::table($campaigns[$key])->get();
         */
        if ($campaigns[$key] == 'Golden Ticket Main') {
            $golder_ticket = new GoldenTicket;
            $columns = $golder_ticket->getTableColumns();
            /* 
                todo: to be added after
                $exclude_columns = ['id', 'created_at', 'updated_at'];
                $get_columns = array_diff($columns, $exclude_columns);
             */
            return response()->json(['status' => true, 'data' => $columns]);
        } else {
            return response()->json(['status' => false]);
        }
    }
}
   
