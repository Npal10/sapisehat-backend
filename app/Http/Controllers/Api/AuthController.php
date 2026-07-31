<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;

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
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ], [
            'name.required' => 'Harap isi Nama',
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo_url) {
                $oldPath = str_replace('/storage/', '', $user->photo_url);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('photo')->store('profiles', 'public');
            $validated['photo_url'] = \Illuminate\Support\Facades\Storage::url($path);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => $user,
        ]);
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

        // Simulasikan pengiriman email dengan menulisnya ke console (muncul di terminal php artisan serve)
        error_log("=== OTP RESET PASSWORD ===");
        error_log("Email: {$email}");
        error_log("OTP: {$otp}");
        error_log("==========================");

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
