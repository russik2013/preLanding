@extends('layout.master')
@section('content')
    <style>
        .error{
            color: red;
        }
    </style>

    <div class="row justify-content-md-center">
        <form action="{{route('admin.article.add')}}" method="post" enctype="multipart/form-data">

            <div class="form-group">
                <label for="exampleInputTitle">Title</label>
                <input type="text" class="form-control" name="title" aria-describedby="titleHelp" placeholder="Enter title">
                @if ($errors->has('title'))
                    <div class="error">{{ $errors->first('title') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="exampleInputTitle">Content</label>
                <textarea class="form-control" rows="5" name="content" id="content"></textarea>
                @if ($errors->has('content'))
                    <div class="error">{{ $errors->first('content') }}</div>
                @endif
            </div>


            <div class="form-group">
                <label>Tracking</label>
                <div class="form-group">
                    @if ($errors->has('tracking_flag'))
                        <div class="error">{{ $errors->first('tracking_flag') }}</div>
                    @endif
                    <select name="tracking_flag" class="custom-select">

                        <option selected value="1">ON</option>
                        <option value="0">OFF</option>

                    </select>
                </div>
            </div>


            <div class="form-group">
                <label>Comment</label>
                <div class="form-group">
                    @if ($errors->has('tracking_flag'))
                        <div class="error">{{ $errors->first('tracking_flag') }}</div>
                    @endif
                    <select name="comment_flag" class="custom-select">

                        <option selected value="1">ON</option>
                        <option value="0">OFF</option>

                    </select>
                </div>
            </div>

            {!! csrf_field() !!}

            <button type="submit" class="btn btn-primary">Submit</button>
            <a class="btn btn-danger" href="{{route('admin.article.index')}}">Back</a>
        </form>
    </div>
    <script src="/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
    <script src="/vendor/unisharp/laravel-ckeditor/adapters/jquery.js"></script>
    <script>
        $('textarea').ckeditor();
        // $('.textarea').ckeditor(); // if class is prefered.
    </script>

@endsection
