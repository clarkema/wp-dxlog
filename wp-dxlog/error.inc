<?php
	function showerror()
	{
		die ("Error " . mysql_errno() . " : " . mysql_error());
	}
?>
