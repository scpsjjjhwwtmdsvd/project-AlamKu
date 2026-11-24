@php
    $moduleCount = 25;
    $quiet = 2;
    $seed = hash('sha256', 'ALAMKU-QRIS-DEMO-2026');
    $bits = '';

    foreach (str_split($seed) as $hex) {
        $bits .= str_pad(base_convert($hex, 16, 2), 4, '0', STR_PAD_LEFT);
    }

    $finderValue = static function (int $x, int $y, int $ox, int $oy): ?bool {
        $localX = $x - $ox;
        $localY = $y - $oy;

        if ($localX < 0 || $localY < 0 || $localX > 6 || $localY > 6) {
            return null;
        }

        $border = $localX === 0 || $localY === 0 || $localX === 6 || $localY === 6;
        $core = $localX >= 2 && $localX <= 4 && $localY >= 2 && $localY <= 4;

        return $border || $core;
    };

    $modules = [];

    for ($y = 0; $y < $moduleCount; $y++) {
        for ($x = 0; $x < $moduleCount; $x++) {
            $finder = $finderValue($x, $y, 0, 0)
                ?? $finderValue($x, $y, $moduleCount - 7, 0)
                ?? $finderValue($x, $y, 0, $moduleCount - 7);

            if ($finder !== null) {
                $modules[] = [$x, $y, $finder];
                continue;
            }

            $isTiming = ($y === 6 && $x >= 8 && $x <= $moduleCount - 9)
                || ($x === 6 && $y >= 8 && $y <= $moduleCount - 9);

            if ($isTiming) {
                $modules[] = [$x, $y, ($x + $y) % 2 === 0];
                continue;
            }

            $bit = $bits[($y * $moduleCount + $x) % strlen($bits)] === '1';
            $modules[] = [$x, $y, $bit];
        }
    }

    $box = $moduleCount + ($quiet * 2);
@endphp

<svg
    class="qris-code"
    viewBox="0 0 {{ $box }} {{ $box }}"
    role="img"
    aria-label="Kode QRIS simulasi pembayaran AlamKu"
    shape-rendering="crispEdges"
>
    <rect width="{{ $box }}" height="{{ $box }}" fill="#ffffff"/>
    @foreach ($modules as [$x, $y, $on])
        @if ($on)
            <rect x="{{ $x + $quiet }}" y="{{ $y + $quiet }}" width="1" height="1" fill="#14261a"/>
        @endif
    @endforeach
</svg>
