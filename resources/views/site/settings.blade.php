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

            {!! csrf_field() !!}

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

@endsection