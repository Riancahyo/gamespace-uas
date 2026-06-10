<?php

use PHPUnit\Framework\TestCase;

class CheckoutTest extends TestCase
{
    public function testCheckoutApiReturnsSuccess()
    {
        $payload = json_encode([
            "userId" => "U01",
            "productId" => "G001",
            "quantity" => 5
        ]);

        $ch = curl_init(
            'http://localhost:8080/api/checkout'
        );

        curl_setopt(
            $ch,
            CURLOPT_RETURNTRANSFER,
            true
        );

        curl_setopt(
            $ch,
            CURLOPT_POST,
            true
        );

        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            $payload
        );

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json'
            ]
        );

        $response = curl_exec($ch);

        $httpCode = curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        curl_close($ch);

        $this->assertEquals(
            200,
            $httpCode
        );

        $this->assertNotFalse(
            $response
        );

        $this->assertStringContainsString(
            'success',
            $response
        );

        $this->assertStringContainsString(
            'orderId',
            $response
        );

        $this->assertStringContainsString(
            'finalTotal',
            $response
        );
    }
}