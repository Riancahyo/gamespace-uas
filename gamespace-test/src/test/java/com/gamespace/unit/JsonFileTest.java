package com.gamespace.unit;

import org.junit.jupiter.api.*;

import java.io.IOException;
import java.nio.file.*;

import static org.junit.jupiter.api.Assertions.*;

/**
 * Integration test – memverifikasi file JSON di folder data/ tersedia.
 * setUp()    → menyalin seed bersih ke orders.json SEBELUM tiap tes,
 *              sehingga tes tidak flaky meski orders.json berubah karena
 *              transaksi sebelumnya.
 * tearDown() → mengembalikan orders.json ke array kosong SETELAH tiap tes
 *              agar tidak ada sampah data yang bocor ke tes berikutnya.
 */
public class JsonFileTest {

    // Path relatif terhadap working directory (root proyek saat mvn test)
    private static final Path DATA_DIR      = Paths.get("../data");
    private static final Path ORDERS_FILE   = DATA_DIR.resolve("orders.json");
    private static final Path ORDERS_SEED   = DATA_DIR.resolve("orders.seed.json");
    private static final Path PRODUCTS_FILE = DATA_DIR.resolve("products.json");

    // setUpClass: buat file seed sekali sebelum semua tes
    @BeforeAll
    static void createSeedFiles() throws IOException {
        // Buat seed orders jika belum ada
        if (!Files.exists(ORDERS_SEED)) {
            Files.writeString(ORDERS_SEED, "[]");
        }
    }

    // setUp: restore seed SEBELUM setiap tes
    @BeforeEach
    void setUp() throws IOException {
        // Salin seed bersih → orders.json aktif
        Files.copy(ORDERS_SEED, ORDERS_FILE, StandardCopyOption.REPLACE_EXISTING);
    }

    // tearDown: bersihkan sampah SETELAH setiap tes
    @AfterEach
    void tearDown() throws IOException {
        // Kembalikan orders.json ke array kosong
        Files.writeString(ORDERS_FILE, "[]");
    }

    // Test Cases
    @Test
    void usersJsonExists() {
        assertTrue(
            Files.exists(DATA_DIR.resolve("users.json")),
            "data/users.json harus ada"
        );
    }

    @Test
    void productsJsonExists() {
        assertTrue(
            Files.exists(PRODUCTS_FILE),
            "data/products.json harus ada"
        );
    }

    @Test
    void ordersJsonExists() {
        assertTrue(
            Files.exists(ORDERS_FILE),
            "data/orders.json harus ada"
        );
    }

    @Test
    void ordersJsonIsCleanArrayOnStart() throws IOException {
        // Setelah setUp(), isi orders.json harus selalu array kosong
        String content = Files.readString(ORDERS_FILE).trim();
        assertEquals("[]", content,
            "orders.json harus bersih (array kosong) di awal setiap tes"
        );
    }
}