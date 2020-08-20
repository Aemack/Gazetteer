<?php
/*
$result = array();
$dbName = "id14621135_countryinfo";
$dbUser = "id14621135_aemac";
$dbHost = "localhost";
$dbPass = "/O8+a?xn&>U6m9xy";
*/

$result = array();
$dbName = "countryinfo";
$dbUser = "root";
$dbHost = "localhost";
$dbPass = "";


//Creates main country table
function createTable(){
        
    global $dbName, $dbUser, $dbHost, $dbPass;

    $link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
    if($link === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }
    $sql = "CREATE TABLE IF NOT EXISTS countryData (
        iso3 VARCHAR(3) NOT NULL PRIMARY KEY,
        iso2 VARCHAR(2),
        name VARCHAR(50) NOT NULL,
        capital VARCHAR(50) NOT NULL,
        pop INT NOT NULL,
        currencyName VARCHAR(50),
        currencySymbol VARCHAR(10),
        curISO VARCHAR(5),
        exchRate DECIMAL(10,8),
        symbolFirst BOOLEAN DEFAULT 0,
        lat DECIMAL(15,10) NOT NULL,
        lng DECIMAL(15,10) NOT NULL,
        flag VARCHAR(50),
        dateUpdated BIGINT(30) NOT NULL
    )";
    if(mysqli_query($link, $sql)){
        echo "Database created successfully";
    } else{
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
    }

    
    
    
    mysqli_close($link);
}

//Creates exchange rate table 
function createExchTable(){
    

    global $dbName, $dbUser, $dbHost, $dbPass;

    $link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
    
    if($link === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    $sql = "CREATE TABLE IF NOT EXISTS exchRates (
        iso3 VARCHAR(3) NOT NULL PRIMARY KEY,
        exchRate VARCHAR(50),
        dateUpdated BIGINT(30)
    )";

    if(mysqli_query($link, $sql)){
        echo "Database created successfully";
    } else{
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
    }

    

    mysqli_close($link);
}

//Creates country names table and populates it 
function createCountryTable(){
    
    global $dbName, $dbUser, $dbHost, $dbPass;

  

    $link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
    
    if($link === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    $sql = "CREATE TABLE IF NOT EXISTS countryNames (
        name VARCHAR(50) NOT NULL PRIMARY KEY
    )";

    if(mysqli_query($link, $sql)){
        echo "Database created successfully";
    } else{
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
    }
    
    

    $r = getCountriesFromAPI();







    
    
    for ($i=0; $i < count($r) ;$i++){

        $sql = "INSERT INTO countryNames(name) VALUES('$r[$i]')";

        if(mysqli_query($link, $sql)){
            echo "Records added successfully.";
        } else{
            echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
        }
    
    }



    

    mysqli_close($link);

}

//Updates exchange row in main table if last updated over 24 hours ago
function updateTable($iso2,$iso3,$capital,$country,$exchRate,$curIso,$curName,$curSymbol,$curSymbolFirst,$flag,$lat,$lng,$pop){
    createTable();

        
    global $dbName, $dbUser, $dbHost, $dbPass;

    $link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
    $capital = str_replace("'","\'", $capital);
    $country = str_replace("'","\'", $country);

    if($link === false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }

    if ($curSymbolFirst){
        $currencySymbolFirst = 1;
    } else {
        $currencySymbolFirst = 0;
    }



    $sql = "SELECT dateUpdated FROM countryData WHERE name='$country'";
    $res = mysqli_query($link, $sql);
    $row = mysqli_fetch_assoc($res);

    $date = time();


    if ($date - $row["dateUpdated"] < 86400 || !$row){


        if ($curSymbol){
            $date = time();
            $sql = "INSERT INTO countryData (name, iso2, iso3, capital, flag, pop, currencyName, curISO, currencySymbol, symbolFirst, exchRate, lat, lng, dateUpdated) 
            VALUES ('$country', '$iso2', '$iso3', '$capital', '$flag', '$pop', '$curName', '$curIso', '$curSymbol', '$currencySymbolFirst', '$exchRate','$lat','$lng','$date')
            ON DUPLICATE KEY 
            UPDATE name=VALUES(name), iso2=VALUES(iso2), iso3=VALUES(iso3), capital=VALUES(capital), flag=VALUES(flag), pop=VALUES(pop), currencyName=VALUES(currencyName), curISO=VALUES(curISO), currencySymbol=VALUES(currencySymbol), symbolFirst=VALUES(symbolFirst), exchRate=VALUES(exchRate), lat=VALUES(lat), lng=VALUES(lng), dateUpdated=VALUES(dateUpdated)";
        } else {
            $date = time();
            $sql = "INSERT INTO countryData (name, iso2, iso3, capital, flag, pop, lat, lng, dateUpdated) 
            VALUES ('$country', '$iso2', '$iso3', '$capital', '$flag', '$pop','$lat','$lng','$date')
            ON DUPLICATE KEY 
            UPDATE name=VALUES(name), iso2=VALUES(iso2), iso3=VALUES(iso3), capital=VALUES(capital), flag=VALUES(flag), pop=VALUES(pop), lat=VALUES(lat), lng=VALUES(lng), dateUpdated=VALUES(dateUpdated)";
                
        }
        if(mysqli_query($link, $sql)){
            echo "Records added successfully.";
        } else{
            echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
        }
    }
        
    // Close connection
    mysqli_close($link);
}

