@extends('layout.master')
@section('content')

    <table class="table table-striped">

        <thead>
        <tr>
            <th scope="col">text</th>
            <th scope="col">photo</th>
            <th scope="col">url</th>
            <th scope="col">profit</th>
            <th scope="col">author</th>
            <th scope="col">groop</th>
            <th scope="col">control</th>
        </tr>

        </thead>
        <tbody>
        @foreach($sidebars as $sidebar)

            <tr>
                <td>{!! $sidebar->text !!}</td>
                <td>
                    <div class="col-md-4" style="padding-left: 0px;  padding-right: 0px;">
                        <img style="max-height: 100px; max-width: 100px;" src="{{asset('images/'.$sidebar->photo)}}" class="img-fluid">
                    </div>
                </td>
                <td>{{$sidebar->url}}</td>
                <td>{{$sidebar->profit}}</td>
                <td>{{$sidebar->people}}</td>
                <td>{{$sidebar->groop ? $sidebar->groop->name : '-'}}</td>
                <td>

                    <a class="btn btn-primary" href="{{route('admin.sidebar.edit', ['id' => $sidebar->id])}}" role="button">Edit</a>
                    <a class="btn btn-danger" href="{{route('admin.sidebar.delete', ['id' => $sidebar->id])}}">Delete</a>

                    {{--<a class="btn btn-success" href="{{route('book.show', ['id' => $book->id])}}">Show</a>--}}
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>

    <a class="btn btn-primary btn-lg btn-block" href="{{route('admin.sidebar.create')}}">Add new</a>
@endsection