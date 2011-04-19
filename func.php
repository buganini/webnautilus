<?php
$CFG['url']='http://www.chops.ntct.edu.tw/picture/';
$CFG['gcpurl']='/jscss/gcp';		#google code prettifier
$CFG['memcache']='localhost';
$CFG['cachedir']='/home/www/picture/cache/';
$CFG['cacheurl']='cache/';
$CFG['tempdir']='/home/www/picture/temp/';
$CFG['mplayer']='/usr/local/bin/mplayer';
$CFG['yamdi']='/usr/local/bin/yamdi';
$CFG['ffmpeg']='/usr/local/bin/ffmpeg';
$CFG['timidity']='/usr/local/bin/timidity';
$CFG['imagemagick_convert']='/usr/local/bin/convert';
$CFG['imagemagick_identify']='/usr/local/bin/identify';
$CFG['sevenzip']='/usr/local/bin/7z';
$CFG['enca']='/usr/local/bin/enca';
$CFG['libreoffice']='/usr/local/bin/libreoffice';
$CFG['ghostscript']='/usr/local/bin/gsc';
$CFG['firefox']='C:/Firefox/firefox.exe';
$CFG['ffprofile']='d:/ffprofile';
$CFG['ffdownload']='d:/ffdown/';
$CFG['thumb_size']='80x60';

$base=array(
	#'basename'=>array('title','relative path',archive);
	'CHOPS'=>array('CHOPS','CHOPS/',true,'zh_TW.UTF-8'),
);

#filename in this list will not display, in lower case
$ignore=array('thumbs.db','desktop.ini','readme.txt');

date_default_timezone_set('Asia/Taipei');
#---------------------------------------------------------------------------
$_now=time();

$CFG['gcpurl']=fixdirpath($CFG['gcpurl']);
$CFG['cachedir']=fixdirpath(urealpath($CFG['cachedir']));
$CFG['tempdir']=fixdirpath(urealpath($CFG['tempdir']));
$CFG['cacheurl']=fixdirpath($CFG['cacheurl']);
$sysroot=fixdirpath(dirname(__FILE__));
chdir($sysroot);

$RTI['memcache'] = new Memcache;
$RTI['memcache']->pconnect($CFG['memcache']);

function mklink($base,$sub){
	$r=array();
	$t='';
	$sub=trim($sub,'/');
	if($sub)
		$a=explode('/',$sub);
	else
		$a=array();
	$r[]='<a href="index.php?base='.$base.'">'.$base.'</a>';
	$l=count($a)-1;
	for($i=0;$i<$l;++$i){
		$t=pathjoin($t,$a[$i]);
		$r[]='<a href="index.php?base='.$base.'&dir='.urlencode($t).'">'.$a[$i].'</a>';
	}
	if($l>=0)
		$r[]='<a>'.$a[$l].'</a>';
	return '<span id="link">'.implode('/',$r).'</span>';
}

function selfurl(){
	return 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
}

function mylocked($t){
	global $RTI;
	if($RTI['memcache']->get('webnautilus-'.$t)){
		return true;
	}
	
}

function mylock($t){
	global $RTI;
	return $RTI['memcache']->add('webnautilus-'.$t,1,0,86400);
}

function myunlock($t){ 
	global $RTI;
	$RTI['memcache']->delete('webnautilus-'.$t);
}

function mkhash($f){
	if(uis_dir($f)){
		return sha1(basename(rtrim($f,'\\/'))).md5(serialize(uscandir($f)));
	}
	return sha1(basename($f)).ufilesize($f);
}

function urealpath($p){
	$p=realpath(r($p));
	if($p===false) return false;
	return upath(q($p));
}

function upath($p){
	if($p===false) return false;
	$p=str_replace('\\','/',$p);
	return $p;
}

function safepath($root,$path){
	if($root===false) return false;
	$path=urealpath($root.$path);
	$root=urealpath($root);
	if($root===false || $path===false) return false;
	$len=strlen($root);
	if(substr($path,0,$len)==$root){
		if(strlen($path)==$len) return '';
		return substr($path,$len);
	}
	return false;
}

function q($s){
	return mb_convert_encoding($s,'UTF-8');
}

function r($s){
	return mb_convert_encoding($s,mb_internal_encoding(),'UTF-8');
}

function ufile_get_contents($f){
	return file_get_contents(r($f));
}

function ufilesize($f){
	return filesize(r($f));
}

