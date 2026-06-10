package com.gamespace;

public class DiscountCalculator {

    public static double calculateTotal(
            int quantity,
            String role) {

        double basePrice = 100000.0;
        double total = basePrice * quantity;

        if (quantity >= 20) {
            total *= 0.85;
        }
        else if (quantity >= 10) {
            total *= 0.90;
        }
        else if (quantity >= 5) {
            total *= 0.95;
        }

        if ("VIP".equals(role)) {
            total *= 0.80;
        }
        else {
            if (total > 1500000) {
                total -= 50000;
            }
        }

        if (total < 0) {
            total = 0;
        }

        return total;
    }
}