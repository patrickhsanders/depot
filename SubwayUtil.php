<?php

	class SubwayUtil{
		
		private $geojson_data; 
		public $lines = Array();
		
		public function __construct($data){
			$this->geojson_data = json_decode($data, true);
			$this->lines = $this->all_lines();
		} 
		
		private function all_lines(){
			if(empty($this->lines)){
				foreach($this->geojson_data["features"] as $item){
					$array_of_routes = explode("-",$item["properties"]["line"]);
					foreach ($array_of_routes as $line) {
						if(!in_array($line, $this->lines)){
							array_push($this->lines, $line);
						}
					}
				}
				sort($this->lines);
			}
			return $this->lines;
		}
		
		private function line_stations($subway_line = "1"){
			$single_line = Array();
			foreach($this->geojson_data["features"] as $item){
				$array_of_routes = explode("-",$item["properties"]["line"]);
				if(in_array($subway_line,$array_of_routes)){
					$station = array();
					$station["name"] = $item["properties"]["name"];
					$station["lines"] = $item["properties"]["line"];
					$station["latitude"] = floatval($item["geometry"]["coordinates"][0]);
					$station["longitude"] = floatval($item["geometry"]["coordinates"][1]);
					$station["distance_to_next"] = 0;
					$single_line[$station["name"]] = $station;
				}
			}
			return $single_line;
		}
		
		private function get_terminal_station_for_line($subway_line){
			$terminal_station = "";
			switch ($subway_line) {
				case "1":
					$terminal_station = "South Ferry";
					break;
				case "2":
					$terminal_station = "Brooklyn College - Flatbush Ave";
					break;	
				case "3":
					$terminal_station = "Harlem - 148 St";
					break;	
				case "4":
					$terminal_station = "Woodlawn";
					break;	
				case "5":
					$terminal_station = "Eastchester - Dyre Ave";
					break;	
				case "6":
					$terminal_station = "Pelham Bay Park";
					break;	
				case "6 Express":
					$terminal_station = "Pelham Bay Park";
					break;	
				case "7":
					$terminal_station = "Flushing - Main St";
					break;	
				case "7 Express":
					$terminal_station = "Flushing - Main St";
					break;	
				case "A":
					$terminal_station = "Inwood - 207th St";
					break;	
				case "B":
					$terminal_station = "Bedford Park Blvd";
					break;	
				case "C":
					$terminal_station = "168th St";
					break;	
				case "D":
					$terminal_station = "Norwood - 205th St";
					break;	
				case "E":
					$terminal_station = "Jamaica Ctr - Parsons / Archer";
					break;	
				case "F":
					$terminal_station = "Jamaica - 179th St";
					break;	
				case "G":
					$terminal_station = "Church Ave";
					break;	
				case "J":
					$terminal_station = "Jamaica Ctr - Parsons / Archer";
					break;	
				case "L":
					$terminal_station = "Canarsie - Rockaway Pkwy";
					break;	
				case "M":
					$terminal_station = "Forest Hills - 71st Av";
					break;	
				case "N":
					$terminal_station = "Astoria - Ditmars Blvd";
					break;	
				case "Q":
					$terminal_station = "Astoria - Ditmars Blvd";
					break;
				case "R":
					$terminal_station = "Bay Ridge - 95th St";
					break;	
				case "S":
					$terminal_station = "Grand Central - 42nd St";
					break;	
				case "Z":
					$terminal_station = "Jamaica Ctr - Parsons / Archer";
					break;	
				default:
					break;
			}
			return $terminal_station;
		}
		
		private function sorted_line_stations($single_line, $line){
			$sorted_stations = array();
			$current_station = $this->get_terminal_station_for_line($line);
			array_push($sorted_stations, $single_line[$current_station]);

			while(count($single_line) > 1){
				//var_dump($single_line); 
				$distance = 100.0;
				$station = "";
				foreach ($single_line as $key => $value) {
					if($key != $current_station){
						
						$lat1 = $single_line[$current_station]["latitude"];
						$long1 = $single_line[$current_station]["longitude"];
						//echo "(" . $lat1 . "," . $long1 . ")\n";
						
						$lat2 = $value["latitude"];
						$long2 = $value["longitude"];
						//echo "(" . $lat2 . "," . $long2 . ")\n";
						
						$between_points =  $this->distance($lat1, $long1, $lat2, $long2, "K");
						if($between_points < $distance){ 
							$distance = $between_points;
							$station = $key;
						}
					}
				}
				$sorted_stations[count($sorted_stations)-1]["distance_to_next"] = $distance;
				array_push($sorted_stations, $single_line[$station]);
				unset($single_line[$current_station]);
				$current_station = $station;
			}
			return $sorted_stations;
		}
		
		private function distance($lat1, $lon1, $lat2, $lon2, $unit) {
			//this code is from https://www.geodatasource.com/developers/php
			$theta = $lon1 - $lon2;
			$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
			$dist = acos($dist);
			$dist = rad2deg($dist);
			$miles = $dist * 60 * 1.1515;
			$unit = strtoupper($unit);

			if ($unit == "K") {
				return ($miles * 1.609344);
			} else if ($unit == "N") {
					return ($miles * 0.8684);
				} else {
						return $miles;
			}
		}
		
		public function formatted_sorted_stations_for_line($line){
			return json_encode($this->sorted_line_stations($this->line_stations($line), $line),JSON_PRETTY_PRINT);
		}
		
		public function formatted_sorted_stations_for_lines($lines){
			$output_lines = array();
			foreach ($lines as  $line) {
				$output_lines[$line] = $this->sorted_line_stations($this->line_stations($line), $line);
			}
			return json_encode($output_lines,JSON_PRETTY_PRINT);
		}
		
		public function formatted_sorted_stations_all_lines(){
			$lines = array();
			foreach ($this->lines as $line) {
				if($line == "W"){
					continue;
				}
				$single_line = $this->line_stations($line);
				$sorted_line = $this->sorted_line_stations($single_line, $line);
				$lines[$line] = $sorted_line;
			}
			return json_encode($lines,JSON_PRETTY_PRINT);
		}
		
		public function formatted_available_lines(){
			return json_encode($this->lines,JSON_PRETTY_PRINT);
		}
	}
?>