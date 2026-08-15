<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — SapiSehat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 border border-gray-100 space-y-6">
        <!-- Logo & Brand Header -->
        <div class="text-center space-y-3">
            <div class="inline-block p-3 rounded-2xl bg-green-50 border border-green-100 shadow-sm">
                <img src="{{ asset('logo.png') }}" alt="SapiSehat Logo" class="w-16 h-16 object-contain mx-auto">
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">SapiSehat Admin</h1>
                <p class="text-xs text-gray-500 mt-1">Panel Pengawasan & Pemantauan Wabah Sapi (PMK & LSD)</p>
            </div>
        </div>

        <!-- Session Flash Notifications -->
        @if(session('error'))
            <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center space-x-2">
                <i class="ri-error-warning-fill text-base"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700 text-xs flex items-center space-x-2">
                <i class="ri-checkbox-circle-fill text-base"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email Admin</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                        <i class="ri-mail-line"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@sapisehat.com" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-600 focus:border-green-600 text-sm transition">
                </div>
                @error('email')
                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                        <i class="ri-lock-line"></i>
                    </span>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-600 focus:border-green-600 text-sm transition">
                </div>
                @error('password')
                    <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-green-700 hover:bg-green-800 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition duration-200 text-sm flex items-center justify-center space-x-2">
                <span>Masuk ke Web Admin</span>
                <i class="ri-arrow-right-line"></i>
            </button>
        </form>

        <div class="pt-4 border-t border-gray-100 text-center text-xs text-gray-400">
            Khusus Petugas Dinas Peternakan & Pengelola Sistem
        </div>
    </div>

</body>
</html>
