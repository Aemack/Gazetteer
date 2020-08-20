//Global Variables
var mymap;
var countryData = {};
var currentLocation = {};
var layerGroup;
var markers;
var lastCountry;
var airports = L.layerGroup()
var overlays;
var layersControl;
//Checks for geolocation/runs fillSelect/loads map
window.onload = function(){
        fillSelect()
        loadMap()
        createTable()
        updateExchRates()

        
}

//Gets countries from Database/API, calls 
function fillSelect() {
    jQuery.ajax({
        type: "POST",
        url: 'php/gazetteer.php',
        dataType: 'json',
        data: {functionname: 'fillSelect'},
        success: fillSelectElem
    })
}

//Calls getCountryData with current lat and long
function currentLocationClicked(){
    getCountryData(currentLocation.lat,currentLocation.lng)
}

//Remove's output elements
function clearOutput(){
    outputs = document.querySelectorAll(".output")
    outputs.forEach((output)=>{
        output.remove()
    })
}

//Initialises Map
function loadMap(coords){
    
    
    mymap = L.map('mapid',{
        layers:[airports]
    }).setView([0, 0], 3)
    

    const attribution = 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>';
    const tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    const tiles = L.tileLayer(tileUrl, { attribution });
    tiles.addTo(mymap);


    mymap.invalidateSize()
}

//Loads location onto map
function newMap(lat, lng){
    mymap.flyTo([lat,lng],3)
}

//Gets POI data from API and calls showAPI
function getPOI(){
    jQuery.ajax({
        type: "POST",
        url: 'php/gazetteer.php',
        dataType: 'json',
        data: {functionname: 'getPOI', arguments: [countryData.ISOa2]},
        success: showPOI,
        error: (e)=>{
            console.log(e)
        }
    })    
}

//Clears map layer then POI puts markers onto new layer 
function showPOI(obj){
    if (layersControl){
        mymap.removeLayer(airports)
        mymap.removeLayer(zoos)
        mymap.removeLayer(gallerys)
        layersControl.remove()
    }
    
    
    searchTerms = ["airport","museum","zoo","gallery"];

    var airportIcon = L.icon({
        iconUrl: 'img/airport.png',
    
        iconSize:     [32, 37], // size of the icon
        iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
        popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    });

    var galleryIcon = L.icon({
        iconUrl: 'img/gallery.png',
    
        iconSize:     [32, 37], // size of the icon
        iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
        popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    });

    var zooIcon = L.icon({
        iconUrl: 'img/zoo.png',
    
        iconSize:     [32, 37], // size of the icon
        iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
        popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    });

    var museumIcon = L.icon({
        iconUrl: 'img/museum.png',
    
        iconSize:     [32, 37], // size of the icon
        iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
        popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    });


    
    airports = L.markerClusterGroup()
    obj.airport.results.forEach((poi)=>{
        L.marker([poi.position.lat, poi.position.lon], {icon: airportIcon}).bindPopup(poi.poi.name).addTo(airports);    
    })

    
    gallerys = L.markerClusterGroup()
    obj.gallery.results.forEach((poi)=>{
        L.marker([poi.position.lat, poi.position.lon], {icon: galleryIcon}).bindPopup(poi.poi.name).addTo(gallerys);    
    })

    
    museums = L.markerClusterGroup()
    obj.museum.results.forEach((poi)=>{
        L.marker([poi.position.lat, poi.position.lon], {icon: museumIcon}).bindPopup(poi.poi.name).addTo(museums);    
    })

    
    zoos = L.markerClusterGroup()
    obj.zoo.results.forEach((poi)=>{
        L.marker([poi.position.lat, poi.position.lon], {icon: zooIcon}).bindPopup(poi.poi.name).addTo(zoos);    
    })

    
    var overlays = {
        "Aiports":airports,
        "Zoos":zoos,
        "Gallerys":gallerys,
        "Museums":museums
    }

    layersControl = L.control.layers(overlays).addTo(mymap)

}

