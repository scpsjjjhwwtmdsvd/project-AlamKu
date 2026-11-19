<?php

namespace App\Http\Controllers;

use App\Models\KelompokTani;
use App\Models\LaporanLapangan;
use App\Models\PesananAdopsi;
use App\Models\Pohon;
use App\Models\Proyek;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            ['label' => 'Donatur', 'value' => User::query()->where('role', 'donatur')->count(), 'hint' => 'Akun donatur terdaftar'],
            ['label' => 'Kelompok Tani', 'value' => KelompokTani::query()->count(), 'hint' => 'Mitra tani aktif'],
            ['label' => 'Proyek', 'value' => Proyek::query()->count(), 'hint' => 'Program konservasi'],
            ['label' => 'Donasi Terkonfirmasi', 'value' => (int) PesananAdopsi::query()->where('status', 'terkonfirmasi')->sum('total_bayar'), 'hint' => 'Rupiah terkumpul', 'currency' => true],
        ];

        $modules = [
            ['route' => 'proyek.index', 'label' => 'Proyek', 'module' => 'proyek', 'count' => Proyek::query()->count(), 'desc' => 'Program tanam & kuota bibit'],
            ['route' => 'pesanan-adopsi.index', 'label' => 'Pesanan Adopsi', 'module' => 'adopsi', 'count' => PesananAdopsi::query()->count(), 'desc' => 'Pesanan donatur'],
            ['route' => 'pohon.index', 'label' => 'Pohon', 'module' => 'pohon', 'count' => Pohon::query()->count(), 'desc' => 'Pohon terdaftar'],
            ['route' => 'laporan-lapangan.index', 'label' => 'Laporan Lapangan', 'module' => 'laporan', 'count' => LaporanLapangan::query()->count(), 'desc' => 'Verifikasi lapangan'],
            ['route' => 'kelompok-tani.index', 'label' => 'Kelompok Tani', 'module' => 'kelompok-tani', 'count' => KelompokTani::query()->count(), 'desc' => 'Mitra & legalitas'],
            ['route' => 'lokasi-hutan.index', 'label' => 'Lokasi Hutan', 'module' => 'lokasi-hutan', 'count' => \App\Models\LokasiHutan::query()->count(), 'desc' => 'Area konservasi'],
            ['route' => 'jenis-pohon.index', 'label' => 'Jenis Pohon', 'module' => 'jenis-pohon', 'count' => \App\Models\JenisPohon::query()->count(), 'desc' => 'Spesies & serapan karbon'],
            ['route' => 'pembayaran.index', 'label' => 'Pembayaran', 'module' => 'pembayaran', 'count' => \App\Models\Pembayaran::query()->count(), 'desc' => 'Transaksi & gateway'],
            ['route' => 'alokasi-dana.index', 'label' => 'Alokasi Dana', 'module' => 'alokasi-dana', 'count' => \App\Models\AlokasiDana::query()->count(), 'desc' => 'Anggaran per proyek'],
            ['route' => 'foto-geotag.index', 'label' => 'Foto Geotag', 'module' => 'foto-geotag', 'count' => \App\Models\FotoGeotag::query()->count(), 'desc' => 'Bukti visual lapangan'],
            ['route' => 'log-perubahan.index', 'label' => 'Log Perubahan', 'module' => 'log-perubahan', 'count' => \App\Models\LogPerubahan::query()->count(), 'desc' => 'Jejak audit data'],
        ];

        $recentOrders = PesananAdopsi::query()
            ->with(['donatur', 'proyek'])
            ->latest('id')
            ->take(5)
            ->get();

        $recentReports = LaporanLapangan::query()
            ->with(['proyek', 'pengguna'])
            ->latest('id')
            ->take(5)
            ->get();

        $projects = Proyek::query()
            ->with(['lokasiHutan', 'jenisPohon'])
            ->latest('id')
            ->take(4)
            ->get();

        return view('admin.dashboard', compact('stats', 'modules', 'recentOrders', 'recentReports', 'projects'));
    }
}
