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
	echo $b.$p."\n";
	$rootdir=getbase($b);
	$fp=$rootdir.$p;
	if(uis_dir($fp)){
		$fs=uscandir($fp);
		foreach($fs as $f){
			if($f=='.'||$f=='..') continue;
			$todo[]=array($b,$p.'/'.$f);
		}
		continue;
	}

	$hash=mkhash($fp);
	$hash=$b.'/'.$hash;

	$size=$DEFAULT['thumb_size'];

	if(thumb_able($fp)){
		if(!newer($fp,$CFG['cachedir'].$hash.'_'.$size.'.jpg')){
			continue;
		}
		$job=array('base'=>$b,'file'=>$p,'size'=>$size);
		$s=serialize($job);
		echo $s;
		echo "\n";
		$gmc->doBackground("webnautilus",$s);
	}
}
?>
