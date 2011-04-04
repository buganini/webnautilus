<?php
include('func.php');
chdir($sysroot);
foreach($base as $b){
	$rootdir=getbase($b[0]);
	udirsize($rootdir);
}
?>
