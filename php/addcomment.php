<?php

	session_start();

include "func.php";
include "import_func.php";

	$msgid = $_GET['id'];
	$text = $_POST['text'];
	$parent = $_GET['parent'];
	$uid = $_GET['uid'];

	if (!$text)
		die();


	$user = checkLoginCookies();		

	connect();	
	
	
	if ($user)
	{
	
		$msg = getMessage($msgid);
	
		if (!getMember($msg,$user))
			die();
	
	
		$urls = getURLs($text);
		foreach($urls as $u)
		{
			processImage($u);
		}
		
		
		$q = 'INSERT INTO comments ';
		$q .= 'VALUES(';
		$q .= '0';
		$q .= ','.sqlVar($msgid);
		$q .= ',0';
		$q .= ','.$user->id;
		$q .= ','.sqlVar($text);
		$q .= ",UNIX_TIMESTAMP()";
		$q .= ",".sqlVar($msg->id);
		$q .= ')';
			

		
		$cid = sqlInsert($q);
				
		$out = commentHTML($cid,$user->name,$text,true,time());
		
		echo $out;
	


		addHistory(ACTION_EDIT,$msg->id,0,$parent,$user,$uid);
		
		if ($msg->assigned != $user->id)
			sendTo($user,$msg->assigned,$msg,$text);
			
		if (($msg->sender != $user->id) && ($msg->sender!=$msg->assigned))
			sendTo($user,$msg->sender,$msg,$text);
			
	}	
	
	disconnect();


	function sendTo($user,$uid,$msg,$text)
	{
		
		$euser = sqlObject("SELECT * FROM users WHERE id=".$uid);
		if ($euser && $euser->notify)
		{		
			$rmsg = getMessage($msg->root);
			if ($rmsg)
			{
				$subject = "Comment added to note #".$msg->msgid.' in '.$rmsg->plain;
				$mail = "<b>$user->name</b> has added the following comment to note #$msg->msgid in $rmsg->plain<br>";
			
				$mail .= "<blockquote>'".nl2br(htmlspecialchars($text))."'</blockquote>";
				$mail .= makeClickableLinks(CONFIG_BASEURL.'/?p='.$msg->gparent.'&h='.$msg->id);
			
				sendEmail($euser->email,$subject,$mail);	
			}	
		}
	}		


?>