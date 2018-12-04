<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SideBarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        switch (request()::route()->getName()){

            case 'admin.sidebar.add' :

                return [
                    'text'              =>  'required|string',
                    'url'               =>  'required|string|url',
                    'photo'             =>  'required|image|max:10240',
                    'side_bar_groop_id' =>  'required|numeric|exists:side_bar_groops,id',
                    'profit'            =>  'required|numeric',
                    'people'            =>  'required|string',
                ];

                break;
            case 'admin.sidebar.update' :

                return [
                    'text'              =>  'required|string',
                    'url'               =>  'required|string|url',
                    'photo'             =>  'nullable|image|max:10240',
                    'side_bar_groop_id' =>  'required|numeric|exists:side_bar_groops,id',
                    'profit'            =>  'required|numeric',
                    'people'            =>  'required|string',
                ];

                break;

        }

        return [
            //
        ];
    }
}
