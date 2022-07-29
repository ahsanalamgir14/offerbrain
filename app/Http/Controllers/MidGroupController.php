<?php

namespace App\Http\Controllers;

use App\Models\MidGroup;
use App\Models\Mid;
use App\Models\Order;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Auth;
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
      
        $query = MidGroup::select('*')->where(['user_id' => Auth::id()])->whereNull('deleted_at');
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if ($start_date != null && $end_date != null) {
            $start_date = date('Y-m-d H:i:s', strtotime($request->start_date));
            $end_date = date('Y-m-d', strtotime($request->end_date));
            // $query->whereBetween('created_at', [$start_date, $end_date.' 23:59:59']);
        }
        $data = $query->get();
        if ($start_date != null && $end_date != null) {
            foreach ($data as $key => $group) {
                $mids = Mid::where(['user_id' => Auth::id(), 'is_active' => 1, 'mid_group' => $group['group_name']]);
                $group['assigned_mids'] = $mids->count();
                $group['assigned_mid_ids'] = $mids->pluck('gateway_id')->toArray();
                $group['mids_data'] = DB::table('mids')->where(['user_id' => Auth::id(), 'is_active' => 1])->whereIn('gateway_id', $group['assigned_mid_ids'])->get();
                $orders = DB::table('orders')
                    ->where('time_stamp', '>=', $start_date)
                    ->where('time_stamp', '<=', $end_date)
                    ->whereIn('gateway_id', $group['assigned_mid_ids'])->sum('order_total');
                $group['gross_revenue'] = round($orders, 2);
            }
        } else {
            foreach ($data as $key => $group) {
                $mids = Mid::where(['user_id' => Auth::id(), 'is_active' => 1, 'mid_group' => $group['group_name']]);
                $group['assigned_mids'] = $mids->count();
                $group['assigned_mid_ids'] = $mids->pluck('gateway_id')->toArray();
                $group['mids_data'] = DB::table('mids')->where(['user_id' => Auth::id(), 'is_active' => 1])->whereIn('gateway_id', $group['assigned_mid_ids'])->get();
                $orders = DB::table('orders')
                    ->where(['user_id' => Auth::id()])
                    ->whereIn('gateway_id', $group['assigned_mid_ids'])
                    ->sum('order_total');
                $group['gross_revenue'] = round($orders, 2);
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
        // DB::enableQueryLog();
        // $queryok = MidGroup::select('*')->leftJoin('invoices', function ($leftJoin) {
        //     $leftJoin->on('invoices.mid_group_id', '=', 'mid_groups.id')
        //          ->where('invoices.create_at', '=', DB::raw("(select max(`create_at`) from invoices)"));
        // })->get();
        // $query1 = DB::getQueryLog();
        return response()->json(['status' => true, 'data' => $data, 'query'=>'$query1']);
    }
    public function getMidDetail(Request $request)
    {
        $group_name = $request->group_name;
        $data = Profile::select('global_fields', 'alias', 'profile_id')->where('global_fields->mid_group', '=', $group_name)->get('alias');
        return response()->json(['status' => true, 'data' => $data]);
    }
    public function getProductDetail(Request $request)
    {
        $id = $request->id;
        $query = DB::table('orders')->select('id', 'main_product_id', 'gateway_id', 'order_status')->where('gateway_id', $id)->where('order_status', 7);
        $data['product_id'] = $query->get('main_product_id');
        $data['total_count'] = $query->count('id');
        return response()->json(['status' => true, 'data' => $data]);
    }
    public function get_assigned_mids(Request $request)
    {
        $mids = Mid::where(['user_id' => Auth::id(), 'is_active' => 1])->where('mid_group', 'like', '%' . $request->value . '%');
        $assigned_mids = $mids->count();
        if (isset($assigned_mids)) {
            return response()->json(['status' => true, 'mids' => $assigned_mids]);
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
        $data['user_id'] = Auth::id();
        if (!$request->bank_per) {
            $data['bank_per'] = '20';
        }
        $result = MidGroup::create($data);
        $id = $result->id;
        $this->refresh_mids_groups();
        return response()->json(['status' => true, 'mid_group_id'=> $id, ['message' => 'Mid Group created successfully']]);
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
        DB::table('mids')->where('mid_group', $mid_group->group_name)->update(['mid_group' => '']);
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
        $db_mid_group_names = MidGroup::where(['user_id' => Auth::id()])->pluck('group_name')->toArray();
        $mid_groups = MidGroup::where(['user_id' => Auth::id()])->get();

        $created = 0;
        $updated = 0;
        $result = [];

        foreach ($mid_groups as $mid_group) {

            if (in_array($mid_group->group_name, $db_mid_group_names)) {
                $group = MidGroup::where(['user_id' => Auth::id(), 'group_name' => $mid_group->group_name])->first();
                $group->update($result);
                $updated++;
            } else {
                $result['group_name'] = $mid_group->group_name;
                $result['user_id'] = Auth::id();
                MidGroup::create($result);
                $created++;
            }
            $result = [];
        }
        return response()->json(['status' => true, 'data' => ['new' => $created, 'updated' => $updated]]);
    }
}
