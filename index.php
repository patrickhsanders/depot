<?php

	include "SubwayUtil.php";
	
	date_default_timezone_set("America/New_York");
	
	$data_caching = true;
	$result_caching = true;
	
	//determine what is being requested or ALL
	if(htmlspecialchars(isset($_GET["line"]) && $_GET["line"]) != ""){
		$subway_line = htmlspecialchars($_GET["line"]);
		$subway_line = explode("-",$subway_line);
		sort($subway_line);
	} else {
		$subway_line = "all";
	}
	
	$formatted_lines_for_printing = is_array($subway_line) ? implode("-", $subway_line) : $subway_line;
	
	$result_filename = "data/subways" . $formatted_lines_for_printing . "-" . date("Ymd") . ".json";
	$data_filename = "data/raw/subways_raw" . date("Ymd") . ".geojson";

	//checks the cache for result availability
	if(file_exists($result_filename) && $result_caching == true){
		$file = fopen($result_filename, "r");
		echo fread($file, filesize($result_filename));
		fclose($file);
	} else {
		//checks the cache for data availability
		if(!file_exists($data_filename) && $data_caching == true) {
			copy("https://data.cityofnewyork.us/api/geospatial/arq3-7z49?method=export&format=GEOJson",$data_filename);
		}
	} 
	
	$output_content;
	$output_file = fopen($result_filename, "w");
	
	$subway = new SubwayUtil(file_get_contents($data_filename));
	if($subway_line == "all"){
		$output_content = $subway->formatted_sorted_stations_all_lines();
	} elseif(is_array($subway_line) && count($subway_line) == 1){
		$output_content = $subway->formatted_sorted_stations_for_line($subway_line[0]);
	} elseif(is_array($subway_line) && count($subway_line) > 1){
		$output_content = $subway->formatted_sorted_stations_for_lines($subway_line);
	} else {
		$output_content = "{ \n Nothing here }";
	}
	
	fwrite($output_file, $output_content);
	fclose($output_file);
	echo $output_content;
