<?php

	//include "func.php";

function sql($query,$db)
{
	return mysql_db_query($db,$query);
}

function sqlObject($query,$db)
{
	$r = sql($query,$db);
	if ($r)
		return mysql_fetch_object($r);
	return false;
}

function sqlInsert($query,$db)
{
	$r = sql($query,$db);
	if ($r)
		return mysql_insert_id();
	return 0;
}

function sqlArray($query,$db)
{
	$list = array();
	$r = sql($query,$db);
	if ($r)
		while ($o = mysql_fetch_object($r))
			$list[] = $o;
	return $list;
}






	$link2notes = mysql_connect ("localhost", "login2notes", "peewee1");

	$all = sqlArray("SELECT * FROM messages WHERE sender=1","2notes");

	print_r($all[0]);
	
//	echo count($all);

	mysql_close($link2notes);






	
/*
	connect();	
	
	$all = sqlArray("SELECT * FROM messages");
	
	foreach($all as $msg)
	{
		$msg->html = mecab($msg->plain);
		
		//sql("UPDATE messages SET html=".sqlVar($msg->html)." WHERE id=".$msg->id);
		
		$msg->html=str_replace(" ", '<span style="background-color:#f00;">    </span>', $msg->html);
		
		echo $msg->msgid."<br>".$msg->html."<hr>";
		//echo $msg->id."<br>";
	}
	
	echo "done";
	
	

	disconnect();
*/

?>