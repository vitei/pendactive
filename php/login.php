<?php
	include "func.php";

	session_start();
	


	
	$email = $_POST["email"];
	$pwd = $_POST["pwd"];
	$signup = $_POST["signup"];	
	
	
	$pwdkey = $_GET["pwdkey"];
	$loginkey = $_GET["loginkey"];
	$uid = $_GET["uid"];

	if (!$email)
		$email = $_GET['email'];

	$gotoSettings = $_GET["s"];

	connect();			
	
	if ($signup)
	{
		header("Location:/settings.php?email=".$email);
	}else
	{
		if (!$pwdkey && $pwd)
			$pwdkey = md5($dbSalt.$pwd);
			
		if ($email && $pwdkey)
			$user = sqlObject("SELECT * FROM users WHERE email=".sqlVar($email)." AND pwdkey=".sqlVar($pwdkey));
		else if ($uid && $loginkey)	
		{
			$user = sqlObject("SELECT * FROM users WHERE id=".sqlVar($uid));
			if (makeLoginKey($user)!=$loginkey)
				die();
		}
			
			
		disconnect();			
					
		if ($user)
		{			
			
			login($user);
			
			if ($gotoSettings)
				header("Location: /settings.php");
			else
				header("Location: /");
			
		}else
		{
			$error = "Bad email address or password.";
			
		}				
	}	
					

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=500">
<link rel="stylesheet" type="text/css" href="../css/main.css" />
<link rel="stylesheet" type="text/css" href="../css/nav.css" />
<link rel="stylesheet" href="../css/font-awesome.css">
<link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>


<style type="text/css">

html,body{width:100%; height:100%;}

.container{
	text-align: center;
	position: relative;
	vertical-align: middle;
	display: table-cell;
	/*padding-top: 40px;*/
}

.inner{
	margin-left: auto;
	margin-right: auto;
	
	width: 100%;
	
}

</style>

<title>login</title>
</head>



<body class="dark">


<div class="outer">
	<div class="container">
		<div class="inner">

<div class="intro single">

<div id="title"><i class="icon-warning-sign icon-large" style="color:#d00"></i> Sorry, we had a problem!</div>


	<h3>
	
	<?php echo $error; ?>
	
	<br><br>
	
	<a href="javascript:history.go(-1);"><i class="icon-backward"></i> Try again</a>

	</h3>
	

	<p>
	<?php 
		if (validEmail($email)) 
			echo '<a href="/php/forgot.php?email='.$email.'"> Forgot password?</a>'; 
	?>
	</p>	
	
	
</div>

</div>

</body>
</html>



