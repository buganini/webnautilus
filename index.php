<?php
include('func.php');

if(empty($_GET['base']) && count($base)==1){
	foreach($base as $k=>$v)
		redirect('index.php?base='.$k);
}
?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!-- http://github.com/buganini/webnautilus/ -->
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
<link href="common.css" rel="stylesheet" type="text/css" />
</head>
<body><?php

function renderdir($rootdir,$dir){
	global $ignore;
	$ret='';
	$r=$fnlist=$dirlist=array();
	$r['index']=null;
	if(($dir=safepath($rootdir,$dir))===false){
		redirect('index.php');
	}
	$r['dir']=$dir;
	$list=(array)uscandir(pathjoin($rootdir,$dir));
	if($list===false){
		redirect('index.php?base='.$_GET['base']);
	}
	foreach($list as $e)
	{
		if($e=='.' || $e=='..' || in_array(strtolower($e),$ignore))
		{
			continue;
		}
		if(uis_dir(pathjoin($rootdir,$dir,$e)))
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
		$r['index']=pathjoin($rootdir,$dir,$index_file);
	}
	natsort($dirlist);
	natsort($fnlist);
	foreach($dirlist as $e){
		$cfg=loadcfg(pathjoin($rootdir,$dir,$e));
		if(isset($cfg['index'])){
			$idx=pathjoin($dir,$e,upath($cfg['index']));
			$ahref=urlenc(pathjoin($rootdir,$idx));
			$img='thumb.php?base='.$_GET['base'].'&file='.urlencode($idx);
		}else{
			$ahref='index.php?base='.$_GET['base'].'&dir='.urlencode(pathjoin($dir,$e));
			$img='images/dir.gif';
		}
		if(isset($cfg['icon'])){
			$img='thumb.php?base='.$_GET['base'].'&file='.urlencode(pathjoin($dir,$e,upath($cfg['icon'])));
		}
		$ret.=mkitem($e,$e,$e,'<a href="'.$ahref.'"'.($cfg['target']=='_blank'?' target="_blank"':'').'>',$img,istoday(pathjoin($rootdir,$dir,$e)),(($dz=udirsize(pathjoin($rootdir,$dir,$e)))>0?'<br /><a name="pack" style="visibility: hidden; float:right;" href="pack.php?base='.$_GET['base'].'&dir='.urlencode(pathjoin($dir,$e)).'"><img alt="Download" title="Download - '.fsize($dz).'" src="images/pack.gif" /></a>':''));
	}
	foreach($fnlist as $e){
		if(isvideo($e) || isaudio($e)){
			$ahref='<a href="flowplayer.php?base='.$_GET['base'].'&file='.urlencode(pathjoin($dir,$e)).'">';
		}elseif(isimage($e)){
			$ahref='<a href="image.php?base='.$_GET['base'].'&file='.urlencode(pathjoin($dir,$e)).'">';
		}elseif(isweb($e)){
			$ahref='<a href="'.urlenc(pathjoin($rootdir,$dir,$e)).'">';
		}elseif(isdocument($e)){
			$ahref='<a href="document.php?base='.$_GET['base'].'&file='.urlencode(pathjoin($dir,$e)).'">';
		}elseif(iscode($e)){
			$ahref='<a href="code.php?base='.$_GET['base'].'&file='.urlencode(pathjoin($dir,$e)).'">';
		}else{
			$ahref='<a href="'.urlenc(pathjoin($rootdir,$dir,$e)).'">';
		}
#		echo $dir.$e."\t".urlencode($dir.$e)."\n";
		$ret.=mkitem($e,$e,$e.' - ('.fsize(ufilesize(pathjoin($rootdir,$dir,$e))).')',$ahref,'thumb.php?base='.$_GET['base'].'&file='.urlencode(pathjoin($dir,$e)),istoday(pathjoin($rootdir,$dir,$e)),null);
	}
	$r['html']=$ret;
	return $r;
}

function mkitem($text,$alt,$title,$ahref,$img,$today,$pack){
	$ret='';
	$ret.='<div class="item">'.($today?'<img src="images/new.gif" style="position:absolute; z-index:20;" alt="new" />':'')."\n";
	$ret.='<div style="width:80px;height:60px;margin:auto;">'.$ahref."\n";
	if(substr($img,0,9)=='thumb.php'){
		$ret.='<img style="alt="'.$alt.'" title="'.$title.'" thumb="true" src="images/working.gif" realsrc="'.$img.'" />'."\n";
	}else{
		$ret.='<img style="alt="'.$alt.'" title="'.$title.'" src="'.$img.'" />'."\n";
	}
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
	echo mklink(getbasename($_GET['base']),$t['dir']);
	echo '</div>';
	
	if(!empty($t['index'])){
		echo '<div style="border:solid 1px #f00; background:#dff; padding:2px; font-size:10pt; text-align:center;">這個資料夾內有幾個網頁，<a href="'.urlenc($t['index']).'" style="color:#00f;">這裡</a>可能是首頁</div>';
	}

	if(strlen(rtrim($t['dir'],'/\\'))>0){
#		echo mkitem('[上一層]','Parent','Parent','<a href="javascript:history.back();">','images/pdir.gif',null,null);
#		echo mkitem('[上一層]','Parent','Parent','<a href="index.php?base='.$_GET['base'].'&dir='.urlencode($dir.'..').'">','images/pdir.gif',null,null);
	}

	echo $t['html'];
}else{
	foreach($base as $k=>$v){
		getbase($k);
		echo mkitem($v[0],$v[0],$v[0],'<a href="index.php?base='.$k.'">','images/dir.gif',istoday($v[1]),(($dz=udirsize($v[1]))>0?'<br /><a style="float:right;" href="pack.php?base='.$k.'"><img alt="Pack n Download" title="下載這個資料夾 - '.fsize($dz).'" src="images/pack.gif" /></a>':''));
	}
}
?>
<script type="text/javascript">
$('a[name="pack"]').each(function(){
	$(this).parent().hover(function(){
		$(this).children('a[name="pack"]').css('visibility','visible')
	},function(){
		$(this).children('a[name="pack"]').css('visibility','hidden')
	})
})
function loadimg(){
	var c=0;
	$('img[thumb="true"]').each(function(){
		var t=this;
		c+=1;
		$.ajax({
			url: $(this).attr('realsrc'),
			type: 'GET',
			async: true,
			dataType: 'text',
 			success: function(text){
				$(t).attr('thumb','');
				$(t).attr('src',text);
			}
		})		
	});
	if(c)
		setTimeout("loadimg()",10000);
}
loadimg();
</script>
</body>
</html>
