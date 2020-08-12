var mymap;
countryData = {};

function clearOutput(){
    $("#error").hide()
    outputs = document.querySelectorAll(".output")
    outputs.forEach((output)=>{
        output.remove()
    })
}


function logData(obj){
    console.log(obj)
}

function getCountryData(lat,lng){
        document.querySelector(".modal-title").innerText = $("#countryQuery").val();
        $("#loadingImageMod").hide();
        $("#mapid").hide()
        $("#loadingImage").show()
        
        clearOutput()
        if (!lat && !lng){
            country = $("#countryQuery").val().split(" ").join("%20");
            if (country === "Palestine"){
                obj = {
                    ISoa2:"PS",
                    ISOa3:"PSE",
                    capital:"Jerusalem",
                    country:"Palestinian Territory",
                    geometry:{lat:31.2752047,lng:34.2558269},
                    currency:{
                        iso:"EGP",
                        name:"Egyptian pound"
                    }
    
                }
                restCountriesData(obj)
            }else{
            jQuery.ajax({
                type: "POST",
                url: 'php/gazetteer.php',
                dataType: 'json',
                data: {functionname: 'getAllData', arguments: [country]},
                success: logData,
                error:function(){
                    console.log("Could not locate")
                    jQuery.ajax({
                        type: "POST",
                        url: 'php/gazetteer.php',
                        dataType: 'json',
                        data: {functionname: 'getAltData', arguments: [country]},
                        success: restCountriesData,
                        error: ()=>{console.log("Error: "+country)}
                    })
                }
            })}
        } else {
            latlng = lat+","+lng
            jQuery.ajax({
                type: "POST",
                url: 'php/gazetteer.php',
                dataType: 'json',
                data: {functionname: 'getCountryData', arguments: [latlng]},
                success: restCountriesData,
                error:function(){
                    
                    console.log("ERRUR: CUD NUT FIND CUNTRY")
                }
            })
        }
    }


window.onload = function(){
    if (navigator.geolocation){

        jQuery.ajax({
            type: "POST",
            url: 'php/gazetteer.php',
            dataType: 'json',
            data: {functionname: 'fillSelect'},
            success: fillSelect,
            error:function(){
                console.log("Error loading country list")
                $("#error").show()
                $("#error").innerText="Could Not Load Country List"
            }
        })
    
        loadMap();
        $("#loadingImage").hide();
        $("#mapid").show(); 


    }
}

function fillSelect(obj){
    console.log(obj)
    for (i =0; i < obj.length;i++){
        option = document.createElement("option")
        option.value = obj[i]
        option.text = obj[i]
        option.classList.add("dropdown-item")
        option.href="#"
        select = document.getElementById("countryQuery")
        select.add(option)
    }
}

