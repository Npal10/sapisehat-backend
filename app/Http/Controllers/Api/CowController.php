<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cow;
use App\Models\VaccinationHistory;
use Illuminate\Support\Facades\Storage;

class CowController extends Controller
{
    // Mengambil semua sapi milik user yang sedang login beserta vaksinnya
    public function index(Request $request)
    {
        $cows = $request->user()->cows()->with('vaccines')->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $cows]);
    }

    // Menambah data sapi baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'ear_tag' => 'required|string|unique:cows',
            'gender' => 'required|in:Jantan,Betina',
            'age' => 'required|integer|min:1',
            'breed' => 'required|string',
            'weight' => 'nullable|numeric',
            'last_vaccinated_at' => 'nullable|date',
            'acquisition_date' => 'required|date',
            'acquisition_place' => 'required|string',
            'status' => 'nullable|in:Tersedia,Terjual',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'ear_tag.unique' => 'Tag telinga ini sudah terdaftar',
            'name.required' => 'Nama sapi wajib diisi',
            'acquisition_date.required' => 'Tanggal perolehan wajib diisi',
            'acquisition_place.required' => 'Tempat perolehan wajib diisi',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('cows', 'public');
            $validated['photo_url'] = Storage::url($path);
        }

        $cow = $request->user()->cows()->create($validated);

        if ($request->filled('last_vaccinated_at')) {
            $cow->vaccines()->create([
                'vaccine_name' => 'Vaksin Awal (Pendaftaran)',
                'administered_at' => $request->last_vaccinated_at,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Data sapi berhasil ditambahkan', 'data' => $cow->load('vaccines')], 201);
    }

    // Mengubah data sapi
    public function update(Request $request, $id)
    {
        $cow = $request->user()->cows()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'ear_tag' => 'sometimes|required|string|unique:cows,ear_tag,' . $cow->id,
            'gender' => 'sometimes|required|in:Jantan,Betina',
            'age' => 'sometimes|required|integer|min:1',
            'breed' => 'sometimes|required|string',
            'weight' => 'nullable|numeric',
            'last_vaccinated_at' => 'nullable|date',
            'acquisition_date' => 'sometimes|required|date',
            'acquisition_place' => 'sometimes|required|string',
            'status' => 'nullable|in:Tersedia,Terjual',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($cow->photo_url) {
                $oldPath = str_replace('/storage/', '', $cow->photo_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('photo')->store('cows', 'public');
            $validated['photo_url'] = Storage::url($path);
        }

        // Cek jika tanggal vaksin berubah/ditambahkan
        $oldVaccineDate = $cow->last_vaccinated_at ? $cow->last_vaccinated_at->format('Y-m-d') : null;

        $cow->update($validated);

        // Jika user menginput/mengubah tanggal vaksin, tambahkan ke history
        if ($request->filled('last_vaccinated_at')) {
            $newVaccineDate = \Carbon\Carbon::parse($request->last_vaccinated_at)->format('Y-m-d');
            if ($oldVaccineDate !== $newVaccineDate) {
                $cow->vaccines()->create([
                    'vaccine_name' => 'Vaksin (Pembaruan Data)',
                    'administered_at' => $request->last_vaccinated_at,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Data sapi berhasil diperbarui', 'data' => $cow->load('vaccines')]);
    }

    // Menghapus data sapi
    public function destroy(Request $request, $id)
    {
        $cow = $request->user()->cows()->findOrFail($id);
        
        if ($cow->photo_url) {
            $oldPath = str_replace('/storage/', '', $cow->photo_url);
            Storage::disk('public')->delete($oldPath);
        }
        
        $cow->delete();

        return response()->json(['success' => true, 'message' => 'Data sapi berhasil dihapus']);
    }

    // Menambah riwayat vaksin khusus
    public function addVaccine(Request $request, $id)
    {
        $cow = $request->user()->cows()->findOrFail($id);

        $validated = $request->validate([
            'vaccine_name' => 'required|string',
            'administered_at' => 'required|date',
        ]);

        $cow->vaccines()->create($validated);
        
        // Update the last_vaccinated_at on cow directly
        $cow->update(['last_vaccinated_at' => $validated['administered_at']]);

        return response()->json(['success' => true, 'message' => 'Riwayat vaksin berhasil ditambahkan', 'data' => $cow->load('vaccines')]);
    }

    // Menghapus riwayat vaksin
    public function removeVaccine(Request $request, $id)
    {
        $vaccine = \App\Models\VaccinationHistory::findOrFail($id);
        
        // Pastikan vaksin milik sapi yang dimiliki user ini
        if ($vaccine->cow->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin untuk menghapus vaksin ini'], 403);
        }

        $vaccine->delete();
        
        return response()->json(['success' => true, 'message' => 'Vaksin berhasil dihapus']);
    }
}
