<?php

namespace App\Http\Controllers;

use App\Artecle;
use App\Http\Requests\SiteSettingRequest;
use App\SideBarGroop;
use App\SiteSetting;
use App\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    public function index()
    {
        return view('index')
            -> with('setting', SiteSetting::first())
            ->with('article', Artecle::first());
    }

    public function login()
    {
        return view('site.login');
    }

    public function auth(Request $request)
    {


//        $user = new User();
//
//        $user->name = 'oleg';
//        $user->email = 'oleg@gmail.com';
//        $user->password = bcrypt('oleg123454321');
//
//        $user->save();

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->route('admin.article.index');
        }

        return back()->withErrors(['login_error' => 'wrong login data'])->withInput();
    }

    public function rightSideBar(View $view)
    {
        $view->with('SideBarGroop', SideBarGroop::with('items')->get());
    }

    public function siteSittings(SiteSetting $setting)
    {
        return view('site.settings')
            -> with('settings', $setting->first())
            -> with('allAmount', config('globalsiteamount'));
    }

    public function siteSittingsUpdate(SiteSetting $setting, SiteSettingRequest $request)
    {
        $setting = $setting->first();
        $setting->fill($request->all());
        $setting->save();

        return redirect()->back();
    }
}
