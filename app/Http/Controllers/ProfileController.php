<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use App\Models\Profile;
use function GuzzleHttp\json_decode;


class ProfileController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function show(Profile $profile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function edit(Profile $profile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Profile $profile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function destroy(Profile $profile)
    {
        //
    }
    public function update_profiles(){
        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";

        $profile_data = new Profile();
        $profile_id = $profile_data->pluck('profile_id')->toArray();
        
        $account_ids = $this->pull_account_ids($username, $password);
        if ($account_ids) {
            foreach ($account_ids as $acc_id) {
                $url = 'https://thinkbrain.sticky.io/api/v2/providers/payment/' . $acc_id . '/profiles';
                $data = json_decode(Http::withBasicAuth($username, $password)->accept('application/json')->get($url)->getBody()->getContents());
                if (property_exists($data, 'data')) {
                    $profiles_array[] = $data->data;
                    for($i = 0; $i < count($profiles_array); $i++){
                        if(in_array('146',$profile_id)){
                            Profile::where('profile_id',$profiles_array[$i]->id)->update(['alias' => $profiles_array[$i]->alias]);
                        } else {
                            $profile_data->alias = $profiles_array[$i]->alias;
                            $profile_data->create();
                        }
                    }
                }
            }
            return;
        }
    }
    public function pull_profiles()
    {
        $username = "yasir_dev";
        $password = "yyutmzvRpy5TPU";

        $account_ids = $this->pull_account_ids($username, $password);
        if ($account_ids) {
            foreach ($account_ids as $id) {
                $url = 'https://thinkbrain.sticky.io/api/v2/providers/payment/' . $id . '/profiles';
                $data = json_decode(Http::withBasicAuth($username, $password)->accept('application/json')->get($url)->getBody()->getContents());
                if (property_exists($data, 'data')) {
                    $profiles_array[] = $data->data;
                }
            }
            if ($profiles_array) {
                for ($i = 0; $i < count($profiles_array); $i++) {
                    foreach ($profiles_array[$i] as $profile) {
                        $profile_ids[] = $profile->id;
                    }
                }
                if ($profile_ids) {
                    $profiles_data = $this->get_profile_details($profile_ids, $username, $password);
                }
                if ($profiles_data) {
                    $response = $this->save_profiles($profiles_data);
                }
            }

            return response()->json(['status' => true, 'new_profiles' => $response['new_profiles'], 'updated_profiles' => $response['updated_profiles']]);
        }
    }

    public function pull_account_ids($username, $password)
    {
        $url = 'https://thinkbrain.sticky.io/api/v2/providers/payment';
        $page = 1;
        $last_page = Http::withBasicAuth($username, $password)->accept('application/json')->get($url)['last_page'];

        for ($i = 0; $i < $last_page; $i++) {
            $accounts = Http::withBasicAuth($username, $password)->accept('application/json')->get($url, ['page' => $page])['data'];
            foreach ($accounts as $account) {
                $account_ids[] = $account['id'];
            }
            $page++;
        }
        $account_ids = Arr::sortRecursive($account_ids);
        return $account_ids;
    }

    public function get_profile_details($profile_ids, $username, $password)
    {
        foreach ($profile_ids as $id) {
            $url = 'https://thinkbrain.sticky.io/api/v2/providers/payment/profiles/' . $id;
            $data = json_decode(Http::withBasicAuth($username, $password)->accept('application/json')->get($url)->getBody()->getContents());
            if (property_exists($data, 'data')) {
                $profiles_data[] = $data->data;
            }
        }
        return $profiles_data;
    }

    public function save_profiles($profiles)
    {
        $updated_profiles = 0;
        $new_profiles = 0;
        $db_profile_ids = Profile::pluck('profile_id')->toArray();

        foreach ($profiles as $data) {
            // dd($data->id);
            $model = new Profile();
            $data->profile_id = $data->id;
            $data->currency_id = $data->currency->id;
            $data->currency_title = $data->currency->title;
            $data->currency_code = $data->currency->code;
            $data->currency_symbol_left = $data->currency->symbol_left;
            if(property_exists($data->fields, 'global_fields')){
                $data->global_fields = $data->fields->global_fields;
            }
            if(property_exists($data->fields, 'account_fields')){
                $data->account_fields = $data->fields->account_fields;
            }
            if(property_exists($data->fields, 'fee_fields')){
                $data->fee_fields = $data->fields->fee_fields;
            }
            if (in_array($data->profile_id, $db_profile_ids)) {
                $updated_profiles++;
                $profile_update = Profile::where(['profile_id' => $data->profile_id])->first();
                $profile_update->update((array)$data);
            } else {
                $new_profiles++;
                $model->create((array)$data);
            }
        }
        $response['new_profiles'] = $new_profiles;
        $response['updated_profiles'] = $updated_profiles;
        return $response;
    }
}
