<?php

require "config.php";


define(DEFAULT_ID,0);
define(NOTICE_ID,1);
define(ANYBODY_ID,2);
define(ADMIN_ID,3);


define(ACTION_NONE,0);
define(ACTION_NEW,1);
define(ACTION_DELETE,2);
define(ACTION_MOVE_BEFORE,3);
define(ACTION_MOVE_AFTER,4);
define(ACTION_MOVE_TOP,5);
define(ACTION_EDIT,6);
define(ACTION_MOVE_INTO,7);








$dbSalt = "salt1234";






 
class array2xml {
   var $output = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
   function array2xml($array, $root = 'root', $element = 'element') {     
      $this->output .= $this->make($array, $root, $element);
   }
   function make($array, $root, $element) {
      $xml = "<{$root}>\n";
      foreach ($array as $key => $value) {
         if (is_array($value)) {
            $xml .= $this->make($value, $element, $key);
         } else {
            if (is_numeric($key)) {
               $xml .= "<{$root}>{$value}</{$root}>\n";
            } else {
               $xml .= "<{$key}>{$value}</{$key}>\n";
            }
         }
      }
      $xml .= "</{$root}>\n";      
      return $xml;
   }
}


function groupInviteHash($iid,$email,$groupid)
{
	global $dbSalt;
	
	return md5($iid.$email.$groupid.$dbSalt);
}


function connect()
{
	global $link;
		
	$link = @mysql_connect (CONFIG_DB_HOSTNAME, CONFIG_DB_USER, CONFIG_DB_PASSWORD);
	
	if (!$link)
		die("Unable to connect to database, please come back later");
	
	
	return $link;
}

function disconnect()
{
	global $link;
	@mysql_close($link);
}

function mysql_escape_mimic($inp) { 
    if(is_array($inp)) 
        return array_map(__METHOD__, $inp); 

    if(!empty($inp) && is_string($inp)) { 
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp); 
    } 

    return $inp; 
} 

function sqlVar($value,$like=false)
{
   if (get_magic_quotes_gpc()) 
       $value = stripslashes($value);
	   
    if (!is_numeric($value)) 
	{
		if ($like)
			$value = "'%" . mysql_escape_mimic($value) . "%'";
		else
			$value = "'" . mysql_escape_mimic($value) . "'";
    }
   
      
   return $value;
}

function sql($query,$db=false)
{
	global $link;
	
	if (!$db)
		$db = CONFIG_DB_NAME;
	
	if (!$link)
		die("Sorry, please come back in a bit");
	
	return @mysql_db_query($db,$query);
}

function sqlObject($query)
{
	$r = sql($query);
	if ($r)
		return @mysql_fetch_object($r);
	return false;
}

function sqlInsert($query)
{
	$r = sql($query);
	if ($r)
		return @mysql_insert_id();
	return 0;
}


function sqlArray($query)
{
	$list = array();
	$r = sql($query);
	if ($r)
		while ($o = @mysql_fetch_object($r))
			$list[] = $o;
	return $list;
}

function findUserByName($name)
{
	return sqlObject("SELECT * FROM users WHERE verified>=1 AND name=".sqlVar($name));
}

function findUserByEmail($email,$verified=1)
{
	return sqlObject("SELECT * FROM users WHERE verified>=$verified AND email=".sqlVar($email));
}

function findUserByKey($key)
{
	return sqlObject("SELECT * FROM users WHERE ukey=".sqlVar($key));
}

function plural($num, $str)
{
	if ($num != 1)
		return $str.'s';
	else
		return $str;
}

function timeDiff($dt)
{
	//global $lang;
	
	$diff = time() - $dt;
	
	$out="";
		
	if ($diff <= (60*60*24))
		$out .= '<span id="timeago-'.$dt.'">';
	
	$secs = round($diff);
	$mins = round($secs/60);
	$hours = round($mins/60);
	$days = round($hours/24);
	$months = round($days/30);
	$years = round($days/365);
	

	if ($secs < 60)
		$out .= "<span class='new'>just now</span>";
	else if ($mins < 60)
		$out .= $mins." ".plural($mins,'min').' ago';
	else if ($hours < 24)
		$out .= $hours." ".plural($hours,'hr').' ago';
	else 
	
	{
		if (date("Y",$dt) == date("Y"))		
			$out .= date("j M",$dt);
		else
			$out .= date("j M",$dt)." ".date("Y",$dt);
	}

	if ($diff <= (60*60*24))
		$out .= '</span>';

	return $out;
	
}

function timeFormat($dt)
{
	global $lang;

	$lt = localtime($dt,true);
	
	$r = "";
	$r .= ($lt['tm_year']+1900);
	$r .= "/".($lt['tm_mon']+1);
	$r .= "/".($lt['tm_mday']+1);

	$wd = mb_substr($lang['dayname'],$lt['tm_wday'],1,"UTF-8");
	
	$r .= " ($wd) ";	 

	$r .= sprintf("%02d",$lt['tm_hour']).":".sprintf("%02d",$lt['tm_min']);
	
	return $r;
	
}

function defraction( $fraction )
{
    list( $nominator, $denominator ) = explode( "/", $fraction );

    if( $denominator )
    {
        return ( $nominator / $denominator );
    }
    else
    {
        return $fraction;
    }
}


function exifGPS($exif)
{
	if ($exif['GPSLatitude'] && $exif['GPSLongitude'])
	{
		// Latitude
		$northing = -1;
		if( $exif['GPSLatitudeRef'] && 'N' == $exif['GPSLatitudeRef'] )
		{
			$northing = 1;
		}
		
		$northing *= defraction( $exif['GPSLatitude'][0] ) + ( defraction($exif['GPSLatitude'][1] ) / 60 ) + ( defraction( $exif['GPSLatitude'][2] ) / 3600 );
		$loc->lat = $northing;
		
		// Longitude
		$easting = -1;
		if( $exif['GPSLongitudeRef'] && 'E' == $exif['GPSLongitudeRef'] )
		{
			$easting = 1;
		}
		
		$easting *= defraction( $exif['GPSLongitude'][0] ) + ( defraction( $exif['GPSLongitude'][1] ) / 60 ) + ( defraction( $exif['GPSLongitude'][2] ) / 3600 );						
		$loc->lng = $easting;

		return $loc;							
	}	
						
	return	false;
	
}



