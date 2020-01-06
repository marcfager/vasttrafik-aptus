<?php
/*
vasttrafik-aptus script. Displays departure boards for Västtrafik public transport.

See INSTALL for installation instructions.

marcfager 2020, GPLv3.
*/
date_default_timezone_Set('Europe/Stockholm');
session_start();

include 'oauth.php';
include 'mfvt.php';
include 'mfprotectip.php';

// Start protect IP
$mfproip = new MFProtectIP;

/*
:::1 Modify if MFProtect ip is enabled to reflect (at least) your email and the allowed IP:s. If you want to disable MFProtectIP, comment out the two mfproip lines.
If you don't know the Aptus systems IP, direct the Aptus AGERA screen to http://your-host/vasttrafik-aptus/ip.php and read the IP from the screen.
*/
//$mfproip->init('ip.txt', 'mail@example.com', array('1.2.3.4', '2.3.4.5'));
//$mfproip->run();

// Init OAuth and MFVT
$oauth = new oauth();
$mfvt = new mfvt();

/*
:::2 Modify to match your userid and key to Västtrafik API
*/
$oauth->init("Key", "Secret", 'https://api.vasttrafik.se/token', 'https://api.vasttrafik.se/bin/rest.exe/v2/');


// If a key is stored
if (isset($_SESSION['oa_vfy']) && isset($_SESSION['oa_device']) && isset($_SESSION['oa_token']) && isset($_SESSION['oa_expires']) && isset($_SESSION['oa_device']) && (strlen($_SESSION['oa_vfy']) > 0)) {
	// If it is valid
	if (crypt($_SESSION['oa_token'] . $_SESSION['oa_expires'] . $_SESSION['oa_device'], 'mg') == $_SESSION['oa_vfy']) {
		if ($_SESSION['oa_expires'] > time()) {
			$oauth->recover($_SESSION['oa_device'], $_SESSION['oa_token'], $_SESSION['oa_expires']);
		} else {
			$_SESSION['oa_vfy'] = "";
			unset($_SESSION['oa_vfy']);
		}
	} else {
		// Else clear it
		$_SESSION['oa_vfy'] == "";
		unset($_SESSION['oa_vfy']);
	}
}

if (isset($_SESSION['c_dev']) && is_numeric($_SESSION['c_dev'])) {
	$cdevice = $_SESSION['c_dev'];
} else {
	$cdevice = rand(1, 60000);
	$_SESSION['c_dev'] = $cdevice;
}

// If key valid
if ($oauth->keyIsValid()) {
	// If valid for less than specified time, wait and recreate
	$oauth->recreateIfNeeded(3);
} else {
	// Else create a key.
	if ($oauth->create($cdevice)) {
		// Store key in session
		$_SESSION['oa_token'] = $oauth->getToken();
		$_SESSION['oa_device'] = $cdevice;
		$_SESSION['oa_expires'] = $oauth->uexpiresIn();
		$_SESSION['oa_vfy'] = crypt($_SESSION['oa_token'] . $_SESSION['oa_expires'] . $_SESSION['oa_device'], 'mg');
	}
}


// If key valid
if ($oauth->keyIsValid()) {
	/*
	:::3 Start. Modify the queries below to match what you want to display on your Aptus AGERA screens
	*/

	// Get data
	$turer_bockkranen = $oauth->query("departureBoard?id=9021014001595000&date=" . date('Y-m-d') . "&" . date("H:") . (date("M") - 1) . "&useVas=0&useLDTrain=0&useRegTrain=0&useBoat=0&useTram=0&maxDeparturesPerLine=3");
	$deps_bockkranen = $mfvt->createDepList($turer_bockkranen);

        $turer_torget = $oauth->query("departureBoard?id=9021014002240000&date=" . date('Y-m-d') . "&" . date("H:") . (date("M") - 1) . "&useVas=0&useLDTrain=0&useRegTrain=0&useBus=1&useBoat=0&useTram=0&maxDeparturesPerLine=3");

        $deps_torget = $mfvt->createDepList($turer_torget);

	$turer_farjelage = $oauth->query("departureBoard?id=9021014002239000&date=" . date('Y-m-d') . "&" . date("H:") . (date("M") - 1) . "&useVas=0&useLDTrain=0&useRegTrain=0&useBus=0&useBoat=1&useTram=0&maxDeparturesPerLine=3");

	$deps_farjelage = $mfvt->createDepList($turer_farjelage);

	// If multiple stops, use "createMultiTable" function. Otherwise "createSimpleTable" function can be used.
	$deptable = $mfvt->createMultiTable(array($deps_bockkranen, $deps_torget, $deps_farjelage), array("Bockkranen", "Eriksbergstorget", "F&auml;rjel&auml;get"), "avgangar");

	/*
	:::3 End. Modify the queries above to match what you want to display on your Aptus AGERA screens
	*/


	// Add one minute to time array times
	$times = $mfvt->addMinute(array(clone $deps_bockkranen[2], clone $deps_torget[2], clone $deps_farjelage[2]));

	// Refresh according to min time
	$ref0 = date_diff(new DateTime('now'), $times[$mfvt->minDate($times)]);
	$ref = max(1, $ref0->format("%i")) * 60;


}


?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta http-equiv="refresh" content="<?php echo $ref; ?>">

    <title>Kollektivtrafik vasttrafik-agera</title>

    <!-- :::3 Modify the following bootstrap reference to match your bootstrap installation -->

    <!-- Bootstrap core CSS -->
    <link href="bootstrap-3.3.7-dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS to remove margin at top -->
    <style>
    </style>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

	<div class="container">
		<?php echo $deptable; ?>
		<i>Tiden avser uppskattad avg&aring;ngstid, parentesen anger f&ouml;rh&aring;llande mot tidtabellen. Ex. 11:39 (+2) avg&aring;r 11:39 p&aring; riktigt, men 11:37 enligt tidtabell. Tildetecken (&#126;) indikerar att ingen uppskattad avg&aring;ngstid finns tillg&auml;nglig.</i>
		<br /><i>V&auml;sttrafik-Aptus MF2020 GPLv3</i>
	</div>
    <!-- :::4 Modify the following bootstrap reference to match your bootstrap installation -->
    <script src="bootstrap-3-3.7-dist/js/bootstrap.min.js"></script>
  </body>
</html>