//Initialises Map
function loadMap(lat,long){
    
    mymap = L.map('mapid').setView([0, 0], 3)
    
    const attribution = 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>';
    const tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    const tiles = L.tileLayer(tileUrl, { attribution });
    tiles.addTo(mymap);
    mymap.invalidateSize()
}
//Loads location onto map
function newMap(lat, lng){
    mymap.panTo(new L.LatLng(lat, lng));
}
/*
//Gets Country/Lat/Long/ISOCodes/CurrencyName/CurrencyISOCode/
function getCountryData(lat,lng){
    document.querySelector(".modal-title").innerText = $("#countryQuery").val();
    $("#loadingImageMod").hide();
    $("#mapid").hide()
    $("#loadingImage").show()
    
    clearOutput()
    if (!lat && !lng){
        country = $("#countryQuery").val().split(" ").join("%20");
        if (country === "Palestine"){
            obj = {
                ISoa2:"PS",
                ISOa3:"PSE",
                capital:"Jerusalem",
                country:"Palestinian Territory",
                geometry:{lat:31.2752047,lng:34.2558269},
                currency:{
                    iso:"EGP",
                    name:"Egyptian pound"
                }

            }
            restCountriesData(obj)
        }else{
        jQuery.ajax({
            type: "POST",
            url: 'php/gazetteer.php',
            dataType: 'json',
            data: {functionname: 'getCountryData', arguments: [country]},
            success: restCountriesData,
            error:function(){
                console.log("Could not locate")
                jQuery.ajax({
                    type: "POST",
                    url: 'php/gazetteer.php',
                    dataType: 'json',
                    data: {functionname: 'getAltData', arguments: [country]},
                    success: restCountriesData,
                    error: ()=>{console.log("Error: "+country)}
                })
            }
        })}
    } else {
        latlng = lat+","+lng
        jQuery.ajax({
            type: "POST",
            url: 'php/gazetteer.php',
            dataType: 'json',
            data: {functionname: 'getCountryData', arguments: [latlng]},
            success: restCountriesData,
            error:function(){
                
                console.log("ERRUR: CUD NUT FIND CUNTRY")
            }
        })
    }

} 

//Gets population/capital/flag
function restCountriesData(obj){
    countryData = obj;
    console.log(obj)
    jQuery.ajax({
        type: "POST",
        url: 'php/gazetteer.php',
        dataType: 'json',
        data: {functionname: 'restCountriesData', arguments: [obj.ISOa3]},
        success: getExchange,
        error:function(){
            
            console.log("Error: Couldn't get pop/capi/flag")
        }
    })

}

//Gets Exchange rate
function getExchange(obj){
    countryData.capital = obj.capital;
    countryData.flag = obj.flag;
    countryData.population = obj.population;


    jQuery.ajax({
        type: "POST",
        url: 'php/gazetteer.php',
        dataType: 'json',
        data: {functionname: 'getExchange', arguments: [countryData.currency.iso]},
        success: getWeather,
        error:function(){
            
            console.log("Error: Couldn't get Exch")
            getWeather({exchRate:"Could Not Retrieve Exchange Rate"})
        }
    })
      
    
}

//Get 5 day weather forecast
function getWeather(obj){
    countryData.currency.exchRate = obj.exchRate;

    jQuery.ajax({
        type: "POST",
        url: 'php/gazetteer.php',
        dataType: 'json',
        data: {functionname: 'getWeather', arguments: [countryData.geometry.lat,countryData.geometry.lng]},
        success: logData,
        error:function(){
            
            console.log("ERRUR: CUD NUT FIND CUNTRY")
        }
    })
}
*/
function logData(obj){
    countryData = obj.result;
    console.log(countryData)
    newMap(countryData.geometry.lat,countryData.geometry.lng)
    $("#mapid").show()
    mymap.invalidateSize()
    $("#loadingImage").hide()
    outputData()

}


function outputData(){ 

    
    $("#loadingImageMod").hide();

    if( document.querySelector(".modal-title").innerText !== countryData.country){
        document.querySelector(".modal-title").innerText = document.querySelector(".modal-title").innerText +" / "+countryData.country;
    }
    flagImage = document.createElement("img");
    flagImage.classList.add("output");
    flagImage.classList.add("flag");
    flagImage.src=countryData.flag;

    capitalName = document.createElement("h4");
    capitalName.classList.add("output");
    capitalName.innerText ="Capital: "+countryData.capital

    
    population = document.createElement("h4");
    population.classList.add("output");
    population.innerText ="Population: "+countryData.population

    
    currencyName = document.createElement("h4");
    currencyName.classList.add("output");
    currencyName.innerText ="Currency Name: "+countryData.currency.name

    
    currencyEx = document.createElement("h4");
    currencyEx.classList.add("output");
    currencyEx.innerText ="Exchange Rate: "+ countryData.currency.exchRate


    document.querySelector("#outputElem").appendChild(flagImage)
    document.querySelector("#outputElem").appendChild(capitalName)
    document.querySelector("#outputElem").appendChild(population)
    document.querySelector("#outputElem").appendChild(currencyName)
    document.querySelector("#outputElem").appendChild(currencyEx)

}