<?php
set_time_limit(0);

include('func.php');

$rootdir=getbase($_GET['base']);
if(($dir=fixdirpath(safepath($rootdir,$_GET['dir'])))===false){
	redirect('index.php');
}
$hash=mkhash($rootdir.$dir);
$thash=$_GET['base'].'-'.$hash;
$hash=$_GET['base'].'/'.$hash;
$path=rtrim($rootdir.$dir,'/');
$apath=explode('/',$path);

$bdir=array_pop($apath);
chdir(r(implode('/',$apath)));

$zfile=$bdir.'-'.$thash.'.tar';

header('Content-type: application/x-tar');
header('Content-Disposition: attachment; filename="'.preg_replace('![\\/?]!i','',$zfile).'"');
$cmd=$CFG['sevenzip'].' a -tTar -so dummy '.escapeshellarg(r($bdir));
exe($cmd);
passthru($cmd);
?>
