<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LinkRequest extends FormRequest
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

            case 'admin.link.add' :

                return [
                    'name'     =>  'required|string|unique:links,name,NULL',
                ];

                break;

            case 'admin.link.update' :

                return [
                    'name'     =>  'required|string|unique:links,name,'.$this->route('link')->id,
                ];

                break;

        }

        return [
            //
        ];
    }
}
