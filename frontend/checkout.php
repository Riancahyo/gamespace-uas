<?php

require_once __DIR__ . '/CheckoutService.php';

$service = new CheckoutService();

// Validasi input
$validation = $service->validateInput($_POST);

if (!$validation['valid']) {
    echo "<h1>Input tidak valid:</h1><ul>";
    foreach ($validation['errors'] as $err) {
        echo "<li>" . htmlspecialchars($err, ENT_QUOTES, 'UTF-8') . "</li>";
    }
    echo "</ul>";
    exit;
}

// Kirim ke backend Java
$payload  = $service->buildPayload($_POST);
$result   = $service->sendToBackend($payload);

echo "<h1>Status Transaksi: " . $result['httpCode'] . "</h1>";
echo "<h3>Respon dari Core Engine:</h3>";
echo "<pre id='server-response'>" . htmlspecialchars($result['body']) . "</pre>";

// Tampilkan catatan — sudah disanitasi (XSS fix)
$catatan = $service->sanitizeCatatan($_POST['catatan'] ?? '');
echo "<div id='order-notes'>";
echo "Catatan Pembeli: " . $catatan;
echo "</div>";
?>