function GPStoCity($lng,$lat)
{
//	return sqlObject("SELECT *,(((lng - $lng)*(lng - $lng)) + ((lat - $lat)*(lat - $lat))) AS dist FROM cities ORDER BY dist ASC LIMIT 1");
		
	$eq = "ACOS(SIN(RADIANS(lat))*SIN(RADIANS($lat))+COS(RADIANS(lat))*COS(RADIANS($lat))*COS(RADIANS($lng-lng)))*6371";

	return sqlObject("SELECT *,$eq AS dist FROM cities ORDER BY dist ASC LIMIT 1");

}
function getTypeImage($type,$local)
{
	if ($type == "pdf")										
		return '<img src="/images/pdf.gif">';
	else if ($type == "archive")										
		return '<img src="/images/icon_zip.png">';
	else if ($type == "audio")										
		return '<img src="/images/icon_sound.png">';
	else if ($type == "html")
		return '<img src="/images/icon_www.png">';
	else if ($type == "spreadsheet")
		return '<img src="/images/icon_spreadsheet.gif">';
	else if ($type == "contact")
		return '<img src="/images/icon_vcf.png">';
	else if ($type == "movie")
		return '<img src="/images/icon_movie.png">';
	else if ($type == "text")
		return '<img src="/images/icon_textfile.png">';
	else if ($type == "maya")
		return '<img src="/images/icon_maya2009.png">';
	else if ($local) 
		return '<img src="/images/icon_down.png">';
	else
		return '<img src="/images/icon_www.png">';
		
}

function getURLs($str)
{
//	$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
	//$reg_exUrl = "/(http)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
	
//	preg_match($reg_exUrl, $str, $urls);


	preg_match_all('!https?://[\S]+!', $str, $matches);
	$urls = $matches[0];
	
	$urls = array_unique($urls);
	
	return $urls;
}

function canAccessMessage($id,$user)
{
	$msg = getMessage($id,$user);
	
	if ($msg)
	{
		if ($user)
			if (getMember($msg,$user))
				return $msg;
	
		$rmsg = getMessage($msg->root,$user);
		if (($rmsg && $rmsg->public) || $msg->public)
			return $msg;
	}		

	return null;	
}



function getMessage($id,$user=false)
{
	$q = "SELECT ";
	$q .= " messages.*";
	$q .= ",users.name AS username";
	$q .= ",messages.id AS gid";
	$q .= ",messages.msgid AS mid";
	$q .= ",assusers.id AS assid";
	$q .= ",assusers.name AS assname";
	$q .= ",assusers.avatar AS assver";
	$q .= ",viausers.id AS viaid";
	$q .= ",viausers.name AS vianame";
	
	if ($user)
	{
		$q .= ",members.user AS memberid";
		$q .= ",followers.user AS followerid";
	}
	
	$q .= ",messages.root AS rid";
	$q .= ",(SELECT plain FROM messages WHERE id=rid) AS rootname";
	$q .= ",messages.gparent AS pid";
	$q .= ",(SELECT plain FROM messages WHERE id=pid) AS parentname";
	
	
	$q .= ",(SELECT COUNT(*) FROM messages WHERE gparent=gid AND state=0) AS numchildren_pending";	
	$q .= ",(SELECT COUNT(*) FROM messages WHERE gparent=gid AND state=1) AS numchildren_active";	
	$q .= ",(SELECT COUNT(*) FROM messages WHERE gparent=gid AND state=2) AS numchildren_done";	
	
	$q .= ",(SELECT COUNT(*) FROM comments WHERE gparent=gid ) AS numcomments";
	$q .= ",(SELECT comments.time FROM comments WHERE gparent=gid ORDER BY comments.time DESC LIMIT 1) AS lastcomment";
	
	$q .= " FROM messages";
	$q .= " LEFT JOIN users ON users.id=messages.sender";
	$q .= " LEFT JOIN users assusers ON assusers.id=messages.assigned";
	$q .= " LEFT JOIN users viausers ON viausers.id=messages.sender";		
	
	if ($user)
	{
		$q .= " LEFT JOIN members ON members.user=$user->id AND (members.message=messages.id OR members.message=messages.root)";	
		$q .= " LEFT JOIN followers ON followers.user=$user->id AND (followers.message=messages.id)";	
	}
		
	$q .= " WHERE users.id>0";
	
	$q .= " AND messages.id=".sqlVar($id);
	
	return sqlObject($q);
}


	
function findMessages($from,$show,$find,$state,$pmsg,$user,$isFlat=false,$numCols=3)
{

	$q = "SELECT ";
	
	if (!$from)
		$q .= " SQL_CALC_FOUND_ROWS "; 
	$q .= " messages.*";
	$q .= ",users.name AS username";
	$q .= ",messages.msgid AS mid";
	$q .= ",assusers.id AS assid";
	$q .= ",assusers.name AS assname";
	$q .= ",assusers.avatar AS assver";
	$q .= ",viausers.id AS viaid";
	$q .= ",viausers.name AS vianame";
//	$q .= ",marked.user AS marked";
	$q .= ",messages.id AS gid";
	if ($user)
	{
		$q .= ",members.user AS memberid";
		$q .= ",followers.user AS followerid";
	}
	
	if (!$pmsg && $isFlat)
	{
		$q .= ",messages.root AS rid";
		$q .= ",(SELECT plain FROM messages WHERE id=rid) AS rootname";
	} else if ($isFlat)
	{
		$q .= ",messages.gparent AS pid";
		$q .= ",(SELECT plain FROM messages WHERE id=pid) AS parentname";
	}
	
	
	
	$q .= ",(SELECT COUNT(*) FROM messages WHERE gparent=gid AND state=0 AND !deleted) AS numchildren_pending";	
	$q .= ",(SELECT COUNT(*) FROM messages WHERE gparent=gid AND state=1 AND !deleted) AS numchildren_active";	
	$q .= ",(SELECT COUNT(*) FROM messages WHERE gparent=gid AND state=2 AND !deleted) AS numchildren_done";	
	
	$q .= ",(SELECT COUNT(*) FROM comments WHERE gparent=gid ) AS numcomments";
	$q .= ",(SELECT comments.time FROM comments WHERE gparent=gid ORDER BY comments.time DESC LIMIT 1) AS lastcomment";
	
	$q .= " FROM children";
	$q .= " LEFT JOIN messages ON messages.id = children.cmessage";
	
	$q .= " LEFT JOIN users ON users.id=messages.sender";
	$q .= " LEFT JOIN users assusers ON assusers.id=messages.assigned";
	$q .= " LEFT JOIN users viausers ON viausers.id=messages.sender";	
//	$q .= " LEFT JOIN marked ON marked.message=messages.msgid  AND marked.user=$user->id";

	if ($user)
	{
		$q .= " LEFT JOIN members ON members.user=$user->id AND (members.message=messages.id OR members.message=messages.root)";	
		$q .= " LEFT JOIN followers ON followers.user=$user->id AND (followers.message=messages.id)";	
	}
	
	$q .= " WHERE ";
	
	$q .= " !messages.deleted";
	
	if ($numCols==3 || ($state&8))		
	{
		if ($state & 8)
			$q .= " AND (1<<messages.state)&".sqlVar($state);
		else	
			$q .= " AND messages.state=".sqlVar($state);
	}
	
		
	if ($find)
	{
		$terms = explode(" ",$find);
		
		foreach($terms as $t)
		{
			$f = explode(":",$t);
			
			$ft = $f[0]; 
			$fq = $f[1];
			
			if ($ft == "user" || $ft == "assigned")
			{
				$q .= " AND assusers.name=".sqlVar($fq);		
			}else if ($ft == "order")
			{
				//if ($fq == "time")
				//	$isFlat = true;
			}else if ($ft == "via")
			{
				$q .= " AND viausers.name=".sqlVar($fq);		
				
			}else if (is_numeric($ft))
			{
				$q .= " AND (";
				$q .= " messages.msgid=".sqlVar($ft);
			 	$q .= " OR MATCH(subject,plain,html) AGAINST (".sqlVar($ft).")";
				$q .= " )";
			}else if ($ft == "today")
			{
				$q .= " AND messages.idate>(UNIX_TIMESTAMP()-(60*60*24))";
			}else if ($ft == "yesterday")
			{
				$q .= " AND messages.idate>(UNIX_TIMESTAMP()-(60*60*24*2))";
				$q .= " AND messages.idate<(UNIX_TIMESTAMP()-(60*60*24*1))";
			}else if ($ft == "marked")
			{
				if ($fq)
					$q .= " AND marked.user";
					
			}else if ($ft == "comments")
			{
				$q .= " AND (SELECT COUNT(*) FROM comments WHERE gparent=messages.id)>=".sqlVar($fq);
				//$q .= " AND numcomments >=".$fq;

			}else if ($ft == "debug")
			{
				$isDebug=true;
				
			}else if ($ft!="")
			{
				//$isFlat = true;
				
				$q .= " AND (";
				$q .= "  assusers.name=".sqlVar($ft);		
				$q .= " OR viausers.name=".sqlVar($ft);		
			 	$q .= " OR MATCH(subject,plain,html) AGAINST (".sqlVar($ft).")";
	
				$q .= ")";
			
			}
		
		}
	}
	

	
	
	
	
	
	if ($pmsg)
	{
		if ($isFlat)
		{
			$q .= " AND children.pmessage=$pmsg->id";				
		}else{
			$q .= " AND messages.gparent=$pmsg->id";		
		}		
		
	}else
	{

		if ($isFlat)
		{
			$q .= " AND ";
			$q .= "(";
			$q .= " children.cmessage=members.message OR children.pmessage=members.message OR followers.user";			
			$q .= ")";
		}else
		{
			$q .= " AND children.pmessage=0";					
			if ($user)
			{
			$q .= " AND ";
			$q .= " (";
				$q .=   " (members.user AND members.message=children.cmessage AND messages.root=0)";
				$q .=   " OR ";
				$q .=   " (followers.user)";
			$q .= " )";
			}
		}

	}	
	

		
		

		
	if (!$show)
		$show = 10;
	if ($show > 100)
		$show = 100;
		
	$q .= " GROUP BY messages.id";
	
	if ($isFlat)
		$q .= " ORDER BY messages.id DESC";
	else
		$q .= " ORDER BY messages.ordering DESC";
			
	$q .= " LIMIT ".sqlVar($show);
	
	if ($from)
		$q .= " OFFSET ".sqlVar($from);	
		
		
	$messages = sqlArray($q);
	
	

	$out = "";
	
	
		
	$ostate = $numCols==1 ? 3 : $state;
	
	
	//if (count($messages))
	{	
	
		$out .= '<div id="state'.$ostate.'">';	
		
		if ($isDebug)
		{	
			$out .= '<div id="debug">'.$q.'</div>';
		}
		
		foreach ($messages as $m)
		{	
			$out .= outputMessage($m,$user,null,true,$pmsg->id,$isFlat,$numCols);
		}
		
		
		$out .= '</div>';
		
		if ($from==0)
		{
			$r = sqlObject("SELECT FOUND_ROWS() AS total");
			
			$out .= '<div id="total'.$ostate.'" style="display:none">'.$r->total.'</div>';
		}
	}

	
	
	
	return $out;	
}




