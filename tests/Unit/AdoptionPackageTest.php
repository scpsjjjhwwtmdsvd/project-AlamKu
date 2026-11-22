<?php

namespace Tests\Unit;

use App\Support\AdoptionPackage;
use PHPUnit\Framework\TestCase;

class AdoptionPackageTest extends TestCase
{
    public function test_package_prices_follow_the_planned_tiers(): void
    {
        $expected = [
            'starter' => [2, 50000],
            'growth' => [5, 100000],
            'forest' => [10, 200000],
            'guardian' => [25, 500000],
        ];

        foreach ($expected as $key => [$trees, $price]) {
            $package = AdoptionPackage::find($key);

            $this->assertNotNull($package);
            $this->assertSame($trees, $package['trees']);
            $this->assertSame($price, $package['price']);
            $this->assertGreaterThanOrEqual(AdoptionPackage::MINIMUM_TRANSACTION, $package['price']);
        }
    }

    public function test_allocation_has_five_pos_and_totals_exactly(): void
    {
        $allocation = AdoptionPackage::allocation(50000);

        $this->assertSame(
            [25000, 10000, 7500, 5000, 2500],
            array_column($allocation, 'amount')
        );
        $this->assertSame(100, array_sum(array_column($allocation, 'percent')));

        foreach (AdoptionPackage::all() as $package) {
            $packageAllocation = AdoptionPackage::allocation($package['price']);

            $this->assertSame($package['price'], array_sum(array_column($packageAllocation, 'amount')));
        }
    }
}
