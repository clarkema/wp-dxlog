<?php
	require_once('../wp-content/plugins/wp-dxlog/dbinc.php');
	require_once('../wp-content/plugins/wp-dxlog/error.inc');
?>

<h2>Upload Cabrillo/ADIF Log</h2>
<p> Please select the filename of the log to be uploaded into the database. 
<br><b>Only Cabrillo or ADIF format logs are accepted</b>
<p>
<table BORDER="0" CELLPADDING="5" CELLSPACING="10">
<tr>
<td BGCOLOR="lightyellow">

<form action="/wp-admin/admin.php?page=wp-dxlog/admin/readlog.php" enctype="multipart/form-data" method="post">
    <br>Filename:&nbsp;<input name="userfile" type="file">
    <p>
        Callsign:&nbsp;
        <select name="callsign">

<?php

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

</body>
</html>
