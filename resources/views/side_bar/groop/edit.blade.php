@extends('layout.master')
@section('content')
    <style>
        .error{
            color: red;
        }
    </style>

    <div class="row justify-content-md-center">
        <form action="{{route('admin.sidebar.groop.update', ['id' => $goop->id])}}" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="exampleInputTitle">Name</label>
                <input type="text" class="form-control" value="{{$goop->name}}" name="name" aria-describedby="titleHelp" placeholder="Enter name">
                @if ($errors->has('name'))
                    <div class="error">{{ $errors->first('name') }}</div>
                @endif
            </div>

            <input type="hidden" class="form-control" value="{{$goop->id}}" name="id">

            <div class="form-group">
                <label>White site</label>
                <div class="form-group">
                    @if ($errors->has('white_site_flag'))
                        <div class="error">{{ $errors->first('white_site_flag') }}</div>
                    @endif
                    <select name="white_site_flag" class="custom-select">

                        <option @if($goop->white_site_flag == 1) selected @endif value="1">ON</option>
                        <option @if($goop->white_site_flag == 0) selected @endif value="0">OFF</option>

                    </select>
                </div>
            </div>

            {!! csrf_field() !!}

            <button type="submit" class="btn btn-primary">Submit</button>
            <a class="btn btn-danger" href="{{route('admin.sidebar.groop.index')}}">Back</a>
        </form>
    </div>



@endsection
