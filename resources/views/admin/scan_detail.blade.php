@extends('admin.layout')

@section('title', 'Detail Riwayat Scan #' . $scan->id)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.scans') }}" class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-gray-800 transition">
            <i class="ri-arrow-left-line mr-1 text-lg"></i> Kembali ke Pemantauan Wabah
        </a>
    </div>

    <!-- Main Detail Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <!-- Card Header Badge -->
        <div class="p-6 border-b border-gray-200 flex flex-wrap justify-between items-center gap-4 bg-gray-50/50">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">ID Laporan Scan</span>
                <h1 class="text-2xl font-extrabold text-gray-900">#SCAN-{{ $scan->id }}</h1>
                <p class="text-xs text-gray-500 mt-1"><i class="ri-calendar-event-line"></i> Dilakukan pada {{ $scan->created_at->format('d MMMM YYYY, H:i') }} WIB</p>
            </div>
            
            <!-- Result Badge Header -->
            <div class="text-right">
                @if($scan->fmd_risk == 'PMK')
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold bg-red-100 text-red-800 border border-red-200">
                        <i class="ri-virus-line mr-1.5 text-lg"></i> PMK (Mulut & Kuku)
                    </span>
                @elseif($scan->fmd_risk == 'LSD')
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold bg-orange-100 text-orange-800 border border-orange-200">
                        <i class="ri-virus-line mr-1.5 text-lg"></i> LSD (Lato-lato)
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <i class="ri-checkbox-circle-line mr-1.5 text-lg"></i> Sapi Sehat
                    </span>
                @endif
            </div>
        </div>

        <div class="p-6 space-y-8">

            <!-- Sub Grid: Metadata Peternak & Sapi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-5 rounded-xl border border-gray-200">
                <!-- Data Peternak -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Informasi Pemilik (Peternak)</h3>
                    <div class="space-y-1.5 text-sm">
                        <div class="font-bold text-gray-900 text-base"><i class="ri-user-3-line text-brand-600 mr-1"></i> {{ $scan->cow->user->name ?? 'Peternak' }}</div>
                        <div class="text-gray-600"><i class="ri-mail-line text-gray-400 mr-1"></i> {{ $scan->cow->user->email ?? '-' }}</div>
                        <div class="text-gray-600"><i class="ri-map-pin-line text-gray-400 mr-1"></i> {{ $scan->cow->user->location ?? 'Lokasi Kandang Belum Ditentukan' }}</div>
                    </div>
                </div>

                <!-- Data Sapi -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Informasi Subjek (Sapi)</h3>
                    <div class="space-y-1.5 text-sm">
                        <div class="font-bold text-gray-900 text-base"><i class="ri-heart-pulse-line text-brand-600 mr-1"></i> {{ $scan->cow->name ?? 'Sapi' }}</div>
                        <div class="text-gray-600 font-mono"><i class="ri-price-tag-3-line text-gray-400 mr-1"></i> Ear Tag: <strong>{{ $scan->cow->ear_tag ?? '-' }}</strong></div>
                        <div class="text-gray-600"><i class="ri-file-info-line text-gray-400 mr-1"></i> Ras: {{ $scan->cow->breed ?? '-' }} ({{ $scan->cow->gender ?? '-' }}, {{ $scan->cow->age ?? '-' }} bulan)</div>
                    </div>
                </div>
            </div>

            <!-- Hasil Analisis AI Detail -->
            <div class="space-y-4">
                <h3 class="text-base font-bold text-gray-900 flex items-center border-b border-gray-100 pb-2">
                    <i class="ri-brain-line text-brand-600 mr-2 text-xl"></i> Hasil Penalaran AI (Google Gemini NLP)
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                        <div class="text-xs font-bold text-gray-400 uppercase">Penyakit Dominan</div>
                        <div class="text-lg font-extrabold text-gray-900 mt-1">{{ $scan->fmd_risk }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                        <div class="text-xs font-bold text-gray-400 uppercase">Tingkat Risiko</div>
                        <div class="text-lg font-extrabold text-gray-900 mt-1">{{ $scan->lsd_risk }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-red-50/50 border border-red-200">
                        <div class="text-xs font-bold text-red-600 uppercase">Risiko PMK (%)</div>
                        <div class="text-lg font-extrabold text-red-700 mt-1">{{ number_format($scan->pmk_percentage, 1) }}%</div>
                    </div>
                    <div class="p-4 rounded-xl bg-orange-50/50 border border-orange-200">
                        <div class="text-xs font-bold text-orange-600 uppercase">Risiko LSD (%)</div>
                        <div class="text-lg font-extrabold text-orange-700 mt-1">{{ number_format($scan->lsd_percentage, 1) }}%</div>
                    </div>
                </div>


                <!-- Penjelasan Medis AI -->
                <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100 space-y-1">
                    <div class="text-xs font-bold text-blue-900 uppercase"><i class="ri-information-fill text-blue-600 mr-1"></i> Penjelasan Medis AI</div>
                    <p class="text-sm text-blue-900 leading-relaxed">{{ $scan->explanation }}</p>
                </div>

                <!-- Rekomendasi Penanganan -->
                <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-100 space-y-1">
                    <div class="text-xs font-bold text-emerald-900 uppercase"><i class="ri-stethoscope-fill text-emerald-600 mr-1"></i> Rekomendasi Penanganan Darurat</div>
                    <p class="text-sm text-emerald-900 leading-relaxed">{{ $scan->recommendation }}</p>
                </div>
            </div>

            <!-- Teks Deskripsi Bebas Peternak -->
            <div class="space-y-2">
                <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2">
                    <i class="ri-chat-3-line text-brand-600 mr-2"></i> Teks Keluhan Tambahan dari Peternak
                </h3>
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-sm text-gray-700 italic">
                    "{{ $scan->description ?: '(Peternak tidak menuliskan teks deskripsi tambahan)' }}"
                </div>
            </div>

            <!-- Rincian Jawaban Kuisioner -->
            <div class="space-y-3">
                <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2">
                    <i class="ri-list-check-2 text-brand-600 mr-2"></i> Rincian Jawaban Kuisioner Klinis (17 Pertanyaan)
                </h3>

                @php
                    $qData = is_string($scan->questionnaire_data) ? json_decode($scan->questionnaire_data, true) : $scan->questionnaire_data;
                @endphp

                @if(!empty($qData) && is_array($qData))
                    <div class="border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-100 text-sm">
                        @foreach($qData as $question => $answer)
                            <div class="p-3.5 flex justify-between items-center hover:bg-gray-50">
                                <span class="text-gray-800 font-medium">{{ $question }}</span>
                                @if($answer)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        <i class="ri-check-line mr-1"></i> Ya
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                        Tidak
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic">Data rincian kuisioner tidak tersedia.</p>
                @endif
            </div>

        </div>
    </div>

</div>
@endsection