function outputMessage($m,$u,$member,$wrapdiv,$parent,$isFlat=0,$numCols=3)
{
		
/*		
	$canEdit = ($m->viaid == $u->id) || ($m->assid == $u->id) || ($m->assid==ANYBODY_ID);
	if (!$canEdit)
		$attr = 'nomove="1"';
*/
	

		
	if ($wrapdiv)		
	{
		$out .= '<div id="msg'.$m->id.'" class="message" '.$attr.' >';			
//		$out .= '<div id="msg'.$m->id.'" class="message" '.$attr.' >';			
	}
	


	
	$baseURL = "/?";
	if ($isFlat)
		$baseURL .= "f=1&";
		
	if ($numCols!=3)
		$baseURL .= "c=1&";
		
	$baseURLnoP = $baseURL;	
		
	if ($parent)
		$baseURL .= "p=".$parent."&";




	$out .= '<div id="col2">';
	
	if ($numCols==1)
	{
		$out .= '<div class="pad">';
		$out .= '<div onclick="setMessageState('.$m->id.',0);" class="pending '.($m->state==0?'on':'').'"></div>';
		$out .= '<div onclick="setMessageState('.$m->id.',1);" class="active '.($m->state==1?'on':'').'"></div>';
		$out .= '<div onclick="setMessageState('.$m->id.',2);" class="done '.($m->state==2?'on':'').'"></div>';		
		$out .= '</div>';
	}
	
	
	
	$out .= '<a href="'.$baseURL.'q=user:'.$m->assname.'">';
	$out .= '<div class="avatar"><img src="php/image.php?avatar='.$m->assid.'&v='.$m->assver.'"></div>';
	$out .= '<div id="imgname">'.$m->assname.'</div>';
	$out .= '</a>';
		
	if ($m->root)
		$out .= '<div class="id">'.$m->msgid.'</div>';
		
	$out .= '</div>';





	$numchildren = $m->numchildren_pending + $m->numchildren_active + $m->numchildren_done;

	$out .= '<div id="col3">';
	
	$out .= '<div id="topinfo">';

	$out .= '<div id="num">';
	
	
	$out .= timeDiff($m->idate);

	if (!$parent && $isFlat)
		$out .= ' in <a href="'.$baseURL.'p='.$m->root.'">'.messageTitle($m->rootname).'</a>';
	else if ($isFlat && $m->gparent!=$parent)
		$out .= ' in <a href="'.$baseURL.'p='.$m->gparent.'">'.messageTitle($m->parentname).'</a>';
	
	if ($m->viaid != $m->assid)
		$out .= ' via <a href="'.$baseURL.'q=via:'.$m->vianame.'">'.$m->vianame.'</a>';		

	if ($m->public)
		$out .= ' <i class="icon-eye-open icon-large"></i> ';		


	
	$out .= '</div>';		// num
 		


	$out .= '<div id="overlay">';


	$out .= '<div id="icons">';


	if ($numchildren)
	{
		$out .= '<div style="float:right;margin-top:5px; margin-left:4px;">';
		$out .= writeBars($m->numchildren_pending,$m->numchildren_active,$m->numchildren_done,$baseURLnoP."p=$m->gid",true);		
		$out .= '</div>';	
	}
	
	
	
	
	if (!$m->root && $m->sender!=$u->id)
		$out .= '<a class="vis" href="javascript:askQuit('.$m->gid.');"><i class="icon-trash icon-small"></i> Remove</a>';
	else if ($m->sender == $u->id || $m->assigned == $u->id)
		$out .= '<a class="vis" href="javascript:askDelete('.$m->gid.');"><i class="icon-trash icon-small"></i> Delete</a>';
	
	
	if ($m->sender != $u->id && $m->assigned != $u->id)
		$out .= '<a class="vis" href="javascript:editMessage('.$m->gid.');"><i class="icon-pencil icon-small"></i> View</a>';
	else
		$out .= '<a class="vis" href="javascript:editMessage('.$m->gid.');"><i class="icon-pencil icon-small"></i> Edit</a>';
	
	
	if (!$numchildren)
	{
		$out .= '<a class="vis" href="'.$baseURLnoP.'p='.$m->gid.'"><i class="icon-share icon-small"></i> Open</a>';
	}


	$out .= '</div>';	// icons
	
	$out .= '<div class="vis bgfade"> </div>';

	$out .= '</div>';	// overlay
	
		
	
  
		
	$out .= '</div>';		// topinfo


	$out .= '<div id="body">';
	$out .= nl2br(makeClickableLinks(htmlspecialchars($m->plain)));
	$out .= '</div>';			
	

	
	
		
	$out .= '</div>';
	
	
	$out .= '<div id="comments">';
	$out .= '<div id="thread"></div><div id="box"></div>';
	$out .= '</div>';

	if ($m->numcomments)
	{
	
		$age = time() - $m->lastcomment;

		$out .= '<div id="botinfo" style="opacity:1.0">';
		$out .= '<span></span><a href="javascript:showComments('.$m->id.','.($m->memberid?1:0).');"><i class="icon-comment"></i> '.$m->numcomments.' comment'.(($m->numcomments>1)?'s':'');
		if ($age <= (60*60*24))
			$out .= ' '.timeDiff($m->lastcomment);
		$out .= ' ▼</a>';
		$out .= '</div>';
	}else
	{
		$out .= '<div id="botinfo">';
		if ($u && $m->memberid)
			$out .= '<a href="javascript:showAddComment('.$m->id.','.($m->memberid?1:0).');"><i class="icon-comment"></i> no comments</a>';
		$out .= '</div>';
	}
	
	if ($wrapdiv)
		$out .= "</div>";
	
	return $out;

}





