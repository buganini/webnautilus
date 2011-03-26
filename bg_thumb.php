<?php
ignore_user_abort(true);
set_time_limit(0);
include('func.php');

$shm=shm_attach($shmid);
if(($c=@shm_get_var($shm,0))===false){
	$c=0;
}
if($c>=$concurrency){
	shm_detach($shm);
	die('System busy');
}elseif(shm_put_var($shm,0,++$c)===false){
	shm_detach($shm);
	die('Unable to write on shm');
}
shm_detach($shm);
function ctimecmp($a,$b){
	global $jobdir;
	return filectime($jobdir.$a)-filectime($jobdir.$b);
}

function getjob(){
	global $jobdir;
	$jobs=array();
	$l=scandir($jobdir);
	usort($l,'ctimecmp');
	foreach($l as $n){
		if($n!='.' && $n!='..'){
			$jobf=$jobdir.$n;
			if(file_exists($jobf)){
				$job=file_get_contents($jobf);
				rmtry($jobf);
				if($job){
					$job=unserialize($job);
					return $job;
				}
			}
		}
	}
	return false;
}
	
while($job=getjob()){

	$rootdir=getbase($job['base']);
	$file=$job['file'];
	$hash=mkhash($rootdir.$file);
	$thash=$job['base'].'-'.$hash;
	$hash=$job['base'].'/'.$hash;

	$size=$job['size'];

	if(file_exists($lockdir.$thash.'.lock')){
		continue;
	}
	if(!ufile_exists($cachedir.$job['base'])){
		umkdir($cachedir.$job['base']);
	}
	if(isvideo($file)){
		if((newer($rootdir.$file,$cachedir.$hash.'.flv') || newer($rootdir.$file,$cachedir.$hash.'_L.jpg') || newer($rootdir.$file,$cachedir.$hash.'_'.$size.'.jpg'))){
			touch($lockdir.$thash.'.lock');
				if(!file_exists($cachedir.$hash.'.flv')){
					$cmd1=$mencoder.' -vf scale=320:240 -ffourcc FLV1 -of lavf -lavfopts i_certify_that_my_video_stream_does_not_use_b_frames -ovc lavc -lavcopts vcodec=flv:vbitrate=200 -srate 22050 -oac lavc -lavcopts acodec=mp3:abitrate=56 '.escapeshellarg(r($rootdir.$file)).' -o '.escapeshellarg($tempdir.$thash.'.flv');
					exe($cmd1);
					rmtry($cachedir.$hash.'.flv');
					rmtry($cachedir.$hash.'_L.jpg');
					rmtry($cachedir.$hash.'.jpg');
					$cmd2=$yamdi.' -i '.escapeshellarg($tempdir.$thash.'.flv').' -o '.escapeshellarg($cachedir.$hash.'.flv');
					exe($cmd2);
					uunlink($tempdir.$thash.'.flv');
				}
				if(!file_exists($cachedir.$hash.'_L.jpg')){
					$info=exe($mplayer.' -identify -nosound -vc dummy -vo null '.escapeshellarg($cachedir.$hash.'.flv'));
					preg_match('/ID_LENGTH=([0-9\\.]+)/s',$info,$len);
					$len=$len[1];
					$cmd3=$ffmpeg.' -i '.escapeshellarg($cachedir.$hash.'.flv').' -y -f image2 -ss '.($len/2).' -t 0.001 -s 400x326 '.escapeshellarg($cachedir.$hash.'_L.jpg');
					exe($cmd3);
				}
				$cmd4=$imagemagick_convert.' -quality 70 -geometry '.$size.' '.escapeshellarg($cachedir.$hash.'_L.jpg').' '.escapeshellarg($cachedir.$hash.'_'.$size.'.jpg');
				exe($cmd4);
			unlink($lockdir.$thash.'.lock');
		}
	}elseif(isimage($file)){
		if(newer($rootdir.$file,$cachedir.$hash.'_'.$size.'.jpg')){
			utouch($lockdir.$thash.'.lock');
				if((!ufile_exists($tempdir.$thash.'.jpg')) && (!(ufile_exists($tempdir.$thash.'-0.jpg')))){
					$cmd=$imagemagick_convert.' -quality 70 -geometry '.$size.' '.escapeshellarg(r($rootdir.$file)).' '.escapeshellarg($tempdir.$thash.'.jpg');
					exe($cmd);
				}
				if(ufile_exists($tempdir.$thash.'.jpg')){
					ucopy($tempdir.$thash.'.jpg',$cachedir.$hash.'_'.$size.'.jpg');
					uunlink($tempdir.$thash.'.jpg');
				}elseif(ufile_exists($tempdir.$thash.'-0.jpg')){
					ucopy($tempdir.$thash.'-0.jpg',$cachedir.$hash.'_'.$size.'.jpg');
					$i=0;
					while(ufile_exists($tempdir.$thash.'-'.$i.'.jpg')){
						unlink($tempdir.$thash.'-'.$i.'.jpg');
						++$i;
					}
				}
			uunlink($lockdir.$thash.'.lock');
		}
	}elseif(isdocument($file)){
		if(newer($rootdir.$file,$cachedir.$hash.'_'.$size.'.jpg')){
			utouch($lockdir.$thash.'.lock');
				if(!ufile_exists($tempdir.$thash.'.bmp')){
					$tfile=$tempdir.$thash.'.'.getext($file);
					ucopy($rootdir.$file,$tfile);
					$pdf=$tempdir.$thash.'.pdf';
					if(!ufile_exists($pdf)){
						$cmd=$unoconv.' -f pdf '.escapeshellarg($tfile);
						exe($cmd);
					}
					$cmd=$ghostscript.' -dNOPAUSE -dBATCH -dFirstPage=1 -dLastPage=1 -sDEVICE=bmp16 -sOutputFile='.$tempdir.$thash.'.bmp '.escapeshellarg($pdf);
					#$cmd=$imagemagick_convert.' '.escapeshellarg($pdf).' '.escapeshellarg($tempdir.$thash.'.jpg');
					exe($cmd);
					rmtry($tfile);
					rmtry($pdf);
				}
				
				$cmd=$imagemagick_convert.' -quality 70 -geometry '.$size.' '.escapeshellarg($tempdir.$thash.'.bmp').' '.escapeshellarg($tempdir.$thash.'.jpg');
				exe($cmd);
				rmtry($tempdir.$thash.'.bmp');

				if(ufile_exists($tempdir.$thash.'.jpg')){
					ucopy($tempdir.$thash.'.jpg',$cachedir.$hash.'_'.$size.'.jpg');
					uunlink($tempdir.$thash.'.jpg');
				}

			uunlink($lockdir.$thash.'.lock');
		}
	}elseif(isweb($file)){
		if(newer($rootdir.$file,$cachedir.$hash.'_'.$size.'.jpg')){
			utouch($lockdir.$thash.'.lock');
				if(!ufile_exists($tempdir.$thash.'.png')){
					while(ufile_exists($tempdir.'firefox.lock')){
						sleep(rand(5,15));
					}
					utouch($tempdir.'firefox'.'.lock');
						file_put_contents($tempdir.'firefox'.'.lock',$thash);
						$cmd=$firefox.' -no-remote -profile '.escapeshellarg($ffprofile).' -saveimage '.escapeshellarg('file:///'.r(urealpath($rootdir.$file))).' -savedelay 100 -witdh 1024';
						#logger($cmd);
						exe($cmd);
						ucopy($ffdownload.'shot.png',$tempdir.$thash.'.png');
						uunlink($ffdownload.'shot.png');
					uunlink($tempdir.'firefox'.'.lock');
					$cmd=$imagemagick_identify.' -format "%wx%h" '.escapeshellarg($tempdir.$thash.'.png');
					list($w,$h)=explode('x',trim(exe($cmd)));
					$A4=297/210;
					if(($h/$w)>$A4){
						$h=intval($w*$A4);
						ucopy($tempdir.$thash.'.png',$tempdir.$thash.'_0.png');
						uunlink($tempdir.$thash.'.png');
						$cmd=$imagemagick_convert.' -crop "'.$w.'x'.$h.'+0+0" '.escapeshellarg($tempdir.$thash.'_0.png').' '.escapeshellarg($tempdir.$thash.'.png');
						exe($cmd);
						uunlink($tempdir.$thash.'_0.png');
					}
				}
				$cmd=$imagemagick_convert.' -quality 70 -geometry '.$size.' '.escapeshellarg($tempdir.$thash.'.png').' '.escapeshellarg($cachedir.$hash.'_'.$size.'.jpg');
				exe($cmd);
				rmtry($tempdir.$thash.'.png');
			uunlink($lockdir.$thash.'.lock');
		}
	}
}

$shm=shm_attach($shmid);
$c=@shm_get_var($shm,0);
if($c===false || $c<=0){
	$c=0;
}else{
	$c-=1;
}
shm_put_var($shm,0,$c);
shm_detach($shm);
?>
