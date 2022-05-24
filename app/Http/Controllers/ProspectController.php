<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ProspectController extends Controller
{
    public function index(Request $request)
    {
        $pageno = isset($request->page) ? $request->page : 1;
        $no_of_records_per_page = isset($request->per_page) ? $request->per_page : 25;
        
        $query = Prospect::select('id', 'first_name', 'last_name', 'address', 'city', 'state', 'zip', 'country', 'phone', 'email', 'affiliate', 'sub_affiliate')->orderBy('id', 'desc');
        // $total_rows = Prospect::where('id', '>', 0)->count('id');
        // $total_rows = DB::table('prospects')->select('id')->count();
        // $total_rows = 200000;
        $total_rows = $query->count('id');

        if ($request->search != '') {
            $query->where('first_name', 'like', '%' . $request->search . '%')
                ->orWhere('last_name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('address', 'like', '%' . $request->search . '%');
        }

        $rows = $query->SimplePaginate($no_of_records_per_page);
        // $total_rows = $query->count('id');
        
        // $total_rows = 260466;
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
    
    public function pull_prospects_dec()
    {
        $new_prospects = 0;
        $updated_prospects = 0;
        $db_prospect_ids = Prospect::pluck('prospect_id')->toArray();
        // $db_prospect_ids = DB::table('prospects')->pluck('prospect_id')->toArray();

        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v1/prospect_find';
        $model = new Prospect();

        $startDate = Carbon::createFromFormat('Y-m-d', '2021-12-01');
        $endDate = Carbon::createFromFormat('Y-m-d', '2021-12-31');
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
                foreach ($results as $result) {

                    $prospect = new Prospect();
                    // $result['prospect_id'] = $result['id'];
                    $month = Carbon::parse($result['date_created'])->format('F');
                    $year = Carbon::parse($result['date_created'])->format('Y');
                    $result['month_created'] = $month;
                    $result['year_created'] = $year;
                    $result['notes'] = json_encode($result['notes']);

                    if (in_array($result['prospect_id'], $db_prospect_ids)) {
                        $updated_prospects++;
                        $prospect = Prospect::where(['prospect_id' => $result['prospect_id']])->first();
                        $prospect->update($result);
                    } else {
                        $new_prospects++;
                        $prospect->create($result);
                    }
                }
                $results = null;
                $data = null;
            } else {
                //this part is not updates yet
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
                    foreach ($results as $result) {

                        $prospect = new Prospect();
                        // $result['prospect_id'] = $result['id'];
                        $month = Carbon::parse($result['date_created'])->format('F');
                        $year = Carbon::parse($result['date_created'])->format('Y');
                        $result['month_created'] = $month;
                        $result['year_created'] = $year;
                        $result['notes'] = json_encode($result['notes']);

                        if (in_array($result['prospect_id'], $db_prospect_ids)) {
                            $updated_prospects++;
                            $prospect = Prospect::where(['prospect_id' => $result['prospect_id']])->first();
                            $prospect->update($result);
                        } else {
                            $new_prospects++;
                            $prospect->create($result);
                        }
                    }
                    $results = null;
                    $data = null;
                // return response()->json(['status' => false, 'message' => "data too large to be handled", 'Day' => $day]);
                }
            }
        }
        return response()->json(['status' => true, 'New Record in todays API' => $new_prospects, 'Previous prospects to be updated in prospects table' => $updated_prospects]);
    }
    public static function pull_prospects()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $new_prospects = 0;
        $updated_prospects = 0;

        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v1/prospect_find';
        $model = new Prospect();

        $startDate = Carbon::createFromFormat('Y-m-d', '2022-04-28');
        $endDate = Carbon::createFromFormat('Y-m-d', '2022-04-29');
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

                $updated_prospects = 0;
                $new_prospects = 0;
                $db_prospect_ids = Prospect::pluck('prospect_id')->toArray();

                foreach ($results as $result) {
                    $prospect = new Prospect();
                    $month = Carbon::parse($result['date_created'])->format('F');
                    $year = Carbon::parse($result['date_created'])->format('Y');
                    $result['month_created'] = $month;
                    $result['year_created'] = $year;
                    $result['notes'] = json_encode($result['notes']);

                    if (in_array($result['prospect_id'], $db_prospect_ids)) {
                        $updated_prospects++;
                        $prospect = Prospect::where(['prospect_id' => $result['prospect_id']])->first();
                        $prospect->update($result);
                    } else {
                        $new_prospects++;
                        $prospect->create($result);
                    }
                }
                $response['new_prospects'] = $new_prospects;
                $response['updated_prospects'] = $updated_prospects;
                // return $response;

                // $response = $this->save_prospects($results);
                $new_prospects += $response['new_prospects'];
                $updated_prospects += $response['updated_prospects'];
                $results = null;
                $data = null;
            } else {
                // $results = $this->get_prospect_with_time($username, $password, $url, $day);
                // $response = $this->save_prospects($results);

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

                    if (in_array($result['prospect_id'], $db_prospect_ids)) {
                        $updated_prospects++;
                        $prospect = Prospect::where(['prospect_id' => $result['prospect_id']])->first();
                        $prospect->update($result);
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
        return response()->json(['status' => true, 'New Record in todays API' => $new_prospects, 'Previous prospects to be updated in prospects table' => $updated_prospects]);
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
        // dd($results);
        return $results;
    }
    public function save_prospects($results)
    {
        $updated_prospects = 0;
        $new_prospects = 0;
        $db_prospect_ids = Prospect::pluck('prospect_id')->toArray();

        foreach ($results as $result) {

            $prospect = new Prospect();
            $month = Carbon::parse($result['date_created'])->format('F');
            $year = Carbon::parse($result['date_created'])->format('Y');
            $result['month_created'] = $month;
            $result['year_created'] = $year;
            $result['notes'] = json_encode($result['notes']);

            if (in_array($result['prospect_id'], $db_prospect_ids)) {
                $updated_prospects++;
                $prospect = Prospect::where(['prospect_id' => $result['prospect_id']])->first();
                $prospect->update($result);
            } else {
                $new_prospects++;
                $prospect->create($result);
            }
        }
        $response['new_prospects'] = $new_prospects;
        $response['updated_prospects'] = $updated_prospects;
        return $response;
    }

    public function pull_local_prospects()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $new_prospects = 0;
        $updated_prospects = 0;
        // $db_prospect_ids = DB::table('prospects')->pluck('prospect_id')->toArray();

        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";
        $url = 'https://thinkbrain.sticky.io/api/v1/prospect_find';
        $model = new Prospect();

        $startDate = Carbon::createFromFormat('Y-m-d', '2022-04-27');
        $endDate = Carbon::createFromFormat('Y-m-d', '2022-05-24');
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
        return response()->json(['status' => true, 'New Record in todays API' => $new_prospects, 'Previous prospects to be updated in prospects table' => $updated_prospects]);
    }
}

