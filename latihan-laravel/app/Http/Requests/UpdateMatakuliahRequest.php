<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMatakuliahRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('matakuliah')->id;

        return [
            'kode' => ['sometimes', 'string', 'max:10', 'unique:matakuliahs,kode,'.$id],
            'nama' => ['sometimes', 'string', 'max:100'],
            'sks' => ['sometimes', 'integer', 'min:1', 'max:6'],
            'semester' => ['sometimes', 'integer', 'min:1', 'max:8'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kode.unique' => 'Kode matakuliah tersebut sudah terdaftar',
            'sks.max' => 'SKS maksimal 6',
            'semester.max' => 'Semester maksimal 8',
        ];
    }
}
