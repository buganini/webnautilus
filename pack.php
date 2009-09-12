<?
ignore_user_abort(true);
include('func.php');

$rootdir=getbase($_GET['base']);
if(($dir=fixdirpath(safepath($rootdir,$_GET['dir'])))===false){
	redirect('index.php');
}
$hash=mkhash($rootdir.$dir);
$thash=$_GET['base'].'-'.$hash;
$hash=$_GET['base'].'/'.$hash;
$path=rtrim($rootdir.$dir,'/');
$apath=array();
$pos=0;
for($i=0;$i<mb_strlen($path,'UTF-8');$i++){
	$c=mb_substr($path,$i,1,'UTF-8');
	if($c=='/'){
		++$pos;
	}else{
		$apath[$pos].=$c;
	}
}
$bdir=array_pop($apath);
$zfile=$hash.'.'.$bdir.'.zip';

bg($browserurl.'bg_pack.php?base='.urlencode($_GET['base']).'&dir='.urlencode($_GET['dir']));
if(ufile_exists($cachedir.$zfile)){
	#header('Content-type: application/octet-stream');
	#header('Content-Disposition: attachment; filename="'.preg_replace('/^[^\\.]+\\./i','',$zfile).'"');
	#readfile($cachedir.$zfile);
	redirect($cacheurl.urlenc($zfile));
}else{
?><html>
<head>
<meta http-equiv="refresh" content="10" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
body{
	background:transparent;
}
</style>
<!-- <?echo $thash;?> -->
</head>
<body>
資料壓縮中，請稍後<a href="javascript:document.location.reload(true);">再試</a>。
</body>
</html>
<?
}
?>