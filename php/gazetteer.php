<?php
$result = array();

function getAllData($name){
    $curl = curl_init("https://api.opencagedata.com/geocode/v1/json?q=".$name."&key=b31118c454714ac5bff8f1535317f621");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);

    
    $r = json_decode($json_result, true);
    
    $result["result"]["currency"]["name"] = $r["results"][0]["annotations"]["currency"]["name"];
    $result["result"]["currency"]["iso"] = $r["results"][0]["annotations"]["currency"]["iso_code"];
    $result["result"]["ISOa2"] = $r["results"][0]["components"]["ISO_3166-1_alpha-2"];
    $result["result"]["ISOa3"] = $r["results"][0]["components"]["ISO_3166-1_alpha-3"];
    $result["result"]["country"] = $r["results"][0]["components"]["country"];
    $result["result"]["geometry"]["lat"] = $r["results"][0]["geometry"]["lat"];
    $result["result"]["geometry"]["lng"] = $r["results"][0]["geometry"]["lng"];

    $curl = curl_init("https://restcountries.eu/rest/v2/alpha?codes=".$result["result"]["ISOa3"]);

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    
    $r = json_decode($json_result, true);

    $result["result"]["population"]=$r[0]["population"];
    $result["result"]["capital"]=$r[0]["capital"];
    $result["result"]["flag"]=$r[0]["flag"];

    
    $curl = curl_init("https://openexchangerates.org/api/latest.json?app_id=5eeb7bdfb5d94387a56d7dcd9413b55f");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    
    $r = json_decode($json_result, true);

    $result["result"]["exchRate"]=$r["rates"][$curCode];


    

    $curl = curl_init("https://api.openweathermap.org/data/2.5/forecast?lat=".$result["result"]["geometry"]["lat"]."&lon=".$result["result"]["geometry"]["lng"]."&cnt=5&appid=c0a8cf4628667898c6a3d913189f3596");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    
    $r = json_decode($json_result, true);
    $i=0;
    foreach( $r["list"] as $day){

        $result["result"]["weather"][$i]=$day;
        $i++;
    }

}

function getCountries(){

    
    $curl = curl_init("https://restcountries.eu/rest/v2/all");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    
    $r = json_decode($json_result, true);
    
    
    for ($i=0; $i < count($r) ;$i++){
        if (strpos($r[$i]["name"], 'Korea') !== false) {
            $r[$i]["name"]="Korea (South)";
        }
        if (strpos($r[$i]["name"], 'Palestine') !== false) {
            $r[$i]["name"]="Palestine";
        }
        $result[$i] = $r[$i]["name"];
    }
    
    
    return $result;

    
}

function getCountryData($name){
    
    $curl = curl_init("https://api.opencagedata.com/geocode/v1/json?q=".$name."&key=b31118c454714ac5bff8f1535317f621");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);

    
    $r = json_decode($json_result, true);
    
    $result["result"]["currency"]["name"] = $r["results"][0]["annotations"]["currency"]["name"];
    $result["result"]["currency"]["iso"] = $r["results"][0]["annotations"]["currency"]["iso_code"];
    $result["result"]["ISOa2"] = $r["results"][0]["components"]["ISO_3166-1_alpha-2"];
    $result["result"]["ISOa3"] = $r["results"][0]["components"]["ISO_3166-1_alpha-3"];
    $result["result"]["country"] = $r["results"][0]["components"]["country"];
    $result["result"]["geometry"]["lat"] = $r["results"][0]["geometry"]["lat"];
    $result["result"]["geometry"]["lng"] = $r["results"][0]["geometry"]["lng"];
    return $result["result"];
}

function restCountriesData($iso){
    $curl = curl_init("https://restcountries.eu/rest/v2/alpha?codes=".$iso);

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    
    $r = json_decode($json_result, true);

    $result["results"]["population"]=$r[0]["population"];
    $result["results"]["capital"]=$r[0]["capital"];
    $result["results"]["flag"]=$r[0]["flag"];
    
    return $result["results"];

}

function getExchange($curCode){
    $curl = curl_init("https://openexchangerates.org/api/latest.json?app_id=5eeb7bdfb5d94387a56d7dcd9413b55f");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    
    $r = json_decode($json_result, true);

    $result["results"]["exchRate"]=$r["rates"][$curCode];
    
    return $result["results"];


}

function getWeather($lat,$lng){

    $curl = curl_init("https://api.openweathermap.org/data/2.5/forecast?lat=".$lat."&lon=".$lng."&cnt=5&appid=c0a8cf4628667898c6a3d913189f3596");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    
    $r = json_decode($json_result, true);
    $i=0;
    foreach( $r["list"] as $day){

        $result["results"]["weather"][$i]=$day;
        $i++;
    }
    return $result["results"];


}

function getAltName($name){
    $curl = curl_init("https://restcountries.eu/rest/v2/name/".$name."?fullText=true");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    
    $r = json_decode($json_result, true);

    $result["result"]["currency"]["name"] = $r[0]["currencies"][0]["name"];
    $result["result"]["currency"]["iso"] = $r[0]["currencies"][0]["code"];
    $result["result"]["ISOa2"] = $r[0]["alpha2Code"];
    $result["result"]["ISOa3"] = $r[0]["alpha3Code"];
    $result["result"]["country"] = $r[0]["name"];
    $result["result"]["geometry"]["lat"] = $r[0]["latlng"][0];
    $result["result"]["geometry"]["lng"] = $r[0]["latlng"][1];
    return $result["result"];

    return $result;
}

switch($_POST['functionname']) {
    case 'getCountryData':
        $result=getCountryData($_POST['arguments'][0]);
        break;
    case 'restCountriesData':
        $result=restCountriesData($_POST['arguments'][0]);
        break;
    case 'getExchange':
        $result=getExchange($_POST['arguments'][0]);
        break;
    case 'getWeather':
        $result=getWeather(($_POST['arguments'][0]),($_POST['arguments'][1]));
        break;
    case 'fillSelect':
        $result=getCountries();
        break;
    case 'getAltData':
        $result=getAltName(($_POST['arguments'][0]));
        break;
    case 'getAllData':
        $result=getAllData(($_POST['arguments'][0]));
        break;
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);



?>