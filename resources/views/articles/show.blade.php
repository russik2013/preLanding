<?php
/**
 * Created by PhpStorm.
 * User: russik
 * Date: 12/7/2018
 * Time: 3:43 PM
 */
?>

@extends('layout.master')
@section('content')
    <style>
        .error{
            color: red;
        }
    </style>

    <div class="row justify-content-md-center">
        <form>

            <div class="form-group">
                <label for="exampleInputTitle">Title : </label>
                <label for="exampleInputTitle"><b>{{$article->title}}</b></label>

            </div>

            <div class="form-group">
                <label for="exampleInputTitle">Content</label><br/>
                <label for="exampleInputTitle">{!! $article->content !!}</label>

            </div>



            {!! csrf_field() !!}

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

