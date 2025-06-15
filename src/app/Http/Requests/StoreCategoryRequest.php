<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'uuid', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Поле name обязательно',
            'name.string'    => 'Поле name должно быть строкой',
            'name.max'       => 'Поле name не должно превышать 255 символов',

            'parent_id.uuid'   => 'Поле parent_id должно быть валидным UUID',
            'parent_id.exists' => 'Родительская категория не найдена',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(response()->json([
            'message'   => 'Ошибка валидации',
            'data'      => ['field' => $errors],
            'timestamp' => now()->toIso8601ZuluString(),
            'success'   => false,
        ], 422));
    }
}
