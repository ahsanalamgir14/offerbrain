@extends('layouts.app')

@section('content')
<div class="container login-content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Enter Array of missing orders ids:</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('missing-history.post') }}">
                        @csrf
                        <label for="">Order ids:</label>
                        <input type="text" id="orders_array" name="orders_array" value="">
                        <button class="btn btn-primary" type="submit">Insert</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection