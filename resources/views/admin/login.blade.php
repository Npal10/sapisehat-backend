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
<body class="bg-gradient-to-br from-green-900 via-green-800 to-emerald-900 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
        <!-- Brand Header -->
        <div class="bg-gradient-to-r from-emerald-600 to-green-700 p-8 text-center text-white">
            <div class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner border border-white/30">
                🐄
            </div>
            <h1 class="text-2xl font-bold tracking-tight">SapiSehat Admin</h1>
            <p class="text-xs text-green-100 mt-1">Panel Pengawasan & Pemantauan Wabah Sapi (PMK & LSD)</p>
        </div>

        <!-- Login Form Body -->
        <div class="p-8">
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center space-x-2">
                    <i class="ri-error-warning-fill text-lg"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-center space-x-2">
                    <i class="ri-checkbox-circle-fill text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email Admin</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="ri-mail-line"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@sapisehat.com" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition">
                    </div>
                    @error('email')
                        <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="ri-lock-line"></i>
                        </span>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition">
                    </div>
                    @error('password')
                        <span class="text-xs text-red-600 mt-1 block font-medium">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition duration-200 transform active:scale-95 text-sm flex items-center justify-center space-x-2">
                    <span>Masuk ke Web Admin</span>
                    <i class="ri-arrow-right-line"></i>
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center text-xs text-gray-400">
                Khusus Petugas Dinas Peternakan & Pengelola Sistem
            </div>
        </div>
    </div>

</body>
</html>
