<?php

namespace Tests\Feature;

use Laravel\Paddle\Cashier;
use Laravel\Paddle\ProductPrices;

class PricesTest extends FeatureTestCase
{
    public function test_it_can_fetch_the_prices_of_products()
    {
        $prices = Cashier::prices([1516, 1515]);

        $this->assertCount(2, $prices);
        $this->assertContainsOnlyInstancesOf(ProductPrices::class, $prices->all());
    }
}
