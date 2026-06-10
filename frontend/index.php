<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>GameSpace - Beli Voucher</title>
    <script src="assets/script.js"></script>
</head>
<body>
    <h1>GameSpace Playground</h1>
    <form id="orderForm" action="checkout.php" method="POST">
        <div>
            <label for="userId">User ID:</label>
            <input type="text" id="userId" name="userId" value="U01" required>
        </div>
        <br>
        <div>
            <label for="productId">Product ID:</label>
            <input type="text" id="productId" name="productId" value="G001" readonly>
        </div>
        <br>
        <div>
            <label for="quantity">Kuantitas:</label>
            <input type="number" id="quantity" name="quantity" required>
        </div>
        <br>
        <div>
            <label for="catatan">Catatan Pesanan:</label>
            <textarea id="catatan" name="catatan"></textarea>
        </div>
        <br>
        <button type="submit" id="btn-submit">Checkout</button>
    </form>
</body>
</html>