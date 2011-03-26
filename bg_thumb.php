<?php
ignore_user_abort(true);
set_time_limit(0);
include('func.php');

$gmw=new GearmanWorker();
$gmw->addServer();
$gmw->addFunction("webnautilus","doThumb");

while($gmw->work()){
	while(1){
		$load=sys_getloadavg();
		if($load[0]>5){
			sleep(rand(5,20));
		}else{
			break;
		}
	}
}

function doThumb($job){
	global $CFG;
	$job=$job->workload();
	$job=unserialize($job);
	$rootdir=getbase($job['base']);
	$file=$job['file'];
	$hash=mkhash($rootdir.$file);
	$thash=$job['base'].'-'.$hash;
	$hash=$job['base'].'/'.$hash;

	$size=$job['size'];

	if(mylocked($thash)){
		return;
	}
	if(!ufile_exists($CFG['cachedir'].$job['base'])){
		umkdir($CFG['cachedir'].$job['base']);
	}
	if(isvideo($file)){
		if((newer($rootdir.$file,$CFG['cachedir'].$hash.'.flv') || newer($rootdir.$file,$CFG['cachedir'].$hash.'_L.jpg') || newer($rootdir.$file,$CFG['cachedir'].$hash.'_'.$size.'.jpg'))){
			mylock($thash);
				if(!file_exists($CFG['cachedir'].$hash.'.flv')){
					$cmd1=$CFG['mencoder'].' -vf scale=320:240 -ffourcc FLV1 -of lavf -lavfopts i_certify_that_my_video_stream_does_not_use_b_frames -ovc lavc -lavcopts vcodec=flv:vbitrate=200 -srate 22050 -oac lavc -lavcopts acodec=mp3:abitrate=56 '.escapeshellarg(r($rootdir.$file)).' -o '.escapeshellarg($CFG['tempdir'].$thash.'.flv');
					exe($cmd1);
					rmtry($CFG['cachedir'].$hash.'.flv');
					rmtry($CFG['cachedir'].$hash.'_L.jpg');
					rmtry($CFG['cachedir'].$hash.'.jpg');
					$cmd2=$CFG['yamdi'].' -i '.escapeshellarg($CFG['tempdir'].$thash.'.flv').' -o '.escapeshellarg($CFG['cachedir'].$hash.'.flv');
					exe($cmd2);
					uunlink($CFG['tempdir'].$thash.'.flv');
				}
				if(!file_exists($CFG['cachedir'].$hash.'_L.jpg')){
					$info=exe($CFG['mplayer'].' -identify -nosound -vc dummy -vo null '.escapeshellarg($CFG['cachedir'].$hash.'.flv'));
					preg_match('/ID_LENGTH=([0-9\\.]+)/s',$info,$len);
					$len=$len[1];
					$cmd3=$ffmpeg.' -i '.escapeshellarg($CFG['cachedir'].$hash.'.flv').' -y -f image2 -ss '.($len/2).' -t 0.001 -s 400x326 '.escapeshellarg($CFG['cachedir'].$hash.'_L.jpg');
					exe($cmd3);
				}
				$cmd4=$CFG['imagemagick_convert'].' -quality 70 -geometry '.$size.' '.escapeshellarg($CFG['cachedir'].$hash.'_L.jpg').' '.escapeshellarg($CFG['cachedir'].$hash.'_'.$size.'.jpg');
				exe($cmd4);
			myunlock($thash);
		}
	}elseif(isimage($file)){
		if(newer($rootdir.$file,$CFG['cachedir'].$hash.'_'.$size.'.jpg')){
			mylock($thash);
				if((!ufile_exists($CFG['tempdir'].$thash.'.jpg')) && (!(ufile_exists($CFG['tempdir'].$thash.'-0.jpg')))){
					$cmd=$CFG['imagemagick_convert'].' -quality 70 -geometry '.$size.' '.escapeshellarg(r($rootdir.$file)).' '.escapeshellarg($CFG['tempdir'].$thash.'.jpg');
					exe($cmd);
				}
				if(ufile_exists($CFG['tempdir'].$thash.'.jpg')){
					ucopy($CFG['tempdir'].$thash.'.jpg',$CFG['cachedir'].$hash.'_'.$size.'.jpg');
					uunlink($CFG['tempdir'].$thash.'.jpg');
				}elseif(ufile_exists($CFG['tempdir'].$thash.'-0.jpg')){
					ucopy($CFG['tempdir'].$thash.'-0.jpg',$CFG['cachedir'].$hash.'_'.$size.'.jpg');
					$i=0;
					while(ufile_exists($CFG['tempdir'].$thash.'-'.$i.'.jpg')){
						unlink($CFG['tempdir'].$thash.'-'.$i.'.jpg');
						++$i;
					}
				}
			myunlock($thash);
		}
	}elseif(isdocument($file)){
		if(newer($rootdir.$file,$CFG['cachedir'].$hash.'_'.$size.'.jpg')){
			mylock($thash);
				if(!ufile_exists($CFG['tempdir'].$thash.'.bmp')){
					$tfile=$CFG['tempdir'].$thash.'.'.getext($file);
					ucopy($rootdir.$file,$tfile);
					$pdf=$CFG['tempdir'].$thash.'.pdf';
					if(!ufile_exists($pdf)){
						$cmd=$CFG['unoconv'].' -f pdf '.escapeshellarg($tfile);
						exe($cmd);
					}
					$cmd=$CFG['ghostscript'].' -dNOPAUSE -dBATCH -dFirstPage=1 -dLastPage=1 -sDEVICE=bmp16 -sOutputFile='.$CFG['tempdir'].$thash.'.bmp '.escapeshellarg($pdf);
					#$cmd=$CFG['imagemagick_convert'].' '.escapeshellarg($pdf).' '.escapeshellarg($CFG['tempdir'].$thash.'.jpg');
					exe($cmd);
					rmtry($tfile);
					rmtry($pdf);
				}
				
				$cmd=$CFG['imagemagick_convert'].' -quality 70 -geometry '.$size.' '.escapeshellarg($CFG['tempdir'].$thash.'.bmp').' '.escapeshellarg($CFG['tempdir'].$thash.'.jpg');
				exe($cmd);
				rmtry($CFG['tempdir'].$thash.'.bmp');

				if(ufile_exists($CFG['tempdir'].$thash.'.jpg')){
					ucopy($CFG['tempdir'].$thash.'.jpg',$CFG['cachedir'].$hash.'_'.$size.'.jpg');
					uunlink($CFG['tempdir'].$thash.'.jpg');
				}
			myunlock($thash);
		}
	}elseif(isweb($file)){
		if(newer($rootdir.$file,$CFG['cachedir'].$hash.'_'.$size.'.jpg')){
			mylock($thash);
				if(!ufile_exists($CFG['tempdir'].$thash.'.png')){
					while(ufile_exists($CFG['tempdir'].'firefox.lock')){
						sleep(rand(5,15));
					}
					utouch($CFG['tempdir'].'firefox'.'.lock');
						file_put_contents($CFG['tempdir'].'firefox'.'.lock',$thash);
						$cmd=$firefox.' -no-remote -profile '.escapeshellarg($ffprofile).' -saveimage '.escapeshellarg('file:///'.r(urealpath($rootdir.$file))).' -savedelay 100 -witdh 1024';
						#logger($cmd);
						exe($cmd);
						ucopy($ffdownload.'shot.png',$CFG['tempdir'].$thash.'.png');
						uunlink($ffdownload.'shot.png');
					uunlink($CFG['tempdir'].'firefox'.'.lock');
					$cmd=$imagemagick_identify.' -format "%wx%h" '.escapeshellarg($CFG['tempdir'].$thash.'.png');
					list($w,$h)=explode('x',trim(exe($cmd)));
					$A4=297/210;
					if(($h/$w)>$A4){
						$h=intval($w*$A4);
						ucopy($CFG['tempdir'].$thash.'.png',$CFG['tempdir'].$thash.'_0.png');
						uunlink($CFG['tempdir'].$thash.'.png');
						$cmd=$imagemagick_convert.' -crop "'.$w.'x'.$h.'+0+0" '.escapeshellarg($CFG['tempdir'].$thash.'_0.png').' '.escapeshellarg($CFG['tempdir'].$thash.'.png');
						exe($cmd);
						uunlink($CFG['tempdir'].$thash.'_0.png');
					}
				}
				$cmd=$imagemagick_convert.' -quality 70 -geometry '.$size.' '.escapeshellarg($CFG['tempdir'].$thash.'.png').' '.escapeshellarg($CFG['cachedir'].$hash.'_'.$size.'.jpg');
				exe($cmd);
				rmtry($CFG['tempdir'].$thash.'.png');
			myunlock($thash);
		}
	}
}

?>
