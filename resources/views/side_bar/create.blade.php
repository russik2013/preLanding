@extends('layout.master')
@section('content')
    <style>
        .error{
            color: red;
        }
    </style>

    <div class="row justify-content-md-center">
        <form action="{{route('admin.sidebar.add')}}" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="exampleInputTitle">Text</label>
                <textarea class="form-control" rows="5" name="text"></textarea>
                @if ($errors->has('text'))
                    <div class="error">{{ $errors->first('text') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="exampleInputTitle">URL</label>
                <input type="text" class="form-control" name="url" aria-describedby="titleHelp" placeholder="Enter url">
                @if ($errors->has('url'))
                    <div class="error">{{ $errors->first('url') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="exampleFormControlPhoto">Photo</label>
                <input type="file" name="photo" class="form-control-file" >
                @if ($errors->has('photo'))
                    <div class="error">{{ $errors->first('photo') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="exampleFormControlPhoto">Profit</label>
                <input type="text" name="profit" class="form-control-file" >
                @if ($errors->has('profit'))
                    <div class="error">{{ $errors->first('profit') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label>Group</label>
                <div class="form-group">
                    @if ($errors->has('side_bar_groop_id'))
                        <div class="error">{{ $errors->first('side_bar_groop_id') }}</div>
                    @endif
                    <select name="side_bar_groop_id" class="custom-select">
                        @foreach($sideBarGroops as $sideBarGroop)

                            <option value="{{$sideBarGroop->id}}">{{$sideBarGroop->name}}</option>

                            @endforeach

                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="exampleFormControlPhoto">Author Name</label>
                <input type="text" name="people" class="form-control-file" >
                @if ($errors->has('people'))
                    <div class="error">{{ $errors->first('people') }}</div>
                @endif
            </div>

            {!! csrf_field() !!}

            <button type="submit" class="btn btn-primary">Submit</button>
            <a class="btn btn-danger" href="{{route('admin.sidebar.index')}}">Back</a>
        </form>
    </div>

    <script src="/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
    <script src="/vendor/unisharp/laravel-ckeditor/adapters/jquery.js"></script>
    <script>
        $('textarea').ckeditor();
        // $('.textarea').ckeditor(); // if class is prefered.
    </script>


@endsection
