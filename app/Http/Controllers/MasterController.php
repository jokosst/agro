<?php

namespace App\Http\Controllers;

use App\Models\Kebun;
use App\Models\KebunBlok;
use App\Models\MasterHamaPenyakit;
use App\Models\TugasHarian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MasterController extends Controller
{
    // ==========================================
    // 1. MASTER LOKASI / LAHAN & BLOK
    // ==========================================
    public function lahan()
    {
        $kebun = Kebun::with('bloks')->first();
        if (! $kebun) {
            $kebun = Kebun::create([
                'nama' => 'Kebun Cabai Agrocom',
                'lokasi_text' => 'Sambas, Kalimantan Barat',
                'latitude' => -0.1234000,
                'longitude' => 109.3456000,
                'radius_meter' => 50,
                'luas_lahan' => '2 Hektar',
                'status' => 'aktif',
            ]);
        }

        $bloks = KebunBlok::where('kebun_id', $kebun->id)->get();
        $googleMapsApiKey = config('services.google.maps_api_key', env('GOOGLE_MAPS_API_KEY', ''));

        return view('admin.master.lahan', compact('kebun', 'bloks', 'googleMapsApiKey'));
    }

    public function updateKebun(Request $request, $id)
    {
        $kebun = Kebun::findOrFail($id);
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'lokasi_text' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:5000',
            'luas_lahan' => 'nullable|string|max:100',
            'status' => 'required|in:aktif,nonaktif,pemeliharaan',
        ]);

        $kebun->update($validated);

        return back()->with('success', 'Data informasi kebun berhasil diperbarui.');
    }

    public function storeBlok(Request $request)
    {
        $validated = $request->validate([
            'kebun_id' => 'required|exists:kebun,id',
            'kode_blok' => 'required|string|max:50',
            'nama_blok' => 'required|string|max:255',
            'status_kondisi' => 'required|in:normal,perhatian,masalah',
            'jumlah_tanaman' => 'required|integer|min:0',
            'jumlah_masalah' => 'nullable|integer|min:0',
            'jumlah_hama' => 'nullable|integer|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'keterangan' => 'nullable|string',
        ]);

        KebunBlok::create($validated);

        return back()->with('success', 'Blok lahan baru berhasil ditambahkan.');
    }

    public function updateBlok(Request $request, $id)
    {
        $blok = KebunBlok::findOrFail($id);
        $validated = $request->validate([
            'kode_blok' => 'required|string|max:50',
            'nama_blok' => 'required|string|max:255',
            'status_kondisi' => 'required|in:normal,perhatian,masalah',
            'jumlah_tanaman' => 'required|integer|min:0',
            'jumlah_masalah' => 'nullable|integer|min:0',
            'jumlah_hama' => 'nullable|integer|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'keterangan' => 'nullable|string',
        ]);

        $blok->update($validated);

        return back()->with('success', 'Data Blok '.$blok->kode_blok.' berhasil diperbarui.');
    }

    public function destroyBlok($id)
    {
        $blok = KebunBlok::findOrFail($id);
        $kode = $blok->kode_blok;
        $blok->delete();

        return back()->with('success', 'Blok '.$kode.' berhasil dihapus.');
    }

    // ==========================================
    // 2. MASTER DATA PEKERJA (USERS)
    // ==========================================
    public function pekerja(Request $request)
    {
        $query = User::with('kebun')->orderBy('role', 'asc')->orderBy('name', 'asc');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $pekerjaList = $query->paginate(15);
        $kebunList = Kebun::all();

        return view('admin.master.pekerja', compact('pekerjaList', 'kebunList'));
    }

    public function storePekerja(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:pekerja,admin',
            'kebun_id' => 'nullable|exists:kebun,id',
            'password' => 'required|string|min:6',
        ], [
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'email.unique' => 'Alamat email sudah terdaftar.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['kebun_id'] = $validated['kebun_id'] ?: Kebun::first()?->id;

        User::create($validated);

        return back()->with('success', 'Data pekerja baru berhasil ditambahkan.');
    }

    public function updatePekerja(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:pekerja,admin',
            'kebun_id' => 'nullable|exists:kebun,id',
            'password' => 'nullable|string|min:6',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'Data pekerja '.$user->name.' berhasil diperbarui.');
    }

    public function resetPasswordPekerja(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password akun '.$user->name.' berhasil direset.');
    }

    public function destroyPekerja($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', 'Akun pekerja '.$name.' berhasil dihapus.');
    }

    // ==========================================
    // 3. MASTER TUGAS HARIAN
    // ==========================================
    public function tugasHarian()
    {
        $kebun = Kebun::first();
        $tugasList = TugasHarian::orderBy('urutan', 'asc')->orderBy('id', 'asc')->get();

        return view('admin.master.tugas_harian', compact('tugasList', 'kebun'));
    }

    public function storeTugasHarian(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['kebun_id'] = Kebun::first()?->id;
        $validated['urutan'] = $validated['urutan'] ?? (TugasHarian::max('urutan') + 1);
        $validated['is_active'] = $request->boolean('is_active', true);

        TugasHarian::create($validated);

        return back()->with('success', 'Tugas harian baru berhasil ditambahkan.');
    }

    public function updateTugasHarian(Request $request, $id)
    {
        $tugas = TugasHarian::findOrFail($id);
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $tugas->update($validated);

        return back()->with('success', 'Tugas "'.$tugas->judul.'" berhasil diperbarui.');
    }

    public function toggleTugasStatus($id)
    {
        $tugas = TugasHarian::findOrFail($id);
        $tugas->is_active = ! $tugas->is_active;
        $tugas->save();

        $status = $tugas->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Tugas {$tugas->judul} berhasil {$status}.");
    }

    public function destroyTugasHarian($id)
    {
        $tugas = TugasHarian::findOrFail($id);
        $judul = $tugas->judul;
        $tugas->delete();

        return back()->with('success', 'Tugas "'.$judul.'" berhasil dihapus.');
    }

    // ==========================================
    // 4. MASTER DATA DUKUNG (HAMA & PENYAKIT)
    // ==========================================
    public function dataDukung(Request $request)
    {
        $query = MasterHamaPenyakit::query()->orderBy('kategori', 'asc')->orderBy('nama', 'asc');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('bagian')) {
            $query->where('bagian_tanaman', $request->bagian);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('gejala', 'like', "%{$search}%")
                    ->orWhere('solusi_pengendalian', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(15);

        return view('admin.master.data_dukung', compact('items'));
    }

    public function storeDataDukung(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|in:Hama,Penyakit,Gulma,Lainnya',
            'bagian_tanaman' => 'required|in:Daun,Batang,Bunga,Buah,Akar,Semua',
            'gejala' => 'nullable|string',
            'solusi_pengendalian' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        MasterHamaPenyakit::create($validated);

        return back()->with('success', 'Data dukung '.$validated['nama'].' berhasil ditambahkan.');
    }

    public function updateDataDukung(Request $request, $id)
    {
        $item = MasterHamaPenyakit::findOrFail($id);
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|in:Hama,Penyakit,Gulma,Lainnya',
            'bagian_tanaman' => 'required|in:Daun,Batang,Bunga,Buah,Akar,Semua',
            'gejala' => 'nullable|string',
            'solusi_pengendalian' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $item->update($validated);

        return back()->with('success', 'Data dukung '.$item->nama.' berhasil diperbarui.');
    }

    public function destroyDataDukung($id)
    {
        $item = MasterHamaPenyakit::findOrFail($id);
        $nama = $item->nama;
        $item->delete();

        return back()->with('success', 'Data '.$nama.' berhasil dihapus.');
    }
}
