<html>
<head>
<style>
html{font-family: "arial", sans-serif;}
td{position: relative;padding:4px;}
a{padding:5px 8px; background-color: #525252; text-decoration: none; color:#fff; font-size: 0.8em;}
a:hover, a.hover{background-color: #828282;}

</style>
</head>

<body>
<?php
 $fjern_path = str_replace("/path", "", $_SERVER['PHP_SELF']);
 $path = $_SERVER['DOCUMENT_ROOT'].$fjern_path;
 $ny = dirname($path);
 echo '<table cellspacing="3">';
 echo '<td><a href="#" alt="'.$ny.'/">Kopier</a></td><td>'.$ny.'/</td><td><font color="red">Base path</font></td><tr>';
 
 $templates = $ny.'/system/expressionengine/templates/';
 echo '<td><a href="#" alt="'.$templates.'">Kopier</a></td><td>'.$templates.'</td><td> <font color="red">Template path</font></td><tr>';
 
 $minimee = $ny.'/assets/minimee';
 echo '<td><a href="#" alt="'.$minimee.'">Kopier</a></td><td>'.$minimee.'</td><td> <font color="red">Minimee Cache Path</font></td><tr>';
 
 $minimee_url = curPageURL().'/assets/minimee';
 echo '<td><a href="#" alt="'.$minimee_url.'">Kopier</a></td><td>'.$minimee_url.'</td><td> <font color="red">Minimee Cache URL</font></td><tr>';
 
 
  echo '</table>';
 
 function curPageURL() {
 	$pageURL = 'http';
 	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 	$pageURL .= "://";
 	$current_url = str_replace("www.", "", $_SERVER["SERVER_NAME"]);
  	$pageURL = $pageURL.'www.'.$current_url;
 	return $pageURL;
}
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
<script src="jquery.zclip.min.js"></script>
<script>
$(document).ready(function(){
	var link;
		
		$('a').each(function(){
			link = $(this).attr('alt');
			
			$(this).zclip({
        	path:'ZeroClipboard.swf',
        	copy: link
    	});
		});
});
</script>
</body>
</html>