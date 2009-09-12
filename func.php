<?
$browserurl='http://www.gisedu.geog.ntu.edu.tw/gisedubrowser/';
$shmid=1085;
$concurrency=2;
$gcpurl='/jscss/gcp';		#google code prettifier
$cachedir='D:/cache/';
$cacheurl='cache/';
$tempdir='D:/temp/data';
$lockdir='D:/temp/lock';
$jobdir='D:/temp/job';
$mencoder='C:/mplayer/mencoder.exe';
$mplayer='C:/mplayer/mplayer.exe';
$yamdi='C:/yamdi.exe';
$ffmpeg='C:/ffmpeg.exe';
$imagemagick_convert='C:/ImageMagick/im_convert.exe';
$imagemagick_identify='C:/ImageMagick/identify.exe';
$sevenzip='C:/7-Zip/7z.exe';
$unoconv='C:/OpenOffice.org/program/python-core-2.3.4/bin/python.exe C:/unoconv';
$ghostscript='C:/gs/gs8.61/bin/gswin32c.exe';
$firefox='C:/Firefox/firefox.exe';
$ffprofile='d:/ffprofile';
$ffdownload='d:/ffdown/';
$charset='Big5';
$DEFAULT['thumb_size']='80x60';
#"C:/OpenOffice.org/program/soffice.exe" -headless -accept="socket,host=localhost,port=2002;urp;"
$base=array(
	#'basename'=>array('title','relative path',archive);
	'Module'=>array('教學模組','../module/ntu/95/',true),
	'Mapresource'=>array('地圖資源','../result/96/',true),
	'competition3th'=>array('第三屆全國比賽','../result/96/',true),
);

#filename in this list will not display, in lower case
$ignore=array('thumbs.db','desktop.ini','readme.txt');

set_timezone('Asia/Taipei');
#---------------------------------------------------------------------------
if(!function_exists('shm_attach')){
	include('shm.php');
}
set_magic_quotes_runtime(0);
if(get_magic_quotes_gpc()==1){
	foreach($_GET as $key => $val){
		$_GET[$key]=stripslashes($_GET[$key]);
	}
}

mb_internal_encoding($charset);

$_now=time();

if((!$daemon) && ($_now-(@file_get_contents($tempdir.'daemon.lock'))>600)){
	#bg($browserurl.'daemon.php');
}
$browserurl=fixdirpath($browserurl);
$gcpurl=fixdirpath($gcpurl);
$cachedir=fixdirpath(urealpath($cachedir));
$tempdir=fixdirpath(urealpath($tempdir));
$lockdir=fixdirpath(urealpath($lockdir));
$jobdir=fixdirpath(urealpath($jobdir));
$cacheurl=fixdirpath($cacheurl);

function selfurl(){
	return 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
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
	$p=upath($p);
	return mb_convert_encoding($p,'UTF-8');
}

function upath($p){
	if($p===false) return false;
#	$p=mb_convert_encoding($p,'UTF-8');
	$p=str_replace('\\','/',$p);
#	$p=mb_convert_encoding($p,mb_internal_encoding(),'UTF-8');
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
	return mb_convert_encoding(readdir($dp),'UTF-8',mb_internal_encoding());
}

