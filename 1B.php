<?php
//Establish connection to MySQL database
    $path = $_SERVER['DOCUMENT_ROOT'];
    $path .= "/conn.php";
    include_once($path);

//Fetch all of cities in the "IM2C" database
	$query = $db->prepare("SELECT * FROM IM2C");
	$query->execute();
	$rows = $query->fetchAll();

//Find the elevation of cities
	foreach($rows as $a)
	{
    //Pull coordinates from database
		$lat = $a["lat"];
		$long = $a["long"];

    //Call Google Maps Geocoding API and retrieve json object
		$json = "https://maps.googleapis.com/maps/api/elevation/json?locations=$lat,$long&key=API_KEY"; //API Key omitted
		$jsonfile = file_get_contents($json);

    //Unwrap json object
		$decoded = json_decode($jsonfile);
		$results = $decoded->results;
		$obj = $results[0];
		$alt = $obj->elevation //elevation object

    //Upload the elevation information into IM2C database
		$import = $db->prepare("UPDATE IM2C SET `alt` = '$alt' WHERE lat = '$lat';");
		$import->execute();
	}
?>