function getUserName($id)
{
	connect();
	$user = sqlObject("SELECT * FROM users WHERE id=".sqlVar($id));
	if ($user)
		$name = $user->name;
	disconnect();
	return $name;
}


function messageTitle($str,$maxlen=15)
{
	$line = explode("\n",$str);
	$text = $line[0];

	if ($text=="")
		$text = $str;	

	$out = "";
	
	$cut = mb_strcut($text,0,$maxlen,"UTF-8");
	
	$out .= htmlspecialchars($cut);
	

	if ($cut!=$text)
		$out .= "…";
	
		
	return $out;
}



function writeBars($nump,$numa,$numd,$url,$info)
{	
	$height = 20;

	$norm = max($nump,$numa,min($height,$numd));
	if ($norm==0)
		$norm = 1;
			
	$hp = min($height,round(($nump*$height)/$norm));
	$ha = min($height,round(($numa*$height)/$norm));
	$hd = min($height,round(($numd*$height)/$norm));
		





	$out = "";
	if ($url)
		$out .= '	<div class="barc" onclick="location.href=\''.$url.'\'">';
	else
		$out .= '	<div class="barc">';
	$out .= 		'<div style="height:'.$hp.'px;" class="bar0"></div>';
	$out .= 		'<div style="height:'.$ha.'px;" class="bar1"></div>';
	$out .= 		'<div style="height:'.$hd.'px;" class="bar2"></div>';
	if ($info)
	{
		$out .= 		'<div class="info left">';
		$out .=				'<div class="pending"><b>'.$nump.'</b> pending</div> ';
		$out .=				'<div class="active"><b>'.$numa.'</b> active</div> ';
		$out .=				'<div class="done"><b>'.$numd.'</b> done</div> ';
		$out .= 		'</div>'; 
	}
	$out .= '	</div>';
	
	return $out;
}

function getMember($msg,$user)
{
	if ($msg->root)
		$member = sqlObject("SELECT * FROM members WHERE user=$user->id AND message=$msg->root");
	else
		$member = sqlObject("SELECT * FROM members WHERE user=$user->id AND message=$msg->id");
		
	return $member;
			
}


function checkAccess($id,$user)
{
	$pmsg = getMessage($id);
	
	if (!getMember($id,$user))
		die();
			
}


