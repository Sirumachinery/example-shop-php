<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;

class ProductRepository
{
    public function findAll(): array
    {
        return [
            new Product(
                'ng-mario-50',
                'Nintendo Giftcard',
                '50.00',
                'NGN',
                'NG',
                'images/mario.png',
                [
                    'basePrice' => '50.00',
                    'currency' => 'NGN',
                    'variant' => 'variant3',
                    'purchaseCountry' => 'NG',
                ]
            ),
            new Product(
                'ng-mario-100',
                'Nintendo Giftcard',
                '100.00',
                'NGN',
                'NG',
                'images/mario.png',
                [
                    'basePrice' => '100.00',
                    'currency' => 'NGN',
                    'variant' => 'variant3',
                    'purchaseCountry' => 'NG',
                ]
            ),
            new Product(
                'ng-mario-500',
                'Nintendo Giftcard',
                '500.00',
                'NGN',
                'NG',
                'images/mario.png',
                [
                    'basePrice' => '500.00',
                    'currency' => 'NGN',
                    'variant' => 'variant3',
                    'purchaseCountry' => 'NG',
                ]
            )
        ];
    }

    public function findOneById(string $id): ?Product
    {
        foreach ($this->findAll() as $product) {
            if ($product->id === $id) {
                return $product;
            }
        }
        return null;
    }
}
