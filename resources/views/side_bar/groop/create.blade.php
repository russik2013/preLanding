@extends('layout.master')
@section('content')
    <style>
        .error{
            color: red;
        }
    </style>

    <div class="row justify-content-md-center">
        <form action="{{route('admin.sidebar.groop.add')}}" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="exampleInputTitle">Name</label>
                <input type="text" class="form-control" name="name" aria-describedby="titleHelp" placeholder="Enter name">
                @if ($errors->has('name'))
                    <div class="error">{{ $errors->first('name') }}</div>
                @endif
            </div>
            {!! csrf_field() !!}

            <div class="form-group">
                <label>Tracking</label>
                <div class="form-group">
                    @if ($errors->has('white_site_flag'))
                        <div class="error">{{ $errors->first('white_site_flag') }}</div>
                    @endif
                    <select name="white_site_flag" class="custom-select">

                        <option selected value="1">ON</option>
                        <option value="0">OFF</option>

                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
            <a class="btn btn-danger" href="{{route('admin.sidebar.groop.index')}}">Back</a>
        </form>
    </div>



@endsection