//Populates the select with options
function fillSelectElem(obj){
    obj.sort()
    select = document.getElementById("countryQuery")
    for (i =0; i < obj.length;i++){
        if (!obj[i]){
            continue;
        }
        
        option = document.createElement("option")
        option.value = obj[i]
        option.text = obj[i]
        option.classList.add("dropdown-item")
        option.setAttribute("data-toggle","modal")
        option.setAttribute("data-target","#exampleModal")
        option.href="#"
        select.appendChild(option)
    }

    
    lastCountry = localStorage.getItem('country');
    if (navigator.geolocation){
        navigator.geolocation.getCurrentPosition((position)=>{
            currentLocation.lat=position.coords.latitude.toFixed(4);
            currentLocation.lng=position.coords.longitude.toFixed(4);
            if (!lastCountry){
                getCountryData(currentLocation.lat,currentLocation.lng)
            } else {
                $('#countryQuery').val(lastCountry);
                getCountryData()
            }
             })
             } else if (lastCountry) {
                 $('#countryQuery').val(lastCountry);
        }
    

}

//Get GEOJSON country data and apply to map layer 
function applyCountryBorder(countryname) {
    if (layerGroup){
        mymap.removeLayer(layerGroup);
    }
    jQuery.ajax({
        type: "GET",
        dataType: "json",
        url:
          "https://nominatim.openstreetmap.org/search?country=" +
          countryname.trim() +
          "&polygon_geojson=1&format=json"
      })
      .then(function(data) {
          var bounds = new L.LatLngBounds([data[0].boundingbox[0],data[0].boundingbox[2]],[data[0].boundingbox[1],data[0].boundingbox[3]]);
          mymap.fitBounds(bounds)
        layerGroup = new L.LayerGroup();
        layerGroup.addTo(mymap);
        L.geoJSON(data[0].geojson, {
          color: "blue",
          weight: 1,
          opacity: 1,
          fillOpacity: 0.5 
        }).addTo(layerGroup);
      });
  }



//Gets all country data and runs displaying function 
function getCountryData(lat,lng){
        $("#mainOutput").addClass('d-none');

        $("#countryButton").addClass('d-none'); 
        $("#currencyButton").addClass('d-none'); 
        $("#weatherButton").addClass('d-none');

            
        collapseClicked({id:0})
        



        //$("#modalFooter").hide()
        $("#loadingImage").show()

        $("#exampleModal").modal('toggle')
        
        
        clearOutput()


        if (!lat){
            document.querySelector(".modal-title").innerText = $("#countryQuery").val();
            country = $("#countryQuery").val()
            jQuery.ajax({
                type: "POST",
                url: 'php/gazetteer.php',
                dataType: 'json',
                data: {functionname: 'getData', arguments: [country]},
                success: outputData                
            })    
        } else {
            document.querySelector(".modal-title").innerText = "Current Location";
            latLong = lat+","+lng
            jQuery.ajax({
                type: "POST",
                url: 'php/gazetteer.php',
                dataType: 'json',
                data: {functionname: 'getAPIData', arguments: [latLong]},
                success: outputData                
            })

        }
            
}

