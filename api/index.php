<?php
$location_override = "";
require('api-key.php');
$ip = ""; // Initialize $ip variable

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    // Share internet
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    // Proxy
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    // Remote address
    $ip = $_SERVER['REMOTE_ADDR'];
}

$location = 'Geelong, Australia';

if ($ip) {
    $geoip_query = "http://api.geoiplookup.net/?query=" . $ip;

    $content = file_get_contents($geoip_query);
    $x = simplexml_load_string($content, null, LIBXML_NOCDATA);

    $geo_results = $x->results->result;

    if (!empty($geo_results)) {
        if (!empty($geo_results->city) && !empty($geo_results->countryname)) {
            $geo_city = (string) $geo_results->city; // Fix variable assignment
            $geo_cname = (string) $geo_results->countryname;

            $location = $geo_city . ', ' . $geo_cname;
        }
    }
}

if ($location_override) {
    $location = $location_override; // Fix variable assignment
}

$url = 'https://api.weatherapi.com/v1/current.json?key=' . $key . '&q=' . str_replace(" ", "%20", $location) . '&aqi=no'; // Fix URL construction

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

$response_arr = json_decode($response);
$data = array(
    "style" => "", // Add missing comma and semicolon
    "temp" => "", // Add missing comma and semicolon
    "location" => $location
);

$valid_style_options = array(
    "CloudAtNight",
    "CloudSunny",
    "Cloudy",
    "CloudyRain",
    "Fog",
    "HeavyRain",
    "HeavySnow",
    "LightRain",
    "LightSnow", // Add missing comma
    "MiddleRain",
    "MinSnow",
    "Moon",
    "RainSnow",
    "Sunny",
    "SunnySnow",
    "Thunder",
    "ThunderRain",
    "Tornado",
    "Wind",
    "Temperature"
);
if (isset($response_arr->current->temp_c) && isset($response_arr->current->condition->text)) {
    $style = $response_arr->current->condition->text;

    if (in_array($style, $valid_style_options)) {
        switch (strtolower($style)) {
            case "clear":
                $style = "Moon";
                break;
            case "partly cloudy":
                $style = "CloudAtNight";
                break;
            case "overcast":
                $style = "LightRain";
                break;
            case "mist":
                $style = "LightRain";
                break;
            case "patchy rain possible":
                $style = "LightRain";
                break;
            case "patchy snow possible":
                $style = "MinSnow";
                break;
            case "patchy sleet possible":
                $style = "MinSnow";
                break;
            case "patchy freezing drizzle possible":
                $style = "LightRain";
                break;
            case "thundery outbreaks possible":
                $style = "ThunderRain";
                break; // Add missing break statement
            case "blowing snow":
                $style = "LightSnow";
                break;
            case "blizzard":
                $style = "HeavySnow";
                break;
            case "freezing fog":
                $style = "Fog";
                break;
            case "patchy light drizzle":
                $style = "LightRain";
                break;
            case "light drizzle":
                $style = "LightRain";
                break;
            default:
                $style = "Temperature";
                break;
        }
    }

    $data["style"] = $style; // Add missing semicolon
    $data["temp"] = $response_arr->current->temp_c; // Add missing semicolon
}

echo json_encode($data);
?>
