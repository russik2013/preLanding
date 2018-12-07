<?php

namespace App\Http\Controllers;

use App\Artecle;
use App\Http\Requests\LinkRequest;
use App\LinkParams;
use App\Links;
use App\SideBarGroop;
use Illuminate\Http\Request;

class LinksController extends Controller
{
    protected $link;

    public function __construct(Links $link)
    {
        $this->link     = $link;
    }

    public function index()
    {
        return view('link.index')
            ->with('links', $this->link->with('getParamsArticles.params', 'getParamsSideBarGroups.params')->get());
    }

    public function create()
    {
        return view('link.create')
            ->with('articles', Artecle::get(['id','title']))
            ->with('sidebargroup', SideBarGroop::get(['id','name']));
    }


    public function add(LinkRequest $request)
    {
        $this->link->fill($request->all());
        $this->link->save();

        $params = $this -> formBookParamsArray($request, $this->link);
        LinkParams::insert($params);

        return redirect() -> route('admin.link.index');
    }

    public function edit(Links $link)
    {
        return view('link.edit')
            -> with('link', $link)
            ->with('articles', Artecle::get(['id','title']))
            ->with('sidebargroups', SideBarGroop::get(['id','name']));
    }

    public function update(LinkRequest $request, Links $link)
    {
        $link->fill($request->all());
        $link->save();

        $link -> allParams()->delete();

        $params = $this -> formBookParamsArray($request, $link);
        LinkParams::insert($params);

        return redirect() -> route('admin.link.index');
    }

    public function delete(Links $link)
    {
        $link->delete();
        return redirect() -> route('admin.link.index');
    }

    private function formBookParamsArray($request, $link)
    {
        $params = [];
        if($request->articles){
            foreach (array_unique($request->articles) as $article){
                $params[] = [
                    'params_type' => 'App\Artecle',
                    'params_id'   => $article,
                    'link_id'     => $link->id,
                ];
            }
        }
        if($request->sidebargroup){
            foreach (array_unique($request->sidebargroup) as $sidebargroup){
                $params[] = [
                    'params_type' => 'App\SideBarGroop',
                    'params_id'   => $sidebargroup,
                    'link_id'         => $link->id,
                ];
            }
        }
        return $params;
    }

}