function udirsize($d){
	return dirsize(r($d));
}

function uopendir($d){
	return opendir(r($d));
}

function ureaddir($dp){
	return q(readdir($dp));
}

function uscandir($d){
	$l=scandir(r($d));
	foreach($l as &$v){
		$v=q($v);
		unset($v);
	}
	return $l;
}

function ufile_exists($f){
	return file_exists(r($f));
}

function utouch($f){
	return touch(r($f));
}

function umkdir($f){
	return mkdir(r($f));
}

function uis_dir($d){
	return is_dir(r($d));
}

function uunlink($f){
	return unlink(r($f));
}

function ucopy($f,$g){
	return copy(r($f),r($g));
}

function fixdirpath($p){
	$p=rtrim($p,'/\\').'/';
	return $p;
}

function pathjoin(){
	$args=func_get_args();
	$r=array_shift($args);
	foreach($args as $a){
		$r=rtrim($r,'/\\').'/'.ltrim($a,'/\\');
	}
	return $r;
}

function urlenc($p){
	$a=explode('/',$p);
	foreach($a as &$v){
		$v=rawurlencode($v);
		unset($v);
	}
	$p=implode('/',$a);
	return $p;
}

function set_timezone($tz){
	putenv('TZ='.$tz);
	ini_set('date.timezone', $tz);
	if(function_exists('date_default_timezone_set')){
		date_default_timezone_set($tz);
	}
}

function ftime($s){
	$m=$h=$d=0;
	if($s>=60){
		$m=intval($s/60);
		$s%=60;
	}
	if($m>=60){
		$h=intval($m/60);
		$m%=60;
	}
	if($h>=24){
		$d=intval($h/24);
		$h%=24;
	}
	return $d.'d'.$h.'h'.$m.'m'.$s.'s';
}

function ufiletime($f){
	return filetime(r($f));
}

function filetime($f){
	return filemtime($f);
}
function rmtry($f){
	if(ufile_exists($f)){
		@uunlink($f);
	}
}

function getbasename($s){
	global $base;
	if(isset($base[$s])){
		return $base[$s][0];
	}else{
		return false;
	}
}

function getbase($s){
	global $base,$RTI;
	if(isset($base[$s])){
		setlocale(LC_ALL,$base[$s][3]);
		$t=explode('.',$base[$s][3]);
		$RTI['locale']=$t[0];
		mb_internal_encoding($t[1]);
		$RTI['base']=$s;
		return fixdirpath($base[$s][1]);
	}else{
		mb_internal_encoding('UTF-8');
		return false;
	}
}

function loadcfg($d){
	$arr=array();
	if(ufile_exists(pathjoin($d,'config.ini'))){
		$s=ufile_get_contents(pathjoin($d,'config.ini'));
		$s=preg_replace("/[\r\n]+/s","\n",$s);
		$a=explode("\n",$s);
		foreach($a as $l){
			$n=strpos($l,'=');
			if($n!==false){
				$key=trim(substr($l,0,$n));
				$val=trim(substr($l,$n+1));
				$arr[$key]=$val;
			}else{
				$arr[$l]=true;
			}
		}
	}
	return $arr;
}

function dehtml($s){
	$s=str_replace('&','&amp;',$s);
	$s=str_replace('<','&lt;',$s);
	$s=str_replace('>','&gt;',$s);
	return $s;
}


function isvideo($file){
	return in_array(getext($file),array('mpg','mpeg','avi','rm','rmvb','mov','wmv','mod','asf','m1v','mp2','mpe','mpa','flv','3pg','vob','mp4'));
}

function isaudio($file){
	return in_array(getext($file),array('mp3','wav','flac','ape','wma','mid','rmi'));
}

function ismidi($file){
	return in_array(getext($file),array('mid','rmi'));
}

function isdocument($file){
	return in_array(getext($file),array('doc','txt','pdf','ppt','pps','xls'));
}

function isimage($file){
	return in_array(getext($file),array('jpg','bmp','gif','png','jpeg','tiff','tif','psd'));
}

function isspecimage($file){
	return in_array(getext($file),array('tiff','tif','psd'));
}

function isweb($file){
	return in_array(getext($file),array('htm','html'));
}

function iscode($file){
	return in_array(getext($file),array('c','cpp','h','pl','py','php','phps','asp','aspx','css','jsp','sh','shar'));
}

function redirect($s){
	header('Location: '.$s);
	die();
}

