<?php
ignore_user_abort(true);
include('func.php');

$rootdir=getbase($_GET['base']);
if(($file=safepath($rootdir,$_GET['file']))===false){
	die();
}
$hash=mkhash($rootdir.$file);
$thash=$_GET['base'].'-'.$hash;
$hash=$_GET['base'].'/'.$hash;

if(preg_match('/^[0-9]+x[0-9]+$/',$_GET['size'])){
	$size=$_GET['size'];
}else{
	$size=$DEFAULT['thumb_size'];
}
$extmap=array(
	'zip'=>'zip.gif',
	'rar'=>'rar.gif',
	'7z'=>'7zip.gif',
	'7zip'=>'7zip.gif',
	'tgz'=>'gzip.gif',
	'gz'=>'gzip.gif',
	'tar'=>'tar.gif',
	'xls'=>'xls.gif',
	'doc'=>'doc.gif',
	'ppt'=>'ppt.gif',
	'txt'=>'txt.gif',
	'kmz'=>'ge.gif',
	'kml'=>'ge.gif',
	'htm'=>'html.gif',
	'html'=>'html.gif',
	'mht'=>'html.gif',
	'wav'=>'wav.gif',
	'mp3'=>'mp3.gif',
	'wma'=>'wma.gif',
	'swf'=>'swf.gif',
	'fla'=>'fla.gif',
	'aac'=>'aac.gif',
	'ace'=>'ace.gif',
	'aiff'=>'aiff.gif',
	'ape'=>'ape.gif',
	'arj'=>'arj.gif',
	'cab'=>'cab.gif',
	'mpc'=>'mpc.gif',
	'ogg'=>'ogg.gif',
	'pdf'=>'pdf.gif',
	'vqf'=>'vqf.gif',
	'xml'=>'xml.gif',
);
if(thumb_able($file)){
	if(!newer($rootdir.$file,$CFG['cachedir'].$hash.'_'.$size.'.jpg')){
		echo ($CFG['cacheurl'].$hash.'_'.$size.'.jpg');
		exit;
	}

	$job=array('base'=>$_GET['base'],'file'=>$file,'size'=>$size);
	$gmc=new Gearmanclient();
	$gmc->addServer();
	$gmc->doBackground("webnautilus",serialize($job));
	header('HTTP/1.1 503');
	exit;	
}elseif(isset($extmap[getext($file)])){
	echo ('images/'.$extmap[getext($file)]);
}else{
	echo ('images/noimage.gif');
}
?>
