<?php

namespace App\Repository;

class ProductRepository
{
    /**
     * @var
     */
    protected $products;

    public function __construct()
    {
        $this->products = [
            1 => array(
                "title"       => "product.card_deposit.title",
                "description" => "product.card_deposit.description",
                "image"       => "siru-prepaid.png",
                "price"       => '5.00',
                "country"     => 'FI',
                "currency"    => 'EUR'
            ),
            2 => array(
                "title"       => "product.on_demand.title",
                "description" => "product.on_demand.description",
                "image"       => "siru-ondemand.png",
                "price"       => '0.10',
                "country"     => 'FI',
                "currency"    => 'EUR'
            ),
            3 => array(
                "title"       => "product.voucher.title",
                "description" => "product.voucher.description",
                "image"       => "siru-voucher.png",
                "price"       => '0.11',
                "country"     => 'FI',
                "currency"    => 'EUR'
            ),

            4 => array(
                "title"       => "product.card_deposit.title",
                "description" => "product.card_deposit.description",
                "image"       => "siru-prepaid.png",
                "price"       => '5.00',
                "country"     => 'SE',
                "currency"    => 'SEK'
            ),
            5 => array(
                "title"       => "product.charity.title",
                "description" => "product.charity.description",
                "image"       => "donation.png",
                "price"       => '30.00',
                "country"     => 'SE',
                "currency"    => 'SEK'
            ),

            6 => array(
                "title"       => "product.card_deposit.title",
                "description" => "product.card_deposit.description",
                "image"       => "siru-prepaid.png",
                "price"       => '5.00',
                "country"     => 'NO',
                "currency"    => 'NOK'
            ),
            7 => array(
                "title"       => "product.charity.title",
                "description" => "product.charity.description",
                "image"       => "donation.png",
                "price"       => '30.00',
                "country"     => 'NO',
                "currency"    => 'NOK'
            ),


            8 => array(
                "title"       => "product.charity.title",
                "description" => "product.charity.description",
                "image"       => "donation.png",
                "price"       => '15.00',
                "country"     => 'GB',
                "currency"    => 'GBP'
            ),
            9 => array(
                "title"       => "product.gaming.title",
                "description" => "product.gaming.description",
                "image"       => "gaming.png",
                "price"       => '5.00',
                "country"     => 'GB',
                "currency"    => 'GBP'
            )

        ];
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->products;
    }

    /**
     * @param $id
     * @return array
     */
    public function findById($id)
    {
        return isset($this->products[$id]) ? $this->products[$id] : null;
    }

}
