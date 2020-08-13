<?php
$result = array();

function getDirections($from,$end){
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, "https://api.openrouteservice.org/v2/matrix/driving-car");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, '{"locations":[['.$from.'],['.$end.']]}');
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Accept: application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8",
        "Authorization: 5b3ce3597851110001cf624823c917d1ecff477697fade82683b62d9",
        "Content-Type: application/json; charset=utf-8"
    ));
    
    $result = curl_exec($curl);
    curl_close($curl);

    
    $r = json_decode($result, true);
    
    return $r;

}

function getAllData($name){
    $curl = curl_init("https://api.opencagedata.com/geocode/v1/json?q=".$name."&key=b31118c454714ac5bff8f1535317f621");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);

    
    $r = json_decode($json_result, true);
    
    $result["result"]["currency"]["symbol_first"] = $r["results"][0]["annotations"]["currency"]["symbol_first"];
    $result["result"]["currency"]["symbol"] = $r["results"][0]["annotations"]["currency"]["symbol"];
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
    $curIso = $result["result"]["currency"]["iso"];
    $result["result"]["currency"]["exchRate"]=$r["rates"][$curIso];


    

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

    $result["result"]["wiki"] = "https://en.wikipedia.org/wiki/".$result["result"]["country"];



    return $result["result"];

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

switch($_POST['functionname']) {
    case 'fillSelect':
        $result=getCountries();
        break;
    case 'getAllData':
        $result=getAllData(($_POST['arguments'][0]));
        break;
    case 'getDirections':
        $result=getDirections(($_POST['arguments'][0]),($_POST['arguments'][1]));
        break;
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);



?>