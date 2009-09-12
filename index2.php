<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="common.css" rel="stylesheet" type="text/css" />
<style type="text/css">
td, th{
border: solid 1px #ccc;
}
</style>
</head>
<body>
<table width="100%">
<tr><th>中心學校名稱</th><th>模組名稱</th><th>檔案</th></tr><?
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

	$cfg=loadcfg($rootdir.$dir);
	if(isset($cfg['index'])){
		$idx=$dir.upath($cfg['index']);
		$ahref=urlenc($rootdir.$idx);
		$img='thumb.php?base='.$_GET['base'].'&file='.rawurlencode($idx);
		if(isset($cfg['icon'])){
			$img='thumb.php?base='.$_GET['base'].'&file='.rawurlencode($dir.upath($cfg['icon']));
		}
		$r['html']=mkitem(t(basename(trim($dir,'/'))),t($dir),t($dir),'<a href="'.$ahref.'"'.($cfg['target']=='_blank'?' target="_blank"':'').'>',$img,istoday($rootdir.$dir),(($dz=dirsize($rootdir.$dir))>0?'<br /><a style="float:right;" href="pack.php?base='.$_GET['base'].'&dir='.urlencode($dir).'"><img alt="Pack n Download" title="下載這個資料夾 - '.fsize($dz).'" src="images/pack.gif" /></a>':''));
		return $r;
	}
	
	$list=@uscandir($rootdir.$dir);
	if($list===false){
#		redirect('/gisedubrowser/index.php?base='.$_GET['base']);
	}
	foreach($list as $e){
		if($e=='.' || $e=='..' || in_array(strtolower($e),$ignore)){
			continue;
		}
		if(uis_dir($rootdir.$dir.$e)){
			$dirlist[]=$e;
		}else{
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
			$img='thumb.php?base='.$_GET['base'].'&file='.rawurlencode($idx);
		}else{
			$ahref='index.php?base='.$_GET['base'].'&dir='.urlencode($dir.$e);
			$img='images/dir.gif';
		}
		if(isset($cfg['icon'])){
			$img='thumb.php?base='.$_GET['base'].'&file='.rawurlencode($dir.fixdirpath($e).upath($cfg['icon']));
		}
		$ret.=mkitem($e,$e,$e,'<a href="'.$ahref.'"'.($cfg['target']=='_blank'?' target="_blank"':'').'>',$img,istoday($rootdir.$dir.$e),(($dz=dirsize($rootdir.$dir.$e))>0?'<br /><a style="float:right;" href="pack.php?base='.$_GET['base'].'&dir='.urlencode($dir.$e).'"><img alt="Pack n Download" title="下載這個資料夾 - '.fsize($dz).'" src="images/pack.gif" /></a>':''));
	}
	foreach($fnlist as $e){
		if(isvideo($e)){
			$ahref='<a href="flvplayer.php?base='.$_GET['base'].'&file='.urlencode($dir.$e).'">';
		}elseif(isimage($e)){
			$ahref='<a href="image.php?base='.$_GET['base'].'&file='.urlencode($dir.$e).'">';
		}elseif(isweb($e)){
			$ahref='<a href="'.$rootdir.$dir.$e.'">';
		}elseif(iscode($e)){
			$ahref='<a href="code.php?base='.$_GET['base'].'&file='.urlencode($dir.$e).'">';
		}else{
			$ahref='<a href="'.urlenc($rootdir.$dir.$e).'">';
		}
		$ret.=mkitem($e,$e,$e.' - ('.fsize(ufilesize($rootdir.$dir.$e)).')',$ahref,'thumb.php?base='.$_GET['base'].'&file='.rawurlencode($dir.$e),istoday($rootdir.$dir.$e),null);
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
	if(($dir=fixdirpath(safepath($rootdir,$_GET['dir'])))===false){
		redirect('index.php');
	}
	if($dir=='/'){
		$dir='';
	}
	$schools=scandir($rootdir.$dir);
	foreach($schools as $school){
		if($school=='.' || $school=='..') continue;
		$school=fixdirpath($school);
		$modules=uscandir($rootdir.$dir.$school);
		$modn=0;
		$html='">'.trim($school,'/').'</td>';

		$cfg=loadcfg($rootdir.$dir.$school);
		if(isset($cfg['index'])){
			$idx=upath($cfg['index']);
			$ahref=urlenc($rootdir.$dir.$school.$idx);
			$img='thumb.php?base='.$_GET['base'].'&file='.rawurlencode($dir.$school.$idx);
			if(isset($cfg['icon'])){
				$img='thumb.php?base='.$_GET['base'].'&file='.rawurlencode($dir.$school.upath($cfg['icon']));
			}
			$school=trim($school,'/');
			echo '<td>'.$school.'</td><td>'.$school.'</td><td>';
			echo mkitem($school,$school,$school,'<a href="'.$ahref.'"'.($cfg['target']=='_blank'?' target="_blank"':'').'>',$img,istoday($rootdir.$dir.$school),(($dz=dirsize($rootdir.$dir.$school))>0?'<br /><a style="float:right;" href="pack.php?base='.$_GET['base'].'&dir='.urlencode($school).'"><img alt="Pack n Download" title="下載這個資料夾 - '.fsize($dz).'" src="images/pack.gif" /></a>':''));
			echo '</td></tr>';
		}else{
			foreach($modules as $module){
				if($module=='.' || $module=='..') continue;
				++$modn;
				if(uis_dir($rootdir.$dir.$school.$module)){
					$t=renderdir($rootdir,$dir.$school.$module);
				}else{
					$e=$module;
					$module=getname($module);
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
					$t['html']=mkitem($e,$e,$e.' - ('.fsize(ufilesize($rootdir.$dir.$school.$e)).')',$ahref,'thumb.php?base='.$_GET['base'].'&file='.rawurlencode($dir.$school.$e),istoday($rootdir.$dir.$school.$e),null);
				}
				if($modn>1) $html.='<tr>';
				$html.='<td>'.trim($module,'/').'</td><td>'.$t['html'].'</td></tr>';
			}
			echo '<tr><td rowspan="'.$modn;
			echo $html;
		}
	}
}
?>
</body>
</html>