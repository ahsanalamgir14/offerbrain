<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;
use Auth;

class ProspectController extends Controller
{
    public function index(Request $request)
    {
        $pageno = isset($request->page) ? $request->page : 1;
        $no_of_records_per_page = isset($request->per_page) ? $request->per_page : 25;
        $query = Prospect::where('user_id', $request->user()->id)->select('id', 'first_name', 'last_name', 'address', 'city', 'state', 'zip', 'country', 'phone', 'email', 'affiliate', 'sub_affiliate')->orderBy('id', 'desc');
        $total_rows = $query->count('id');

        if ($request->search != '') {
            $query->where('first_name', 'like', '%' . $request->search . '%')
                ->orWhere('last_name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('address', 'like', '%' . $request->search . '%');
        }

        $rows = $query->SimplePaginate($no_of_records_per_page);
        $total_pages = ceil($total_rows / $rows->perPage());

        $pag['count'] = $total_rows;
        $pag['total_pages'] = $total_pages;
        $pag['pageno'] = $pageno;
        $pag['rows_per_page'] = $no_of_records_per_page;
        return response()->json(['status' => true, 'data' => $rows, 'pag' => $pag]);

    }

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
     * @param  \App\Models\Prospect  $prospect
     * @return \Illuminate\Http\Response
     */
    public function show(Prospect $prospect)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Prospect  $prospect
     * @return \Illuminate\Http\Response
     */
    public function edit(Prospect $prospect)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Prospect  $prospect
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Prospect $prospect)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Prospect  $prospect
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $prospect = Prospect::find($id);
        if ($prospect) {
            $prospect->delete();
            return response()->json(['status' => true, 'message' => '1 Prospect Deleted Successfully']);
        } else {
            return response()->json(['status' => false, 'message' => "Opps!! Prospect Could not be deleted"]);
        }
    }

    public function delete_prospects(Request $request)
    {
        $data = $request->all();
        $ids = array_column($data, 'id');
        $total_records = count($ids);
        Prospect::whereIn('id', $ids)->delete();
        if ($total_records <= 1) {
            return response()->json(['status' => true, 'message' => '<b>1</b> Prospect Deleted Successfully']);
        } else {
            return response()->json(['status' => true, 'message' => $total_records . ' Prospects Deleted Successfully']);
        }
    }

    public static function pull_prospects($startDate, $endDate)
    {
        $users = User::orderBy('id','desc')->get();
        foreach($users as $user){
            $new_prospects = 0;
            $updated_prospects = 0;

            $username = $user->sticky_api_username;
            $password = Crypt::decrypt($user->sticky_api_key);
            $url = $user->sticky_url.'/api/v1/prospect_find';

            $startDate = Carbon::createFromFormat('Y-m-d', $startDate);
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate);
            $dateRange = CarbonPeriod::create($startDate, $endDate);
            $dateRange->toArray();

            foreach ($dateRange as $day) {
                $monthDays[] = Carbon::parse($day)->format('m/d/Y');
            }
            foreach ($monthDays as $day) {
                $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
                    $url,
                    [
                        'start_date' => $day,
                        'end_date' => $day,
                        'campaign_id' => 'all',
                        'criteria' => 'all',
                        'search_type' => 'all',
                        'return_type' => 'prospect_view'
                    ]
                )->getBody()->getContents());
                if ($api_data->response_code == 602) {
                    continue;
                }
                $prospect_ids = $api_data->prospect_id;
                $data = $api_data->data;
                $total_prospects = $api_data->total_prospects;

                foreach ($data as $object) {
                    $results[] = (array)$object;
                }
                $db_prospect_ids = Prospect::pluck('prospect_id')->toArray();
                if (isset($total_prospects) && $total_prospects != 0 && $total_prospects <= 10000) {
                    $updated_prospects = 0;
                    $new_prospects = 0;
                    $index = 1;
                    foreach ($results as $result) {
                        $month = Carbon::parse($result['date_created'])->format('F');
                        $year = Carbon::parse($result['date_created'])->format('Y');
                        $result['month_created'] = $month;
                        $result['year_created'] = $year;
                        $result['notes'] = json_encode($result['notes']);
                        $result['user_id'] = $user->id;
                        unset($result['response_code']);
                        if (in_array($result['prospect_id'], $db_prospect_ids)) {
                            $updated_prospects++;
                            Prospect::where('prospect_id', $result['prospect_id'])->update($result);
                        } else {
                            $prospect = new Prospect();
                            $new_prospects++;
                            $prospect->create($result);
                        }
                        $index++;
                        if ($index == 150) {
                            break;
                        }
                    }
                    return response()->json(['status' => true, 'New Record in todays API' => $new_prospects, 'Previous prospects to be updated in prospects table' => $updated_prospects]);
                    $response['new_prospects'] = $new_prospects;
                    $response['updated_prospects'] = $updated_prospects;

                    $new_prospects += $response['new_prospects'];
                    $updated_prospects += $response['updated_prospects'];
                    $results = null;
                    $data = null;
                } else {
                    $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
                        $url,
                        [
                            'start_date' => $day,
                            'end_date' => $day,
                            'campaign_id' => 'all',
                            'criteria' => 'all',
                            'start_time' => '00:00:00',
                            'end_time' => '12:00:00',
                            'search_type' => 'all',
                            'return_type' => 'prospect_view'
                        ]
                    )->getBody()->getContents());

                    $prospect_ids = $api_data->prospect_id;
                    $data = $api_data->data;
                    $total_prospects = $api_data->total_prospects;
                    foreach ($data as $object) {
                        $results[] = (array)$object;
                    }

                    $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
                        $url,
                        [
                            'start_date' => $day,
                            'end_date' => $day,
                            'campaign_id' => 'all',
                            'criteria' => 'all',
                            'start_time' => '12:00:01',
                            'end_time' => '23:59:59',
                            'search_type' => 'all',
                            'return_type' => 'prospect_view'
                        ]
                    )->getBody()->getContents());
                    $prospect_ids = $api_data->prospect_id;
                    $data = $api_data->data;
                    $total_prospects = $api_data->total_prospects;
                    foreach ($data as $object) {
                        $results[] = (array)$object;
                    }

                    foreach ($results as $result) {
                        $prospect = new Prospect();
                        $month = Carbon::parse($result['date_created'])->format('F');
                        $year = Carbon::parse($result['date_created'])->format('Y');
                        $result['month_created'] = $month;
                        $result['year_created'] = $year;
                        $result['notes'] = json_encode($result['notes']);
                        $result['user_id'] = $user->id;

                        if (in_array($result['prospect_id'], $db_prospect_ids)) {
                            $updated_prospects++;
                            $prospect = Prospect::where(['prospect_id' => $result['prospect_id']])->update($result);
                        } else {
                            $new_prospects++;
                            $prospect->create($result);
                        }
                    }
                    $response['new_prospects'] = $new_prospects;
                    $response['updated_prospects'] = $updated_prospects;

                    $new_prospects += $response['new_prospects'];
                    $updated_prospects += $response['updated_prospects'];
                    $results = null;
                    $data = null;
                }
            }
        }
    }

    public function get_prospect_with_time($username, $password, $url, $day)
    {
        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
            $url,
            [
                'start_date' => $day,
                'end_date' => $day,
                'campaign_id' => 'all',
                'criteria' => 'all',
                'start_time' => '00:00:00',
                'end_time' => '12:00:00',
                'search_type' => 'all',
                'return_type' => 'prospect_view'
            ]
        )->getBody()->getContents());

        $prospect_ids = $api_data->prospect_id;
        $data = $api_data->data;
        $total_prospects = $api_data->total_prospects;
        foreach ($data as $object) {
            $results[] = (array)$object;
        }

        $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
            $url,
            [
                'start_date' => $day,
                'end_date' => $day,
                'campaign_id' => 'all',
                'criteria' => 'all',
                'start_time' => '12:00:01',
                'end_time' => '23:59:59',
                'search_type' => 'all',
                'return_type' => 'prospect_view'
            ]
        )->getBody()->getContents());
        $prospect_ids = $api_data->prospect_id;
        $data = $api_data->data;
        $total_prospects = $api_data->total_prospects;
        foreach ($data as $object) {
            $results[] = (array)$object;
        }
        return $results;
    }

    public function save_prospects($results)
    {
        $updated_prospects = 0;
        $new_prospects = 0;
        $db_prospect_ids = Prospect::where('user_id',  Auth::id())->pluck('prospect_id')->toArray();

        foreach ($results as $result) {

            $month = Carbon::parse($result['date_created'])->format('F');
            $year = Carbon::parse($result['date_created'])->format('Y');
            $result['user_id'] = Auth::id();
            $result['month_created'] = $month;
            $result['year_created'] = $year;
            $result['notes'] = json_encode($result['notes']);

            if (in_array($result['prospect_id'], $db_prospect_ids)) {
                $updated_prospects++;
                $prospect = Prospect::where(['prospect_id' => $result['prospect_id'], 'user_id'=> Auth::id()])->first();
                $prospect->update($result);
            } else {
                $new_prospects++;
                Prospect::create($result);
            }
        }
        $response['new_prospects'] = $new_prospects;
        $response['updated_prospects'] = $updated_prospects;
        return $response;
    }

    public function pull_user_prospects(Request $request)
    {
        $new_prospects = 0;
        $updated_prospects = 0;
        $user = User::find($request->user()->id);
        $username = $user->sticky_api_username;
        $password = Crypt::decrypt($user->sticky_api_key);
        $url = $user->sticky_url . '/api/v1/prospect_find';
        $startDate = Carbon::createFromFormat('Y-m-d', '2022-03-01');
        $endDate = Carbon::createFromFormat('Y-m-d', '2022-06-17');
        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $dateRange->toArray();

        foreach ($dateRange as $day) {
            $monthDays[] = Carbon::parse($day)->format('m/d/Y');
        }

        foreach ($monthDays as $day) {

            $api_data = json_decode(Http::asForm()->withBasicAuth($username, $password)->accept('application/json')->post(
                $url,
                [
                    'start_date' => $day,
                    'end_date' => $day,
                    'campaign_id' => 'all',
                    'criteria' => 'all',
                    'search_type' => 'all',
                    'return_type' => 'prospect_view'
                ]
            )->getBody()->getContents());
            if ($api_data->response_code == 602) {
                continue;
            }

            $prospect_ids = $api_data->prospect_id;
            $data = $api_data->data;
            $total_prospects = $api_data->total_prospects;

            foreach ($data as $object) {
                $results[] = (array)$object;
            }
            if (isset($total_prospects) && $total_prospects != 0 && $total_prospects <= 10000) {
                $response = $this->save_prospects($results);
                $new_prospects += $response['new_prospects'];
                $updated_prospects += $response['updated_prospects'];
                $results = null;
                $data = null;
            } else {
                $results = $this->get_prospect_with_time($username, $password, $url, $day);
                $response = $this->save_prospects($results);
                $new_prospects += $response['new_prospects'];
                $updated_prospects += $response['updated_prospects'];
                $results = null;
                $data = null;
            }
        }
        return response()->json(['status' => true, 'user_id' => Auth::id(), 'new_prospects' => $new_prospects, 'updated_prospects' => $updated_prospects]);
    }
}

