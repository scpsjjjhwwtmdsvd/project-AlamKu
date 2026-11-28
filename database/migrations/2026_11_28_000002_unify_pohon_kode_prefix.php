<?php

use App\Models\Pohon;
use App\Models\Proyek;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Satukan penamaan pohon lama (mis. MAHONI-1-005) ke prefix proyek
     * (mis. CKL-MHN-004) agar pengguna tidak melihat dua format kode.
     */
    public function up(): void
    {
        Proyek::query()
            ->with(['lokasiHutan', 'jenisPohon'])
            ->each(function (Proyek $proyek): void {
                // Pertahankan prefix yang sudah dipakai data lama (mis. CKL-KLD),
                // supaya kode yang sudah dilihat pengguna tidak berubah nama.
                $prefix = $this->detectExistingPrefix($proyek->id)
                    ?? Pohon::prefixForProject($proyek);

                if ($proyek->kode_prefix !== $prefix) {
                    $proyek->forceFill(['kode_prefix' => $prefix])->save();
                }

                $this->renameTrees($proyek->id, $prefix);
            });
    }

    private function detectExistingPrefix(int $proyekId): ?string
    {
        $codes = DB::table('pohon')
            ->where('proyek_id', $proyekId)
            ->pluck('kode_pohon');

        $counts = [];

        foreach ($codes as $code) {
            if (preg_match('/^([A-Z]+-[A-Z]+)-\d+$/', $code, $matches)) {
                $counts[$matches[1]] = ($counts[$matches[1]] ?? 0) + 1;
            }
        }

        if ($counts === []) {
            return null;
        }

        arsort($counts);

        return array_key_first($counts);
    }

    private function renameTrees(int $proyekId, string $prefix): void
    {
        $trees = DB::table('pohon')
            ->where('proyek_id', $proyekId)
            ->orderBy('id')
            ->get(['id', 'kode_pohon']);

        $pattern = '/^'.preg_quote($prefix, '/').'-(\d+)$/';
        $used = [];

        foreach ($trees as $tree) {
            if (preg_match($pattern, $tree->kode_pohon, $matches)) {
                $used[(int) $matches[1]] = true;
            }
        }

        $next = $used === [] ? 1 : max(array_keys($used)) + 1;

        foreach ($trees as $tree) {
            if (preg_match($pattern, $tree->kode_pohon)) {
                continue;
            }

            while (isset($used[$next])) {
                $next++;
            }

            $kode = sprintf('%s-%03d', $prefix, $next);

            DB::table('pohon')
                ->where('id', $tree->id)
                ->update(['kode_pohon' => $kode]);

            $used[$next] = true;
            $next++;
        }
    }

    public function down(): void
    {
        // Penggantian nama kode lama tidak dikembalikan otomatis.
    }
};
