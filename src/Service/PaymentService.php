<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PaymentService
{
    /**
     * @var string
     */
    private const PAYMENT_URL = 'https://ya.ru';

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return bool
     */
    public function pay(): bool
    {
        try {
            $response = $this->client->request('GET', self::PAYMENT_URL);
        } catch (GuzzleException $e) {
            return false;
        }

        return $response->getStatusCode() === 200;
    }
}