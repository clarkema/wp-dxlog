<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN""http://www.w3c.org/TR/1999/REC-html401-19991224/loose.dtd">
<html>
<head>
<title>Upload Cabrillo/ADIF Log</title>
</head>

<body>
<h2>Upload Cabrillo/ADIF Log</h2>
<p> Please select the filename of the log to be uploaded into the database. 
<br><b>Only Cabrillo or ADIF format logs are accepted</b>
<p>
<table BORDER="0" CELLPADDING="5" CELLSPACING="10">
<tr>
<td BGCOLOR="lightyellow">
<form action="readlog.php" enctype="multipart/form-data" method="post">
<br>Filename:&nbsp;<input name="userfile" type="file">
<p>Callsign:&nbsp;
<select name="callsign">

<?php
	include 'dbinc.php';
	include 'error.inc';

	// Connect to the database
	if (!($connection = @ mysql_connect ($hostName,
                                        $username,
                                        $password)))

	die ("Could not connect to database");

	// Select the radiolog database
	if (!mysql_select_db ($databaseName, $connection))
		showerror();

	// Display all the available DX callsigns in order
	$query = "SELECT dxcallsign FROM dxstation order by id";

	if (!($result = @ mysql_query ($query, $connection)))
		showerror();

	$i = 0;

	// Display each DX callsign in a drop down menu. 
	while ($row = @ mysql_fetch_array ($result))
	{
		$i++;
		if ($i == 1)
			echo "<option value=" . $i . " selected>" . $row['dxcallsign'] . "\n";
		else
			echo "<option value=" . $i . ">" . $row['dxcallsign'] . "\n";

	}

?>

</select>
<p><input type="reset" value="Reset">
<input type="submit" value="Upload Log">
</form>

</td>
</tr>
</table>

<?php
	echo "<H3><BR><BR><BR><BR>Last 10 logs uploaded</H3>\n";
	
	// Display the last 10 files uploaded
	$query = "SELECT * FROM logfiles order by id DESC LIMIT 10";

	if (!($result = @ mysql_query ($query, $connection)))
		showerror();

	echo "\n<table border=1 width=75% bgcolor=\"lightyellow\">";

	echo "\n<tr>";
	echo "\n\t<th>Filename</th>";
	echo "\n\t<th>No. QSOs</th>";
	echo "\n\t<th>File type</th>";
	echo "\n\t<th>Date uploaded</th>";
	echo "\n</tr>";

	while ($row = @ mysql_fetch_array ($result))
	{
		echo "\n<tr>" .
		     "\n\t<td>{$row["filename"]}</td>" .
		     "\n\t<td>{$row["qsos"]}</td>" .
		     "\n\t<td>{$row["filetype"]}</td>" .
		     "\n\t<td>{$row["loaded"]}</td>";

	}

	echo "\n</table>";		

	// Disconnect from the database
	if (!mysql_close ($connection))
   	showerror();
?>


</body>
</html>
