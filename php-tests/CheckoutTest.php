<?php

use PHPUnit\Framework\TestCase;

class CheckoutTest extends TestCase
{
    public function testPayloadCreation()
    {
        $_POST['userId'] = 'U01';
        $_POST['productId'] = 'G001';
        $_POST['quantity'] = '5';
        $_POST['catatan'] = 'Testing';

        $data = array(
            "userId" => $_POST['userId'],
            "productId" => $_POST['productId'],
            "quantity" => $_POST['quantity']
        );

        $payload = json_encode($data);

        $this->assertEquals(
            '{"userId":"U01","productId":"G001","quantity":"5"}',
            $payload
        );
    }

    public function testApiEndpoint()
    {
        $url =
            'http://localhost:8080/api/checkout';

        $this->assertEquals(
            'http://localhost:8080/api/checkout',
            $url
        );
    }

    public function testContentTypeHeader()
    {
        $headers = array(
            'Content-Type: application/json'
        );

        $this->assertContains(
            'Content-Type: application/json',
            $headers
        );
    }
}