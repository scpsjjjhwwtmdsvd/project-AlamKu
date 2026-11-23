<?php

namespace App\Http\Controllers;

use App\Models\PesananAdopsi;
use App\Models\Pohon;
use App\Models\Proyek;
use App\Support\AdoptionPackage;
use App\Support\PaymentMethod;
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
            'metode' => ['required', 'string', Rule::in(array_keys(PaymentMethod::channels()))],
            'bank' => ['nullable', 'string', 'required_if:metode,virtual_account', Rule::in(PaymentMethod::banks())],
            'nomor_rekening' => ['nullable', 'string', 'required_if:metode,virtual_account', 'min:6', 'max:24', 'regex:/^[0-9]+$/'],
            'dompet' => ['nullable', 'string', 'required_if:metode,dompet_digital', Rule::in(PaymentMethod::wallets())],
            'nomor_hp' => ['nullable', 'string', 'required_if:metode,dompet_digital', 'min:9', 'max:15', 'regex:/^0[0-9]+$/'],
        ], [
            'metode.required' => 'Pilih metode pembayaran.',
            'metode.in' => 'Metode pembayaran tidak dikenali.',
            'bank.required_if' => 'Pilih bank untuk pembayaran m-banking.',
            'bank.in' => 'Bank yang dipilih belum didukung.',
            'nomor_rekening.required_if' => 'Masukkan nomor rekening.',
            'nomor_rekening.min' => 'Nomor rekening terlalu pendek.',
            'nomor_rekening.max' => 'Nomor rekening terlalu panjang.',
            'nomor_rekening.regex' => 'Nomor rekening hanya boleh berisi angka.',
            'dompet.required_if' => 'Pilih e-wallet untuk pembayaran.',
            'dompet.in' => 'E-wallet yang dipilih belum didukung.',
            'nomor_hp.required_if' => 'Masukkan nomor HP e-wallet.',
            'nomor_hp.min' => 'Nomor HP terlalu pendek.',
            'nomor_hp.max' => 'Nomor HP terlalu panjang.',
            'nomor_hp.regex' => 'Nomor HP harus diawali 0 dan berisi angka.',
        ], [
            'paket' => 'paket adopsi',
            'metode' => 'metode pembayaran',
            'bank' => 'bank',
            'nomor_rekening' => 'nomor rekening',
            'dompet' => 'e-wallet',
            'nomor_hp' => 'nomor HP',
        ]);

        $package = AdoptionPackage::find($data['paket']);

        if ($package === null) {
            throw ValidationException::withMessages([
                'paket' => 'Paket adopsi tidak dikenali.',
            ]);
        }

        $method = $data['metode'];
        $methodLabel = PaymentMethod::label($method);
        $provider = match ($method) {
            'virtual_account' => $data['bank'],
            'dompet_digital' => $data['dompet'],
            default => 'QRIS Simulasi AlamKu',
        };
        $source = match ($method) {
            'virtual_account' => $data['bank'].' • '.$this->maskDigits($data['nomor_rekening']),
            'dompet_digital' => $data['dompet'].' • '.$this->maskDigits($data['nomor_hp'], 4),
            default => 'QRIS Simulasi AlamKu',
        };

        $remaining = max(0, (int) $proyek->kuota_bibit - (int) $proyek->bibit_dipesan);

        if ($proyek->status !== 'aktif' || $remaining < $package['trees']) {
            throw ValidationException::withMessages([
                'paket' => $remaining < 1
                    ? 'Program ini sedang tidak menerima adopsi baru.'
                    : 'Sisa kuota program ini hanya '.number_format($remaining, 0, ',', '.').' bibit. Pilih paket yang lebih kecil.',
            ]);
        }

        $order = DB::transaction(function () use ($request, $proyek, $package, $method, $methodLabel, $provider, $source): PesananAdopsi {
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
                'metode' => $method,
                'penyedia' => $provider,
                'referensi_penyedia' => 'SIM-'.$order->kode_pesanan,
                'jumlah' => $package['price'],
                'status' => 'berhasil',
                'dibayar_pada' => now(),
                'data_respons_gateway' => [
                    'mode' => 'demo',
                    'channel' => $method,
                    'channel_label' => $methodLabel,
                    'sumber' => $source,
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
                'payment_method' => $methodLabel,
                'payment_source' => $source,
                'demo' => true,
            ]);
    }

    /**
     * Sembunyikan sebagian digit agar nomor rekening/HP tidak tampil utuh.
     */
    private function maskDigits(string $value, int $prefix = 0): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        $length = strlen($digits);

        if ($length === 0) {
            return '';
        }

        $suffix = min(4, $length);
        $prefix = min($prefix, max(0, $length - $suffix));
        $hidden = max(0, $length - $prefix - $suffix);

        return substr($digits, 0, $prefix)
            .str_repeat('•', $hidden)
            .substr($digits, $length - $suffix);
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
