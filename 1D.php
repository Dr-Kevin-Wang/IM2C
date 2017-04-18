<?php
//Establish connection to MySQL database
	$path = $_SERVER['DOCUMENT_ROOT'];
	$path .= "/conn.php";
	include_once($path);

//Fetch all of cities in the "IM2C" database
	$query = $db->prepare("SELECT * FROM IM2C");
	$query->execute();
	$rows = $query->fetchAll();

	foreach($rows as $a)
	{
		//Retrieve timezone inherited from Program 1C as well as city list
		$tz = $a["tzName"]; //e.g. America/New_York
		$city = $a["city"];

		//Set current timezone
		date_default_timezone_set($tz);

		//Find UTC offset of timezone at specified date
		$date = new DateTime('2016-01-01'); //Depending on the date of meeting, UTC offset might differ due to daylight saving time
		$utc_offset =  $date->format('Z') / 3600; //Calculate offset using PHP's native date formatter.

		if ($tz != "")
		{
			//Uploade UTC offsets to the IM2C database
			$import = $db->prepare("UPDATE CityNx_Jan SET `utc` = '$utc_offset' WHERE city = '$city';");
			$import->execute();
		}
	}
?>
