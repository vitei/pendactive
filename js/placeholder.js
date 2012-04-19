
function checkPlaceholders()
{
	var fakeInput = document.createElement("input");
	
	if ("placeholder" in fakeInput)
		return;


		function changeInputType(oldObject, oType) 
		{
		  var newObject = document.createElement('input');
		  newObject.type = oType;
		  if(oldObject.size) newObject.size = oldObject.size;
		  if(oldObject.value) newObject.value = oldObject.value;
		  if(oldObject.name) newObject.name = oldObject.name;
		  if(oldObject.id) newObject.id = oldObject.id;
		  if(oldObject.className) newObject.className = oldObject.className;
		  oldObject.parentNode.replaceChild(newObject,oldObject);
		  return newObject;
		}



	var forms = document.forms;
	

	for(var i=0; i<forms.length; i++)
	{
		var elements = forms[i].elements;
				
		for(var e=0; e<elements.length; e++)
		{
			var elem = elements[e];
	        if (elem.getAttribute("placeholder") != undefined)
	        {
		
	        	elem.value = elem.getAttribute("placeholder");
				if (elem.type != "submit")
				{
				
					if (elem.type == "password")
					{
						elem = changeInputType(elem,"text");
						elem.onfocus = function () 
							{
								this.value=""; 
								this.style.color="#000";
								var e = changeInputType(this,"password");
								var func = function() {e.focus();}
								setTimeout(func,1);
								return true;
							}
					}else
					{
						elem.onfocus = function () { this.value=""; this.style.color="#000";}
					}	
					elem.style.color = "#999";
											
				}
			}
		}	
	}
}

