<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../frontend/CheckoutService.php';

class CheckoutServiceTest extends TestCase
{
    private CheckoutService $service;

    // Path ke file data (relatif terhadap root proyek)
    private const DATA_DIR        = __DIR__ . '/../../data';
    private const ORDERS_FILE     = self::DATA_DIR . '/orders.json';
    private const ORDERS_SEED     = self::DATA_DIR . '/orders.seed.json';   // cadangan bersih
    private const PRODUCTS_FILE   = self::DATA_DIR . '/products.json';
    private const PRODUCTS_SEED   = self::DATA_DIR . '/products.seed.json'; // cadangan bersih

    // setUp: restore seed data SEBELUM setiap tes
    protected function setUp(): void
    {
        // Buat file seed sekali jika belum ada (pertama kali dijalankan)
        if (!file_exists(self::ORDERS_SEED)) {
            file_put_contents(self::ORDERS_SEED, '[]');
        }
        if (!file_exists(self::PRODUCTS_SEED) && file_exists(self::PRODUCTS_FILE)) {
            copy(self::PRODUCTS_FILE, self::PRODUCTS_SEED);
        }

        // Salin seed bersih → file aktif
        copy(self::ORDERS_SEED,   self::ORDERS_FILE);
        copy(self::PRODUCTS_SEED, self::PRODUCTS_FILE);

        $this->service = new CheckoutService('http://localhost:8080/api/checkout');
    }

    // tearDown: hapus transaksi sampah SETELAH setiap tes
    protected function tearDown(): void
    {
        // Kembalikan orders.json ke array kosong (bukan hapus, agar file tetap ada)
        file_put_contents(self::ORDERS_FILE, '[]');
    }

    // validateInput

