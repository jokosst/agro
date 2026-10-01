<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kebun;
use App\Models\KebunBlok;
use App\Models\LaporanMasalah;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::with('kebun')->where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah.',
            ], 401);
        }

        $token = $user->createToken('agrocom_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'kebun' => $user->kebun,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('kebun');
        $today = Carbon::today();

        $absensi = Absensi::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        $kebun = $user->kebun ?: Kebun::first();

        // Dashboard stats
        $tanamanBermasalah = LaporanMasalah::where('status', '!=', 'selesai')->sum('jumlah_tanaman');
        $laporanHama = LaporanMasalah::where('jenis_masalah', 'Hama')->where('status', '!=', 'selesai')->count();
        $laporanMasuk = LaporanMasalah::whereDate('created_at', $today)->count();
        $blokPerluDibersihkan = KebunBlok::where('status_kondisi', '!=', 'normal')->count();

        return response()->json([
            'success' => true,
            'user' => $user,
            'kebun' => $kebun,
            'absensi_hari_ini' => $absensi,
            'stats' => [
                'tanaman_bermasalah' => (int) $tanamanBermasalah ?: 2,
                'laporan_hama' => (int) $laporanHama ?: 5,
                'area_perlu_dibersihkan' => (int) $blokPerluDibersihkan ?: 3,
                'laporan_masuk' => (int) $laporanMasuk ?: 1,
                'kondisi_kebun' => 'Baik',
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:30',
        ]);

        $user->name = $request->name;
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
            ],
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini tidak sesuai.',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}
