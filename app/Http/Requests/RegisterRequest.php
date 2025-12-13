<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Semua orang boleh register
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
            'role' => 'nullable|in:driver,company,family,admin',
            'phone' => 'required|string|max:20',
            'license_number' => 'required_if:role,driver|string|max:50|unique:driver_details',
            'license_expiry' => 'required_if:role,driver|date|after:today',
            'company_name' => 'required_if:role,company|string|max:255',
            'company_email' => 'required_if:role,company|email|unique:companies,email',
            'emergency_contacts' => 'nullable|array',
            'emergency_contacts.*.name' => 'required_with:emergency_contacts|string',
            'emergency_contacts.*.phone' => 'required_with:emergency_contacts|string',
            'emergency_contacts.*.relationship' => 'required_with:emergency_contacts|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap harus diisi',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password harus diisi',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'phone.required' => 'Nomor telepon harus diisi',
            'license_number.required_if' => 'Nomor SIM harus diisi untuk pengemudi',
            'license_number.unique' => 'Nomor SIM sudah terdaftar',
            'license_expiry.required_if' => 'Tanggal berakhir SIM harus diisi',
            'license_expiry.after' => 'Tanggal berakhir SIM harus setelah hari ini',
            'company_name.required_if' => 'Nama perusahaan harus diisi',
            'company_email.required_if' => 'Email perusahaan harus diisi',
            'company_email.unique' => 'Email perusahaan sudah terdaftar',
        ];
    }

    public function prepareForValidation(): void
    {
        // Set default role ke driver jika tidak diisi
        if (!$this->has('role')) {
            $this->merge(['role' => 'driver']);
        }
    }
}