@extends('layout.master')
@section('content')
    <style>
        .error{
            color: red;
        }
    </style>

    <div class="row justify-content-md-center">
        <form action="{{route('admin.site.setting.update')}}" method="post">

            <div class="form-group">
                <label>Amount</label>
                <div class="form-group">
                    @if ($errors->has('amount'))
                        <div class="error">{{ $errors->first('amount') }}</div>
                    @endif
                    <select name="amount" class="custom-select">
                        @foreach($allAmount as $key => $value)

                            <option @if($settings->amount == $key) selected @endif value="{{$key}}">{{$key}} ({{$value}})</option>

                        @endforeach

                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Amount</label>
                <div class="form-group">
                    @if ($errors->has('tracking_flag'))
                        <div class="error">{{ $errors->first('tracking_flag') }}</div>
                    @endif
                    <select name="tracking_flag" class="custom-select">

                        <option @if($settings->tracking_flag == 1) selected @endif value="1">ON</option>
                        <option @if($settings->tracking_flag == 0) selected @endif value="0">OFF</option>

                    </select>
                </div>
            </div>

            {!! csrf_field() !!}

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

@endsection