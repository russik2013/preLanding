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

            <button type="submit" class="btn btn-primary">Submit</button>
            <a class="btn btn-danger" href="{{route('admin.sidebar.groop.index')}}">Back</a>
        </form>
    </div>



@endsection
