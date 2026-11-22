<?php

namespace Tests\Feature;

use App\Models\Proyek;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSeparationTest extends TestCase
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

    public function test_public_pages_are_accessible_to_guests(): void
    {
        $this->get('/')->assertOk();
        $this->get('/program')->assertOk();
        $this->get('/masuk')->assertOk();
        $this->get('/daftar')->assertOk();
    }

    public function test_guests_are_redirected_to_login_from_protected_areas(): void
    {
        $this->get('/akun')->assertRedirect(route('login'));
        $this->get('/mitra')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_donatur_portal_only_accepts_donatur(): void
    {
        $donatur = $this->user('donatur@alamku.test');

        $this->actingAs($donatur)->get('/akun')->assertOk();
        $this->actingAs($donatur)->get('/akun/adopsi')->assertOk();
        $this->actingAs($donatur)->get('/akun/profil')->assertOk();

        $this->actingAs($donatur)->get('/mitra')->assertRedirect(route('akun.dashboard'));
        $this->actingAs($donatur)->get('/admin')->assertRedirect(route('akun.dashboard'));
    }

    public function test_mitra_portal_only_accepts_mitra(): void
    {
        $mitra = $this->user('mitra@alamku.test');

        $this->actingAs($mitra)->get('/mitra')->assertOk();
        $this->actingAs($mitra)->get('/mitra/proyek')->assertOk();
        $this->actingAs($mitra)->get('/mitra/pohon')->assertOk();
        $this->actingAs($mitra)->get('/mitra/laporan')->assertOk();
        $this->actingAs($mitra)->get('/mitra/laporan/baru')->assertOk();
        $this->actingAs($mitra)->get('/mitra/profil')->assertOk();

        $this->actingAs($mitra)->get('/akun')->assertRedirect(route('mitra.dashboard'));
        $this->actingAs($mitra)->get('/admin')->assertRedirect(route('mitra.dashboard'));
    }

    public function test_admin_portal_only_accepts_admin_and_all_modules_render(): void
    {
        $admin = $this->user('admin@alamku.test');

        $this->actingAs($admin)->get('/admin')->assertOk();

        foreach ([
            'proyek',
            'pesanan-adopsi',
            'pohon',
            'laporan-lapangan',
            'kelompok-tani',
            'lokasi-hutan',
            'jenis-pohon',
            'pembayaran',
            'alokasi-dana',
            'foto-geotag',
            'log-perubahan',
        ] as $module) {
            $this->actingAs($admin)->get('/admin/'.$module)->assertOk();
        }

        $this->actingAs($admin)->get('/akun')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get('/mitra')->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_open_a_project_detail_page(): void
    {
        $admin = $this->user('admin@alamku.test');
        $project = Proyek::query()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('proyek.show', $project))
            ->assertOk();
    }

    public function test_donatur_can_adopt_package_and_reserve_quota(): void
    {
        $donatur = $this->user('donatur@alamku.test');

        $project = Proyek::query()
            ->where('status', 'aktif')
            ->whereColumn('bibit_dipesan', '<', 'kuota_bibit')
            ->firstOrFail();

        $before = (int) $project->bibit_dipesan;

        $response = $this->actingAs($donatur)->post(route('program.adopt', $project), [
            'paket' => 'starter',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('payment_success');

        $this->assertDatabaseHas('pesanan_adopsi', [
            'donatur_id' => $donatur->id,
            'proyek_id' => $project->id,
            'jumlah_bibit' => 2,
            'harga_per_bibit' => 25000,
            'total_bayar' => 50000,
            'status' => 'terkonfirmasi',
        ]);

        $this->assertDatabaseHas('pembayaran', [
            'jumlah' => 50000,
            'status' => 'berhasil',
        ]);

        $this->assertSame($before + 2, (int) $project->fresh()->bibit_dipesan);
    }

    public function test_adoption_requires_a_known_package(): void
    {
        $donatur = $this->user('donatur@alamku.test');

        $project = Proyek::query()
            ->where('status', 'aktif')
            ->whereColumn('bibit_dipesan', '<', 'kuota_bibit')
            ->firstOrFail();

        $response = $this->actingAs($donatur)->post(route('program.adopt', $project), [
            'paket' => 'custom',
        ]);

        $response->assertSessionHasErrors('paket');
    }

    public function test_adoption_rejects_package_above_remaining_quota(): void
    {
        $donatur = $this->user('donatur@alamku.test');

        $project = Proyek::query()
            ->where('status', 'aktif')
            ->whereColumn('bibit_dipesan', '<', 'kuota_bibit')
            ->firstOrFail();

        $project->forceFill(['bibit_dipesan' => $project->kuota_bibit - 1])->save();

        $response = $this->actingAs($donatur)->post(route('program.adopt', $project), [
            'paket' => 'starter',
        ]);

        $response->assertSessionHasErrors('paket');

        $this->assertDatabaseMissing('pesanan_adopsi', [
            'donatur_id' => $donatur->id,
            'proyek_id' => $project->id,
            'jumlah_bibit' => 2,
        ]);
    }
}
