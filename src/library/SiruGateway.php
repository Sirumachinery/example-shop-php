<?php

class SiruGateway
{
    /**
     * @var string
     */
    private $merchantSecret;

    /**
     * @var int
     */
    private $merchantId;

    /**
     * @param string $merchantSecret
     * @param int    $merchantId
     */
    public function __construct($merchantSecret, $merchantId)
    {
        $this->merchantSecret = $merchantSecret;
        $this->merchantId = $merchantId;
    }

    /**
     * @param  array $inputFields
     * @return string
     */
    public function createRequestSignature(array $inputFields)
    {
        $signedFields = array_merge([ 'merchantId' => $this->merchantId ], $inputFields);

        ksort($signedFields);

        return $this->calculateHash($signedFields);
    }

    /**
     * @param  array $fields
     * @return string
     */
    private function calculateHash($fields)
    {
        return hash_hmac("sha512", implode(';', $fields), $this->merchantSecret);
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
} 