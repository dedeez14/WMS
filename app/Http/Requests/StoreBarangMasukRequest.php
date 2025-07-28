<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangMasukRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_barang' => 'required|exists:barang,id',
            'qty' => 'required|integer|min:1|max:99999',
            'status_yang_mengembalikan' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'id_barang.required' => 'Barang wajib dipilih',
            'id_barang.exists' => 'Barang tidak ditemukan',
            'qty.required' => 'Jumlah wajib diisi',
            'qty.integer' => 'Jumlah harus berupa angka',
            'qty.min' => 'Jumlah minimal 1',
            'qty.max' => 'Jumlah maksimal 99999',
            'status_yang_mengembalikan.max' => 'Status maksimal 500 karakter',
        ];
    }
}
