<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogRequest extends FormRequest
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
        return [
            'title' => ['required'],
            'description' => ['required'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')],
            'img_url' => ['nullable'],
            'created_by' => ['required'],
            'updated_by' => ['required'],
        ];

    }

    public function prepareForValidation()
    {

        $this->merge([
            'created_by' => authUser(true),
            'updated_by' => authUser(true),
        ]);
    }

    public function messages()
    {
        return [
            'title.required' => 'Title Cannot be Empty',
            'category_id.exists' => 'Not an existing category ID',
        ];
    }
}
