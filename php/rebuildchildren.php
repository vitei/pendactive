<?php


	include "func.php";


	connect();	


		sql("DELETE FROM children");
		$all = sqlArray("SELECT * FROM messages WHERE !deleted");
		
		foreach($all as $msg)
		{
			
			$cnt=0;
			$pmsg = $msg;
			do
			{
				$p = $pmsg->gparent;
				sql("INSERT INTO children VALUES($p,$msg->id)");
				$cnt++;		
				
				if ($cnt>100)
				{
					echo "WARNING: message $msg->id is recursive<br>";
					break;
				}
				
				$pmsg = sqlObject('SELECT * FROM messages WHERE id='.$p);
				
												
			}while($p);
			
			//echo "done: $cnt<br>";
		}


	echo "finished";
	
	
	
	

	disconnect();


?>