function dirsize($d){
	global $RTI;
	if(is_dir($d)){
		$key='dirsize-'.$RTI['base'].'-'.$d.'-'.filetime($d);
		$ret=$RTI['memcache']->get($key);
		if($ret!==false){
			return $ret;
		}else{
			$z=0;
			$dp=opendir($d);
			while(false!==($e=readdir($dp))){
				if($e=='.' || $e=='..'){continue;}
				$z+=dirsize(pathjoin($d,$e));
			}
			closedir($dp);
			$RTI['memcache']->set($key,$z,0,rand(86400,604800));
			return $z;
		}
	}else{
		return filesize($d);
	}
}

function fsize($s){
	$lvu=array('','K','M','G','T');
	$lv=0;
	while($s>1024){
		$s/=1024;
		++$lv;
	}
	$s=sprintf("%.2f",$s);
	return $s.$lvu[$lv].'B';
}

function getext($s){
	return strtolower(preg_replace('/^.*?\\.([^.]+)$/','\1',$s));
}

function getname($s){
	return preg_replace('/^(.*?)\\.[^.]+$/','\1',$s);
}

function thumb_able($file){
	return (isvideo($file) || isimage($file) || isdocument($file) || isweb($file));
}

function newer($a,$b){
	$a=r($a);
	$b=r($b);
	if((!file_exists($b)) || (!file_exists($a))){
		return true;
	}
	$at=filetime($a);
	$bt=filetime($b);
	if($at!=$bt){
		return true;
	}
	if(is_dir($a)){
		$dp=opendir($a);
		while(false!==($e=readdir($dp))){
			if($e=='.' || $e=='..'){continue;}
			if(newer(pathjoin($a,$e),$b)){
				closedir($dp);
				return true;
			}
		}
		closedir($dp);
	}
	return false;
}

function exe($c){
	#return '';
	return shell_exec($c);
}

function logger($s){
	global $sysroot;
	file_put_contents($sysroot.'webnautilus.log',$s."\r\n",FILE_APPEND);
	return $s;
}

function istoday($f){
	global $base,$RTI;
	if($base[$RTI['base']][2]){
		return false;
	}
	if(istoday_r(r($f))){
		return true;
	}
	return false;
}

function istoday_r($f){
	global $_now;
	$ft=filetime($f);
	if($_now-$ft<86400){
		return true;
	}
	if(is_dir($f)){
		$dp=opendir($f);
		while($e=readdir($dp)){
			if($e=='.' || $e=='..'){continue;}
			if(istoday_r(pathjoin($f,$e))){
				closedir($dp);
				touch($f,$ft,$_now);
				return true;
			}
		}
		closedir($dp);
	}
	return false;
}

function isarchive(){
	global $base,$RTI;
	return ($base[$RTI['base']][2]);
}

function basedir($dir){
	return basename(rtrim($dir,'/\\'));
}

function tryindex($fs,$dir){
	global $CFG,$RTI;
	$bdir=basedir($dir);
	$index_file='';
	$ifile=pathjoin($CFG['cachedir'],$RTI['base'],sha1($dir).'.idx');
	if(isarchive() && ($index_file=@file_get_contents($ifile))!==false){
		return $index_file;
	}else{
		foreach($fs as $f){
			if(!isweb($f)){
				continue;
			}
			if(empty($index_file)){
				$index_file=$f;
			}elseif(preg_match('/^index/i',$f)){
				if(preg_match('/^index/i',$index_file)){
					if(strlen(getname($f))<strlen(getname($index_file))){
						$index_file=$f;
					}
				}else{
					$index_file=$f;
				}
			}elseif(preg_match('/^default/i',$f)){
				if(preg_match('/^default/i',$index_file)){
					if(strlen(getname($f))<strlen(getname($index_file))){
						$index_file=$f;
					}
				}elseif(!preg_match('/^index/i',$index_file)){
					$index_file=$f;
				}
			}elseif(levenshtein($bdir,$f)<levenshtein($bdir,$index_file) && !preg_match('/^index/i',$index_file) && !preg_match('/^default/i',$index_file)){
				$index_file=$f;
			}
		}
		if(isarchive()){
			if(!ufile_exists($CFG['cachedir'].$RTI['base'])){
				umkdir($CFG['cachedir'].$RTI['base']);
			}
			file_put_contents($ifile,$index_file);
		}
		return $index_file;
	}
}
?>
