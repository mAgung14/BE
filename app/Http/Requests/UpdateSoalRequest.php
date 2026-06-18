<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSoalRequest extends FormRequest
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
            'soal_soal' => 'sometimes|required|string',
            'gambar_soal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'poin' => 'nullable|integer|min:1',
            'jawaban_a' => 'sometimes|required|string',
            'jawaban_b' => 'sometimes|required|string',
            'jawaban_c' => 'sometimes|required|string',
            'jawaban_d' => 'sometimes|required|string',
            'jawaban_benar' => 'sometimes|required|in:a,b,c,d',
            'gambar_jawaban_a' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gambar_jawaban_b' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gambar_jawaban_c' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gambar_jawaban_d' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'soal_soal.required' => 'Pertanyaan soal wajib diisi.',
            'jawaban_a.required' => 'Jawaban A wajib diisi.',
            'jawaban_b.required' => 'Jawaban B wajib diisi.',
            'jawaban_c.required' => 'Jawaban C wajib diisi.',
            'jawaban_d.required' => 'Jawaban D wajib diisi.',
            'jawaban_benar.in' => 'Jawaban benar harus A, B, C, atau D.',
            'gambar_soal.image' => 'Gambar soal harus berupa gambar.',
            'gambar_soal.max' => 'Ukuran gambar soal maksimal 2MB.',
        ];
    }
}
