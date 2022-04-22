<?php

namespace App\Http\Controllers;

use App\Models\MidGroup;
use App\Models\Mid;
use App\Models\Order;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;

class MidGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = MidGroup::select('*')->whereNull('deleted_at');
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if ($start_date != null && $end_date != null){
            $start_date = date('Y-m-d H:i:s', strtotime($request->start_date));
            $end_date = date('Y-m-d', strtotime($request->end_date));
            // $query->whereBetween('created_at', [$start_date, $end_date.' 23:59:59']);
        }
        $data = $query->get();
        if($start_date != null && $end_date != null){
            foreach ($data as $key => $group) {
                $mids = Mid::where('mid_group', '=', $group['group_name']);
                $group['assigned_mids'] = $mids->count();
                $group['assigned_mid_ids'] = $mids->pluck('gateway_id')->toArray();
                $group['mids_data'] = DB::table('mids')->whereIn('gateway_id', $group['assigned_mid_ids'])->get();
                $orders = DB::table('orders')
                ->where('time_stamp', '>=', $start_date)
                ->where('time_stamp', '<=', $end_date)
                ->whereIn('gateway_id', $group['assigned_mid_ids'])->sum('order_total');
                $group['gross_revenue'] = round($orders,2);
            }
        } else {
            foreach ($data as $key => $group) {
                $mids = Mid::where('mid_group', '=', $group['group_name']);
                $group['assigned_mids'] = $mids->count();
                $group['assigned_mid_ids'] = $mids->pluck('gateway_id')->toArray();
                $group['mids_data'] = DB::table('mids')->whereIn('gateway_id', $group['assigned_mid_ids'])->get();
                $orders = DB::table('orders')
                ->whereIn('gateway_id', $group['assigned_mid_ids'])->sum('order_total');
                $group['gross_revenue'] = round($orders,2);
            }
        }
        // $start_date = $request->start_date;
        // $end_date = $request->end_date;
        // if ($start_date != null && $end_date != null) {
        //     $start_date = date('Y-m-d H:i:s', strtotime($request->start_date));
        //     $end_date = date('Y-m-d', strtotime($request->end_date));
        // }

        // $data = DB::table('mid_groups')
        // ->join('profiles','mid_groups.group_name = profiles.global_fields->mid_group')
        // ->join('mids','profiles.profile_id = mids.gateway_id')
        // ->where('deleted_at',null)
        // ->get();

        return response()->json(['status' => true, 'data' => $data]);
    }
    public function get_assigned_mids(Request $request)
    {
        $data = MidGroup::where('group_name', 'like', '%' . $request->value . '%')->get('assigned_mids')->first();
        if(isset($data)){
            return response()->json(['status' => true, 'mids' => $data->assigned_mids]);
        } else {
            return response()->json(['status' => false, 'mids' => 0]);
        }
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
        $data = $request->all();
        if (!$request->bank_per) {
            $data['bank_per'] = '20';
        }
        MidGroup::create($data);
        $this->refresh_mids_groups();
        return response()->json(['status' => true, 'data' => ['message' => 'Mid Group created successfully']]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MidGroup  $MidGroup
     * @return \Illuminate\Http\Response
     */
    public function show(MidGroup $MidGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MidGroup  $MidGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(MidGroup $MidGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MidGroup  $MidGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $mid_group = MidGroup::where(['id' => $id])->first();
        $data = $request->all();
        $mid_group->update($data);
        $this->refresh_mids_groups();
        return response()->json(['status' => true, 'data' => ['message' => 'Mid Group updated successfully']]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MidGroup  $MidGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $mid_group = MidGroup::where(['id' => $id])->first();
        DB::table('profiles')->where('global_fields->mid_group', $mid_group->group_name)->update(['global_fields->mid_group' => '']);
        $mid_group->delete();
        return response()->json(['status' => true, 'data' => ['message' => 'Mid Group deleted successfully']]);
    }

    public function mid_group_names()
    {
        $data = MidGroup::orderBy('group_name', 'asc')->pluck('group_name')->toArray();
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function refresh_mids_groups()
    {
        $mid_groups = [];
        $db_mid_group_names = MidGroup::pluck('group_name')->toArray();
        $db_mid_groups = MidGroup::all();

        $data = Profile::where('global_fields->mid_group', '!=', '')->whereNotNull('global_fields->mid_group')->get();

        if ($data) {
            foreach ($data as $key => $profile) {
                $mid_groups[$key]['name'] = $profile->global_fields->mid_group;
            }
            $mid_groups = array_unique(array_column($mid_groups, 'name'));
            $result = [];
            $created = 0;
            $updated = 0;

            if ($mid_groups) {
                foreach ($mid_groups as $key => $mid_group_name) {
                    $result['group_name'] = $mid_group_name;
                    $profiles = Profile::where('global_fields->mid_group', '=', $mid_group_name);
                    $result['assigned_mids'] = $profiles->count();
                    $result['assigned_mid_ids'] = $profiles->pluck('profile_id')->toArray();
                    $gross_revenue = Mid::whereIn('gateway_id', $result['assigned_mid_ids'])->sum('current_monthly_amount');
                    $result['gross_revenue'] = $gross_revenue;
                    // $result['target_bank_balance'] = $result['gross_revenue'] * 0.2;
                    if (in_array($result['group_name'], $db_mid_group_names)) {
                        $group = MidGroup::where(['group_name' => $result['group_name']])->first();
                        $group->update($result);
                        $updated++;
                    } else {
                        $model = new MidGroup();
                        $model->create($result);
                        $created++;
                    }
                    $result = [];
                }
            }
        }
        // dd($db_mid_groups[1]);
        foreach ($db_mid_groups as $mid_group) {
            $mid_group->target_bank_balance = ($mid_group->gross_revenue * $mid_group->bank_per) / 100;
            $mid_group->save();
        }
        return response()->json(['status' => true, 'data' => ['new' => $created, 'updated' => $updated]]);
    }
}