function writeTopMenu($user,$login=false,$tag=false,$search=false,$location=false,$source=false,$assigned=false,$via=false,$marked=false,$find=false,$query="",$parent=0,$isFlat=false,$pmsg=false,$numCols=3)
{

	$out = '';
	$out .= '<div id="topmenu">';
	
	
	$topmsg = $pmsg;

	$debug = $user->id==1 || $user->id==3 || $user->id==4;
	
	
	$baseURL = '/?';
	if ($numCols==1)
		$baseURL .= 'c=1&';
	
	$homeURL = $baseURL;	
		
	if ($isFlat)
		$baseURL .= 'f=1&';
	
	



	$out .= '<div class="finder" id="finder">';
		

	
	$out .= '<a href="'.$homeURL.'" id="parent0" style="padding:0 3px;margin-left:-25px;">';			
	$out .= '<i class="icon-home icon-large"></i>';
	$out .= '</a>';
	
	$p = "";
	
	$cnt=0;					
	while ($topmsg && $cnt<3)
	{
/*	
		if ($cnt==0)
			$t = '<li class="active">';
		else
		{
			$t = '<li>';
			$t .= '<i class="icon-play icon-small" style="color:#ddd; margin-top: 10px; font-size:8px;"></i>';
		}
		
*/			
		$t = '<i class="icon-play icon-small" style="color:#ddd; margin-top: 10px; font-size:8px;"></i>';
		
		$t .= '<a class="'.($cnt==0?'crumb active':'crumb').'" href="'.$baseURL.'p='.$topmsg->id.'" id="parent'.$topmsg->id.'">';			
	
	
		$mc = "";
		$mc .= '<div class="avatar"><img src="php/image.php?avatar='.$topmsg->assid.'&v='.$topmsg->assver.'"></div>';
		$mc .= '<p>';
		$mc .= (nl2br(htmlspecialchars($topmsg->plain)));
		$mv .= '</p>';
		
		
//		$mc = outputMessage($topmsg,$user,$member,true,$parent);

		$t .= '<div class="info state'.$topmsg->state.'">'.$mc.'</div>';			
					
//		if ($topmsg->assid)
//			$t .= '<img class="savatar" src="php/image.php?avatar='.$topmsg->assid.'&v='.$topmsg->assver.'">';
				
		$t .= messageTitle($topmsg->plain,30);
		//$t .= '<img class="arrow" src="/images/play.png">';
		$t .= '</a>';

		$p = $t.$p;
		
		if ($topmsg->last)
			break;
		
		
		$cnt++;		
		
		$topmsg = getMessage($topmsg->gparent);					
				
		
								
	};
	
	$out .= $p;
	
			
	if ($search && $numCols==3)
	{
		$out .= '<div id="bars" style="margin-top:1px; margin-right:10px; margin-left:5px; float:left; visibility:hidden;">';
			$out .= writeBars(0,0,0,null,false);	
		$out .= '</div>';
	}
								
		
	$out .= '</div>';		
	




	$out .= '<div id="options">';
	


	if ($user)
	{	
		$baseURL = '/?f=1&';
	
		if ($user->latest_notice)
		{
			$q = "SELECT COUNT(*) AS total FROM children";
			$q .= " LEFT JOIN members ON members.user=$user->id";	
			$q .= " LEFT JOIN messages ON messages.id=children.cmessage";	
			$q .= " WHERE ";
			$q .= " messages.assigned=".NOTICE_ID;
			$q .= " AND messages.id>$user->latest_notice";
			$q .= " AND (children.cmessage=members.message OR children.pmessage=members.message)";
			
			//if ($user->id==1)
			//	die($q);
			
			$r = sqlObject($q);
			if ($r->total)
			{
				$out .= '<div id="notice" class="notice">';
				$out .= '<a href="'.$baseURL.'hf='.$user->latest_notice.'&q=user:notice">';
				$out .= '<img src="/images/exclamation.png">';
				$out .= '<div class="num">'.$r->total.'</div>';
				$out .= '</a>';
				$out .= '</div>';
			}
			
		}else{
		
			sql("UPDATE users SET latest_notice=(SELECT id FROM messages ORDER BY id DESC LIMIT 1) WHERE id=$user->id");
		
		}		
	
	}


	if ($search)
	{
		$out .= '<ul class="switcher">';
		$out .= '<li '.(!$isFlat?'class="active"':'').'><a href="#" onclick="setTreeSwitch(this.parentNode.parentNode,0);return false;" >Tree</a></li>';
		$out .= '<li '.($isFlat?'class="active"':'').'><a href="#" onclick="setTreeSwitch(this.parentNode.parentNode,1);return false;" >All</a></li>';
		$out .= '</ul>';
		
		//if ($user->id==1)
		{
			$out .= '<ul class="switcher">';
			$out .= '<li '.($numCols==3?'class="active"':'').'><a href="#" onclick="setColumnSwitch(this.parentNode.parentNode,3);return false;">•••</a></li>';
			$out .= '<li '.($numCols==1?'class="active"':'').'><a href="#" onclick="setColumnSwitch(this.parentNode.parentNode,1);return false;">•</a></li>';
			$out .= '</ul>';
		}
		
		
//		•●	
	
	}
    	
    
	
	if ($search)
	{
			
	
    	$out .= '<FORM id="search" name="search" method="" action="">';    
        $out .= '<input state="'.($query?'1':'0').'" type="text" id="text" name="text"  value="'.$query.'" onkeyup="searchChange()" onkeypress="return searchKeypress(event);" placeholder="" style="margin-right:5px" />';
    	$out .= '<i id="searchIcon" onclick="document.search.text.focus();" class="icon-search"></i>';       
    	$out .= '<i id="clearIcon" onclick="clearSearch(); clearResults(); doSearch();" class="icon-remove-sign"></i>';       
        $out .= '</FORM>';
	}            
                
                
    if ($user)
    {         
       	

		$baseURL = '/?';
		if ($isFlat)
			$baseURL .= 'f=1&';
		if ($parent)
			$baseURL .= 'p='.$parent.'&';

		$out .= '<div class="usermenu">';
		$out .= '<a href="/settings.php">';	
		
		$out .= '<div class="topicon"><img src="/php/image.php?avatar='.$user->id.'&v='.$user->avatar.'"></div>';	
			
		$out .= '</a>';

		$out .= '<ul>';				
			$out .= '<li><p>'.$user->name.' ▲</p></li>';
			$out .= '<hr>';
			$out .= '<li><a href="'.$baseURL.'q=user:'.$user->name.'"><i class="icon-user icon-large"></i> Assigned To Me</a></li>';
			$out .= '<li><a href="/"><i class="icon-home icon-large"></i> Home</a></li>';
			
			$out .= '<hr>';
			//$out .= '<li><a href="'.$baseURL.'q=user:Notice"><img class="icon" src="/images/exclamation-w.png">Notices</a></li>';
			
			
			$out .= '<li><a href="/settings.php"><i class="icon-cog icon-large"></i> Settings</a></li>';
	
			$out .= '<li><a href="/php/logout.php"><i class="icon-off icon-large"></i> Logout</a></li>';					
			
			if (CONFIG_FEEDBACK_SCRIPT)
			{
				$out .= '<hr>';
				$out .= '<li><a href="javascript:UserVoice.showPopupWidget();"><i class="icon-comment icon-large"></i> Feedback</a></li>';
				$out .= '<li><a href="http://www.surveymonkey.com/s/976GPJ5" target="_blank"><i class="icon-check icon-large"></i> Take Survey</a></li>';
			}	
			
			
			
			
			if ($user->id==ADMIN_ID)
			{
				$out .= '<hr>';
				$out .= '<li><a href="/php/stats.php"><i class="icon-user icon-large"></i> Stats</a></li>';								
			}
			 
			
		$out .= '</ul>';
		$out .= '</div>';
		
	}else
	{
		$out .= '<span style="padding:0 5px"></span>';
	}

				
	
	$out .= '</div>';
	
	

				
					


	$out .= '</div>';


	
	echo $out;

}

