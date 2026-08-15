@extends('admin.layout')

@section('title', 'Monitoring Wabah & Scan Global')

@section('content')
<div class="space-y-6">

    <!-- Page Title -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pemantauan Riwayat Scan Wabah Global</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar seluruh riwayat deteksi dini penyakit sapi dari seluruh peternak terdaftar.</p>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('admin.scans') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Filter Penyakit -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Status Penyakit</label>
                <select name="disease" onchange="this.form.submit()" class="w-full border-gray-300 rounded-xl text-sm focus:ring-brand-500 focus:border-brand-500 py-2.5">
                    <option value="">Semua Penyakit (Semua)</option>
                    <option value="PMK" {{ request('disease') == 'PMK' ? 'selected' : '' }}>PMK (Penyakit Mulut & Kuku)</option>
                    <option value="LSD" {{ request('disease') == 'LSD' ? 'selected' : '' }}>LSD (Lato-lato)</option>
                    <option value="Sehat" {{ request('disease') == 'Sehat' ? 'selected' : '' }}>Sehat (Bebas Gejala)</option>
                </select>
            </div>

            <!-- Filter Tingkat Risiko -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Tingkat Risiko</label>
                <select name="risk" onchange="this.form.submit()" class="w-full border-gray-300 rounded-xl text-sm focus:ring-brand-500 focus:border-brand-500 py-2.5">
                    <option value="">Semua Risiko (Semua)</option>
                    <option value="Tinggi" {{ request('risk') == 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                    <option value="Sedang" {{ request('risk') == 'Sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="Rendah" {{ request('risk') == 'Rendah' ? 'selected' : '' }}>Rendah</option>
                </select>
            </div>

            <!-- Search Field -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Cari Peternak / Sapi / Tag / Lokasi</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama peternak, ear tag, atau lokasi kandang..." class="w-full pl-10 pr-24 border-gray-300 rounded-xl text-sm focus:ring-brand-500 focus:border-brand-500 py-2.5">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="ri-search-line"></i>
                    </span>
                    <button type="submit" class="absolute inset-y-1 right-1 px-4 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs rounded-lg transition flex items-center">
                        Cari
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Scans Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">ID & Waktu</th>
                        <th class="py-3.5 px-6">Peternak & Lokasi</th>
                        <th class="py-3.5 px-6">Sapi & Tag</th>
                        <th class="py-3.5 px-6">Hasil AI</th>
                        <th class="py-3.5 px-6">Tingkat Risiko</th>
                        <th class="py-3.5 px-6">Confidence</th>
                        <th class="py-3.5 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($scans as $scan)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-4 px-6 text-xs text-gray-600 font-medium whitespace-nowrap">
                                <div class="font-bold text-gray-900">#SCAN-{{ $scan->id }}</div>
                                <div>{{ $scan->created_at->format('d MMM YYYY, H:i') }} WIB</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-gray-900">{{ $scan->cow->user->name ?? 'Peternak' }}</div>
                                <div class="text-xs text-gray-500"><i class="ri-map-pin-line"></i> {{ $scan->cow->user->location ?? 'Lokasi -' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-gray-800">{{ $scan->cow->name ?? 'Sapi' }}</div>
                                <div class="text-xs font-mono text-gray-500">Tag: {{ $scan->cow->ear_tag ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                @if($scan->fmd_risk == 'PMK')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        <i class="ri-virus-line mr-1"></i> PMK
                                    </span>
                                @elseif($scan->fmd_risk == 'LSD')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800">
                                        <i class="ri-virus-line mr-1"></i> LSD
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        <i class="ri-checkbox-circle-line mr-1"></i> Sehat
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @php $risk = strtolower($scan->lsd_risk); @endphp
                                @if(str_contains($risk, 'tinggi'))
                                    <span class="text-xs font-bold text-red-600">🔴 Tinggi</span>
                                @elseif(str_contains($risk, 'sedang'))
                                    <span class="text-xs font-bold text-orange-600">🟠 Sedang</span>
                                @else
                                    <span class="text-xs font-bold text-emerald-600">🟢 Rendah</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 font-semibold text-gray-700">
                                {{ number_format($scan->confidence_score, 1) }}%
                            </td>
                            <td class="py-4 px-6 text-center">
                                <a href="{{ route('admin.scans.show', $scan->id) }}" class="inline-flex items-center px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold rounded-lg transition">
                                    <i class="ri-eye-line mr-1"></i> Liha Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400 text-sm">
                                <i class="ri-search-eye-line text-3xl block mb-2"></i>
                                Tidak ada data riwayat scan yang cocok dengan pencarian / filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($scans->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $scans->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
