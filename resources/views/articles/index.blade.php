@extends('layout.master')
@section('content')

    <table class="table table-striped">

        <thead>
        <tr>
            <th scope="col">title</th>
            <th scope="col">content</th>

            <th scope="col">control</th>
        </tr>

        </thead>
        <tbody>
        @foreach($articles as $article)

            <tr>
                <td>{!! $article->title !!}</td>
                <td>{!! $article->content !!}</td>
                <td>

                    <a class="btn btn-primary" href="{{route('admin.article.edit', ['id' => $article->id])}}" role="button">Edit</a>
                    <a class="btn btn-primary" href="{{route('admin.article.show', ['article' => $article->id])}}">Show</a>
                    <a class="btn btn-danger" href="{{route('admin.article.delete', ['id' => $article->id])}}">Delete</a>

                    {{--<a class="btn btn-success" href="{{route('book.show', ['id' => $book->id])}}">Show</a>--}}
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>

    <a class="btn btn-primary btn-lg btn-block" href="{{route('admin.article.create')}}">Add new</a>
@endsection