package com.gamespace.unit;

import com.gamespace.JsonFileUtil;
import org.junit.jupiter.api.*;
import java.io.IOException;
import java.nio.file.*;
import static org.junit.jupiter.api.Assertions.*;

public class JsonFileUtilTest {

    private static Path tempDir;
    private static Path usersFile;
    private static Path ordersFile;

    @BeforeAll
    static void setUpFiles() throws IOException {
        tempDir = Files.createTempDirectory("gamespace-test");

        usersFile = tempDir.resolve("users.json");
        Files.writeString(usersFile,
            "[\n" +
            "  {\"id\": \"U01\", \"username\": \"tester_reg\", \"role\": \"REGULAR\"},\n" +
            "  {\"id\": \"U02\", \"username\": \"tester_vip\", \"role\": \"VIP\"}\n" +
            "]"
        );

        ordersFile = tempDir.resolve("orders.json");
        Files.writeString(ordersFile, "[]");
    }

    @AfterAll
    static void tearDownFiles() throws IOException {
        // Bersihkan semua file sementara setelah seluruh test selesai
        Files.deleteIfExists(ordersFile);
        Files.deleteIfExists(usersFile);
        Files.deleteIfExists(tempDir);
    }


    @Test
    void shouldReadUsersJson() throws Exception {
        String content = JsonFileUtil.readJsonFile(usersFile.toString());
        assertFalse(content.isEmpty());
    }

    @Test
    void usersJsonContainsUser() throws Exception {
        String content = JsonFileUtil.readJsonFile(usersFile.toString());
        assertTrue(content.contains("U01"));
    }

    @Test
    void shouldWriteJsonFile() throws Exception {
        String before = JsonFileUtil.readJsonFile(ordersFile.toString());

        JsonFileUtil.appendJsonFile(
                ordersFile.toString(),
                "\n{\"unitTest\":\"OK\"}"
        );

        String after = JsonFileUtil.readJsonFile(ordersFile.toString());
        assertTrue(after.length() > before.length());
    }
}