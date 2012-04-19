<?php

	session_start();

include "php/func.php";



	$id = $_GET['id'];
	$key = $_GET['key'];
	
	connect();
	
	$user = getLoggedInUser();
	
	$invite = sqlObject("SELECT * FROM invites WHERE id=".sqlVar($id));
	
	if (!$invite)
		$error = "Invalid invite";
	else if ($key != makeInviteKey($invite->message,$invite->email,$invite->id))
		$error = "Invalid key";	
	



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<meta name="viewport" content="width=860">

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="./css/main.css" />
<link rel="stylesheet" type="text/css" href="./css/nav.css" />
<link rel="stylesheet" href="./css/font-awesome.css">
<link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>

<style type="text/css">

html,body{width:100%; height:100%;}

.outer{
	width: 100%;
	height: 100%;
	display: table;
	vertical-align: middle;
	background-color: #f8f8f8;
	
background: rgb(255,255,255);
background: -moz-radial-gradient(center, ellipse cover,  rgba(255,255,255,1) 0%, rgba(196,220,255,1) 100%);
background: -webkit-gradient(radial, center center, 0px, center center, 100%, color-stop(0%,rgba(255,255,255,1)), color-stop(100%,rgba(196,220,255,1)));
background: -webkit-radial-gradient(center, ellipse cover,  rgba(255,255,255,1) 0%,rgba(196,220,255,1) 100%);
background: -o-radial-gradient(center, ellipse cover,  rgba(255,255,255,1) 0%,rgba(196,220,255,1) 100%);
background: -ms-radial-gradient(center, ellipse cover,  rgba(255,255,255,1) 0%,rgba(196,220,255,1) 100%);
background: radial-gradient(center, ellipse cover,  rgba(255,255,255,1) 0%,rgba(196,220,255,1) 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#c4dcff',GradientType=1 );
	
}
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


<title>Join</title>
</head>

<body class="dark">

<div class="outer">
	<div class="container">
		<div class="inner">

<div class="intro single">


<div id="title">
<?php echo "Welcome to ".CONFIG_SITE_TITLE; ?>
</div>


<p>

<?php
if ($error)
	echo $error;
else if ($invite)
{
	$msg = getMessage($invite->message);

	echo '<h3>You have been invited to join: <br><br>'.$msg->plain.'</h3>';
	echo '<form id="form1" name="form1" method="post" action="/php/accept.php" enctype="multipart/form-data" >';
    echo '<button class="button large gray" /><i class="icon-remove"></i> Decline</button>';
    echo '<button class="button large blue" name="accept" value="1" class="button"><i class="icon-ok icon-large"></i> Accept</button>';
      
	echo '<input type="hidden" name="id" value="'.$id.'">';
	echo '<input type="hidden" name="key" value="'.$key.'">';

  	echo '</form>';

}

	disconnect();	


?>

</p>

  
  
  
  </div>
  
</body>
</html>
