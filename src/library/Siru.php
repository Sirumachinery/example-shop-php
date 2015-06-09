<?php

class Siru
{
    /**
     * @var string
     */
    private $merchantSecret;

    /**
     * @param string $merchantSecret
     */
    public function __construct($merchantSecret)
    {
        $this->merchantSecret = $merchantSecret;
    }

    /**
     * @param  array $fields
     * @return string
     */
    public function createRequestSignature(array $fields)
    {
        ksort($fields);

        return $this->calculateHash($fields);
    }

    /**
     * @param  array $request
     * @return bool
     */
    public function responseSignatureIsValid(array $request)
    {
        $signedFields = ['siru_uuid', 'siru_merchantId', 'siru_submerchantReference', 'siru_purchaseReference', 'siru_event'];

        $signature = $this->calculateHash(array_map(function($field) use ($request) {
            return $request[$field];
        }, $signedFields));

        return $request['siru_signature'] === $signature;
    }

    /**
     * @param  array $fields
     * @return string
     */
    private function calculateHash($fields)
    {
        return hash_hmac("sha512", implode(';', $fields), $this->merchantSecret);
    }
} 