<?php
	include "func.php";

	
	$email = $_GET['email'];


	
	if ($email)
	{
	
		connect();			
		$user = sqlObject("SELECT * FROM users WHERE email=".sqlVar($email));
		disconnect();			
					
		if ($user)
		{	
			$subject = "Your account at Pendactive";
			$mail = "Hello ".$user->fullname.",<br><br>Please click the following link to login and change your password:<br><br>";

			
			$url = CONFIG_BASEURL.'/php/login.php?email='.$user->email.'&pwdkey='.$user->pwdkey.'&s=1';
			$mail .= makeClickableLinks($url,false,false);

			sendEmail($user->email,$subject,$mail);	
			
			$info = "Please check your inbox and spam folder.";
			
		}else
		{
			$error = "We don't have a user with that email address.";			
		}				
	}else
		die();	
					

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

<title>forgotten password</title>
</head>



<body class="dark">


<div class="outer">
	<div class="container">
		<div class="inner">

<div class="intro single">

<div id="title">

	<?php 
	if ($error)
		echo '<i class="icon-warning-sign icon-large" style="color:#d00"></i> Sorry, we had a problem!';
	else
		echo 'We sent you a mail.';
	
	?>
</div>

	<h3>
	
	<?php 
		if ($error)
			echo $error; 
		else
			echo $info;
	?>
	
	<br><br><a href="/"><i class="icon-backward"></i> Back</a>

	</h3>
</div>

</div>

</body>
</html>



