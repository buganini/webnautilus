<?php
include('func.php');

$lockn=count(scandir($lockdir))-2;
list($dmlast,$dmtoken)=explode("\t",@file_get_contents($tempdir.'daemon.lock'));
?><html>
<head>
<meta http-equiv="refresh" content="10" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="common.css" rel="stylesheet" type="text/css" />
<style type="text/css">
th{ text-align:right; }
td{ text-align:left; }
</style>
</head>
<body>
<table>
<tr><th>Lock</th><td><?php echo $lockn;?></td></tr>
<tr><th>Firefox</th><td><?php echo file_exists($tempdir.'firefox.lock')?'Locked':'Ready';?></td></tr>
<tr><th>Daemon</th><td><?php echo ftime(time()-$dmlast);?> (Token: <?echo $dmtoken;?>)</td></tr>
<tr><th>Cache</th><td><?php echo fsize(dirsize($cachedir));?> used/<?echo fsize(disk_free_space($cachedir));?> free</td></tr>
<tr><th>Temp</th><td><?php echo fsize(dirsize($tempdir));?> used/<?echo fsize(disk_free_space($tempdir));?> free</td></tr>
<tr><th>JobQueue</th><td><?php echo count(scandir($jobdir))-2;?></td></tr>
<tr><th>Concurrency</th><td><?php
$shm=shm_attach($shmid);
var_dump(shm_get_var($shm,0));
shm_detach($shm);
?></td></tr>
</table>
</body>
</html>
