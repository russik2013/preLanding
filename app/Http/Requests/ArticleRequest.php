<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
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

            case 'admin.article.add' :

                return [
                    'title'     =>  'required|string',
                    'content'   =>  'required|string',
                ];

                break;

            case 'admin.article.update' :
                return [
                    'title'     =>  'required|string',
                    'content'   =>  'required|string',
                ];

                break;

        }

        return [
            //
        ];
    }
}
