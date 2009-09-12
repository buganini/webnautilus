<?
include('func.php');
$rootdir=getbase($_GET['base']);
if(($file=safepath($rootdir,$_GET['file']))===false){
	die();
}
$hash=mkhash($rootdir.$file);
?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="common.css" rel="stylesheet" type="text/css" />
<script style="text/javascript">
function autozoom(o){
	cw = 700;
//	cw = document.body.clientWidth-20;
	ch = document.body.clientHeight-20;
	ow = o.width;
	oh = o.height;
	if(o.width < cw && o.height < ch){
		return;
	}
	if((o.width/o.height)>(cw/ch)){
		o.width = cw;
		o.height = oh*(cw/ow);
	}else{
		o.height = ch;
		o.width = ow*(ch/oh);
	}
}

function freezoom(o){
	if(o.width!=ow && o.height!=oh){
		o.width = ow;
		o.height = oh;
	}else{
		autozoom(o);
	}
}
</script>
</head>
<body><!-- <?echo $hash;?> -->
<div style="font-size:10pt;"><?	echo getbasename($_GET['base']).$file;?></div>
<?
if(isspecimage($file)){
?>
抱歉，您的瀏覽器無法直接開啟這種格式的圖檔，麻煩<a href="<?echo urlenc($rootdir.$file);?>">直接下載</a>。
<?
}else{
?>
<img onload="autozoom(this);" onclick="freezoom(this);" style="display:block; margin:auto; cursor:pointer;" src="<?echo urlenc($rootdir.$file);?>" style="margin:auto;" title="<?echo basename($_GET['file']);?>" />
<?
}
?>
</body>
</html>