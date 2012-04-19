<?php

	session_start();

include "func.php";

	$id = $_GET['id'];
	$parent = $_GET['parent'];

	$user = checkLoginCookies();		

	
	connect();	
	
	if ($user)
	{
		//if (!$parent)
		//	$parent = $user->root;
			
		
	//	if ($msg)
		{
		
			$comment = sqlObject('SELECT * FROM comments WHERE id='.sqlVar($id));
			
			addHistory(ACTION_EDIT,$comment->message,0,$parent,$user);
			
			
			$r = sql('DELETE FROM comments WHERE id='.sqlVar($id).' LIMIT 1');
			
			
			if ($r)
				echo "OK";
		}
	}	
	
	disconnect();


?>