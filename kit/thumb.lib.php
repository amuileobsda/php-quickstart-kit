<?php
function getThumb($o_path , $n_path , $mode , $width , $height , $fill_yn='Y' ){
	
	$src = array();
	$dst = array();

	$src['path'] = $o_path;
	$dst['path'] = $n_path;

	// 썸네일 이미지 갱신 기간 (1주일)
	if(file_exists($dst['path'])){
		if(mktime() - filemtime($dst['path']) < 60 * 60 * 24 * 7)	return array('bool' => true, 'src' => $dst['path']);
	}

	$imginfo = getimagesize($src['path']);
	$src['mime'] = $imginfo['mime'];

	// 원본 이미지 리소스 호출
	switch($src['mime']){
		case 'image/jpeg' :	$src['img'] = imagecreatefromjpeg($src['path']);	break;
		case 'image/gif' :	$src['img'] = imagecreatefromgif($src['path']);		break;
		case 'image/png' :	$src['img'] = imagecreatefrompng($src['path']);		break;
		case 'image/bmp' :	$src['img'] = imagecreatefrombmp($src['path']);		break;
		// mime 타입이 해당되지 않으면 return false
		default :		return array('bool' => false, 'msg' => '이미지 파일이 아닙니다.');						break;
	}

	// 원본 이미지 크기 / 좌표 초기값
	$src['w'] = $imginfo[0];
	$src['h'] = $imginfo[1];
	$src['x'] = 0;
	$src['y'] = 0;

	// 썸네일 이미지 좌표 초기값 설정
	$dst['x'] = 0;
	$dst['y'] = 0;

	// 썸네일 이미지 가로, 세로 비율 계산
	$dst['ratio']['w'] = $src['w'] / $width;
	$dst['ratio']['h'] = $src['h'] / $height;

	switch($mode){
		case 'ratio' :
			// 썸네일 이미지의 비율계산 (가로 == 세로)
			if($dst['ratio']['w'] == $dst['ratio']['h']){
				$dst['w'] = $width;
				$dst['h'] = $height;
			}
			// 썸네일 이미지의 비율계산 (가로 > 세로)
			elseif($dst['ratio']['w'] > $dst['ratio']['h']){
				$dst['w'] = $width;
				$dst['h'] = round(($width * $src['h']) / $src['w']);
			}
			// 썸네일 이미지의 비율계산 (가로 < 세로)
			elseif($dst['ratio']['w'] < $dst['ratio']['h']){
				$dst['w'] = round(($height * $src['w']) / $src['h']);
				$dst['h'] = $height;
			}

			if($fill_yn == 'Y'){
				$dst['canvas']['w'] = $width;
				$dst['canvas']['h'] = $height;
				$dst['x'] = $width > $dst['w'] ? ($width - $dst['w']) / 2 : 0;
				$dst['y'] = $height > $dst['h'] ? ($height - $dst['h']) / 2 : 0;
			}
			else{
				$dst['canvas']['w'] = $dst['w'];
				$dst['canvas']['h'] = $dst['h'];
			}
			break;
		case 'fixed' :
			// 썸네일 이미지의 비율계산 (가로 == 세로)
			if($dst['ratio']['w'] == $dst['ratio']['h']){
				$dst['w'] = $width;
				$dst['h'] = $height;
			}
			// 썸네일 이미지의 비율계산 (가로 > 세로)
			elseif($dst['ratio']['w'] > $dst['ratio']['h']){
				$dst['w'] = $src['w'] / $dst['ratio']['h'];
				$dst['h'] = $height;

				$src['x'] = ($dst['w'] - $width) / 2;
			}
			// 썸네일 이미지의 비율계산 (가로 < 세로)
			elseif($dst['ratio']['w'] < $dst['ratio']['h']){
				$dst['w'] = $width;
				$dst['h'] = $src['h'] / $dst['ratio']['w'];

				$dst['y'] = 0;
			}
			$dst['canvas']['w'] = $width;
			$dst['canvas']['h'] = $height;
			break;
	}

	// 썸네일 이미지 리소스 생성
	$dst['img'] = imagecreatetruecolor($dst['canvas']['w'], $dst['canvas']['h']);

	// 배경색 처리
	if(in_array($src['mime'], array('image/png', 'image/gif'))){
		// 배경 투명 처리 오류로 인한 , 투명화가 아닌 흰색처리로 수정


		//imagetruecolortopalette($dst['img'], false, 255);

		imagealphablending($dst['img'],false);
		imagesavealpha($dst['img'],true); 

		$bgcolor = imagecolorallocatealpha($dst['img'], 255, 255, 255, 127);
		imagefilledrectangle($dst['img'], 0, 0, $dst['canvas']['w'],$dst['canvas']['h'], $bgcolor);	
	}
	else{
		// 배경 흰색 처리
		$bgclear = imagecolorallocate($dst['img'],255,255,255);
		imagefill($dst['img'],0,0,$bgclear);
	}

	// 원본 이미지 썸네일 이미지 크기에 맞게 복사
	imagecopyresampled($dst['img'] ,$src['img'] ,$dst['x'] ,$dst['y'] ,$src['x'] ,$src['y'] ,$dst['w'] ,$dst['h'] ,$src['w'] ,$src['h']);

	// imagecopyresampled 함수 사용 불가시 사용
	//imagecopyresized($dst['img'] ,$src['img'] ,$dst['x'] ,$dst['y'] ,$src['x'] ,$src['y'] ,$dst['w'] ,$dst['h'] ,$src['w'] ,$src['h']);

	ImageInterlace($dst['img']);

	// 썸네일 이미지 리소스를 기반으로 실제 이미지 생성
	switch($src['mime']){
		case 'image/jpeg' :	imagejpeg($dst['img'], $dst['path']);	break;
		case 'image/gif' :	imagegif($dst['img'], $dst['path']);	break;
		case 'image/png' :	imagepng($dst['img'], $dst['path']);	break;
		case 'image/bmp' :	imagebmp($dst['img'], $dst['path']);	break;
	}

	// 원본 이미지 리소스 종료
	imagedestroy($src['img']);
	// 썸네일 이미지 리소스 종료
	imagedestroy($dst['img']);

	// 썸네일 파일경로 존재 여부 확인후 리턴
	return file_exists($dst['path']) ? array('bool' => true, 'src' => $dst['path']) : array('bool' => false, 'msg' => '파일 생성에 실패하였습니다.');
}
?>
