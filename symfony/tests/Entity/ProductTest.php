<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Exception\InsufficientStockException;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    private function createProduct(int $stock = 10, int $threshold = 5): Product
    {
        return new Product()
            ->setName('Rose Poudré')
            ->setShade('rose')
            ->setPriceHt('14.90')
            ->setStock($stock)
            ->setAlertThreshold($threshold);
    }

    public function testSellDecreasesStock(): void
    {
        $product = $this->createProduct(10);
        $product->sell(3);

        $this->assertSame(7, $product->getStock());
    }

    public function testSellExactStockIsAllowed(): void
    {
        $product = $this->createProduct(10);
        $product->sell(10);

        $this->assertSame(0, $product->getStock());
    }

    public function testSellMoreThanStockThrows(): void
    {
        $product = $this->createProduct(2);

        $this->expectException(InsufficientStockException::class);
        $product->sell(3);
    }

    public function testIsInAlertIsStrict(): void
    {
        $this->assertFalse($this->createProduct(5, 5)->isInAlert());
        $this->assertTrue($this->createProduct(4, 5)->isInAlert());  // stock < threshold
    }
}
