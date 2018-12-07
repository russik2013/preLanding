<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/{param?}', 'SiteController@index')->name('home');

Route::get('admin/login', 'SiteController@login')->name('admin.login');
Route::post('admin/auth', 'SiteController@auth')->name('admin.auth');


Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function (){

    Route::group(['prefix' => 'site'], function (){

        Route::get('setting',            'SiteController@siteSittings')         ->name('admin.site.setting');
        Route::post('setting',           'SiteController@siteSittingsUpdate')   ->name('admin.site.setting.update');

    });

    Route::group(['prefix' => 'article'], function (){

        Route::get('/',                 'ArticleController@index')          ->  name('admin.article.index');
        Route::get('create',            'ArticleController@create')         ->  name('admin.article.create');
        Route::post('add',              'ArticleController@add')            ->  name('admin.article.add');
        Route::get('edit/{id}',         'ArticleController@edit')           ->  name('admin.article.edit');
        Route::post('update/{id}',      'ArticleController@update')         ->  name('admin.article.update');
        Route::get('delete/{id}',       'ArticleController@delete')         ->  name('admin.article.delete');
        Route::get('show/{article}',    'ArticleController@show')           ->  name('admin.article.show');

    });

    Route::group(['prefix' => 'sidebar'], function (){

        Route::group(['prefix' => 'groop'], function (){

            Route::get('/',              'SideBarGroopController@index')   ->name('admin.sidebar.groop.index');
            Route::get('/create',        'SideBarGroopController@create')  ->name('admin.sidebar.groop.create');
            Route::post('/add',          'SideBarGroopController@add')     ->name('admin.sidebar.groop.add');
            Route::get('/edit/{id}',     'SideBarGroopController@edit')    ->name('admin.sidebar.groop.edit');
            Route::post('/update/{id}',  'SideBarGroopController@update')  ->name('admin.sidebar.groop.update');
            Route::get('/delete/{id}',   'SideBarGroopController@delete')  ->name('admin.sidebar.groop.delete');
            Route::get('/show/{id}',     'SideBarGroopController@show')    ->name('admin.sidebar.groop.show');

        });



        Route::get('/',               'SideBarController@index')    ->name('admin.sidebar.index');
        Route::get('/create',         'SideBarController@create')   ->name('admin.sidebar.create');
        Route::post('/add',           'SideBarController@add')      ->name('admin.sidebar.add');
        Route::get('/edit/{id}',      'SideBarController@edit')     ->name('admin.sidebar.edit');
        Route::post('/update/{id}',   'SideBarController@update')   ->name('admin.sidebar.update');
        Route::get('/delete/{id}',    'SideBarController@delete')   ->name('admin.sidebar.delete');

    });

    Route::group(['prefix' => 'link'], function (){

        Route::get('/',                 'LinksController@index')    -> name('admin.link.index');
        Route::get('/create',           'LinksController@create')   -> name('admin.link.create');
        Route::post('/add',             'LinksController@add')      -> name('admin.link.add');
        Route::get('/edit/{link}',      'LinksController@edit')     -> name('admin.link.edit');
        Route::post('/update/{link}',   'LinksController@update')   -> name('admin.link.update');
        Route::get('/delete/{link}',    'LinksController@delete')   -> name('admin.link.delete');
//        Route::get('/{sidebar}', function (App\SideBar $sidebar){
//            dd($sidebar);
//        });


    });

});
//Route::get('articles', );