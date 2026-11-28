<?php

namespace Tests\Feature;

use App\Models\JenisPohon;
use App\Models\KelompokTani;
use App\Models\LokasiHutan;
use App\Models\Proyek;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraProjectCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoDataSeeder::class);
    }

    private function mitra(): User
    {
        return User::query()->where('email', 'mitra@alamku.test')->firstOrFail();
    }

    private function mitraTanpaLokasi(): User
    {
        $user = User::create([
            'name' => 'Mitra Baru',
            'email' => 'mitra.baru@alamku.test',
            'password' => 'password',
            'role' => 'mitra',
        ]);

        KelompokTani::create([
            'pengguna_id' => $user->id,
            'nama_kelompok' => 'Kelompok Baru',
            'provinsi' => 'Bali',
            'kabupaten' => 'Gianyar',
            'desa' => 'Singakerta',
            'terverifikasi' => false,
        ]);

        return $user->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(LokasiHutan $location, JenisPohon $species, array $overrides = []): array
    {
        return array_merge([
            'judul' => 'Proyek Uji Mitra',
            'lokasi_hutan_id' => $location->id,
            'jenis_pohon_id' => $species->id,
            'kode_prefix' => 'UJI-MTR',
            'deskripsi' => 'Deskripsi proyek uji.',
            'target_ekologi' => 'Target ekologi uji.',
            'biaya_per_bibit' => 25000,
            'kuota_bibit' => 100,
            'tanggal_mulai_tanam' => '2026-12-01',
            'tanggal_selesai_tanam' => '2026-12-31',
        ], $overrides);
    }

    public function test_projects_page_shows_create_project_button(): void
    {
        $response = $this->actingAs($this->mitra())->get(route('mitra.projects'));

        $response->assertOk();
        $response->assertSee('Buat proyek');
        $response->assertSee(route('mitra.projects.create'), false);
    }

    public function test_mitra_can_open_the_create_project_page(): void
    {
        $response = $this->actingAs($this->mitra())->get(route('mitra.projects.create'));

        $response->assertOk();
        $response->assertSee('Ajukan proyek baru.');
        $response->assertSee('Buat Proyek');
    }

    public function test_mitra_without_location_still_sees_the_project_form(): void
    {
        $mitra = $this->mitraTanpaLokasi();

        $response = $this->actingAs($mitra)->get(route('mitra.projects.create'));

        $response->assertOk();
        $response->assertSee('name="judul"', false);
        $response->assertSee('name="nama_area_konservasi"', false);
        $response->assertSee('name="new_location"', false);
        $response->assertSee('Lengkapi data lokasi baru');
    }

    public function test_mitra_without_location_can_create_project_with_new_location(): void
    {
        $mitra = $this->mitraTanpaLokasi();
        $kelompok = $mitra->kelompokTani;
        $species = JenisPohon::query()->firstOrFail();

        $response = $this->actingAs($mitra)->post(route('mitra.projects.store'), [
            'judul' => 'Proyek Tanpa Lokasi',
            'jenis_pohon_id' => $species->id,
            'kode_prefix' => 'UJI-NEW',
            'deskripsi' => 'Deskripsi proyek.',
            'target_ekologi' => 'Target ekologi.',
            'biaya_per_bibit' => 25000,
            'kuota_bibit' => 50,
            'tanggal_mulai_tanam' => '2026-12-01',
            'tanggal_selesai_tanam' => '2026-12-31',
            'new_location' => '1',
            'nama_area_konservasi' => 'Hutan Uji Baru',
            'provinsi' => 'Bali',
            'kabupaten' => 'Gianyar',
            'latitude' => '-8.5069000',
            'longitude' => '115.2625000',
            'luas_hektar' => '2.5',
            'deskripsi_lokasi' => 'Lokasi uji.',
        ]);

        $project = Proyek::query()->where('judul', 'Proyek Tanpa Lokasi')->firstOrFail();
        $location = LokasiHutan::query()->where('nama_area_konservasi', 'Hutan Uji Baru')->firstOrFail();

        $response->assertRedirect(route('mitra.projects.show', $project));

        $this->assertSame($kelompok->id, $location->kelompok_tani_id);
        $this->assertSame($location->id, $project->lokasi_hutan_id);
        $this->assertDatabaseHas('proyek', [
            'id' => $project->id,
            'kelompok_tani_id' => $kelompok->id,
            'status' => 'draf',
        ]);
    }

    public function test_mitra_can_submit_a_new_project_as_draft(): void
    {
        $mitra = $this->mitra();
        $kelompok = $mitra->kelompokTani;
        $location = LokasiHutan::query()->where('kelompok_tani_id', $kelompok->id)->firstOrFail();
        $species = JenisPohon::query()->firstOrFail();

        $response = $this->actingAs($mitra)->post(
            route('mitra.projects.store'),
            $this->payload($location, $species)
        );

        $project = Proyek::query()->where('judul', 'Proyek Uji Mitra')->firstOrFail();

        $response->assertRedirect(route('mitra.projects.show', $project));

        $this->assertDatabaseHas('proyek', [
            'id' => $project->id,
            'kelompok_tani_id' => $kelompok->id,
            'status' => 'draf',
            'bibit_dipesan' => 0,
            'kode_prefix' => 'UJI-MTR',
        ]);
    }

    public function test_mitra_cannot_use_a_location_from_another_kelompok(): void
    {
        $mitra = $this->mitra();

        $otherUser = User::create([
            'name' => 'Mitra Lain',
            'email' => 'mitra.lain@alamku.test',
            'password' => 'password',
            'role' => 'mitra',
        ]);

        $otherKelompok = KelompokTani::create([
            'pengguna_id' => $otherUser->id,
            'nama_kelompok' => 'Kelompok Lain',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Bandung',
            'desa' => 'Lembang',
            'terverifikasi' => false,
        ]);

        $otherLocation = LokasiHutan::create([
            'kelompok_tani_id' => $otherKelompok->id,
            'nama_area_konservasi' => 'Lokasi Kelompok Lain',
            'provinsi' => 'Jawa Barat',
            'kabupaten' => 'Bandung',
            'latitude' => -6.8000000,
            'longitude' => 107.6000000,
        ]);

        $response = $this->actingAs($mitra)->post(
            route('mitra.projects.store'),
            $this->payload($otherLocation, JenisPohon::query()->firstOrFail(), ['kode_prefix' => 'UJI-LAIN'])
        );

        $response->assertSessionHasErrors('lokasi_hutan_id');
        $this->assertDatabaseMissing('proyek', ['judul' => 'Proyek Uji Mitra']);
    }
}
