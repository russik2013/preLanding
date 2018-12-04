<?php

namespace App\Http\Controllers;

use App\Http\Requests\SideBarRequest;
use App\SideBar;
use App\SideBarGroop;
use Illuminate\Http\Request;

class SideBarController extends Controller
{
    public function index()
    {
       return view('side_bar.index') -> with('sidebars', SideBar::all());
    }

    public function create()
    {
        return view('side_bar.create') -> with('sideBarGroops', SideBarGroop::all());
    }

    public function add(SideBarRequest $request, SideBar $sideBar)
    {
        $sideBar -> fill($request->all());
        $sideBar -> save();

        return redirect()->route('admin.sidebar.index');
    }

    public function edit($id, SideBarGroop $groops)
    {
        return view('side_bar.edit')
            -> with('sideBarGroops', $groops->get())
            -> with('sideBar', SideBar::find($id));
    }

    public function update(SideBarRequest $request, $id )
    {
        $sideBar = SideBar::find($id);
        $sideBar -> fill($request->all());
        $sideBar -> save();
        return redirect()->route('admin.sidebar.index');

    }

    public function delete($id)
    {
        if(SideBar::find($id))
        {
            SideBar::find($id)->delete();
        }

        return redirect()->route('admin.sidebar.index');
    }
}
