<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Campaign;
use App\Models\GoldenTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;


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
        // return Auth::id();
        $new_campaigns = 0;
        $updated_campaigns = 0;
        // $db_campaign_ids = Campaign::where(['user_id' => 2])->pluck('campaign_id')->toArray();
        $db_campaign_ids = Campaign::where(['user_id' => Auth::id()])->pluck('campaign_id')->toArray();
        // $user = User::find(2);
        $user = User::find($request->user()->id);
        $username = $user->sticky_api_username;
        $password = Crypt::decrypt($user->sticky_api_key);
        $url = $user->sticky_url . '/api/v2/campaigns';
        $page = 1;

        $api_data = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page]);

        $last_page = $api_data['last_page'];
        $campaigns = $api_data['data'];

        if ($campaigns) {
            //adding or updating page 1 campaigns
            foreach ($campaigns as $result) {
                $campaign = new Campaign();
                $result['campaign_id'] = $result['c_id'];
                // $result['user_id'] = 2;
                $result['user_id'] = Auth::id();
                $result['created_at'] = $result['created_at']['date'];
                if ($result['updated_at']) {
                    $result['updated_at'] = $result['updated_at']['date'];
                }
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
                        // $result['user_id'] = 2;
                        $result['user_id'] = Auth::id();
                        $result['created_at'] = $result['created_at']['date'];
                        if ($result['updated_at']) {
                            $result['updated_at'] = $result['updated_at']['date'];
                        }
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
        // $campaigns = DB::table('campaigns')->select('id', 'campaign_id', 'gateway_id', 'name')->where(['user_id' => 2])->whereNotNull('is_active')->groupBy('campaign_id')->get();
        $campaigns = DB::table('campaigns')->select('id', 'campaign_id', 'gateway_id', 'name')->where(['user_id' => Auth::id()])->whereNotNull('is_active')->groupBy('campaign_id')->get();
        return response()->json(['status' => true, 'New campaigns:' => $new_campaigns, 'Updated Campaigns:' => $updated_campaigns, 'data' => ['campaigns' => $campaigns]]);
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
   