function rstrpos($haystack,$needle,$offset=NULL)
{
    return strlen($haystack)
           - strpos( strrev($haystack) , strrev($needle) , $offset)
           - strlen($needle);
}


function safeFileName($filename) 
{
	$filename = strtolower($filename);
	$filename = str_replace("#","_",$filename);
	$filename = str_replace(" ","_",$filename);
	$filename = str_replace("'","",$filename);
	$filename = str_replace('"',"",$filename);
	$filename = str_replace("__","_",$filename);
	$filename = str_replace("&","and",$filename);
	$filename = str_replace("/","_",$filename);
	$filename = str_replace("\\","_",$filename);
	$filename = str_replace("?","",$filename);
	return $filename;
}

function getLoggedInUser()
{
	$user = sqlObject("SELECT * FROM users WHERE id=".sqlVar($_SESSION['userid']));
	
	return $user;
}

function getTwitterUser($id)
{
	$user = sqlObject("SELECT * FROM users WHERE twitter_id=".sqlVar($id));
	
	return $user;
}


function createUser($email="",$pwdkey="",$name="",$fullname="")
{

	$q = "INSERT INTO users VALUES(";
	$q .= "0";
	$q .= ",".sqlVar($name);
	$q .= ",".sqlVar($email);
	$q .= ",0";
	$q .= ",UNIX_TIMESTAMP()";
	$q .= ",UNIX_TIMESTAMP()";
	$q .= ",".sqlVar($pwdkey);
	$q .= ",''";
	$q .= ",''";
	$q .= ",''";
	$q .= ",0";
	$q .= ",''";
	$q .= ",0";
	$q .= ",''";
	$q .= ",0";
	$q .= ",0";
	$q .= ",1";
	$q .= ",0";
	$q .= ",".sqlVar($fullname);		// fullname
	$q .= ",0";			// root
	$q .= ",0";			// ordering
	$q .= ",0";			// latest_notice
		
	
	$q .= ")";
	
	$id = sqlInsert($q);
	
	if (CONFIG_EXAMPLE_NOTE)
		sql("INSERT INTO followers VALUES($id,".CONFIG_EXAMPLE_NOTE.")");

	return $id;
}

function verifyAddEmail($addemail,$addnum,$id)
{
	global $dbSalt;
		
	$key = md5($dbSalt.$id.$addemail);

	
	/*
	$msg  = "Please click the following link to verify this email address:<br><br>";

	$url = "http://2notes.com/addemail.php?add=$addemail&num=$addnum&id=$id&key=$key";
	$msg .= '<a href="'.$url.'">'.$url.'</a><br><br>';

	mail($addemail,"2notes.com",$msg,"From:no-reply@2notes.com\r\nContent-type: text/html\r\n");
	*/

	$msg  = "Hello,\n\n";

	$msg .= "Please click the following link to verify this email address:\n\n";

	$msg .= "http://2notes.com/addemail.php?add=$addemail&num=$addnum&id=$id&key=$key";

	mail($addemail,"2notes.com",$msg,"From:no-reply@2notes.com\r\nContent-type: text/plain\r\n");

}

function removeLoginCookies()
{
	$token = $_COOKIE['token'];
	$userid = $_COOKIE['userid'];

	connect();
//	$session = sqlObject("UPDATE sessions SET token='' WHERE userid=".sqlVar($userid)." AND token=".sqlVar($token));
	$session = sqlObject("UPDATE sessions SET token='' WHERE userid=".sqlVar($userid));
	disconnect();		
	
	
	setcookie("userid","",0,"/");
	setcookie("token","",0,"/");
}

function checkLoginCookies()
{

	$token = $_COOKIE['token'];
	$userid = $_COOKIE['userid'];

	connect();
	$session = sqlObject("SELECT * FROM sessions WHERE userid=".sqlVar($userid)." AND token=".sqlVar($token));
	
	if ($session)
	{	
		$_SESSION['userid'] = $userid;
		
		$user = getLoggedInUser();
		
		$_SESSION['username'] = $user->name;
	}
	disconnect();
	
	return $user;
	
	
}

function setLoginCookies($userid)
{
	global $dbSalt;
		

	$time = time() + (60*60*24*365);
	
	$token = md5($dbSalt.$time.$userid);

	setcookie("userid",$userid,$time,"/");
	setcookie("token",$token,$time,"/");

	
	connect();
	sqlInsert("INSERT INTO sessions VALUES(0,UNIX_TIMESTAMP(),".sqlVar($token).",".$userid.",".sqlVar($_SERVER['REMOTE_ADDR']).")");
	disconnect();
}



