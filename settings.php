<?php

	session_start();

include "php/func.php";
	

	$user = checkLoginCookies();		

	
	if ($user)
	{
		$email = $user->email;			
		$name = $user->name;
		$fullname = $user->fullname;
	}else
	{
		$email = $_GET['email'];
		$name = $_GET['name'];
		$fullname = $_GET['fullname'];
	}
	
	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<meta name="viewport" content="width=860">

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="./css/main.css" />
<link rel="stylesheet" type="text/css" href="./css/iphone.css" />
<link rel="stylesheet" href="./css/font-awesome.css">
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



<title>settings</title>


<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-30341747-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>

<body class="dark">
<?php
	//writeTopMenu($user);
	
		
?>

<div class="outer">
	<div class="container">
		<div class="inner">

<div class="intro single">
<div id="title"><?php echo $user ? "User Settings" : "Welcome to Pendactive";?></div>

<form id="form1" name="form1" method="post" action="/php/verify.php" enctype="multipart/form-data" >
<div class="table">
  <table width="90%" border="0" align="center" cellpadding="10">
    <tr>
      <td width="40%"><div align="right">Nickname: </div></td>
      <td width="60%"><?php echo '<input name="name" type="text" id="name" value="'.$name.'"/>' ?></td>
    </tr>

    <?php if ($user) { ?>

    <tr>
      <td width="40%" ><div align="right">Full name: </div></td>
      <td width="60%"><?php echo '<input name="fullname" type="text" id="name" value="'.$fullname.'"/>' ?></td>
    </tr>
    
     <?php } ?>
   
    
    <tr>
      <td width="40%"><div align="right">Email: </div>
      <td width="60%"><?php echo '<input name="email" type="text" id="email" value="'.$email.'"/>' ?></td>
    </tr>
    <tr>
      <td width="40%"><div align="right">Password: </div></td>
      <td width="60%"><?php echo '<input name="pwd" type="password" id="pwd" AutoComplete="off" />' ?></td>
    </tr>
    <tr>
      <td width="40%"><div align="right">Confirm password: </div></td>
      <td width="60%"><?php echo '<input name="pwd2" type="password" id="pwd2"  AutoComplete="off" />' ?></td>
    </tr>

    <?php if ($user && TWITTER_CONSUMER_KEY) { ?>

    
    <tr>
    <td width="40%"><div align="right"><i class="icon-twitter-sign"></i> Twitter: </div></td>
    <td width="60%">
    
	    
    <?php
	    echo '<a href="/php/twitterredirect.php?force=1">';

		if ($user->twitter_id)
	    	echo $user->twitter_name;
	    else
	    	echo 'Link';
	    	
	    	
	    echo '</a>';
    ?>
    
    </td>
    </tr>
    
    
    <?php } ?>
    
    
    <?php if ($user) { ?>
    
    <tr>
      <td width="40%" valign="top"><div class="fieldtitle">Avatar: </div></td>
      <td width="60%" >
      
      <?php echo '<div class="avatar"><img src="/php/image.php?avatar='.$user->id.'&v='.$user->avatar.'"></div>'.'<br>'; ?>
      
      <input size="50" type="file" name="avatar" />
	  </td>
    </tr>

    <?php } ?>
    
    
    <tr>
      <td width="40%"><div align="right"></div></td>
      
      <td width="60%">
       <button class="button large gray" onclick="window.location='/'; return false;" /><i class="icon-remove"></i> Cancel</button>


	<?php
      
      if ($user)
 	     echo '<button class="button large blue" name="Submit" value="Save" class="button"><i class="icon-ok"></i> Save Settings</button>';
	  else 	 
 	     echo '<button class="button large orange" name="Submit" value="Save" class="button"><i class="icon-ok"></i> Sign up</button>';
      
      
      ?>
      </td>
    </tr>
  </table>
 </div>
  
  
  </form>
  
  </div>
 </div>

 </div>
 </div>
 </div>

  
</body>
</html>
