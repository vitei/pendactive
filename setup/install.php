<?php

	include "../php/func.php";

	echo "Pendactive Setup<br><br>";
		
	
	echo "Checking MYSQL connection... ";
	
	$link = @mysql_connect (CONFIG_DB_HOSTNAME, CONFIG_DB_USER, CONFIG_DB_PASSWORD);	
	
	showResult($link);
	
	mysql_select_db(CONFIG_DB_NAME, $link);
	
	
	echo "Creating TABLES... ";


	$sql = file_get_contents("db.sql");
	
	
	$queries = preg_split("/;+(?=([^'|^\\\']*['|\\\'][^'|^\\\']*['|\\\'])*[^'|^\\\']*[^'|^\\\']$)/", $sql); 
	foreach ($queries as $query)
	{ 
   		if (strlen(trim($query)) > 0) 
   		{
   			$r = mysql_query($query); 
   			if (!$r)
   				break;
	   	}
	} 
	showResult($r);
	
	
	
	echo "Creating default users... ";
	
   	$r = mysql_query("INSERT INTO users (id,name) VALUES (".NOTICE_ID.',"Notice")'); 
	showResult($r);




	echo "Checking data directory... ";
	
	$testfile = CONFIG_DATADIR.'/test';
	
	$handle = fopen($testfile, "w");
	showResult($handle);
	
	unlink($testfile);
	
	
	echo "Creating avatars directory... ";
	$r = mkdir(CONFIG_DATADIR.'/avatars');
	showResult($r);

	echo "Creating thumbs directory... ";
	$r = mkdir(CONFIG_DATADIR.'/thumbs');
	showResult($r);



	echo "Copying default avatars... ";
	$r = copy("../images/notice_avatar.jpg", CONFIG_DATADIR.'/avatars/'.NOTICE_ID.'.jpg');
	$r |= copy("../images/anybody_avatar.jpg", CONFIG_DATADIR.'/avatars/'.ANYBODY_ID.'.jpg');
	$r |= copy("../images/default_avatar.jpg", CONFIG_DATADIR.'/avatars/'.DEFAULT_ID.'.jpg');
	
	showResult($r);



	
	
	
	echo '<br><br><font color="green">SUCCESS!</font><br>';
	
	
	mysql_close($link);



function showResult($passed)
{
	if (!$passed)
	{
		echo '<font color="red">FAILED</font><br>';
		die();
	}else
		echo '<font color="green">OK</font><br>';
}


?>