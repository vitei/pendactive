<?php

include "php/func.php";


	session_start();


	$query = $_GET['q'];
	$parent = $_GET['p'];
	$hilight = $_GET['h'];
	$hilightFrom = $_GET['hf'];
	$isFlat = $_GET['f']?1:0;
	$numCols = $_GET['c']==1?1:3;
	
	$user = checkLoginCookies();		
	
	if (!$user && !$parent)
	{
		header("Location:/login/");
		die();
	}


	



	if (!$parent)
		$parent = 0;

	if (!$hilight)
		$hilight = 0;
	if (!$hilightFrom)
		$hilightFrom = 0;


	connect();

	
	if ($hilightFrom)
		sql("UPDATE users SET latest_notice=(SELECT id FROM messages ORDER BY id DESC LIMIT 1) WHERE id=$user->id");


	
	if ($parent)
	{
		$pmsg = getMessage($parent);
		
		if ($pmsg->root)
		{
			$rmsg = getMessage($pmsg->root);
			$member = sqlObject("SELECT * FROM members WHERE user=$user->id AND message=$pmsg->root");
		}else
			$member = sqlObject("SELECT * FROM members WHERE user=$user->id AND message=$pmsg->id");
			
			
		if (!$member && !$rmsg->public && !$pmsg->public)
		{
			//die("access denied");
			header("Location:/login");
			die();
		}
	}

//	$canPost = ((!$pmsg || $member) && !$isFlat) ? 1 : 0;
	$canPost = ((!$pmsg || $member)) ? 1 : 0;

	

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 
 
<?php

	if ($numCols==1)
		echo '<meta name="viewport" content="width=600">';
?>


<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">



<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script language="JavaScript" src="/js/placeholder.js"></script>

<link rel="stylesheet" type="text/css" href="./css/main.css" />
<link rel="stylesheet" type="text/css" href="./css/loading.css" />
<link rel="stylesheet" type="text/css" href="./css/iphone.css" />
<link rel="stylesheet" href="../css/font-awesome.css">
<link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>




<?php 
	if (CONFIG_FEEDBACK_SCRIPT)
		echo CONFIG_FEEDBACK_SCRIPT;
	
	echo '<style type="text/css">';
	if ($numCols==1)
	{
	/*
		echo '#topmenu{ width: 300px;}';
		echo '.finder{ width: 150px; }';*/
	}echo '</style>';
?>


<title>

<?php 


	if ($pmsg)
		echo CONFIG_SITE_TITLE.' / '.messageTitle($pmsg->plain); 
	else if ($rmsg) 
		echo CONFIG_SITE_TITLE.' / '.messageTitle($rmsg->plain); 
	else
		echo CONFIG_SITE_TITLE;
	
?>

</title>



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

	<body onLoad="init();" >



<?php


	writeTopMenu($user,true,$tag,$user||$parent,$location,$source,$assigned,$via,$marked,$find,$query,$parent,$isFlat,$pmsg,$numCols); 
	
	disconnect();
	
	echo '<script language="JavaScript">';
	echo 'var searchTerm="'.$query.'";';	
	if ($user->id)
		echo 'var userid='.$user->id.";";
	else
		echo 'var userid=0;';
	echo 'var parent='.$parent.';';	
	echo 'var hilight='.$hilight.';';
	echo 'var hilightFrom='.$hilightFrom.';';
	echo 'var isFlat='.$isFlat.';';
	echo 'var canPost='.$canPost.';';
	echo 'var numCols='.$numCols.';';
	echo '</script>';
		
	

	
?>

<script language="JavaScript">

	var searchFrom=new Array();
	var searchShow=10;
	var searchTimeout;
	var searchInput=false;
	var searchFirstClick=true;
	var searchReq;
