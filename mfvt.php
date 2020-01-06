<?php

/*
MF Västtrafik script. Creates HTML data from the replies from Västtrafik API.

Intended for vasttrafik-aptus.

marcfager 2020, GPLv3.
*/


class mfvt {

	// Add minute to all times in array
	public function addMinute($data) {
		for ($i=0;$i<sizeof($data);$i++) {
			$data[$i]->add(new DateInterval("PT1M")); // Add one minute
		}
		return $data;
	}

	// Return position of first datetime to occur in array
	public function minDate($data) {
		$pos = 0;
		for ($i=1;$i<sizeof($data);$i++) {
			if ($data[$i] < $data[($i-1)]) {
				$pos = $i;
			}
		}
		return $pos;
	}
		

	// Create departure list array
	public function createDepList($turer) {
	        $deps = array();
		$colors = array();
		$mintime = new DateTime("now");
		$mintime->add(new DateInterval("PT1H")); // Default refresh 1 hour
        	// If departures available
	        if (isset($turer['DepartureBoard']['Departure']) && (sizeof($turer['DepartureBoard']['Departure']) > 0)) {
        	        // For every departure
                	for ($i=0;$i<sizeof($turer['DepartureBoard']['Departure']);$i++) {
        	                // If line (line + direction) doesn't exist, create it
	                        $linename = $turer['DepartureBoard']['Departure'][$i]['sname'] . '-' . $turer['DepartureBoard']['Departure'][$i]['direction'];
        	                if (!isset($deps[$linename])) {
                	                $deps[$linename] = array();
					// Add color data
					$colors[$linename] = $turer['DepartureBoard']['Departure'][$i]['fgColor'];
                        	}

				// Calculate offset in minutes if available.
				// Else add ~ in front of time
				$satds = "";
				$rtTimeAvailable = false;
				if (isset($turer['DepartureBoard']['Departure'][$i]['rtTime'])) {
					$tstd = DateTime::createFromFormat('Y-m-d H:i', $turer['DepartureBoard']['Departure'][$i]['date'] . ' ' . $turer['DepartureBoard']['Departure'][$i]['time']);
					$tatd = DateTime::createFromFormat('Y-m-d H:i', $turer['DepartureBoard']['Departure'][$i]['date'] . ' ' . $turer['DepartureBoard']['Departure'][$i]['rtTime']);
					$satd = date_diff($tstd, $tatd);

					if ($satd->format("%i") != "0") {
						$satds = " (" . $satd->format("%R%i") . ")";
					}
		                        // Add time
	        	                array_push($deps[$linename], $turer['DepartureBoard']['Departure'][$i]['rtTime'] . $satds);

					// rtTime available, calculate time diff
					$rtTimeAvailable = true;
				} else {
		                        // Add time
	        	                array_push($deps[$linename], "&#126;" . $turer['DepartureBoard']['Departure'][$i]['time'] . $satds);

				}

				// If time is earlier than $mintime, update $mintime
				if ($rtTimeAvailable) {
					$tdep = DateTime::createFromFormat('Y-m-d H:i', $turer['DepartureBoard']['Departure'][$i]['date'] . ' ' . $turer['DepartureBoard']['Departure'][$i]['rtTime']);
				} else {
					$tdep = DateTime::createFromFormat('Y-m-d H:i', $turer['DepartureBoard']['Departure'][$i]['date'] . ' ' . $turer['DepartureBoard']['Departure'][$i]['time']);
				}
					
				if ($tdep < $mintime) {
					$mintime = $tdep;
				}
	                }
        	}

		return array($deps, $colors, $mintime);
	}

	// If data else empty for simpler printing to tables
	private function idee($s) {
		if (isset($s) && (strlen($s) > 0)) {
			return $s;
		} else {
			return "";
		}
	}

	// Return data for index if exists from array
	private function rdee($a, $i) {
		if (sizeof($a) > $i) {
			return $this->idee($a[$i]);
		} else {
			return "";
		}
	}


	// Create simple table
	public function createSimpleTable($deps, $ref) {
		$ret = '<table border="0" name="' . $ref . '" id="' . $ref . '">' . "\n";
		$ret .= "\t<tr><td><b>Linje</b></td><td><b>Mot</b></td><td><b>N&auml;sta</b></td><td><b>D&auml;refter</b></td></tr>";
		for ($i=0;$i<sizeof($deps[0]);$i++) {
			$ldata = explode('-', key($deps[1]));
			$ret .= '<tr><td bgcolor="' . $deps[1][key($deps[0])] . '">' . $ldata[0] . '</td><td>' . $ldata[1] . '</td><td>' . $this->rdee($deps[0][key($deps[0])], 0) . '</td><td>' . $this->rdee($deps[0][key($deps[0])], 1) . '</td></tr>' . "\n";
			next($deps[1]);
			next($deps[0]);

		}
		$ret .= '</table>';
		return $ret;
	}

	// Create combined table
	public function createMultiTable($deps, $topics, $ref) {
		$ret = '<table class="table table-condensed" name="' . $ref . '" id="' . $ref . '">' . "\n";
		for ($j=0;$j<sizeof($deps);$j++) {
			$ret .= "\t\t\t" . '<tr><td class="text-left" colspan="4"><h4>' . $topics[$j] . '</h4></td></tr>' . "\n";
			$ret .= "\t\t\t<tr><th class=\"text-center\">Linje</th><th class=\"text-left\">Mot</th><th class=\"text-left\">N&auml;sta</th><th class=\"text-left\">D&auml;refter</th></tr>\n";
			for ($i=0;$i<sizeof($deps[$j][0]);$i++) {
				$ldata = explode('-', key($deps[$j][1]));
				$ret .= "\t\t\t" . '<tr><td class="text-center" bgcolor="' . $deps[$j][1][key($deps[$j][0])] . '">' . $ldata[0] . '</td><td class="text-left">' . $ldata[1] . '</td><td class="text-left">' . $this->rdee($deps[$j][0][key($deps[$j][0])], 0) . '</td><td class="text-left">' . $this->rdee($deps[$j][0][key($deps[$j][0])], 1) . '</td></tr>' . "\n";
				next($deps[$j][1]);
				next($deps[$j][0]);
			}
		}
		$ret .= "\t\t</table>\n";
		return $ret;
	}

	// Create data for stop list
	public function createStopList($t) {
		return $t['LocationList']['StopLocation'];
	}

	// Create simple table for stop list
	public function createSimpleStopTable($t) {
		$ret = '<table border="0" name="stops" id="stops"' . "\n";
		$ret .= "\t<tr><td><b>Namn</b></td><td><b>Longitud</b></td><td><b>Latitud</b></td><td><b>id</b></td></tr>\n";
		for ($i=0;$i<sizeof($t);$i++) {
			$ret .= "\t<tr><td>" . $t[$i]['name'] . "</td><td>" . $t[$i]['lon'] . "</td><td>" . $t[$i]['lat'] . "</td><td>" . $t[$i]['id'] . "</td></tr>\n";
		}
		$ret .= "</table>";
		return $ret;
	}

}

?>