function getFileType($path)
{
	$path = strtolower($path);
	
    $ext = substr($path, rstrpos($path, ".")+1);
	
	if ($ext == 'bmp' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'tga' || $ext == 'png') 
		return "image";
		
	if ($ext == 'pdf')
		return "pdf";
		
	if ($ext == 'doc')
		return "doc";

	if ($ext == 'ppt')
		return "slideshow";

	if ($ext == 'xls')
		return "spreadsheet";

	if ($ext == 'zip' || $ext=="tgz" || $ext=="tar" || $ext=="gz")
		return "archive";

	if ($ext == 'exe' || $ext=="sh" )
		return "executable";
		
	if ($ext == 'txt' || $ext=="text" )
		return "text";
		
	if ($ext == 'html' || $ext=="htm" )
		return "html";
		
	if ($ext == 'mp3' || $ext=="mp4" || $ext=="wav")
		return "audio";

	if ($ext == 'mov' || $ext=="avi" || $ext=="mpg" || $ext=="mpeg")
		return "movie";
		
	if ($ext == 'vcf')
		return "contact";


	return "unknown";		
}		
		
		
function commentHTML($id,$name,$text,$candel,$time=0)
{


	$out .= '<div id="c'.$id.'" class="comment">';
	$out .= '<div class="info">';
	
	if ($candel)	
		$out .= '<span class="del"><a style="padding:5px;" href="javascript:deleteComment('.$id.');"><i class="icon-remove"></i></a></span> ';
	
	$out .= $name;
	if ($time)
		$out .= ', '.timeDiff($time);
	$out .= ' wrote:';
	$out .= '</div>';
	
	
	$html = htmlspecialchars($text);

	$html = $text;
	
	
	$urls = getURLs($html);
	
	
	
	foreach($urls as $u)
	{
		$thumb = CONFIG_DATADIR."/thumbs/".md5($u)."-100.jpg";
		
		if (file_exists($thumb))
		{
			$img = '<a href="'.$u.'"><img src="/php/image.php?thumb='.md5($u).'"></a>';
			$html = str_replace($u,$img,$html);
		}else
		{
			$link = '<a href="'.$u.'">'.$u.'</a>';
			$html = str_replace($u,$link,$html);
		}
		
	}

	//$html .= " #".count($urls);
	
	//foreach($urls as $u)
	//	$html .= "$u ";


	$html = nl2br($html);
	
	
	
	
	$out .= '<div id="text">'.$html;	
//	$out .= '<div id="text">'.nl2br(makeClickableLinks(htmlspecialchars($text)));
	$out .= '</div>';		
/*
	if ($candel)	
		$out .= '<div class="botinfo">[<a href="javascript:deleteComment('.$id.');"> x </a>]</div>';
*/
	$out .= '</div>';


	return $out;
}		
		
	
function isAscii($str) {
    return mb_check_encoding($str, 'ASCII');
}
function mecab($input)
{	
	if (isAscii($input))
		return $input;
		
	$order   = array("\r\n", "\n", "\r");
	$replace = ' ';

	$input = str_replace($order, $replace, $input);


	$cmd = "echo ".$input." | /opt/local/bin/mecab -O wakati";
	$r = exec($cmd,$lines);
	
	$s = "";
	foreach($lines as $l)
		$s .= $l;
	
	return $s;
}

function mb_wordwrap($str, $width, $break)
{
    $return = '';
    $br_width = mb_strlen($break, 'UTF-8');
    for($i = 0, $count = 0; $i < mb_strlen($str, 'UTF-8'); $i++, $count++)
    {
        if (mb_substr($str, $i, $br_width, 'UTF-8') == $break)
        {
            $count = 0;
            $return .= mb_substr($str, $i, $br_width, 'UTF-8');
            $i += $br_width - 1;
        }
        
        if ($count > $width)
        {
            $return .= $break;
            $count = 0;
        }
        
        $return .= mb_substr($str, $i, 1, 'UTF-8');
    }
    
    return $return;
}

function utf8_wordwrap($str, $width, $break, $cut = false) {
    if (!$cut) {
        $regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.$width.',}\b#U';
    } else {
        $regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.$width.'}#';
    }
    if (function_exists('mb_strlen')) {
        $str_len = mb_strlen($str,'UTF-8');
    } else {
        $str_len = preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $var_empty);
    }
    $while_what = ceil($str_len / $width);
    $i = 1;
    $return = '';
    while ($i < $while_what) {
        preg_match($regexp, $str,$matches);
        $string = $matches[0];
        $return .= $string.$break;
        $str = substr($str, strlen($string));
        $i++;
    }
    return $return.$str;
}


function htmlwrap($str, $width = 60, $break = "\n", $nobreak = "") { 

  // Split HTML content into an array delimited by < and > 
  // The flags save the delimeters and remove empty variables 
  $content = preg_split("/([<>])/", $str, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY); 

  // Transform protected element lists into arrays 
  $nobreak = explode(" ", strtolower($nobreak)); 

  // Variable setup 
  $intag = false; 
  $innbk = array(); 
  $drain = ""; 

  // List of characters it is "safe" to insert line-breaks at 
  // It is not necessary to add < and > as they are automatically implied 
  $lbrks = "/?!%)-}]\\\"':;&"; 

  // Is $str a UTF8 string? 
  $utf8 = (preg_match("/^([\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*$/", $str)) ? "u" : ""; 

  while (list(, $value) = each($content)) { 
    switch ($value) { 

      // If a < is encountered, set the "in-tag" flag 
      case "<": $intag = true; break; 

      // If a > is encountered, remove the flag 
      case ">": $intag = false; break; 

      default: 

        // If we are currently within a tag... 
        if ($intag) { 

          // Create a lowercase copy of this tag's contents 
          $lvalue = strtolower($value); 

          // If the first character is not a / then this is an opening tag 
          if ($lvalue{0} != "/") { 

            // Collect the tag name    
            preg_match("/^(\w*?)(\s|$)/", $lvalue, $t); 

            // If this is a protected element, activate the associated protection flag 
            if (in_array($t[1], $nobreak)) array_unshift($innbk, $t[1]); 

          // Otherwise this is a closing tag 
          } else { 

            // If this is a closing tag for a protected element, unset the flag 
            if (in_array(substr($lvalue, 1), $nobreak)) { 
              reset($innbk); 
              while (list($key, $tag) = each($innbk)) { 
                if (substr($lvalue, 1) == $tag) { 
                  unset($innbk[$key]); 
                  break; 
                } 
              } 
              $innbk = array_values($innbk); 
            } 
          } 

        // Else if we're outside any tags... 
        } else if ($value) { 

          // If unprotected... 
          if (!count($innbk)) { 

            // Use the ACK (006) ASCII symbol to replace all HTML entities temporarily 
            $value = str_replace("\x06", "", $value); 
            preg_match_all("/&([a-z\d]{2,7}|#\d{2,5});/i", $value, $ents); 
            $value = preg_replace("/&([a-z\d]{2,7}|#\d{2,5});/i", "\x06", $value); 

            // Enter the line-break loop 
            do { 
              $store = $value; 

              // Find the first stretch of characters over the $width limit 
              if (preg_match("/^(.*?\s)?([^\s]{".$width."})(?!(".preg_quote($break, "/")."|\s))(.*)$/s{$utf8}", $value, $match)) { 

                if (strlen($match[2])) { 
                  // Determine the last "safe line-break" character within this match 
                  for ($x = 0, $ledge = 0; $x < strlen($lbrks); $x++) $ledge = max($ledge, strrpos($match[2], $lbrks{$x})); 
                  if (!$ledge) $ledge = strlen($match[2]) - 1; 

                  // Insert the modified string 
                  $value = $match[1].substr($match[2], 0, $ledge + 1).$break.substr($match[2], $ledge + 1).$match[4]; 
                } 
              } 

            // Loop while overlimit strings are still being found 
            } while ($store != $value); 

            // Put captured HTML entities back into the string 
            foreach ($ents[0] as $ent) $value = preg_replace("/\x06/", $ent, $value, 1); 
          } 
        } 
    } 

    // Send the modified segment down the drain 
    $drain .= $value; 
  } 

  // Return contents of the drain 
  return $drain; 
} 

