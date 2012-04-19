<?php


function findPlixiImage($data)
{
	$regexp = "<a\s[^>]*href=\"([^\"]*)\"\sid=\"([^\"]*)\"[^>]*>(.*)<\/a>";
	
	if(preg_match_all("/$regexp/siU", $data, $matches, PREG_SET_ORDER)) 
	{
		foreach($matches as $match) 
		{
			if ($match[2] == "embed")
				return $match[1];
		}
	}
		
	return false;
}
function findTwitPicImage($data)
{
	$regexp = "<img\s[^>]*id=\"([^\"]*)\"\ssrc=\"([^\"]*)\"[^>]*>";
	
	if(preg_match_all("/$regexp/siU", $data, $matches, PREG_SET_ORDER)) 
	{
		foreach($matches as $match) 
		{
			if ($match[1] == "photo-display")
				return $match[2];
		}
	}
			
	return false;
}

function findYfrogImage($data)
{
	$regexp = "<img\s[^>]*id=\"([^\"]*)\"[^>]*src=\"([^\"]*)\"[^>]*>";
	
	if(preg_match_all("/$regexp/siU", $data, $matches, PREG_SET_ORDER)) 
	{
		foreach($matches as $match) 
		{
			if ($match[1] == "main_image")
				return $match[2];
				
		}
	}
	
	//die("yfrog");
			
	return false;
}


function findEmbededImage($url,$data)
{
	if (stristr($url,"plixi.com"))
		$u = findPlixiImage($data);
	else if (stristr($url,"twitpic.com"))
		$u = findTwitPicImage($data);
	else if (stristr($url,"yfrog.com"))
		$u = findYfrogImage($data);
	else 
	{
		$u = findTwitPicImage($data);
		if (!$u)
			$u = findPlixiImage($data);
		if (!$u)
			$u = findYfrogImage($data);
	}
	
	if ($u)	
		return @file_get_contents($u);
		
	return false;
}


function processImageURL($url)
{
//	ini_set('user_agent', 'Opera/9.80 (Windows NT 6.0; U; en) Presto/2.8.99 Version/11.10');

	$data = @file_get_contents($url);
	
	if ($data)
	{
		//$embed = findEmbededImage($url,$data);
		//if ($embed)
		//	return processImage($embed,false,$userid,$msgid,$num);
		
			
		if (processImage($data,false))
			return true;

	}
	return false;	
}


function processImage($url)
{
	$data = @file_get_contents($url);



	$src = @imagecreatefromstring($data);		
	
	if ($src)
	{
		
		$fname = md5($url);
	
		$size = 100;
	
	
		$orientation = 0;
		$longitude = 0;
		$latitude = 0;
		$city = "";
		$country = "";
		
		
		if ($exif != FALSE)
		{
			/*
			$loc = exifGPS($exif);
			
			if ($loc)
			{
				$longitude = $loc->lng;
				$latitude = $loc->lat;	
			
				$c = GPStoCity($loc->lng, $loc->lat);
				
				if ($c)
				{		
					$city = $c->city;
					$country = $c->country;
				}
			}
			
			
			$orientation = $exif['Orientation'];
			
		
			$xml = new array2xml($exif);	
			file_put_contents(CONFIG_DATADIR."/images/".$fname."_exif.xml",$xml->output);
			
			
			switch($orientation)
			{
				case 6:
					$src = imagerotate($src,-90.0,0);
					break;
				case 8:
					$src = imagerotate($src,90.0,0);
					break;
				case 3:
					$src = imagerotate($src,180.0,0);
					break;
			}
		
			*/
	
		}
			
		
	
		$width = imagesx($src);
		$height = imagesy($src);
	
		if (($width > $size) || ($height > $size))
		{
			if ($width>$height)
			{
				$height = $size*($height/$width);
				$width = $size;
			}else
			{	
				$width = $size*($width/$height);
				$height = $size;
			}
		}
		
		
		
		$dst = imagecreatetruecolor($width,$height);	
	
		imagecopyresampled($dst,$src,0,0,0,0,$width,$height,imagesx($src),imagesy($src));		
		imagejpeg($dst,CONFIG_DATADIR."/thumbs/".$fname."-".$size.".jpg");
		
		
		/*
		if (!is_numeric($orientation))
			$orientation=0;
			
		
		
		$q = "INSERT INTO images VALUES(";
		$q .= "0";
		$q .= ",".sqlVar($fname);
		$q .= ",".imagesx($src);
		$q .= ",".imagesy($src);	
		$q .= ",".($exif ? "1":"0");
		$q .= ",".$orientation;
		$q .= ",".$longitude;
		$q .= ",".$latitude;
		$q .= ",".sqlVar($city);
		$q .= ",".sqlVar($country);
		$q .= ",".$userid;
		$q .= ",".$msgid;
		$q .= ",".$num;
		$q .= ")";
		
		
		sqlInsert($q);
		*/
		
		return true;
	}	
	
	return false;
	
}



