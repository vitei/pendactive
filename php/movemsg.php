<?php

	session_start();

include "func.php";

	$id = $_GET['id'];
	$parent = $_GET['parent'];
	$state = $_GET['state'];
	$before = $_GET['before'];
	$after = $_GET['after'];
	$into = $_GET['into'];
	$uid = $_GET['uid'];
		
	connect();
	
	$user = getLoggedInUser();
	
	if ($user)
	{
		$msg = getMessage($id);
		
		if ($msg->root)
			$rmsg = getMessage($msg->root,$user);
		
		if ($state==-1 || $state==3)
			$state = $msg->state; 
		
		if ($before)
			$bmsg = sqlObject("SELECT * FROM messages WHERE id=".sqlVar($before));
		if ($after)	
			$amsg = sqlObject("SELECT * FROM messages WHERE id=".sqlVar($after));

		/*
		if (       $msg->assigned == ANYBODY_ID 
				|| $msg->assigned == $user->id 
				|| $msg->sender == $user->id 
				|| ($rmsg && ($rmsg->assigned==$user->id || $rmsg->sender==$user->id))
			)
		*/	


		if (($rmsg && $rmsg->memberid) || $msg->sender == $user->id)
		{
			if ($bmsg)
			{
				sql("UPDATE messages SET ordering=ordering+1 WHERE ordering>".$bmsg->ordering." AND gparent=".sqlVar($parent));
				$ordering = $bmsg->ordering+1;
			}else if ($amsg)
			{
				sql("UPDATE messages SET ordering=ordering+1 WHERE ordering>=".$amsg->ordering." AND gparent=".sqlVar($parent));
				$ordering = $amsg->ordering;
			}else if (isset($into))
			{
							
				if ($into)
				{	
					$imsg = getMessage($into,$user);				
					
					if (!$imsg->memberid)
						die("NG");
					
					$msg->gparent=$imsg->id;
					if ($imsg->root)
						$msg->root = $imsg->root;
					else
						$msg->root = $imsg->id;
				}
				else
				{
					sql("INSERT INTO members VALUES(1,0,$user->id,0,$msg->id,0)");
					$msg->gparent=0;
					$msg->root=0;
				}
				
				sql("UPDATE messages SET gparent=".sqlVar($msg->gparent).", root=".sqlVar($msg->root)." WHERE id=".sqlVar($msg->id));
				
				sql("UPDATE messages SET root=".sqlVar($msg->root)." WHERE id IN (SELECT cmessage FROM children WHERE pmessage=".sqlVar($msg->id).")");
				
				if ($imsg)
				{
					$lmsg = sqlObject("SELECT ordering FROM messages WHERE gparent=".sqlVar($imsg->id)." ORDER BY ordering DESC LIMIT 1");
					$ordering = $lmsg->ordering+1;
				}
				
				relocateChildrenTree($msg);


			}else
			{
				if ($parent)
				{
					$lmsg = sqlObject("SELECT ordering FROM messages WHERE gparent=".sqlVar($parent)." ORDER BY ordering DESC LIMIT 1");
					$ordering = $lmsg->ordering+1;
				}else
				{
					$ordering = $user->ordering+1;
					sql("UPDATE users SET ordering=ordering+1 WHERE id=".$user->id);		
					
				}
					 
			}	
		
			//sql("UPDATE projects SET num_edits=num_edits+1 WHERE id=".$user->project);
			
			$q = "UPDATE messages SET";
			$q .= " udate=UNIX_TIMESTAMP()";
			$q .= ",state=".sqlVar($state);
			$q .= ",ordering=".$ordering;
			$q .= " WHERE id=".sqlVar($id);
			
			
			$r = sql($q);		
			
			
			//if ($r)
			{
			
			
				if ($before>0)
					addHistory(ACTION_MOVE_BEFORE,$id,$before,$parent,$user,$uid);
				else if ($after>0)
					addHistory(ACTION_MOVE_AFTER,$id,$after,$parent,$user,$uid);
				else if (isset($into))
				{
					addHistory(ACTION_MOVE_INTO,$id,$into,$parent,$user,$uid);
					addHistory(ACTION_NEW,$msg->id,$msg->state,$into,$user,$uid);		
					
					
				
					// mark old parent as being edited
					if ($parent)
					{
						$pmsg = getMessage($parent);
						if ($pmsg)
							addHistory(ACTION_EDIT,$pmsg->id,0,$pmsg->gparent,$user,$uid);
					}
					
								
				}
				else
					addHistory(ACTION_MOVE_TOP,$id,$state,$parent,$user,$uid);
				
				if ($msg->gparent)
				{
					$pmsg = getMessage($msg->gparent);
					if ($pmsg)
						addHistory(ACTION_EDIT,$pmsg->id,0,$pmsg->gparent,$user,$uid);
				}
					
			
			
				echo "OK";
				
	
				
				if ($msg->state != $state || $into)
				{
					if ($msg->sender != $user->id)
						sendTo($user,$msg->sender,$msg,$state);
					
					if ($msg->assigned != $user->id && $msg->assigned!=$msg->sender)
						sendTo($user,$msg->assigned,$msg,$state);
				}
				
					
			}		
			
			
				
		}else{
			echo "NG";
		}
			

	}		

			
	
	disconnect();

				
	function sendTo($user,$uid,$msg,$newstate)
	{
		
		$suser = sqlObject("SELECT * FROM users WHERE id=".$uid);
		
		if ($suser && $suser->notify)
		{
			$rmsg = getMessage($msg->root);
			if ($rmsg)
			{
				$subject = "Note #$msg->msgid in $rmsg->plain has moved";
				$mail = "<b>$user->name</b> has moved note #$msg->msgid in $rmsg->plain to <b>".stateName($newstate)."</b><br>";
	
				$mail .= "<blockquote>'".nl2br(htmlspecialchars($msg->plain))."'</blockquote>";
	
				$mail .= makeClickableLinks(CONFIG_BASEURL.'/?p='.$msg->gparent.'&h='.$msg->id);
				
				
				sendEmail($suser->email,$subject,$mail);		
			}
		}
	}
			


?>