<?php

	session_start();

	include "func.php";

	$id = $_GET['id'];
	$parent = $_GET['parent'];

	$user = checkLoginCookies();		

	connect();

	
	if ($user)
	{
	
		sql("DELETE FROM members WHERE message=".sqlVar($id)." AND user=".$user->id);			
		sql("DELETE FROM followers WHERE message=".sqlVar($id)." AND user=".$user->id);			
		
		echo "OK";
	}	
	
	disconnect();


?>