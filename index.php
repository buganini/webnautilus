<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="common.css" rel="stylesheet" type="text/css" />
</head>
<body><?
include('func.php');

function renderdir($rootdir,$dir){
	global $ignore;
	$ret='';
	$r=$fnlist=$dirlist=array();
	$r['index']=null;
	if(($dir=fixdirpath(safepath($rootdir,$dir)))===false){
		redirect('index.php');
	}
	if($dir=='/'){
		$dir='';
	}
	$r['dir']=$dir;
	$list=(array)uscandir($rootdir.$dir);
	if($list===false){
		redirect('/gisedubrowser/index.php?base='.$_GET['base']);
	}
	foreach($list as $e)
	{
		if($e=='.' || $e=='..' || in_array(strtolower($e),$ignore))
		{
			continue;
		}
		if(uis_dir($rootdir.$dir.$e))
		{
			$dirlist[]=$e;
		}
		else
		{
			$fnlist[]=$e;
		}
	}
	$index_file=tryindex($fnlist,$dir);
	if(!empty($index_file)){
		$r['index']=$rootdir.$dir.$index_file;
	}
	natsort($dirlist);
	natsort($fnlist);
	foreach($dirlist as $e){
		$cfg=loadcfg($rootdir.$dir.$e);
		if(isset($cfg['index'])){
			$idx=$dir.fixdirpath($e).upath($cfg['index']);
			$ahref=urlenc($rootdir.$idx);
			$img='thumb.php?base='.$_GET['base'].'&file='.urlencode($idx);
		}else{
			$ahref='index.php?base='.$_GET['base'].'&dir='.urlencode($dir.$e);
			$img='images/dir.gif';
		}
		if(isset($cfg['icon'])){
			$img='thumb.php?base='.$_GET['base'].'&file='.urlencode($dir.fixdirpath($e).upath($cfg['icon']));
		}
		$ret.=mkitem($e,$e,$e,'<a href="'.$ahref.'"'.($cfg['target']=='_blank'?' target="_blank"':'').'>',$img,istoday($rootdir.$dir.$e),(($dz=udirsize($rootdir.$dir.$e))>0?'<br /><a style="float:right;" href="pack.php?base='.$_GET['base'].'&dir='.urlencode($dir.$e).'"><img alt="Pack n Download" title="下載這個資料夾 - '.fsize($dz).'" src="images/pack.gif" /></a>':''));
	}
	foreach($fnlist as $e){
		if(isvideo($e)){
			$ahref='<a href="flvplayer.php?base='.$_GET['base'].'&file='.urlencode($dir.$e).'">';
		}elseif(isimage($e)){
			$ahref='<a href="image.php?base='.$_GET['base'].'&file='.urlencode($dir.$e).'">';
		}elseif(isweb($e)){
			$ahref='<a href="'.urlenc($rootdir.$dir.$e).'">';
		}elseif(iscode($e)){
			$ahref='<a href="code.php?base='.$_GET['base'].'&file='.urlencode($dir.$e).'">';
		}else{
			$ahref='<a href="'.urlenc($rootdir.$dir.$e).'">';
		}
#		echo $dir.$e."\t".urlencode($dir.$e)."\n";
		$ret.=mkitem($e,$e,$e.' - ('.fsize(ufilesize($rootdir.$dir.$e)).')',$ahref,'thumb.php?base='.$_GET['base'].'&file='.urlencode($dir.$e),istoday($rootdir.$dir.$e),null);
	}
	$r['html']=$ret;
	return $r;
}

function mkitem($text,$alt,$title,$ahref,$img,$today,$pack){
	$ret='';
	$ret.='<div class="item">'.($today?'<img src="images/new.gif" style="position:absolute; z-index:20;" alt="new" />':'')."\n";
	$ret.='<div style="width:80px; height:60px; margin:auto;">'.$ahref."\n";
	$ret.='<img alt="'.$alt.'" title="'.$title.'" src="'.$img.'" />'."\n";
	$ret.='</a></div>'.$ahref.mb_strimwidth($text,0,15,'..','UTF-8').'</a>'."\n";
	$ret.=$pack."\n";
	$ret.='</div>'."\n";
	$ret.="\n\n";
	return $ret;
}

$rootdir=getbase($_GET['base']);
if($rootdir){
	$t=renderdir($rootdir,$_GET['dir']);
	echo '<div style="font-size:10pt;">';
	echo getbasename($_GET['base']).'/'.$t['dir'];
	echo '</div>';
	
	if(!empty($t['index'])){
		echo '<div style="border:solid 1px #f00; background:#dff; padding:2px; font-size:10pt; text-align:center;">這個資料夾內有幾個網頁，<a href="'.urlenc($t['index']).'" style="color:#00f;">這裡</a>可能是首頁</div>';
	}

	if(strlen(rtrim($t['dir'],'/\\'))>0){
		echo mkitem('[上一層]','Parent','Parent','<a href="javascript:history.back();">','images/pdir.gif',null,null);
#		echo mkitem('[上一層]','Parent','Parent','<a href="index.php?base='.$_GET['base'].'&dir='.urlencode($dir.'..').'">','images/pdir.gif',null,null);
	}

	echo $t['html'];
}else{
	foreach($base as $k=>$v){
		$_GET['base']=$k;
		echo mkitem($v[0],$v[0],$v[0],'<a href="index.php?base='.$k.'">','images/dir.gif',istoday($v[1]),(($dz=dirsize($v[1]))>0?'<br /><a style="float:right;" href="pack.php?base='.$k.'"><img alt="Pack n Download" title="下載這個資料夾 - '.fsize($dz).'" src="images/pack.gif" /></a>':''));
	}
}
?>
</body>
</html>