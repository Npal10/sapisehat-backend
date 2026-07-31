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

    /**
     * Menganalisis gejala dan mengembalikan array data hasil prediksi.
     */
    public function analyzeDisease(array $questionnaire, string $description): array
    {
        // 1. Merakit kuisioner menjadi teks yang mudah dibaca LLM
        $questionnaireText = "";
        foreach ($questionnaire as $question => $answer) {
            $jawaban = $answer ? 'Ya' : 'Tidak';
            $questionnaireText .= "- $question: $jawaban\n";
        }

        // 2. Merakit Prompt (Prompt Engineering)
        // Instruksi yang sangat spesifik (System Prompt) agar AI hanya membalas dengan JSON
        $prompt = "Anda adalah sistem ahli dokter hewan virtual untuk deteksi dini penyakit sapi. " .
            "Tugas Anda adalah menganalisis gejala berikut untuk mendeteksi Foot and Mouth Disease (FMD/PMK) " .
            "dan Lumpy Skin Disease (LSD).\n\n" .
            "PEDOMAN SKORING RISIKO:\n" .
            "Untuk PMK (FMD):\n" .
            "- Gejala Utama (Bobot Tinggi): Ngiler, luka/lepuh di mulut/lidah, luka di kaki/kuku, pincang, demam, sapi lain bergejala.\n" .
            "- 0-3 gejala 'Ya' = Risiko Rendah.\n" .
            "- 4-7 gejala 'Ya' = Suspek PMK (Risiko Sedang).\n" .
            "- 8+ gejala 'Ya' (atau jika gejala utama sangat dominan) = Risiko Tinggi PMK.\n\n" .
            "Untuk LSD:\n" .
            "- Gejala Utama (Bobot Tinggi): Benjolan/nodul pada kulit, benjolan banyak, demam.\n" .
            "- 0-3 gejala 'Ya' = Risiko Rendah.\n" .
            "- 4-7 gejala 'Ya' = Suspek LSD (Risiko Sedang).\n" .
            "- 8+ gejala 'Ya' (atau jika gejala utama sangat dominan) = Risiko Tinggi LSD.\n\n" .
            "Input Kuisioner:\n" . $questionnaireText . "\n" .
            "Input Deskripsi Tambahan:\n\"" . $description . "\"\n\n" .
            "Gunakan pedoman skoring di atas sebagai referensi utama Anda. NAMUN, jika pada 'Deskripsi Tambahan' 
            terdapat petunjuk kuat seperti riwayat kontak dengan hewan sakit (misal dari pasar) atau sebutan lokal penyakit (misal: lato-lato), 
            Anda DIWAJIBKAN menaikkan status risiko menjadi Sedang atau Tinggi meskipun jawaban kuisioner 0. " .
            "PENTING: Anda WAJIB mengembalikan balasan HANYA dalam format JSON murni, tanpa teks pembuka/penutup, " .
            "tanpa format markdown (jangan gunakan ```json). Format JSON harus persis seperti berikut:\n" .
            "{\n" .
            "  \"fmd_risk\": \"Rendah|Sedang|Tinggi\",\n" .
            "  \"lsd_risk\": \"Rendah|Sedang|Tinggi\",\n" .
            "  \"confidence_score\": 90.5,\n" .
            "  \"explanation\": \"Penjelasan singkat mengapa diberikan tingkat risiko tersebut berdasarkan jumlah gejala (maksimal 3 kalimat)\",\n" .
            "  \"recommendation\": \"Rekomendasi tindakan awal yang harus dilakukan peternak\"\n" .
            "}";

        try {
            // 3. Mengirim Request ke Gemini API
            $response = Http::timeout(10)->post($this->apiUrl . '?key=' .$this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                // Mengatur temperatur rendah agar LLM tidak berimajinasi/halusinasi, fokus pada akurasi analitis
                'generationConfig' => [
                    'temperature' => 0.2, 
                ]
            ]);

            if ($response->successful()) {
                $result =$response->json();
                
                // Mengambil teks balasan dari struktur JSON Gemini
                $responseText =$result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                
                // Membersihkan markdown ```json jika AI membandel
                $responseText = str_replace(['```json', '```'], '', $responseText);
                $responseText = trim($responseText);

                // Decode JSON string menjadi PHP Array
                $decodedJson = json_decode($responseText, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decodedJson;
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
            // Jika koneksi timeout atau error lain, fallback
            return $this->fallbackAnalysis($questionnaire, $description);
        }
    }

    /**
     * Algoritma pakar manual tanpa AI (Mode Offline)
     */
    private function fallbackAnalysis(array $questionnaire, string $description): array
    {
        $fmdScore = 0;
        $lsdScore = 0;
        $totalYa = 0;

        $fmdSymptoms = [];
        $lsdSymptoms = [];

        foreach ($questionnaire as $question => $answer) {
            if ($answer) {
                $totalYa++;
                $qLower = strtolower($question);
                if (preg_match('/(ngiler|liur|mulut|lidah|luka|lepuh|kaki|kuku|pincang)/', $qLower, $matches)) {
                    $fmdScore += 2;
                    $fmdSymptoms[] = $matches[1];
                } else if (preg_match('/(demam|suhu)/', $qLower, $matches)) {
                    $fmdScore += 1;
                    $lsdScore += 1;
                    $fmdSymptoms[] = $matches[1];
                    $lsdSymptoms[] = $matches[1];
                } else if (preg_match('/(benjolan|nodul|kulit)/', $qLower, $matches)) {
                    $lsdScore += 2;
                    $lsdSymptoms[] = $matches[1];
                } else {
                    $fmdScore += 1;
                    $lsdScore += 1;
                }
            }
        }
        
        $descLower = strtolower($description);
        if (preg_match_all('/(ngiler|liur|mulut|lidah|luka|lepuh|kaki|kuku|pincang)/', $descLower, $matches)) {
            $fmdScore += count($matches[0]);
            $fmdSymptoms = array_merge($fmdSymptoms, $matches[0]);
        }
        if (preg_match_all('/(benjolan|nodul|kulit|lato-lato)/', $descLower, $matches)) {
            $lsdScore += count($matches[0]);
            $lsdSymptoms = array_merge($lsdSymptoms, $matches[0]);
        }
        
        $fmdSymptoms = array_unique($fmdSymptoms);
        $lsdSymptoms = array_unique($lsdSymptoms);

        $fmdRisk = 'Rendah';
        if ($fmdScore >= 8) $fmdRisk = 'Tinggi';
        elseif ($fmdScore >= 4) $fmdRisk = 'Sedang';

        $lsdRisk = 'Rendah';
        if ($lsdScore >= 8) $lsdRisk = 'Tinggi';
        elseif ($lsdScore >= 4) $lsdRisk = 'Sedang';

        $explanation = "";
        $recommendation = "";

        if ($fmdRisk === 'Rendah' && $lsdRisk === 'Rendah') {
            $explanation = "Sistem Pakar: Tidak ditemukan gejala klinis yang mengarah kuat ke PMK maupun LSD. Sapi dalam kondisi relatif aman.";
            $recommendation = "Tetap pantau kondisi sapi, jaga kebersihan kandang, dan berikan pakan bernutrisi untuk menjaga daya tahan tubuh sapi.";
        } elseif ($fmdRisk !== 'Rendah' && $lsdRisk !== 'Rendah') {
            $fmdText = implode(", ", $fmdSymptoms);
            $lsdText = implode(", ", $lsdSymptoms);
            $explanation = "Sistem Pakar: Ditemukan gejala PMK ($fmdText) dan LSD ($lsdText) secara bersamaan.";
            $recommendation = "Kondisi KRITIS. Segera isolasi sapi ini secara total. Jangan memindahkan sapi ke lokasi lain. Segera hubungi Dinas Peternakan atau Dokter Hewan terdekat.";
        } elseif ($fmdRisk !== 'Rendah') {
            $fmdText = implode(", ", $fmdSymptoms);
            $explanation = "Sistem Pakar: Terdeteksi gejala spesifik PMK dengan tingkat risiko $fmdRisk karena ditemukan indikasi: $fmdText.";
            $recommendation = "Segera pisahkan sapi dari kawanan yang sehat. Semprot kandang dengan disinfektan dan hubungi Mantri/Dokter Hewan secepatnya untuk penanganan PMK.";
        } else {
            $lsdText = implode(", ", $lsdSymptoms);
            $explanation = "Sistem Pakar: Terdeteksi gejala spesifik LSD dengan tingkat risiko $lsdRisk karena ditemukan indikasi: $lsdText.";
            $recommendation = "Karantina sapi yang sakit. Bersihkan kandang dari genangan air dan kotoran untuk mencegah sarang nyamuk/lalat penyebar LSD. Segera lapor ke petugas kesehatan hewan.";
        }

        return [
            'fmd_risk' => $fmdRisk,
            'lsd_risk' => $lsdRisk,
            'confidence_score' => 75.0,
            'explanation' => $explanation,
            'recommendation' => $recommendation
        ];
    }
}