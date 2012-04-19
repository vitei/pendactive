<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<style type="text/css">

html,body{width:100%; height:100%;}

body{
	font-family: Helvetica, Verdana, Arial, sans-serif;
	background-color: #fff;
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
	background-color: #eee;
	
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
}

.inner{
	margin-left: auto;
	margin-right: auto;
	
	width: 600px;
	
/*	background-color: #f00;*/
}

.sitetitle{
	width:100%;
	text-align: center;
	font-size: 45px;
	color: #bbb;
	font-weight: bold;
	margin-bottom: 80px;

}


.logotitle{
	vertical-align: middle;
	height:100%;
	width:220px;
	float:right;
	text-align: left;
	font-size: 40px;
	color: #bbb;
	font-weight: bold;
	margin-right: -10px;
	
	
/*	background-color: #0f0;*/

}

.footer{
	margin-top: 100px;
	font-size: small;
	color: #999;

}


</style>

<meta name="viewport" content="width=800">


<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script language="JavaScript" src="/js/placeholder.js"></script>

<link rel="stylesheet" type="text/css" href="../css/main.css" />
<link rel="stylesheet" href="../css/font-awesome.css">
<link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>

<title>Pendactive</title>
</head>

<body onLoad="checkPlaceholders();">


<div class="outer">
	<div class="container">
		<div class="inner">
		
		    <div class="sitetitle">
		   <div style="font-size:70px"><span class="pending">Pend</span><span class="active">active</span></div>
		    </div>
		
			<div class="logotitle" style="font-size:40px; line-height:50px;">
			<div>recursive</div>
			<div>task</div>
			<div>management</div>
						
				<div style="margin-top:20px">
					<form id="login" name="login" method="post" action="/php/login.php" >
					<input name="email" type="text" id="login-email" placeholder="email"/>
					<input name="pwd" type="password" id="login-pwd" placeholder="password" />
					<button class="button medium lgray" style="padding: 10px 8px"><i class="icon-user icon-medium"></i> Log in</button>
					 <button class="button medium blue" name="signup" value="1" style="padding: 10px 8px"><i class="icon-ok icon-medium"></i> Sign up</button>
					</form>
					
					
					
				</div>
			</div>

		
			<div class="bbarc" onclick="location.href='/?p=1513'" style="cursor:pointer">
				<div class="bar0" style="height:100px"></div>
				<div class="bar1" style="height:200px"></div>
				<div class="bar2" style="height:300px"></div>
			</div>
			
			
		</div>
		
		
		
		
	<div class="footer">
		contact: <a href="mailto:feedback@pendingactivedone.com">feedback@pendingactivedone.com</a>
	</div>
		
</div>



  
</body>
</html>