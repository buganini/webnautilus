<?php
include('func.php');

$gmc=new Gearmanclient();
$gmc->addServer();

$todo=array();

foreach($base as $b){
	$todo[]=array($b[0],'');
}

while(count($todo)){
	list($b,$p)=array_shift($todo);
#	echo $b.$p."\n";
	$rootdir=getbase($b);
	$fp=pathjoin($rootdir,$p);
	if(uis_dir($fp)){
		$fs=uscandir($fp);
		foreach($fs as $f){
			if($f=='.'||$f=='..') continue;
			$todo[]=array($b,pathjoin($p,$f));
		}
		continue;
	}

	$hash=mkhash($fp);
	$hash=$b.'/'.$hash;

	$size=$CFG['thumb_size'];

	if(thumb_able($fp)){
		$cf=$CFG['cachedir'].$hash.'_'.$size.'.jpg';
		if(!newer($fp,$cf)){
			touch($cf,filemtime($cf),$_now);
			continue;
		}
	}elseif(isaudio($fp)){
		$cf=$CFG['cachedir'].$hash.'.mp3';
		if(!newer($fp,$cf)){
			touch($cf,filemtime($cf),$_now);
			continue;
		}
	}else{
		continue;
	}
	$job=array('base'=>$b,'file'=>$p,'size'=>$size);
	$s=serialize($job);
	echo $s;
	echo "\n";
	$gmc->doLowBackground("webnautilus",$s);
}
?>
