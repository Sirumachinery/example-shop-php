<?php

class Demoshop
{
    /**
     * @var array
     */
    private $products = [];

    public function __construct()
    {
        $this->products = json_decode(file_get_contents('../data/products.json'), true);
    }

    public function confirmAndLogPurchase(array $notify)
    {
        $product = $this->getProduct($notify['siru_purchaseReference']);

        $logEntry = (new DateTime())->format('d.m.Y H:i:s')
                  . " - {$product['name']} ({$product['id']}) was sold for {$product['price']} euros\n";

        file_put_contents('../data/logs/purchases.log', $logEntry, FILE_APPEND);
    }

    /**
     * @param  string $reference
     * @return array
     * @throws Exception
     */
    public function getProduct($reference)
    {
        $products = array_filter($this->products, function($product) use ($reference) {
            return $product['id'] === $reference;
        });

        if (count($products) === 1) {
            return array_shift($products);
        }

        throw new Exception('Product not found with id: ' . $reference);
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
} 