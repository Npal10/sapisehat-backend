<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Mail\OtpMail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // butuh password_confirmation di frontend
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Tolong masukan email anda',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Tolong masukan kata sandi anda',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            // Hashing password sangat penting untuk mencegah kebocoran data
            'password' => Hash::make($request->password), 
            'fcm_token' => $request->fcm_token,
        ]);

        $token = $user->createToken('mobile_app_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Tolong masukan email anda',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Tolong masukan kata sandi anda',
        ]);

        $user = User::where('email', $request->email)->first();

        // Validasi kredensial
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi yang Anda masukkan salah.'],
            ]);
        }

        // Hapus token lama agar tidak menumpuk (opsional, untuk keamanan ekstra)
        $user->tokens()->delete();

        if ($request->has('fcm_token') && $request->fcm_token) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        $token = $user->createToken('mobile_app_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        // Mencabut token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'name'         => 'required|string|max:255',
                'location'     => 'nullable|string|max:255',
                'photo_base64' => 'nullable|string',
            ], [
                'name.required' => 'Harap isi Nama',
            ]);

            // Jika ada foto baru dikirim, simpan ke kolom photo_base64
            if (!empty($validated['photo_base64'])) {
                $user->photo_base64 = $validated['photo_base64'];
            }

            $user->name     = $validated['name'];
            $user->location = $validated['location'] ?? $user->location;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data'    => $user,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'email yang anda masukan salah',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.'
        ]);
        $email = $request->email;

        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));

        // Delete existing OTP for this email
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Insert new OTP
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $otp,
            'created_at' => now(),
        ]);

        // Mengirimkan email OTP menggunakan API langsung (Bypass Symfony Mailer)
        try {
            $brevoKey = env('BREVO_KEY');
            if (!$brevoKey) {
                return response()->json(['message' => 'BREVO_KEY belum diatur di Railway.'], 500);
            }

            $response = Http::timeout(5)->withHeaders([
                'api-key' => $brevoKey,
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => env('MAIL_FROM_NAME', 'SapiSehat Admin'),
                    'email' => env('MAIL_FROM_ADDRESS', 'admin@sapisehat.com')
                ],
                'to' => [
                    ['email' => $email]
                ],
                'subject' => 'Kode OTP Reset Password SapiSehat',
                'htmlContent' => '<html><body style="font-family: Arial, sans-serif; text-align: center; padding: 20px;"><h2>SapiSehat</h2><p>Kode OTP Anda adalah:</p><h1 style="color: #1565C0; letter-spacing: 5px;">' . $otp . '</h1><p>Berlaku selama 15 menit.</p></body></html>'
            ]);

            if (!$response->successful()) {
                return response()->json(['message' => 'Gagal dari server Brevo: ' . $response->body()], 500);
            }

        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'OTP telah dikirim ke email Anda.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8', 
        ], [
            'email.exists' => 'email yang anda masukan salah',
            'otp.required' => 'OTP wajib diisi.',
            'password.required' => 'Password wajib diisi.'
        ]);

        $resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->otp)
            ->first();

        if (!$resetRecord) {
            throw ValidationException::withMessages([
                'otp' => ['OTP tidak valid atau salah.'],
            ]);
        }

        // Check if OTP is expired (e.g., 15 minutes)
        if (\Carbon\Carbon::parse($resetRecord->created_at)->addMinutes(15)->isPast()) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            throw ValidationException::withMessages([
                'otp' => ['OTP sudah kedaluwarsa.'],
            ]);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete OTP
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Kata sandi berhasil direset. Silakan login dengan kata sandi baru.']);
    }
}
