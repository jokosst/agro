<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kebun;
use App\Services\GoogleDriveService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    protected GoogleDriveService $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    /**
     * Hitung jarak geofencing menggunakan Haversine Formula (dalam meter)
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meter

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * POST /api/absen/masuk
     */
    public function absenMasuk(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'nullable', // file or base64
        ]);

        $user = $request->user();
        $today = Carbon::today();
        $kebun = $user->kebun ?: Kebun::first();

        // Cek jarak GPS
        $jarak = 0;
        $isValidGeofence = true;
        if ($kebun && $kebun->latitude && $kebun->longitude) {
            $jarak = $this->calculateDistance(
                (float) $request->latitude,
                (float) $request->longitude,
                (float) $kebun->latitude,
                (float) $kebun->longitude
            );
            $isValidGeofence = ($jarak <= ($kebun->radius_meter ?: 50));
        }

        // Upload foto selfie ke Google Drive
        $fotoUrl = null;
        if ($request->hasFile('foto')) {
            $uploadResult = $this->driveService->uploadFile($request->file('foto'), 'absensi_masuk');
            $fotoUrl = $uploadResult['url'];
        } elseif ($request->filled('foto')) {
            $uploadResult = $this->driveService->uploadFile($request->foto, 'absensi_masuk');
            $fotoUrl = $uploadResult['url'];
        }

        // Simpan / update absensi masuk
        $absensi = Absensi::updateOrCreate(
            [
                'user_id' => $user->id,
                'tanggal' => $today,
            ],
            [
                'kebun_id' => $kebun ? $kebun->id : null,
                'jam_masuk' => Carbon::now()->format('H:i:s'),
                'foto_masuk' => $fotoUrl ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400',
                'lat_masuk' => $request->latitude,
                'long_masuk' => $request->longitude,
                'jarak_masuk_meter' => $jarak,
                'is_valid_geofence_masuk' => $isValidGeofence,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Absen pagi berhasil disimpan.',
            'data' => $absensi,
            'geofence' => [
                'distance_meters' => $jarak,
                'radius_meter' => $kebun ? $kebun->radius_meter : 50,
                'is_valid' => $isValidGeofence,
            ],
        ]);
    }

    /**
     * POST /api/absen/pulang
     */
    public function absenPulang(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'status_pekerjaan' => 'required|in:selesai,sebagian,belum',
            'foto' => 'nullable',
            'catatan' => 'nullable|string',
        ]);

        $user = $request->user();
        $today = Carbon::today();
        $kebun = $user->kebun ?: Kebun::first();

        // Cek jarak GPS
        $jarak = 0;
        $isValidGeofence = true;
        if ($kebun && $kebun->latitude && $kebun->longitude) {
            $jarak = $this->calculateDistance(
                (float) $request->latitude,
                (float) $request->longitude,
                (float) $kebun->latitude,
                (float) $kebun->longitude
            );
            $isValidGeofence = ($jarak <= ($kebun->radius_meter ?: 50));
        }

        // Upload foto selfie pulang ke Google Drive
        $fotoUrl = null;
        if ($request->hasFile('foto')) {
            $uploadResult = $this->driveService->uploadFile($request->file('foto'), 'absensi_pulang');
            $fotoUrl = $uploadResult['url'];
        } elseif ($request->filled('foto')) {
            $uploadResult = $this->driveService->uploadFile($request->foto, 'absensi_pulang');
            $fotoUrl = $uploadResult['url'];
        }

        // Update absensi pulang
        $absensi = Absensi::updateOrCreate(
            [
                'user_id' => $user->id,
                'tanggal' => $today,
            ],
            [
                'kebun_id' => $kebun ? $kebun->id : null,
                'jam_pulang' => Carbon::now()->format('H:i:s'),
                'foto_pulang' => $fotoUrl ?: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
                'lat_pulang' => $request->latitude,
                'long_pulang' => $request->longitude,
                'jarak_pulang_meter' => $jarak,
                'is_valid_geofence_pulang' => $isValidGeofence,
                'status_pekerjaan' => $request->status_pekerjaan,
                'catatan' => $request->catatan,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil disimpan.',
            'data' => $absensi,
            'geofence' => [
                'distance_meters' => $jarak,
                'radius_meter' => $kebun ? $kebun->radius_meter : 50,
                'is_valid' => $isValidGeofence,
            ],
        ]);
    }

    /**
     * GET /api/absen/today
     */
    public function today(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $absensi = Absensi::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        return response()->json([
            'success' => true,
            'data' => $absensi,
        ]);
    }
}