//Outputs data into modal, creating elements and clearing the old
function outputData(obj){
    



    countryData = obj;
    console.log(typeof(countryData.currency.exchRate))
    countryData.currency.exchRate = parseFloat(countryData.currency.exchRate).toFixed(2)
    
    countryNameValue = $("#countryQuery").val();
    localStorage.clear();
    localStorage.setItem('country',countryNameValue)
    if (currentLocation.lat || currentLocation.lng){
        var homeIcon = L.icon({
            iconUrl: 'img/home.png',
           
            iconSize:     [32, 37], // size of the icon
            iconAnchor:   [0, 0], // point of the icon which will correspond to marker's location
            popupAnchor:  [0, 0] // point from which the popup should open relative to the iconAnchor
        });
        var marker = L.marker([currentLocation.lat, currentLocation.lng],{icon:homeIcon}).addTo(mymap);
        marker.bindPopup("YOU ARE <br><b>HERE</b>")
    }
    newMap(obj.geometry.lat,obj.geometry.lng)
    $("#loadingImage").hide()
    $("#mainOutput").removeClass('d-none')


    //$("#modalFooter").show()
    if (countryData.currency.name){
        $("#currencyButton").removeClass('d-none');
    } 
    $("#countryButton").removeClass('d-none'); 
    $("#weatherButton").removeClass('d-none'); 

    applyCountryBorder(countryData.country)
    mymap.invalidateSize()

    

    getPOI();
    if( document.querySelector(".modal-title").innerText !== countryData.country){
        document.querySelector(".modal-title").innerText = document.querySelector(".modal-title").innerText +" / "+countryData.country;
    }
    flagImage = document.createElement("img");
    flagImage.classList.add("output");
    flagImage.classList.add("flag");
    flagImage.classList.add("card-body");
    flagImage.classList.add("mx-auto");
    flagImage.classList.add("d-block");
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
    currencyName.innerHTML ="Currency Name "+countryData.currency.name

    
    currencyEx = document.createElement("h5");
    currencyEx.classList.add("output");
    currencyEx.classList.add("card-body");
    if (countryData.currency.symbol_first){
        currencyEx.innerText =`Exchange Rate ${countryData.currency.symbol}${countryData.currency.exchRate}`+ " = 1 USD"
    } else {
        currencyEx.innerText =`Exchange Rate ${countryData.currency.exchRate}${countryData.currency.symbol}`+" = 1 USD"
    }

    
    
    weatherElems = document.getElementById("weatherData").children


    for (i=0;i<weatherElems.length;i++){
        date = (countryData.weather[i*10].dt_txt).split(" ")
        dayray = date[0].split("-")
        day = dayray[2]+"/"+dayray[1]+'<br>'
        weatherElems[i].innerHTML = day;
        switch (countryData.weather[i*10].weather[0].main){
            case "Clear":
                weatherElems[i].innerHTML += '<i class="fas fa-sun"></i>'
                break;
            case "Clouds":
                weatherElems[i].innerHTML += '<i class="fas fa-cloud"></i>'
                break;
            case "Rain":
                weatherElems[i].innerHTML += '<i class="fas fa-cloud-rain"></i>'
                break;
            case "Snow":
                weatherElems[i].innerHTML += '<i class="fas fa-snowman"></i>'
                break;
        }
        temp = (countryData.weather[i*10].main.temp - 273.15).toFixed(1)
        weatherElems[i].innerHTML += '<br>'+temp+"&#8451"
        if (temp < 5){
            weatherElems[i].style.background="#70cdfc";
        } else if (temp < 10){
            weatherElems[i].style.background="#b7feec";
        } else if (temp < 15){
            weatherElems[i].style.background="#e3ffb4";
        } else if (temp < 20){
            weatherElems[i].style.background="#ffd736";
        } else if (temp < 25){
            weatherElems[i].style.background="#ff9700";
        } else if (temp < 35){
            weatherElems[i].style.background="#fe4d01";
        }else {
            weatherElems[i].style.background="#fb1f00";
        }
        
    }

    document.querySelector("#imageContainer").appendChild(flagImage)
    document.querySelector("#countryData").appendChild(capitalName)
    document.querySelector("#countryData").appendChild(population)
    document.querySelector("#currencyData").appendChild(currencyName)
    document.querySelector("#currencyData").appendChild(currencyEx)
    
    $("#collapseCountry").addClass('show')
    
    updateTable();
    
    
    
}

//Updates Main countyData table
function updateTable(){
    jQuery.ajax({
        type: "POST",
        url: 'php/gazetteer.php',
        dataType: 'json',
        data: {functionname: 'updateTable', arguments: [countryData.ISOa2,countryData.ISOa3, countryData.capital, countryData.country, countryData.currency.exchRate, countryData.currency.iso, countryData.currency.name, countryData.currency.symbol, countryData.currency.symbol_first, countryData.flag, countryData.geometry.lat, countryData.geometry.lng, countryData.population]},
        success: function(obj){
            console.log(obj)
        }
        })

}

//Updates exchange rates
function updateExchRates(){
    jQuery.ajax({
        type: "POST",
        url: 'php/gazetteer.php',
        dataType: 'json',
        data: {functionname: 'updateExchRates'},
        success: function(obj){
            console.log(obj)
        }
    })
}


//Creates the main table
function createTable(){
    jQuery.ajax({
        type: "POST",
        url: 'php/gazetteer.php',
        dataType: 'json',
        data: {functionname: 'createTable'},
        success: function(obj){
            console.log(obj)
        }
    })
}

//Collapses other buttons when clicked
function collapseClicked(obj){
    if (obj.id == "currencyButton"){
        $("#collapseWeather").collapse('hide')
        $("#collapseCountry").collapse('hide')
    }else if (obj.id == "countryButton"){
        $("#collapseWeather").collapse('hide')
        $("#collapseCurrency").collapse('hide')
    } else if (obj.id == "weatherButton"){
        $("#collapseCountry").collapse('hide')
        $("#collapseCurrency").collapse('hide')
    } else {
        $("#collapseWeather").collapse('hide')
        $("#collapseCountry").collapse('hide')        
        $("#collapseCurrency").collapse('hide')
    }
}