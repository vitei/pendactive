<?php

	include "func.php";


	if ($_GET['email'] && $_GET['pwdkey'])
	{
		connect();
		$user = sqlObject("SELECT * FROM users WHERE email=".sqlVar($_GET['email'])." AND pwdkey=".sqlVar($_GET['pwdkey']));
		if ($user)
			login($user);
		disconnect();
		header("Location:/php/stats.php");
		die();
	}

	
	$user =	checkLoginCookies();		
	
		
	if ($user->id!=1)
	{
		header("Location:/login");
		die();
	}

	
	connect();


	$sort = $_GET['s'];
	
	if (!$sort)
		$sort = "reg";



	$delete = $_POST['delete'];
	
	if ($delete && is_numeric($delete))
	{
		sql("DELETE FROM users WHERE id=$delete LIMIT 1");		
	}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<style type="text/css">

p {
	text-align: center;
	margin-left: 0px;
	margin-bottom: 5px;
	margin-right: 0px;
	margin-top: 5px;
	padding: 0px;
}

form {
	text-align: center;
}

</style>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=420">

<link rel="stylesheet" href="../css/font-awesome.css">


<link rel="stylesheet" type="text/css" href="../css/main.css" />
</head>
<body>

		<p>
		<?php
		
		$q = "SELECT";
		$q .= " (SELECT COUNT(*) FROM users) AS total";
		$q .= ", (SELECT COUNT(*) FROM users WHERE created > (UNIX_TIMESTAMP()-(60*60*24*1))) AS day0";
		$q .= ", (SELECT COUNT(*) FROM users WHERE created > (UNIX_TIMESTAMP()-(60*60*24*2)) AND created < (UNIX_TIMESTAMP()-(60*60*24*1))) AS day1";
		$q .= ", (SELECT COUNT(*) FROM users WHERE created > (UNIX_TIMESTAMP()-(60*60*24*3)) AND created < (UNIX_TIMESTAMP()-(60*60*24*2))) AS day2";
		$q .= ", (SELECT COUNT(*) FROM users WHERE created > (UNIX_TIMESTAMP()-(60*60*24*4)) AND created < (UNIX_TIMESTAMP()-(60*60*24*3))) AS day3";
		
		$r = sqlObject($q);
				
		$url = "?email=".$user->email."&pwdkey=".$user->pwdkey;
		echo '<p><a href="'.$url.'">Logged in</a> as <a href="/">'.$user->name.'</a></p>';
		
		echo "<p>Signups: $r->total";		
		echo " ($r->day0, $r->day1, $r->day2, $r->day3)";		
		echo '</p>';

		
		
		?>
		</p>
	
		<p>
		Sort by : <a href="?s=reg">Join</a>, <a href="?s=msg">Updates</a>,  <a href="?s=num"># Msgs</a>
		</p>
		
		<form method="post">
		<input name="delete" style="width: 40px"/><button>Delete User</button>
		</form>

		
		
		

<?php


		$q = "SELECT *";
		$q .= ",users.id AS uid";
		$q .= ",(SELECT COUNT(*) FROM messages WHERE sender=uid AND !deleted) AS total";
		$q .= ",(SELECT idate FROM messages WHERE sender=uid AND !deleted ORDER BY idate DESC LIMIT 1) AS latest";
		$q .= " FROM users ";
		
		if ($sort == "reg")
			$q .= " ORDER BY id DESC";
		else if ($sort == "msg")
			$q .= " ORDER BY latest DESC";
		else if ($sort == "num")
			$q .= " ORDER BY total DESC";
			
		$q .= " LIMIT 50";
		
		$users = sqlArray($q);
		
//		$users = sqlArray("SELECT *,users.id AS uid,(SELECT COUNT(*) FROM messages WHERE sender=uid) AS total FROM users ORDER BY id DESC");
		
		echo '<div style="width:400px; margin:auto; font-size:small;">';
		foreach($users as $u)
		{
			if ($u->id!=ANYBODY_ID && $u->id!=NOTICE_ID)
			{
			    echo '<div class="finduser-result">';
	  			echo 	'   <div style="float:left;" class="avatar">';
	  			echo	     '<img src="/php/image.php?avatar='.$u->id.'&v='.$u->avatar.'">';
	  			echo        '</div>';  		

	      		$url = "/php/login.php?uid=".$u->id."&loginkey=".makeLoginKey($u);
	      		echo 	      '<div style="float:right; text-align:right; clear:right;">';
	      		echo 		 '<a href="'.$url.'">';
	      		echo 	 	    '<div style="font-size:small;">';
	      		
	      		echo   				$u->total.' msgs';		      		

	      		if ($u->latest)
					echo 	 	    ', '.timeDiff($u->latest);		      		

		      	echo   		   '</div>';	
	      		echo 		 '</a>';
	      		echo 	      '</div>';	   


	      	//	echo  '<div style="float:right; font-size:small; color:#f00;"><a href="?d='.$u->id.'">[X]</a></div>';		      		
				echo 		'<div style="margin-left: 70px;">';
	      		echo 		  '<div >'.$u->id.'. '.$u->name;
	     	    echo '</div>';


	      		
	      		
	      		
	      		echo 	 	  '<div style="font-size:small;">'.$u->fullname.'</div>';	
	      		
	      		if ($u->email)	      		
		      		echo 	 	  '<div style="font-size:small;"><i class="icon-envelope"></i> '.$u->email.'</div>';		
	      		
	      		
	      		if ($u->twitter_id)
	      		{
		      		echo '<div style="font-size:small;">';
	      			echo '<a href="http://twitter.com/'.$u->twitter_name.'"><i class="icon-twitter-sign"></i> '.$u->twitter_name.'</a>';
	      			echo '</div>';
	      		}
	      		
	      		      		
	      		echo 	    '</div>';	   
	      		echo '</div>';
	      	}
		}			
		
		echo '</div>';

		
	
	disconnect();


?>
</body>
</html>