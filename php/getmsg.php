<?php

	session_start();

	include "func.php";

	$id = $_GET['id'];
	$parent = $_GET['parent'];
	$isFlat = $_GET['f'];
	$numCols = $_GET['c'];

	$user = checkLoginCookies();		


	connect();
	
	
	if ($user)
	{
			
		$msg = canAccessMessage($id,$user);
		if ($msg)
		{
			echo outputMessage($msg,$user,$member,true,$parent,$isFlat,$numCols);
		}

				
	}	
	
	disconnect();


?>