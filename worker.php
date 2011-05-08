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

if(mylocked($thash)) exit;

if(!ufile_exists($CFG['cachedir'].$job['base'])){
	umkdir($CFG['cachedir'].$job['base']);
}
if(isvideo($file)){
	if((newer($rootdir.$file,$CFG['cachedir'].$hash.'.mp4') || newer($rootdir.$file,$CFG['cachedir'].$hash.'_L.jpg') || newer($rootdir.$file,$CFG['cachedir'].$hash.'_'.$size.'.jpg'))){
		mylock($thash) || exit;
			if(!file_exists($CFG['cachedir'].$hash.'.mp4')){
				$cmd1=$CFG['ffmpeg'].' -y -i '.escapeshellarg(r($rootdir.$file)).' -acodec libfaac -ab 96 -bufsize 500k -maxrate 500k -vcodec libx264 -vpre fast -crf 22 -threads 0 '.escapeshellarg($CFG['tempdir'].$thash.'.mp4');
				exe($cmd1);
				rmtry($CFG['cachedir'].$hash.'.mp4');
				rmtry($CFG['cachedir'].$hash.'_L.jpg');
				rmtry($CFG['cachedir'].$hash.'_'.$size.'.jpg');
				$cmd2=$CFG['yamdi'].' -i '.escapeshellarg($CFG['tempdir'].$thash.'.mp4').' -o '.escapeshellarg($CFG['cachedir'].$hash.'.mp4');
				exe($cmd2);
				if(!file_exists($CFG['cachedir'].$hash.'.mp4')){
					copy($CFG['tempdir'].$thash.'.mp4',$CFG['cachedir'].$hash.'.mp4');
				}
				uunlink($CFG['tempdir'].$thash.'.mp4');
				touch($CFG['cachedir'].$hash.'.mp4',ufiletime($rootdir.$file),$_now);
			}
			if(!file_exists($CFG['cachedir'].$hash.'_L.jpg')){
				$cmd3=$CFG['mplayer'].' -identify -nosound -vc dummy -vo null '.escapeshellarg($CFG['cachedir'].$hash.'.mp4');
				$info=exe($cmd3);
				preg_match('/ID_LENGTH=([0-9\\.]+)/s',$info,$len);
				$len=$len[1];
				$cmd4=$CFG['ffmpeg'].' -i '.escapeshellarg($CFG['cachedir'].$hash.'.mp4').' -y -ss '.($len/2).' -s 680x480 '.escapeshellarg($CFG['cachedir'].$hash.'_L.jpg');
				exe($cmd4);
				touch($CFG['cachedir'].$hash.'_L.jpg',ufiletime($rootdir.$file),$_now);
			}
			$cmd5=$CFG['imagemagick_convert'].' -quality 70 -geometry '.$size.' '.escapeshellarg($CFG['cachedir'].$hash.'_L.jpg').' '.escapeshellarg($CFG['cachedir'].$hash.'_'.$size.'.jpg');
			exe($cmd5);
			touch($CFG['cachedir'].$hash.'_'.$size.'.jpg',ufiletime($rootdir.$file),$_now);
		myunlock($thash);
	}
}elseif(isaudio($file)){
	if(newer($rootdir.$file,$CFG['cachedir'].$hash.'.mp3')){
		mylock($thash) || exit;
		if(ismidi($file)){
			$cmd=$CFG['timidity'].' -Ow -o '.escapeshellarg(r($CFG['tempdir'].$thash.'.wav')).' '.escapeshellarg(r($rootdir.$file));
			exe($cmd);
			$cmd=$CFG['ffmpeg'].' -y -i '.escapeshellarg(r($CFG['tempdir'].$thash.'.wav')).' -b 96k '.escapeshellarg($CFG['cachedir'].$hash.'.mp3');
			exe($cmd);
			uunlink($CFG['tempdir'].$thash.'.wav');
		}else{
			$cmd=$CFG['ffmpeg'].' -y -i '.escapeshellarg(r($rootdir.$file)).' '.escapeshellarg($CFG['cachedir'].$hash.'.mp3');
			exe($cmd);
		}
		touch($CFG['cachedir'].$hash.'.mp3',ufiletime($rootdir.$file),$_now);
		myunlock($thash);		
	}	
}elseif(isimage($file)){
	if(newer($rootdir.$file,$CFG['cachedir'].$hash.'_'.$size.'.jpg')){
		mylock($thash) || exit;
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
			touch($CFG['cachedir'].$hash.'_'.$size.'.jpg',ufiletime($rootdir.$file),$_now);
		myunlock($thash);
	}
}elseif(isdocument($file)){
	if(newer($rootdir.$file,$CFG['cachedir'].$hash.'_'.$size.'.jpg')){
		mylock($thash) || exit;
			if(!ufile_exists($CFG['tempdir'].$thash.'.bmp')){
				$tfile=$CFG['tempdir'].$thash.'.'.getext($file);
				if(getext($file)=='txt'){
					$cmd=$CFG['enca'].' -L '.$RTI['locale'].' -x UTF-8 < '.escapeshellarg($rootdir.$file).' > '.escapeshellarg($tfile);
					exe($cmd);
				}else{
					ucopy($rootdir.$file,$tfile);
				}
				$pdf=$CFG['tempdir'].$thash.'.pdf';
				if(!ufile_exists($pdf)){
					$cmd=$CFG['libreoffice'].' -norestore -convert-to pdf -outdir '.escapeshellarg($CFG['tempdir']).' '.escapeshellarg($tfile);
					exe($cmd);
				}
				copy($pdf,$CFG['cachedir'].$hash.'.pdf');
				touch($CFG['cachedir'].$hash.'.pdf',ufiletime($rootdir.$file),$_now);
				$cmd=$CFG['ghostscript'].' -dNOPAUSE -dBATCH -dFirstPage=1 -dLastPage=1 -sDEVICE=bmp16 -sOutputFile='.$CFG['tempdir'].$thash.'.bmp '.escapeshellarg($pdf);
				exe($cmd);
				rmtry($tfile);
				rmtry($pdf);
			}
			
			$cmd=$CFG['imagemagick_convert'].' -quality 70 -geometry '.$size.' '.escapeshellarg($CFG['tempdir'].$thash.'.bmp').' '.escapeshellarg($CFG['tempdir'].$thash.'.jpg');
			exe($cmd);
			rmtry($CFG['tempdir'].$thash.'.bmp');
			if(ufile_exists($CFG['tempdir'].$thash.'.jpg')){
				ucopy($CFG['tempdir'].$thash.'.jpg',$CFG['cachedir'].$hash.'_'.$size.'.jpg');
				touch($CFG['cachedir'].$hash.'_'.$size.'.jpg',ufiletime($rootdir.$file),$_now);
				uunlink($CFG['tempdir'].$thash.'.jpg');
			}
		myunlock($thash);
	}
}elseif(isweb($file)){
	if(newer($rootdir.$file,$CFG['cachedir'].$hash.'_'.$size.'.jpg')){
		mylock($thash) || exit;
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
