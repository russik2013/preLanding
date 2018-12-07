<?php
/**
 * Created by PhpStorm.
 * User: russik
 * Date: 12/6/2018
 * Time: 3:09 PM
 */
?>
@extends('layout.master')
@section('content')
    <style>
        .error{
            color: red;
        }
    </style>

    <style>
        .error{
            color: red;
        }
        /*the container must be positioned relative:*/
        .custom-select {
            position: relative;
            font-family: Arial;
        }
        .custom-select select {
            display: none; /*hide original SELECT element:*/
        }
        .select-selected {
            background-color: DodgerBlue;
        }
        /*style the arrow inside the select element:*/
        .select-selected:after {
            position: absolute;
            content: "";
            top: 14px;
            right: 10px;
            width: 0;
            height: 0;
            border: 6px solid transparent;
            border-color: #fff transparent transparent transparent;
        }
        /*point the arrow upwards when the select box is open (active):*/
        .select-selected.select-arrow-active:after {
            border-color: transparent transparent #fff transparent;
            top: 7px;
        }
        /*style the items (options), including the selected item:*/
        .select-items div,.select-selected {
            color: #ffffff;
            padding: 8px 16px;
            border: 1px solid transparent;
            border-color: transparent transparent rgba(0, 0, 0, 0.1) transparent;
            cursor: pointer;
        }
        /*style items (options):*/
        .select-items {
            position: absolute;
            background-color: DodgerBlue;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 99;
        }
        /*hide the items when the select box is closed:*/
        .select-hide {
            display: none;
        }
        .select-items div:hover, .same-as-selected {
            background-color: rgba(0, 0, 0, 0.1);
        }
    </style>

    <div class="row justify-content-md-center">
        <form action="{{route('admin.link.add')}}" method="post" enctype="multipart/form-data">

            <div class="form-group">
                <label for="exampleInputTitle">Name</label>
                <input type="text" class="form-control" name="name" aria-describedby="titleHelp" placeholder="Enter name">
                @if ($errors->has('name'))
                    <div class="error">{{ $errors->first('name') }}</div>
                @endif
            </div>



            {!! csrf_field() !!}


            <div class="form-group">
                <label>Article</label>
                <div id="articles">

                </div>

            </div>

            <div class="form-group">
                <button id="add_article" type="button" class="btn btn-info">Add article</button>
            </div>






            <div class="form-group">
                <label>Sidebar groups</label>
                <div id="sidebargroups">

                </div>

            </div>

            <div class="form-group">
                <button id="add_sidebargroup" type="button" class="btn btn-info">Add sidebar group</button>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
            <a class="btn btn-danger" href="{{route('admin.link.index')}}">Back</a>
        </form>
    </div>

    <script src="/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
    <script src="/vendor/unisharp/laravel-ckeditor/adapters/jquery.js"></script>
    <script>
        $('textarea').ckeditor();
        // $('.textarea').ckeditor(); // if class is prefered.
    </script>
    <script>
        function remove(id){
            $("#"+id).remove();
            console.log('remove item' );
            console.log(id);
        }
        $( document ).ready(function() {



            var articles = JSON.parse('{!! json_encode($articles) !!}');
            var sidebargroup = JSON.parse('{!! json_encode($sidebargroup) !!}');


            $('#add_article').click(function () {
                id = Math.floor(Math.random() * 10000);
                var select = '<div class="form-group" id="'+id+'"><select name="articles[]" class="custom-select">';
                for(var i = 0; i < articles.length; i ++){
                    select += '<option value="'+articles[i].id+'">'+articles[i].title+'</option>';
                }
                select += "</select>" +
                    "<button  type='button' class='btn btn-danger' onclick='remove("+id+")'>remove</button></div>";
                $('#articles').append(select);
            });



            $('#add_sidebargroup').click(function () {
                id = Math.floor(Math.random() * 10000);
                var select = '<div class="form-group" id="'+id+'"><select name="sidebargroup[]" class="custom-select">';
                for(var i = 0; i < sidebargroup.length; i ++){
                    console.log(sidebargroup[i]);
                    select += '<option value="'+sidebargroup[i].id+'">'+sidebargroup[i].name+'</option>';
                }
                select += "</select>" +
                    "<button  type='button' class='btn btn-danger' onclick='remove("+id+")'>remove</button></div>";
                $('#sidebargroups').append(select);
            });
        });
    </script>


@endsection

