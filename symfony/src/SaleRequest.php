<?php

namespace App;

use Symfony\Component\Validator\Constraints\Positive;

final class SaleRequest
{
    public function __construct(
        #[Positive(message: 'Quantity must be a positive integer.')]
        public readonly int $quantity,
    ) {
    }
}
