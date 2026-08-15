@extends('admin.layout')

@section('title', 'Daftar Peternak Terdaftar')

@section('content')
<div class="space-y-6">

    <!-- Page Title -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Daftar Peternak Terdaftar</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar seluruh peternak pengguna aplikasi SapiSehat beserta lokasi kandangnya.</p>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.users') }}" class="flex gap-4">
            <div class="flex-1 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peternak, email, atau lokasi kandang..." class="w-full pl-10 pr-24 border-gray-300 rounded-xl text-sm focus:ring-brand-500 focus:border-brand-500 py-2.5">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="ri-search-line"></i>
                </span>
                <button type="submit" class="absolute inset-y-1 right-1 px-4 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs rounded-lg transition flex items-center">
                    Cari
                </button>
            </div>
        </form>
    </div>

    <!-- Farmers Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">ID & Tanggal Daftar</th>
                        <th class="py-3.5 px-6">Nama Peternak</th>
                        <th class="py-3.5 px-6">Email</th>
                        <th class="py-3.5 px-6">Lokasi Kandang</th>
                        <th class="py-3.5 px-6 text-center">Jumlah Sapi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-4 px-6 text-xs text-gray-600 font-medium whitespace-nowrap">
                                <div class="font-bold text-gray-900">#USR-{{ $user->id }}</div>
                                <div>{{ $user->created_at->format('d MMM YYYY') }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-gray-900 flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-800 font-bold flex items-center justify-center mr-2.5 text-xs">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-gray-600 font-mono text-xs">
                                {{ $user->email }}
                            </td>
                            <td class="py-4 px-6 text-gray-700">
                                <div class="flex items-center text-xs font-medium">
                                    <i class="ri-map-pin-line text-brand-600 mr-1 text-sm"></i>
                                    <span>{{ $user->location ?: 'Belum diisi' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                    {{ $user->cows_count }} Sapi
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400 text-sm">
                                <i class="ri-user-unfollow-line text-3xl block mb-2"></i>
                                Tidak ada data peternak yang cocok dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