    public function testValidateInput_valid(): void
    {
        $post   = ['userId' => 'U01', 'productId' => 'G001', 'quantity' => '5'];
        $result = $this->service->validateInput($post);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testValidateInput_emptyUserId(): void
    {
        $post   = ['userId' => '', 'productId' => 'G001', 'quantity' => '5'];
        $result = $this->service->validateInput($post);

        $this->assertFalse($result['valid']);
        $this->assertContains('userId tidak boleh kosong.', $result['errors']);
    }

    public function testValidateInput_emptyProductId(): void
    {
        $post   = ['userId' => 'U01', 'productId' => '', 'quantity' => '5'];
        $result = $this->service->validateInput($post);

        $this->assertFalse($result['valid']);
        $this->assertContains('productId tidak boleh kosong.', $result['errors']);
    }

    public function testValidateInput_quantityZero(): void
    {
        $post   = ['userId' => 'U01', 'productId' => 'G001', 'quantity' => '0'];
        $result = $this->service->validateInput($post);

        $this->assertFalse($result['valid']);
        $this->assertContains('Kuantitas harus antara 1 hingga 50.', $result['errors']);
    }

    public function testValidateInput_quantityNegative(): void
    {
        $post   = ['userId' => 'U01', 'productId' => 'G001', 'quantity' => '-1'];
        $result = $this->service->validateInput($post);

        $this->assertFalse($result['valid']);
        $this->assertContains('Kuantitas harus antara 1 hingga 50.', $result['errors']);
    }

    public function testValidateInput_quantityTooLarge(): void
    {
        $post   = ['userId' => 'U01', 'productId' => 'G001', 'quantity' => '51'];
        $result = $this->service->validateInput($post);

        $this->assertFalse($result['valid']);
        $this->assertContains('Kuantitas harus antara 1 hingga 50.', $result['errors']);
    }

    public function testValidateInput_quantityExactly50_isValid(): void
    {
        $post   = ['userId' => 'U01', 'productId' => 'G001', 'quantity' => '50'];
        $result = $this->service->validateInput($post);

        $this->assertTrue($result['valid']);
    }

    public function testValidateInput_multipleErrors(): void
    {
        $post   = ['userId' => '', 'productId' => '', 'quantity' => '0'];
        $result = $this->service->validateInput($post);

        $this->assertFalse($result['valid']);
        $this->assertCount(3, $result['errors']);
    }

    // buildPayload

    public function testBuildPayload_returnsValidJson(): void
    {
        $post    = ['userId' => 'U01', 'productId' => 'G001', 'quantity' => '5'];
        $payload = $this->service->buildPayload($post);
        $decoded = json_decode($payload, true);

        $this->assertNotNull($decoded);
        $this->assertEquals('U01',  $decoded['userId']);
        $this->assertEquals('G001', $decoded['productId']);
        $this->assertEquals(5,      $decoded['quantity']);
    }

    public function testBuildPayload_quantityCastToInt(): void
    {
        $post    = ['userId' => 'U02', 'productId' => 'G001', 'quantity' => '10'];
        $payload = $this->service->buildPayload($post);
        $decoded = json_decode($payload, true);

        $this->assertIsInt($decoded['quantity']);
    }

    public function testBuildPayload_missingKeysDefaultToEmpty(): void
    {
        $payload = $this->service->buildPayload([]);
        $decoded = json_decode($payload, true);

        $this->assertEquals('', $decoded['userId']);
        $this->assertEquals('', $decoded['productId']);
        $this->assertEquals(0,  $decoded['quantity']);
    }

    // sendToBackend — mock (tidak butuh server)
    public function testSendToBackend_mockSuccess(): void
    {
        $mockExecutor = function (string $url, string $payload): array {
            return [
                'httpCode' => 200,
                'body'     => '{"status":"success","orderId":"abc-123","finalTotal":475000}',
            ];
        };

        $result = $this->service->sendToBackend('{"userId":"U01"}', $mockExecutor);

        $this->assertEquals(200, $result['httpCode']);
        $this->assertStringContainsString('success', $result['body']);
    }

    public function testSendToBackend_mockServerError(): void
    {
        $mockExecutor = function (string $url, string $payload): array {
            return ['httpCode' => 500, 'body' => '{"error":"Internal Server Error"}'];
        };

        $result = $this->service->sendToBackend('{"userId":"U01"}', $mockExecutor);

        $this->assertEquals(500, $result['httpCode']);
    }

    public function testSendToBackend_mockCurlFailure(): void
    {
        $mockExecutor = function (string $url, string $payload): array {
            return ['httpCode' => 0, 'body' => ''];
        };

        $result = $this->service->sendToBackend('{"userId":"U01"}', $mockExecutor);

        $this->assertEquals(0,  $result['httpCode']);
        $this->assertEquals('', $result['body']);
    }

    public function testSendToBackend_passesCorrectUrlToExecutor(): void
    {
        $capturedUrl  = '';
        $mockExecutor = function (string $url, string $payload) use (&$capturedUrl): array {
            $capturedUrl = $url;
            return ['httpCode' => 200, 'body' => '{}'];
        };

        $service = new CheckoutService('http://localhost:9999/api/checkout');
        $service->sendToBackend('{}', $mockExecutor);

        $this->assertEquals('http://localhost:9999/api/checkout', $capturedUrl);
    }

    /**
     * @group integration
     */
    public function testSendToBackend_realCurl_toJavaServer(): void
    {
        $payload = json_encode([
            'userId'    => 'U01',
            'productId' => 'G001',
            'quantity'  => 5,
        ]);

        // Panggil TANPA mock executor → jalur cURL produksi
        $result = $this->service->sendToBackend($payload);

        $this->assertEquals(200, $result['httpCode'],
            'Server Java harus nyala di port 8080 untuk test ini.'
        );
        $this->assertNotEmpty($result['body']);

        $decoded = json_decode($result['body'], true);
        $this->assertArrayHasKey('status',  $decoded);
        $this->assertArrayHasKey('orderId', $decoded);
        $this->assertEquals('success', $decoded['status']);
    }

    // sanitizeCatatan

    public function testSanitizeCatatan_plainText(): void
    {
        $result = $this->service->sanitizeCatatan('Pesanan biasa');
        $this->assertEquals('Pesanan biasa', $result);
    }

    public function testSanitizeCatatan_xssScript(): void
    {
        $input    = '<script>alert("xss")</script>';
        $expected = '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;';

        $this->assertEquals($expected, $this->service->sanitizeCatatan($input));
    }

    public function testSanitizeCatatan_htmlEntities(): void
    {
        $result = $this->service->sanitizeCatatan('<b>Bold</b> & "quoted"');

        $this->assertStringNotContainsString('<b>', $result);
        $this->assertStringContainsString('&lt;b&gt;', $result);
    }

    public function testSanitizeCatatan_emptyString(): void
    {
        $this->assertEquals('', $this->service->sanitizeCatatan(''));
    }
}