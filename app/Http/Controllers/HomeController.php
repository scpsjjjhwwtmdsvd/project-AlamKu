<?php

namespace App\Http\Controllers;

use App\Models\LaporanLapangan;
use App\Models\PesananAdopsi;
use App\Models\Pohon;
use App\Models\Proyek;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $stats = [
            ['value' => Proyek::count(), 'label' => 'Program konservasi'],
            ['value' => Pohon::count(), 'label' => 'Pohon terdaftar'],
            ['value' => PesananAdopsi::count(), 'label' => 'Adopsi pohon'],
            ['value' => LaporanLapangan::count(), 'label' => 'Laporan lapangan'],
        ];

        $projects = Proyek::query()
            ->with(['lokasiHutan', 'jenisPohon'])
            ->whereIn('status', ['aktif', 'penuh'])
            ->where('judul', 'not like', 'Pulihkan Hutan Terbakar%')
            ->latest('id')
            ->take(3)
            ->get();

        $karhutlaProjects = Proyek::query()
            ->with(['lokasiHutan', 'jenisPohon'])
            ->whereIn('status', ['aktif', 'penuh'])
            ->where('judul', 'like', 'Pulihkan Hutan Terbakar%')
            ->latest('id')
            ->get();

        $totalKuota = (int) Proyek::query()->sum('kuota_bibit');
        $totalAdopsi = (int) Proyek::query()->sum('bibit_dipesan');
        $activeProjects = Proyek::query()->where('status', 'aktif')->count();

        return view('home', compact('stats', 'projects', 'karhutlaProjects', 'totalKuota', 'totalAdopsi', 'activeProjects'));
    }
}
