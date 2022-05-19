@extends('layouts.app')

@section('content')
<div class="container login-content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Orders updated successfully:</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="">New Orders:</label>
                            <input type="text" value="{{$new_orders}}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="">Updated Order:</label>
                            <input type="text" value="{{$updated_orders}}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection