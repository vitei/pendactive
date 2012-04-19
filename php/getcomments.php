<?php

	session_start();

include "func.php";

	$id = $_GET['id'];
	
	$user = checkLoginCookies();		

	connect();	
	

	$msg = canAccessMessage($id,$user);

	if ($msg)	
	{
		$q = 'SELECT comments.time AS time,comments.user AS user,comments.text AS text, comments.id AS cid,users.name AS name FROM comments';
		$q .= ' JOIN users ON users.id=comments.user';
		$q .= ' WHERE gparent='.sqlVar($msg->id);
		$q .= ' ORDER BY comments.id';
		$comments = sqlArray($q);
		

		$out = "";
		foreach($comments as $c)
		{
			$out .= commentHTML($c->cid,$c->name,$c->text,$c->user==$user->id,$c->time);		
		}
	
		echo $out;
	}
	
	disconnect();


?>