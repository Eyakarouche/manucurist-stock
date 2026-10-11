<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(409)]
final class InsufficientStockException extends \DomainException
{
    public function __construct(?int $productId, int $requested, int $available)
    {
        parent::__construct(sprintf(
            'Insufficient stock for product %d: %d requested, %d available.',
            $productId,
            $requested,
            $available,
        ));
    }
}
