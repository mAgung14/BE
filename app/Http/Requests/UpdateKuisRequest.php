<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKuisRequest extends FormRequest
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
            'judul' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'sometimes|required|string|max:100',
            'soal_waktu' => 'sometimes|required|integer|min:1',
            'perm_istirahat' => 'nullable|integer|min:0',
            'akses' => 'sometimes|required|in:publik,private',
            'status' => 'sometimes|required|in:draft,aktif,selesai',
            'is_published' => 'nullable|boolean',
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
            'status.in' => 'Status harus draft, aktif, atau selesai.',
        ];
    }
}
