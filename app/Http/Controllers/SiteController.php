<?php

namespace App\Http\Controllers;

use App\Artecle;
use App\Http\Requests\SiteSettingRequest;
use App\Links;
use App\SideBarGroop;
use App\SiteSetting;
use App\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class SiteController extends Controller
{
    protected $link = null;
    public function __construct()
    {
        if(Route::current()->parameter('param')){
            $this->link = Links::where('name', 'like', Route::current()->parameter('param')) ->with('getParamsArticles.params', 'getParamsSideBarGroups.params') ->first();
        }

    }

    public function index()
    {
        if(!$this->link){
            return view('index')
                -> with('setting', SiteSetting::first())
                ->with('article', Artecle::first());
        }

        if($this->link){
            $article = $this->link->getParamsArticles->first() ? $this->link->getParamsArticles->first()->params : Artecle::first();
            return view('index')
                -> with('setting', SiteSetting::first())
                ->with('article', $article);
        }

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
        $sidebars = SideBarGroop::with('items')->get();

        if($this->link && $this->link->getParamsSideBarGroups){
            $groupdIdsArray = $this->link->getParamsSideBarGroups->pluck('params_id')->toArray();
            if(!empty($groupdIdsArray)){
                $sidebars = SideBarGroop::whereIn('id', $groupdIdsArray)->with('items')->get();
            }
        }
        $view->with('SideBarGroop', $sidebars);
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
