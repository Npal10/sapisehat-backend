<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cow;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    /**
     * Halaman Login Admin
     */
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    /**
     * Proses Login Admin
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Email wajib diisi',
            'password.required' => 'Password wajib diisi',
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $user = Auth::user();
            if ($user->role === 'admin') {
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'));
            }
            Auth::logout();
            return back()->withErrors(['email' => 'Akun Anda tidak memiliki hak akses Admin.']);
        }

        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    /**
     * Dashboard Utama Web Admin
     */
    public function index()
    {
        $totalPeternak = User::where('role', 'peternak')->count();
        $totalSapi     = Cow::count();
        $totalScan     = Scan::count();

        // Statistik Penyakit Terdeteksi
        $totalPmk   = Scan::where('fmd_risk', 'PMK')->count();
        $totalLsd   = Scan::where('fmd_risk', 'LSD')->count();
        $totalSehat = Scan::where('fmd_risk', 'Sehat')->count();

        // Riwayat Deteksi Terbaru (10 Transaksi)
        $recentScans = Scan::with(['cow.user'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalPeternak',
            'totalSapi',
            'totalScan',
            'totalPmk',
            'totalLsd',
            'totalSehat',
            'recentScans'
        ));
    }

    /**
     * Pemantauan Seluruh Riwayat Scan Wabah Global
     */
    public function scans(Request $request)
    {
        $query = Scan::with(['cow.user']);

        // Filter Berdasarkan Penyakit
        if ($request->filled('disease')) {
            $query->where('fmd_risk', $request->disease);
        }

        // Filter Berdasarkan Tingkat Risiko
        if ($request->filled('risk')) {
            $query->where('lsd_risk', $request->risk);
        }

        // Pencarian Nama Peternak / Sapi / Ear Tag
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('cow', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ear_tag', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                         ->orWhere('location', 'like', "%{$search}%");
                  });
            });
        }

        $scans = $query->latest()->paginate(15);

        return view('admin.scans', compact('scans'));
    }

    /**
     * Halaman Detail Single Scan Result
     */
    public function showScan($id)
    {
        $scan = Scan::with(['cow.user'])->findOrFail($id);
        return view('admin.scan_detail', compact('scan'));
    }

    /**
     * Daftar Seluruh Peternak Terdaftar
     */
    public function users(Request $request)
    {
        $query = User::where('role', 'peternak')->withCount(['cows']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users', compact('users'));
    }

    /**
     * Logout Admin
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('success', 'Berhasil keluar dari sesi admin.');
    }
}
