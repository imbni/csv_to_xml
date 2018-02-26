<?php
function google_maps_search($address, $key = '') {
    $url = sprintf('https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s', urlencode($address), urlencode($key));
    $response = file_get_contents($url);
    $data = json_decode($response, 'true');
    return $data;
}
function map_google_search_result($geo) {
    if (empty($geo['status']) || $geo['status'] != 'OK' || empty($geo['results'][0])) {
        return null;
    }
    $data = $geo['results'][0];
    $postalcode = '';
    foreach ($data['address_components'] as $comp) {
        if (!empty($comp['types'][0]) && ($comp['types'][0] == 'postal_code')) {
            $postalcode = $comp['long_name'];
            break;
        }
    }
    $location = $data['geometry']['location'];
    $formatAddress = !empty($data['formated_address']) ? $data['formated_address'] : null;
    $placeId = !empty($data['place_id']) ? $data['place_id'] : null;

    $result = [
        'lat' => $location['lat'],
        'lng' => $location['lng'],
        'postal_code' => $postalcode,
        'formated_address' => $formatAddress,
        'place_id' => $placeId,
    ];
    return $result;
}
function csv_to_array($filename = '', $delimiter = ',') {
    if (!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $header = FALSE;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            if (!$header)
                $header = $row;
            else
                $data[] = array_combine($header, $row);
        }
        fclose($handle);
    }
    return $data;
}

class Location {

    public $name;
    public $lat;
    public $lng;
    public $category;
    public $address;
    public $address2;
    public $city;
    public $state;
    public $postal;
    public $country;
    public $phone;
    public $email;
    public $web;
    public $hours1;

    function __construct(
    $name, $lat, $lng, $category, $address, $address2, $city, $state, $postal, $country, $phone, $email, $web, $hours1) {
        $this->name = $name;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->category = $category;
        $this->address = $address;
        $this->address2 = $address2;
        $this->city = $city;
        $this->state = $state;
        $this->postal = $postal;
        $this->country = $country;
        $this->phone = $phone;
        $this->email = $email;
        $this->web = $web;
        $this->hours1 = $hours1;
    }

}

$locations = array();
$locs = csv_to_array('locations.csv');
$show_content = FALSE;
$googleKey = 'AIzaSyBfBNhENOS2Ezckf7tzN0GHBoQObaFU8RI';
foreach ($locs as $item) {
    $first_value = reset($item);
    $first_key = key($item);
    $geoData = google_maps_search($item['location_address'], $googleKey);
    if (!$geoData) {
        $mapData['lat'] = '-';
        $mapData['lng'] = '-';
    } else {
        $mapData = map_google_search_result($geoData);
    }
    array_push($locations, new Location(
            $item['location_name'], $mapData['lat'], $mapData['lng'], 'category', $item['location_address'], $item['location_address'], '', /* city */ '', /* state */ '', /* postal */ 'CA', $item['location_phone'], 'email', $item['﻿location_slug'], $item['location_hours']
    ));
}
$xmlDoc = new DOMDocument();
$root = $xmlDoc->appendChild(
        $xmlDoc->createElement("markers"));
foreach ($locations as $tut) {
    $tutTag = $root->appendChild(
            $xmlDoc->createElement("marker"));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("name"))->appendChild(
            $xmlDoc->createTextNode($tut->name));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("lat"))->appendChild(
            $xmlDoc->createTextNode($tut->lat));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("lng"))->appendChild(
            $xmlDoc->createTextNode($tut->lng));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("category"))->appendChild(
            $xmlDoc->createTextNode($tut->category));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("address"))->appendChild(
            $xmlDoc->createTextNode($tut->address));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("address2"))->appendChild(
            $xmlDoc->createTextNode($tut->address2));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("city"))->appendChild(
            $xmlDoc->createTextNode($tut->city));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("state"))->appendChild(
            $xmlDoc->createTextNode($tut->state));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("postal"))->appendChild(
            $xmlDoc->createTextNode($tut->postal));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("country"))->appendChild(
            $xmlDoc->createTextNode($tut->country));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("phone"))->appendChild(
            $xmlDoc->createTextNode($tut->phone));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("email"))->appendChild(
            $xmlDoc->createTextNode($tut->email));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("web"))->appendChild(
            $xmlDoc->createTextNode($tut->web));
    $tutTag->appendChild(
            $xmlDoc->createAttribute("hours1"))->appendChild(
            $xmlDoc->createTextNode($tut->hours1));
}

header("Content-Type: text/plain");
$xmlDoc->formatOutput = true;
$xmlDoc->saveXML();
echo utf8_encode($xmlDoc->saveXML());


