<?php
namespace Siru\DemoShop;

class Products
{
    /**
     * @var array
     */
    private $products = [];

    public function __construct()
    {
        $this->products = json_decode(file_get_contents('../data/products.json'), true);
    }

    /**
     * @param  string $reference
     * @return array
     * @throws \Exception
     */
    public function getProduct($reference)
    {
        $products = array_filter($this->products, function($product) use ($reference) {
            return $product['id'] === $reference;
        });

        if (count($products) === 1) {
            return array_shift($products);
        }

        throw new \Exception('Product not found with id: ' . $reference);
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
} 
