<?php

namespace App\Http\Controllers;

use App\Models\PesananAdopsi;
use App\Models\Pohon;
use App\Models\Proyek;
use App\Support\AdoptionPackage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $projects = Proyek::query()
            ->with(['lokasiHutan', 'kelompokTani', 'jenisPohon'])
            ->whereIn('status', ['aktif', 'penuh'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('judul', 'like', '%'.$search.'%')
                        ->orWhere('deskripsi', 'like', '%'.$search.'%')
                        ->orWhere('target_ekologi', 'like', '%'.$search.'%');
                });
            })
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        $stats = [
            'projects' => Proyek::query()->whereIn('status', ['aktif', 'penuh'])->count(),
            'kuota' => (int) Proyek::query()->whereIn('status', ['aktif', 'penuh'])->sum('kuota_bibit'),
            'adopsi' => (int) Proyek::query()->whereIn('status', ['aktif', 'penuh'])->sum('bibit_dipesan'),
            'donatur' => PesananAdopsi::query()->where('status', 'terkonfirmasi')->count(),
        ];

        return view('program.index', compact('projects', 'stats', 'search'));
    }

    public function show(Request $request, Proyek $proyek): View
    {
        $proyek->load(['lokasiHutan', 'kelompokTani', 'jenisPohon']);

        $remaining = max(0, (int) $proyek->kuota_bibit - (int) $proyek->bibit_dipesan);
        $percent = (int) $proyek->kuota_bibit > 0
            ? min(100, (int) round(($proyek->bibit_dipesan / $proyek->kuota_bibit) * 100))
            : 0;

        $trees = Pohon::query()->where('proyek_id', $proyek->id)->count();
        $reports = $proyek->laporanLapangan()->count();

        $myOrder = null;

        if ($request->user()?->role === 'donatur') {
            $myOrder = PesananAdopsi::query()
                ->where('donatur_id', $request->user()->id)
                ->where('proyek_id', $proyek->id)
                ->latest('id')
                ->first();
        }

        $related = Proyek::query()
            ->with(['lokasiHutan', 'jenisPohon'])
            ->whereIn('status', ['aktif', 'penuh'])
            ->whereKeyNot($proyek->id)
            ->latest('id')
            ->take(3)
            ->get();

        $packages = AdoptionPackage::all();

        foreach ($packages as $key => $package) {
            $packages[$key]['available'] = $package['trees'] <= $remaining;
        }

        $availablePackages = array_filter($packages, static fn (array $package): bool => $package['available']);
        $canAdopt = $proyek->status === 'aktif' && $availablePackages !== [];
        $defaultPackage = $availablePackages === []
            ? $packages['starter']
            : reset($availablePackages);

        $selectedPackage = (string) old('paket', $defaultPackage['key']);

        if (! isset($packages[$selectedPackage]) || ! $packages[$selectedPackage]['available']) {
            $selectedPackage = $defaultPackage['key'];
        }

        $adoptData = [
            'packages' => array_values($packages),
            'allocations' => AdoptionPackage::allocationRules(),
            'default' => $defaultPackage['key'],
        ];

        return view('program.show', compact(
            'proyek',
            'remaining',
            'percent',
            'trees',
            'reports',
            'myOrder',
            'related',
            'packages',
            'canAdopt',
            'selectedPackage',
            'adoptData',
        ));
    }
}
