<?php

// file: app/Http/Controllers/Api/ScanController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Scan;
use App\Services\NlpService;
use Exception;

class ScanController extends Controller
{
    protected $nlpService;

    // Dependency Injection dari service yang telah kita buat
    public function __construct(NlpService $nlpService)
    {
        $this->nlpService = $nlpService;
    }

    public function analyze(Request $request)
    {
        // 1. Validasi Input dari Flutter
        $request->validate([
            'cow_id' => 'required|exists:cows,id',
            'questionnaire' => 'required|array',
            'description' => 'required|string|min:5',
        ]);

        try {
            // 2. Kirim ke NLP Service untuk Dianalisis
            $analysisResult = $this->nlpService->analyzeDisease(
                $request->questionnaire,
                $request->description
            );

            // 3. Simpan ke Database (Tabel Scans)
            $scan = Scan::create([
                'cow_id' => $request->cow_id,
                'questionnaire_data' => json_encode($request->questionnaire),
                'description' => $request->description,
                'fmd_risk' => $analysisResult['fmd_risk'],
                'lsd_risk' => $analysisResult['lsd_risk'],
                'confidence_score' => $analysisResult['confidence_score'],
                'explanation' => $analysisResult['explanation'],
                'recommendation' => $analysisResult['recommendation'],
            ]);

            // 4. Kembalikan Response Sukses ke Flutter
            return response()->json([
                'success' => true,
                'message' => 'Analisis berhasil dilakukan.',
                'data' => $scan
            ], 201);

        } catch (Exception $e) {
            // Jika ada error (misal API timeout), kembalikan error ke Flutter
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan analisis: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function history(Request $request)
    {
        // Mengambil seluruh riwayat scan yang sapinya dimiliki oleh user yang sedang login
        $scans = Scan::whereHas('cow', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })
        ->with('cow:id,name,ear_tag') // Eager loading hanya ID, Nama, dan Tag Sapi
        ->orderBy('created_at', 'desc') // Urutkan dari yang paling baru
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil riwayat scan',
            'data' => $scans
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $scan = Scan::find($id);

        if (!$scan) {
            return response()->json([
                'success' => false,
                'message' => 'Riwayat scan tidak ditemukan.',
            ], 404);
        }

        // Pastikan scan dimiliki oleh user yang sedang login
        if ($scan->cow->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus riwayat ini.',
            ], 403);
        }

        $scan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat scan berhasil dihapus.',
        ]);
    }
}