function makeClickableLinks($text,$wrap=true,$emails=true) { 

//  $text = preg_replace('/([\S]{26})(?![^a-zA-Z])/', '$1 ', $text); 	 	 
//  $text = preg_replace('/([a-zA-Z]{26})(?![^a-zA-Z])/', '$1 ', $text); 	 	 
//  $text = preg_replace('/([a-zA-Z0-9:=_\]\[,]{26})(?![^a-zA-Z0-9:=_\]\[,])/', '$1 ', $text); 	 	 
//  $text = preg_replace('/([-a-zA-Z0-9@:%_\+.~#?&//=]{26})(?![^a-zA-Z])/', '$1 ', $text); 	 	 


//  $text = preg_replace('/([a-zA-Z0-9:=_\]\[,\/]{26})(?![^a-zA-Z0-9:=_\]\[,\/])/', '$1 ', $text); 	 	 

//	$text = htmlwrap($text, 26, "\n", true);


  
//  $text = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:;%_\+.~#?&//=]+)', 



//  $text = eregi_replace('(((ftp|http|https)://)[\S]+)', 


//  $text = mb_eregi_replace('(((ftp|http|https)://)[-a-zA-Z0-9@:;%_\+.~#?&//=]+)'
//, 
//  $text = mb_eregi_replace('(((ftp|http|https)://)[-a-zA-Z0-9@:;%_\+.~#?&//=]+)', 
//    '<a href="\\1">\\1</a>', $text); 

  //$text = preg_replace('@((ftp|https|http)?://([-\w\.]+)+(:\d+)?(/([;\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $text);  

//  $text = mb_eregi_replace('((ftp|https|http)?://([-\w\.]+)+(:\d+)?(/([;\w/_\.]*(\?\S+)?)?)?)', '<a href="\\1">\\1</a>', $text);  
//  $text = mb_eregi_replace('((ftp|https|http)?://([-\w\.]+)+(:\d+)?(/([;\w/_\.]*(\?\S+)?)?)?)', '<a href="\\1">\\1</a>', $text);  
    
  $text = mb_eregi_replace('((ftp|https|http)?://([-\w\.]+)+(:\d+)?(/([;\w/_\.]*(\S+)?)?)?)', '<a href="\\1">\\1</a>', $text);  
    
  $text = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:;%_\+.~#?&//=]+)', 
    '\\1<a href="http://\\2">\\2</a>', $text); 
    
  if ($emails)
	  $text = eregi_replace('([\+_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})', 
    	'<a href="mailto:\\1">\\1</a>', $text); 



 // $text = preg_replace('@((ftp|https|http)?://([-\w\.]+)+(:\d+)?(/([;\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $text);  


	if ($wrap)
		$text = htmlwrap($text,26," ");
   

/*
  $text = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)', 
    '<a href="\\1">[URL]</a>', $text); 
  $text = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', 
    '\\1<a href="http://\\2">[URL]</a>', $text); 
  $text = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})', 
    '<a href="mailto:\\1">[MAIL]</a>', $text); 
*/

   
//  $text = preg_replace('/([a-zA-Z:=_]{20})/', '$1 ', $text); 	 	    
//  $text = preg_replace('/(^\s{16})(^\s)/', '$1 ', $text); 	 	 



  return $text; 

} 



function stateName($state)
{
	switch($state)
	{
		case 0: return "pending";
		case 1: return "active";
		case 2: return "done";
	}
	return "unknown";
}

function touchMessage($id)
{
	sql("UPDATE messages SET udate=UNIX_TIMESTAMP() WHERE id=".$msg->id);
}

function touchUser($user)
{
//	sql("UPDATE members SET last_search=UNIX_TIMESTAMP() WHERE user=".sqlVar($user->id)." AND project=".$user->project);
}


function addHistory($action, $id, $value, $parent, $user, $uid=0)
{
	sql("INSERT INTO history VALUES(0,".$action.",".sqlVar($id).",".sqlVar($value).",".sqlVar($parent).",".sqlVar($user->id).",".sqlVar($uid).")");
}


function sendEmail($to,$subject,$body)
{
	mail($to,$subject,$body,"From:".CONFIG_NOREPLY_EMAIL."\nMIME-Version: 1.0\nContent-type: text/html; Charset=UTF-8\n");		
}

function deleteChildrenTree($msg)
{
	sql("DELETE FROM children WHERE cmessage=".sqlVar($msg->id));		
}
function createParentTree($msg)
{
	$cnt=0;
	$pmsg = $msg;
	do
	{
		$p = $pmsg->gparent;
		sql("INSERT INTO children VALUES($p,$msg->id)");
		$pmsg = sqlObject('SELECT * FROM messages WHERE id='.$p);
			
		$cnt++;
		if ($cnt>100)
			break;			
	}while($p);
}
function relocateChildrenTree($msg)
{
	$children = sqlArray("SELECT * FROM children WHERE pmessage=$msg->id");

	foreach($children AS $c)
	{
		deleteChildrenTree($c->cmessage);
	}
	
	createParentTree($msg);
	foreach($children AS $c)
	{
		$msg = getMessage($c->cmessage);
		createParentTree($msg);
	}
}




/**
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.
*/
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if
(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
      
   }
   return $isValid;
}


function makeInviteKey($msgid,$email,$inviteID)
{
	global $dbSalt;
	return	md5($dbSalt.$msgid.$email.$inviteID);
}

function login($user)
{
	$_SESSION['userid'] = $user->id;
	$_SESSION['username'] = $user->name;
	
	setLoginCookies($user->id);		
}

function logout()
{
	session_start();
	
	session_destroy();
	
	session_unset();
	
	removeLoginCookies();
}

function makeAvatar($user,$avatar)
{
	
	$src = imagecreatefromstring($avatar);
	
	$size = 50;
	
	$dst = imagecreatetruecolor($size,$size);	
	
	$white = imagecolorallocate($dst, 255, 255, 255);
	imagefill($dst,0,0,$white);

	imagecopyresampled($dst,$src,0,0,0,0,$size,$size,imagesx($src),imagesy($src));		
	
	imagejpeg($dst,CONFIG_DATADIR."/avatars/".$user->id.".jpg",$quality=90);
	imagejpeg($src,CONFIG_DATADIR."/avatars/".$user->id."-orig.jpg",$quality=90);

	
	sql("UPDATE users SET avatar=avatar+1 WHERE id=$user->id");
}


function makeLoginKey($u)
{
	global $dbSalt;
	return md5($dbSalt.$u->id.$u->email.$u->twitter_id);
}

?>
