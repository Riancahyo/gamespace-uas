package com.gamespace.e2e;

import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;

import java.io.IOException;
import java.nio.file.*;
import java.time.Duration;

import static org.junit.jupiter.api.Assertions.*;

/**
 * System / E2E test menggunakan Selenium WebDriver.
 * setUp()    → reset orders.json ke seed bersih SEBELUM setiap tes,
 *              sehingga tes tidak bergantung pada sisa transaksi sebelumnya.
 * tearDown() → tutup browser DAN bersihkan orders.json SETELAH setiap tes.
 */
public class CheckoutE2ETest {

    private WebDriver driver;

    // Path seed data (relatif terhadap working dir = root proyek)
    private static final Path DATA_DIR    = Paths.get("../data");
    private static final Path ORDERS_FILE = DATA_DIR.resolve("orders.json");
    private static final Path ORDERS_SEED = DATA_DIR.resolve("orders.seed.json");

    // Nomor 5 – setUpClass: pastikan seed ada sebelum semua tes
    @BeforeAll
    static void createSeedFiles() throws IOException {
        if (!Files.exists(ORDERS_SEED)) {
            Files.writeString(ORDERS_SEED, "[]");
        }
    }

    // setUp: reset data DAN buka browser SEBELUM tiap tes
    @BeforeEach
    void setUp() throws IOException {
        // 1. Restore seed data bersih
        Files.copy(ORDERS_SEED, ORDERS_FILE, StandardCopyOption.REPLACE_EXISTING);

        // 2. Konfigurasi ChromeOptions
        ChromeOptions options = new ChromeOptions();

        // Mode headless: aktif di CI (env var CI=true) atau bisa di-set manual
        boolean isCI = "true".equalsIgnoreCase(System.getenv("CI"));
        if (isCI) {
            options.addArguments("--headless=new");
            options.addArguments("--no-sandbox");
            options.addArguments("--disable-dev-shm-usage");
            options.addArguments("--disable-gpu");
            options.addArguments("--window-size=1280,800");
        }

        String chromedriverPath = System.getProperty("webdriver.chrome.driver");
        if (chromedriverPath != null && !chromedriverPath.isBlank()) {
            System.setProperty("webdriver.chrome.driver", chromedriverPath);
        }

        driver = new ChromeDriver(options);
        driver.manage().timeouts().implicitlyWait(Duration.ofSeconds(10));
    }

    // tearDown: tutup browser DAN bersihkan sampah
    @AfterEach
    void tearDown() throws IOException {
        // 1. Tutup browser
        if (driver != null) {
            driver.quit();
            driver = null;
        }

        // 2. Hapus transaksi sampah – kembalikan orders.json ke array kosong
        Files.writeString(ORDERS_FILE, "[]");
    }

    // Test Case: Alur checkout normal berhasil
    @Test
    void checkoutFlowTest() {
        driver.get("http://localhost:8000/index.php");

        // Isi form quantity
        WebElement quantity = driver.findElement(By.id("quantity"));
        quantity.clear();
        quantity.sendKeys("5");

        // Isi catatan
        WebElement catatan = driver.findElement(By.id("catatan"));
        catatan.sendKeys("Pesanan Selenium");

        // Submit
        driver.findElement(By.id("btn-submit")).click();

        // Verifikasi respons server
        WebElement response = driver.findElement(By.id("server-response"));
        assertTrue(response.getText().contains("success"),
            "Respons server harus mengandung 'success'"
        );

        // Verifikasi catatan muncul di halaman
        WebElement notes = driver.findElement(By.id("order-notes"));
        assertTrue(notes.getText().contains("Pesanan Selenium"),
            "Catatan pesanan harus tampil di halaman"
        );
    }

    // Test Case: Validasi client-side – quantity kosong
    @Test
    void checkoutFlow_emptyQuantity_showsError() {
        driver.get("http://localhost:8000/index.php");

        // Kosongkan quantity lalu langsung submit
        WebElement quantity = driver.findElement(By.id("quantity"));
        quantity.clear();

        driver.findElement(By.id("btn-submit")).click();

        // Halaman harus menampilkan pesan error (bukan pindah ke halaman sukses)
        String pageSource = driver.getPageSource();
        assertTrue(
            pageSource.contains("error") || pageSource.contains("required") ||
            driver.getCurrentUrl().contains("index.php"),
            "Harus ada pesan error atau tetap di halaman yang sama"
        );
    }
}