<?php
	session_start();
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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


<title>verify</title>
</head>

<body class="dark">
<?php 
	include "func.php";
?>


<div class="outer">
	<div class="container">
		<div class="inner">

	<div class="intro single">

<?php








	if( $_POST['cancel'])
	{
		header("Location: /");
		die();
	}
	

	
	$email = $_POST["email"];
	$name = $_POST["name"];
	$pwd = $_POST["pwd"];
	$pwd2 = $_POST["pwd2"];
	$notify = $_POST["notify"];
	$fullname = $_POST["fullname"];
		
	
	$user = checkLoginCookies();		

	
	$pwdkey = md5($dbSalt.$pwd);
	
		
	connect();	
	
				
	if (!$user && $name && $pwd!="" && $pwd==$pwd2)
	{	
		if (validEmail($email))
		{	
			if (sqlObject("SELECT * FROM users WHERE email=".sqlVar($email)))
				$error = "That email address is already used.";
			else
			{	
				$user->id = createUser($email,$pwdkey,$name);
				$user->email = $email;
				$user->name = $name;
				$user->pwdkey = $pwdkey;
				login($user);			
				connect();
			}
		}else
		{
			$error = "Invalid email address.";
		}
	}
	
	
	if ($user->pwdkey=="" && $pwd=="" && !$user->twitter_id) 
	{
		$error= "Invalid password.";
	}
	else if (!$name) 
	{
		$error= "Please enter a valid nickname.";
	}
	else if (($pwd!="" || $pwd2!="") && ($pwd!=$pwd2)) 
	{
		$error= "Please re-type both passwords correctly.";
	}else  
	{	
		
		if ($_FILES['avatar']['name'])
		{
			$err = $_FILES['avatar']['error'];
		
			if ($err === UPLOAD_ERR_OK)
			{
				$avatar = file_get_contents($_FILES['avatar']['tmp_name']);
				
				makeAvatar($user,$avatar);		
			}
		}	
	
	
		//sql("UPDATE users SET verified=UNIX_TIMESTAMP() WHERE id=$user->id AND verified=0");
		
		sql("UPDATE users SET name=".sqlVar($name).", fullname=".sqlVar($fullname).", notify=".sqlVar($notify?1:0)." WHERE id=$user->id");

		if ($pwd)
		{
			
			sql("UPDATE users SET pwdkey='".$pwdkey."' WHERE id=$user->id");
			
			/*
			$mail = "Your password is: ".$pwd."<br><br>";
		
			mail($email,"Vitei Notes Password",$mail,"From:no-reply@vitei.com\r\nContent-type: text/html\r\n");		
			*/
		}


		if ($email && $email != $user->email)
		{
			sql("UPDATE users SET email=".sqlVar($email)." WHERE id=$user->id");
		
			/*	
			$mail = "Your login email address has changed.".$pwd."<br><br>";
		
			mail($email,"Vitei Notes Email",$mail,"From:no-reply@vitei.com\r\nContent-type: text/html\r\n");		
			*/
		}
							
		

//		if ($email && $email != $user->email)
//			verifyAddEmail($email,0,$user->id);


		$_SESSION['userid'] = $user->id;
		$_SESSION['username'] = $name;
	
	
	
	}				
	
	disconnect();			

	if (!$error)	
	{
		header("Location: /");			
	}
?>

	<div id="title"><i class="icon-warning-sign icon-large" style="color:#d00"></i> Sorry, we had a problem!</div>


	<h3>
	
	<?php echo $error; ?>
	
	<br><br><a href="javascript:history.go(-1);"><i class="icon-backward"></i> Try again</a>

	</h3>
	

	</div>
</div>
</div>

</body>

</html>