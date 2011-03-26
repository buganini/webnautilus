<?php
include('func.php');

$rootdir=getbase($_GET['base']);
if(($file=safepath($rootdir,$_GET['file']))===false){
	redirect('index.php');
}
if(!ufile_exists($rootdir.$file)){
header('HTTP/1.0 404 Not Found');
die();
}
?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="common.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $gcpurl;?>prettify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="<?php echo $gcpurl;?>prettify.js"></script>
</head>
<body onload="prettyPrint()">
<div style="font-size:10pt;"><?php echo getbasename($_GET['base']).t($_GET['file']);?></div>
<pre class="prettyprint"><?php
$s=ufile_get_contents($rootdir.$file);
echo dehtml(mb_convert_encoding($s,'UTF-8',mb_detect_encoding($s)));
?></pre>
</body>
</html>
