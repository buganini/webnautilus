<?
exit;
#XXX not unicode-safe
ignore_user_abort(true);
set_time_limit(0);
$daemon=true;
include('func.php');
$tok=rand().rand();
$lock=$tempdir.'daemon.lock';
if($_now-(@file_get_contents($lock))<600) die();
file_put_contents($lock,$_now."\t".$tok);

clearstatcache();

$i=0;
$list=scandir($tempdir);
foreach($list as $file){
	if(in_array($file,array('.','..','daemon.lock'))) continue;
	$file=$tempdir.$file;
	if($_now-max(filectime($file),filemtime($file))>86400){
		unlink($file);
	}
	++$i;
	if($i==100){
		chklock();
		$i=0;
	}
}
/*
foreach($base as $k=>$v){
	$list=scandir($cachedir.$k);
	foreach($list as $file){
		if(in_array($file,array('.','..'))) continue;
		$file=$cachedir.$k.'/'.$file;
		if($_now-fileatime($file)>259200){
			#unlink($file);
		}
		++$i;
		if($i==100){
			chklock();
			$i=0;
		}
	}
}
*/
chklock();

function chklock(){
	global $_now,$tok,$lock;
	list($last,$tk)=explode("\t",file_get_contents($lock));
	if($tk!=$tok) die();
	if(time()-$_now>600){
		$_now=time();
		file_put_contents($lock,$_now."\t".$tok);
	}
}
?>