<?php
include('func.php');
$rootdir=getbase($_GET['base']);
if(($file=safepath($rootdir,$_GET['file']))===false){
	die();
}
$hash=$_GET['base'].'/'.mkhash($rootdir.$file);
if(ufile_exists($CFG['cachedir'].$hash.'.mp4')){
?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="common.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="flowplayer/example/flowplayer-3.2.6.min.js"></script>
</head>
<body><!-- <?php echo $hash;?> -->
<div style="font-size:10pt;"><?php echo mklink(getbasename($_GET['base']),$file);?></div>
<center>
<span id="player" style="display:block;width:640px;height:480px"></span>
<!--
<img src="<?php echo $CFG['browserurl'].$CFG['cacheurl'].$hash.'_L.jpg';?>" style="width:640px; height:480px;" />
-->
<script type="text/javascript">
	flowplayer("player", "flowplayer/flowplayer-3.2.7.swf", {
		clip: {
			url: '<?php echo $CFG['browserurl'].$CFG['cacheurl'].$hash.'.mp4';?>',
			autoPlay: true,
			scaling: 'fit'
		}
	});
</script>
<br /><br />
<a href="<?php echo urlenc($rootdir.$file);?>" style="color:#555;">原始檔案下載(<?php echo fsize(ufilesize($rootdir.$_GET['file']));?>)</a>
</center>
</body>
</html>
<?php
}else{
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
