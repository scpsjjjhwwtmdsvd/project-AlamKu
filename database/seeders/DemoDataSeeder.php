<?php

namespace Database\Seeders;

use App\Models\AlokasiDana;
use App\Models\FotoGeotag;
use App\Models\JenisPohon;
use App\Models\KelompokTani;
use App\Models\LaporanLapangan;
use App\Models\LokasiHutan;
use App\Models\Pembayaran;
use App\Models\PesananAdopsi;
use App\Models\Proyek;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@alamku.test'],
            [
                'name' => 'Arif Nugraha',
                'phone' => '081200000001',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        $donatur = User::updateOrCreate(
            ['email' => 'donatur@alamku.test'],
            [
                'name' => 'Sari Wulandari',
                'phone' => '081200000002',
                'password' => 'password',
                'role' => 'donatur',
            ]
        );

        $mitra = User::updateOrCreate(
            ['email' => 'mitra@alamku.test'],
            [
                'name' => 'Pak Dedi',
                'phone' => '081200000003',
                'password' => 'password',
                'role' => 'mitra',
            ]
        );

        $kelompok = KelompokTani::updateOrCreate(
            ['pengguna_id' => $mitra->id],
            [
                'nama_kelompok' => 'Kelompok Tani Rimba Lestari',
                'provinsi' => 'Jawa Barat',
                'kabupaten' => 'Bandung Barat',
                'desa' => 'Cikole',
                'nomor_legalitas' => 'SK-DEMO-2026-001',
                'terverifikasi' => true,
                'foto_profil' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80',
                'deskripsi' => 'Kelompok tani yang berfokus pada restorasi hutan dan penguatan ekonomi warga sekitar.',
            ]
        );

        $lokasiCikole = LokasiHutan::updateOrCreate(
            ['nama_area_konservasi' => 'Hutan Lindung Cikole'],
            [
                'kelompok_tani_id' => $kelompok->id,
                'provinsi' => 'Jawa Barat',
                'kabupaten' => 'Bandung Barat',
                'latitude' => -6.7865000,
                'longitude' => 107.6463000,
                'luas_hektar' => 24.50,
                'deskripsi' => 'Area konservasi lereng utara dengan tutupan hutan sekunder.',
            ]
        );

        $lokasiLembang = LokasiHutan::updateOrCreate(
            ['nama_area_konservasi' => 'Blok Restorasi Lembang'],
            [
                'kelompok_tani_id' => $kelompok->id,
                'provinsi' => 'Jawa Barat',
                'kabupaten' => 'Bandung Barat',
                'latitude' => -6.8124000,
                'longitude' => 107.6179000,
                'luas_hektar' => 12.75,
                'deskripsi' => 'Blok restorasi yang menghubungkan koridor satwa dan mata air desa.',
            ]
        );

        $mahoni = JenisPohon::updateOrCreate(
            ['nama_lokal' => 'Mahoni'],
            ['nama_ilmiah' => 'Swietenia macrophylla', 'serapan_karbon_per_tahun' => 42.50]
        );

        $sengon = JenisPohon::updateOrCreate(
            ['nama_lokal' => 'Sengon'],
            ['nama_ilmiah' => 'Albizia chinensis', 'serapan_karbon_per_tahun' => 35.20]
        );

        $kaliandra = JenisPohon::updateOrCreate(
            ['nama_lokal' => 'Kaliandra'],
            ['nama_ilmiah' => 'Calliandra calothyrsus', 'serapan_karbon_per_tahun' => 28.75]
        );

        $proyekCikole = Proyek::updateOrCreate(
            ['judul' => 'Restorasi Hutan Cikole'],
            [
                'lokasi_hutan_id' => $lokasiCikole->id,
                'kelompok_tani_id' => $kelompok->id,
                'jenis_pohon_id' => $mahoni->id,
                'deskripsi' => 'Menanam mahoni di area lereng utara untuk memulihkan tutupan hutan dan memperkuat sumber mata air desa.',
                'target_ekologi' => 'Memulihkan 24 hektare tutupan hutan, menahan erosi lereng, dan menambah serapan karbon tahunan.',
                'biaya_per_bibit' => 25000,
                'kuota_bibit' => 1000,
                'bibit_dipesan' => 420,
                'tanggal_mulai_tanam' => '2026-03-01',
                'tanggal_selesai_tanam' => '2026-08-31',
                'status' => 'aktif',
            ]
        );

        $proyekLembang = Proyek::updateOrCreate(
            ['judul' => 'Koridor Hijau Lembang'],
            [
                'lokasi_hutan_id' => $lokasiLembang->id,
                'kelompok_tani_id' => $kelompok->id,
                'jenis_pohon_id' => $sengon->id,
                'deskripsi' => 'Membangun koridor hijau dengan sengon dan tanaman cepat tumbuh untuk menghubungkan dua blok hutan.',
                'target_ekologi' => 'Menciptakan koridor satwa sepanjang 3 kilometer dan meningkatkan tutupan kanopi blok barat.',
                'biaya_per_bibit' => 30000,
                'kuota_bibit' => 600,
                'bibit_dipesan' => 600,
                'tanggal_mulai_tanam' => '2026-02-15',
                'tanggal_selesai_tanam' => '2026-07-15',
                'status' => 'penuh',
            ]
        );

        $proyekKaliandra = Proyek::updateOrCreate(
            ['judul' => 'Madu & Kaliandra Cikole'],
            [
                'lokasi_hutan_id' => $lokasiCikole->id,
                'kelompok_tani_id' => $kelompok->id,
                'jenis_pohon_id' => $kaliandra->id,
                'deskripsi' => 'Menanam kaliandra untuk pakan lebah dan pupuk hijau, mendukung pendapatan tambahan kelompok tani.',
                'target_ekologi' => 'Meningkatkan pakan lebah lokal, memperbaiki tanah, dan mengurangi ketergantungan pada pupuk kimia.',
                'biaya_per_bibit' => 20000,
                'kuota_bibit' => 800,
                'bibit_dipesan' => 215,
                'tanggal_mulai_tanam' => '2026-04-01',
                'tanggal_selesai_tanam' => '2026-10-30',
                'status' => 'aktif',
            ]
        );

        $orderConfirmed = PesananAdopsi::updateOrCreate(
            ['kode_pesanan' => 'ADP-DEMO-0001'],
            [
                'donatur_id' => $donatur->id,
                'proyek_id' => $proyekCikole->id,
                'jumlah_bibit' => 20,
                'harga_per_bibit' => 25000,
                'total_bayar' => 500000,
                'status' => 'terkonfirmasi',
                'batas_bayar' => now()->addDays(3),
                'dikonfirmasi_pada' => now()->subDays(5),
            ]
        );

        PesananAdopsi::updateOrCreate(
            ['kode_pesanan' => 'ADP-DEMO-0002'],
            [
                'donatur_id' => $donatur->id,
                'proyek_id' => $proyekKaliandra->id,
                'jumlah_bibit' => 5,
                'harga_per_bibit' => 20000,
                'total_bayar' => 100000,
                'status' => 'menunggu_pembayaran',
                'batas_bayar' => now()->addDay(),
                'dikonfirmasi_pada' => null,
            ]
        );

        PesananAdopsi::updateOrCreate(
            ['kode_pesanan' => 'ADP-DEMO-0003'],
            [
                'donatur_id' => $donatur->id,
                'proyek_id' => $proyekLembang->id,
                'jumlah_bibit' => 12,
                'harga_per_bibit' => 30000,
                'total_bayar' => 360000,
                'status' => 'terkonfirmasi',
                'batas_bayar' => now()->subDays(10),
                'dikonfirmasi_pada' => now()->subDays(12),
            ]
        );

        $trees = [
            ['kode_pohon' => 'CKL-MHN-001', 'proyek_id' => $proyekCikole->id, 'jenis_pohon_id' => $mahoni->id, 'status' => 'tumbuh_baik', 'tanggal' => '2026-03-12', 'lat' => -6.7861000, 'long' => 107.6469000],
            ['kode_pohon' => 'CKL-MHN-002', 'proyek_id' => $proyekCikole->id, 'jenis_pohon_id' => $mahoni->id, 'status' => 'tumbuh_baik', 'tanggal' => '2026-03-12', 'lat' => -6.7863000, 'long' => 107.6471000],
            ['kode_pohon' => 'CKL-MHN-003', 'proyek_id' => $proyekCikole->id, 'jenis_pohon_id' => $mahoni->id, 'status' => 'perlu_penyulaman', 'tanggal' => '2026-03-14', 'lat' => -6.7867000, 'long' => 107.6474000],
            ['kode_pohon' => 'CKL-KLD-001', 'proyek_id' => $proyekKaliandra->id, 'jenis_pohon_id' => $kaliandra->id, 'status' => 'tumbuh_baik', 'tanggal' => '2026-04-18', 'lat' => -6.7859000, 'long' => 107.6466000],
        ];

        foreach ($trees as $tree) {
            DB::table('pohon')->updateOrInsert(
                ['kode_pohon' => $tree['kode_pohon']],
                [
                    'pesanan_id' => $orderConfirmed->id,
                    'donatur_id' => $donatur->id,
                    'proyek_id' => $tree['proyek_id'],
                    'jenis_pohon_id' => $tree['jenis_pohon_id'],
                    'status_kesehatan' => $tree['status'],
                    'latitude' => $tree['lat'],
                    'longitude' => $tree['long'],
                    'tanggal_tanam' => $tree['tanggal'],
                    'created_at' => now(),
                ]
            );
        }

        Pembayaran::updateOrCreate(
            ['referensi_penyedia' => 'DEMO-QRIS-0001'],
            [
                'pesanan_id' => $orderConfirmed->id,
                'metode' => 'qris',
                'penyedia' => 'AlamKu Demo Gateway',
                'jumlah' => 500000,
                'status' => 'berhasil',
                'dibayar_pada' => now()->subDays(5),
                'data_respons_gateway' => ['mode' => 'demo', 'reference' => 'DEMO-QRIS-0001'],
            ]
        );

        AlokasiDana::updateOrCreate(
            ['proyek_id' => $proyekCikole->id, 'kategori' => 'Pembibitan'],
            ['rencana' => 8000000, 'realisasi' => 6200000, 'keterangan' => 'Pengadaan bibit dan media tanam.']
        );

        AlokasiDana::updateOrCreate(
            ['proyek_id' => $proyekCikole->id, 'kategori' => 'Perawatan'],
            ['rencana' => 4000000, 'realisasi' => 2100000, 'keterangan' => 'Penyulaman, pemupukan, dan pengendalian gulma.']
        );

        $reportOne = LaporanLapangan::updateOrCreate(
            ['judul' => 'Pemantauan blok utara Cikole'],
            [
                'pohon_id' => DB::table('pohon')->where('kode_pohon', 'CKL-MHN-001')->value('id'),
                'proyek_id' => $proyekCikole->id,
                'pengguna_id' => $mitra->id,
                'deskripsi' => 'Pohon tumbuh baik, daun lebat, dan tidak ditemukan serangan hama berarti.',
                'kondisi' => 'sehat',
                'tinggi_cm' => 165,
                'diameter_cm' => 4.20,
                'tanggal_laporan' => '2026-10-05',
                'status' => 'terverifikasi',
                'catatan_verifikasi' => 'Data sesuai dengan foto geotag.',
            ]
        );

        LaporanLapangan::updateOrCreate(
            ['judul' => 'Penyulaman bibit Cikole'],
            [
                'pohon_id' => DB::table('pohon')->where('kode_pohon', 'CKL-MHN-003')->value('id'),
                'proyek_id' => $proyekCikole->id,
                'pengguna_id' => $mitra->id,
                'deskripsi' => 'Bibit perlu penyulaman karena tanah terlalu padat. Akan ditanam ulang pekan depan.',
                'kondisi' => 'perlu_perawatan',
                'tinggi_cm' => 92,
                'diameter_cm' => 2.10,
                'tanggal_laporan' => '2026-10-18',
                'status' => 'draft',
                'catatan_verifikasi' => null,
            ]
        );

        FotoGeotag::updateOrCreate(
            ['path' => 'https://images.unsplash.com/photo-1511497584788-876760111969?auto=format&fit=crop&w=900&q=80'],
            [
                'laporan_lapangan_id' => $reportOne->id,
                'pohon_id' => DB::table('pohon')->where('kode_pohon', 'CKL-MHN-001')->value('id'),
                'latitude' => -6.7861000,
                'longitude' => 107.6469000,
                'altitude' => 1280,
                'captured_at' => now()->subDays(3),
                'keterangan' => 'Foto pemantauan pohon mahoni.',
            ]
        );
    }
}
