<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'supplier_id' => ['required', 'uuid', 'exists:suppliers,id'],
            'price'       => ['required', 'numeric'],
            'file'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,csv,xlsx'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required'        => 'Поле name обязательно',
            'name.string'          => 'Поле name должно быть строкой',
            'name.max'             => 'Поле name не должно превышать 255 символов',

            'description.string'   => 'Поле description должно быть строкой',

            'category_id.required' => 'Поле category_id обязательно',
            'category_id.uuid'     => 'Поле category_id должно быть валидным UUID',
            'category_id.exists'   => 'Категория с таким ID не найдена',

            'supplier_id.required' => 'Поле supplier_id обязательно',
            'supplier_id.uuid'     => 'Поле supplier_id должно быть валидным UUID',
            'supplier_id.exists'   => 'Поставщик с таким ID не найден',

            'price.required'       => 'Поле price обязательно',
            'price.numeric'        => 'Поле price должно быть числом',

            'file.file'            => 'Поле file должно быть файлом',
            'file.mimes'           => 'Файл должен иметь одно из расширений: jpg, jpeg, png, pdf, doc, docx, csv, xlsx',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
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
