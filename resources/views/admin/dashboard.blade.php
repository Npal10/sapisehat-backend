@extends('admin.layout')

@section('title', 'Dashboard Monitoring Wabah')

@section('content')
<div class="space-y-8">

    <!-- Page Header Title -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Pengawasan Wabah Sapi</h1>
        <p class="text-sm text-gray-500 mt-1">Pemantauan real-time kasus PMK, LSD, dan populasi ternak sapi di seluruh wilayah peternak.</p>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Peternak -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Peternak Terdaftar</div>
                <div class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($totalPeternak) }}</div>
                <div class="text-xs text-gray-500 mt-1">Pengguna Aktif App Mobile</div>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl">
                <i class="ri-user-line"></i>
            </div>
        </div>

        <!-- Total Sapi -->
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Populasi Sapi</div>
                <div class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($totalSapi) }}</div>
                <div class="text-xs text-gray-500 mt-1">Sapi Terdaftar di Kandang</div>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-2xl">
                <i class="ri-heart-pulse-line"></i>
            </div>
        </div>

        <!-- Kasus PMK -->
        <div class="bg-white p-6 rounded-2xl border border-red-200 bg-red-50/20 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-red-600 uppercase tracking-wider">Kasus PMK Terdeteksi</div>
                <div class="text-3xl font-extrabold text-red-700 mt-1">{{ number_format($totalPmk) }}</div>
                <div class="text-xs text-red-600/80 mt-1">Penyakit Mulut & Kuku</div>
            </div>
            <div class="w-12 h-12 bg-red-100 text-red-700 rounded-xl flex items-center justify-center text-2xl">
                <i class="ri-error-warning-line"></i>
            </div>
        </div>

        <!-- Kasus LSD -->
        <div class="bg-white p-6 rounded-2xl border border-orange-200 bg-orange-50/20 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-orange-600 uppercase tracking-wider">Kasus LSD Terdeteksi</div>
                <div class="text-3xl font-extrabold text-orange-700 mt-1">{{ number_format($totalLsd) }}</div>
                <div class="text-xs text-orange-600/80 mt-1">Lumpy Skin Disease (Lato-lato)</div>
            </div>
            <div class="w-12 h-12 bg-orange-100 text-orange-700 rounded-xl flex items-center justify-center text-2xl">
                <i class="ri-virus-line"></i>
            </div>
        </div>
    </div>

    <!-- Ringkasan Status Kesehatan Sapi -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
        <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center">
            <i class="ri-pie-chart-line mr-2 text-brand-600"></i> Distribusi Kesehatan Hasil Deteksi AI
        </h2>
        
        @php
            $totalScanVal = max($totalScan, 1);
            $pctPmk = round(($totalPmk / $totalScanVal) * 100, 1);
            $pctLsd = round(($totalLsd / $totalScanVal) * 100, 1);
            $pctSehat = round(($totalSehat / $totalScanVal) * 100, 1);
        @endphp

        <div class="space-y-4">
            <!-- Progress Bar Stacked -->
            <div class="w-full h-4 bg-gray-100 rounded-full overflow-hidden flex">
                <div style="width: {{ $pctSehat }}%" class="bg-emerald-500 h-full" title="Sehat: {{ $pctSehat }}%"></div>
                <div style="width: {{ $pctPmk }}%" class="bg-red-500 h-full" title="PMK: {{ $pctPmk }}%"></div>
                <div style="width: {{ $pctLsd }}%" class="bg-orange-500 h-full" title="LSD: {{ $pctLsd }}%"></div>
            </div>

            <!-- Legend Badge -->
            <div class="flex flex-wrap gap-6 text-sm">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <span class="font-medium text-gray-700">Sehat: <strong class="text-gray-900">{{ $totalSehat }}</strong> ({{ $pctSehat }}%)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="font-medium text-gray-700">PMK: <strong class="text-gray-900">{{ $totalPmk }}</strong> ({{ $pctPmk }}%)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                    <span class="font-medium text-gray-700">LSD: <strong class="text-gray-900">{{ $totalLsd }}</strong> ({{ $pctLsd }}%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Scans Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h2 class="text-base font-bold text-gray-900">Riwayat Scan Terbaru (10 Terakhir)</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pantau deteksi penyakit yang baru saja diinput oleh peternak.</p>
            </div>
            <a href="{{ route('admin.scans') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800 flex items-center">
                <span>Lihat Semua Riwayat</span>
                <i class="ri-arrow-right-s-line ml-1 text-lg"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Waktu & Tanggal</th>
                        <th class="py-3.5 px-6">Peternak & Lokasi</th>
                        <th class="py-3.5 px-6">Sapi & Tag</th>
                        <th class="py-3.5 px-6">Hasil AI (Penyakit)</th>
                        <th class="py-3.5 px-6">Tingkat Risiko</th>
                        <th class="py-3.5 px-6">Confidence</th>
                        <th class="py-3.5 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($recentScans as $scan)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-4 px-6 text-xs text-gray-600 font-medium whitespace-nowrap">
                                {{ $scan->created_at->format('d MMM YYYY, H:i') }} WIB
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-gray-900">{{ $scan->cow->user->name ?? 'Peternak' }}</div>
                                <div class="text-xs text-gray-500"><i class="ri-map-pin-line"></i> {{ $scan->cow->user->location ?? '-' }}</div>
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
                            <td class="py-4 px-6 font-semibold text-xs whitespace-nowrap">
                                <div class="text-red-600">PMK: <strong>{{ number_format($scan->pmk_percentage, 1) }}%</strong></div>
                                <div class="text-orange-600">LSD: <strong>{{ number_format($scan->lsd_percentage, 1) }}%</strong></div>
                            </td>

                            <td class="py-4 px-6 text-center">
                                <a href="{{ route('admin.scans.show', $scan->id) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition">
                                    <i class="ri-eye-line mr-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400 text-sm">Belum ada riwayat scan yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
