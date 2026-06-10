public class JsonUtil {

    public static String extractJsonValue(String json, String key){

        String searchKey = "\"" + key + "\":";

        int startIndex = json.indexOf(searchKey);

        if(startIndex == -1){
            return "";
        }

        startIndex += searchKey.length();

        int endIndex = json.indexOf(",", startIndex);

        if(endIndex == -1){
            endIndex = json.indexOf("}", startIndex);
        }

        String value =
                json.substring(startIndex, endIndex)
                        .trim();

        if(value.startsWith("\"")
                && value.endsWith("\"")){

            value =
                    value.substring(
                            1,
                            value.length() - 1
                    );
        }

        return value;
    }
}