//	var searchTerm="";
	var searchExhausted=new Array();
	var searchTotal=new Array();
	var searchHidden=0;
	var stateMask=(8+4+2+1);
	var animDelay = 30;
	var animSpeed = 0.05;
	
	
	var uniqueID = Math.floor(Math.random()*1000000);
	
	var historyID=0;

	var checkTimeout;
	
	var deleteMsgID=0;
	
	var lastHilightMsgID=0;
	
	var Action={
		NONE:0,
		NEWMSG:1,
		DELETEMSG:2,
		MOVEBEFOREMSG:3,
		MOVEAFTERMSG:4,
		MOVETOPMSG:5,
		EDITMSG:6,
		MOVEINTOMSG:7
	}
	
	
	function init()
	{
		if (!userid && !parent)
			return;
			
			
		clearResults(); 
		doSearch(); 
		checkScroll();
		checkTimeago();


		if (hilightFrom)
		{
			var hilight = document.getElementById("notice");
			if (hilight)
				hilight.style.visibility="hidden";	
		}


		
		checkPlaceholders();
		
		checkHistory();

		if (isMobile())
		{
		/*
			document.addEventListener("touchstart",touchStart,false);
			document.addEventListener("touchend",touchEnd,false);
			document.addEventListener("touchcancel",touchEnd,false);
			*/
			animSpeed = 0.2;
			/*			
			document.onmousedown = function(){return false};
			document.onmouseup = function(){return false};
			*/
				
		}else{
		}
		
		document.onmousedown = onMouseDown;
		document.onmouseup = onMouseUp;
		
		document.onselectstart = function () {return false};
		
	}
	
	var touchTimeout;
	
	function touchStart(e)
	{	
		onMouseDown(e.changedTouches[0]);
		
		//if (dragID)
		//	e.preventDefault();
		/*
		if (dragID)
		{				
			touchTimeout = setTimeout(function(){onMouseDown(e.changedTouches[0]);},250);
		}
		*/
	}

	function touchMoveCheck(e)
	{
		onMouseMoveCheck(e.changedTouches[0]);
		//e.preventDefault();
	}
	
	function touchMove(e)
	{
		onMouseMove(e.changedTouches[0]);
		e.preventDefault();
	}
	function touchEnd(e)
	{
		onMouseUp(e.changedTouches[0]);
		clearTimeout(touchTimeout);
	}
	
	
	function getNumber(value)
	{
	    var n = parseInt(value);
		
	    return n == null || isNaN(n) ? 0 : n;
	}


	function posInColumn(cx,cy,col)
	{
		var intro = document.getElementById("intro"+col);
		
		if (intro)
		{
					
			var left = getAbsX(intro);
			var top = getAbsY(intro);				
		
			if ( cx >= left && cx <= (left+intro.offsetWidth) && cy >= top )
				return intro;
		}
		return null;
	}			
	
	var dragAlpha=0.7;
	var dragStartX,dragStartY;
	var dragOffsetX,dragOffsetY;
	var dragParent;
	var dragOriginX,dragOriginY;
	var dragPosX,dragPosY;
	var dragFromX,dragFromY;
	var dragDIV;
	var dragID;
	var dragToState,dragOldState;
	var dragRot,dragRotMax=5;
	var dragReturnSibling,dragReturnSiblingAfter;
	var dragSiblingState;
	var dragBusy;
	var dragDummy,dragPlaceholder;
	var dragTarget;
	
	var dragAfter,dragBefore,dragInto;
	var dragIntoFixedPos;
	
	var dragLastMessage;
	var	dragTimeout;
	var dragScale;
	var dragInTopMenu;
	var dragToNewState;
	var dragCanInsert;
	var dragNoMove;
	var dragDragging;


	function isIE() {return document.all;}
	
	
	
	function setRotationTranslateScale(div,deg,x,y,s)
	{
		dragPosX = x;
		dragPosY = y;
		
		if (isIE())
		{
			div.style.left = (dragOriginX+x)+"px";
			div.style.top = (dragOriginY+y)+"px";
		}else
		{
			var t = " translateX("+x+"px) translateY("+y+"px)" + " rotate("+deg+"deg)"+" scale("+s+")";
	
			div.style.webkitTransform = t;
			div.style.MozTransform = t;
		}	
	}
	
	
	function getClickDivID(e)
	{
	    if (e == null) 
    	    var e = window.event; 
    
    	var target = e.target != null ? e.target : e.srcElement;
    
    	if (target.tagName=="DIV")
    	{
    		var p = target;
    		
    		while (p)
    		{
    			if (p.id.substr(0,3) == "msg")
    			{
					return Number(p.id.substr(3));
    			}
			
    			p = p.parentNode;
    		}
    	
    	
    	}

		return 0;
	}
	
	
	function onClick(e)
	{		
		var id = getClickDivID(e);
		
		if (id)
			document.location = "/?p="+id;			
	}
	function onDoubleClick(e)
	{
	
		var id = getClickDivID(e);
		
		if (id)
			editMessage(id);
	}
	

	function onMouseDown(e)
	{
    	dragID = 0;
    	dragDragging=false;

		if (dragBusy)
			return false;
	
	    if (e == null) 
    	    var e = window.event; 
    
    	var target = e.target != null ? e.target : e.srcElement;
    
    	if (e.button==2)
    		return;
    
    	
    	if (target.tagName=="DIV")
    	{
    		var p = target;
    		
    		var nomove=false;

	    		
    		while (p)
    		{
    			if (p.className=="pad")
    				return false;
    			//if (p.className=="comment")
    			//	return false;
    				
    			if (p.getAttribute!=undefined)
					if (p.getAttribute("nomove"))
						nomove=true;
    				
    		
    			if (p.id && p.id.substr(0,3) == "msg")
    			{
					dragID = Number(p.id.substr(3));
					dragNoMove = nomove;
					
	  				dragStartX = e.clientX;
					dragStartY = e.clientY;
					
					if (isMobile())
						document.addEventListener("touchmove",touchMoveCheck, false);
					else
						document.onmousemove = onMouseMoveCheck;
		
					
					dragLastMessage = null;
					
					if (p.nextSibling)
					{
						dragReturnSibling = p.nextSibling;
						dragReturnSiblingAfter = false;
					}else{
						dragReturnSibling = p.previousSibling;
						dragReturnSiblingAfter = true;
					}

    				return false;
    			}
			
    			p = p.parentNode;
    		}
    	
    	
    	}
    
	}
	
	function hasChild(p,n)
	{
		if (p==n)
			return true;
			
		var c = p.firstChild;
		while (c != null)
		{
			if (hasChild(c,n))
				return true;
				
			c = c.nextSibling;
		}
			
		return false;
	}
	
	function onMouseMoveCheck(e,force)
	{
	   if (e == null) 
    	   var e = window.event; 

		
		var dx = e.clientX - dragStartX;
		var dy = e.clientY - dragStartY;		
		
		dragDragging = false;

		if (Math.sqrt(dx*dx+dy*dy) > 5 || force)
		{
			if (dragNoMove)
				dragID = 0;
			else if (dragMessage(dragID))
			{    				
			
				dragDragging = true;
				
				dragStartX = e.clientX;
				dragStartY = e.clientY;
				
				dragOriginX = dragDIV.offsetLeft;
				dragOriginY = dragDIV.offsetTop;
				    				
				dragBefore = dragAfter = dragInto = null;
				
				dragToNewState = -1;
				
				dragCanInsert = null;
				
				if (isMobile())
				{
					document.removeEventListener("touchmove",touchMoveCheck, false);
					document.addEventListener("touchmove",touchMove, false);
				}else
				{
					document.onmousemove = onMouseMove;						
				}    				
				
				
				for(var c=0; c<3; c++)
				{
					var col = posInColumn(e.clientX,e.clientY,c);
					if (col)
					{
						dragSiblingState = c;
						dragOldState = c;
						break;
					}
				}
				
				dragToState=dragSiblingState;    		
			}
		}
		

		
	}
	
	
	function clearColumnBorders()
	{
		for(var i=0; i<4; i++)
		{
			var col = document.getElementById("intro"+i);
			if (col)
				col.style.borderColor = "#ddd";
		}
	}
	
	
	
	function getAbsX(div)
	{
		var obj = div;
		var left = 0;
		do { left += obj.offsetLeft; } 
		while (obj = obj.offsetParent);		
		return left;
	}
	function getAbsY(div)
	{
		var obj = div;
		var top = 0;
		do { top += obj.offsetTop; } 
		while (obj = obj.offsetParent);
		return top;
	}
	
	function insideDiv(x,y,div)
	{
		var divX = getAbsX(div);
		var divY = getAbsY(div);
		
		return (x >= divX && y >= divY && x <= (divX+div.offsetWidth) && y <= (divY+div.offsetHeight));
	}
		
	
	function findDrop(x,y,top,childTag)
	{	
		if (top.tagName == childTag)
		{
			if (insideDiv(x,y,top))
				return top;
		}

		var elem = top.firstChild;
			
		while (elem)
		{
			var r = findDrop(x,y,elem,childTag);
			if (r)
				return r;
			elem = elem.nextSibling;
			
		}
			
		return null;		
	}	



	function numMessagesInColumn(column)
	{
		var cnt=0;
		var msg = column.firstChild;
		while (msg)
		{
			if (msg.id && msg.id.substr(0,3)=="msg")
				cnt++;
			msg = msg.nextSibling;
		}
		return cnt;	
	}


	function onMouseMove(e)
	{
		if (!dragDIV)
			return;

	    if (e == null) 
		    var e = window.event; 

		dragRot = (e.clientX - dragStartX) / 200;
		if (dragRot > 1) dragRot = 1;
		else if (dragRot < -1) dragRot = -1;
			
		dragInTopMenu = e.clientY < 40;
		//dragScale = dragInTopMenu ? 0.5 : 1;
		dragScale = 1.0;
		
		setRotationTranslateScale(dragDIV,dragRot*dragRotMax, e.clientX-dragStartX, e.clientY-dragStartY,dragScale);
		
		checkDrag(e);
	}

	function checkDrag(e)
	{
	   if (e == null) 
    	   var e = window.event; 	

		var scrollX = document.body.scrollLeft+document.documentElement.scrollLeft;
		var scrollY = document.body.scrollTop+document.documentElement.scrollTop;

		var mouseX = e.clientX + scrollX;
		var mouseY = e.clientY + scrollY;
		
		checkDragTo(mouseX,mouseY);
	}
	
	function checkDragTo(mouseX,mouseY)
	{

		

		clearColumnBorders();
		dragToState = -1;
		
						
		dragTarget.style.visibility = "hidden";
		
		if (dragInTopMenu)
		{
			// top bar is fixed so subtract scroll x/y 
			var drop = findDrop(mouseX-scrollX,mouseY-scrollY,document.getElementById("finder"),"A");
		
			if (drop)
			{
				var dropID = getMsgID(drop);
								
				
				if (dropID >= 0 && dropID != parent)
				{
					dragTarget.style.visibility = "visible";														
								
					dragTarget.style.left = getAbsX(drop)+"px";
					dragTarget.style.top = (getAbsY(drop)+scrollY)+"px";
					
					//alert(e.pageY);
					
					
					dragTarget.style.width = (drop.offsetWidth-6)+"px";
					dragTarget.style.height = (drop.offsetHeight-6)+"px";
					
					dragTarget.style.zIndex = 999;
					
					drop.backgroundColor = "#f00";
	
					dragInto = drop;
					dragIntoFixedPos = true;
					
				}
			}
			return;
		}
	
		
		dragBefore = dragAfter = dragInto = null;
		
		if (numCols==3)
		{
			for(var i=0; i<3; i++)
			{
				var col = posInColumn(mouseX,mouseY,i);
				if (col)
				{
					if (i!=dragSiblingState)
						col.style.borderColor = "#f80";
					dragToState = i;
				}
			}			
		}else{
			dragToState = dragSiblingState = 3; 	
		}

		
		if (dragToState >= 0)
		{			
		
			var content = document.getElementById("content");	
			var column = findChild(content,"state"+dragToState);
	
	
			if (dragSiblingState!=dragToState)
			{

				dragCanInsert = null;

				if (dragToNewState!=dragToState)
				{				
					if (dragTimeout)
						clearTimeout(dragTimeout);
				
				
					dragToNewState = dragToState;
					
					dragTarget.style.visibility = "hidden";
					
					dragLastMessage = null;										
						
					if (numMessagesInColumn(column) && !isFlat)
					{		
						var func = function() {dragSiblingState=dragToState; dragToState=-1; checkDragTo(mouseX,mouseY);}
						dragTimeout = setTimeout(func,1000);
					}				
				}
			}else if (!isFlat)
			{
			
				if (column.firstChild)
				{
					var after=false;
				
					var asChild=false;
					var msg = column.firstChild;
					while (msg)
					{
						if (msg.id.substr(0,3)=="msg")
						{
							var ty = getAbsY(msg);
							var cy = ty + msg.offsetHeight/2;
							var by = ty + msg.offsetHeight;
							
							
							//var dy = getAbsY(dragDIV) + (e.clientY-dragStartY) + dragDIV.offsetHeight/2;
							var dy = mouseY;
									
							if (dy > ty && dy < by)
							{
								asChild = dragCanInsert==msg && Math.abs(dy-cy) < ((msg.offsetHeight/2));		
																
								after = !asChild && dy > cy;
								
								/*
								if ((after && msg.nextSibling && msg.nextSibling.id.substr(0,3)!="msg")
									   || (!asChild && !after && msg.previousSibling && msg.previousSibling && msg.previousSibling.id.substr(0,3)!="msg") )	
									msg = null;
								*/
								
								break;						
							}				
						}
	
						msg = msg.nextSibling;
					}
													
						
						
					if (!asChild)
						dragCanInsert = null;
						
					if (msg)
					{		
				
						if (msg!=dragLastMessage)
						{
							dragLastMessage=msg;
							
							if (dragTimeout)
								clearTimeout(dragTimeout);
					

							var func = function() {
								if (dragSiblingState==dragToState)
								{
									dragCanInsert = msg; 
									checkDragTo(mouseX,mouseY);
								} 
							}
							dragTimeout = setTimeout(func,1500);
						}			
						
						
						
						dragTarget.style.visibility = "visible";							
						
						
						if (!after)
						{							
							dragTarget.style.left = getAbsX(msg)+"px";
							dragTarget.style.top = getAbsY(msg)+"px";
						}else
						{
							dragTarget.style.left = getAbsX(msg)+"px";
							dragTarget.style.top = (getAbsY(msg)+msg.offsetHeight)+"px";							
						}
						
						if (asChild)
							dragTarget.style.height = (msg.offsetHeight-6)+"px";
						else
							dragTarget.style.height = "4px";
						
						dragTarget.style.width = (msg.offsetWidth-6)+"px";
							
						dragTarget.style.zIndex = 9;		// under top menu but above message
							
						dragIntoFixedPos = false;
							
						if (asChild)
							dragInto = msg;	
						else if (after)
							dragAfter = msg;
						else 
							dragBefore = msg;
								
							
							
							
					}else
					{
						if (dragTimeout)
							clearTimeout(dragTimeout);
						dragCanInsert = null;
						dragLastMessage = null;
						dragTarget.style.visibility = "hidden";
					}
						
				}
				
				
			}
		}
	}
	
	function getMsgID(msg)
	{
		if (msg.id.substr(0,3)=="msg")
			return msg.id.substr(3);
		else if (msg.id.substr(0,6)=="parent")
			return msg.id.substr(6);
		else
			return -1;
	}
	
	function isMobile()
	{
		return navigator.appVersion.indexOf("Mobile") > -1;
	}
	
	function onMouseUp(e)
	{
		if (dragTimeout)
			clearTimeout(dragTimeout);
	
		
		if (isMobile())
			document.removeEventListener("touchmove",touchMove, false);
		else
			document.onmousemove = null;


		if (dragDIV)
		{
			clearColumnBorders();
			dragBusy = true;
			
			
			if (!dragBefore && !dragAfter && !dragInto && (dragSiblingState==dragToState || dragToState==-1))
			{
				if (dragTarget)
					dragTarget.style.visibility = "hidden";

				returnDragMessage(0,dragDIV);
				dragDIV=null;
				return;
			}
			
				
			var req = getXMLHttpRequest(); 
			req.div = dragDIV;
			req.onreadystatechange = function()
			{ 		
				if(req.readyState == 4)
				{
					if(req.status == 200)
					{
						if (req.responseText == "OK")
						{
						
							dragFromX = dragPosX;
							dragFromY = dragPosY;								

							if (dragInto)
							{
								moveDragIntoMessage(0,req.div, dragInto);		
			
							}else
							{
								dragDummy = document.createElement('div');
								dragDummy.style.width = (req.div.offsetWidth-5)+"px";
								dragDummy.style.height = "0px";
								dragDummy.style.backgroundColor = "#fff";
								dragDummy.style.margin = "0px";

								var offsetY=0;
								
								var content = document.getElementById("content");	
								var column = findChild(content,"state"+dragToState);
								
								if (dragSiblingState!=dragToState)
								{
									column.insertBefore(dragDummy,column.firstChild);
								}else
								{																	
									if (dragBefore)
										column.insertBefore(dragDummy,dragBefore);
									else if (dragAfter)
										column.insertBefore(dragDummy,dragAfter.nextSibling);
									
									if (dragPlaceholder.parentNode == column)
										if (getAbsY(dragDummy)>getAbsY(dragPlaceholder))
											offsetY = -req.div.offsetHeight;
								}
							
								moveDragMessage(0,req.div,getAbsX(dragDummy),getAbsY(dragDummy)+offsetY);		
							}
							
						}else
						{
							if (dragTarget)
								dragTarget.style.visibility = "hidden";

							returnDragMessage(0,req.div);
						}							
					}
					
				}
				
			};
			

			dragTarget.parentNode.removeChild(dragTarget);

				
			dragDIV = null;
			
			var args = "";
			args += "?id="+dragID;
			args += "&parent="+parent;
			args += "&uid="+uniqueID;

			if (dragInto)
				args += "&state="+dragOldState;
			else
				args += "&state="+dragToState;
				
			if (dragBefore)
				args += "&before="+getMsgID(dragBefore);
			if (dragAfter)
				args += "&after="+getMsgID(dragAfter);
			if (dragInto)
				args += "&into="+getMsgID(dragInto);

						
			 								
			 	
			req.open("GET", "/php/movemsg.php"+args);
			
			req.send(null);				
							
			
			
			
			
		}else{
				
			if (dragID && !dragDragging)
				document.location = "/?p="+dragID+(isFlat?"&f=1":"")+(numCols!=3?"&c=1":"");			
		}
	}
	
	
	

	function moveDragIntoMessage(time,div,tdiv)
	{
		var col=document.getElementById("state"+dragToState);
			
		if (time < 1.0)
		{
			var stime = (Math.cos((1-time)*Math.PI)+1)/2;
			var stime2 = (Math.cos((1-((time-0.5)*2))*Math.PI)+1)/2;
				
						
			if (time > 0.5 && dragPlaceholder)
				dragPlaceholder.style.height = (div.offsetHeight*(1-stime2))+"px";
						
		
			var left = getAbsX(dragInto) + dragInto.offsetWidth/2;
			var top = getAbsY(dragInto) + dragInto.offsetHeight/2; 
			
			var x = left - div.offsetWidth/2;
			var y = top - div.offsetHeight/2;
			
			
			if (dragIntoFixedPos)			
				y += document.body.scrollTop+document.documentElement.scrollTop;
		
			
			
			var rx = dragFromX + ((x - (dragFromX + dragOriginX)) * stime);
			var ry = dragFromY + ((y - (dragFromY + dragOriginY)) * stime);
			
			setRotationTranslateScale(div,dragRot*dragRotMax*(1-stime), rx,ry,dragScale*(1-stime));
			
			div.style.opacity = dragAlpha*(1-stime);			
			
			time += animSpeed;
			
			var func = function() {moveDragIntoMessage(time,div,tdiv);}
			setTimeout(func,animDelay);
			
		}else
		{
			getEditedMessage(getMsgID(dragInto));
			
			if (dragPlaceholder)
				dragPlaceholder.parentNode.removeChild(dragPlaceholder);
			
			document.body.removeChild(div);	
			
			dragBusy = false;
			
			searchTotal[dragSiblingState]--;
			updateBars();
			
			

		}
	
	}
	
		
	function moveDragMessage(time,div,left,top)
	{
		var col=document.getElementById("state"+dragToState);
			
		if (time < 1.0)
		{
			var stime = (Math.cos((1-time)*Math.PI)+1)/2;
			
			
			var rx = dragFromX + ((left - (dragFromX + dragOriginX)) * stime);
			var ry = dragFromY + ((top - (dragFromY + dragOriginY)) * stime);
						
			setRotationTranslateScale(div,dragRot*dragRotMax*(1-stime), rx,ry,1);
			div.style.opacity = dragAlpha+(stime*(1-dragAlpha));			
			
			dragDummy.style.height = (div.offsetHeight*stime)+"px";
			
			if (dragPlaceholder)
				dragPlaceholder.style.height = (div.offsetHeight*(1-stime))+"px";
			
			time += animSpeed;
			
			var func = function() {moveDragMessage(time,div,left,top);}
			setTimeout(func,animDelay);
			
		}else
		{
			dragDummy.parentNode.insertBefore(div.firstChild,dragDummy);
			dragDummy.parentNode.removeChild(dragDummy);
			
			if (dragPlaceholder)
			{
				dragPlaceholder.parentNode.removeChild(dragPlaceholder);
				dragPlaceholder = null;
			}
				
			document.body.removeChild(div);	
			
			dragBusy = false;
			
			searchTotal[dragToState]++;
			searchTotal[dragSiblingState]--;
			updateBars();

		}
	
	}
	
	function returnDragMessage(time,div)
	{
	
		if (time < 1.0)
		{
			setRotationTranslateScale(div,dragRot*dragRotMax*(1-time), dragPosX * (1-time), dragPosY * (1-time),1);			
			div.style.opacity = dragAlpha+(time*(1-dragAlpha));			
						
			time += animSpeed;
			var func = function() {returnDragMessage(time,div);}
			setTimeout(func,animDelay);
			
		}else
		{
			if (dragPlaceholder)
			{
				dragPlaceholder.parentNode.replaceChild(div.firstChild,dragPlaceholder);
				dragPlaceholder = null;
			}					
			
			document.body.removeChild(div);	
									
			dragBusy = false;			
		}
	

	}
		
		
	
	
	function findChild(elem,id)
	{
		if (elem)
		{	
			if (elem.id == id)
				return elem;
				
			for(var i=0; i<elem.childNodes.length; i++)
			{
				var r = findChild(elem.childNodes[i],id);
				if (r)
					return r;
			}
		}
		return null;
	}
	function findChildDiv(elem,div)
	{
		if (elem)
		{	
			if (elem == div)
				return elem;
				
			for(var i=0; i<elem.childNodes.length; i++)
			{
				var r = findChildDiv(elem.childNodes[i],div);
				if (r)
					return r;
			}
		}
		return null;
	}
	

	function removeChildren(cell)
	{
		if ( cell.hasChildNodes() )
		{
		    while ( cell.childNodes.length >= 1 )
	   	 	{
	    	    cell.removeChild( cell.firstChild );       
	    	}
	    } 
	}
	
	function findMessageDiv(id)
	{
		var content = document.getElementById("content");	
	
		return findChild(content,"msg"+id)
	}

	function findCommentDiv(id)
	{
		var content = document.getElementById("content");	
	
		return findChild(content,"c"+id)
	}



	
	function getquerystring(formname) {
	
	    var form = document.forms[formname];
	
		var qstr = "";
	
	
	
	    function GetElemValue(name, value) {
	
	        qstr += (qstr.length > 0 ? "&" : "") + encodeURIComponent(name) + "=" + encodeURIComponent(value);
				
	    }
	
		
	
		var elemArray = form.elements;
	
	
	    for (var i = 0; i < elemArray.length; i++) {
	
	        var element = elemArray[i];
	
	        var elemType = element.type.toUpperCase();
	
	        var elemName = element.name;
	
	        if (elemName) {
	
	            if (elemType == "TEXT"
	
	                    || elemType == "TEXTAREA"
	
	                    || elemType == "PASSWORD"
	
						|| elemType == "BUTTON"
	
						|| elemType == "RESET"
	
						|| elemType == "SUBMIT"
	
						|| elemType == "FILE"
	
						|| elemType == "IMAGE"
	
	                    || elemType == "HIDDEN")
	
	                GetElemValue(elemName, element.value);
	
	            else if (elemType == "CHECKBOX" && element.checked)
	
	                GetElemValue(elemName, 
	
	                    element.value ? element.value : "On");
	
	            else if (elemType == "RADIO" && element.checked)
	
	                GetElemValue(elemName, element.value);
	
	            else if (elemType.indexOf("SELECT") != -1)
	
	                for (var j = 0; j < element.options.length; j++) {
	
	                    var option = element.options[j];
	
	                    if (option.selected)
	
	                        GetElemValue(elemName,
	
	                            option.value ? option.value : option.text);
	
	                }
	
	        }
	
	    }
	
	    return qstr;
	
	}

	function plural(val,str)
	{
		return val==1 ? str : str+"s";
		
	}
	
	function timeDiff(time)
	{
		var now = new Date;
		var secs = Math.round((now.getTime()/1000) - time);

	
		var mins = Math.round(secs/60);
		var hours = Math.round(mins/60);
		var days = Math.round(hours/24);
	
		if (secs < 60)
			return "<span class='new'>just now</span>";
		if (mins < 60)
			return mins+" "+plural(mins,"min")+" ago";
		if (hours < 24)
			return hours+" "+plural(hours,"hr")+" ago";
		
		var months=new Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
		
		var then = new Date;
		then.setTime(time*1000);
	
		if (then.getFullYear() == now.getFullYear())
			return then.getDate()+" "+months[then.getMonth()];
		else
			return then.getDate()+" "+months[then.getMonth()]+" "+then.getFullYear();
		
	}

	function checkTimeago()
	{
		var elems = document.getElementsByTagName("span");
		for (var i=0; i<elems.length; i++) 
		{
			if (elems[i].id.indexOf("timeago-") == 0)
		  	{
		  		var time = elems[i].id.substr(8);
		  
			  	elems[i].innerHTML = timeDiff(time);
			  	
		  	}
		}
		
		var func = function() {checkTimeago();}
		setTimeout(func,30*1000);
	

	}

	function shrinkDivAndRemove(div,height,time)
	{	
		if (time >= 1)
		{
			var parent = div.parentNode;
			if(parent)
				parent.removeChild(div);
		}else
		{		
			time += 0.05;
		
			var t = time*2;
			if (t < 1) 
				div.style.opacity = div.style.opacity * (1-t);			
			else
			{
				t -= 1;				
				div.style.height = (height*(1-t)) + "px";
			}
			div.style.filter = 'alpha(opacity = '+(div.style.opacity*100)+')';

			var func = function() {shrinkDivAndRemove(div,height,time);}
			setTimeout(func,animDelay);
		}
	}

	
	

	function bounceDiv(msgid,msg,ndiv,next,state,vx,vy,time,gravity,frames)
	{
		
		var col=document.getElementById("state"+state);
		
		
		if (time>=1)
		{
		
			if (col.firstChild)
				col.firstChild.style.marginTop = "0px";
			
			//var icons = findChild(msg,"icons");		
			//removeChildren(icons);
	
			
			//msg.onmouseover = function() {hilightMessage(msgid,state);};
	
			if (col.firstChild)
				col.insertBefore(msg,col.firstChild);
			else
				col.appendChild(msg);
			document.body.removeChild(ndiv);			
		
			if (next)
				next.style.marginTop = "0px";
				
				
			
		}else
		{	
			time+=(1/frames);
			
			var func = function() {bounceDiv(msgid,msg,ndiv,next,state,vx,vy,time,gravity,frames);}
			setTimeout(func,animDelay);
		
			ndiv.style.top = (ndiv.offsetTop+vy)+"px";
			ndiv.style.left = (ndiv.offsetLeft+vx)+"px";
	
			if (next)
				next.style.marginTop = (msg.offsetHeight*(1-(time)))+"px";

			var t = 0;
			if (time > 0.5)
				t = (time-0.5)*2;
				
			if (col.firstChild)
				col.firstChild.style.marginTop = (msg.offsetHeight*t)+"px";


			vy += gravity;
		}
	}

	function shrinkDivAndMove(msgid,div,oldHeight,height,state)
	{	
//		if (div.clientHeight <= 10)
		//if (div.style.opacity<0.1)
		if (1)
		{
			div.parentElement.removeChild(div);
			
			
			var col=document.getElementById("state"+state);
			
			//div.style.height = oldHeight+"px";
			//div.style.filter = 'alpha(opacity = '+(100)+')';
			//div.style.opacity = 1.0;


			var icons = findChild(div,"icons");		
			removeChildren(icons);
	//		icons.innerHTML = null;		
//			findChild(div,"icons").style.visibility = "visible";		
			
			
			col.insertBefore(div,col.firstChild);
			//div.onmouseover = function() {hilightMessage(msgid,state);};
			//div.onmouseout = function() {unhilightMessage(msgid);};
			
			//div.onMouseEnter = function() {alert("on");};

			
		}else
		{		
		
			div.style.opacity = div.style.opacity - 0.05;			
			
			if (div.style.opacity < 0.2)
			{
				height -= 10;
				
				if (div.style.opacity < 0.0)
					div.style.opacity = 0.0;
					
				//div.style.height = (height) + "px";
			}				


			div.style.filter = 'alpha(opacity = '+(div.style.opacity*100)+')';

			var func = function() {shrinkDivAndMove(msgid,div,oldHeight,height,state);}
			setTimeout(func,animDelay);
		}
	}
	
	function undoDelete(id)
	{
		if (deleteMsgID!=0 && deleteMsgID!=id)
		{
			var msgOld = findMessageDiv(deleteMsgID);
			var col1 = findChild(msgOld,"col1");
			var divOld = findChild(col1,"options");

			findChild(divOld,"delete").childNodes[0].innerHTML = "delete";
			deleteMsgID = 0;
		}
	}


	function getNewMessage(id,state)
	{
		var req = getXMLHttpRequest(); 

		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					if (req.responseText)
					{
						var col;
						if (numCols==3)
							col = document.getElementById("state"+state);
						else
							col = document.getElementById("state3");
						
						col.innerHTML = req.responseText+col.innerHTML;
						
						searchTotal[state]++;
						updateBars();
						
					}					
				}
			}
		}

		req.open("GET", "/php/getmsg.php?id="+id+"&parent="+parent+"&f="+isFlat+"&c="+numCols, true); 
		req.send(null);				
	}	





	function getNewMessageBefore(id,before)
	{
		var req = getXMLHttpRequest(); 

		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					if (req.responseText)
					{
						var newDiv = document.createElement('DIV');
						newDiv.innerHTML = req.responseText;
						
						var bmsg = findMessageDiv(before);
						
						if (bmsg)
							bmsg.parentNode.insertBefore(newDiv.firstChild,bmsg)
					}					
				}
			}
		}

		req.open("GET", "/php/getmsg.php?id="+id+"&parent="+parent+"&f="+isFlat+"&c="+numCols, true); 
		req.send(null);				
	}	


	function getNewMessageAfter(id,after)
	{
		
		var req = getXMLHttpRequest(); 

		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					if (req.responseText)
					{
						var newDiv = document.createElement('DIV');
						newDiv.innerHTML = req.responseText;
						
						var amsg = findMessageDiv(after);
						
						if (amsg)
						{
							if (amsg.nextSibling)
								amsg.parentNode.insertBefore(newDiv.firstChild,amsg.nextSibling);
							else
								amsg.parentNode.appendChild(newDiv.firstChild);
						}
					}					
				}
			}
		}

		req.open("GET", "/php/getmsg.php?id="+id+"&parent="+parent+"&f="+isFlat+"&c="+numCols, true); 
		req.send(null);				
	}	







	function getEditedMessage(id)
	{
	
		if (commentsID==id)
			return;
	
		var msg = findMessageDiv(id);
		
		if (!msg)
			return;
			
			
	
		var req = getXMLHttpRequest(); 


		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					if (req.responseText)
					{
						var ndiv = document.createElement('div');
						ndiv.innerHTML = req.responseText;
						msg.parentNode.replaceChild(ndiv.firstChild,msg);
					}
					
				}
				
			}
		}

		req.open("GET", "/php/getmsg.php?id="+id+"&parent="+parent+"&f="+isFlat+"&c="+numCols, true); 
		req.send(null);				
	}	



	
	
	var historyReq;
	var historyTimeout;
	
	function checkHistory()
	{
	//	if (userid==1)
	//		alert("in :"+historyID);
	
		if (historyTimeout)
			clearTimeout(historyTimeout);

		if (historyReq)
		{
			historyReq.abort();
			historyReq = null;
		}
	
		var func = function() {checkHistory();}
		historyTimeout = setTimeout(func,5*1000);

/*
		if (dragBusy)
			return;

		if (searchReq)
			return;
*/
	
		historyReq = getXMLHttpRequest(); 


		historyReq.onreadystatechange = function()
		{ 		
			if(historyReq.readyState == 4)
			{
				if(historyReq.status == 200)
				{
					//if (userid==1)
					//	alert(historyReq.responseText);
										
					if (historyReq.responseText)
					{
					
						var values = historyReq.responseText.split(" ");
						
						if (values[0] == "OK")
						{							
							historyID = values[1];							
								
							switch(Number(values[2]))
							{
								case Action.NONE:
									break;						
									
								case Action.MOVEBEFOREMSG:
									animateDeleteMessage(values[3]);	
									getNewMessageBefore(values[3],values[4]);
									break;		
								case Action.MOVEAFTERMSG:
									animateDeleteMessage(values[3]);	
									getNewMessageAfter(values[3],values[4]);
									break;		
								case Action.MOVETOPMSG:
									if (numCols==3)
									{
										animateDeleteMessage(values[3]);	
										getNewMessage(values[3],values[4]);
									}else
									{
										getEditedMessage(values[3]);
									}
									break;		
								case Action.MOVEINTOMSG:
									animateDeleteMessage(values[3]);	
									getEditedMessage(values[4]);
									break;		
								case Action.DELETEMSG:
									animateDeleteMessage(values[3]);	
									break;		
								case Action.NEWMSG:
									if (searchTerm=="")
										getNewMessage(values[3],values[4]);
									break;		
								case Action.EDITMSG:
									getEditedMessage(values[3]);
									break;					
							}
						}
						//else
						//	alert(values[0]);	
					}
					
				}
				historyReq = 0;
				
			}
		}

		historyReq.open("GET", "/php/history.php?f="+historyID+"&p="+parent+"&u="+uniqueID, true); 
		historyReq.send(null);				
	}	
	
	
	function animateDeleteMessage(id)
	{
	
		var msg = findMessageDiv(id);
		
		if (!msg)
			return;


		for(var state=0; state<3; state++)
		{
			var col = document.getElementById("state"+state);
			if (findChild(col,"msg"+id))
			{
				searchTotal[state]--;
				updateBars();
				break;
			}
		}

		
		msg.style.opacity = 1.0;
		shrinkDivAndRemove(msg,msg.clientHeight,0);	
		
		
		msg.onmouseover = null;						
		msg.onmouseout = null;						
	}
		
	function deleteMessage(id)	
	{
		var req = getXMLHttpRequest(); 

		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					if (req.responseText == "OK")
					{
						//animateDeleteMessage(id);	
						checkHistory();
					}
				}
			}
		}

		req.open("GET", "/php/deletemsg.php?id="+id+"&parent="+parent, true); 
		req.send(null);				
		
		
	}	
		
	function quitMessage(id)	
	{
		var req = getXMLHttpRequest(); 
		
		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					if (req.responseText == "OK")
					{
						animateDeleteMessage(id);	
						
						//checkHistory();
					}
				}
			}
		}

		req.open("GET", "/php/quitmsg.php?id="+id+"&parent="+parent, true); 
		req.send(null);				
	}	
	
	/*
	function getNextSibling(elem)
	{
		do {
			elem = elem.nextElementSibling;
		}while(elem && elem.nodeType!='DIV');
		
		return elem;
	}
	function getPrevSibling(elem)
	{
		do {
			elem = elem.previousSibling;
		}while(elem && elem.nodeType!='DIV');
		
		return elem;
	}
	*/
	
	function swapDivs(ndiv,msg1,msg2,move,time)
	{
		var speed = 0.1;
	
		if (time < 1)
		{
			time += speed;

			
			if (move < 0)
			{
				msg2.style.marginTop = (msg1.offsetHeight*time)+"px";
				msg2.style.marginBottom = (msg1.offsetHeight*(1-time))+"px";

			}else
			{
				msg2.style.marginBottom = (msg1.offsetHeight*(time))+"px";
				msg2.style.marginTop = (msg1.offsetHeight*(1-time))+"px";
			}
			
			ndiv.style.marginLeft = ((Math.sin(time*Math.PI) *50)+10) +"px";
			
			var ny;
			if (move < 0)
				ny = -msg2.offsetHeight*speed;
			else
				ny = msg2.offsetHeight*speed;
			
			ndiv.style.top = ndiv.offsetTop + (ny)+"px";
		
			var func = function() {swapDivs(ndiv,msg1,msg2,move,time);}
			setTimeout(func,animDelay);
		
		}else
		{
		
			var parent = msg2.parentNode;
		
			
			if (move < 0)
				parent.insertBefore(msg1,msg2)
			else
				parent.insertBefore(msg1,msg2.nextSibling);
			

			msg2.style.marginTop = "0px";
			msg2.style.marginBottom = "0px";

			document.body.removeChild(ndiv);			
			

		}

							
	}

	function markMessage(id,mark)	
	{

		
		

		var req = getXMLHttpRequest(); 
		
		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					var msg = findMessageDiv(id);	
				
					if (req.responseText=="1")
					{
						var star = findChild(msg,"star");
						star.id = "staron";
						star.innerHTML = '<a href="javascript:markMessage('+id+',0)"><img src="/images/red-star.png"></a>';
					}else if (req.responseText=="0")
					{
						var star = findChild(msg,"staron");
						star.id = "star";
			
						star.innerHTML = '<a href="javascript:markMessage('+id+',1)"><img src="/images/star.png"></a>';
					}	
		
				}
			}
		}

		req.open("GET", "/php/markmsg.php?id="+id+"&mark="+mark+"&parent="+parent, true); 
		req.send(null);				
		
		
		
		
			
	}

	var commentsID;
	
	function cleartext(elem)
	{
		elem.value="";
		elem.style.color = "#000";
	}

	function sendComment(id)
	{
	
		var msg = findMessageDiv(id);

		var thread = findChild(msg,"thread");
		var comments = findChild(msg,"comments");


		var req = getXMLHttpRequest(); 
		
		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					if (req.responseText)
						thread.innerHTML += req.responseText;
					checkHistory();
				}
			}
		}

 	   	
		req.open("POST", "/php/addcomment.php?id="+id+"&parent="+parent+"&uid="+uniqueID, true); 
		req.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
		req.send(getquerystring("addcomment"+id));		
		
		var box = findChild(comments,"box");
		findChild(box,"text").value = "";
		
		
	}

	function deleteComment(id)
	{
		var comment = findCommentDiv(id);
		
		var req = getXMLHttpRequest(); 
		
		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					if (req.responseText=="OK")
					{
						var parent = comment.parentNode;
						parent.removeChild(comment);
						
						checkHistory();
					}
				}
			}
		}

 	   
		req.open("GET", "/php/delcomment.php?id="+id+"&parent="+parent, true); 
		req.send(null);		
		
	}

	function showAddComment(id,member)
	{
		if (commentsID)
			closeComments(commentsID,false);
		
		addCommentBox(id,member);	
		
		commentsID = id;

	}

	function showComments(id,member)
	{
		if (commentsID)
			closeComments(commentsID,false);
	
	
		var msg = findMessageDiv(id);

		var thread = findChild(msg,"thread");

		var req = getXMLHttpRequest(); 
		
		
		
		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{				
					if (req.responseText)
						thread.innerHTML = req.responseText;
					
					addCommentBox(id,member);	
				}
			}
		}

