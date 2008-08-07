<?php
	include 'dbinc.php';	// Variables for DB connection
	include 'error.inc';	// Error handler

	// Function to display a bar chart from O'Reilly 'PHP Cookbook'
	function pc_bar_chart ($question, $answers)
	{
	
		// define colours to draw the bars
		$colours = array (array(255,102,0), array(0,153,0),
				array(51,51,204), array(255,0,51),
				array(255,255,0), array(102,255,255),
				array(153,0,204));

		$total = array_sum($answers['qsos']);

		// define some spacing
		$padding = 15;
		$line_width = 80;
		$scale = $line_width * 7.5;
		$bar_height = 10;

		$x = $y = $padding;

		// Allocate a large palette for drawing
		$image = ImageCreate (550, 800);
		$bg_colour = ImageColorAllocate($image, 224, 224, 224);
		$black = ImageColorAllocate($image, 0, 0, 0);

		// print the query
		$wrapped = explode ("\n", wordwrap($question, $line_width));
		foreach ($wrapped as $line)
		{
			ImageString($image,3,$x,$y,$line,$black);
			$y += 10;
		}

		$y += $padding;

		// print the results
		for ($i = 0; $i < count ($answers['query']); $i++)
		{

			// format percentages
			$percent = sprintf ('%1.1f', 100 * $answers ['qsos'][$i]/$total);
			$bar = sprintf ('%d', $scale * $answers ['qsos'][$i]/$total);

			// grab colour
			$c = $i % count($colours);
			$text_colour = ImageColorAllocate($image, $colours[$c][0],
							$colours[$c][1], $colours[$c][2]);

			// draw bar and percentage numbers
			ImageFilledrectangle ($image, $x, $y, $x + $bar,
						$y + $bar_height, $text_colour);

			ImageString ($image, 3, $x + $bar + $padding, $y, "$percent%", $black);

			$y += 10;

			// print query
			$wrapped = explode ("\n", wordwrap ($answers['query'][$i], $line_width));
			foreach ($wrapped as $line)
			{
				ImageString ($image, 2, $x, $y, $line, $black);
				$y += 12;
			}

			$y += 25;
		}

		// crop image by copying it
		$chart = ImageCreate (550, $y);
		ImageCopy ($chart, $image, 0, 0, 0, 0, 550, $y);

		//deliver image
		header ('Content-type: image/png');
		ImagePng($chart);

		//clean up
		ImageDestroy ($image);
		ImageDestroy ($chart);
	}

	// Start of program

	$qso_count = 0;
	$cw_count = 0;
	$dig_count = 0;
	$ssb_count = 0;

	
	// Connect to the database. The variables are stored in the include file db.inc
	if (! ($connection = @ mysql_connect($hostName,$username,$password)))
		die ("Could not connect to the database");

	// Connect to the log database. The error handler is defined in the error.inc include file
	if (!mysql_select_db ($databaseName, $connection))
		showerror();

	// Query the total number of QSOs
	if (! ($result = mysql_query ("	SELECT count(*) 
					FROM qsos
					WHERE fk_dxstn IN (2,3,4,5,10)",
			$connection)))
		showerror();

	$row = @ mysql_fetch_array ($result);

        $qso_count = $row['count(*)'];
	
	// Extract the number of QSOs made per mode
	if (! ($result = mysql_query ("	SELECT count(*), op_mode 
					FROM qsos 
					WHERE fk_dxstn IN (2,3,4,5,10) 
					GROUP BY op_mode",
			$connection)))
		showerror();

	// Fetch each row. Return as CW, DIG, SSB
	while ($row = @ mysql_fetch_array ($result))
	{
		$count = $row['count(*)'];
		$mode = $row['op_mode'];

		switch ($mode)
		{
			case "CW":
			$cw_count = $count;
			break;

			case "DIG":
			$dig_count = $count;
			break;

			case "SSB":
			$ssb_count = $count;
			break;
		}			
	}

	$question = 'QSOs per mode for 9M2/G4ZFE. Total number of QSOs = ' . $qso_count;

	$answers['query'][] = $cw_count . ' CW QSOs';
	$answers['qsos'][] = $cw_count;

	$answers['query'][] = $dig_count . ' Digital QSOs';
	$answers['qsos'][] = $dig_count;

	$answers['query'][] = $ssb_count . ' SSB QSOs';
	$answers['qsos'][] = $ssb_count;

	pc_bar_chart ($question, $answers);

	// Close the database connection
	if (!mysql_close ($connection))
		showerror();
?>
