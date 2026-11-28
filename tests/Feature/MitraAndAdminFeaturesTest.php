<?php

namespace Tests\Feature;

use App\Models\FotoGeotag;
use App\Models\JenisPohon;
use App\Models\KelompokTani;
use App\Models\LaporanLapangan;
use App\Models\LokasiHutan;
use App\Models\Pohon;
use App\Models\Proyek;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\KarhutlaFundraisingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MitraAndAdminFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoDataSeeder::class);
    }

    private function user(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }

    private function png(): UploadedFile
    {
        $binary = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII='
        );

        return UploadedFile::fake()->createWithContent('foto.png', $binary);
    }

    public function test_karhutla_fundraising_programs_appear_on_public_pages(): void
    {
        $this->seed(KarhutlaFundraisingSeeder::class);

        $this->assertSame(
            4,
            Proyek::query()->where('judul', 'like', 'Pulihkan Hutan Terbakar%')->count()
        );

        $this->get('/')
            ->assertOk()
            ->assertSee('Penggalangan dana karhutla')
            ->assertSee('Pulihkan Hutan Terbakar Kalimantan')
            ->assertSee('Pulihkan Hutan Terbakar Papua');

        $this->get('/program')
            ->assertOk()
            ->assertSee('Pulihkan Hutan Terbakar Sumatra')
            ->assertSee('Pulihkan Hutan Terbakar Sulawesi');
    }

    public function test_mitra_can_submit_a_new_project_as_draft(): void
    {
        $mitra = $this->user('mitra@alamku.test');
        $kelompok = $mitra->kelompokTani;
        $location = LokasiHutan::query()->where('kelompok_tani_id', $kelompok->id)->firstOrFail();
        $species = JenisPohon::query()->firstOrFail();

        $this->actingAs($mitra)
            ->get(route('mitra.projects.create'))
            ->assertOk()
            ->assertSee('Ajukan proyek baru')
            ->assertSee('Proyek baru berstatus');

        $response = $this->actingAs($mitra)->post(route('mitra.projects.store'), [
            'lokasi_hutan_id' => $location->id,
            'jenis_pohon_id' => $species->id,
            'judul' => 'Proyek Uji Mitra',
            'deskripsi' => 'Deskripsi proyek uji.',
            'target_ekologi' => 'Target ekologi proyek uji.',
            'biaya_per_bibit' => 25000,
            'kuota_bibit' => 100,
            'tanggal_mulai_tanam' => '2026-12-01',
            'tanggal_selesai_tanam' => '2027-06-01',
        ]);

        $project = Proyek::query()->where('judul', 'Proyek Uji Mitra')->firstOrFail();

        $response->assertRedirect(route('mitra.projects.show', $project));
        $this->assertSame('draf', $project->status);
        $this->assertSame($kelompok->id, $project->kelompok_tani_id);
        $this->assertSame(0, (int) $project->bibit_dipesan);
    }

    public function test_mitra_cannot_activate_their_own_draft_project(): void
    {
        $mitra = $this->user('mitra@alamku.test');
        $kelompok = $mitra->kelompokTani;
        $location = LokasiHutan::query()->where('kelompok_tani_id', $kelompok->id)->firstOrFail();
        $species = JenisPohon::query()->firstOrFail();

        $project = Proyek::create([
            'lokasi_hutan_id' => $location->id,
            'kelompok_tani_id' => $kelompok->id,
            'jenis_pohon_id' => $species->id,
            'judul' => 'Draf Tidak Boleh Aktif',
            'deskripsi' => 'Draf.',
            'target_ekologi' => 'Target.',
            'biaya_per_bibit' => 25000,
            'kuota_bibit' => 10,
            'bibit_dipesan' => 0,
            'tanggal_mulai_tanam' => '2026-12-01',
            'tanggal_selesai_tanam' => '2027-06-01',
            'status' => 'draf',
        ]);

        $this->actingAs($mitra)
            ->put(route('mitra.projects.status', $project), ['status' => 'aktif'])
            ->assertSessionHas('error');

        $this->assertSame('draf', $project->fresh()->status);
    }

    public function test_mitra_can_open_project_detail_and_update_process_status(): void
    {
        $mitra = $this->user('mitra@alamku.test');
        $project = Proyek::query()->firstOrFail();

        $this->actingAs($mitra)
            ->get(route('mitra.projects.show', $project))
            ->assertOk()
            ->assertSee('Rincian dana')
            ->assertSee('Notifikasi donasi');

        $this->actingAs($mitra)
            ->put(route('mitra.projects.status', $project), ['status' => 'selesai'])
            ->assertRedirect();

        $this->assertSame('selesai', $project->fresh()->status);
    }

    public function test_mitra_can_update_tree_health_status(): void
    {
        $mitra = $this->user('mitra@alamku.test');
        $tree = Pohon::query()->firstOrFail();

        $this->actingAs($mitra)
            ->put(route('mitra.trees.status', $tree), ['status_kesehatan' => 'perlu_penyulaman'])
            ->assertRedirect();

        $this->assertSame('perlu_penyulaman', $tree->fresh()->status_kesehatan);
    }

    public function test_mitra_can_edit_condition_on_a_draft_report(): void
    {
        $mitra = $this->user('mitra@alamku.test');
        $report = LaporanLapangan::query()->where('status', 'draft')->firstOrFail();

        $this->actingAs($mitra)
            ->get(route('mitra.reports.edit', $report))
            ->assertOk()
            ->assertSee('Perbarui kabar lapangan');

        $this->actingAs($mitra)
            ->put(route('mitra.reports.update', $report), [
                'proyek_id' => $report->proyek_id,
                'pohon_id' => $report->pohon_id,
                'judul' => $report->judul,
                'deskripsi' => $report->deskripsi,
                'kondisi' => 'kritis',
                'tinggi_cm' => $report->tinggi_cm,
                'diameter_cm' => $report->diameter_cm,
                'tanggal_laporan' => $report->tanggal_laporan->format('Y-m-d'),
            ])
            ->assertRedirect(route('mitra.reports'));

        $this->assertSame('kritis', $report->fresh()->kondisi);
    }

    public function test_mitra_cannot_edit_a_verified_report(): void
    {
        $mitra = $this->user('mitra@alamku.test');
        $report = LaporanLapangan::query()->where('status', 'terverifikasi')->firstOrFail();

        $this->actingAs($mitra)
            ->get(route('mitra.reports.edit', $report))
            ->assertForbidden();
    }

    public function test_mitra_dashboard_shows_donation_notifications_and_fund_details(): void
    {
        $mitra = $this->user('mitra@alamku.test');

        $this->actingAs($mitra)
            ->get(route('mitra.dashboard'))
            ->assertOk()
            ->assertSee('Donasi yang sudah dibayar')
            ->assertSee('Rincian dana proyek')
            ->assertSee('Dana diterima');
    }

    public function test_donatur_sees_qris_code_location_and_weather_disclaimer(): void
    {
        $donatur = $this->user('donatur@alamku.test');
        $project = Proyek::query()->firstOrFail();

        $this->actingAs($donatur)
            ->get(route('program.show', $project))
            ->assertOk()
            ->assertSee('Pindai kode QRIS')
            ->assertSee('QRIS Simulasi')
            ->assertSee('Catatan risiko cuaca')
            ->assertSee('tidak dapat direfund')
            ->assertSee('Alamat');
    }

    public function test_admin_can_upload_a_kelompok_tani_profile_photo(): void
    {
        $admin = $this->user('admin@alamku.test');
        $kelompok = KelompokTani::query()->firstOrFail();

        $response = $this->actingAs($admin)->put(route('kelompok-tani.update', $kelompok), [
            'pengguna_id' => $kelompok->pengguna_id,
            'nama_kelompok' => $kelompok->nama_kelompok,
            'provinsi' => $kelompok->provinsi,
            'kabupaten' => $kelompok->kabupaten,
            'desa' => $kelompok->desa,
            'nomor_legalitas' => $kelompok->nomor_legalitas,
            'terverifikasi' => $kelompok->terverifikasi ? 1 : 0,
            'deskripsi' => $kelompok->deskripsi,
            'foto_profil' => $this->png(),
        ]);

        $response->assertRedirect();

        $path = $kelompok->fresh()->foto_profil;

        $this->assertStringStartsWith('uploads/kelompok-tani/', $path);
        $this->assertFileExists(public_path($path));
    }

    public function test_admin_can_upload_a_geotag_photo_instead_of_a_url(): void
    {
        $admin = $this->user('admin@alamku.test');
        $foto = FotoGeotag::query()->firstOrFail();

        $response = $this->actingAs($admin)->put(route('foto-geotag.update', $foto), [
            'laporan_lapangan_id' => $foto->laporan_lapangan_id,
            'pohon_id' => $foto->pohon_id,
            'latitude' => $foto->latitude,
            'longitude' => $foto->longitude,
            'altitude' => $foto->altitude,
            'captured_at' => $foto->captured_at?->format('Y-m-d\TH:i'),
            'keterangan' => $foto->keterangan,
            'path' => $this->png(),
        ]);

        $response->assertRedirect();

        $path = $foto->fresh()->path;

        $this->assertStringStartsWith('uploads/foto-geotag/', $path);
        $this->assertFileExists(public_path($path));
    }
}