//		var comments = findChild(msg,"comments");
//		comments.style.visibility = "visible";
 	   
 	   
 	   
		req.open("GET", "/php/getcomments.php?id="+id+"&parent="+parent, true); 
		req.send(null);
		
		var botinfo = findChild(msg,"botinfo");
		botinfo.innerHTML = 'loading...';

		commentsID = id;

	}



	function closeComments(id,fetch)
	{
		commentsID = 0;

		if (fetch)
			getEditedMessage(id);

		/*
		var msg = findMessageDiv(id);
		var comments = findChild(msg,"comments");
		var thread = findChild(msg,"thread");


		var botinfo = findChild(msg,"botinfo");
		
		var num = thread.childNodes.length;
		
		if (num==0)
			botinfo.innerHTML = '<a href="javascript:showAddComment('+id+');">add comment ▼</a>'
		else
		{
			botinfo.innerHTML = '<span></span><a href="javascript:showComments('+id+');">'+num+' comment'+(num>1?'s':'')+' ▼</a>'	
			botinfo.style.visibility = "visible";
		}
		
		
		thread.innerHTML='';
		
		
		findChild(comments,"box").innerHTML = '';
		
		*/

	}


	function addCommentBox(id,member)	
	{
	
		var msg = findMessageDiv(id);
		var comments = findChild(msg,"comments");
		var box = findChild(comments,"box");
		
		var out = "";
		
		if (member)
		{
			out += '<form name="addcomment'+id+'"  method="post" onsubmit="sendComment('+id+');return false;" >';
	
			out += '<textarea id="text" name="text" style="width:90%;" ></textarea>';
			
			out += '<button class="button medium lgray" type="submit" onclick="closeComments('+id+',true);return false;"><i class="icon-remove"></i> Close</button>';
			out += '<button class="button medium blue" type="submit"><i class="icon-ok"></i> Add Comment</button>';
	
			out += '</form>';
			
			box.innerHTML = out;
			findChild(box,"text").focus();
		}
		
		
		var botinfo = findChild(msg,"botinfo");
		botinfo.innerHTML = '<a href="javascript:closeComments('+id+',true);">close ▲</a>';
		botinfo.style.visibility = "visible";
		
		

	}	

	
	function closePopup()
	{
		var popup = findChild(document.body,"popup");

		document.body.removeChild(popup);
		
		document.body.style.overflow = "auto";		

	}
	
	function doDeleteMessage(id)
	{
		closePopup();
		deleteMessage(id);
	}
	function doQuitMessage(id)
	{
		closePopup();
		quitMessage(id);
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
		

	function askDelete(id)
	{
		var dialog = makePopup();
		
		
		var out = "";
		
		
		out += '<div class="intro single">';
		out += '<div id="title">delete</div>';
		
		out += "<h1>Are you sure?</h1>";
		out += "<p>WARNING:</p>";
		out += "<p>This will delete all children too!</p>";
		out += '<br>';
		out += ' <button class="button large gray" onclick="closePopup();"><i class="icon-remove"></i> Cancel</button> ';
		out += ' <button class="button large red" onclick="doDeleteMessage('+id+');"><i class="icon-trash"></i> Delete Note</button>';
		out += '</div>';
		
		dialog.innerHTML = out;
		
		
	}




	function askQuit(id)
	{
		var dialog = makePopup();
		
		
		var out = "";
		
		
		out += '<div class="intro single">';
		out += '<div id="title">remove</div>';
		
		out += "<h1>Are you sure?</h1>";
		out += '<br>';
		out += ' <button class="button large gray" onclick="closePopup();"><i class="icon-remove"></i> Cancel</button> ';
		out += ' <button class="button large red" onclick="doQuitMessage('+id+');"><i class="icon-trash"></i> Remove Note</button>';
		out += '</div>';
		
		dialog.innerHTML = out;
		
		
	}


	function sendMessage(id)
	{

		var req = getXMLHttpRequest(); 
 	   
 	   	var status=findChild(document.forms.addmsg,"status");

		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					if (req.responseText == "OK")
					{
						closePopup();	
						
						checkHistory();
					}else
					{
						status.innerHTML = "Error";
						status.style.color = "#f00";
						document.forms.addmsg.Submit.style.visibility = "visible";
					}
				}
			}
		}
		
		
		status.innerHTML = "Sending…";
		status.style.color = "#4a4";
 	   
		req.open("POST", "/php/addmsg.php", true); 
		req.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
		req.send(getquerystring("addmsg"));		

		
		
	}
		
	
		
		
		
				
	function addMessage(state)
	{
		var dialog = makePopup();

		var req = getXMLHttpRequest(); 
		
		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					dialog.innerHTML = req.responseText;	
					
					checkPlaceholders();
					findChild(dialog,"note").focus();
				}
			}
		}
		
		
		selectedUserID = 0;
		req.open("GET", "/php/msgform.php?state="+state+"&parent="+parent+"&f="+isFlat+"&c="+numCols, true); 
		req.send(null);		
	}
		
		
	function editMessage(id)
	{
			
		var dialog = makePopup();

		var req = getXMLHttpRequest(); 
		
		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					dialog.innerHTML = req.responseText;	
				}
			}
		}

		selectedUserID = 0;
		req.open("GET", "/php/msgform.php?id="+id+"&parent="+parent+"&f="+isFlat+"&c="+numCols, true); 
		req.send(null);			
		
	}
	

	function addIcons(msg,id,state)
	{	

		var icons = findChild(msg,"icons");
		
		if (icons)
		{


			var edit = findChild(icons,"edit");
			if (edit)
			{
	
				if (!edit.firstChild)
				{		
					
					var out=""; 
					/*
					if (state==1 || state==2)
						out += '<a href="javascript:moveMessage('+id+','+state+',-1)"><img src="/images/reply.png"></a>';
					if (state==0 || state==1)
						out += '<a href="javascript:moveMessage('+id+','+state+',+1)"><img src="/images/action2.png"></a>';
					*/
					//out += '<a href="javascript:editMessage('+id+')"><img src="/images/pencil.png"></a>';
					//out += '<a href="javascript:askDelete('+id+')"><img src="/images/trash2.png"></a>';
					out += '<a href="/?p='+id+'"><img src="/images/plus.png"></a>';
					edit.innerHTML = out;		
					
				}
			}
	
	
	
			icons.style.visibility = "visible";

		}


	}
	
	var hiNum=0;
	
	function dragMessage(id)
	{
		var msg = findMessageDiv(id);
		
		
		if (!msg)
			return false;

		dragPlaceholder = document.createElement('div');
		dragPlaceholder.style.width = (1)+"px";
		dragPlaceholder.style.height = (msg.offsetHeight)+"px";
//		dragPlaceholder.style.backgroundColor = "#ddd";
		dragPlaceholder.style.margin = "0";
		dragPlaceholder.style.padding = "0";


		dragParent = msg.parentNode;
		

		dragTarget = document.createElement('div');
		dragTarget.style.width = (msg.offsetWidth-5)+"px";
		dragTarget.style.height = "4px";
		dragTarget.style.margin = "0px";
		
//		dragTarget.style = "float:left";		
		dragTarget.style.position = "absolute";
		
		dragTarget.className = "drop";
		dragTarget.style.visibility = "hidden";
		
		
		document.body.appendChild(dragTarget);


		
		

		//if (lastHilightMsgID)
		//	unhilightMessage(lastHilightMsgID);
					
		var ndiv = document.createElement('div');
		
		ndiv.style.position="absolute";
		
		var left=0,top=0;
		var obj = msg;
		do {
			top += obj.offsetTop;
			left += obj.offsetLeft; 		
		} while (obj = obj.offsetParent);

		
		ndiv.style.left = left+"px";
		ndiv.style.top = top+"px";
		ndiv.style.width = msg.offsetWidth+"px";
		ndiv.style.height = msg.offsetHeight+"px";
		
		ndiv.className = "message-container";
		
		document.body.appendChild(ndiv);
		
		
		dragParent.replaceChild(dragPlaceholder,msg);
				
		ndiv.appendChild(msg);
		
		dragDIV = ndiv;
		
		return ndiv;
	}
	
	function showChildren(id)
	{
		document.location = "/php/settop.php?id="+id;
	}
	
	function hilightMessage(id,state)
	{			
		
		if (lastHilightMsgID==id)
			return;
			
	//	undoDelete(id);

		
		if (lastHilightMsgID)
		{
			unhilightMessage(lastHilightMsgID);			
		}
		
		var msg = findMessageDiv(id);

//		msg.className = "message";

		//addIcons(msg,id,state);
		
		var order = findChild(msg,"order");
		if (order)
			order.style.visibility = "visible";		
	
		var star = findChild(msg,"star");
		if (star)
			star.style.visibility = "visible";		
	
		var botinfo = findChild(msg,"botinfo");		
		
		if (botinfo)
			botinfo.style.visibility="visible";

//		if (botinfo.childNodes.length<2)
//			botinfo.innerHTML = '<a href="javascript:showAddComment('+id+');">add comment</a>';

		
		
		lastHilightMsgID=id;				
					
	}
		
	function unhilightMessage(id)
	{
		var msg = findMessageDiv(id);

	
		var icons = findChild(msg,"icons");
		if (icons)
			icons.style.visibility = "hidden";			
	
		var order = findChild(msg,"order");
		if (order)
			order.style.visibility = "hidden";		

		var star = findChild(msg,"star");
		if (star)
			star.style.visibility = "hidden";		

		var botinfo = findChild(msg,"botinfo");
		if (botinfo)
		{

			if (botinfo.childNodes.length<2)
				botinfo.style.visibility="hidden";
		}		

		lastHilightMsgID=0;							
	}

	/*
	function focusEmail(elem)
	{
		if (elem.value == "email")
		{
			elem.style.color = "#000";
			elem.value = "";
		}
	}
	
	function focusPassword(elem)
	{
		elem.type = "password";
		if (elem.value == "password")
		{
			elem.style.color = "#000";
			elem.value = "";
		}
	}
	
	function blurEmail(elem)
	{
		if (elem.value == "")
		{
			elem.style.color = "#aaa";
			elem.value = "email";
		}
	}
	
	function blurPassword(elem)
	{
		if (elem.value == "")
		{
			elem.type = "text"; 
			elem.style.color = "#aaa";
			elem.value = "password";
		}
	}


	
	function searchFocus()
	{
	}
	
	
	function searchBlur()
	{
		if (document.search.text.value == "")
		{
			//document.search.text.value = "Search";
			searchFirstClick = true;
		}
	
	}
	
		*/

	function clearColumn(num)
	{
		var col = document.getElementById("state"+num);
		if (col)
			col.innerHTML = "";
	}


	function clearResults()
	{
		
		clearColumn(0);
		clearColumn(1);
		clearColumn(2);
		clearColumn(3);
			
		document.getElementById("status").innerHTML = "";				
		
		
		searchInput = false;
		


		for(var i=0; i<4; i++)
		{
			searchFrom[i] = 0;
			searchExhausted[i] = false;
			searchTotal[i] = 0;
		}

		if (searchReq)
		{
			searchReq.abort();
			searchReq = null;
		}
	}
		
	function searchKeypress(e)
	{
		var key = window.event ? e.keyCode : e.which;

		    
		if (key == 13)
		{	
			trySearch();
				
			return false;
		}else
		{
			return true;
		}
		
	}
	
	function trySearch()
	{
		if (document.search.text.value == searchTerm)
			return;
			
		clearResults();
		searchInput=true;
		doSearch();
			
		if (searchTerm)
			window.history.replaceState('Object', 'Title', '/?p='+parent+"&f="+isFlat+'&q='+searchTerm);
		else
			window.history.replaceState('Object', 'Title', '/?p='+parent+"&f="+isFlat);	

		if (searchTimeout)
		{
			clearTimeout(searchTimeout);
			searchTimeout = null;
		}

	}
	
	function searchChange()
	{
		var elem = document.search.text;
		
		elem.setAttribute("state",elem.value==""?0:1);	
		
		if (searchTimeout)
			clearTimeout(searchTimeout);
		
		searchTimeout = setTimeout("trySearch()",1000);
	}
	
		

	function getXMLHttpRequest() 
	{
		if (window.XMLHttpRequest) 
			return new window.XMLHttpRequest;
		else
			return new ActiveXObject("MSXML2.XMLHTTP.3.0");
	}
	
	function clearSearch()
	{
		searchTerm = "";
		document.search.text.value = "";
		searchChange();
		if (searchTimeout)
			clearTimeout(searchTimeout);
		window.history.replaceState('Object', 'Title', '/?p='+parent+"&f="+isFlat);	

	}
	
	function doSearch()
	{ 		
		if (searchReq)
			return; 
	
	
		
			
		if (searchInput)
			searchTerm = document.search.text.value;
	
	
		searchReq = getXMLHttpRequest(); 
		
		if (!searchReq)
			return;

		var status = document.getElementById("status");


		searchReq.onreadystatechange = function()
		{ 		

			if(searchReq.readyState == 4)
			{
				
				if(searchReq.status == 200)
				{				

					var newDiv = document.createElement('DIV');
					newDiv.innerHTML = searchReq.responseText;

					for(var i=0; i<newDiv.childNodes.length; i++)
					{
						var n = newDiv.childNodes[i];
						
						if (n.id.substr(0,5) == "state")
						{
							var state = Number(n.id.substr(5));
							
							searchFrom[state] += n.childNodes.length;

							var content = document.getElementById(n.id);
							
							while (n.childNodes.length)
								content.appendChild(n.childNodes[0]);
								
						}else if (n.id.substr(0,5) == "total")
						{
							var state = Number(n.id.substr(5));
							searchTotal[state] = Number(n.innerHTML);
							
							
						}				
						
					}
					

					if (numCols==3)
					{					
						for(var c=0; c<3; c++)
						{
							var state = document.getElementById("state"+c);
							if (state)
								searchExhausted[c]=state.childNodes.length >= searchTotal[c];
						}
					}else{
						var state = document.getElementById("state3");
						searchExhausted[3] = state.childNodes.length >= (searchTotal[3]);
					}
					
					
					
					
					
					searchReq = null;
					
					
					if (hilight)
					{
						var hmsg = findMessageDiv(hilight);
						if (hmsg)
						{
							hmsg.style.backgroundColor = "#ff8";
						}
					}
					
					if (hilightFrom)
					{
						for(var c=0; c<3; c++)
						{
							var state = document.getElementById("state"+c);
							if (state)
							{
								for(var i=0; i<state.childNodes.length; i++)
								{
									var n = state.childNodes[i];
									if (n.id.substr(0,3) == "msg")
		    						{
										var id = Number(n.id.substr(3));
										if (id > hilightFrom)
											n.style.backgroundColor = "#ff8";
		    						}
								}
							}
						}
					}
					
					
					if ((searchExhausted[0] && searchExhausted[1] && searchExhausted[2]) || searchExhausted[3]) 
						status.innerHTML = '';	
					
					
					updateBars();
					checkAllColumns();
					
					
						
				}else
				{
					status.innerHTML = '<img src="/images/warning.png">';	
				}	
				
				
				
				
			} 
		}; 
		
		var loading = "";
		loading += "<div id='lcircle' >";
		loading += "<div id='lcircle_1' class='lcircle'></div>";
		loading += "<div id='lcircle_2' class='lcircle'></div>";
		loading += "<div id='lcircle_3' class='lcircle'></div>";
		loading += "<div class='clearfix'></div>";
		loading += "</div>";
		
		
		
		status.innerHTML = loading;			
		
				
		var u = new Date().getTime();
		
		var from = "";
		
		if (numCols==3)
		{
			for(var c=0; c<3; c++)
				from += "&from"+c+"="+(searchExhausted[c]?-1:searchFrom[c]);		
		}else
		{
			from = "&from3="+(searchExhausted[3]?-1:searchFrom[3]);		
		}
		
			
		var uri = "/php/search.php?&q="+encodeURIComponent(searchTerm)+from+"&show="+searchShow+"&u="+u+"&p="+parent+"&f="+isFlat+"&c="+numCols+"&sm="+stateMask;
		
		
		searchReq.open("GET",uri, true); 
		searchReq.send(null);
		
	} 
	
	

	function checkColumnMore(col)
	{
		if (searchExhausted[col])
			return false;

		var column = document.getElementById("state"+col);	
		
		if (!column)
			return;
			
		var cbottom=column.offsetHeight;		
		while (column.offsetParent!==null) 
		{
            cbottom += column.offsetTop;
            column = column.offsetParent;
        }

		var winHeight = 0;
		if( typeof( window.innerWidth ) == 'number' ) 
			winHeight = window.innerHeight;
		else if( document.documentElement && ( document.documentElement.clientHeight ) )
			winHeight = document.documentElement.clientHeight;
		else if( document.body && ( document.body.clientHeight ) )
			winHeight = document.body.clientHeight;
			
		var winOffset = typeof(window.pageYOffset) == 'number' ? window.pageYOffset : document.documentElement.scrollTop;

		var wbottom = winHeight+winOffset;
        	
		//document.getElementById("status").innerHTML = wbottom+","+cbottom+","+winOffset;

		if (wbottom >= cbottom)
			return true;	
			
		return false;
	}
	
	
	
	
	function checkAllColumns()
	{
		for(var i=0; i<4; i++)
		{
			if (checkColumnMore(i))
			{			
				doSearch();
				break;
			}
		}
		
	}
	
	function checkScroll()
	{
		window.onscroll=window.onresize= checkAllColumns;
	}
	
	
	
	var selectedUserID=0;
	function selectUser(id)
	{
		if (!selectedUserID)
			selectedUserID = document.addmsg.assigned.value;			
	
		if (selectedUserID)
		{
			var sel = document.getElementById("member"+selectedUserID);
			if (sel)
				sel.className = "member spaced";
		}
		
			
		if (selectedUserID!=id)
		{
			var sel = document.getElementById("member"+id);	
			sel.className = "member spaced selected";		
		
			document.addmsg.assigned.value = id;	
			
			selectedUserID = id;
		}else{
			document.addmsg.assigned.value = 0;	
			selectedUserID = 0;
		}		
	}
		
	function removeMember(id)
	{
		var res = document.getElementById("member"+id);	
		
		if (res)
			res.parentNode.removeChild(res);
	
		var res = document.getElementById("result"+id);	
		
		if (res)
			findChild(res,"addbutton").style.visibility = "visible";
	
	
	}
	
	
	function addMember(id,ver,name,tag)
	{
		var members = document.getElementById("members");	
		
		if (members.childNodes.length >= 16)
		{
			alert("Sorry, too many members");
			return;
		}

		var av = '<div id="member'+tag+'" class="member spaced" >';
		av += '<a href="javascript:removeMember(\''+tag+'\');"><i class="icon-remove"></i></a>';
		av += '<p>'+name+'</p>';
		
		
		
		if (id)
		{
			av += '<div id="avatar'+id+'" class="avatar" onclick="selectUser('+id+')"><img src="/php/image.php?avatar='+id+'&v='+ver+'"></div>';
			av += '<input type="hidden" name="member'+id+'" value="'+id+'">';					
		}else
		{
			av += '<div id="avatar'+id+'" class="avatar"><img src="/php/image.php?avatar=0&v=0"></div>';
			av += '<input type="hidden" name="invite'+tag+'" value="'+name+'">';					
		}
		av += '</div>';
		members.innerHTML += av;
	
	
		var res = document.getElementById("result"+tag);	
		
		if (res)
			findChild(res,"addbutton").style.visibility = "hidden";
	
	}
	
	
	
	var searchMemberTimeout;
	var searchMemberReq;
		
	function tryMemberSearch(q)
	{
		if (searchMemberTimeout)
			clearTimeout(searchMemberTimeout);
	
		var res = document.getElementById("results");	
		res.innerHTML = "";	
	
		if (q.length < 3)
			return;
			
		searchMemberTimeout = setTimeout("doMemberSearch('"+q+"')",250);
	}
	
	function doMemberSearch(q)
	{
	
		if (searchMemberReq)
			searchMemberReq.abort();
	
		searchMemberReq = getXMLHttpRequest(); 
		
		if (!searchMemberReq)
			return;
			
		var res = document.getElementById("results");	
		
	
				
		searchMemberReq.onreadystatechange = function()
		{ 		
			if(searchMemberReq.readyState == 4)
			{
				if(searchMemberReq.status == 200)
				{						
					var newDiv = document.createElement('DIV');
					newDiv.innerHTML = searchMemberReq.responseText;
				
				
					var sr = newDiv.firstChild;
					while (sr)
					{
						var next = sr.nextSibling;
						res.appendChild(sr);
					
						if (hasMember(sr.id.substr(6)))
							findChild(sr,"addbutton").style.visibility = "hidden";
						
						sr = next;
					}			
					
				} 
			}; 
		}
	
	
		
		var uri = "/php/findusers.php?&q="+encodeURIComponent(q);
				
		searchMemberReq.open("GET",uri, true); 
		searchMemberReq.send(null);
	
	}
	
	
	function ignoreEnter(e)
	{
		var key = window.event ? e.keyCode : e.which;
		if (key == 13)
			return false;
		
		
		return true;
	}
	
	function hasMember(id)
	{
		return document.getElementById("member"+id);	
	}
	
	
	function updateBars()
	{
		var bars = document.getElementById("bars");	
		
		if (bars)
		{
			if (searchTotal[0]+searchTotal[1]+searchTotal[2])
				bars.style.visibility = "visible";
					
			bars.innerHTML = writeBars(searchTotal[0],searchTotal[1],searchTotal[2],null);		
		}		
	}
	
	
	function writeBars(nump,numa,numd,url)
	{
	
		var height = 20;
		
		var norm = Math.max(nump,numa,Math.min(height,numd));
		if (norm==0)
			norm = 1;
				
		hp = Math.min(height,Math.round((nump*height)/norm));
		ha = Math.min(height,Math.round((numa*height)/norm));
		hd = Math.min(height,Math.round((numd*height)/norm));
	
	
		out = "";
		
		
		if (url)
			out += '	<div class="barc" onclick="location.href=\''+url+'\'">';
		else
			out += '	<div class="barc">';
		
		out += 			'<div style="height:'+hp+'px;" class="bar0"></div>';
		out += 			'<div style="height:'+ha+'px;" class="bar1"></div>';
		out += 			'<div style="height:'+hd+'px;" class="bar2"></div>';
		out += 		'<div class="info below">';	
		out +=				'<div class="pending"><b>'+nump+'</b> pending</div> ';
		out +=				'<div class="active"><b>'+numa+'</b> active</div> ';
		out +=				'<div class="done"><b>'+numd+'</b> done</div> ';
		out += 		'</div>'; 
		out += 		'</div>';
		
		
		return out;
	}
	

	function setTreeSwitch(div,on)
	{
		isFlat = on;
		div.childNodes[0].className = on?"":"active";
		div.childNodes[1].className = on?"active":"";
		
		url = "/?";
		
		if (parent)
			url += "p="+parent+"&";
		
		if (isFlat)
			url += "f=1&";
			
		if (numCols==1)
			url += "c=1&";
		
		if (searchTerm)
			url += "q="+encodeURIComponent(searchTerm)+"&";
		
		document.location = url;
		
	}

	function setColumnSwitch(div,col)
	{
		numCols = col;
		
		div.childNodes[0].className = col==3?"active":"";
		div.childNodes[1].className = col==1?"active":"";
		
		url = "/?";
		
		if (parent)
			url += "p="+parent+"&";
		
		if (isFlat)
			url += "f=1&";
		
		if (searchTerm)
			url += "q="+encodeURIComponent(searchTerm)+"&";
			
		if (numCols==1)
			url += "c=1&";
		
		document.location = url;
	}
	
	
	function setMessageState(id,state)
	{
						
		var req = getXMLHttpRequest(); 
		
		req.onreadystatechange = function()
		{ 		
			if(req.readyState == 4)
			{
				if(req.status == 200)
				{
					if (req.responseText == "OK")
					{		
						checkHistory();
					}

				}
			}
		}


		req.open("GET", "/php/movemsg.php?id="+id+"&state="+state+"&parent="+parent, true); 
		req.send(null);				

	
	}
	
	
	function closeFeedback()
	{		
		var fb = document.getElementById("feedback");	

		fb.parentNode.removeChild(fb);
		
		setCookie("nofeedback",1,365);
	}
	
	
	function openFeedback()
	{		
		var fb = document.getElementById("feedback");	
		fb.className = "opened";
		
		var question = findChild(fb,"question");
		
		question.innerHTML = "Please tell us ..";
		
		
		var text = document.createElement('textarea');		
		text.id = "text";		
		fb.appendChild(text);
		text.focus();
		
		var action = document.createElement('div');
		action.id = "action";
		

		var cancel = document.createElement('button');				
		cancel.innerHTML = "Cancel";
		cancel.className = "button large lgray";
		cancel.onclick = closeFeedback;
		action.appendChild(cancel);
		
		var send = document.createElement('button');				
		send.innerHTML = "Send";
		send.className = "button large blue";
		send.onclick = sendFeedback;
		action.appendChild(send);

		fb.appendChild(action);
	}
	
	
	function sendFeedback()
	{
		var fb = document.getElementById("feedback");	
		fb.className = "opened";

		var text = findChild(fb,"text");
		text.disabled = "disabled";
		
		var action = findChild(fb,"action");
		action.innerHTML = "Sending now.. Thank you!";

		setTimeout(closeFeedback,2000);


		var req = getXMLHttpRequest(); 

		req.open("POST", "/php/feedback.php", true); 
		req.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=utf-8");
		req.send("text="+encodeURIComponent(text.value));		
	
	}
		
		
	function setCookie(c_name,value,exdays)
	{
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
	}
	
	
	
	
	
	
	</script>
		
				
	
	<div id="content">
	
