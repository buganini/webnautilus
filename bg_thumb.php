<?php
ignore_user_abort(true);
set_time_limit(0);
include('func.php');

$job=file_get_contents('php://stdin');
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
exit;
	if((newer($rootdir.$file,$CFG['cachedir'].$hash.'.mp4') || newer($rootdir.$file,$CFG['cachedir'].$hash.'_L.jpg') || newer($rootdir.$file,$CFG['cachedir'].$hash.'_'.$size.'.jpg'))){
		mylock($thash);
			if(!file_exists($CFG['cachedir'].$hash.'.mp4')){
#				$cmd1=$CFG['mencoder'].' '.escapeshellarg(r($rootdir.$file)).' -of lavf -lavfopts format=mp4 -sws 9 -af volnorm -srate 48000 -channels 2 -vf-add scale=480:272,harddup -oac faac -faacopts br=96:mpeg=4:object=2:raw -ovc x264 -ffourcc H264 -x264encopts crf=22:threads=2:level_idc=30:bframes=3:frameref=2:global_header:partitions=all -o '.escapeshellarg($CFG['tempdir'].$thash.'.mp4');
#				$cmd1=$CFG['ffmpeg'].' -y -i '.escapeshellarg(r($rootdir.$file)).' -acodec libfaac -ar 44100 -ab 96k -vcodec libx264 -level 41 -crf 25 -bufsize 20000k -maxrate 25000k -g 250 -r 20 -s 1280x720 '.escapeshellarg($CFG['tempdir'].$thash.'.mp4');
#				$cmd1=$CFG['mencoder'].' -of lavf -lavfopts format=mp4 -oac lavc -ovc lavc -lavcopts aglobal=1:vglobal=1:acodec=libfaac:abitrate=128:vcodec=mpeg4:keyint=25 -ofps 25 -af lavcresample=44100 -vf harddup,scale=640:480 -mc 0 -noskip '.escapeshellarg(r($rootdir.$file)).' -o '.escapeshellarg($CFG['tempdir'].$thash.'.mp4');
				$cmd1=$CFG['ffmpeg'].' -y -i '.escapeshellarg(r($rootdir.$file)).' -vcodec libx264 -qmin 10 -qmax 51 -qdiff 4 -me_range 16 -keyint_min 25 -qcomp 0.6 -b 400K '.escapeshellarg($CFG['tempdir'].$thash.'.mp4');
echo $cmd1."\n\n";
#				exe($cmd1);
				rmtry($CFG['cachedir'].$hash.'.mp4');
				rmtry($CFG['cachedir'].$hash.'_L.jpg');
				rmtry($CFG['cachedir'].$hash.'.jpg');
				$cmd2=$CFG['yamdi'].' -i '.escapeshellarg($CFG['tempdir'].$thash.'.mp4').' -o '.escapeshellarg($CFG['cachedir'].$hash.'.mp4');
echo $cmd2."\n\n";
				exe($cmd2);
				if(!file_exists($CFG['cachedir'].$hash.'.mp4')){
					copy($CFG['tempdir'].$thash.'.mp4',$CFG['cachedir'].$hash.'.mp4');
				}
#				uunlink($CFG['tempdir'].$thash.'.mp4');
			}
			if(!file_exists($CFG['cachedir'].$hash.'_L.jpg')){
				$cmd3=$CFG['mplayer'].' -identify -nosound -vc dummy -vo null '.escapeshellarg($CFG['cachedir'].$hash.'.mp4');
echo $cmd3."\n\n";
				$info=exe($cmd3);
				preg_match('/ID_LENGTH=([0-9\\.]+)/s',$info,$len);
				$len=$len[1];
				$cmd4=$CFG['ffmpeg'].' -i '.escapeshellarg($CFG['cachedir'].$hash.'.mp4').' -y -f image2 -ss '.($len/2).' -t 0.001 -s 960x720 '.escapeshellarg($CFG['cachedir'].$hash.'_L.jpg');
echo $cmd4."\n\n";
				exe($cmd4);
			}
			$cmd5=$CFG['imagemagick_convert'].' -quality 70 -geometry '.$size.' '.escapeshellarg($CFG['cachedir'].$hash.'_L.jpg').' '.escapeshellarg($CFG['cachedir'].$hash.'_'.$size.'.jpg');
echo $cmd5."\n\n";
			exe($cmd5);
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
				copy($pdf,$CFG['cachedir'].$hash.'.pdf');
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
?>
