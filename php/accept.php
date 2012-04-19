<?php

	session_start();

	include "func.php";

	$id = $_POST['id'];
	$key = $_POST['key'];
	$accept = $_POST['accept'];
	
		
	connect();	
	
	
	$invite = sqlObject("SELECT * FROM invites WHERE id=".sqlVar($id));

	if ($key == makeInviteKey($invite->message,$invite->email,$invite->id))
	{
		if ($accept)
		{
			$user = getLoggedInUser();
			
			if ($user && $user->email != $invite->email)
			{
				//logout();
				$user = null;
			}
			
			if (!$user)
			{
				$user->email = $invite->email;
				$user->id = createUser($user->email);
				login($user);
			}
		
		
			connect();	
			sql('INSERT INTO members VALUES(0,0,'.$user->id.',0,'.$invite->message.',0)');
			
			sql("DELETE FROM invites WHERE id=".sqlVar($id));
			
			header("Location:/settings.php");
			
			
		}else
		{
			sql("DELETE FROM invites WHERE id=".sqlVar($id));
			header("Location:/");
		}
	}
	
	disconnect();


?>