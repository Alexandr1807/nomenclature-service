<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'contact_name'  => ['nullable', 'string', 'max:255'],
            'website'       => ['nullable', 'url', 'max:255'],
            'description'   => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Поле name обязательно',
            'name.string'           => 'Поле name должно быть строкой',
            'name.max'              => 'Поле name не должно превышать 255 символов',
            'phone.string'          => 'Поле phone должно быть строкой',
            'phone.max'             => 'Поле phone не должно превышать 50 символов',
            'contact_name.string'   => 'Поле contact_name должно быть строкой',
            'contact_name.max'      => 'Поле contact_name не должно превышать 255 символов',
            'website.url'           => 'Поле website должно быть корректным URL',
            'website.max'           => 'Поле website не должно превышать 255 символов',
            'description.string'    => 'Поле description должно быть строкой',
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
