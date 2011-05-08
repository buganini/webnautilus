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

	if(isimage($fp)){
		$t1=$CFG['cachedir'].$hash.'_'.$size.'.jpg';
		if(! newer($fp,$t1) ){
			touch($t1,filemtime($t1),$_now);
			continue;
		}
	}elseif(isvideo($fp)){
		$t1=$CFG['cachedir'].$hash.'.mp4';
		$t2=$CFG['cachedir'].$hash.'_L.jpg';
		$t3=$CFG['cachedir'].$hash.'_'.$size.'.jpg';
		if(!( newer($fp,$t1) || newer($fp,$t2) || newer($fp,$t3) )){
			touch($t1,filemtime($t1),$_now);
			touch($t2,filemtime($t2),$_now);
			touch($t3,filemtime($t3),$_now);
			continue;
		}
	}elseif(isaudio($fp)){
		$t1=$CFG['cachedir'].$hash.'.mp3';
		if(!newer($fp,$t1)){
			touch($t1,filemtime($t1),$_now);
			continue;
		}
	}elseif(isdocument($fp)){
		$t1=$CFG['cachedir'].$hash.'.pdf';
		$t2=$CFG['cachedir'].$hash.'_'.$size.'.jpg';
		if(!( newer($fp,$t1) || newer($fp,$t2) )){
			touch($t1,filemtime($t1),$_now);
			touch($t2,filemtime($t2),$_now);
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
