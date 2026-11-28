<?php

namespace Database\Seeders;

use App\Models\JenisPohon;
use App\Models\KelompokTani;
use App\Models\LokasiHutan;
use App\Models\Proyek;
use App\Models\User;
use Illuminate\Database\Seeder;

class KarhutlaFundraisingSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            [
                'mitra' => [
                    'name' => 'Yohanes Kurniawan',
                    'email' => 'mitra.kalimantan@alamku.test',
                    'phone' => '081200001101',
                ],
                'kelompok' => [
                    'nama_kelompok' => 'Kelompok Tani Hutan Gambut Katingan',
                    'provinsi' => 'Kalimantan Tengah',
                    'kabupaten' => 'Katingan',
                    'desa' => 'Sebangau',
                    'nomor_legalitas' => 'SK-KARHUTLA-KALTENG-01',
                    'deskripsi' => 'Kelompok masyarakat pengelola lahan gambut di sekitar Sebangau yang bergerak pada restorasi dan pencegahan karhutla.',
                ],
                'lokasi' => [
                    'nama_area_konservasi' => 'Restorasi Gambut Sebangau',
                    'provinsi' => 'Kalimantan Tengah',
                    'kabupaten' => 'Katingan',
                    'latitude' => -2.5000000,
                    'longitude' => 113.5000000,
                    'luas_hektar' => 180.00,
                    'deskripsi' => 'Areal restorasi gambut bekas terbakar di koridor Sebangau, Kalimantan Tengah.',
                ],
                'jenis' => [
                    'nama_lokal' => 'Belangeran',
                    'nama_ilmiah' => 'Shorea balangeran',
                    'serapan_karbon_per_tahun' => 32.50,
                ],
                'proyek' => [
                    'judul' => 'Pulihkan Hutan Terbakar Kalimantan',
                    'deskripsi' => 'Pemulihan lahan gambut bekas karhutla di Sebangau melalui penanaman belangeran dan tanaman lokal. Sepanjang 2023, sekitar 508.430 hektare lahan di Kalimantan terbakar atau 43,71% dari total karhutla nasional (KLHK/BRIN, 2024). Greenpeace mencatat sekitar 123.000 hektare karhutla 2024 berada di Kalimantan dan Sumatra.',
                    'target_ekologi' => 'Menanam 2.000 bibit di 180 hektare areal restorasi, menahan erosi gambut, dan memulihkan tutupan lahan bekas kebakaran. Sumber: SiPongi Kemenhut/KLHK, BRIN, Greenpeace.',
                    'biaya_per_bibit' => 25000,
                    'kuota_bibit' => 2000,
                    'tanggal_mulai_tanam' => '2026-12-01',
                    'tanggal_selesai_tanam' => '2027-08-31',
                ],
            ],
            [
                'mitra' => [
                    'name' => 'Rina Marlina',
                    'email' => 'mitra.sumatera@alamku.test',
                    'phone' => '081200001102',
                ],
                'kelompok' => [
                    'nama_kelompok' => 'Kelompok Tani Hutan Gambut Pelalawan',
                    'provinsi' => 'Riau',
                    'kabupaten' => 'Pelalawan',
                    'desa' => 'Teluk Meranti',
                    'nomor_legalitas' => 'SK-KARHUTLA-RIAU-01',
                    'deskripsi' => 'Kelompok tani yang mendampingi restorasi lahan gambut dan pencegahan kebakaran di pesisir Riau.',
                ],
                'lokasi' => [
                    'nama_area_konservasi' => 'Restorasi Gambut Pelalawan',
                    'provinsi' => 'Riau',
                    'kabupaten' => 'Pelalawan',
                    'latitude' => 0.3000000,
                    'longitude' => 102.1000000,
                    'luas_hektar' => 150.00,
                    'deskripsi' => 'Areal gambut bekas terbakar di Kabupaten Pelalawan, Riau.',
                ],
                'jenis' => [
                    'nama_lokal' => 'Meranti',
                    'nama_ilmiah' => 'Shorea leprosula',
                    'serapan_karbon_per_tahun' => 35.20,
                ],
                'proyek' => [
                    'judul' => 'Pulihkan Hutan Terbakar Sumatra',
                    'deskripsi' => 'Penanaman kembali lahan gambut bekas karhutla di Riau. Pada 2023, Sumatera Selatan mencatat 132.082,86 hektare lahan terbakar (SiPongi/KLHK), sedangkan BNPB mencatat Riau menjadi provinsi dengan kejadian karhutla terbanyak pada Juli 2025, yaitu 53 kejadian.',
                    'target_ekologi' => 'Menanam 1.800 bibit di 150 hektare areal restorasi, mengurangi risiko kebakaran berulang, dan menjaga simpanan karbon gambut. Sumber: SiPongi Kemenhut/KLHK dan BNPB, 2025.',
                    'biaya_per_bibit' => 20000,
                    'kuota_bibit' => 1800,
                    'tanggal_mulai_tanam' => '2026-12-01',
                    'tanggal_selesai_tanam' => '2027-07-31',
                ],
            ],
            [
                'mitra' => [
                    'name' => 'Andi Saputra',
                    'email' => 'mitra.sulawesi@alamku.test',
                    'phone' => '081200001103',
                ],
                'kelompok' => [
                    'nama_kelompok' => 'Kelompok Tani Hutan Luwu',
                    'provinsi' => 'Sulawesi Selatan',
                    'kabupaten' => 'Luwu',
                    'desa' => 'Seko',
                    'nomor_legalitas' => 'SK-KARHUTLA-SULSEL-01',
                    'deskripsi' => 'Kelompok tani di dataran tinggi Luwu yang merawat bibit dan lahan bekas kebakaran.',
                ],
                'lokasi' => [
                    'nama_area_konservasi' => 'Restorasi Lereng Luwu',
                    'provinsi' => 'Sulawesi Selatan',
                    'kabupaten' => 'Luwu',
                    'latitude' => -3.0000000,
                    'longitude' => 120.2000000,
                    'luas_hektar' => 90.00,
                    'deskripsi' => 'Areal restorasi lereng bekas karhutla di Kabupaten Luwu, Sulawesi Selatan.',
                ],
                'jenis' => [
                    'nama_lokal' => 'Cempaka',
                    'nama_ilmiah' => 'Elmerrillia ovalis',
                    'serapan_karbon_per_tahun' => 28.40,
                ],
                'proyek' => [
                    'judul' => 'Pulihkan Hutan Terbakar Sulawesi',
                    'deskripsi' => 'Pemulihan lereng bekas kebakaran di Luwu dengan cempaka dan tanaman lokal. Data SiPongi/KLHK mencatat 6.489,26 hektare karhutla di Sulawesi Selatan pada 2023 dan 3.427,36 hektare pada 2024.',
                    'target_ekologi' => 'Menanam 1.200 bibit di 90 hektare, memperkuat tutupan lereng, dan mengurangi risiko longsor pascakebakaran. Sumber: SiPongi Kemenhut/KLHK 2023-2024.',
                    'biaya_per_bibit' => 25000,
                    'kuota_bibit' => 1200,
                    'tanggal_mulai_tanam' => '2026-12-01',
                    'tanggal_selesai_tanam' => '2027-06-30',
                ],
            ],
            [
                'mitra' => [
                    'name' => 'Mikael Wenda',
                    'email' => 'mitra.papua@alamku.test',
                    'phone' => '081200001104',
                ],
                'kelompok' => [
                    'nama_kelompok' => 'Kelompok Tani Hutan Merauke',
                    'provinsi' => 'Papua Selatan',
                    'kabupaten' => 'Merauke',
                    'desa' => 'Sota',
                    'nomor_legalitas' => 'SK-KARHUTLA-PAPUA-01',
                    'deskripsi' => 'Kelompok masyarakat adat dan tani yang merawat lahan bekas kebakaran di Papua Selatan.',
                ],
                'lokasi' => [
                    'nama_area_konservasi' => 'Restorasi Savana Merauke',
                    'provinsi' => 'Papua Selatan',
                    'kabupaten' => 'Merauke',
                    'latitude' => -8.5000000,
                    'longitude' => 140.4000000,
                    'luas_hektar' => 120.00,
                    'deskripsi' => 'Areal restorasi savana dan hutan bekas karhutla di Merauke, Papua Selatan.',
                ],
                'jenis' => [
                    'nama_lokal' => 'Merbau',
                    'nama_ilmiah' => 'Intsia bijuga',
                    'serapan_karbon_per_tahun' => 40.10,
                ],
                'proyek' => [
                    'judul' => 'Pulihkan Hutan Terbakar Papua',
                    'deskripsi' => 'Restorasi lahan bekas kebakaran di Papua Selatan. Pada 2023, Papua Selatan mencatat 150.813,34 hektare karhutla (SiPongi/KLHK), sementara MapBiomas mencatat puncak kebakaran Papua seluas 448.981 hektare pada 2015.',
                    'target_ekologi' => 'Menanam 1.500 bibit di 120 hektare, memulihkan tutupan savana dan hutan, serta melindungi keanekaragaman hayati setempat. Sumber: SiPongi Kemenhut/KLHK dan MapBiomas Fire.',
                    'biaya_per_bibit' => 30000,
                    'kuota_bibit' => 1500,
                    'tanggal_mulai_tanam' => '2026-12-01',
                    'tanggal_selesai_tanam' => '2027-09-30',
                ],
            ],
        ];

        foreach ($regions as $region) {
            $mitra = User::updateOrCreate(
                ['email' => $region['mitra']['email']],
                [
                    'name' => $region['mitra']['name'],
                    'phone' => $region['mitra']['phone'],
                    'password' => 'password',
                    'role' => 'mitra',
                ]
            );

            $kelompok = KelompokTani::updateOrCreate(
                ['pengguna_id' => $mitra->id],
                $region['kelompok'] + [
                    'terverifikasi' => true,
                    'foto_profil' => null,
                ]
            );

            $lokasi = LokasiHutan::updateOrCreate(
                ['nama_area_konservasi' => $region['lokasi']['nama_area_konservasi']],
                $region['lokasi'] + ['kelompok_tani_id' => $kelompok->id]
            );

            $jenis = JenisPohon::updateOrCreate(
                ['nama_lokal' => $region['jenis']['nama_lokal']],
                $region['jenis']
            );

            Proyek::updateOrCreate(
                ['judul' => $region['proyek']['judul']],
                $region['proyek'] + [
                    'lokasi_hutan_id' => $lokasi->id,
                    'kelompok_tani_id' => $kelompok->id,
                    'jenis_pohon_id' => $jenis->id,
                    'bibit_dipesan' => 0,
                    'status' => 'aktif',
                ]
            );
        }
    }
}
