<?php


include "func.php";



function showAvatar($id)
{
	
	$fname = CONFIG_DATADIR."/avatars/".$id.'.jpg';
	
	Header("content-type:image/jpeg"); 
	
	if (file_exists($fname))
		readfile($fname);
	else
		readfile(CONFIG_DATADIR."/avatars/0.jpg");
	
	
}

function showThumb($id)
{
	$fname = CONFIG_DATADIR."/thumbs/".$id.'-100.jpg';
	
	Header("content-type:image/jpeg"); 
	
	if (file_exists($fname))
		readfile($fname);
}




	$avatar = $_GET['avatar'];
		
	if (intval($avatar))
		showAvatar($avatar);
	

	$thumb = $_GET['thumb'];
	
	if ($thumb)
		showThumb($thumb);


?>