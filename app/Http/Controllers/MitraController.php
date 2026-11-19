<?php

namespace App\Http\Controllers;

use App\Models\KelompokTani;
use App\Models\LaporanLapangan;
use App\Models\Pohon;
use App\Models\Proyek;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MitraController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $kelompok = $user->kelompokTani;

        $projectQuery = Proyek::query()
            ->when($kelompok, fn ($query) => $query->where('kelompok_tani_id', $kelompok->id), fn ($query) => $query->whereRaw('1 = 0'));

        $projectIds = (clone $projectQuery)->pluck('id')->all();

        $stats = [
            'projects' => count($projectIds),
            'trees' => Pohon::query()->whereIn('proyek_id', $projectIds)->count(),
            'reports' => LaporanLapangan::query()->whereIn('proyek_id', $projectIds)->count(),
            'adoptions' => (int) (clone $projectQuery)->sum('bibit_dipesan'),
        ];

        $projects = (clone $projectQuery)
            ->with(['lokasiHutan', 'jenisPohon'])
            ->latest('id')
            ->take(4)
            ->get();

        $reports = LaporanLapangan::query()
            ->with(['proyek', 'pohon'])
            ->whereIn('proyek_id', $projectIds)
            ->latest('id')
            ->take(5)
            ->get();

        $trees = Pohon::query()
            ->with(['proyek', 'jenisPohon'])
            ->whereIn('proyek_id', $projectIds)
            ->latest('id')
            ->take(4)
            ->get();

        return view('portal.mitra.dashboard', compact('user', 'kelompok', 'stats', 'projects', 'reports', 'trees'));
    }

    public function projects(Request $request): View
    {
        $kelompok = $request->user()->kelompokTani;

        $projects = Proyek::query()
            ->with(['lokasiHutan', 'jenisPohon'])
            ->when($kelompok, fn ($query) => $query->where('kelompok_tani_id', $kelompok->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->latest('id')
            ->paginate(9);

        return view('portal.mitra.projects', compact('kelompok', 'projects'));
    }

    public function trees(Request $request): View
    {
        $kelompok = $request->user()->kelompokTani;
        $projectIds = $kelompok
            ? Proyek::query()->where('kelompok_tani_id', $kelompok->id)->pluck('id')->all()
            : [];

        $trees = Pohon::query()
            ->with(['proyek', 'jenisPohon'])
            ->whereIn('proyek_id', $projectIds)
            ->latest('id')
            ->paginate(12);

        return view('portal.mitra.trees', compact('kelompok', 'trees'));
    }

    public function reports(Request $request): View
    {
        $kelompok = $request->user()->kelompokTani;
        $projectIds = $kelompok
            ? Proyek::query()->where('kelompok_tani_id', $kelompok->id)->pluck('id')->all()
            : [];

        $reports = LaporanLapangan::query()
            ->with(['proyek', 'pohon'])
            ->whereIn('proyek_id', $projectIds)
            ->latest('id')
            ->paginate(10);

        return view('portal.mitra.reports', compact('kelompok', 'reports'));
    }

    public function createReport(Request $request): View
    {
        $kelompok = $request->user()->kelompokTani;
        $projects = $kelompok
            ? Proyek::query()->where('kelompok_tani_id', $kelompok->id)->orderBy('judul')->get()
            : collect();

        $projectIds = $projects->pluck('id')->all();

        $trees = Pohon::query()
            ->whereIn('proyek_id', $projectIds)
            ->orderBy('kode_pohon')
            ->get();

        return view('portal.mitra.report-create', compact('kelompok', 'projects', 'trees'));
    }

    public function storeReport(Request $request): RedirectResponse
    {
        $kelompok = $request->user()->kelompokTani;

        if (! $kelompok) {
            return redirect()
                ->route('mitra.profile')
                ->with('error', 'Lengkapi profil kelompok tani terlebih dahulu sebelum membuat laporan.');
        }

        $projectIds = Proyek::query()
            ->where('kelompok_tani_id', $kelompok->id)
            ->pluck('id')
            ->all();

        $data = $request->validate([
            'proyek_id' => ['required', Rule::in($projectIds)],
            'pohon_id' => [
                'nullable',
                'integer',
                Rule::exists('pohon', 'id')->where(fn ($query) => $query->whereIn('proyek_id', $projectIds)),
            ],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'kondisi' => ['required', Rule::in(['sehat', 'perlu_perawatan', 'kritis'])],
            'tinggi_cm' => ['nullable', 'integer', 'min:0'],
            'diameter_cm' => ['nullable', 'numeric', 'min:0'],
            'tanggal_laporan' => ['required', 'date'],
        ], [], [
            'proyek_id' => 'proyek',
            'pohon_id' => 'pohon',
            'judul' => 'judul laporan',
            'deskripsi' => 'deskripsi',
            'kondisi' => 'kondisi',
            'tinggi_cm' => 'tinggi',
            'diameter_cm' => 'diameter',
            'tanggal_laporan' => 'tanggal laporan',
        ]);

        $data['pengguna_id'] = $request->user()->id;
        $data['status'] = 'draft';

        LaporanLapangan::create($data);

        return redirect()
            ->route('mitra.reports')
            ->with('success', 'Laporan lapangan berhasil dikirim dan menunggu verifikasi admin.');
    }

    public function profile(Request $request): View
    {
        return view('portal.mitra.profile', [
            'user' => $request->user(),
            'kelompok' => $request->user()->kelompokTani,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $kelompok = $user->kelompokTani;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('pengguna', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:255'],
            'nama_kelompok' => ['required', 'string', 'max:255'],
            'provinsi' => ['required', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:255'],
            'desa' => ['required', 'string', 'max:255'],
            'nomor_legalitas' => ['nullable', 'string', 'max:255'],
            'foto_profil' => ['nullable', 'string', 'max:2048'],
            'deskripsi' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [], [
            'name' => 'nama lengkap',
            'email' => 'alamat email',
            'phone' => 'nomor telepon',
            'nama_kelompok' => 'nama kelompok tani',
            'provinsi' => 'provinsi',
            'kabupaten' => 'kabupaten',
            'desa' => 'desa',
            'nomor_legalitas' => 'nomor legalitas',
            'foto_profil' => 'foto profil',
            'deskripsi' => 'deskripsi',
            'password' => 'kata sandi',
        ]);

        DB::transaction(function () use ($user, $kelompok, $data): void {
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'] ?? null;

            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            $user->save();

            $kelompokData = [
                'nama_kelompok' => $data['nama_kelompok'],
                'provinsi' => $data['provinsi'],
                'kabupaten' => $data['kabupaten'],
                'desa' => $data['desa'],
                'nomor_legalitas' => $data['nomor_legalitas'] ?? null,
                'foto_profil' => $data['foto_profil'] ?? null,
                'deskripsi' => $data['deskripsi'] ?? null,
            ];

            if ($kelompok) {
                $kelompok->update($kelompokData);
            } else {
                KelompokTani::create($kelompokData + ['pengguna_id' => $user->id]);
            }
        });

        return back()->with('success', 'Profil kelompok tani berhasil diperbarui.');
    }
}
