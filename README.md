# vasttrafik-aptus
Display Västtrafik public transport information on Aptus AGERA screens.

Västtrafik (https://www.vasttrafik.se) is the public transport company in the Gothenburg region in Sweden. Aptus (http://www.aptus.se) is an access control system that can be equipped with electronic billboards.

Requirements:
- Aptus system with AGERA screen(s)
- Access to the "Hantera" (admin) portal for the Aptus system
- Västtrafik developer account (free) https://developer.vasttrafik.se/portal/#/
- Web server with PHP5+.
- Bootstrap (getbootstrap.com)

Included:
- vasttrafik-aptus.php - Script to be displayed on the aptus board
- oauth.php - Script to handle OAuth communication towards Västtrafik developer API.
- mfvt.php  - Script to format Västtrafik API responses into HTML.
- mfprotectip.php - (Optional) Script to limit access to a specific ip or array of IP:s and notify if other access.
- (ip.txt - Control file for MFProtectIP.)
- ip.php - Displays the IP address. Usable to find out the IP of the Aptus system if the script above is to be used.

Installation:
- Please see [INSTALL.md](INSTALL.md).
