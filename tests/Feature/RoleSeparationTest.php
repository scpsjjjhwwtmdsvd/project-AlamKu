<?php

namespace Tests\Feature;

use App\Models\PesananAdopsi;
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

    public function test_login_with_remember_me_succeeds(): void
    {
        $donatur = $this->user('donatur@alamku.test');

        $response = $this->post(route('login.attempt'), [
            'email' => $donatur->email,
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('akun.dashboard'));
        $this->assertAuthenticatedAs($donatur);
        $this->assertNotNull($donatur->fresh()->remember_token);
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
            'metode' => 'qris',
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
            'metode' => 'qris',
            'penyedia' => 'QRIS Simulasi AlamKu',
            'jumlah' => 50000,
            'status' => 'berhasil',
        ]);

        $this->assertSame($before + 2, (int) $project->fresh()->bibit_dipesan);
    }

    public function test_adoption_accepts_m_banking_and_masks_the_account_number(): void
    {
        $donatur = $this->user('donatur@alamku.test');

        $project = Proyek::query()
            ->where('status', 'aktif')
            ->whereColumn('bibit_dipesan', '<', 'kuota_bibit')
            ->firstOrFail();

        $response = $this->actingAs($donatur)->post(route('program.adopt', $project), [
            'paket' => 'starter',
            'metode' => 'virtual_account',
            'bank' => 'BCA',
            'nomor_rekening' => '1234567890',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('payment_success', function (array $success): bool {
            return $success['payment_method'] === 'M-Banking'
                && $success['payment_source'] === 'BCA • ••••••7890';
        });

        $this->assertDatabaseHas('pembayaran', [
            'metode' => 'virtual_account',
            'penyedia' => 'BCA',
            'jumlah' => 50000,
            'status' => 'berhasil',
        ]);

        $payment = $donatur->pesananAdopsi()->latest('id')->firstOrFail()->pembayaran()->firstOrFail();

        $this->assertSame('BCA • ••••••7890', $payment->data_respons_gateway['sumber']);
        $this->assertSame('M-Banking', $payment->data_respons_gateway['channel_label']);
    }

    public function test_adoption_accepts_e_wallet_and_stores_the_masked_phone(): void
    {
        $donatur = $this->user('donatur@alamku.test');

        $project = Proyek::query()
            ->where('status', 'aktif')
            ->whereColumn('bibit_dipesan', '<', 'kuota_bibit')
            ->firstOrFail();

        $response = $this->actingAs($donatur)->post(route('program.adopt', $project), [
            'paket' => 'growth',
            'metode' => 'dompet_digital',
            'dompet' => 'GoPay',
            'nomor_hp' => '081234567890',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pembayaran', [
            'metode' => 'dompet_digital',
            'penyedia' => 'GoPay',
            'jumlah' => 100000,
            'status' => 'berhasil',
        ]);

        $payment = $donatur->pesananAdopsi()->latest('id')->firstOrFail()->pembayaran()->firstOrFail();

        $this->assertSame('GoPay • 0812••••7890', $payment->data_respons_gateway['sumber']);
    }

    public function test_adoption_requires_m_banking_account_details(): void
    {        $donatur = $this->user('donatur@alamku.test');

        $project = Proyek::query()
            ->where('status', 'aktif')
            ->whereColumn('bibit_dipesan', '<', 'kuota_bibit')
            ->firstOrFail();

        $response = $this->actingAs($donatur)->post(route('program.adopt', $project), [
            'paket' => 'starter',
            'metode' => 'virtual_account',
        ]);

        $response->assertSessionHasErrors(['bank', 'nomor_rekening']);

        $this->assertSame(0, PesananAdopsi::query()
            ->where('donatur_id', $donatur->id)
            ->where('proyek_id', $project->id)
            ->where('total_bayar', 50000)
            ->count());
    }

    public function test_adoption_requires_an_e_wallet_phone_number(): void
    {
        $donatur = $this->user('donatur@alamku.test');

        $project = Proyek::query()
            ->where('status', 'aktif')
            ->whereColumn('bibit_dipesan', '<', 'kuota_bibit')
            ->firstOrFail();

        $response = $this->actingAs($donatur)->post(route('program.adopt', $project), [
            'paket' => 'starter',
            'metode' => 'dompet_digital',
            'dompet' => 'GoPay',
        ]);

        $response->assertSessionHasErrors(['nomor_hp']);

        $this->assertSame(0, PesananAdopsi::query()
            ->where('donatur_id', $donatur->id)
            ->where('proyek_id', $project->id)
            ->where('total_bayar', 50000)
            ->count());
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
            'metode' => 'qris',
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
            'metode' => 'qris',
        ]);

        $response->assertSessionHasErrors('paket');

        $this->assertDatabaseMissing('pesanan_adopsi', [
            'donatur_id' => $donatur->id,
            'proyek_id' => $project->id,
            'jumlah_bibit' => 2,
        ]);
    }
}
