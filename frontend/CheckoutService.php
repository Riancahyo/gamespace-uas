<?php

class CheckoutService
{
    private string $backendUrl;

    /**
     * @param string        $backendUrl   URL backend Java
     */
    public function __construct(string $backendUrl = 'http://localhost:8080/api/checkout')
    {
        $this->backendUrl = $backendUrl;
    }

    /**
     * Validasi input dari form sebelum dikirim ke backend.
     *
     * @param  array $post  Array berisi userId, productId, quantity
     * @return array{valid: bool, errors: string[]}
     */
    public function validateInput(array $post): array
    {
        $errors = [];

        if (empty($post['userId'])) {
            $errors[] = 'userId tidak boleh kosong.';
        }

        if (empty($post['productId'])) {
            $errors[] = 'productId tidak boleh kosong.';
        }

        $qty = isset($post['quantity']) ? (int) $post['quantity'] : 0;
        if ($qty <= 0 || $qty > 50) {
            $errors[] = 'Kuantitas harus antara 1 hingga 50.';
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Bangun payload JSON yang akan dikirim ke backend Java.
     *
     * @param  array $post
     * @return string  JSON string
     */
    public function buildPayload(array $post): string
    {
        $data = [
            'userId'    => $post['userId']    ?? '',
            'productId' => $post['productId'] ?? '',
            'quantity'  => (int) ($post['quantity'] ?? 0),
        ];

        return json_encode($data);
    }

    /**
     * Kirim request POST ke backend Java.
     *
     * Parameter $curlExecutor memungkinkan injeksi fungsi cURL palsu
     * saat unit test, sehingga tidak butuh server nyala.
     *
     * Signature executor: function(string $url, string $payload): array{httpCode:int, body:string}
     *
     * @param  string        $payload       JSON string
     * @param  callable|null $curlExecutor  Opsional: override cURL untuk testing
     * @return array{httpCode: int, body: string}
     */
    public function sendToBackend(string $payload, ?callable $curlExecutor = null): array
    {
        if ($curlExecutor !== null) {
            // Mode test: gunakan fungsi injeksi, tidak sentuh cURL sama sekali
            return ($curlExecutor)($this->backendUrl, $payload);
        }

        // Mode produksi: cURL asli
        $ch = curl_init($this->backendUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $body     = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'httpCode' => $httpCode,
            'body'     => $body !== false ? $body : '',
        ];
    }

    /**
     * Sanitasi catatan pembeli agar aman ditampilkan di HTML (cegah XSS).
     *
     * @param  string $catatan
     * @return string
     */
    public function sanitizeCatatan(string $catatan): string
    {
        return htmlspecialchars($catatan, ENT_QUOTES, 'UTF-8');
    }
}