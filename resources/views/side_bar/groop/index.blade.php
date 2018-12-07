@extends('layout.master')
@section('content')

<table class="table table-striped">

    <thead>
    <tr>
        <th scope="col">name</th>
        {{--<th scope="col">photo</th>--}}
        <th scope="col">control</th>
    </tr>

    </thead>
    <tbody>
    @foreach($goops as $groop)
        <tr>
            <td>{{$groop->name}}</td>
            {{--<td>--}}
                {{--<div class="col-md-4" style="padding-left: 0px;  padding-right: 0px;">--}}
                    {{--<img style="max-height: 100px; max-width: 100px;" src="{{asset('images/'.$groop->photo)}}" class="img-fluid">--}}
                {{--</div>--}}
            {{--</td>--}}
            <td>

                <a class="btn btn-primary" href="{{route('admin.sidebar.groop.edit', ['id' => $groop->id])}}" role="button">Edit</a>
                <a class="btn btn-danger" href="{{route('admin.sidebar.groop.delete', ['id' => $groop->id])}}">Delete</a>

                {{--<a class="btn btn-success" href="{{route('book.show', ['id' => $book->id])}}">Show</a>--}}
            </td>

        </tr>
    @endforeach
    </tbody>
</table>

<a class="btn btn-primary btn-lg btn-block" href="{{route('admin.sidebar.groop.create')}}">Add new</a>
@endsection