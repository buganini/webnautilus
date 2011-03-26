<?php
include('func.php');
$rootdir=getbase($_GET['base']);
if(($file=safepath($rootdir,$_GET['file']))===false){
	die();
}
$hash=$_GET['base'].'/'.mkhash($rootdir.$file);

if(ufile_exists($cachedir.$hash.'.flv')){
?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="common.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="flowplayer/html/swfobject.js"></script>
</head>
<body><!-- <?php echo $hash;?> -->
<div style="font-size:10pt;"><?php echo getbasename($_GET['base']).$file;?></div>
<center>
<div id="flowplayerholder">
	This will be replaced by the player. 
</div>
<script type="text/javascript">
// <![CDATA[
var fo = new SWFObject("flowplayer/FlowPlayerDark.swf", "FlowPlayer", "468", "350", "7", "#ffffff", true);
// need this next line for local testing, it's optional if your swf is on the same domain as your html page
fo.addParam("allowScriptAccess", "always");
fo.addVariable("config", "{autoPlay: false, splashImageFile: '<?php echo $browserurl.$cacheurl.$hash.'_L.jpg';?>', videoFile: '<?php echo $browserurl.$cacheurl.$hash.'.flv';?>', initialScale: 'scale' }");
fo.write("flowplayerholder");
// ]]>
</script>
<br /><br />
<a href="<?php echo urlenc($rootdir.$file);?>" style="color:#555;">原始檔案下載(<?php echo fsize(ufilesize($rootdir.$_GET['file']));?>)</a>
</center>
</body>
</html>
<?php
}else{
bg($browserurl.'bg_thumb.php?base='.urlencode($_GET['base']).'&file='.urlencode($file).'&size='.$size);
?>
<html>
<head>
<meta http-equiv="refresh" content="10" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="common.css" rel="stylesheet" type="text/css" />
</head>
<body>
轉檔尚未完成，請稍後再試。
</body>
</html>
<?php
}
?>
