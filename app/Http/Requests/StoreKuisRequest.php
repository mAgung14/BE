<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreKuisRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'kategori' => ['required', 'string', 'max:100'],
            'soal_waktu' => ['required', 'integer', 'min:1'],
            'perm_istirahat' => ['sometimes', 'integer', 'min:0'],
            'akses' => ['sometimes', 'required', 'in:publik,private'],
            'tgl_dibuat' => ['sometimes', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul kuis wajib diisi.',
            'judul.max' => 'Judul kuis maksimal 255 karakter.',
            'kategori.required' => 'Kategori kuis wajib diisi.',
            'soal_waktu.required' => 'Waktu soal wajib diisi.',
            'soal_waktu.min' => 'Waktu soal minimal 1 menit.',
            'akses.in' => 'Akses harus publik atau private.',
        ];
    }
}
