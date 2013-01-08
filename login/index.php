<?php
	include "../php/func.php";

	session_start();
	
//	if (checkLoginCookies())
//		header("Location: /");

	
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<meta name="viewport" content="width=800">


<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<meta name="keywords" content="recursive task management, recursive task list, todo, get this done, GTD, tasks, project management" />


<script language="JavaScript" src="/js/placeholder.js"></script>

<link rel="stylesheet" type="text/css" href="../css/main.css" />
<link rel="stylesheet" href="../css/font-awesome.css">
<link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>

<style type="text/css">

html,body{width:100%; height:100%;}

body{
	font-family: 'Lato', sans-serif;
}

iframe { 
	float:left;
	}

.action {
	clear: both;
	margin-top: 0px;
	font-size: 27px; 
	text-align: right;
	width: 200px;
	padding: 15px 0;

}

.action div{
	margin: 5px 0;
}





.bbarc{
	width:300px; 
	height:300px; 
	background-color:#fff; 
	border: solid 15px #fff; 
	
	-webkit-border-radius: 40px; 
	-moz-border-radius: 40px; 
	border-radius: 40px; 
	
	
	color:#f00;
	
	-webkit-box-shadow: 0 0px 30px rgba(0,0,0, 0.2);
	-moz-box-shadow: 0 0px 30px rgba(0,0,0, 0.2);
	-box-shadow: 0 0px 30px rgba(0,0,0, 0.2);
	
	padding:0;
	margin:0;
	
	-webkit-transition: all 0.2s ease-in-out;
	-moz-transition: all 0.2s ease-in-out;
	transition: all 0.2s ease-in-out;
	
	float:left;	
}	


.bbarc:hover{
	-webkit-box-shadow: 0 0px 30px rgba(0,0,0, 0.4);
	-moz-box-shadow: 0 0px 30px rgba(0,0,0, 0.4);
	-box-shadow: 0 0px 30px rgba(0,0,0, 0.4);
}

.bbarc .bar0{
	width:90px; 
	background-color:#d66;
	position: absolute;
	margin-left:0px;	
	-webkit-border-radius: 30px; 
	-moz-border-radius: 30px; 
	border-radius: 30px; 
}
.bbarc .bar1{
	width:90px; 
	background-color:#6b6; 
	position: absolute;
	margin-left:105px;	
	-webkit-border-radius: 30px; 
	-moz-border-radius: 30px; 
	border-radius: 30px; 
}
.bbarc .bar2{
	width:90px; 
	background-color:#66d; 
	position: absolute;
	margin-left:210px;	
	-webkit-border-radius: 30px; 
	-moz-border-radius: 30px; 
	border-radius: 30px; 
}


