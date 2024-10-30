var treexmlhttp;
	
	function loadTreeXMLDoc( url, cfunc )
	{
		if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  treexmlhttp = new XMLHttpRequest();
		  }
		else
		  {// code for IE6, IE5
		  treexmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		  }
		treexmlhttp.onreadystatechange = cfunc;
		treexmlhttp.open( "GET", url, true );
		treexmlhttp.send();
	}
	
	function ajaxTree( url, div, img )
	{
		loadTreeXMLDoc( url, function()
		  {
		  if (treexmlhttp.readyState == 4 && treexmlhttp.status == 200)
		    {
		    	//document.getElementById(div).innerHTML = treexmlhttp.responseText;
		    	document.getElementById(img).style.display = 'none';
		    	document.getElementById(div).style.display = 'block';
		    }
		  else
		  {
			  document.getElementById(div).style.display = 'none';
			  document.getElementById(img).style.display = 'block';			  
		  } 
		  });
	}

	function del( parent, children )
	{
		document.getElementById('parent').value = parent;
		document.getElementById('children').value = children;
		document.getElementById('action').value = 'delete';
		document.getElementById('form_custom_posts').submit();
	}