<?php

namespace App\Http\Controllers;

use App\Models\PesananAdopsi;
use App\Models\Pohon;
use App\Models\Proyek;
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
            'jumlah_bibit' => ['required', 'integer', 'min:1'],
        ], [], [
            'jumlah_bibit' => 'jumlah bibit',
        ]);

        $remaining = max(0, (int) $proyek->kuota_bibit - (int) $proyek->bibit_dipesan);

        if ($proyek->status !== 'aktif' || $remaining < 1) {
            return back()->with('error', 'Program ini sedang tidak menerima adopsi baru.');
        }

        if ((int) $data['jumlah_bibit'] > $remaining) {
            throw ValidationException::withMessages([
                'jumlah_bibit' => 'Sisa kuota program ini hanya '.number_format($remaining, 0, ',', '.').' bibit.',
            ]);
        }

        $order = DB::transaction(function () use ($request, $proyek, $data): PesananAdopsi {
            $jumlahBibit = (int) $data['jumlah_bibit'];
            $hargaPerBibit = (int) $proyek->biaya_per_bibit;

            do {
                $kodePesanan = 'ADP-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
            } while (PesananAdopsi::query()->where('kode_pesanan', $kodePesanan)->exists());

            $order = PesananAdopsi::create([
                'kode_pesanan' => $kodePesanan,
                'donatur_id' => $request->user()->id,
                'proyek_id' => $proyek->id,
                'jumlah_bibit' => $jumlahBibit,
                'harga_per_bibit' => $hargaPerBibit,
                'total_bayar' => $jumlahBibit * $hargaPerBibit,
                'status' => 'menunggu_pembayaran',
                'batas_bayar' => now()->addDay(),
            ]);

            $proyek->increment('bibit_dipesan', $jumlahBibit);
            $proyek->refresh();

            if ((int) $proyek->bibit_dipesan >= (int) $proyek->kuota_bibit) {
                $proyek->status = 'penuh';
                $proyek->save();
            }

            return $order;
        });

        return redirect()
            ->route('akun.adoptions.show', $order->id)
            ->with('success', 'Adopsi berhasil dibuat. Selesaikan pembayaran sebelum batas waktu.');
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
