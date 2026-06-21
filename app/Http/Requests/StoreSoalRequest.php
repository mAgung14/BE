<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSoalRequest extends FormRequest
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
            'kuis_id' => 'required|integer|exists:kuis,kuis_id',
            'soal_soal' => 'required|string',
            'gambar_soal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tipe_soal' => 'sometimes|required|string|in:pilihan_ganda,essay',
            'poin' => 'sometimes|required|integer|min:1',
            'jawaban_a' => 'required|string',
            'jawaban_b' => 'required|string',
            'jawaban_c' => 'required|string',
            'jawaban_d' => 'required|string',
            'jawaban_benar' => 'required|in:a,b,c,d',
            'gambar_jawaban_a' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gambar_jawaban_b' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gambar_jawaban_c' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gambar_jawaban_d' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'kuis_id.required' => 'ID Kuis wajib diisi.',
            'kuis_id.exists' => 'ID Kuis tidak ditemukan.',
            'soal_soal.required' => 'Pertanyaan soal wajib diisi.',
            'jawaban_a.required' => 'Jawaban A wajib diisi.',
            'jawaban_b.required' => 'Jawaban B wajib diisi.',
            'jawaban_c.required' => 'Jawaban C wajib diisi.',
            'jawaban_d.required' => 'Jawaban D wajib diisi.',
            'jawaban_benar.required' => 'Jawaban benar wajib dipilih.',
            'jawaban_benar.in' => 'Jawaban benar harus A, B, C, atau D.',
            'gambar_soal.image' => 'Gambar soal harus berupa gambar.',
            'gambar_soal.max' => 'Ukuran gambar soal maksimal 2MB.',
            'gambar_jawaban_a.image' => 'Gambar jawaban A harus berupa gambar.',
            'gambar_jawaban_a.max' => 'Ukuran gambar jawaban A maksimal 2MB.',
            'gambar_jawaban_b.image' => 'Gambar jawaban B harus berupa gambar.',
            'gambar_jawaban_b.max' => 'Ukuran gambar jawaban B maksimal 2MB.',
            'gambar_jawaban_c.image' => 'Gambar jawaban C harus berupa gambar.',
            'gambar_jawaban_c.max' => 'Ukuran gambar jawaban C maksimal 2MB.',
            'gambar_jawaban_d.image' => 'Gambar jawaban D harus berupa gambar.',
            'gambar_jawaban_d.max' => 'Ukuran gambar jawaban D maksimal 2MB.',
        ];
    }
}