.outer{
	width: 100%;
	height: 100%;
	display: table;
	vertical-align: middle;
	
	background: radial-gradient(center, ellipse cover,  rgba(255,255,255,1) 0%,rgba(196,220,255,1) 100%);
	background: -moz-radial-gradient(center, ellipse cover,  rgba(255,255,255,1) 0%, rgba(196,220,255,1) 100%);
	background: -webkit-gradient(radial, center center, 0px, center center, 100%, color-stop(0%,rgba(255,255,255,1)), color-stop(100%,rgba(196,220,255,1)));
	background: -webkit-radial-gradient(center, ellipse cover,  rgba(255,255,255,1) 0%,rgba(196,220,255,1) 	100%);
	background: -o-radial-gradient(center, ellipse cover,  rgba(255,255,255,1) 0%,rgba(196,220,255,1) 100%);
	background: -ms-radial-gradient(center, ellipse cover,  rgba(255,255,255,1) 0%,rgba(196,220,255,1) 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#c4dcff',GradientType=1 );
}
.container{
	text-align: center;
	position: relative;
	vertical-align: middle;
	display: table-cell;
}

.inner{
	margin-left: auto;
	margin-right: auto;
	
	width: 600px;
	
/*	background-color: #dd0;*/
	padding: 10px 30px;
}

.sitetitle{
	width:100%;
	text-align: center;
	font-size: 50px;
	color: #66a;
	font-weight: bold;

	text-shadow: rgba(0,0,0,0.3) 1px 1px 2px;
}

.sitetitle i{
	font-size:30px;
	color: #ace;
}


.subtitle{
	color:#888;
	font-size: 20px;
	margin-top: 10px;
	margin-bottom: 30px;
}



.logotitle{
	vertical-align: middle;
	height:100%;
	width:250px;
	float:right;
	text-align: left;
	font-size: 40px;
	color: #888;
	font-weight: bold;
	margin-right: -10px;
	

}

.login{
	margin-top: 30px;
}

.footer{
	padding-top: 70px;
	font-size: x-small;
	color: #999;
	clear:both;
	text-align: center;
}


.video{

	background-color: #000; 
	margin:auto; 
	width: 720px; 
	height: 440px;

	margin-top: 10%;

	-webkit-border-radius: 16px;
    -moz-border-radius: 16px;
    border-radius:16px;		

   	-webkit-box-shadow: 3px 3px 10px rgba(0,0,0, .5);
	-moz-box-shadow: 3px 3px 10px rgba(0,0,0, .5);
	box-shadow: 3px 3px 10px rgba(0,0,0, .5);

}

.video iframe{
	margin-left: 40px;
	margin-top: 40px;

}

.video i{
	float:right;
	font-size: 40px;
	margin-right: -15px;
	margin-top: -15px;
	
	
	background-color: #000;
	color: #fff;

	line-height: 40px;
	
	padding: 0px;


	-webkit-border-radius: 40px;
    -moz-border-radius: 40px;
    border-radius:40px;		
    
   	-webkit-box-shadow: 3px 3px 10px rgba(0,0,0, .5);
	-moz-box-shadow: 3px 3px 10px rgba(0,0,0, .5);
	box-shadow: 3px 3px 10px rgba(0,0,0, .5);
 
}

.share{
	background-color: #fff;
	width: 100%;
	padding: 5px 0;
	margin:0;
	
	
	text-align: right;
	
	
	border-bottom-style:solid;
	border-bottom-width:thin;
	border-bottom-color:#ddd;
	

	background-color: #444;
	background: -webkit-gradient(linear, 0 bottom, 0 top, from(#444 ), to(#666));
	background: -moz-linear-gradient(-90deg, #666, #444 ) ;
	
	
	vertical-align: top;
		
}

.share .sns{
	display: inline-table;
	padding:0;
	margin:0;
	width: 100px;
	text-align: left;
}

.share .links {
	margin: 0 20px;
	font-size: 19px;
}

.share .links i{
	margin: 0 5px;
	color:#fff;
}


input{
	background-color: #ffe;
}



</style>


<?php
	echo '<title>'.CONFIG_SITE_TITLE.'</title>';
?>


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


<script language="JavaScript">


	function closePopup()
	{
		var popup=document.getElementById("popup");

		document.body.removeChild(popup);
	}

		
	function makePopup()
	{
		var popup = document.createElement('div');				
		popup.className = "popup";
		popup.id = "popup";
		document.body.appendChild(popup);
	
		var fade = document.createElement('div');				
		fade.className = "fade";
		popup.appendChild(fade);
		
		
		var dialog = document.createElement('div');
		dialog.className = "dialog";
		popup.appendChild(dialog);
		
					
		document.body.style.overflow = "hidden";		

		
		return dialog;
	}		
		

	function showVideo()
	{
		var dialog = makePopup();
		
		
		var out = "";
		
		
		out += '<div class="video">';
		out += '<a href="javascript:closePopup();"><i class="icon-remove-sign icon-large"></i></a>';
		
		out += '<iframe width="640" height="360" src="http://www.youtube.com/embed/YvAa6jGYjnI?rel=0&autoplay=1" frameborder="0" allowfullscreen></iframe>';
		
		out += '</div>';
		
		dialog.innerHTML = out;
		
		
	}

</script>

<body onLoad="checkPlaceholders();">





<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<!-- Place this render call where appropriate -->
<script type="text/javascript">
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>

<a href="http://github.com/vitei/pendactive"><img style="position: absolute; top: 0; left: 0; border: 0; z-index:100;" src="https://a248.e.akamai.net/assets.github.com/img/5d21241b64dc708fcbb701f68f72f41e9f1fadd6/687474703a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f6c6566745f7265645f6161303030302e706e67" alt="Fork me on GitHub"></a>


	<div class="share">


<a rel="license" href="http://creativecommons.org/licenses/by/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by/3.0/80x15.png" /></a>

	<span class="links">
	
	
	<a href="mailto:feedback@pendactive.com"><i class="icon-envelope icon-large"></i></a>
	<a href="https://github.com/vitei/pendactive"><i class="icon-github-sign icon-large"></i></a>
	<a href="http://twitter.com/Pendactive"><i class="icon-twitter-sign icon-large"></i></a>
	<a href="http://www.facebook.com/PendingActiveDone"><i class="icon-facebook-sign icon-large"></i></a>
	
	</span>
	
	<span class="sns">
	<div class="fb-like" data-href="http://pendactive.com" data-send="false" data-layout="button_count" data-width="50" data-show-faces="true"></div>
	</span>
	
	<span class="sns">
	<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://pendactive.com" data-text="Try Pendactive">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	</span>


	<span class="sns">
	<g:plusone size="medium"></g:plusone>
	</span>

	</div>
	
	



<div class="outer">



	<div class="container">




		<div class="inner">
		
		    <div class="sitetitle">pendactive</div>
		    <div class="subtitle">recursive task management</div>
		    
		    
		
			<div class="logotitle">
			
			
			<div class="action">
			<a href="javascript:showVideo();"><div>Watch video <i class="icon-play-circle"></i></div></a>
			<a href="/?p=1513"><div>Try it now <i class="icon-cog"></i></div></a>
			<a href="/php/twitterredirect.php" ><img src="/images/sign-in-with-twitter-l.png" style="margin-top:10px;"></a>
			</div>
						
				<div class="login">
				
				
					<form id="login" name="login" method="post" action="/php/login.php" >
					<input name="email" type="text" id="login-email" placeholder="email"/>
					<input name="pwd" type="password" id="login-pwd" placeholder="password" />
					<button class="button medium blue" style="padding: 10px 8px"><i class="icon-user"></i> Log in</button>
					<button class="button medium orange" name="signup" value="1" style="padding: 10px 8px; font-weight:bold"><i class="icon-ok"></i> Sign up</button>
					</form>
					

					
				</div>
			</div>

		
			<a href="javascript:showVideo();">
			<div class="bbarc" style="cursor:pointer">
				<div class="bar0" style="height:100px"></div>
				<div class="bar1" style="height:200px"></div>
				<div class="bar2" style="height:300px"></div>
			</div>
			</a>


			
		</div>
		
		
	
	<div class="footer">
		<div>© Vitei Inc | <a href="/privacy">Privacy Policy</a> | Icons by <a href="http://fortawesome.github.com/Font-Awesome/">Font Awesome</a></div>
	</div>		
	

</div>
</body>
</html>