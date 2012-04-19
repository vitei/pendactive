<?php

	include "func.php";

	$text = $_POST['text'];

	if (!$text)
		die();

	$user = checkLoginCookies();		

		
	if ($user)
	{	
		$subject = "Feedback from $user->name";
		
		$body = "$user->email ($user->name) wrote:<br><br>".$text;
		
		sendEmail("feedback@pendactive.com",$subject,$body);	
	}	
	



?>