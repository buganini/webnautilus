<?php
set_time_limit(0);

include('func.php');

$rootdir=getbase($_GET['base']);
if(($dir=safepath($rootdir,$_GET['dir']))===false){
	redirect('index.php');
}
$hash=mkhash($rootdir.$dir);
$thash=$_GET['base'].'-'.$hash;
$hash=$_GET['base'].'/'.$hash;
$path=rtrim(joinpath($rootdir,$dir),'/');
$apath=explode('/',$path);

$bdir=array_pop($apath);
chdir(r(implode('/',$apath)));

$zfile=$bdir.'.7z';

#header('Content-type: application/x-zip-compressed');
#header('Content-Disposition: attachment; filename*=utf-8"'.preg_replace('![\\/?]!i','',$zfile).'"');
$cmd=$CFG['sevenzip'].' a -t7z '.escapeshellarg($CFG['cachedir'].$zfile).' '.escapeshellarg(r($bdir));
exe($cmd);
redirect($CFG['cacheurl'].$zfile);
#passthru($cmd);
?>
