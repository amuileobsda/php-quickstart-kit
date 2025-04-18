<?
class files
{

	//==========================================
	// 생성자
	//==========================================
	public function __construct(){
	
	}

	//==============================================//
	// 파일 업로드									//
	// up_dir			::	업로드 디렉토리			//
	// name				::	배열 이름				//
	// upload			::	리턴 값 배열			//
	// bo_upload_size	::	업로드 사이즈			//
	//==============================================//
	public function uploadFiles($up_dir, $name, $upload, $memo = Array(), $bo_upload_size = 10){

		$bo_upload_size = $bo_upload_size * (1024 * 1024);

		global $md, $_FILES, $config, $board_cd;

		if(count($_FILES) > 0){
			@mkdir($md["filedata_path"] . "/" . $up_dir, 0703);	
			@chmod($md["filedata_path"] . "/" . $up_dir, 0703);
		}

		$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));
		$file_upload_msg = "";
		$iLoopCount = 0;

		for ($i = 0 ; $i < count($_FILES[$name]["name"]); $i++){
			
			if(!empty($_FILES[$name]["tmp_name"][$i])){
				$tmp_file	= $_FILES[$name]["tmp_name"][$i];
				$file_name  = $_FILES[$name]["name"][$i];
				$file_size  = $_FILES[$name]["size"][$i];
				$file_memo	= $memo[$i];
				$file_ext	=  $this->getExt($file_name);
				
				// 프로그램 실행 확장자 체크
				if($file_ext == "pl" || $file_ext == "cgi" || $file_ext == "shtm" || $file_ext == "phtm" || $file_ext == "inc" || $file_ext == "html" || $file_ext == "htm" || $file_ext == "php" || $file_ext == "php3"){
					$file_upload_msg .= "\'{$file_ext}\' 는 업로드 불가능 확장자입니다.\\n";
				}

				if($board_cd != ""){ // 게시판 값이 있을 경우 허용 확장자 체크
					
					$sqlString = "Select file_ext From md_board_mng Where del_dt is null And board_cd = '$board_cd'";
					$row = sql_fetch($sqlString);
					$board_ext = explode("|", $row["file_ext"]);

					if(!in_array($file_ext, $board_ext)){
						$file_upload_msg .= "해당 게시판에 \'{$file_ext}\' 확장자는 업로드 불가능합니다.\\n";
					}

				}



				if ($file_name){

					// 서버 설정 용량 값보다 큰 파일 체크
					if ($_FILES[$name]["error"][$i] == 1){
							$file_upload_msg .= "\'{$file_name}\' 파일의 용량이 서버에 설정($upload_max_filesize)된 값보다 크므로 업로드 할 수 없습니다.\\n";
							continue;
					}else if ($_FILES[$name]["error"][$i] != 0){ //파일 업로드 에러 체크
							$file_upload_msg .= "\'{$file_name}\' 파일이 정상적으로 업로드 되지 않았습니다.\\n";
							continue;
					}
				}
				
				//사이트 설정 용량 체크
				if ($file_size > $bo_upload_size){
					$file_upload_msg .= "\'{$file_name}\' 파일의 용량(".number_format($file_size)." 바이트)이 게시판에 설정(".number_format($bo_upload_size)." 바이트)된 값보다 크므로 업로드 하지 않습니다.\\n";
					continue;
				}

				// 에러 메시지 있으면 업로드 정지
				if(!empty($file_upload_msg)){
					alert($file_upload_msg);
					break;
				}

				if(is_uploaded_file($tmp_file)){

					$upload[$i]["file_ord"] = $i;
					$upload[$i]["file_name"] = $file_name;
					$upload[$i]["file_size"] = $file_size;

					// 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
					$file_name = preg_replace("/\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $file_name);

					//파일명 난수 처리
					shuffle($chars_array);
					$shuffle = implode("", $chars_array);

					// 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다.
					$upload[$i]["file_path"] = date("YmdHis").'_'.substr($shuffle,0,8).'_'.str_replace('%', '', urlencode(str_replace(' ', '_', $file_name))); 

					//업로드 경로 
					$dest_file = $md["filedata_path"] . "/" . $up_dir . "/" . $upload[$i]["file_path"];

					if($file_ext != "png"){
						$exifData = exif_read_data($tmp_file); 
						if($exifData['Orientation'] == 6){ 
							// 시계방향으로 90도 돌려줘야 정상인데 270도 돌려야 정상적으로 출력됨 
							$degree = 270; 
						}else if($exifData['Orientation'] == 8){ 
							// 반시계방향으로 90도 돌려줘야 정상 
							$degree = 90; 
						}else if($exifData['Orientation'] == 3){ 
							$degree = 180; 
						}
					}else{
						$degree = "";
					}
					if($degree){ 
						@ini_set('gd.jpeg_ignore_warning', 1);
						if($exifData[FileType] == 1){
							$source = imagecreatefromgif($tmp_file);
							$source = imagerotate($source , $degree, 0);
							imagegif($source, $dest_file);
						}else if($exifData[FileType] == 2){
							$source = imagecreatefromjpeg($tmp_file);
							$source = imagerotate($source , $degree, 0);
							imagejpeg($source, $dest_file);
						}else if($exifData[FileType] == 3){
							$source = imagecreatefrompng($tmp_file);
							$source = imagerotate($source , $degree, 0);
							imagepng($source, $dest_file);
						}
						imagedestroy($source); 
					}else{ 
						// 업로드가 안된다면 에러메세지 출력하고 죽어버립니다. 
						$error_code = move_uploaded_file($tmp_file, $dest_file) or die($_FILES[$name]["error"][$i]);
					}
					$mime_type = mime_content_type($dest_file);
					$exp_mime = explode("/", $mime_type);
					if($exp_mime[0] == "text"){
						if($exp_mime[1] == "x-php" || $exp_mime[1] == "php"){
							unlink($dest_file);
							alert("허가된 파일이 아니라 파일이 삭제 되었습니다.");
							exit;
						}
					}

					// 올라간 파일의 퍼미션을 변경합니다.
					chmod($dest_file, 0404);
					$upload[$i]["image"]		= @getimagesize($dest_file);
					$upload[$i]["file_memo"]	= $file_memo;


				}

				$iLoopCount++;
			}

		}

		return $upload;

	}


	//==============================================//
	// 파일 DB 로드									//
	// ref_table		::	파일 첨부 테이블		//
	// ref_no			::	파일 번호				//
	// noImage			::	대체 이미지				//
	//==============================================//
	public function getFiles($ref_table = '', $ref_no = '', $noImage = '/images/common/noImages.jpg'){

		global $md, $config;

		$addSqlString = ($ref_table != "")?" AND ref_table='$ref_table' ":"";
		$addSqlString .= ($ref_no != "")?" AND ref_no='$ref_no' ":"";

		$file["count"] = 0;
		$sqlString = "Select * From md_files Where del_dt is null " . $addSqlString;
		$result = sql_query($sqlString);

		while($row = sql_fetch_array($result)){
			
			$file_ord							=		$row["file_ord"];
			$file[$file_ord]["file_name"]		=		$row["file_name"];
			$file[$file_ord]["file_size"]		=		$row["file_size"];
			$file[$file_ord]["folder_path"]		=		$md["filedata_path"] . "/" . $ref_table;
			$file[$file_ord]["file_full_name"]	=		$file[$file_ord]["folder_path"] . $row["file_path"];
			$file[$file_ord]["downLoad"]		=		"/include/download.php?file_seq=" . $row["file_seq"];
			$file[$file_ord]["file_memo"]		=		$row["file_memo_1"];

			$del_file_seq = "<input style='vertical-align:middle;' type='checkbox' id='setFile_" . $row["file_seq"] . "' name='del_file_seq[]' id='" . $file[$file_ord]["file_memo"] . "' value='" . $row["file_seq"] . "'><label for='setFile_" . $row["file_seq"] . "'>삭제</label> , ";
			$del_file_seq .= "<a href='" . $file[$file_ord]["downLoad"] . "' title='" . $file[$file_ord]["file_memo"] . "'>" . $file[$file_ord]["file_name"] . " [".$this->format_bytes($file[$file_ord]["file_size"])."]</a>";
			
			$file[$file_ord]["del_file"]		=		$del_file_seq;
			$file[$file_ord]["file_icon"]		=		$this->getExtIcon($row["file_name"]);
			$file[$file_ord]["file_ext"]		=		$this->getExt($row["file_name"]);
			$file[$file_ord]["preview"]			=		"/include/image.php?src=/$ref_table/".rawurlencode($row["file_path"]);
			$file[$file_ord]["filedata"]		=		"/filedata/$ref_table/".rawurldecode($row["file_path"]);
			$file[$file_ord]["list_icon"]		=		"<a href='".$file[$file_ord]["downLoad"]."' title='다운로드'><img style='vertical-align:middle;' src='/images/file_icon/" . $file[$file_ord]["file_icon"] . ".gif' alt='다운로드' /></a>";
			$file[$file_ord]["view_icon"]		=		"<a href='".$file[$file_ord]["downLoad"]."' title='다운로드'><img style='vertical-align:middle;' src='/images/file_icon/" . $file[$file_ord]["file_icon"] . ".gif' alt='다운로드' />" . $file[$file_ord]["file_name"] . "</a>";
			if($row["width"] > 700){
				$file[$file_ord]["preview_img"]		=		"<img src='" . $file[$file_ord]["filedata"] . "' alt='" . $file[$file_ord]["file_memo"] . "' style='width:700px;' />";
			}else{
				$file[$file_ord]["preview_img"]		=		"<img src='" . $file[$file_ord]["filedata"] . "' alt='" . $file[$file_ord]["file_memo"] . "' />";
			}
			
		

			$file[count]++;
		
		}

		return $file;

	}

	//===============================================//
	// 확장자 리턴									 //
	// fileName		::	파일 이름					 //
	//===============================================//
	public function getExt($fileName){
		$ext = explode('.', $fileName); 
		$ext = end($ext); 
		return $ext;
	}

	//===============================================//
	// 확장자 아이콘 리턴							 //
	// fileName		::	파일 이름					 //
	//===============================================//
	public function getExtIcon($fileName){
		
		$file_ext = $this->getExt($fileName);
		$file_ext = strtolower($file_ext);
		$file_icon = "unknown";

		$aIcon = array(
			"ai","arj","avi","bmp","dll",
			"doc","eps","exe","fla","gif",
			"gz","htm","html","hwp","jpg",
			"lzh","mid","mov","mp3","mpeg",
			"pdf","ppt","pds","swf","tar",
			"ttf","txt","unknown","wav","xls","zip"
		);	

		if(in_array($file_ext, $aIcon)){
			$file_icon = $file_ext;
		}

		$file_icon = "disk";

		return $file_icon;

	}

	//===============================================//
	// 용량 리턴									 //
	// size			::	순수 용량					 //
	//===============================================//
	public function format_bytes($size) {
		$units = array(' B', ' KB', ' MB', ' GB', ' TB');
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
		return round($size, 2).$units[$i];
	}

	public function deleteFiles($del_file_seq){
		
		if(empty($del_file_seq))
			return ;
		foreach($del_file_seq as $key => $value){
			$file_data=sql_fetch("select * from md_files where file_seq = '".$value."' ");
			//순서 재배열 
			$sql=sql_query("select * from md_files where ref_table='$file_data[ref_table]' and ref_no='$file_data[ref_no]' and file_seq <> '".$value."' order by file_ord asc ");
			$i=0;
			while($row=sql_fetch_array($sql)){
				$sql_="update md_files set file_ord='$i' where file_seq='$row[file_seq]' ";
				sql_query($sql_);
				$i++;
			}
			//삭제
			$sqlString = "delete from md_files where file_seq = '".$del_file_seq[$key]."'";
			sql_query($sqlString);
			
		}
		

	}

	public function insertFiles($table, $ref_no, $upload, $addFileString=''){

		$sqlString  = " update md_files set ";
		$sqlString .= $addFileString;
		$sqlString .= " upd_dt = now() ";
		$sqlString .= " Where del_dt is null ";
		$sqlString .= " and ref_table = '$table' ";
		$sqlString .= " and ref_no = '$ref_no' ";
		sql_query($sqlString);

		foreach($upload as $key => $value){

			$sqlString = "delete from md_files where del_dt is null ";
			$sqlString .= " And ref_table = '$table' ";
			$sqlString .= " And ref_no = '$ref_no' ";
			$sqlString .= " And file_ord = '".$upload[$key]["file_ord"]."' ";


			sql_query($sqlString);

			$sqlString  = "";	
			$sqlString .= " INSERT INTO md_files SET ";
			$sqlString .= " ref_table = '$table', ";
			$sqlString .= " ref_no = '$ref_no', ";
			$sqlString .= " file_ord = '" . $upload[$key]["file_ord"] . "', ";
			$sqlString .= " file_path = '" . $upload[$key]["file_path"] . "', ";
			$sqlString .= " file_name = '" . $upload[$key]["file_name"] . "', ";
			$sqlString .= " file_size = '" . $upload[$key]["file_size"] . "', ";
			$sqlString .= " file_width = '" . $upload[$key]["image"]["0"] . "', ";
			$sqlString .= " file_height = '" . $upload[$key]["image"]["1"] . "', ";
			$sqlString .= " file_mime = '" . $upload[$key]["image"]["mime"] . "', ";
			$sqlString .= " file_memo_1 = '" . $upload[$key]["file_memo"] . "', ";
			$sqlString .= " reg_ip = '" . $_SERVER['REMOTE_ADDR'] . "', ";
			$sqlString .= " reg_seq = '" . $member["mb_seq"] . "', ";
			$sqlString .= $addFileString;
			$sqlString .= " reg_dt = now() ";	

			sql_query($sqlString);

		}

	}




}
?>
