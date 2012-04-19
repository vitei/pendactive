<?php

	session_start();

	include "func.php";

	$q = $_GET['q'];
	$id = $_GET['id'];
	

	connect();	
	
	$user = getLoggedInUser();
	
	
	if ($user && strlen($q)>2)
	{
	
		$users = sqlArray('SELECT * FROM users WHERE name LIKE '.sqlVar($q,true).' OR fullname LIKE '.sqlVar($q,true).' OR email LIKE '.sqlVar($q,true)." LIMIT 10");

		if ($users)
		{
			foreach($users as $u)
			{
				if ($u->id!=ANYBODY_ID && $u->id!=NOTICE_ID)
				{		
		      		echo '<div class="finduser-result" id="result'.$u->id.'">';
		  			echo 	'   <div style="float:left" class="avatar"><img src="/php/image.php?avatar='.$u->id.'&v='.$u->avatar.'"></div>';  		
					echo 		'<div style="margin-left: 70px; margin-top: 10px;">';
					echo		  '<button id="addbutton" class="button medium orange" style="float:right;" onclick="addMember('.$u->id.','.$u->avatar.',\''.$u->name.'\','.$u->id.');return false;"><i class="icon-plus"></i> Add Member</button>';
		      		echo 		  '<div >'.$u->name.'</div>';
		      		echo 	 	  '<div style="font-size:small;">'.$u->fullname.'</div>';		      		
		      		echo 	    '</div>';	   
		      		echo '</div>';
				}		
			}
		}else if (validEmail($q)){
			
			$tag = md5($q);
							
      		echo '<div class="finduser-result" id="result'.$tag.'">';
  			echo 	'   <div style="float:left" class="avatar"><img src="/php/image.php?avatar=0&v=1"></div>';  		
			echo 		'<div style="margin-left: 70px; margin-top: 10px;">';
			echo		  '<button id="addbutton" class="button medium orange" style="float:right;" onclick="addMember(0,0,\''.$q.'\',\''.$tag.'\'); return false;"><i class="icon-envelope"></i> Invite Member</button>';
      		echo 		  '<div >'.$q.'</div>';
      		echo 	    '</div>';	   
      		echo '</div>';
			
		}
			
		
	}
	
	disconnect();
		
?>