//Updates all exchange rates if not updated in the past 24 hours
function updateExchRates(){
    
    global $dbName, $dbUser, $dbHost, $dbPass;

     createExchTable(); 

     $now = time();

    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT dateUpdated FROM exchRates";
    $res = mysqli_query($conn, $sql);

    $row = mysqli_fetch_assoc($res);
    $lastUpdate = $row["dateUpdated"];

    $now = time();

    if ($now - $lastUpdate > 86400){

        $exchData = getExchData();
        
        $link = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

        if($link === false){
            die("ERROR: Could not connect. " . mysqli_connect_error());
        }

        

        foreach ($exchData as $key => $value) {
            echo "$key => $value\n";
            $sql = "INSERT INTO exchRates (iso3,exchRate,dateUpdated)
            VALUES ('$key','$value','$now')
            ON DUPLICATE KEY 
            UPDATE iso3=VALUES(iso3), exchRate=VALUES(exchRate), dateUpdated=VALUES(dateUpdated)";
                if(mysqli_query($link, $sql)){
                    echo "Records added successfully.";
                } else{
                    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
                }
        }
                    // Close connection
                    mysqli_close($link);
    }
}

//Gets Points of Interest details
function getPOI($iso2)
{

    $searchTerms = ["airport","museum","zoo","gallery"];

    foreach ($searchTerms as $term){
        $curl = curl_init("https://api.tomtom.com/search/2/search/".$term.".json?limit=75&countrySet=".$iso2."&idxSet=POI&key=MvEgAEbEToe0ItOFpa7TwDnAHJVB1CuA");

        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $json_result = curl_exec($curl);

        
        $r = json_decode($json_result, true);

        $result[$term] = $r;
    }
    
    return $result;

}

//Checks if database data is recent then either calls getDatabaseData or getAPIData
function getData($name){
    

        
    global $dbName, $dbUser, $dbHost, $dbPass;

    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT * FROM countryData WHERE name='$name'";
    $res = mysqli_query($conn, $sql);
    

    if(mysqli_num_rows($res) > 0){
        
            $row = mysqli_fetch_assoc($res);
            
            $lastUpdate = $row["dateUpdated"];
            $now = time();

            
            if($now - $lastUpdate < 86400){    
                $result = getDatabaseData($name);
            } else {
            $result = getAPIData($name);
            }
    } else {
        $result = getAPIData($name);
    }
    
    mysqli_close($conn);
    return $result;
}

//Gets data from various APIs and returns as object (works with lat,long and country name)
function getAPIData($name){
        
    global $dbName, $dbUser, $dbHost, $dbPass;

    $name = str_replace(" ", "%20", $name);
    $curl = curl_init("https://api.opencagedata.com/geocode/v1/json?q=".$name."&key=b31118c454714ac5bff8f1535317f621");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);

    
    $r = json_decode($json_result, true);
    if (array_key_exists("currency",$r["results"][0]["annotations"])){
        $result["result"]["currency"]["symbol_first"] = $r["results"][0]["annotations"]["currency"]["symbol_first"];
        $result["result"]["currency"]["symbol"] = $r["results"][0]["annotations"]["currency"]["symbol"];
        $result["result"]["currency"]["name"] = $r["results"][0]["annotations"]["currency"]["name"];
        $result["result"]["currency"]["iso"] = $r["results"][0]["annotations"]["currency"]["iso_code"];
    }
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

    if (array_key_exists("currency",$result["result"])){
        $curIso = $result["result"]["currency"]["iso"];
    }
    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT exchRate FROM exchRates";
    $res = mysqli_query($conn, $sql);

    $row = mysqli_fetch_assoc($res);
    $result["result"]["currency"]["exchRate"]= $row["exchRate"];
    

    mysqli_close($conn);




    

    $curl = curl_init("https://api.openweathermap.org/data/2.5/forecast?lat=".$result["result"]["geometry"]["lat"]."&lon=".$result["result"]["geometry"]["lng"]."&cnt=40&appid=c0a8cf4628667898c6a3d913189f3596");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    
    $r = json_decode($json_result, true);
    $i=0;
    foreach( $r["list"] as $day){
        if ($i%10==0){
           $result["result"]["weather"][$i]=$day;
        }
        $i++;
    }

    $now = time()*1000;

    return $result["result"];

}

