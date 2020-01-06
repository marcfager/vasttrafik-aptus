<?php

/* MFProtectIP

Protect a web page so that it is only visible to specific IP addresses.
With default options set, the page is displayed, but a warning email is issued once.
To get more emails, a file flag needs to be resetted.

With strict option set, the page is not displayed at all. The failPage-option can be used
to direct the user to a different page instead.

Commands:
 - init(filename, email, array(ip1, ip2))	- Init script with file for mail flag, email address and array with allowed ip:s.
 - strict(url)				- Enable strict mode. URL to redirect user to
 - run()				- Run script
 - authorized()				- True if authorized, false if not

Intended for vasttrafik-aptus. By marcfager 2020, GPLv3.

*/

class MFProtectIP {
	private $filename;
	private $email;
	private $ips;
	private $strictx = false;
	private $url;
	private $auth = true;

	// Init function
	public function init($filename, $email, $ips) {
		$this->filename = $filename;
		$this->email = $email;
		$this->ips = $ips;
	}

	// Enable strict
	public function strict($url) {
		$this->strictx = true;
		$this->url = $url;
	}


	// Proceed action
	public function proceed() {
		mail($this->email, "MFProtectIP unauthorized", wordwrap("Unauthorized access to " . $_SERVER['SERVER_ADDR'] . $_SERVER['REQUEST_URI'] . " from " . $_SERVER['REMOTE_ADDR'], 70, "\r\n"));
		// Set text file
		file_put_contents($this->filename, "1");

		// If strict
		if ($this->strictx) {
			header("Location: " . $this->url);
		}
		$this->auth = false;
	}

	// Run script
	public function run() {
		if (!in_array($_SERVER['REMOTE_ADDR'], $this->ips)) {
			// Not in array.
			// Check if control file exists
			if (file_exists($this->filename)) {
				// File exists, read it
				$handle = fopen($this->filename, "r");
				$content = fread($handle, filesize($this->filename));
				fclose($handle);
				// If content != 1, proceed
				if ($content != "1") {
					$this->proceed();
				} else {
					// Just set flag
					$this->auth = false;
				}
			} else {
				// File doesn't exist, send mail and put content.
				$this->proceed();
			}
		}
	}


	// Check if authorized
	public function authorized() {
		return $this->auth;
	}
	
	

}


?>
