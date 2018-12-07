<?php
/**
 * Created by PhpStorm.
 * User: russik 
 * Date: 12/6/2018
 * Time: 1:16 PM
 */

?>
@extends('layout.master')
@section('content')

    <table class="table table-striped">

        <thead>
        <tr>
            <th scope="col">name</th>
            <th scope="col">articles</th>
            <th scope="col">sidebargroups</th>
            <th scope="col">control</th>
        </tr>

        </thead>
        <tbody>
        @foreach($links as $link)

            <tr>
                <td>{{ $link->name }}</td>
                <td>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">name</th>

                        </tr>
                        </thead>
                        <tbody>
                            @foreach($link->getParamsArticles as $articles)
                                <tr>
                                    <td><a href="{{route('admin.article.show', ['id' => $articles->params->id])}}" >{{ $articles->params ? $articles->params->title : '-' }}</a></td>
                                </tr>
                             @endforeach
                        </tbody>
                    </table>

                 </td>

                <td>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">name</th>

                        </tr>
                        </thead>
                        <tbody>
                            @foreach($link->getParamsSideBarGroups as $sideBarGroup)
                                <tr>
                                    <td><a href="{{route('admin.sidebar.groop.show', ['id' => $sideBarGroup->params->id])}}" >{{ $sideBarGroup->params ? $sideBarGroup->params->name : '-' }}</a></td>
                                </tr>
                             @endforeach
                        </tbody>
                    </table>

                 </td>
                <td>

                    <a class="btn btn-primary" href="{{route('admin.link.edit', ['id' => $link->id])}}" role="button">Edit</a>
                    <a class="btn btn-danger" href="{{route('admin.link.delete', ['id' => $link->id])}}">Delete</a>

                    {{--<a class="btn btn-success" href="{{route('book.show', ['id' => $book->id])}}">Show</a>--}}
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>

    <a class="btn btn-primary btn-lg btn-block" href="{{route('admin.link.create')}}">Add new</a>
@endsection
