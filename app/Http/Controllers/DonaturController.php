<?php

namespace App\Http\Controllers;

use App\Models\PesananAdopsi;
use App\Models\Pohon;
use App\Models\Proyek;
use App\Support\AdoptionPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DonaturController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $orders = PesananAdopsi::query()->where('donatur_id', $user->id);

        $stats = [
            'adoptions' => (clone $orders)->count(),
            'trees' => Pohon::query()->where('donatur_id', $user->id)->count(),
            'contribution' => (int) (clone $orders)->sum('total_bayar'),
            'active' => (clone $orders)->whereIn('status', ['menunggu_pembayaran', 'terkonfirmasi'])->count(),
        ];

        $latestOrders = (clone $orders)
            ->with('proyek')
            ->latest('id')
            ->take(4)
            ->get();

        $trees = Pohon::query()
            ->with(['proyek', 'jenisPohon'])
            ->where('donatur_id', $user->id)
            ->latest('id')
            ->take(4)
            ->get();

        $recommended = Proyek::query()
            ->with(['lokasiHutan', 'jenisPohon'])
            ->where('status', 'aktif')
            ->whereColumn('bibit_dipesan', '<', 'kuota_bibit')
            ->latest('id')
            ->take(3)
            ->get();

        return view('portal.donatur.dashboard', compact('user', 'stats', 'latestOrders', 'trees', 'recommended'));
    }

    public function adoptions(Request $request): View
    {
        $user = $request->user();

        $orders = PesananAdopsi::query()
            ->with(['proyek.lokasiHutan'])
            ->where('donatur_id', $user->id)
            ->latest('id')
            ->paginate(10);

        $summary = [
            'total' => PesananAdopsi::query()->where('donatur_id', $user->id)->count(),
            'trees' => (int) PesananAdopsi::query()->where('donatur_id', $user->id)->where('status', 'terkonfirmasi')->sum('jumlah_bibit'),
            'paid' => (int) PesananAdopsi::query()->where('donatur_id', $user->id)->where('status', 'terkonfirmasi')->sum('total_bayar'),
        ];

        return view('portal.donatur.adoptions', compact('user', 'orders', 'summary'));
    }

    public function showAdoption(Request $request, int $id): View
    {
        $user = $request->user();

        $order = PesananAdopsi::query()
            ->with([
                'proyek.lokasiHutan',
                'proyek.kelompokTani',
                'proyek.jenisPohon',
                'pembayaran',
            ])
            ->where('donatur_id', $user->id)
            ->findOrFail($id);

        $trees = Pohon::query()
            ->with('jenisPohon')
            ->where('pesanan_id', $order->id)
            ->orderBy('kode_pohon')
            ->get();

        return view('portal.donatur.adoption-show', compact('user', 'order', 'trees'));
    }

    public function storeAdoption(Request $request, Proyek $proyek): RedirectResponse
    {
        $data = $request->validate([
            'paket' => ['required', 'string', Rule::in(array_keys(AdoptionPackage::all()))],
        ], [], [
            'paket' => 'paket adopsi',
        ]);

        $package = AdoptionPackage::find($data['paket']);

        if ($package === null) {
            throw ValidationException::withMessages([
                'paket' => 'Paket adopsi tidak dikenali.',
            ]);
        }

        $remaining = max(0, (int) $proyek->kuota_bibit - (int) $proyek->bibit_dipesan);

        if ($proyek->status !== 'aktif' || $remaining < $package['trees']) {
            throw ValidationException::withMessages([
                'paket' => $remaining < 1
                    ? 'Program ini sedang tidak menerima adopsi baru.'
                    : 'Sisa kuota program ini hanya '.number_format($remaining, 0, ',', '.').' bibit. Pilih paket yang lebih kecil.',
            ]);
        }

        $order = DB::transaction(function () use ($request, $proyek, $package): PesananAdopsi {
            do {
                $kodePesanan = 'ADP-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
            } while (PesananAdopsi::query()->where('kode_pesanan', $kodePesanan)->exists());

            $order = PesananAdopsi::create([
                'kode_pesanan' => $kodePesanan,
                'donatur_id' => $request->user()->id,
                'proyek_id' => $proyek->id,
                'jumlah_bibit' => $package['trees'],
                'harga_per_bibit' => intdiv($package['price'], $package['trees']),
                'total_bayar' => $package['price'],
                'status' => 'terkonfirmasi',
                'batas_bayar' => now()->addDay(),
                'dikonfirmasi_pada' => now(),
            ]);

            $order->pembayaran()->create([
                'metode' => 'qris',
                'penyedia' => 'Simulasi AlamKu',
                'referensi_penyedia' => 'SIM-'.$order->kode_pesanan,
                'jumlah' => $package['price'],
                'status' => 'berhasil',
                'dibayar_pada' => now(),
                'data_respons_gateway' => [
                    'mode' => 'demo',
                    'paket' => $package['key'],
                    'jumlah_bibit' => $package['trees'],
                    'alokasi' => AdoptionPackage::allocation($package['price']),
                ],
            ]);

            $proyek->increment('bibit_dipesan', $package['trees']);
            $proyek->refresh();

            if ((int) $proyek->bibit_dipesan >= (int) $proyek->kuota_bibit) {
                $proyek->status = 'penuh';
                $proyek->save();
            }

            return $order;
        });

        return redirect()
            ->route('akun.adoptions.show', $order->id)
            ->with('payment_success', [
                'kode_pesanan' => $order->kode_pesanan,
                'package' => $package['name'],
                'trees' => $package['trees'],
                'total' => $package['price'],
                'allocation' => AdoptionPackage::allocation($package['price']),
                'demo' => true,
            ]);
    }

    public function profile(Request $request): View
    {
        return view('portal.donatur.profile', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('pengguna', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [], [
            'name' => 'nama lengkap',
            'email' => 'alamat email',
            'phone' => 'nomor telepon',
            'password' => 'kata sandi',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return back()->with('success', 'Profil donatur berhasil diperbarui.');
    }
}
