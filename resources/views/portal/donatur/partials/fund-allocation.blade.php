@php
    $fundTotal = $total ?? array_sum(array_column($allocation, 'amount'));
@endphp

<ul class="fund-list">
    @foreach ($allocation as $item)
        <li>
            <span class="fund-label">{{ $item['label'] }}</span>
            <span class="fund-percent">{{ $item['percent'] }}%</span>
            <strong class="fund-amount">Rp {{ number_format($item['amount'], 0, ',', '.') }}</strong>
        </li>
    @endforeach
</ul>

<div class="fund-foot">
    <span>Total</span>
    <strong>100% &middot; Rp {{ number_format($fundTotal, 0, ',', '.') }}</strong>
</div>
