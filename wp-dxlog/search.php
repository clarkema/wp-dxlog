<html>
<head>
<!--
NN4 does not understand @import 
-->
<link rel="stylesheet" href="layout1.css" type="text/css"> 
<style type="text/css"> 
@import url(layout1.css);
</style>

<title>G4ZFE Log Search Results</title>
</head>

<body>

<DIV id=Header>
<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="0" WIDTH="100%">
	<TR VALIGN="MIDDLE">
		<TD WIDTH="32">
			<A title="G4ZFE" href="http://www.g4zfe.com">G4ZFE.COM</A>
		</td>
		<TD WIDTH="80%">
			<A href="downloads.html">DOWNLOADS</A>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<A href="iota.html">IOTA</A>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<A href="9m2g4zfe.html">9M2/G4ZFE</A>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<A href="3w2er.html">3W2ER</A>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<A href="audio.html">AUDIO</A>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<A href="misc.html">MISC</A>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<A href="contact.html">CONTACT</A>
		</td>
	</TR>
</TABLE>
</DIV>

<DIV id=Content>
<?php
	include 'dbinc.php';	// Variables for DB connection
	include 'error.inc';	// Error handler

	// Record the start time of the script
	$start = microtime();
	sscanf ($start,"%s %s",&$microseconds,&$seconds);
	$start_time = $seconds + $microseconds;

	// This function displays the modes contacted for each band (cell) as an image
	function print_cell ($value)
	{
		switch ($value)
		{
			case '0':
			echo "\n\t<td width=\"5%\">&nbsp;</td>";
			break;

			case '1':
			echo "\n\t<td width=\"5%\"><CENTER><img src=\"images/ssb.gif\" ALT=\"SSB\"></CENTER></td>";
			break;

			case '2':
			echo "\n\t<td width=\"5%\"><CENTER><img src=\"images/cw.gif\" ALT=\"CW\"></CENTER></td>";
			break;

			case '3':
			echo "\n\t<td width=\"5%\"><CENTER><img src=\"images/ssbcw.gif\" ALT=\"SSB CW\"></CENTER></td>";
			break;

			case '4':
			echo "\n\t<td width=\"5%\"><CENTER><img src=\"images/dig.gif\" ALT=\"DIG\"></CENTER></td>";
			break;

			case '5':
			echo "\n\t<td width=\"5%\"><CENTER><img src=\"images/ssbdig.gif\" ALT=\"SSB DIG\"></CENTER></td>";
			break;

			case '6':
			echo "\n\t<td width=\"5%\"><CENTER><img src=\"images/cwdig.gif\" ALT=\"CW DIG\"></CENTER></td>";
			break;

			case '7':
			echo "\n\t<td width=\"5%\"><CENTER><img src=\"images/cwssbdig.gif\" ALT=\"CW SSB DIG\"></CENTER></td>";
			break;

		}	
	}

	// This function is used to add up a whole array. It is used to check if any QSOs have been made with a
	// particular DX station
	function add_up ($running_total, $current_value)
	{
		$running_total += $current_value;
		return $running_total;
	}

	// Start of program

	// Read the callsign to search. Check to see if we need to strip any slashes
	// from the callsign (magic_quotes_gpc option in php.ini)
	$callsign = ini_get ('magic_quotes_gpc')
		    ? stripslashes ($_POST['callsign'])
		    : $_POST['callsign'];
	
	// Make the callsign upper case
	$callsign = strtoupper ($callsign);


	echo "<CENTER><H1>Log Search result for $callsign</H1></CENTER>";
	echo "<P>";
	
	// Connect to the database. The variables are stored in the include file db.inc
	if (! ($connection = @ mysql_connect($hostName,$username,$password)))
		die ("Could not connect to the database");

	// Connect to the log database. The error handler is defined in the error.inc include file
	if (!mysql_select_db ($databaseName, $connection))
		showerror();

	// Query the database for all the DX callsigns available. Select in alphabetical order of the callsign
	// for display purposes
	if (! ($result = mysql_query ("	SELECT dxcallsign 
					FROM dxstation
					ORDER by dxcallsign",
			$connection)))
		showerror();

	// Read each row from the database and store the callsign in the dxcalls array
	while ($row = @ mysql_fetch_row($result))
		for ($i=0; $i<mysql_num_fields($result); $i++)
			$dxcalls [] = $row[$i];

	// Create an array of the bands to be displayed. This is hard coded to make my life easier. It should
	// really be read from the database and the HTML table dynamically created. Let as a later exercise....
	$bands = array (160,80,40,30,20,17,15,12,10);

	// Initialise the 2-d array i.e. set number of QSOs on each band for each DX station to zero. This
	// make populating the HTML table a little easier.
	for ($i=0; $i < count($dxcalls); $i++)
		for ($j=0; $j < count($bands); $j++)
			$table[$dxcalls[$i]][$bands[$j]] = 0;

	// Query the database for all the QSOs for the requested callsign
	if (! ($result = mysql_query ("	SELECT DISTINCT dx.dxcallsign, 
							q.op_mode, 
							q.band
					FROM dxstation dx, qsos q
					WHERE q.callsign = '$callsign'
					AND q.fk_dxstn = dx.id
					ORDER by dx.dxcallsign,q.band DESC",
			$connection)))
		showerror();

	// Check the number of QSOs
	$count = mysql_num_rows ($result);

	if ($count == 0)
		echo "<P>Sorry no QSOs found for $callsign! Please check the full logs using the <A HREF=\"search.html\">Java log-search</A><P>";
	else
	{

	// Table Headings - bands
	echo 	"\n<center><table BORDER=\"1\" CELLSPACING=\"0\" CELLPADDING=\"5\" width=\"70%\">\n<tr>\n" .
		"\n\t<th>Callsign</th>" .
		"\n\t<th>160</th>" .
		"\n\t<th>80</th>" .
		"\n\t<th>40</th>" .
		"\n\t<th>30</th>" .
		"\n\t<th>20</th>" .
		"\n\t<th>17</th>" .
		"\n\t<th>15</th>" .
		"\n\t<th>12</th>" .
		"\n\t<th>10</th>" .
		"\n</tr>" .
		"\n<p>";

	// Read each row from the database
	while ($row = @ mysql_fetch_array($result))
	{
		// Add up the number of QSOs on each band
		switch ($row["op_mode"])
		{
			case 'SSB':
				$table [$row["dxcallsign"]] [$row["band"]] += 1;
				break;

			case 'CW':
				$table [$row["dxcallsign"]] [$row["band"]] += 2;
				break;

			case 'DIG':
				$table [$row["dxcallsign"]] [$row["band"]] += 4;
				break;

		}
	}

	// We have now read all the QSOs made for all the DX stations into a 2D matrix ($table)
	// Now we go through each row (DX station) and column (band)

	foreach ($table as $k => $v)
	{
		// Count the number of QSOs made with this DX station
		$total = array_reduce ($v, 'add_up');

		// None? Then don't bother displaying the row
		if ($total == 0)
			continue;

		echo "\n<tr>";
		echo "\n\t<td width=\"10%\"><center>$k</center></td>";

		foreach ($v as $k2 => $v2)
		{
			switch ($k2)
			{
				// Display QSOs made on each band
				case '160':
					print_cell($v2);
				break;
				case '80':
					print_cell($v2);
				break;
				case '40':
					print_cell($v2);
				break;
				case '30':
					print_cell($v2);
				break;
				case '20':
					print_cell($v2);
				break;
				case '17':
					print_cell($v2);
				break;
				case '15':
					print_cell($v2);
				break;
				case '12':
					print_cell($v2);
				break;
				case '10':
					print_cell($v2);
				break;
			}
		}
		echo "\n</tr>";
	}

	echo "\n</table></center>";
	echo "\n\n";
	
	} // End else no QSOs found

	// Record the end time of the script
	$end = microtime();
	sscanf ($end,"%s %s",&$microseconds,&$seconds);
	$end_time = $seconds + $microseconds;

	// Calculate elapsed time for the script
	$elapsed = $end_time - $start_time;
	sscanf ($elapsed,"%5f", &$elapsed_time);

	// Display summary info
	
	// Count the number of QSOs in the database
	if (! ($result = mysql_query ("	SELECT count(*) 
					FROM qsos",
			$connection)))
		showerror();

	// Read each row from the database and store the callsign in the dxcalls array
	while ($row = @ mysql_fetch_row($result))
		for ($i=0; $i<mysql_num_fields($result); $i++)
			$total = $row[$i];
			
	switch ($count)
	{
		case 1:
			echo "\n<CENTER><P><P><B>Total of $count QSO with $callsign</B><P>";
			break;
			
		case 0:
			echo "\n<CENTER><P><P>";
			break;
			
		default:
			echo "\n<CENTER><P><P><B>Total of $count QSOs with $callsign</B><P>";
			break;
	}
	
	echo "\n<P>There are $total QSOs in the Database<P>";

	echo "\n<P><FONT SIZE=\"-2\">The search took $elapsed_time seconds</FONT></CENTER>";
	
	// Close the database connection
	if (!mysql_close ($connection))
		showerror();
?>

<BR>
<P>
<A href="search.html">&lt; Return to Log Search Page</A>
</P>
</DIV>


<DIV id=Menu>
<P>
<A HREF="downloads.html">Download the Java Log Search Applet</A>
<P>
<A HREF="downloads.html">Download the MySQL Logbook Database</A>
<P>
</DIV>


<body>
</html>
