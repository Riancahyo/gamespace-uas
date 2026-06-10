<?php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

class SystemTest extends TestCase
{
    private $driver;

    protected function setUp(): void
    {
        $this->driver = RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()
        );
    }

    protected function tearDown(): void
    {
        if ($this->driver) {
            $this->driver->quit();
        }
    }

    public function testCheckoutFlow()
    {
        // buka halaman utama
        $this->driver->get(
            'http://localhost:8000/index.php'
        );

        sleep(2);

        // isi quantity
        $qty =
            $this->driver->findElement(
                WebDriverBy::id('quantity')
            );

        $qty->clear();
        $qty->sendKeys('5');

        // isi catatan
        $this->driver
            ->findElement(
                WebDriverBy::id('catatan')
            )
            ->sendKeys(
                'Pesanan Selenium'
            );

        // klik checkout
        $this->driver
            ->findElement(
                WebDriverBy::id('btn-submit')
            )
            ->click();

        // tunggu halaman hasil
        sleep(5);

        // ambil seluruh html
        $pageSource =
            $this->driver->getPageSource();

        // tampilkan jika perlu debug
        file_put_contents(
            'systemtest-result.html',
            $pageSource
        );

        // validasi URL
        $currentUrl =
            $this->driver->getCurrentURL();

        $this->assertStringContainsString(
            'checkout.php',
            $currentUrl
        );

        // validasi halaman hasil
        $this->assertStringContainsString(
            'Status Transaksi',
            $pageSource
        );

        // validasi catatan tampil
        $this->assertStringContainsString(
            'Pesanan Selenium',
            $pageSource
        );

        // validasi response Java API
        $this->assertStringContainsString(
            'success',
            strtolower($pageSource)
        );
    }
}