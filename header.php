<head>
<title><?php echo $title . ' | ' . WEBSITE_NAME ?></title>
<link type="text/css" rel="stylesheet" href="styles.css">
<link type="text/css" rel="stylesheet" href="rickshaw/rickshaw.min.css">
<link type="text/css" rel="stylesheet" href="rickshaw/src/css/graph.css">
<link type="text/css" rel="stylesheet" href="rickshaw/src/css/legend.css">
<script src="rickshaw/vendor/d3.min.js"></script>
<script src="rickshaw/vendor/d3.v3.js"></script>
<script src="rickshaw/vendor/d3.layout.min.js"></script>
<script src="rickshaw/rickshaw.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.15/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization',
       'version':'1.1','packages':['timeline']}]}"></script>
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('.tabs .tab-links a').on('click', function(e)  {
        var currentAttrValue = jQuery(this).attr('href') + "-div";
 
        // Show/Hide Tabs
        jQuery('.tabs ' + currentAttrValue).show().siblings().hide();
 
        // Change/remove current tab to active
        jQuery(this).parent('li').addClass('active').siblings().removeClass('active');
 
        e.preventDefault();
		history.replaceState('', document.title, window.location.href.split('#')[0]);
		
		drawChart();
    });
	$(".tabs").find('a[href=' + window.location.hash + ']').click();
	history.replaceState('', document.title, window.location.href.split('#')[0]);
});
</script>
</head>
<body>
<div id="content">
<div id="header">
<div id="menu">
<ul>
<?php
$links = array(array("/" . SITE_ROOT, "Home"), array("about", "About"), array("new", "Create Event"), array("help", "Help"));
if ($account !== false)
	array_push($links, array("cp", "Dashboard"));
else
	array_push($links, array("login", "Login/Register"));
foreach($links as $l)
{
	echo "<li";
	if ($l[1] == $title)
	{
		echo " class=\"active\"";
	}
	echo "><a href=" . $l[0] . ">" . $l[1] . "</a></li>";
}
?>
</ul>
</div>
<h1 id="title"><a style="color: black; text-decoration: none" href="<?php echo "/" . SITE_ROOT ?>"><?php echo WEBSITE_NAME ?></a></h1>
<div style="clear:both"></div>
<hr>
</div>
<div id="page">