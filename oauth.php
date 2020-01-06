<?php

/*
Simple OAuth script. Intended for vasttrafik-aptus.

By marcfager 2020, GPLv3.
*/

class oauth {

	private $akey;
	private $expires;
	private $initurl;
	private $queryurl;
	private $deviceid;
	private $access_token;
	private $failinfo;

	// Just init the class
	public function init($user, $key, $url, $qurl) {
		$this->akey = base64_encode($user . ":" . $key);
		$this->expires = 0;
		$this->initurl = $url;
		$this->queryurl = $qurl;
		$this->deviceid = 0;
		$this->access_token = "";
		$this->failinfo = array();
	}

	// Create new key for device id
	public function create($id) {
		// Save device id
		$this->deviceid = $id;

		// Prepare CURL HTTP request
		$ch = curl_init($this->initurl);
		$hdr = array();
		$hdr[] = 'Content-Type: application/x-www-form-urlencoded';
		$hdr[] = 'Authorization: Basic ' . $this->akey;

		$pdata = array('grant_type' => 'client_credentials', 'scope' => 'device_' . $this->deviceid);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $hdr);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, rawurldecode(http_build_query($pdata)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// Execute CURL HTTP request		
		$rest = curl_exec($ch);
		curl_close($ch);
		// Parse JSON
		$jrest = json_decode($rest, true);

		// If the array has the correct size
		if (sizeof($jrest) == 4) {

			// If we got a key that hasn't expired, save and return true
			if ($jrest['expires_in'] > 0) {
				$l = strlen($jrest['access_token']);
				if ($l > 0) {
					// Save expires in and created
					$this->expires = time() + $jrest['expires_in'];
					// Save key
					$this->access_token = $jrest['access_token'];
					return true;
				}
			}
		}
		$this->failinfo = $rest;
		return false;

	}

	// Get fail info
	public function getFailInfo() {
		return $this->failinfo;
	}

	// Check if key is valid
	public function keyIsValid() {
		return (time() < $this->expires);
	}

	// Get expiration time of key
	public function expiresIn() {
		return ($this->expires - time());
	}

	// Get expiration time of key as unixtime
	public function uexpiresIn() {
		return $this->expires;
	}

	// Loop and recreate if needed
	public function recreateIfNeeded($t) {
		if ($this->expiresIn() < $t) {
			// Wait expiresIn s+1
			sleep(expiresIn() + 1);
			// Create new key
			$this->create($this->deviceid);
		}
	}

	// Recover from session/db
	public function recover($device, $token, $expires) {
		$this->deviceid = $device;
		$this->access_token = $token;
		$this->expires = $expires;
	}

	// Give token
	public function getToken() {
		return $this->access_token;
	}

	// Execute query
	public function query($q) {
		// Prepare CURL HTTP request
		$ch = curl_init($this->queryurl . $q . '&format=json');
		$hdr = array();
		$hdr[] = 'Authorization: Bearer ' . $this->access_token;

		curl_setopt($ch, CURLOPT_HTTPHEADER, $hdr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// Execute CURL HTTP request		
		$rest = curl_exec($ch);
		curl_close($ch);
		
		// Parse JSON
		$jrest = json_decode($rest, true);

		return $jrest;
	}

}


?>
