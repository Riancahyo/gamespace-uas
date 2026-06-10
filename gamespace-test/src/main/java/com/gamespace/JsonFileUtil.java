package com.gamespace;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardOpenOption;

public class JsonFileUtil {

    public static String readJsonFile(String path) throws IOException {
        return Files.readString(Path.of(path));
    }

    public static void appendJsonFile(String path, String content) throws IOException {
        Files.write(
                Path.of(path),
                content.getBytes(),
                StandardOpenOption.APPEND
        );
    }
}