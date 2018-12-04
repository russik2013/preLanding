<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SideBarGroopRequest extends FormRequest
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

            case 'admin.sidebar.groop.add' :

                return [
                    'name' =>  'required|string',
                ];

            break;

            case 'admin.sidebar.groop.update' :

                return [
                    'name' =>  'required|string',
                    'id'   =>  'required|numeric|exists:side_bar_groops,id',
                ];

                break;

        }

        return [
        ];
    }
}