function processAttachments($userid,$msg)
{
	$files=0;

	if (count($msg->attachments) && $msg->id)
	{					
		
		foreach($msg->attachments as $file)
		{														
			$fname = $msg->id.'_'.key($msg->attachments);
			$fname = safeFileName($fname);
			

			$path = CONFIG_DATADIR."/files/".$fname;							
			file_put_contents($path,$file);	
									
			$type =  getFileType($path);
			
			$q = "INSERT INTO files VALUES(";
			$q .= "0";
			$q .= ",".sqlVar($fname);
			$q .= ",".sqlVar($type);
			$q .= ",UNIX_TIMESTAMP()";	
			$q .= ",".$userid;
			$q .= ",".$msg->id;
			$q .= ")";
			
			sqlInsert($q);
			
			$files++;
													
		}						
		
	}		
	
	if ($files)
	{
		sql("UPDATE messages SET files=$files WHERE id=$msg->id");
	}
	
}

		
function getTags($to,$userid)
{
	$maxTags = 5;

	$tags = array();
	for($t = 0; $t<$maxTags; $t++)
		$tags[$t] = 0;
		
	$tnames = explode("+",$to);
		
	$tnames = array_unique($tnames);
	 
	$cn=0;
					
	foreach($tnames as $t)
	{
		$tag = sqlObject("SELECT * FROM tags WHERE name=".sqlVar($t));
		
		if ($tag)
		{
			$tags[$cn] = $tag->id;
		}else
		{					
			$q = "INSERT INTO tags VALUES(";
			$q .= "0";
			$q .= ",".sqlVar($t);
			$q .= ",UNIX_TIMESTAMP()";
			$q .= ",".$userid;
			$q .= ",0";
			$q .= ")";
			
			$tags[$cn] = sqlInsert($q);
		}
		
		$cn++;
		
		if ($cn >= $maxTags)
			break;
	}
	
	return $tags;
}		



function sendAssignMail($uid,$msg,$new)
{
	$notify = sqlObject("SELECT * FROM users WHERE id=".sqlVar($uid));
	if ($notify && $notify->notify)
	{		
		$rmsg = getMessage($msg->root);
		if ($rmsg)
		{
			$assuser = sqlObject("SELECT * FROM users WHERE id=".sqlVar($msg->assigned));
		
			if ($new)
			{
				$subject = "Note #$msg->msgid in $rmsg->plain has been created.";
				$mail = "Note #".$msg->msgid." has been created and assigned to <b>$assuser->name</b> in <b>$rmsg->plain</b>:<br>";
			}else
			{
				$subject = "Note #$msg->msgid in $rmsg->plain has been re-assigned.";
				$mail = "Note #".$msg->msgid." has been re-assigned to <b>$assuser->name</b> in <b>$rmsg->plain</b>:<br>";
			}	
			
			$mail .= "<blockquote>'".nl2br(htmlspecialchars($msg->plain))."'</blockquote>";
			$mail .= makeClickableLinks(CONFIG_BASEURL.'/?p='.$msg->gparent.'&h='.$msg->id);
			
			sendEmail($notify->email,$subject,$mail);	
		}		
	}
}
		
		
						
