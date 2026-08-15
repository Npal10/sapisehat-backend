<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') — SapiSehat</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Inter & Remix Icon -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('logo.png') }}" alt="SapiSehat Logo" class="w-10 h-10 object-contain">
                    <div>
                        <span class="text-xl font-bold text-gray-900 tracking-tight">SapiSehat</span>
                        <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-brand-100 text-brand-800 border border-brand-200">Admin Panel</span>
                    </div>
                </div>


                <!-- User Profile & Logout -->
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-gray-500">Administrator Dinas Peternakan</div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center space-x-1 text-sm text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-2 rounded-lg font-medium transition">
                            <i class="ri-logout-box-r-line"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Navigation Bar -->
    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-1 pt-4 pb-3 border-b-2 text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="ri-dashboard-3-line mr-2 text-lg"></i> Dashboard & Statistik
                </a>
                <a href="{{ route('admin.scans') }}" class="inline-flex items-center px-1 pt-4 pb-3 border-b-2 text-sm font-medium transition {{ request()->routeIs('admin.scans*') ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="ri-health-book-line mr-2 text-lg"></i> Monitoring Wabah & Scan Global
                </a>
                <a href="{{ route('admin.users') }}" class="inline-flex items-center px-1 pt-4 pb-3 border-b-2 text-sm font-medium transition {{ request()->routeIs('admin.users') ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="ri-user-shared-line mr-2 text-lg"></i> Daftar Peternak Terdaftar
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content Body -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center space-x-3">
                <i class="ri-checkbox-circle-fill text-xl text-green-600"></i>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center space-x-3">
                <i class="ri-error-warning-fill text-xl text-red-600"></i>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 text-center text-xs text-gray-500">
        &copy; {{ date('Y') }} SapiSehat — Sistem Monitoring Wabah & Deteksi Dini Penyakit Sapi (PMK & LSD).
    </footer>

</body>
</html>
