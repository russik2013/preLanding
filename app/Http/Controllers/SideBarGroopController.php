<?php

namespace App\Http\Controllers;

use App\Http\Requests\SideBarGroopRequest;
use App\SideBarGroop;
use Illuminate\Http\Request;

class SideBarGroopController extends Controller
{
    public function index()
    {
        return view('side_bar.groop.index') -> with('goops', SideBarGroop::all());
    }

    public function create()
    {
        return view('side_bar.groop.create');
    }

    public function add(SideBarGroopRequest $request)
    {
        SideBarGroop::create($request->all());
        return redirect()->route('admin.sidebar.groop.index');
    }

    public function edit($id)
    {
        return view('side_bar.groop.edit') -> with('goop', SideBarGroop::find($id));
    }

    public function update(SideBarGroopRequest $request, $id)
    {

        SideBarGroop::where('id', $id)->update($request->only('name', 'white_site_flag'));
        return redirect()->route('admin.sidebar.groop.index');
    }

    public function delete($id)
    {
        if(SideBarGroop::find($id)){
            SideBarGroop::find($id)->delete();
        }
        return redirect()->route('admin.sidebar.groop.index');
    }

    public function show($id)
    {
        return view('side_bar.groop.edit') -> with('goop', SideBarGroop::find($id));
    }
}