<?php
	if ($user || $parent)
	{
?>
	
	
	
		<?php
		if ($numCols==3)
		{
		?>	
		<div class="columns">
		<div class="column" >
			<div class="intro" id="intro0">
				<div id="title"><span class="pending">pending</span> 
					<div class="icons">
						<?php 
							if ($canPost)
								echo '<a href="javascript:addMessage(0);"><i class="icon-edit icon-large"></i></a>';
							else
								echo '<i class="icon-edit icon-large"></i>';
						?>		
					</div>
				</div>
				<div id="state0"></div>
			</div>
		</div>		
		<div class="column"  >
			<div class="intro" id="intro1">
				<div id="title"><span class="active">active</span> 
					<div class="icons">
						<?php 
							if ($canPost)
								echo '<a href="javascript:addMessage(1);"><i class="icon-edit icon-large"></i></a>';
							else
								echo '<i class="icon-edit icon-large"></i>';
						?>		
					</div>
				</div>
				<div id="state1"></div>
			</div>
		</div>
		<div class="column" >
			<div class="intro" id="intro2">
				<div id="title"><span class="done">done</span> 
					<div class="icons">
						<?php 
							if ($canPost)
								echo '<a href="javascript:addMessage(2);"><i class="icon-edit icon-large"></i></a>';
							else
								echo '<i class="icon-edit icon-large"></i>';
						?>		
					</div>
				</div>
				<div id="state2"></div>
			</div>
		</div>
		</div>

		<div class="status" id="status"></div>
		
	<?php
	}else if ($numCols==1)
	{
	?>
		<div class="columns single">
		<div class="column single" >
			<div class="intro" id="intro3">
				<div id="title" class="small">
				
				<?php				
				echo '<span>';
				echo '<span style="cursor:pointer" class="pending" onclick="if (searchReq) return; stateMask^=1; this.className = stateMask&1 ? \'pending\':\'\'; clearResults(); doSearch();">pending</span> • ';
				echo '<span style="cursor:pointer" class="active" onclick="if (searchReq) return; stateMask^=2; this.className = stateMask&2 ? \'active\':\'\'; clearResults(); doSearch();">active</span> • ';
				echo '<span style="cursor:pointer" class="done" onclick="if (searchReq) return; stateMask^=4; this.className = stateMask&4 ? \'done\':\'\'; clearResults(); doSearch();">done</span>';
				echo '</span>';
				?>
				
				    
				    
				    
					<div class="icons">
						<?php 
							if ($canPost)
								echo '<a href="javascript:addMessage(0);"><i class="icon-edit icon-large"></i></a>';
							else
								echo '<i class="icon-edit icon-large"></i>';
						?>		
					</div>
				</div>
				<div id="state3"></div>
			</div>
		</div>		
		</div>		
		<div class="status" id="status"></div>
	
	<?php
	}
	?>	
	
<?php
	}
?>

</div>

	


</body>

</html>