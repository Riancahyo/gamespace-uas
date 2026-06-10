package com.gamespace.unit;

import com.gamespace.DiscountCalculator;
import org.junit.jupiter.api.Test;

import static org.junit.jupiter.api.Assertions.*;

/**
 * Unit test untuk DiscountCalculator.
 *
 * Cabang (branch) yang dicakup:
 *  qty < 5              → tidak ada diskon kuantitas
 *  qty >= 5  (tepat 5)  → diskon 5%
 *  qty >= 10 (tepat 10) → diskon 10%
 *  qty >= 20 (tepat 20) → diskon 15%
 *  role = REGULAR, total <= 1.500.000  → tidak ada potongan flat
 *  role = REGULAR, total >  1.500.000  → potongan Rp 50.000
 *  role = VIP                          → diskon 20%
 */
public class DiscountCalculatorTest {

    @Test
    void regularQty5() {
        // 5 * 100.000 = 500.000 → diskon 5% → 475.000
        // total <= 1.500.000 → tidak ada potongan flat
        double total = DiscountCalculator.calculateTotal(5, "REGULAR");
        assertEquals(475000.0, total);
    }

    @Test
    void regularQty10() {
        // 10 * 100.000 = 1.000.000 → diskon 10% → 900.000
        // total <= 1.500.000 → tidak ada potongan flat
        double total = DiscountCalculator.calculateTotal(10, "REGULAR");
        assertEquals(900000.0, total);
    }

    @Test
    void vipQty5() {
        // 5 * 100.000 = 500.000 → diskon 5% → 475.000 → VIP 20% → 380.000
        double total = DiscountCalculator.calculateTotal(5, "VIP");
        assertEquals(380000.0, total);
    }

    @Test
    void regularQty20() {
        // 20 * 100.000 = 2.000.000 → diskon 15% → 1.700.000
        // REGULAR, total > 1.500.000 → kurang 50.000 → 1.650.000
        double total = DiscountCalculator.calculateTotal(20, "REGULAR");
        assertEquals(1650000.0, total);
    }

    /**
     * qty = 1 (kurang dari 5) → tidak ada diskon kuantitas sama sekali.
     * REGULAR, total = 100.000 ≤ 1.500.000 → tidak ada potongan flat.
     */
    @Test
    void regularQty1_noDiscount() {
        double total = DiscountCalculator.calculateTotal(1, "REGULAR");
        assertEquals(100000.0, total);
    }

    /**
     * VIP dengan qty = 10 → diskon 10%, lalu diskon VIP 20%.
     * 10 * 100.000 = 1.000.000 → x0.90 → 900.000 → x0.80 → 720.000
     */
    @Test
    void vipQty10() {
        double total = DiscountCalculator.calculateTotal(10, "VIP");
        assertEquals(720000.0, total);
    }

    /**
     * VIP dengan qty = 20 → diskon 15%, lalu diskon VIP 20%.
     * 20 * 100.000 = 2.000.000 → x0.85 → 1.700.000 → x0.80 → 1.360.000
     */
    @Test
    void vipQty20() {
        double total = DiscountCalculator.calculateTotal(20, "VIP");
        assertEquals(1360000.0, total);
    }

    @Test
    void regularQty17_flatDiscount() {
        // 17 * 100.000 = 1.700.000 → diskon 10% (qty>=10) → 1.530.000
        // REGULAR, total > 1.500.000 → 1.530.000 - 50.000 = 1.480.000
        double total = DiscountCalculator.calculateTotal(17, "REGULAR");
        assertEquals(1480000.0, total);
    }

    @Test
    void zeroQuantity_returnsZero() {
        double total = DiscountCalculator.calculateTotal(0, "REGULAR");
        assertEquals(0.0, total);
    }
}