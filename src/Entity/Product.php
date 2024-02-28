<?php
declare(strict_types=1);

namespace App\Entity;

class Product
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $price,
        public readonly string $currency,
        public readonly string $country,
        public readonly ?string $image = null,
        public readonly array $apiVariables = []
    ) {
    }
}
