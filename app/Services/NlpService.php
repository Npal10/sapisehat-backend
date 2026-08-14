<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class NlpService
{
    protected $apiKey;
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }


    public function analyzeDisease(array $questionnaire, string $description): array
    {
        // ─── TAHAP 1: PIPELINE PRA-PEMROSESAN NLP (NLP PREPROCESSING PIPELINE) ───
        // 1.1 Pre-processing Teks Deskripsi Bebas Peternak (Cleaning, Lowercase, Normalisasi Kata Slang/Singkatan)
        $cleanDescription = $this->preprocessText($description);

        // 1.2 Ekstraksi Kata Kunci Gejala Klinis dari Teks Bebas
        $extractedSymptoms = $this->extractSymptomKeywords($cleanDescription);

        // 1.3 Pengelompokan Data Kuisioner Terstruktur
        $gejalaYa = [];
        $gejalaTidak = [];
        foreach ($questionnaire as $question => $answer) {
            if ($answer) {
                $gejalaYa[] = "- $question";
            } else {
                $gejalaTidak[] = "- $question";
            }
        }

        $questionnaireText = "GEJALA YANG DIALAMI (JAWABAN IYA):\n" . 
            (empty($gejalaYa) ? "(Tidak ada gejala yang dipilih)\n" : implode("\n", $gejalaYa) . "\n") . "\n" .
            "GEJALA YANG TIDAK DIALAMI (JAWABAN TIDAK):\n" . 
            (empty($gejalaTidak) ? "(Tidak ada)\n" : implode("\n", $gejalaTidak) . "\n");

        $extractedText = "HASIL EKSTRAKSI KATA KUNCI GEJALA DARI DESKRIPSI:\n" .
            (empty($extractedSymptoms) ? "(Tidak terdeteksi kata kunci gejala khusus)\n" : implode(", ", $extractedSymptoms) . "\n");

        // ─── TAHAP 2: PROMPT ENGINEERING BERBASIS TEKS TER-PREPROSES ───
        $prompt = "Anda adalah sistem ahli dokter hewan virtual untuk deteksi dini penyakit sapi. " .
            "Tugas Anda adalah menganalisis input gejala kuisioner dan deskripsi tambahan ter-preproses berikut untuk menentukan penyakit mana yang lebih dominan antara Foot and Mouth Disease (FMD/PMK) dan Lumpy Skin Disease (LSD), " .
            "atau menyatakan sapi Sehat jika tidak ada gejala spesifik.\n\n" .
            "PEDOMAN SKORING PENYAKIT:\n" .
            "1. PMK (FMD):\n" .
            "   - Gejala Spesifik: Ngiler berlebihan/ngreweh, luka/lepuh/sariawan di mulut/gusi/lidah, luka di kaki/kuku, pincang, demam, sapi lain satu kandang sakit.\n" .
            "2. LSD (Lato-lato):\n" .
            "   - Gejala Spesifik: Benjolan/nodul keras bulat di kulit leher/tubuh, bengkak/memerah di bagian atas teracak, demam, sapi lain satu kandang sakit.\n\n" .
            "Data Masukan Kuisioner:\n" . $questionnaireText . "\n" .
            "Teks Deskripsi Bebas Peternak (Sudah Dicleaning & Normalisasi):\n\"" . $cleanDescription . "\"\n\n" .
            $extractedText . "\n" .

            "TUGAS ANALISIS ANDA:\n" .
            "- Bandingkan jumlah dan bobot gejala spesifik PMK dan LSD yang bernilai 'IYA' baik dari kuisioner maupun dari teks deskripsi tambahan.\n" .
            "- Tentukan salah satu penyakit yang paling dominan (jika tidak ada gejala klinis yang mengarah ke PMK atau LSD, tentukan sebagai 'Sehat').\n" .
            "- Berikan estimasi persentase kemungkinan (confidence score) dari penyakit dominan tersebut (rentang 0-100%).\n" .
            "- Berikan tingkat risiko penyakit dominan tersebut (Rendah, Sedang, atau Tinggi).\n\n" .
            "PENTING: Anda WAJIB mengembalikan balasan HANYA dalam format JSON murni, tanpa teks pembuka/penutup, " .
            "tanpa format markdown (jangan gunakan ```json). Format JSON harus persis seperti berikut:\n" .
            "{\n" .
            "  \"dominant_disease\": \"PMK|LSD|Sehat\",\n" .
            "  \"risk_level\": \"Rendah|Sedang|Tinggi\",\n" .
            "  \"confidence_score\": 85.0,\n" .
            "  \"explanation\": \"Penjelasan singkat mengapa penyakit tersebut dominan, merujuk langsung pada gejala spesifik 'IYA' yang dipilih dan teks deskripsi tambahan (maksimal 3 kalimat)\",\n" .
            "  \"recommendation\": \"Rekomendasi tindakan darurat awal bagi peternak untuk menangani penyakit dominan tersebut\"\n" .
            "}";

        try {
            $response = Http::timeout(10)->post($this->apiUrl . '?key=' .$this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $responseText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                $responseText = str_replace(['```json', '```'], '', $responseText);
                $responseText = trim($responseText);

                $decodedJson = json_decode($responseText, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    // Petakan JSON agar cocok dengan struktur tabel database
                    return [
                        'fmd_risk' => $decodedJson['dominant_disease'] ?? 'Sehat',
                        'lsd_risk' => $decodedJson['risk_level'] ?? 'Rendah',
                        'confidence_score' => (float)($decodedJson['confidence_score'] ?? 0.0),
                        'explanation' => $decodedJson['explanation'] ?? '',
                        'recommendation' => $decodedJson['recommendation'] ?? '',
                    ];
                } else {
                    Log::error('Failed to parse JSON from LLM: ' . json_last_error_msg());
                    throw new Exception("Gagal memproses format jawaban dari AI.");
                }
            } else {
                Log::error('Gemini API Error: ' . $response->body());
                if ($response->status() === 429 || $response->status() === 503) {
                    Log::warning('Gemini Limit reached. Using offline fallback.');
                    return $this->fallbackAnalysis($questionnaire, $description);
                }
                throw new Exception("Terjadi kesalahan saat menghubungi server AI.");
            }
        } catch (Exception $e) {
            Log::error('NlpService Exception: ' . $e->getMessage());
            return $this->fallbackAnalysis($questionnaire, $description);
        }
    }


    private function fallbackAnalysis(array $questionnaire, string $description): array
    {
        $pmkScore = 0;
        $lsdScore = 0;
        $pmkSymptoms = [];
        $lsdSymptoms = [];

        foreach ($questionnaire as $question => $answer) {
            if ($answer) {
                $qLower = strtolower($question);
                if (preg_match('/(ngiler|ngreweh|sariawan|liur|mulut|lidah|luka|lepuh|kaki|tracak|kuku|pincang)/', $qLower, $matches)) {
                    $pmkScore += 2;
                    $pmkSymptoms[] = $matches[1];
                } else if (preg_match('/(demam|suhu)/', $qLower, $matches)) {
                    $pmkScore += 1;
                    $lsdScore += 1;
                    $pmkSymptoms[] = $matches[1];
                    $lsdSymptoms[] = $matches[1];
                } else if (preg_match('/(benjolan|nodul|kulit)/', $qLower, $matches)) {
                    $lsdScore += 2;
                    $lsdSymptoms[] = $matches[1];
                }
            }
        }
        
        $descLower = strtolower($description);
        if (preg_match_all('/(ngiler|ngreweh|sariawan|liur|mulut|lidah|luka|lepuh|kaki|tracak|kuku|pincang)/', $descLower, $matches)) {
            $pmkScore += count($matches[0]);
            $pmkSymptoms = array_merge($pmkSymptoms, $matches[0]);
        }
        if (preg_match_all('/(benjolan|nodul|kulit|lato-lato)/', $descLower, $matches)) {
            $lsdScore += count($matches[0]);
            $lsdSymptoms = array_merge($lsdSymptoms, $matches[0]);
        }
        
        $pmkSymptoms = array_unique($pmkSymptoms);
        $lsdSymptoms = array_unique($lsdSymptoms);

        $dominant = 'Sehat';
        $riskLevel = 'Rendah';
        $confidence = 0.0;
        $explanation = '';
        $recommendation = '';

        if ($pmkScore == 0 && $lsdScore == 0) {
            $dominant = 'Sehat';
            $riskLevel = 'Rendah';
            $confidence = 100.0;
            $explanation = "Sistem Pakar: Tidak ditemukan gejala klinis yang mengarah ke PMK maupun LSD. Sapi dalam kondisi relatif aman.";
            $recommendation = "Tetap jaga kebersihan kandang, berikan pakan bergizi, dan pantau kesehatan sapi secara berkala.";
        } elseif ($pmkScore >= $lsdScore) {
            $dominant = 'PMK';
            $confidence = min(60.0 + ($pmkScore * 4), 95.0); 
            if ($pmkScore >= 8) {
                $riskLevel = 'Tinggi';
            } elseif ($pmkScore >= 4) {
                $riskLevel = 'Sedang';
            } else {
                $riskLevel = 'Rendah';
            }
            $symptomList = implode(', ', $pmkSymptoms);
            $explanation = "Sistem Pakar: Terdeteksi gejala klinis yang mengarah ke Penyakit Mulut dan Kuku (PMK) dengan indikasi spesifik: $symptomList.";
            $recommendation = "Segera karantina sapi dari sapi sehat lainnya. Bersihkan area mulut dan kuku menggunakan antiseptik ringan, serta segera hubungi petugas kesehatan hewan terdekat.";
        } else {
            $dominant = 'LSD';
            $confidence = min(60.0 + ($lsdScore * 4), 95.0);
            if ($lsdScore >= 8) {
                $riskLevel = 'Tinggi';
            } elseif ($lsdScore >= 4) {
                $riskLevel = 'Sedang';
            } else {
                $riskLevel = 'Rendah';
            }
            $symptomList = implode(', ', $lsdSymptoms);
            $explanation = "Sistem Pakar: Terdeteksi gejala klinis yang mengarah ke Lumpy Skin Disease (LSD/Lato-lato) dengan indikasi spesifik: $symptomList.";
            $recommendation = "Karantina sapi secara ketat. Semprot kandang dengan insektisida ramah lingkungan untuk membasmi serangga (nyamuk/lalat) penyebar virus, dan hubungi dokter hewan.";
        }

        return [
            'fmd_risk' => $dominant,
            'lsd_risk' => $riskLevel,
            'confidence_score' => $confidence,
            'explanation' => $explanation,
            'recommendation' => $recommendation
        ];
    }

    /**
     * ─── TAHAP PRA-PEMROSESAN TEKS (NLP PREPROCESSING PIPELINE) ───
     * Meliputi: Case Folding, Cleaning Tanda Baca/Simbol, dan Normalisasi Slang/Singkatan Peternak
     */
    private function preprocessText(string $text): string
    {
        if (trim($text) === '') return '';

        // 1. Case Folding: Mengubah teks menjadi huruf kecil
        $text = strtolower($text);

        // 2. Kamus Normalisasi Kata Informal / Singkatan Peternak
        $slangMap = [
            '/\btdk\b/i'     => 'tidak',
            '/\bgk\b/i'      => 'tidak',
            '/\bga\b/i'      => 'tidak',
            '/\bgatau\b/i'   => 'tidak tahu',
            '/\bkrn\b/i'     => 'karena',
            '/\bklo\b/i'     => 'kalau',
            '/\bkalo\b/i'    => 'kalau',
            '/\bsampek\b/i'  => 'sampai',
            '/\bsampe\b/i'   => 'sampai',
            '/\bdr\b/i'      => 'dari',
            '/\bdgn\b/i'     => 'dengan',
            '/\bbgt\b/i'     => 'sangat',
            '/\bbnyak\b/i'   => 'banyak',
            '/\bbyk\b/i'     => 'banyak',
            '/\bpincg\b/i'   => 'pincang',
            '/\bbenjol\b/i'  => 'benjolan',
            '/\bngreweh\b/i' => 'ngiler',
            '/\bliur\b/i'    => 'air liur',
            '/\bsrwn\b/i'    => 'sariawan',
            '/\bpanas\b/i'   => 'demam',
        ];

        // Terapkan penggantian kata informal
        foreach ($slangMap as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        // 3. Noise Removal & Cleaning: Menghapus simbol khusus/karakter non-alphanumeric berlebih
        $text = preg_replace('/[^\w\s\-]/u', ' ', $text);

        // 4. Normalisasi Spasi Ganda
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * ─── EKSTRAKSI ENTITAS KATA KUNCI GEJALA (FEATURE EXTRACTION) ───
     * Memindai teks deskripsi bebas untuk mengekstrak entitas gejala klinis utama
     */
    private function extractSymptomKeywords(string $cleanText): array
    {
        if ($cleanText === '') return [];

        $foundKeywords = [];

        // Daftar Pola Kata Kunci Gejala Klinis PMK & LSD
        $symptomDictionary = [
            'PMK: Air Liur / Ngiler'     => '/(ngiler|liur|ngreweh|berbusa)/i',
            'PMK: Sariawan / Lepuh Mulut'=> '/(sariawan|lepuh|luka mulut|lidah|gusi)/i',
            'PMK: Pincang / Luka Kuku'  => '/(pincang|kuku|kaki luka|sulit berdiri)/i',
            'LSD: Benjolan / Nodul Kulit' => '/(benjolan|nodul|lato-lato|bintol|bentol)/i',
            'LSD: Kaki Bengkak Teracak'  => '/(bengkak|teracak|meradang)/i',
            'LSD: Mata / Hidung Berlendir'=> '/(mata berair|hidung|lendir)/i',
            'GEJALA: Demam / Panas'      => '/(demam|panas|suhu)/i',
            'GEJALA: Nafsu Makan Turun'  => '/(tidak makan|kurang makan|lahap|lemas|lesu)/i',
        ];

        foreach ($symptomDictionary as $label => $pattern) {
            if (preg_match($pattern, $cleanText)) {
                $foundKeywords[] = $label;
            }
        }

        return $foundKeywords;
    }
}

