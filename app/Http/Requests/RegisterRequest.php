<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Semua user boleh melakukan request register
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules validasi register (API friendly)
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            'role' => 'nullable|in:driver,company,family,admin',
            'phone' => 'required|string|max:20',

            // Driver
            'license_number' => 'required_if:role,driver|string|max:50|unique:driver_details,license_number',
            'license_expiry' => 'required_if:role,driver|date|after:today',

            // Company
            'company_name' => 'required_if:role,company|string|max:255',
            'company_email' => 'required_if:role,company|email|unique:companies,email',

            // Emergency contacts
            'emergency_contacts' => 'nullable|array',
            'emergency_contacts.*.name' => 'required_with:emergency_contacts|string|max:255',
            'emergency_contacts.*.phone' => 'required_with:emergency_contacts|string|max:20',
            'emergency_contacts.*.relationship' => 'required_with:emergency_contacts|string|max:50',
        ];
    }

    /**
     * Pesan error custom (Bahasa Indonesia)
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'phone.required' => 'Nomor telepon wajib diisi',

            'license_number.required_if' => 'Nomor SIM wajib diisi untuk driver',
            'license_number.unique' => 'Nomor SIM sudah terdaftar',
            'license_expiry.required_if' => 'Masa berlaku SIM wajib diisi',
            'license_expiry.after' => 'Tanggal SIM harus setelah hari ini',

            'company_name.required_if' => 'Nama perusahaan wajib diisi',
            'company_email.required_if' => 'Email perusahaan wajib diisi',
            'company_email.unique' => 'Email perusahaan sudah terdaftar',
        ];
    }

    /**
     * Paksa response VALIDASI selalu JSON (tidak HTML)
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Set default role jika tidak dikirim
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('role')) {
            $this->merge([
                'role' => 'driver'
            ]);
        }
    }
}
