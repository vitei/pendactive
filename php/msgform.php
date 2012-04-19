<?php

	session_start();

	include "func.php";

	$msgid = $_GET['id'];
	$state = $_GET['state'];
	$parent = $_GET['parent'];
	$isFlat = $_GET['f'];
	$numCols = $_GET['c'];
	
	$user = checkLoginCookies();		


	connect();	
	
	
	$canedit = true;
	$canquit = false;
	
//	if ($user)
	{
			
		if ($msgid)
		{
			$msg = canAccessMessage($msgid,$user);
			
			if ($msg->root)
				$rmsg = getMessage($msg->root,$user);
			
			$canedit = $msg->memberid 
					   && 
					   (
					    $msg->sender == $user->id 
					    || $msg->assigned == $user->id 
					    || $msg->assigned == ANYBODY_ID 
					    || ($rmsg && ($rmsg->assigned==$user->id || $rmsg->sender==$user->id))
					   );
			
			$canmember = $canedit && !$msg->root;
			
			$canquit = !$canedit && !$msg->root;
			
			
		}else
		{
			$canmember = $parent==0;		
		}
		
		
		$pid = 0;
		
		if ($msg)
			$pmsg = $msg;
		else if ($parent)
			$pmsg = sqlObject('SELECT * FROM messages WHERE id='.$parent);
		
		
		if ($pmsg->root)
			$pid = $pmsg->root;
		else
			$pid = $pmsg->id;	
    
		if ($pid)  
		  	$users = getMembers($pid);
		else
			$users[] = $user;  
  		
		echo '<form id="addmsg" name="addmsg" method="post" action="javascript:sendMessage('.$msgid.');" enctype="multipart/form-data" >';
	
		
		
?>	
<div class="intro single">
	
		<?php
		
			 
		    if ($canedit && $canmember)
		    {
		    	if ($msg && $msg->public)
		    		$checked = 'checked="checked"'; 
		    
				echo '<div class="iphone-toggle-buttons privacy" >';
				echo '    <ul>';
				echo '        <li><label><input type="checkbox" name="public" id="public" '.$checked.'/><span></span></label></li>';
				echo '    </ul>';
				echo '</div>';
			}
		
			echo '<div id="title">';
			 
			 

			 
			if ($msg)
			{
				if ($msg->root)
					$notenum = "#".$msg->msgid;
				else
					$notenum = "";

				echo "note ".$notenum;
					
			}else 
			{
				//echo "add to ";
				
				echo '<div >';
				echo '<span onclick="var e=this.parentNode.firstChild; while(e){e.className=\'\'; e=e.nextSibling;}  this.className=\'pending\'; document.forms.addmsg.state.value=0;" style="cursor:pointer" class="'.($state==0?'pending':'').'">pending</span> • ';
				echo '<span onclick="var e=this.parentNode.firstChild; while(e){e.className=\'\'; e=e.nextSibling;}  this.className=\'active\'; document.forms.addmsg.state.value=1;"  style="cursor:pointer" class="'.($state==1?'active':'').'">active</span> • ';
				echo '<span onclick="var e=this.parentNode.firstChild; while(e){e.className=\'\'; e=e.nextSibling;}  this.className=\'done\'; document.forms.addmsg.state.value=2;"  style="cursor:pointer" class="'.($state==2?'done':'').'">done</span> ';
				echo '</div>';
				
			}
			
			?>
			
	</div>

		<?php
		
		
		echo '<input type="hidden" name="id" value="'.$msgid.'">';
		echo '<input type="hidden" name="state" value="'.$state.'">';
		echo '<input type="hidden" name="parent" value="'.$parent.'">';
		echo '<input type="hidden" name="f" value="'.$isFlat.'">';
		echo '<input type="hidden" name="c" value="'.$numCols.'">';
		
				
		if (!$canedit)
		{
			$readonly = 'readonly="readonly"';
			$disabled = 'disabled="disabled"';
		}
		?>
		
		
		<div class="table">
		  <table width="80%" border="0" align="center" cellpadding="5">
		    <tr>
		      <td>
		      
		      <?php
		      echo '<textarea name="note" id="note" cols="50" rows="7" '.$readonly.' placeholder="Type note text here."/>';
		      
		      if ($msg) 
		      	echo $msg->plain; 
		      	
		      echo '</textarea>';
		      
		      ?>
		      
		      </td>
		    </tr>
		    
		    
		    <tr>
		    
		      
		      <td >
		      <?php


		      	$anybody = sqlArray("SELECT * FROM users WHERE id=".NOTICE_ID);

				$users = array_merge($anybody,$users);


		      	$assigned = $msg?$msg->assigned : $_SESSION['userid'];
		      	
		      	
				echo '<input type="hidden" name="assigned" value="'.$assigned.'">';
		      	
		      	echo '<div id="members">';
		      	foreach($users as $u)
		      	{
		      		$special = $u->id == ANYBODY_ID || $u->id == NOTICE_ID;
		      	
		      		if ($assigned == $u->id)
		      			$selc = "selected";
		      		else
		      			$selc = "";
		      	
	   				echo '<div id="member'.$u->id.'" class="member spaced '.$selc.'" >';
		   				
	   				if ($u->id!=$user->id && $canedit && !$special && $canmember)
		      			echo '<a href="javascript:removeMember('.$u->id.');"><i class="icon-remove"></i></a>';
		      			
		      		echo '<p>'.$u->name.'</p>';
		      		
			      	
			      	
			      	if ($canedit)	
						$sel = 'onclick="selectUser('.$u->id.')"';
					else
						$sel = '';				      		

		      			

	      			echo '<div id="avatar'.$u->id.'" class="avatar" '.$sel.'><img  src="/php/image.php?avatar='.$u->id.'&v='.$u->avatar.'"></div>';
			      		
	      			
	      			if (!$special)
		      			echo '<input type="hidden" name="member'.$u->id.'" value="'.$u->id.'">';
	   				echo '</div>';

		      	}
		      	
		      	if ($msg)
		      	{
		      		$invites = sqlArray("SELECT * FROM invites WHERE message=".$msg->id." ORDER BY id ASC");
		      		
		      		foreach($invites AS $i)
		      		{
		      			$tag = md5($i->email);	
		   				echo '<div id="member'.$tag.'" class="member spaced" >';
		   				
		   				if ($canedit && $canmember)
			      			echo '<a href="javascript:removeMember(\''.$tag.'\');"><i class="icon-remove"></i></a>';
			      			
			      		echo '<p>'.$i->email.'</p>';
			      		
				      	
		      			echo '<div id="avatar" class="avatar"><img  src="/php/image.php?avatar=0&v=0"></div>';
				      		
		      			
		      			echo '<input type="hidden" name="invite'.$tag.'" value="'.$i->email.'">';
			      			
		   				echo '</div>';
		      		}
		      	
		      	}
		      	
		      	
		      
		      	echo '</div>';
		      	
		      	if ($canmember)	
		       		echo '<div style="clear:both; padding-bottom:10px;"></div><input name="search" placeholder="Type name or email address to invite more people." type="text" id="search" onkeyup="tryMemberSearch(this.value); return true;" onkeypress="return ignoreEnter(event);" />';
		       
		      	
		      ?>
		      
		      
			  </td>
		    </tr>
		    
		    <?php 
		    /*
   		      if ($msg)
		      {
		      	$tags = $msg->tag1name.' '.$msg->tag2name.' '.$msg->tag3name.' '.$msg->tag4name.' '.$msg->tag5name;
		      	$tags = trim($tags);
			  }		      

		    if ($canedit || $tags)
		    {
    
		   	echo '<tr>';
		    echo '<td valign="top" ><div class="fieldtitle">Tags: </div>';
		    echo '</div></td>';
		    echo  '<td >';
		      
		      
		      
		      	echo '<input name="keywords" type="text" id="keywords" size="50" value="'.$tags.'" '.$readonly.' placeholder="Separate with spaces, Max. 5"/>';
		      
		     echo'</td>';
		    echo '</tr>';
		    
 		    } 
			  */
 		    ?>

		    
	    
		    <tr>
		      <td>
		      <?php 
		      
				echo '<button class="button large lgray" onclick="closePopup();return false;"><i class="icon-remove"></i> Cancel</button>';
			  
			  
	       		echo '<div id="status" style="float:right;"></div>';
 		      	if ($msg)
 		      	{

	 				if ($canedit)
	 				{
				      echo '<button class="button large blue" name="Submit" onclick="this.style.visibility=\'hidden\';"><i class="icon-ok"></i> Save Note</button>';
//				      echo '<button class="button medium red" style="float:right" name="Submit" onclick="closePopup(); askDelete('.$msgid.');return false;" ><i class="icon-trash" ></i> Delete Note</button>';
//				    }else if ($canquit)
//				    {
//				      echo '<button class="button medium red" style="float:right" name="Submit" onclick="closePopup(); askQuit('.$msgid.');return false;" ><i class="icon-trash"></i> Remove Note</button>';
				    }
				    
			  	}else
			  	{
			     	echo '<button class="button large blue" name="Submit" onclick="this.style.visibility=\'hidden\';"><i class="icon-ok"></i> Add Note</button>';
			  	}
			  
			   
				?>
		      </td>
		    </tr>


		    
		    
		    <?php	    
		    if ($canedit && $canmember)
		    {
		    ?>
		   <tr>
		      <td >
		       
		       <div id="results"></div>
		       
		       
		      </td>
		    </tr>
		    
		    <?php
		    }
		    ?>
		    
		    
		  </table>
		</div>  
	</div>
	</div>
	
	
	
	
	
	
	
<?php	
	}	
	
	disconnect();

	echo'</form>';


function getMembers($id)
{
	return sqlArray("SELECT users.id AS id,users.avatar AS avatar,users.name AS name FROM members JOIN users ON users.id=members.user WHERE members.message=".$id.' GROUP BY users.id ORDER BY name');
}

?>