<?php
//Establish connection to MySQL database
	$path = $_SERVER['DOCUMENT_ROOT'];
	$path .= "/conn.php";
	include_once($path);

//Fetch all of cities in the "IM2C" database
	$query = $db->prepare("SELECT * FROM IM2C");
	$query->execute();
	$rows = $query->fetchAll();

//Retrieve geocoding information (latitude and longitude) of the cities
	foreach ($rows as $a)
	{
		$city = $a["city"]; //city name
		//Call Google geocoding API and retrieve json object
		$json = "https://maps.googleapis.com/maps/api/geocode/json?address=$city&key=API_KEY";
		$jsonfile = file_get_contents($json);

		//Decode json file
		$decoded = json_decode($jsonfile);
		$results = $decoded->results;
		$obj = $results[0];
		$lat = $obj->geometry->location->lat; //latitude
		$long = $obj->geometry->location->lng; //longitude

		//Upload the coordinates into IM2C database
		$import = $db->prepare("UPDATE IM2C SET `long` = '$long', `lat` = '$lat' WHERE city = '$city';");
		$import->execute();
	}
?>
