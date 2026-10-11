<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StockService
{

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function sell(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            throw new BadRequestHttpException('quantity must be greater than 0');
        }
        if ($product->getStock() < $quantity) {
            throw new BadRequestHttpException('quantity must be greater than stock');
        }

        $product->setStock($product->getStock() - $quantity);
        $this->entityManager->flush();
    }
}
