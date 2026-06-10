import com.sun.net.httpserver.HttpExchange;
import com.sun.net.httpserver.HttpHandler;
import com.sun.net.httpserver.HttpServer;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.InetSocketAddress;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.nio.file.StandardOpenOption;
import java.util.UUID;

public class GameSpaceServer{

    public static void main(String[] args) throws IOException{
        HttpServer server = HttpServer.create(new InetSocketAddress(8080), 0);
        server.createContext("/api/checkout", new CheckoutHandler());
        server.setExecutor(null);
        server.start();
    }

    static class CheckoutHandler implements HttpHandler{
        @Override
        public void handle(HttpExchange exchange) throws IOException{
            if ("POST".equals(exchange.getRequestMethod())) {
                InputStream is = exchange.getRequestBody();
                String body = new String(is.readAllBytes());

                String userId =
                    JsonUtil.extractJsonValue(
                            body,
                            "userId"
                    );
                String productId = extractJsonValue(body, "productId");
                String qtyStr = extractJsonValue(body, "quantity");

                int quantity = Integer.parseInt(qtyStr);

                String usersData = new String(Files.readAllBytes(Paths.get("../data/users.json")));
                String productsData = new String(Files.readAllBytes(Paths.get("../data/products.json")));

                String userRole = "REGULAR";
                if (usersData.contains("\"id\": \"" + userId + "\"") && usersData.contains("\"role\": \"VIP\"")) {
                    userRole = "VIP";
                }

                double basePrice = 100000.0;
                double total =
                        DiscountCalculator.calculateTotal(
                                quantity,
                                userRole
                        );
                if (quantity >= 20) {
                    total = total * 0.85;
                } else if (quantity >= 10) {
                    total = total * 0.90;
                } else if (quantity >= 5) {
                    total = total * 0.95;
                }

                if ("VIP".equals(userRole)) {
                    total = total * 0.80;
                } else {
                    if (total > 1500000) {
                        total = total - 50000;
                    }
                }

                if (total < 0) {
                    total = 0;
                }

                String orderId = UUID.randomUUID().toString();
                String orderRecord = "{\"orderId\":\"" + orderId + "\", \"total\":" + total + "}";

                Files.write(Paths.get("../data/orders.json"), (orderRecord + "\n").getBytes(), StandardOpenOption.APPEND);

                String response = "{\"status\": \"success\", \"orderId\": \"" + orderId + "\", \"finalTotal\": " + total + "}";
                exchange.getResponseHeaders().set("Content-Type", "application/json");
                exchange.sendResponseHeaders(200, response.getBytes().length);

                OutputStream os = exchange.getResponseBody();
                os.write(response.getBytes());
                os.close();
            } else {
                exchange.sendResponseHeaders(405, -1);
            }
        }

        private String extractJsonValue(String json, String key){
            String searchKey = "\"" + key + "\":";
            int startIndex = json.indexOf(searchKey);
            if (startIndex == -1) return "";

            startIndex = startIndex + searchKey.length();
            int endIndex = json.indexOf(",", startIndex);
            if (endIndex == -1) {
                endIndex = json.indexOf("}", startIndex);
            }

            String value = json.substring(startIndex, endIndex).trim();
            if (value.startsWith("\"") && value.endsWith("\"")) {
                value = value.substring(1, value.length() - 1);
            }
            return value;
        }
    }
}