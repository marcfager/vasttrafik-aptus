# vasttrafik-aptus
Display Västtrafik public transport information on Aptus AGERA screens.

## Installation:
### Västtrafik API
1. Create an Västtrafik Developer user account.
2. Create a new application in your Västtrafik Developer profile.
3. Create a new subscription for "Reseplaneraren v2" for your application created above.


### Install vasttrafik-aptus and bootstrap
4. Clone this repository and place on web server
5. Download & extract Bootstrap and place on web server
6. Configure vasttrafik-aptus. Open `vasttrafik-aptus.php`. Search for `:::` . The three colons indicate lines that must be modified by you. For a minimal setup, at least the following must be done:
   - Modify point 2 to reflect your Västtrafik API credentials
   - Modify point 3 to reflect one or more stops you want to display.
     - As default, it displays three stops in the Eriksberg area.
     - To find out the stop id, go to Västtrafik Developer Portal, click to read more about the available API:s, select `Show details/Visa detaljer` for Reseplaneraren v2. Click `API-Console/API-Konsol`, then `location`, then `/location.name`. Scroll down to the input field `input` and enter the name of the stop. (i.e. *"Eriksbergstorget"*). Scroll down and click `try it out!`.
     - Scroll down a little further to find the response. In the response, find a line such as
     `<StopLocation name="Eriksbergstorget, Göteborg" lon="11.913224" lat="57.702351" id="9021014002240000" idx="1">`.
     - The id-field `9021014002240000` contains the number needed in vasttrafik-aptus requests.
   - Modify point 4 and 5 to match your Bootstrap installation.


### Test that the setup works
7. Point your browser to `http://your-domain/vasttrafik-aptus/vasttrafik-aptus.php`. If everything worked out, you should be able to see the time tables. Congratulations! If not, enable error reporting in php and try to add some debugging lines into the scripts to find out why. Pay attention to the looks, if there is poor formatting the Bootstrap links are probably incorrect.
8. If you want to limit the access to the script to your own Aptus AGERA screens, please read the info in `mfprotectip.php` to learn how to enable strict mode. Also edit point 1 in `vasttrafik-aptus.php`. If you want to limit access to certain IP:s (your own Aptus AGERA boards), make sure the web server can read&write the file `ip.txt`. I.e. `chown webserver-user:webserver-user ip.txt` and `chmod 770 ip.txt`.


### Configure the Aptus system to display your public transport schedule
As my system was in Swedish, I only have the Swedish texts with guessed English names.

9. Login to the Aptus Hantera page.
10. Click `Communication` (`Kommunikation`) and `Templates` (`Mallar`). Click `New template` (`Ny mall`). This page defines the looks of the AGERA screen. Please see [this example](frame.png) for example with tenant list and article screens together with the script. The url should end with vasttrafik-aptus.php if the filename is unchanged.
11. Under "Communication" (`Kommunikation`) and `Communication boards`, open all your boards and set them to use the template created above.

### Done
