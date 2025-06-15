<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // если пришло — проверяем, иначе пропускаем
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category_id' => ['sometimes', 'required', 'uuid', 'exists:categories,id'],
            'supplier_id' => ['sometimes', 'required', 'uuid', 'exists:suppliers,id'],
            'price'       => ['sometimes', 'required', 'numeric'],
            'file'        => ['sometimes', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,csv,xlsx'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Поле name обязательно',
            'category_id.required' => 'Поле category_id обязательно',
            'supplier_id.required' => 'Поле supplier_id обязательно',
            'price.required'       => 'Поле price обязательно',
            // остальные сообщения как ранее…
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
