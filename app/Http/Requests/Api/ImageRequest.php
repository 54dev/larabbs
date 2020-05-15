<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ImageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rule = ['type' => 'required|string|in:avatar,topic'];

        if($this->type == 'avatar'){
            $rule['image'] = 'required|mimes:jpeg,bmp,png,gif|dimensions:min_width=200,min_height=200';
        }else{
            $rule['image'] = 'required|mimes:jpeg,bmp,png,gif';
        }

        return $rule;
    }

    public function messages()
    {
        return [
            'image.dimensions' => '图片的清晰度不够'
        ];
    }
}
