<?php
//Establish connection to MySQL database
	$path = $_SERVER['DOCUMENT_ROOT'];
	$path .= "/conn.php";
	include_once($path);

//Fetch all of cities in the "IM2C" database
	$query = $db->prepare("SELECT * FROM IM2C");
	$query->execute();
	$rows = $query->fetchAll();

	foreach ($rows as $a)
	{
		//Pull city name, geocoordinates from database
		$city = $a["city"];
		$lat = $a["lat"];
		$long = $a["long"];

		//Initialize data
		$avgTemp = 0.0;
		$avgPressure = 0.0;
		$avgDewPt = 0.0;
		$avgPrecipitation = 0.0;
		$maxHumidity = 0.0;
		$minHumidity = 0.0;

		//Initialize counters to record the number of valid data points
		$tempCount = 0;
		$pressureCount = 0;
		$dewPtCount = 0;
		$precipitationCount = 0;
		$maxHumidityCount = 0;
		$minHumidityCount = 0;

		//Initialize timezone name
		$tzName = "";
		for ($date = 1; $date <= 31; $date++) //The first parameter is starting date, the second parameter is ending date.
		//In case of Scenario 1, the date line about should be for ($date = 10; $date <= 31; $date++)
		{
			if ($date < 10) //This ensures the correct YYYYMMDD format when date is below 10
			{
				$input = "0" . $date;
			}
			else
			{
				$input = $date;
			}

			//Contacts Weather Underground's hisorical climate API
			$json = "http://api.wunderground.com/api/API_KEY/history_201601$input/q/$lat,$long.json"; //API Key omitted
			$jsonfile = file_get_contents($json);

			//Unwrap json object
			$decoded = json_decode($jsonfile);
			$obj = $decoded->history->dailysummary;

			//Unwrap temperature of one day
			$dayTemp = $obj[0]->meantempm;
			$dayPressure = $obj[0]->meanpressurem;
			$dayDewPt = $obj[0]->meandewptm;
			$dayPrecipitation = $obj[0]->precipm;
			$dayMaxHumidity = $obj[0]->maxhumidity;
			$dayMinHumidity = $obj[0]->minhumidity;

			//Only register usable data
			if ($dayTemp != "")
			{
				$avgTemp += $dayTemp;
				$tempCount += 1; //Add to valid datapoint counter
			}

			if ($dayPressure != "")
			{
				$avgPressure += $dayPressure;
				$pressureCount += 1;
			}

			if ($dayDewPt != "")
			{
				$avgDewPt += $dayDewPt;
				$dewPtCount += 1;
			}

			if ($dayPrecipitation != "")
			{
				$avgPrecipitation += $dayPrecipitation;
				$precipitationCount += 1;
			}

			if ($dayMaxHumidity != "")
			{
				$maxHumidity += $dayMaxHumidity;
				$maxHumidityCount += 1;
			}

			if ($dayMinHumidity != "")
			{
				$minHumidity += $dayMinHumidity;
				$minHumidityCount += 1;
			}
			$tzName = $obj[0]->date->tzname;
		}

		//Prevent the program from dividing zero
		if ($tempCount != 0)
		{
			$avgTemp = $avgTemp / $tempCount;
		}
		else //When the counter indicates zero->no data point is available
		{
			$avgTemp = "n/a"; //When no data point was available
		}

		if ($pressureCount != 0)
		{
			$avgPressure = $avgPressure / $pressureCount;
		}
		else
		{
			$avgPressure = "n/a";
		}

		if ($dewPtCount != 0)
		{
			$avgDewPt = $avgDewPt / $dewPtCount;
		}
		else
		{
			$avgDewPt = "n/a";
		}

		if ($precipitationCount != 0)
		{
			$avgPrecipitation = $avgPrecipitation / $precipitationCount;
		}
		else
		{
			$avgPrecipitation = "n/a";
		}

		if ($maxHumidityCount != 0)
		{
			$maxHumidity = $maxHumidity / $maxHumidityCount;
		}
		else
		{
			$maxHumidity = "n/a";
		}

		if ($minHumidityCount != 0)
		{
			$minHumidity = $minHumidity / $minHumidityCount;
		}
		else
		{
			$minHumidity = "n/a";
		}

		//Upload data to IM2C database
		$import = $db->prepare("
		UPDATE CityNx_Jan
		SET `avgTemp` = '$avgTemp',
		`avgPressure` = '$avgPressure',
		`avgDewPt` = '$avgDewPt',
		`precipitation` = '$avgPrecipitation',
		`maxHumidity` = '$maxHumidity',
		`minHumidity` = '$minHumidity',
		`tzName` = '$tzName'
		WHERE city = '$city';
		");
		$import->execute();
	}
?>
