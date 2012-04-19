<?php

	session_start();

	include "func.php";

	$id = $_GET['id'];
	$parent = $_GET['parent'];


	$user = checkLoginCookies();		

	connect();

	
	if ($user)
	{
	
		$msg = getMessage($id);

		if ($msg)
		{
			if (getMember($msg,$user))
			{

				sql("UPDATE messages SET deleted=1 WHERE id=".$msg->id);
				sql("UPDATE messages SET deleted=1 WHERE id IN (SELECT cmessage FROM children WHERE pmessage=".$msg->id.")");				


				$children = sqlArray("SELECT * FROM children WHERE pmessage=".$msg->id);
								
				foreach($children as $c)
				{
					sql("DELETE FROM children WHERE pmessage=".$c->cmessage." OR cmessage=".$c->cmessage);		
				}
				
		
				sql("DELETE FROM children WHERE pmessage=".$msg->id." OR cmessage=".$msg->id);		
				sql("DELETE FROM invites WHERE message=".$msg->id);		
				
				
		
		//		sql("DELETE FROM messages WHERE id=".sqlVar($id));		
		//		sql("DELETE FROM messages WHERE id IN (SELECT cmessage FROM children WHERE pmessage=".sqlVar($id));
		
		
		//		sql("DELETE FROM comments WHERE gparent=".sqlVar($id)." AND gparent=".sqlVar($parent));		
		//		sql("DELETE FROM marked WHERE =".sqlVar($id)." AND gparent=".sqlVar($parent));		
								
				addHistory(ACTION_DELETE,$msg->id,0,$parent,$user);
				if ($parent)
				{
					$pmsg = getMessage($parent);
					if ($pmsg)
						addHistory(ACTION_EDIT,$pmsg->id,0,$pmsg->gparent,$user);
				}
				
				
				
				echo "OK";


			}
		}
		

	}	
	
	disconnect();


?>