function updateMessage($msg,$user)
{

	$msg->html = mecab($msg->plain);

	//sql("UPDATE projects SET num_edits=num_edits+1 WHERE id=".$msg->project);

	$oldMsg = sqlObject("SELECT * FROM messages WHERE id=".sqlVar($msg->id));

	$q = "UPDATE messages";
	$q .= " SET tag1=".$msg->tags[0];
	$q .= " ,tag2=".$msg->tags[1];
	$q .= " ,tag3=".$msg->tags[2];
	$q .= " ,tag4=".$msg->tags[3];
	$q .= " ,tag5=".$msg->tags[4];
	$q .= " ,plain=".sqlVar($msg->plain);
	$q .= " ,html=".sqlVar($msg->html);
	$q .= " ,assigned=".sqlVar($msg->assigned);
	$q .= " ,public=".sqlVar($msg->public);
	$q .= " ,udate=UNIX_TIMESTAMP()";
	$q .= " WHERE id=".sqlVar($msg->id);
	
	sql($q);

	addHistory(ACTION_EDIT,$msg->id,0,$msg->gparent,$user);
	

	if (($oldMsg->assigned != $msg->assigned))
	{
	
		if ($msg->assigned != $user->id)
			sendAssignMail($msg->assigned,$msg,false);
			
		if ($msg->sender != $user->id)
			sendAssignMail($msg->sender,$msg,false);
	}
	
	return getMessage($msg->id,$user);
}					



function addMessage($msg,$user)
{
	$msg->html = mecab($msg->plain);

	
	if ($msg->root)
	{
		sql("UPDATE messages SET childnum=childnum+1 WHERE id=".$msg->root);
		$rmsg = sqlObject("SELECT * FROM messages WHERE id=".$msg->root);
		$msg->msgid = $rmsg->childnum;
	}
		
	if ($msg->gparent)
	{
		sql("UPDATE messages SET ordering=ordering+1 WHERE id=".$msg->gparent);		
		$lmsg = sqlObject("SELECT ordering FROM messages WHERE gparent=".$msg->gparent." ORDER BY ordering DESC LIMIT 1");
		$msg->ordering = $lmsg->ordering+1;
	}else
	{
		$msg->msgid=0;
		$msg->gparent=0;
		$msg->ordering = $user->ordering+1;
		sql("UPDATE users SET ordering=ordering+1 WHERE id=".$user->id);		
	}
		

	$q = "INSERT INTO messages VALUES(";
	$q .= "0";
	$q .= ",UNIX_TIMESTAMP()";
	$q .= ",UNIX_TIMESTAMP()";
	$q .= ",".$user->id;
	$q .= ",".$msg->tags[0];
	$q .= ",".$msg->tags[1];
	$q .= ",".$msg->tags[2];
	$q .= ",".$msg->tags[3];
	$q .= ",".$msg->tags[4];
	$q .= ",".sqlVar($msg->subject);
	$q .= ",".sqlVar($msg->plain);
	$q .= ",".sqlVar($msg->html);
	$q .= ",0";
	$q .= ",0";
	$q .= ",''";
	$q .= ",0";
	$q .= ",".sqlVar($msg->assigned);
	$q .= ",".sqlVar($msg->state);
	$q .= ",".$msg->ordering;	// ordering
	$q .= ",0";					// project
	$q .= ",".$msg->msgid;		// msgid
	$q .= ",0";					// parent
	$q .= ",".$msg->root;		// root		
	$q .= ",0";					// childnum
	$q .= ",".$msg->gparent;	// gparent
	$q .= ",0";					// toplevel
	$q .= ",0";					// deleted
	$q .= ",".$msg->public;		// public
	$q .= ")";
	
	$msg->id = sqlInsert($q);
	
		
	if ($msg->assigned != $user->id)
	{	
		sendAssignMail($msg->assigned,$msg,true);
	}
	
	//if ($msg->root)
	//	sql("UPDATE members SET latest_notice=".$msg->id." WHERE user=".$user->id." AND message=".sqlVar($msg->root));			
	sql("UPDATE users SET latest_notice=".$msg->id." WHERE id=".$user->id);			
	
	
	addHistory(ACTION_NEW,$msg->id,$msg->state,$msg->gparent,$user);

	if ($msg->gparent)
	{	
		$pmsg = getMessage($msg->gparent);
		if ($pmsg)
			addHistory(ACTION_EDIT,$pmsg->id,0,$pmsg->gparent,$user);
	}
	
	createParentTree($msg);


	
	return getMessage($msg->id,$user);
}					



?>