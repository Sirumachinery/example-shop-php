<?php

class Siru {
    /**
     * @param  array  $fields
     * @param  string $merchantSecret
     * @return string
     */
    public function calculateRequestSignature(array $fields, $merchantSecret)
    {
        ksort($fields);

        return $this->calculateHash($fields, $merchantSecret);
    }

    /**
     * @param  array  $getValues
     * @param  string $merchantSecret
     * @return string
     */
    public function calculateResponseSignature(array $getValues, $merchantSecret)
    {
        $signedFields = ['siru_uuid', 'siru_merchantId', 'siru_submerchantReference', 'siru_purchaseReference', 'siru_event'];

        return $this->calculateHash(array_map(function($field) use ($getValues) {
            return $getValues[$field];
        }, $signedFields), $merchantSecret);
    }

    /**
     * @param  array  $fields
     * @param  string $secret
     * @return string
     */
    private function calculateHash($fields, $secret)
    {
        return hash_hmac("sha512", implode(';', $fields), $secret);
    }
} 