//Gets data from database and returns an object
function getDatabaseData($name){
        
    
    global $dbName, $dbUser, $dbHost, $dbPass;
    
    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT * FROM countryData WHERE name='$name'";
    $res = mysqli_query($conn, $sql);

    $row = mysqli_fetch_assoc($res);
    $result["result"]["country"] = $row["name"];
    $result["result"]["capital"] = $row["capital"];
    $result["result"]["ISOa2"] = $row["iso2"];
    $result["result"]["ISOa3"] = $row["iso3"];
    $result["result"]["population"] = $row["pop"];    
    $result["result"]["currency"]["name"] = $row["currencyName"];
    $result["result"]["currency"]["symbol"] = $row["currencySymbol"];
    $result["result"]["currency"]["symbol_first"] = $row["symbolFirst"];
    $result["result"]["currency"]["iso"] = $row["curISO"];
    $result["result"]["flag"] = $row["flag"];
    $result["result"]["geometry"]["lat"] = $row["lat"];
    $result["result"]["geometry"]["lng"] = $row["lng"];
    

    
    $curl = curl_init("https://api.openweathermap.org/data/2.5/forecast?lat=".$result["result"]["geometry"]["lat"]."&lon=".$result["result"]["geometry"]["lng"]."&cnt=40&appid=c0a8cf4628667898c6a3d913189f3596");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    
    $r = json_decode($json_result, true);
    $i=0;
    foreach($r["list"] as $day){
        if ($i%10==0){
           $result["result"]["weather"][$i]=$day;
        }
        $i++;
    }
    
    

    $curISO = $result["result"]["currency"]["iso"];

    $sql = "SELECT exchRate FROM exchRates WHERE iso3='$curISO'";
    $res = mysqli_query($conn, $sql);

    $row = mysqli_fetch_assoc($res);
    $result["result"]["currency"]["exchRate"]= $row["exchRate"];
    

    mysqli_close($conn);

    return $result["result"];

}

//Gets all exchange rate data from API
function getExchData(){
    $curl = curl_init("https://openexchangerates.org/api/latest.json?app_id=5eeb7bdfb5d94387a56d7dcd9413b55f");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    
    $r = json_decode($json_result, true);
    $results = $r["rates"];
    return $results;
}

//Gets list of countries from API and returns array
function getCountriesFromAPI(){
    $curl = curl_init("https://restcountries.eu/rest/v2/all");

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain; charset=UTF-8'));
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $json_result = curl_exec($curl);
    $i=0;
    $r = json_decode($json_result, true);
    forEach($r as $country){
        $results[$i] = $country["name"];
        $i++;
    }
    return $results;
}

//Gets list of country names from database and returns array
function getCountriesFromDB(){
    
    global $dbName, $dbUser, $dbHost, $dbPass;
    
    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }

    $sql = "SELECT name FROM countryNames";
    $res = mysqli_query($conn, $sql);

    $i=0;

    while($row = mysqli_fetch_assoc($res)){
        $result[$i] = $row['name'];
        $i++;
    }
    
    mysqli_close($conn);
    return $result;
}

//Checks if countryName table exists and creates it if not, calls createCountryTable, and then retrieves list from database
function getCountries(){

    global $dbName, $dbUser, $dbHost, $dbPass;

    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

    $sql = "SELECT name FROM countryNames";
    $val = mysqli_query($conn, $sql);

    if(!$val)
    {
        createCountryTable();
    } 
    $r = getCountriesFromDB();
    
    
    for ($i=0; $i < count($r) ;$i++){
     
        $result[$i] = $r[$i];
    }



    
    
    return $r;

    
}

switch($_POST['functionname']) {
    case 'fillSelect':
        $result=getCountries();
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
    case 'getAPIData':
        $result=getAPIData(($_POST['arguments'][0]));
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
    case 'getDatabaseData':
        $result=getDatabaseData(($_POST['arguments'][0]));
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
    case 'createTable':
        createTable();
        break;
    case 'updateTable':
        updateTable(($_POST['arguments'][0]),($_POST['arguments'][1]),($_POST['arguments'][2]),($_POST['arguments'][3]),($_POST['arguments'][4]),($_POST['arguments'][5]),($_POST['arguments'][6]),($_POST['arguments'][7]),($_POST['arguments'][8]),($_POST['arguments'][9]),($_POST['arguments'][10]),($_POST['arguments'][11]),($_POST['arguments'][12]));
        break;
    case 'updateExchRates':
        updateExchRates();
        break;
    case 'getData':
        $result = getData($_POST['arguments'][0]);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
    case 'getPOI':
        $result = getPOI(($_POST['arguments'][0]));
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
}

?>