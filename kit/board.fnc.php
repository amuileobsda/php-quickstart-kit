<?
/* ////////////////////////////////////////////////
//===============================================//
// 게시판 관련 함수								 //
//===============================================//
//////////////////////////////////////////////// */


function dateDiffValue($to_date){
	$from = new DateTime();
	$to = new DateTime( $to_date );
	$a = $from -> diff( $to ) -> days;
	if ( $from > $to ) { $a = '+' . $a; } else { $a = '-' . $a; }
	return $a;
}



//===============================================//
// 새글 확인 여부 리턴							 //
// reg_dt		::	등록 날짜					 //
// new_day		::	새글 기간					 //
//===============================================//
function bNewDate($reg_dt, $new_day='7'){
	$reg_dt = substr($reg_dt,0,10);
	$prev_dt = date("Y-m-d", mktime(0,0,0,date("m")  , date("d")-$new_day, date("Y")));

	if($reg_dt >= $prev_dt){
		return true;
	}else{
		return false;
	}
}


//===============================================//
// 해당 게시판 권한 체크						 //
// file			::	게시판 위치					 //
//===============================================//
function getRoleGb($file){
	$role_gb = "";
	$role_gb = ($file == "list")?"ROLEGB0000":$role_gb;
	$role_gb = ($file == "insert")?"ROLEGB0001":$role_gb;//사용자 글등록시- 권한
	$role_gb = ($file == "view")?"ROLEGB0002":$role_gb;
	$role_gb = ($file == "down")?"ROLEGB0006":$role_gb;
	$role_gb = ($file == "reply")?"ROLEGB0007":$role_gb;
	$role_gb = ($file == "comment")?"ROLEGB0008":$role_gb;
	$role_gb = ($file == "mng")?"ROLEGB0009":$role_gb;
	$role_gb = ($file == "insertmng")?"ROLEGB0010":$role_gb;//관리자 글등록시- 권한
	return $role_gb;
}


//===============================================//
// 기본설정의 금지 단어 필터					 //
// memo			::	내용						 //
//===============================================//
function checkFilter($memo){
	global $config;

	$tmpFilter = $config[site_srch_filter];
	if(empty($tmpFilter)){	return true; }

	$aFilter = explode(",", $tmpFilter);
	for($i=0;$i<sizeof($aFilter);$i++) {
		if(preg_match('/'.$aFilter[$i].'/',$memo)) {
			alert("등록금지단어가 존재합니다.");
		}
	}
}

//===============================================//
// 게시물 정보 리턴		 						 //
// atcl_seq		::	번호						 //
//===============================================//
function getBoardView($atcl_seq){
	if(empty($atcl_seq)) alert('getBoardView:atcl_seq');

	$sqlString = " Select * From md_board where del_dt is null And atcl_seq = '$atcl_seq' ";
	$row = sql_fetch($sqlString);

	return $row;
}

//===============================================//
// 게시물 갯수 정보 업데이트					 //
// board_cd		::	게시물 구분값				 //
//===============================================//
function updateBoardCnt($board_cd){
	if(empty($board_cd)) alert('updateBoardCnt:board_cd');

	$sqlString = " Select count(*) As maxCount From md_board where del_dt is null And board_cd = '$board_cd' ";
	$row = sql_fetch($sqlString);
	$maxCount = $row[maxCount];

	$sqlString  = "Update md_board_mng set ";
	$sqlString .= " atcl_cnt = '$maxCount'  ";
	$sqlString .= " Where board_cd = '$board_cd' ";

	return sql_query($sqlString);
}

//===============================================//
// 타이틀 칼라 리턴								 //
// aInput		::	칼라 배열 값				 //
// strInput		::	기존 포인트 값				 //
//===============================================//
function getColorOptionString($aInput, $strInput){
	if(empty($aInput)) return "";

	foreach($aInput as $k => $v){
		$selectStatus = ($k == $strInput)?"selected":"";
		$optionString .= "<option style=\"color:".$k.";\" value='".$k."' $selectStatus>".$v."</option>";
	}
	return $optionString;
}

//===============================================//
// 게시판 권한 업데이트							 //
// ROLEGB0000	role_gb	목록보기				 //
// ROLEGB0001	role_gb	글쓰기					 //
// ROLEGB0002	role_gb	글읽기					 //
// ROLEGB0003	role_gb	업로드					 //
// ROLEGB0004	role_gb	HTML					 //
// ROLEGB0005	role_gb	링크					 //
// ROLEGB0006	role_gb	다운로드				 //
// ROLEGB0007	role_gb	글답변					 //
//===============================================//
function updateBr(){
	$sqlString = "select br_cd, count(m.mb_br_cd) as maxCount from md_br r ";
	$sqlString .= " left join md_member m on r.br_cd = m.mb_br_cd group by br_cd ";
	$result = sql_query($sqlString);

	for($i=0;$row=sql_fetch_array($result);$i++){
		$iTotalCount = $row[maxCount];
		$br_cd = $row[br_cd];

		$sqlString = "UPDATE md_br SET total_sum = '$iTotalCount' ";
		$sqlString .= " 	WHERE	 br_cd = '$br_cd' ";
		sql_query($sqlString);
	}
}

//===============================================//
// 댓글 내용 가져오기							 //
// cmt_seq		::	댓글번호					 //
//===============================================//
function getBoardCmt($cmt_seq){
	if(empty($cmt_seq)) alert('getBoardCmt:cmt_seq');

	$sqlString = " Select * From md_board_cmt where del_dt is null And cmt_seq = '$cmt_seq' ";
	$row = sql_fetch($sqlString);

	return $row;
}

//===============================================//
// 게시판 Description							 //
// atcl_seq		::	글 번호						//
// board_cd		::	게시판코드					 //
//===============================================//
function boardDescriptionLoad($atcl_seq, $board_cd){

	$sqlString = "select count(*) as cnt from md_board_role where board_cd = '$board_cd' And role_gb = 'ROLEGB0002' And role_seq = '1'";
	$row = sql_fetch($sqlString);
	if($row["cnt"] > 0 ){
		$sqlString = "select content from md_board Where del_dt is null And board_cd = '$board_cd' And (secret_fg <> '' or secret_fg is null) And atcl_seq = '$atcl_seq'";
		$row = sql_fetch($sqlString);
		$return = addslashes(strip_tags($row["content"]));
	}else{
		$return = "";
	}
	return $return;
}

?>
