<?php

namespace App\Http\Controllers;

use App\Models\Automation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutomationController extends Controller
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
        $data = new Automation();
        $affiliate_id = [];
        foreach($request->networks as $network){
            array_push($affiliate_id, $network['id']);
        }
        $data->user_id = Auth::id();
        $data->automation_name = $request->name;
        $data->automation_type = $request->automation_type;
        $data->throttle_resource = $request->automation_resource;
        $data->affiliate = $request->networks;
        $data->affiliate_id = $affiliate_id;
        $data->sub_affiliate = $request->affiliate;
        $data->cpa = $request->cpa;
        $data->cap = $request->cap;
        $data->trigger = $request->trigger;
        $data->operator = $request->operator;
        $data->lookback = $request->lookback;
        $data->action = $request->throttle_action;
        if($request->prefire_target){
            $data->is_prefire_reach_target = 1;
        }
        $data->prefire_resource = $request->prefire_resource;
        $data->timeframe = $request->timeframe;
        if($request->is_per_day){
            $data->is_per_day = 1;
        }
        $data->time_from = $request->time_from;
        $data->time_to = $request->time_to;
        if($data->save()){
            return response()->json(['status' => true, 'message' => 'Automation saved successfully']);
        } else {
            return response()->json(['status' => false, 'message' => "Oops! Something went wrong."]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Automation  $automation
     * @return \Illuminate\Http\Response
     */
    public function show(Automation $automation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Automation  $automation
     * @return \Illuminate\Http\Response
     */
    public function edit(Automation $automation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Automation  $automation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Automation $automation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Automation  $automation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Automation $automation)
    {
        //
    }
}
