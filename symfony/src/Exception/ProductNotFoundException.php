<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(404)]
final class ProductNotFoundException extends \DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(sprintf('Product %d not found.', $id));
    }
}
