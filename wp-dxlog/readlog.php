<?php
	include 'dbinc.php';
	include 'error.inc';

	// Function to read one line of QSO data from the Cabrillo file and parse according to the contest type
	// Parameters:
	// $contest_type - Cabrillo contest type (determines the number of columns in the QSO data)
	// $s - the QSO: line from the Cabrillo file
	// &$qso_data - array to put parsed data into
	function readCabrilloQSO ($contest_type, $s, &$qso_data)
	{

		switch ($contest_type)
		{
			case "ARRL-VHF-SEP":
			// This Cabrillo format has 9 columns (including QSO: column)
			sscanf ($s, "%s %s %s %s %s %s %s %s %s",
			&$dummy,
			&$qso_data['freq'],
			&$qso_data['mode'],
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$qso_data['call'],
			&$dummy);
			break;

			case "IARU":
			case "AP-SPRINT":
			case "ARRL-10":
			case "ARRL-160":
			case "ARRL-DX":
			case "CQ-WPX":
			// This Cabrillo format has 11 columns (including QSO: column)
			sscanf ($s, "%s %s %s %s %s %s %s %s %s %s %s",
			&$dummy,
			&$qso_data['freq'],
			&$qso_data['mode'],
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$qso_data['call'],
			&$dummy,
			&$dummy);
			break;

			case "RSGB-IOTA":
			case "CQ-WW-RTTY":
			// This Cabrillo format has 13 columns (including QSO: column)
			sscanf ($s, "%s %s %s %s %s %s %s %s %s %s %s %s %s",
			&$dummy,
			&$qso_data['freq'],
			&$qso_data['mode'],
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$qso_data['call'],
			&$dummy,
			&$dummy,
			&$dummy);
			break;

			case "RSGB 21":
			// This Cabrillo format has 13 columns (including QSO: column)
			sscanf ($s, "%s %s %s %s %s %s %s %s %s %s %s %s %s",
			&$dummy,
			&$qso_data['freq'],
			&$qso_data['mode'],
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$qso_data['call'],
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy);
			break;

			case "ARRL-SS-CW":
			// This Cabrillo format has 15 columns (including QSO: column)
			sscanf ($s, "%s %s %s %s %s %s %s %s %s %s %s %s %s %s %s",
			&$dummy,
			&$qso_data['freq'],
			&$qso_data['mode'],
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$qso_data['call'],
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy);
			break;
			
			case "DXPEDITION":
			// This Cabrillo format has 9 columns (including QSO: column)
			sscanf ($s, "%s %s %s %s %s %s %s %s %s",
			&$dummy,
			&$qso_data['freq'],
			&$qso_data['mode'],
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$qso_data['call'],
			&$dummy);
			break;
			
               		default:
                	// The default Cabrillo format has 11 columns (inluding QSO: column)
			sscanf ($s, "%s %s %s %s %s %s %s %s %s %s %s",
			&$dummy,
			&$qso_data['freq'],
			&$qso_data['mode'],
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$dummy,
			&$qso_data['call'],
			&$dummy,
			&$dummy);
                	break;
		}
	}


	// Function to convert frequency (211234) to band (15)
	// Only 160 to 6m
	// Parameters:
	// frequency to convert to band
	function convertFrequencyBand ($frequency)
	{
		// 1.8 or 18 MHz
		if (ereg('^18',$frequency))
		{
			if (strlen($frequency) > 4)
				$band = 17;
			else
				$band = 160;
		}
		// 3.5 or 3.8 MHz
		elseif (ereg('^3',$frequency))
		{
			$band = 80;
		}
		// 7.0 or 7.1 MHz
		elseif (ereg('^7',$frequency))
		{
			$band = 40;
		}
		// 10.1 MHz
		elseif (ereg('^10',$frequency))
		{
			$band = 30;
		}
		// 14 MHz
		elseif (ereg('^14',$frequency))
		{
			$band = 20;
		}
		// 21 MHz
		elseif (ereg('^21',$frequency))
		{
			$band = 15;
		}
		// 24 MHz
		elseif (ereg('^24',$frequency))
		{
			$band = 12;
		}
		// 28 MHz
		elseif (ereg('^28',$frequency))
		{
			$band = 10;
		}
		// 29 MHz FM
		elseif (ereg('^29',$frequency))
		{
			$band = 10;
		}
		// 50 MHz
		elseif (ereg('^50',$frequency))
		{
			$band = 6;
		}
		else
			$band = 0;

		return $band;
	}

	// Function to read a Cabrillo file and insert each QSO into the database
	// Parameters:
	// $file - file pointer of Cabrillo file being read
	// $qso_count - (global) number of QSOs read
	// $connection - Database connection
	// $dxcallsign - FK to logbook
	function processCabrilloFile ($file, &$qso_count, $connection, $dxcallsign)
	{
		// Initialise the array
		$qso_data = array ('freq' => '',
				   'mode' => '',
				   'call' => '');

		// Read the Cabrillo file until we reach the "CONTEST:" tag
		while (fscanf ($file,"%s %s",&$tag, &$value))
		{
			if (!strcasecmp($tag, "CONTEST:"))
			{
				// Read the contest type so that we can parse the file
				$contest_type = $value;
				echo "<p>Cabrillo Contest type is $contest_type <P>\n";
				break;
			}
			else
				// Continue until the CONTEST: tag is reached
				continue;
		}

		// Keep a count of the number of QSOs added to the database
		$qso_count = 0;

		// Read each line of the log file
		while ($s = fgets ($file,1024))
		{
			$line = explode (' ', $s);

			// Skip Cabrillo header lines
			if (!strcasecmp($line[0], "QSO:"))
			{
				// Read one line of QSO data from the Cabrillo file
				readCabrilloQSO ($contest_type, $s, &$qso_data);	
			}
			else
				// Continue reading until the "QSO" tag
				continue;

			// Convert frequency to band
			$band = convertFrequencyBand ($qso_data['freq']);

			// Trap unknown bands error
			if ($band == 0)
			{
				$freq = $qso_data['freq'];
				echo "<P><EM>Error - Frequency to Band conversion failed - frequency: $freq</EM></P>\n";
				echo "<P>No QSOs loaded\n";
				echo "<p><A HREF=\"uploadlog.php\">Return to Log Upload Page</A>\n";
				die();
			}				

			// Cabrillo logs contain mode as "CW/PH/RY"
			// Convert PH to SSB
			if (!strcasecmp($qso_data['mode'],"PH"))
				$qso_data['mode'] = 'SSB';

			// Convert RY to DIG
			if (!strcasecmp($qso_data['mode'],"RY"))
				$qso_data['mode'] = 'DIG';
	
			// Insert QSO into the database
			$query = "INSERT INTO qsos SET id = 0, " .
						"callsign = \"" . $qso_data['call'] . "\" , " .
						"op_mode = \"" . $qso_data['mode'] . "\" , " .
						"band = \"" . $band . "\" , " .
						"fk_dxstn = \"" . $dxcallsign . "\" ";

			if (!(@ mysql_query ($query, $connection)))
				showerror();

			$qso_count++;
		}

	}

	// Function to read one line of QSO data from the AIF file
	// Parameters:
	// $string - first valid line from file (<EOH> or <CALL>
	// $qso_data - array to put parsed data into
	// $band_found - boolean flag for <BAND> tag
	// $freq_found - boolean flag for <FREQ> tag
	function readADIFQSO ($string, &$qso_data, &$band_found, &$freq_found)
	{
		$string = strtoupper ($string);

		// Read Callsign
		if ($s = stristr($string,"<CALL"))
		{
			$values = sscanf ($s, "<CALL:%d>%s ", $length,$qso_data['call']);

			if ($values != 2)
				sscanf ($s, "<CALL:%d:%c>%s ", $length,$dummy,$qso_data['call']);
		}

		// Read Band
		if ($s = stristr($string,"<BAND"))
		{
			$band_found = 1;
					
                        $values = sscanf ($s, "<BAND:%d>%s ", $length,$qso_data['band']);

			if ($values != 2)
				sscanf ($s, "<BAND:%d:%c>%s ", $length,$dummy,$qso_data['band']);
				
			// Strip the 'M off e.g. 40M
			if (($pos = strpos ($qso_data['band'], 'M')) != NULL)
				$qso_data['band'][$pos] = ' ';

		}

		// Read Mode
		if ($s = stristr($string,"<MODE"))
		{
			$values = sscanf ($s, "<MODE:%d>%s ", $length,$qso_data['mode']);

			if ($values != 2)
				sscanf ($s, "<MODE:%d:%c>%s ", $length,$dummy,$qso_data['mode']);

			switch ($qso_data['mode'])
			{
			// Convert all Digital modes to 'DIG'
			case "PSK31":
			case "PSK63":
			case "BPSK31":
			case "BPSK63":
			case "RTTY":
			case "MFSK16":
			case "WSJT":
			case "FSK441":
			case "JT6M":
						
			$qso_data['mode'] = "DIG";
			break;

			// Convert all Phone modes to SSB
			case "USB":
			case "LSB":
			case "FM":
			case "AM":

			$qso_data['mode'] = "SSB";
			break;
			}
		}

		// Read Frequency (e.g. if Band is not present in the record)
		if (($s = stristr($string,"<FREQ")) && $band_found == 0)
		{
			$freq_found = 1;

			$values = sscanf ($s, "<FREQ:%d>%s ", $length,$qso_data['freq']);

			if ($values != 2)
				sscanf ($s, "<FREQ:%d:%c>%s ", $length,$dummy,$qso_data['freq']);

		}
	}

	// Function to read an ADIF file and insert each QSO into the database
	function processADIFFile ($file, &$qso_count, $connection, $string, $dxcallsign)
	{
		$EOR = 0;
		$band_found = 0;
		$freq_found = 0;
		$blank_line = 1;

		$qso_data = array ('band' => '',
				   'mode' => '',
				   'freq' => '',
				   'call' => '');

		// Can enter this procedure with $string set to either <CALL:x> which is a valid QSO
		// which needs to be processed or set to <EOH> in which case we need to read any blank
		// lines until the first QSO
		if (stristr($string,"<EOH>"))
			// Skip any blank lines
			while ($string == "\n" || $string == "\r\n")
				$string = fgets ($file, 1024);

		// process the first valid ADIF line
		readADIFQSO ($string, &$qso_data, &$band_found, &$freq_found);

		while (($string = fgets ($file,1024)))
		{
			// Skip any blank lines
			if ($string == "\n" || $string == "\r\n")
				continue;

			while (!$EOR)
			{
				readADIFQSO ($string, &$qso_data, &$band_found, &$freq_found);

				// Check for End of Record
				if (stristr($string,"<EOR>"))
					$EOR = 1;
				else
					$string = fgets($file,1024);

			}

			// End of Record. If no <BAND> data has been found then 
			// convert the frequency to band
			if ($band_found == 0)
				// Convert the frequency to band
				$qso_data['band'] = convertFrequencyBand ($qso_data['freq']);

			// Insert QSO into the database
			$query = "INSERT INTO qsos SET id = 0, " .
						"callsign = \"" . $qso_data['call'] . "\" , " .
						"op_mode = \"" . $qso_data['mode'] . "\" , " .
						"band = \"" . $qso_data['band'] . "\" , " .
						"fk_dxstn = \"" . $dxcallsign . "\" ";


			$band_found = 0;
			$freq_found = 0;
			$EOR = 0;
			$qso_data[]='';

			if (!(@ mysql_query ($query, $connection)))
				showerror();

			$qso_count++;
		}
		
	}



	// Start of Main Program
	$valid_file = 0;
	$file_type = "unknown";

	// Record the start time of the script
	$start = microtime();
	sscanf ($start,"%s %s",&$microseconds,&$seconds);
	$start_time = $seconds + $microseconds;

	// Read data posted from form
	$browser_name = $_FILES['userfile']['name'];
	$temp_name = $_FILES['userfile']['tmp_name'];
	$dxcallsign = $_POST['callsign'];

	// Connect to the database
	if (!($connection = @ mysql_connect ($hostName,
					    $username,
					    $password)))
		die ("Could not connect to database");

	if (!mysql_select_db ($databaseName, $connection))
		showerror();

	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\"http://www.w3c.org/TR/1999/REC-html401-19991224/loose.dtd\">";
	echo "<html>";
	echo "<head>";
	echo "<title>Upload Log</title>";
	echo "</head>";
	echo "<body>";

	// Was a log file uploaded?
	if (is_uploaded_file ($temp_name))
	{
		echo "<h1>File Upload $browser_name</h1>";
		echo "<p>Filename - $browser_name\n";
		echo "<br>DX Callsign - $dxcallsign\n";

		// Open the log file
		if (!($file = fopen ($temp_name, "r")))
			die ("Could not open the log file $file\n");

		// Read the first line
		$string = fgets ($file, 1024);

		// Check that it is a Cabrillo File
		if (stristr($string, "START-OF-LOG:"))
		{
			// Process Cabrillo file
			processCabrilloFile ($file,&$qso_count, $connection, $dxcallsign);

			$file_type = "CABRILLO";
		}
		// Check if it is an ADIF file
		elseif (stristr($string, "<EOH>") || stristr($string,"<CALL"))
		{
			// Process ADIF file
			processADIFFile ($file,&$qso_count, $connection,$string, $dxcallsign);

			$file_type = "ADIF";
		}
		else
		{
			while (($string = fgets ($file, 1024)) && !$valid_file)
			{
				if (stristr($string, "<EOH>") || stristr($string,"<CALL"))
				{
					$valid_file = 1;
					processADIFFile ($file,&$qso_count, $connection,$string, $dxcallsign);
					$file_type = "ADIF";
				}
			}

			// No Cabrillo or ADFI file found - exit with an error
			if (!$valid_file)
			{
				echo "<P>Error - Unable to upload file: $browser_name\n";
				echo "<P>Invalid Cabrillo or ADIF file\n";
				echo "<P>No QSOs loaded\n";
				echo "<p><A HREF=\"uploadlog.php\">Return to Log Upload Page</A>\n";
				die();
			}
		}

		// Record the end time of the script
		$end = microtime();
		sscanf ($end,"%s %s",&$microseconds,&$seconds);
		$end_time = $seconds + $microseconds;

		// Calculate elapsed time for the script
		$elapsed = $end_time - $start_time;
		sscanf ($elapsed,"%5f", &$elapsed_time);

		// Determine the callsign for these logs
		$query = "SELECT dxcallsign from dxstation where id=$dxcallsign";

		if (!($result = @ mysql_query ($query, $connection)))
			showerror();

		$row = @ mysql_fetch_array ($result);

		$callsign = $row['dxcallsign'];

		echo "<P>File type loaded: $file_type";
		echo "<p>A total of $qso_count QSOs were added to the database ";
		echo "for callsign $callsign<P>";
		echo "Elapsed time = $elapsed_time seconds";

		// Count the total number of QSOs in the database
		$query = "SELECT count(*) from qsos";

		if (!($result = @ mysql_query ($query, $connection)))
			showerror();

		$row = @ mysql_fetch_array ($result);

		$total_qso_count = $row['count(*)'];

		echo "<P>There are now $total_qso_count QSOs in the database<P>";

		// Save details of the uploaded file
		$query = "INSERT INTO logfiles SET id = 0, " .
			"filename = \"" . $browser_name . "\" , " .
			"qsos = \"" . $qso_count . "\" , " .
			"filetype = \"" . $file_type . "\" , " .
			"loaded = NOW()";

		if (!(@ mysql_query ($query, $connection)))
			showerror();

	}
	else
	{
		// No file uploaded
		echo "<h1>No file Uploaded</h1>";
	}

	echo "<p><A HREF=\"index.php\">Return to Log Upload Page</A>\n";

 	if (!mysql_close ($connection))
  		showerror();

	echo "</body>";
	echo "</html>";
?>

