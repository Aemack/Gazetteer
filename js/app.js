var mymap;
countryData = {};

//Checks for geolocation/runs fillSelect
window.onload = function(){

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
        if (navigator.geolocation){
            navigator.geolocation.getCurrentPosition(loadMap)
        } else {loadMap()}
            $("#loadingImage").hide();
        $("#mapid").show(); 



}

//Remove's output elements
function clearOutput(){
    $("#error").hide()
    outputs = document.querySelectorAll(".output")
    outputs.forEach((output)=>{
        output.remove()
    })
}

//Initialises Map
function loadMap(coords){
    
    if(coords){
        mymap = L.map('mapid').setView([coords.coords.longitude,coords.coords.longitude], 3)
    }else{
        mymap = L.map('mapid').setView([0, 0], 3)
    }
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

//Populates the select with options
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

//Gets all country data and runs displaying function 
function getCountryData(lat,lng){
        document.querySelector(".modal-title").innerText = $("#countryQuery").val();
        $("#mapid").hide()
        $("#modalFooter").hide()
        $("#loadingImage").show()
        
        clearOutput()
        if (!lat && !lng){
            country = $("#countryQuery").val().split(" ").join("%20");
            jQuery.ajax({
                type: "POST",
                url: 'php/gazetteer.php',
                dataType: 'json',
                data: {functionname: 'getAllData', arguments: [country]},
                success: outputData
                
            })    
            
        } else {
            latlng = lat+","+lng
            jQuery.ajax({
                type: "POST",
                url: 'php/gazetteer.php',
                dataType: 'json',
                data: {functionname: 'getAllData', arguments: [latlng]},
                success: outputData,
                error:function(){
                }
            })
        }
}

function outputData(obj){ 
    countryData = obj.result;
    console.log(countryData)
    newMap(countryData.geometry.lat,countryData.geometry.lng)
    $("#loadingImage").hide()
    $("#mapid").show()
    $("#modalFooter").show()
    mymap.invalidateSize()

    
    $("#loadingImageMod").hide();

    if( document.querySelector(".modal-title").innerText !== countryData.country){
        document.querySelector(".modal-title").innerText = document.querySelector(".modal-title").innerText +" / "+countryData.country;
    }
    flagImage = document.createElement("img");
    flagImage.classList.add("output");
    flagImage.classList.add("flag");
    flagImage.classList.add("card-body");
    flagImage.classList.add("mx-auto");
    flagImage.src=countryData.flag;

    capitalName = document.createElement("h4");
    capitalName.classList.add("output");
    capitalName.classList.add("card-body");
    capitalName.innerText ="Capital: "+countryData.capital

    
    population = document.createElement("h4");
    population.classList.add("output");
    population.classList.add("card-body");
    population.innerText ="Population: "+countryData.population

    
    currencyName = document.createElement("h4");
    currencyName.classList.add("output");
    currencyName.classList.add("card-body");
    currencyName.innerText ="Currency Name: "+countryData.currency.name

    
    currencyEx = document.createElement("h4");
    currencyEx.classList.add("output");
    currencyEx.classList.add("card-body");
    currencyEx.innerText ="Exchange Rate: "+ countryData.currency.exchRate


    document.querySelector("#countryData").appendChild(flagImage)
    document.querySelector("#countryData").appendChild(capitalName)
    document.querySelector("#countryData").appendChild(population)
    document.querySelector("#currencyData").appendChild(currencyName)
    document.querySelector("#currencyData").appendChild(currencyEx)

}

function collapseClicked(obj){
    if (obj.id == "currencyButton"){
        $("#countryButton").collapse()
    }else if (obj.id == "currencyButton"){
        $("#currencyButton").collapse()
    }
}