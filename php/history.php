<?php

	session_start();

	include "func.php";

	$from = $_GET['f'];
	$parent = $_GET['p'];
	$uid = $_GET['u'];


	$user = checkLoginCookies();		

	connect();
	
	
	if ($user)
	{	
		if ($from)
		{
			if ($parent)			
				$last = sqlObject("SELECT * FROM history WHERE id>".sqlVar($from)." AND parent=".sqlVar($parent)." AND uid!=".sqlVar($uid)." ORDER BY id ASC LIMIT 1");
			else
				$last = sqlObject("SELECT * FROM history WHERE id>".sqlVar($from)." AND parent=0 AND user=".sqlVar($user->id)." AND uid!=".sqlVar($uid)." ORDER BY id ASC LIMIT 1");
			
			if ($last)
				echo "OK ".$last->id." ".$last->action." ".$last->message." ".$last->value;		
		}else
		{
			$last = sqlObject("SELECT * FROM history ORDER BY id DESC LIMIT 1");
			
			if ($last)
				echo "OK ".$last->id." ".ACTION_NONE;						
			
		}
				
	}	
	
	disconnect();


?>