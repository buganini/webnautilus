<?php
include('func.php');
$rootdir=getbase($_GET['base']);
if(($file=safepath($rootdir,$_GET['file']))===false){
	die();
}
$hash=$_GET['base'].'/'.mkhash($rootdir.$file);

if(ufile_exists($CFG['cachedir'].$hash.'.pdf')){
	redirect('http://docs.google.com/viewer?url='.urlencode($CFG['url'].$CFG['cacheurl'].$hash.'.pdf?time='.ufiletime($CFG['cachedir'].$hash.'.pdf')));
}
?>
