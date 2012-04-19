<?php

	session_start();

include "func.php";
include "import_func.php";


	$subject = $_POST['subject'];
	$note = $_POST['note'];
	$keywords = $_POST['keywords'];
	$assigned = $_POST['assigned'];
	$msgid	= $_POST['id'];
	$state = $_POST['state'];
	$parent = $_POST['parent'];
	$isFlat = $_POST['f'];
	$numCols = $_POST['c'];



	$public = $_POST['public'] ? 1 : 0;

	if (!$assigned)
		$assigned = ANYBODY_ID;
	
	$user = checkLoginCookies();		

	if (!$user)
		die("not user");
	
	connect();
	
		
	$pmsg = getMessage($parent);

	if ($pmsg)
		if (!getMember($pmsg,$user))
			die("not member");
	
	
		
	if ($msgid)
	{
		
		$q = "SELECT *";
		$q .= ",messages.id AS id";
		$q .= ",tags1.name as tag1name";
		$q .= ",tags2.name as tag2name";
		$q .= ",tags3.name as tag3name";	
		$q .= ",tags4.name as tag4name";	
		$q .= ",tags5.name as tag5name";	
		$q .= ",(SELECT COUNT(*) FROM comments WHERE message=messages.msgid AND project=messages.project) AS numcomments";
		$q .= " FROM messages";
		$q .= " LEFT JOIN tags tags1 ON tags1.id=tag1";
		$q .= " LEFT JOIN tags tags2 ON tags2.id=tag2";
		$q .= " LEFT JOIN tags tags3 ON tags3.id=tag3";
		$q .= " LEFT JOIN tags tags4 ON tags3.id=tag4";
		$q .= " LEFT JOIN tags tags5 ON tags3.id=tag5";

		$q .= " WHERE messages.id=".sqlVar($msgid);
		
		$msg = sqlObject($q);
		$state = $msg->state;
		
		
		//if ($msg->sender != $user->id && $msg->assigned != $user->assigned)
		//	die();
		
	}

/*
	setcookie("tags",$keywords,0,"/");
	
	touchUser($user);
*/


	if ($note)
	{
		$keywords=trim($keywords);
		$keywords = str_replace(","," ",$keywords);	
		$keywords = preg_replace('/\s\s+/', ' ',$keywords);		
		
		
		$keywords = str_replace(" ","+",$keywords);	
		$keywords = str_replace("　","+",$keywords);	
			
		$tnames = explode("+",$keywords);
		
		if ($user)
		{				
			$msg->plain = $note;
			$msg->subject = $subject;
			$msg->msgid = $msgid;
			$msg->state = $state;
			$msg->assigned = $assigned;
			$msg->gparent = $parent;
			$msg->tags = getTags($keywords,$user->id);
			$msg->public = $public;

			if ($msgid)
			{
				$msg = updateMessage($msg,$user);	
			}else
			{
				if ($parent)
				{
	
					if ($pmsg->root)
						$msg->root = $pmsg->root;
					else
						$msg->root = $pmsg->id;
				}else
					$msg->root = 0;
												
				$msg = addMessage($msg,$user);
				
			}


			echo "OK";
			//echo outputMessage($msg,$user,$member,true,$parent,$isFlat,$numCols);
			
			//die("here");


			if (!$msg->root)
			{


				$invites = sqlArray("SELECT * FROM invites WHERE message=".$msg->id);
				
				foreach($invites as $i)
				{
					$isInvited=false;
		            foreach($_POST as $k => $v)
	    	        {                                       
	            	   if ($k == "invite")
	        	       {
	        	       		if ($v == $i->email)
	        	       		{
	        	       			$isInvited=true;
	        	       			break;
	        	       		}	        	    	
	        	       }
	        	    }
	        	    
	        	    
	        	    if (!$isInvited)
	        	    {
	        	    	sql("DELETE FROM invites WHERE id=".$i->id);
	        	    }
				}
			
			
				//print_r($_POST);
				//die();
			
	            foreach($_POST as $k => $v)
	            {                                       
                   if (substr($k,0,6)=="invite")
	               {
	               
	               		$i = sqlObject('SELECT * FROM invites WHERE message='.$msg->id.' AND email='.sqlVar($v));
	               		
	               		if (!$i)
	               		{
	               		
			            	$inviteID = sqlInsert('INSERT INTO invites VALUES(0,'.$msg->id.',UNIX_TIMESTAMP(),'.sqlVar($v).')');	
	               		
							$subject = "You have been invited to join $msg->plain";
							$mail = "Hello,<br><br><b>$user->name</b> has invited you to join <b>$msg->plain</b> at <b>$sitename</b>.<br>";
			
							$mail .= "Click the following link to join:<br><br>";
							
							$url = CONFIG_BASEURL.'/join.php?id='.$inviteID.'&key='.makeInviteKey($msg->id,$v,$inviteID);
							$mail .= makeClickableLinks($url,false);
			
							sendEmail($v,$subject,$mail);	
							
	               		}	               		
	      
	               }
				}
			
			
            	sql('DELETE FROM members WHERE message='.$msg->id);
	    
	            $mq = '';
	    
	            $cnt=0;                 
	            foreach($_POST as $k => $v)
	            {                                       
	                    if (substr($k,0,6)=="member")
	                    {
	                    	if ($mq)
	                        	$mq .= ',';
	                    	$mq .= '(1,0,'.$v.','.$cnt.','.$msg->id.',0)';
	                        $cnt++;
	                    }
	            }
	            if ($mq)         
	            	sql('INSERT INTO members VALUES'.$mq);
    
    
    			//die($mq);
    
			}    
		
		
		
		
		
		}
	
	}
	disconnect();


?>