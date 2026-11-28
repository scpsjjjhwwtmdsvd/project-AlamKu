<?php

namespace App\Http\Controllers;

use App\Models\AlokasiDana;
use App\Models\JenisPohon;
use App\Models\KelompokTani;
use App\Models\LaporanLapangan;
use App\Models\LokasiHutan;
use App\Models\PesananAdopsi;
use App\Models\Pohon;
use App\Models\Proyek;
use App\Models\User;
use App\Support\AdoptionPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

        $donations = PesananAdopsi::query()
            ->with(['donatur', 'proyek'])
            ->whereIn('proyek_id', $projectIds)
            ->where('status', 'terkonfirmasi')
            ->latest('dikonfirmasi_pada')
            ->take(5)
            ->get();

        $donationSummary = [
            'count' => PesananAdopsi::query()
                ->whereIn('proyek_id', $projectIds)
                ->where('status', 'terkonfirmasi')
                ->count(),
            'recent' => PesananAdopsi::query()
                ->whereIn('proyek_id', $projectIds)
                ->where('status', 'terkonfirmasi')
                ->where('dikonfirmasi_pada', '>=', now()->subDays(7))
                ->count(),
            'trees' => (int) PesananAdopsi::query()
                ->whereIn('proyek_id', $projectIds)
                ->where('status', 'terkonfirmasi')
                ->sum('jumlah_bibit'),
            'total' => (int) PesananAdopsi::query()
                ->whereIn('proyek_id', $projectIds)
                ->where('status', 'terkonfirmasi')
                ->sum('total_bayar'),
        ];

        $funds = Proyek::query()
            ->with(['alokasiDana'])
            ->whereIn('id', $projectIds)
            ->orderBy('judul')
            ->get()
            ->map(function (Proyek $project): array {
                $fund = $this->fundBreakdown($project);

                return [
                    'project' => $project,
                    'planned' => $fund['planned'],
                    'realized' => $fund['realized'],
                    'received' => $fund['received'],
                    'derived' => $fund['derived'],
                ];
            });

        return view('portal.mitra.dashboard', compact(
            'user',
            'kelompok',
            'stats',
            'projects',
            'reports',
            'trees',
            'donations',
            'donationSummary',
            'funds',
        ));
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

    public function createProject(Request $request): View|RedirectResponse
    {
        $kelompok = $request->user()->kelompokTani;

        if (! $kelompok) {
            return redirect()
                ->route('mitra.profile')
                ->with('error', 'Lengkapi profil kelompok tani terlebih dahulu sebelum membuat proyek.');
        }

        $locations = LokasiHutan::query()
            ->where('kelompok_tani_id', $kelompok->id)
            ->orderBy('nama_area_konservasi')
            ->get();

        $species = JenisPohon::query()
            ->orderBy('nama_lokal')
            ->get();

        return view('portal.mitra.project-create', compact('kelompok', 'locations', 'species'));
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $kelompok = $request->user()->kelompokTani;

        if (! $kelompok) {
            return redirect()
                ->route('mitra.profile')
                ->with('error', 'Lengkapi profil kelompok tani terlebih dahulu sebelum membuat proyek.');
        }

        if ($request->filled('kode_prefix')) {
            $request->merge([
                'kode_prefix' => Str::upper(trim((string) $request->input('kode_prefix'))),
            ]);
        }

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'lokasi_hutan_id' => [
                'required',
                'integer',
                Rule::exists('lokasi_hutan', 'id')->where('kelompok_tani_id', $kelompok->id),
            ],
            'jenis_pohon_id' => ['required', 'integer', 'exists:jenis_pohon,id'],
            'kode_prefix' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z0-9-]+$/', Rule::unique('proyek', 'kode_prefix')],
            'deskripsi' => ['required', 'string'],
            'target_ekologi' => ['required', 'string'],
            'biaya_per_bibit' => ['required', 'integer', 'min:0'],
            'kuota_bibit' => ['required', 'integer', 'min:1'],
            'tanggal_mulai_tanam' => ['required', 'date'],
            'tanggal_selesai_tanam' => ['required', 'date', 'after_or_equal:tanggal_mulai_tanam'],
        ], [
            'lokasi_hutan_id.exists' => 'Lokasi hutan harus milik kelompok tani Anda.',
            'kode_prefix.regex' => 'Kode prefix hanya boleh berisi huruf kapital, angka, dan strip.',
            'kode_prefix.unique' => 'Kode prefix sudah dipakai proyek lain.',
            'tanggal_selesai_tanam.after_or_equal' => 'Tanggal selesai tanam tidak boleh sebelum tanggal mulai tanam.',
        ], [
            'judul' => 'judul proyek',
            'lokasi_hutan_id' => 'lokasi hutan',
            'jenis_pohon_id' => 'jenis pohon',
            'kode_prefix' => 'kode prefix',
            'deskripsi' => 'deskripsi',
            'target_ekologi' => 'target ekologi',
            'biaya_per_bibit' => 'biaya per bibit',
            'kuota_bibit' => 'kuota bibit',
            'tanggal_mulai_tanam' => 'tanggal mulai tanam',
            'tanggal_selesai_tanam' => 'tanggal selesai tanam',
        ]);

        $project = Proyek::create([
            'lokasi_hutan_id' => $data['lokasi_hutan_id'],
            'kelompok_tani_id' => $kelompok->id,
            'jenis_pohon_id' => $data['jenis_pohon_id'],
            'judul' => $data['judul'],
            'kode_prefix' => $data['kode_prefix'] ?? null,
            'deskripsi' => $data['deskripsi'],
            'target_ekologi' => $data['target_ekologi'],
            'biaya_per_bibit' => $data['biaya_per_bibit'],
            'kuota_bibit' => $data['kuota_bibit'],
            'bibit_dipesan' => 0,
            'tanggal_mulai_tanam' => $data['tanggal_mulai_tanam'],
            'tanggal_selesai_tanam' => $data['tanggal_selesai_tanam'],
            'status' => 'draf',
        ]);

        return redirect()
            ->route('mitra.projects.show', $project)
            ->with('success', 'Proyek berhasil diajukan dan menunggu verifikasi admin.');
    }

    /**
     * Rincian dana proyek: pakai alokasi manual admin jika ada, atau turunkan
     * dari total donasi terkonfirmasi agar proyek tanpa alokasi tidak Rp 0.
     *
     * @return array{planned: int, realized: int, received: int, allocations: array<int, array{kategori: string, rencana: int, realisasi: int, keterangan: string|null}>, derived: bool}
     */
    private function fundBreakdown(Proyek $project): array
    {
        $received = (int) PesananAdopsi::query()
            ->where('proyek_id', $project->id)
            ->where('status', 'terkonfirmasi')
            ->sum('total_bayar');

        $manual = $project->alokasiDana;

        if ($manual->isNotEmpty()) {
            return [
                'planned' => (int) $manual->sum('rencana'),
                'realized' => (int) $manual->sum('realisasi'),
                'received' => $received,
                'allocations' => $manual->map(static fn ($row): array => [
                    'kategori' => $row->kategori,
                    'rencana' => (int) $row->rencana,
                    'realisasi' => (int) $row->realisasi,
                    'keterangan' => $row->keterangan,
                ])->values()->all(),
                'derived' => false,
            ];
        }

        if ($received > 0) {
            return [
                'planned' => $received,
                'realized' => 0,
                'received' => $received,
                'allocations' => array_map(static fn (array $item): array => [
                    'kategori' => $item['label'],
                    'rencana' => (int) $item['amount'],
                    'realisasi' => 0,
                    'keterangan' => 'Alokasi otomatis dari donasi terkonfirmasi.',
                ], AdoptionPackage::allocation($received)),
                'derived' => true,
            ];
        }

        return [
            'planned' => 0,
            'realized' => 0,
            'received' => 0,
            'allocations' => [],
            'derived' => false,
        ];
    }

    public function showProject(Request $request, Proyek $proyek): View
    {
        $kelompok = $request->user()->kelompokTani;

        abort_unless(
            $kelompok && (int) $proyek->kelompok_tani_id === (int) $kelompok->id,
            403
        );

        $proyek->load(['lokasiHutan', 'kelompokTani', 'jenisPohon', 'alokasiDana']);

        $donations = PesananAdopsi::query()
            ->with('donatur')
            ->where('proyek_id', $proyek->id)
            ->where('status', 'terkonfirmasi')
            ->latest('dikonfirmasi_pada')
            ->get();

        $trees = Pohon::query()
            ->with('jenisPohon')
            ->where('proyek_id', $proyek->id)
            ->orderBy('kode_pohon')
            ->get();

        $reports = LaporanLapangan::query()
            ->where('proyek_id', $proyek->id)
            ->latest('tanggal_laporan')
            ->take(6)
            ->get();

        $fund = $this->fundBreakdown($proyek);

        return view('portal.mitra.project-show', compact('kelompok', 'proyek', 'donations', 'trees', 'reports', 'fund'));
    }

    public function updateProjectStatus(Request $request, Proyek $proyek): RedirectResponse
    {
        $kelompok = $request->user()->kelompokTani;

        abort_unless(
            $kelompok && (int) $proyek->kelompok_tani_id === (int) $kelompok->id,
            403
        );

        if ($proyek->status === 'draf') {
            return back()->with('error', 'Proyek masih draf dan menunggu verifikasi admin.');
        }

        if (! in_array($proyek->status, ['aktif', 'penuh', 'selesai'], true)) {
            return back()->with('error', 'Status proyek ini hanya dapat diubah oleh admin.');
        }

        $allowedStatuses = $proyek->status === 'penuh'
            ? ['selesai']
            : ['aktif', 'selesai'];

        $data = $request->validate([
            'status' => ['required', Rule::in($allowedStatuses)],
        ], [], [
            'status' => 'status proyek',
        ]);

        $proyek->status = $data['status'];
        $proyek->save();

        return back()->with('success', 'Status proses proyek diperbarui menjadi '.$data['status'].'.');
    }

    public function updateTreeStatus(Request $request, Pohon $pohon): RedirectResponse
    {
        $kelompok = $request->user()->kelompokTani;

        $ownsProject = $kelompok && Proyek::query()
            ->whereKey($pohon->proyek_id)
            ->where('kelompok_tani_id', $kelompok->id)
            ->exists();

        abort_unless($ownsProject, 403);

        $data = $request->validate([
            'status_kesehatan' => ['required', Rule::in(['belum_ditanam', 'tumbuh_baik', 'perlu_penyulaman', 'mati'])],
        ], [], [
            'status_kesehatan' => 'status kesehatan pohon',
        ]);

        $pohon->status_kesehatan = $data['status_kesehatan'];
        $pohon->timestamps = false;
        $pohon->save();

        return back()->with('success', 'Status pohon '.$pohon->kode_pohon.' diperbarui.');
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

        $recipient = User::query()
            ->where('role', 'admin')
            ->orderBy('id')
            ->first();

        return view('portal.mitra.report-create', compact('kelompok', 'projects', 'trees', 'recipient'));
    }

    public function editReport(Request $request, LaporanLapangan $laporan): View
    {
        $kelompok = $request->user()->kelompokTani;

        abort_unless($kelompok && (int) $laporan->proyek?->kelompok_tani_id === (int) $kelompok->id, 403);
        abort_unless($laporan->status === 'draft', 403);

        $projects = Proyek::query()
            ->where('kelompok_tani_id', $kelompok->id)
            ->orderBy('judul')
            ->get();

        $projectIds = $projects->pluck('id')->all();

        $trees = Pohon::query()
            ->whereIn('proyek_id', $projectIds)
            ->orderBy('kode_pohon')
            ->get();

        $recipient = User::query()
            ->where('role', 'admin')
            ->orderBy('id')
            ->first();

        return view('portal.mitra.report-edit', compact('kelompok', 'projects', 'trees', 'laporan', 'recipient'));
    }

    public function updateReport(Request $request, LaporanLapangan $laporan): RedirectResponse
    {
        $kelompok = $request->user()->kelompokTani;

        abort_unless($kelompok && (int) $laporan->proyek?->kelompok_tani_id === (int) $kelompok->id, 403);
        abort_unless($laporan->status === 'draft', 403);

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

        $laporan->update($data);

        return redirect()
            ->route('mitra.reports')
            ->with('success', 'Laporan "'.$laporan->judul.'" berhasil diperbarui.');
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
            ->with('success', 'Laporan lapangan berhasil dikirim ke admin AlamKu dan menunggu verifikasi.');
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
            'foto_profil' => ['nullable', 'image', 'max:4096'],
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

        DB::transaction(function () use ($user, $kelompok, $data, $request): void {
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'] ?? null;

            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            $user->save();

            $fotoProfil = $kelompok?->foto_profil;

            if ($request->hasFile('foto_profil')) {
                $directory = public_path('uploads/mitra');
                File::ensureDirectoryExists($directory);

                $file = $request->file('foto_profil');
                $filename = Str::random(16).'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $file->move($directory, $filename);

                $fotoProfil = 'uploads/mitra/'.$filename;
            }

            $kelompokData = [
                'nama_kelompok' => $data['nama_kelompok'],
                'provinsi' => $data['provinsi'],
                'kabupaten' => $data['kabupaten'],
                'desa' => $data['desa'],
                'nomor_legalitas' => $data['nomor_legalitas'] ?? null,
                'foto_profil' => $fotoProfil,
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