function uscandir($d){
	$l=scandir(r($d));
	foreach($l as &$v){
		$v=mb_convert_encoding($v,'UTF-8');
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
	if($p===false) return false;
#	$p=mb_convert_encoding($p,'UTF-8');
	$p=rtrim($p,'/\\').'/';
#	$p=mb_convert_encoding($p,mb_internal_encoding(),'UTF-8');
	return $p;
}

function urlenc($p){
#	$p=mb_convert_encoding($p,'UTF-8');
	$a=explode('/',$p);
	foreach($a as &$v){
		$v=rawurlencode($v);
		unset($v);
	}
	$p=implode('/',$a);
#	$p=mb_convert_encoding($p,mb_internal_encoding(),'UTF-8');
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

function bg($url){
/*
#old method, take longer latency
	$c=curl_init();
	curl_setopt($c, CURLOPT_URL,$url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_TIMEOUT, 1);
	$ret=curl_exec($c);
	curl_close($c);
	return $ret;
*/
	$ctx = stream_context_create(array('http' => array('timeout' => 0.01))); 
	return file_get_contents($url, 0, $ctx);
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
	global $base;
	if(isset($base[$s])){
		return fixdirpath($base[$s][1]);
	}else{
		return false;
	}
}

function loadcfg($d){
	$d=fixdirpath($d);
	$arr=array();
	if(ufile_exists($d.'config.ini')){
		$s=ufile_get_contents($d.'config.ini');
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

$sysroot=fixdirpath(dirname(__FILE__));

function isvideo($file){
	if(in_array(getext($file),array('mpg','mpeg','avi','rm','rmvb','mov','wmv','mod','asf','m1v','mp2','mpe','mpa','flv','3pg','vob'))){
		return true;
	}
	return false;
}

function isdocument($file){
	if(in_array(getext($file),array('doc','txt','pdf','ppt','pps','xls'))){
		return true;
	}
	return false;
}

function isimage($file){
	if(in_array(getext($file),array('jpg','bmp','gif','png','jpeg','tiff','tif','psd'))){
		return true;
	}
	return false;
}

function isspecimage($file){
	if(in_array(getext($file),array('tiff','tif','psd'))){
		return true;
	}
	return false;
}

function isweb($file){
	if(in_array(getext($file),array('htm','html'))){
		return true;
	}
	return false;
}

function iscode($file){
	if(in_array(getext($file),array('c','cpp','h','pl','py','php','phps','asp','aspx','css','jsp','sh','shar'))){
		return true;
	}
	return false;
}

function redirect($s){
	header('Location: '.$s);
	die();
}

function dirsize($d){
	if(is_dir($d)){
			//for windows
			$fsobj = new COM('Scripting.FileSystemObject');
			$file = $fsobj->GetFolder(realpath($d));
			return $file->Size;
	}else{
		return filesize($d);
	}
}

/* old method, slow and the cache dont check if info is renew
function dirsize($d){
	global $base,$cachedir;
	if(is_dir($d)){
		$sfile=$cachedir.$_GET['base'].'/'.sha1($d).'.siz';
		if($base[$_GET['base']][2] && ($ret=@file_get_contents($sfile))!==false){
			return $ret;
		}else{
			$z=0;
			$dp=opendir($d);
			while(false!==($e=readdir($dp))){
				if($e=='.' || $e=='..'){continue;}
				$z+=dirsize($d.'/'.$e);
			}
			closedir($dp);
			if($base[$_GET['base']][2]){
				file_put_contents($sfile,$z);
			}
			return $z;
		}
	}else{
		return filesize($d);
	}
}
*/

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
	$at=max(filemtime($a),filectime($a));
	$bt=max(filemtime($b),filectime($b));
	if($at>$bt){
		return true;
	}
	if(is_dir($a)){
		$a=fixdirpath($a);
		$dp=opendir($a);
		while(false!==($e=readdir($dp))){
			if($e=='.' || $e=='..'){continue;}
			if(newer($a.$e,$b)){
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
	@file_put_contents($sysroot.'gisedubrowser.log',$s."\r\n",FILE_APPEND);
	return $s;
}

function istoday($f){
	global $base;
	if($base[$_GET['base']][2]){
		return false;
	}
	if(istoday_r(r($f))){
		return true;
	}
	return false;
}

function istoday_r($f){
	global $_now;
	$ft=max(filemtime($f),filectime($f));
	if($_now-$ft<86400){
		return true;
	}
	if(is_dir($f)){
		$dp=opendir($f);
		while($e=readdir($dp)){
			if($e=='.' || $e=='..'){continue;}
			if(istoday_r($f.'/'.$e)){
				closedir($dp);
				touch($f,$ft);
				return true;
			}
		}
		closedir($dp);
	}
	return false;
}

function isarchive(){
	global $base;
	return ($base[$_GET['base']][2]);
}

function basedir($dir){
	return basename(rtrim($dir,'/\\'));
}

function tryindex($fs,$dir){
	global $cachedir;
	$bdir=basedir($dir);
	$index_file='';
	$ifile=$cachedir.$_GET['base'].'/'.sha1($dir).'.idx';
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
			if(!ufile_exists($cachedir.$_GET['base'])){
				umkdir($cachedir.$_GET['base']);
			}
			file_put_contents($ifile,$index_file);
		}
		return $index_file;
	}
}
?>