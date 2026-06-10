document.addEventListener("DOMContentLoaded", function(){
    const form = document.getElementById("orderForm");

    form.addEventListener("submit", function(event){
        const qtyInput = document.getElementById("quantity").value;
        const qty = parseInt(qtyInput, 10);

        if (isNaN(qty) || qty <= 0 || qty > 50) {
            event.preventDefault();
            alert("Validasi Gagal: Kuantitas harus antara 1 hingga 50!");
        }
    });
});