<?php

session_start();


include "func.php";

	$from0 = $_GET['from0'];
	$from1 = $_GET['from1'];
	$from2 = $_GET['from2'];
	$from3 = $_GET['from3'];
	$show = $_GET['show'];
	$find = $_GET['q'];
	$parent = $_GET['p'];
	$flat = $_GET['f'];
	$cols = $_GET['c'];
	$stateMask = $_GET['sm'];
	
		
	
	header("Cache-Control: no-cache, must-revalidate");
	
	$user = checkLoginCookies();		

	connect();
	
	
	if (!$parent)
		$parent=0;

	if ($parent)
		$pmsg = getMessage($parent);


	if ($cols==3)
	{
		if ($from0>=0)
			echo findMessages($from0,$show,$find,0,$pmsg,$user,$flat,3);
			
		if ($from1>=0)
			echo findMessages($from1,$show,$find,1,$pmsg,$user,$flat,3);
		
		if ($from2>=0)
			echo findMessages($from2,$show,$find,2,$pmsg,$user,$flat,3);
	}else{
	
		if ($from3>=0)
			echo findMessages($from3,$show,$find,$stateMask,$pmsg,$user,$flat,1);
	}
		
	

	disconnect();
	
	
	
	
	
?>