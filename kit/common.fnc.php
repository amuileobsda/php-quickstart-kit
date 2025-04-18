<?
//===============================================//
// SESSION 변수 생성							 //
// session_name	::	세션 이름					 //
// value		::	세션 값						 //
//===============================================//
function set_session($session_name, $value)
{
	if (PHP_VERSION < '5.3.0') // PHP 버전별 차이를 없애기 위한 방법
		session_register($session_name);
	$_SESSION["$session_name"] = $value;
}
//===============================================//
// SESSION 변수 리턴							 //
// session_name	::	세션 이름					 //
//===============================================//
function get_session($session_name)
{
	return $_SESSION[$session_name];
}

//==============================================//
// COOKIE 변수 생성								//
// cookie_name	::	쿠키 이름					//
// value		::	쿠키 값						//
// expire		::	쿠키 시간 (단위: Day)		//
//==============================================//
function set_cookie($cookie_name, $value, $expire)
{
	global $md;

	setcookie(md5($cookie_name), base64_encode($value), time() + 60 * 60 * 24 * $expire, '/', $md["cookie_domain"]);
}

//==============================================//
// COOKIE 변수 리턴								//
// cookie_name	::	쿠키 이름					//
//==============================================//
function get_cookie($cookie_name)
{
	return base64_decode($_COOKIE[md5($cookie_name)]);
}

//==============================================//
// 접근성 URL 변환 값 리턴						//
// url			::	변환 대상					//
//==============================================//
function get_webacc_url($url){
	$url = (!strstr($url, "&amp;"))?htmlspecialchars($url):$url;
	return $url;
}

//==============================================//
// 사이트 IP 접근 차단 							//
//==============================================//
function site_access_deny_by_ip(){
	global $config;

	$bBanIp = false;
	$pattern = explode(",", trim($config['site_ban_ip']));
	for ($i=0; $i<count($pattern); $i++) {
		$pattern[$i] = trim($pattern[$i]);
		if (empty($pattern[$i])) continue;

		$pattern[$i] = str_replace(".", "\.", $pattern[$i]);
		$real_pattern = "/^{$pattern[$i]}/";
		$bBanIp = preg_match($real_pattern, $_SERVER['REMOTE_ADDR']);
		if ($bBanIp)
			die ("해당 IP는 접근 불가합니다.");
	}
}

//==============================================//
// 권한 목록 리턴	 							//
//==============================================//
function getRoleList(){
	$sqlString = "select * from md_role where del_dt is null order by disp_ord asc";
	$result = sql_query($sqlString);

	for($k = 0;$row = sql_fetch_array($result);$k++){
		$key = $row[role_seq];
		$aList[$key] = $row;
	}
	return $aList;
}

//==============================================//
// 그룹 목록 리턴	 							//
//==============================================//
function getGroupList(){
	$sqlString = "select * from md_member_group where del_dt is null order by disp_ord asc";
	$result = sql_query($sqlString);

	for($k = 0;$row = sql_fetch_array($result);$k++){
		$key = $row[group_seq];
		$aList[$key] = $row;
	}
	return $aList;
}

//===============================================//
// 회원 그룹 업데이트							 //
//===============================================//
function updateGroup(){

	$sqlString = "select group_seq, count(m.mb_group_seq) as maxCount from md_member_group g ";
	$sqlString .= " left join md_member m on g.group_seq = m.mb_group_seq group by group_seq ";
	$result = sql_query($sqlString);

	for($i=0;$row=sql_fetch_array($result);$i++){
		$iTotalCount = $row[maxCount];
		$group_seq = $row[group_seq];

		$sqlString = "UPDATE md_member_group SET total_sum = '$iTotalCount' ";
		$sqlString .= " 	WHERE	 group_seq = '$group_seq' ";
		sql_query($sqlString);
	}
}


//===============================================//
// 회원 권한 업데이트							 //
//===============================================//
function updateRole(){
	$sqlString = "select role_seq, count(m.mb_role_seq) as maxCount from md_role r ";
	$sqlString .= " left join md_member m on r.role_seq = m.mb_role_seq group by role_seq ";
	$result = sql_query($sqlString);

	for($i=0;$row=sql_fetch_array($result);$i++){
		$iTotalCount = $row[maxCount];
		$role_seq = $row[role_seq];

		$sqlString = "UPDATE md_role SET total_sum = '$iTotalCount' ";
		$sqlString .= " 	WHERE	 role_seq = '$role_seq' ";
		sql_query($sqlString);
	}

}

//==============================================//
// 로그인			 							//
//==============================================//
function login($mb_seq)
{
	set_session('login_seq', $mb_seq);
	$sqlString = "update md_member set login_count = login_count + 1, login_st_dt = now() where mb_seq = '$mb_seq' ";
	sql_query($sqlString);
}

//==============================================//
// 소셜 로그인			 							//
//==============================================//
function snsLogin($mb_seq)
{
	set_session('login_seq', $mb_seq);
	set_session('login_sns', $mb_seq);
	$sqlString = "update md_member_sns set login_count = login_count + 1, login_st_dt = now() where mb_seq = '$mb_seq' ";
	sql_query($sqlString);
}

//==============================================//
// 로그아웃			 							//
//==============================================//
function logout(){
	session_unset(); // 모든 세션변수를 언레지스터 시켜줌
	session_destroy(); // 세션해제함
}

//==============================================//
// 이메일 목록 리턴								//
// value			::	선택 목록 값			//
//==============================================//
function getEmailList($value){
	global $aEmailList;
	return getOption($aEmailList, $value);
}

//==============================================//
// 검색 단어 색상 변경							//
// srchText			::	검색 텍스트				//
// str				::	전체 문자열				//
//==============================================//
function search_font($srchText, $str){
    // 문자앞에 \ 를 붙입니다.
    $src = array("/", "|");
    $dst = array("\/", "\|");

    if (!trim($srchText)) return $str;

    // 검색어 전체를 공란으로 나눈다
    $s = explode(" ", $srchText);

    // "/(검색1|검색2)/i" 와 같은 패턴을 만듬
    $pattern = "";
    $bar = "";
    for ($m=0; $m<count($s); $m++) {
        if (trim($s[$m]) == "") continue;
        $tmp_str = quotemeta($s[$m]);
        $tmp_str = str_replace($src, $dst, $tmp_str);
        $pattern .= $bar . $tmp_str . "(?![^<]*>)";
        $bar = "|";
    }

    // 지정된 검색 폰트의 색상, 배경색상으로 대체
    $replace = "<span style='background-color:yellow;'>\\1</span>";

    return preg_replace("/($pattern)/i", $replace, $str);
}

//==============================================//
// 게시물 view 페이지 권한 확인			 							//
//==============================================//

function boardViewRoll($board_cd)
{
	$sqlString = "Select role_seq From md_board_role Where board_cd = '" . $board_cd . "' and role_gb = 'ROLEGB0002' and role_seq = '1'";
	$row = sql_fetch($sqlString);

	return $row[role_seq];
}


//==============================================//
// 해당 테이블 다음 값 출력						//
// table			::	테이블					//
// col				::	컬럼					//
// addSqlString		::	추가 조건				//
//==============================================//
function getNextSeq($table, $col, $addSqlString){
	if(empty($table)) alert('getNextSeq:table');
	if(empty($col)) alert('getNextSeq:col');

	$sqlString = " Select ifnull(max($col)+1, 1) As nextSeq From $table " . $addSqlString;
	$row = sql_fetch($sqlString);
	$nextSeq = $row[nextSeq];

	return $nextSeq;
}

//==============================================//
// Option Html 리턴								//
// aInput			::	옵션 객체[배열]			//
// value			::	현재 값 				//
// option			::	OPTION 값 				//
//==============================================//
function getOption($aInput, $value='', $option=''){
	if(empty($aInput)) return "";

	foreach($aInput as $k => $v){
		if(!empty($option)){	 $v = $v[$option]; }
		$status = ($value == $k)?"selected":"";
		$optionString .= "<option value='$k' $status>$v</option>";
	}
	return $optionString;
}

//==============================================//
// Option Html 리턴								//
// aInput			::	옵션 객체[배열]			//
// strInput			::	선택 객체				//
//==============================================//
function getOptionString($aInput, $strInput){
	if(empty($aInput)) return "";

	foreach($aInput as $k => $v){
		$selectStatus = ($k == $strInput)?"selected":"";
		$optionString .= "<option value='".$k."' $selectStatus>".$v."</option>";
	}
	return $optionString;
}
function getOptionString2($aInput, $strInput){
	if(empty($aInput)) return "";

	foreach($aInput as $k => $v){
		$optionString .= "<option value='".$k."'>".$v."</option>";
	}
	return $optionString;
}

//==============================================//
// CheckBox Html 리턴							//
// aInput			::	옵션 객체[배열]			//
// strInput			::	선택 객체				//
// addString		::	추가 구문				//
//==============================================//
function getCheckString($aInput, $strInput, $addString){
	if(empty($aInput)) return "";

	foreach($aInput as $k => $v){
		$status = (!empty($strInput) && ($strInput == $k))?"checked":"";
		$optionString .= "<input type='checkbox' ".$addString." value='".$k."' $status id='$v' /><label for='$v'>".$v."</label>&nbsp;";
	}
	return $optionString;
}

//==============================================//
// Radio Html 리턴								//
// aInput			::	옵션 객체[배열]			//
// strInput			::	선택 객체				//
// addString		::	추가 구문				//
//==============================================//
function getRadioString($aInput, $strInput, $addString){
	if(empty($aInput)) return "";

	$iLoopCount = 0;
	foreach($aInput as $k => $v){
		$status = (!empty($strInput) && ($strInput == $k))?"checked":"";
		$status = ($iLoopCount == 0 && empty($strInput))?"checked":"";
		$optionString .= "<input type='radio' ".$addString." value='".$k."' $status id='$v' />&nbsp;<label for='$v'>".$v."</label>&nbsp;";
		$iLoopCount++;
	}
	return $optionString;
}

//==============================================//
// url 값으로 이동								//
// url				::	페이지 이동 URL			//
//==============================================//
function goto_url($url)
{
	$outputString .= "<script language='JavaScript'>location.href='$url';</script><noscript></noscript>";
	$outputString .= "<a href='".get_webacc_url($url)."'>페이지로 이동하기</a>";
	echo $outputString;
}

//==============================================//
// url 값으로 이동								//
// url				::	페이지 이동 URL			//
//==============================================//
function alert($msg='', $url=''){

	$msg = (!empty($msg))?$msg:"올바른 방법으로 이용해 주십시오.";

	$outputString  = "<script language='javascript'>alert('".$msg."');";
	$outputString .= (empty($url))?"history.back(-1);":"location.href='".$url."';";
	$outputString .= "</script><noscript><a href='".get_webacc_url($url)."'>페이지로 이동하기</a></noscript>";
	echo $outputString;
    exit;

}
function alert_delay($msg='', $url=''){

	$msg = (!empty($msg))?$msg:"올바른 방법으로 이용해 주십시오.";

	$outputString  = "<script language='javascript'>setTimeout(function(){alert('".$msg."');";
	$outputString .= (empty($url))?"history.back(-1);":"location.href='".$url."';";
	$outputString .= "}, 1500)</script><noscript><a href='".get_webacc_url($url)."'>페이지로 이동하기</a></noscript>";
	echo $outputString;
    exit;

}
//==============================================//
// 회원 정보 리턴								//
// mb_seq			::	회원 일련번호			//
// fields			::	리턴할 값				//
//==============================================//
function get_member($mb_seq, $fields='*')
{
    return sql_fetch(" select $fields from md_member where mb_seq = TRIM('$mb_seq') and del_dt is null ");
}

//==============================================//
// 소셜회원 정보 리턴								//
// mb_seq			::	회원 일련번호			//
// fields			::	리턴할 값				//
//==============================================//
function get_member_sns($mb_seq, $fields='*')
{
    return sql_fetch(" select $fields from md_member_sns where mb_seq = TRIM('$mb_seq') and del_dt is null ");
}

//==============================================//
// 글자 자르기								//
// str				::	총 문자열				//
// len				::	제외 길이				//
// suffix			::	마지막 출력문			//
//==============================================//
function cut_str($str, $len, $suffix=".."){
    preg_match_all('/[\xEA-\xED][\x80-\xFF]{2}|./', $str, $match);

    $m    = $match[0];
    $slen = strlen($str);  // length of source string
    $tlen = strlen($tail); // length of tail string
    $mlen = count($m); // length of matched characters

    if ($slen <= $len) return $str;

    $ret   = array();
    $count = 0;

    for ($i=0; $i < $len; $i++) {
        $count += (strlen($m[$i]) > 1)?2:1;

        if ($count + $tlen > $len) break;
        $ret[] = $m[$i];
    }
	$returnString = join('', $ret);

    return $returnString.$suffix;
}

//==============================================//
// 이미지 정보 리턴								//
// image			::	이미지 경로 			//
// maxW				::	최대 width 이미지		//
// maxH				::	최대 height 이미지		//
//==============================================//
function newImageSize( $image, $maxW, $maxH ){

		$_new_W = $maxW;
		$_new_H = $maxH;
		$_isize = @getimagesize( $image );
		$_width = $_isize[0];
		$_height = $_isize[1];
		if ( $_new_W != "" && $_new_W < $_width ){

			$_height = $_new_W * $_height / $_width;
			$_width = $_new_W;

		}
		if ( $_new_H != "" && $_new_H < $_height ){

			$_width = $_width * $_new_H / $_height;
			$_height = $_new_H;

		}

		$IMGSIZE['width'] = $_width;
		$IMGSIZE['height'] = $_height;
		$IMGSIZE['format'] = $_isize[2];

		return $IMGSIZE;
}

//==============================================//
// 디렉토리 배열 리턴							//
// skin			::	반환 디렉토리 경로			//
//==============================================//
function get_skin_dir($skin){

    global $md;

	$aTmp = array();
    $aResult = array();
    $dirname = $md[root_rpath].$skin;

	if(is_dir($dirname)){

		$handle = opendir($dirname);
		while ($file = readdir($handle)){

			if($file == "."||$file == "..") continue;
			if (is_dir($dirname.$file)) $aTmp[$file] = $file;

		}
		closedir($handle);
	}

    sort($aTmp);

	foreach($aTmp as $k => $v){
		$aResult[$v] = $v;
	}

    return $aResult;
}

//==============================================//
// 패턴 추출 키워드 							//
// $refer			::	이전 페이지값			//
// $pattern			::	찾는 패턴 				//
//==============================================//
function getKeyword($refer, $pattern)
{
	if( $pos = strpos($refer, $pattern) )
	{
		$refer = substr($refer, $pos+strlen($pattern));

		if( ($pos = strpos($refer, "&")) !== false )
			$refer = substr($refer, 0, $pos);

		return $refer;
	}
	else
		return false;
}
//==============================================//
// 키워드 출력 함수 							//
// $str				::	찾는 키워드				//
// $refer			::	이전 페이지값			//
// $is_utf8			::	케릭터셋				//
//==============================================//
function getDecKeyword($str, $referer, $is_utf8=false)
{
	$str = addslashes(urldecode(trim($str)));

	return $str;
}


//==============================================//
// 키워드 출력 함수 							//
// $refer			::	이전 페이지값			//
//==============================================//
function getSearchWord($refer){

	if( preg_match("/index.php/i", $refer) && $keyword = getKeyword($refer, "stext="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/google./i", $refer) && $keyword = getKeyword($refer, "q=") )
	{
		if( (strpos($refer, "ie=euc-kr") && strpos($refer, "&oe=euc-kr")) === true )
			return getDecKeyword("$keyword", $refer);
		else
			return getDecKeyword("$keyword", $refer, 1);
	}
	else if( preg_match("/search.naver.com/i", $refer) && $keyword = getKeyword($refer, "query="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/m.search.naver.com/i", $refer) && $keyword = getKeyword($refer, "query="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/tattertools.com/i", $refer) && $keyword = getKeyword($refer, "search="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/miniwini.com/i", $refer) && $keyword = getKeyword($refer, "keyword="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/search.yahoo.com/i", $refer) && $keyword = getKeyword($refer, "p="))
	{
		if( preg_match("ei=UTF-8", $refer) )
			return getDecKeyword("$keyword", $refer, 1);
		else
			return getDecKeyword("$keyword", $refer);
	}
	else if( preg_match("/zboard.php/i", $refer) && $keyword = getKeyword($refer, "keyword="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/view.php/i", $refer) && $keyword = getKeyword($refer, "keyword="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/search.daum.net/i", $refer) && $keyword = getKeyword($refer, "q="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/m.search.daum.net/i", $refer) && $keyword = getKeyword($refer, "q="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/blogkorea.org/i", $refer) && $keyword = getKeyword($refer, "st="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/srch.bugs.co.kr/i", $refer) && $keyword = getKeyword($refer, "keyword="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/search.allblog.net/i", $refer) && $keyword = getKeyword($refer, "search="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/search.chol.com/i", $refer) && $keyword = getKeyword($refer, "q="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/search.dreamwiz.com/i", $refer) && $keyword = getKeyword($refer, "q="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/search.hanafos.com/i", $refer) && $keyword = getKeyword($refer, "query="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/search.nate.com/i", $refer) && $keyword = getKeyword($refer, "query="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/m.search.nate.com/i", $refer) && $keyword = getKeyword($refer, "query="))
		return getDecKeyword("$keyword", $refer);
	else if( preg_match("/search.msn./i", $refer) && $keyword = getKeyword($refer, "q="))
	{
		if( preg_match("cp=949", $refer) )
			return getDecKeyword("$keyword", $refer);
		else
			return getDecKeyword("$keyword", $refer, 1);
	}
}





//==============================================//
// 지점 목록 리턴								//
//==============================================//
function getBrList(){
	$sqlString = "select * from md_br where del_dt is null order by disp_ord asc";
	$result = sql_query($sqlString);

	for($k = 0;$row = sql_fetch_array($result);$k++){
		$key = $row[br_cd];
		$aList[$key] = $row;
	}
	return $aList;
}


//==============================================//
// 진료과목 업데이트							//
//==============================================//
function updateDiv(){
	$sqlString = "select div_cd, count(m.mb_div_cd) as maxCount from md_div r ";
	$sqlString .= " left join md_member m on r.div_cd = m.mb_div_cd group by div_cd ";
	$result = sql_query($sqlString);

	for($i=0;$row=sql_fetch_array($result);$i++){
		$iTotalCount = $row[maxCount];
		$div_cd = $row[div_cd];

		$sqlString = "UPDATE md_div SET total_sum = '$iTotalCount' ";
		$sqlString .= " 	WHERE	 div_cd = '$div_cd' ";
		sql_query($sqlString);
	}
}



//================================== 문자열 치환 [S] ===============================//

//==============================================//
// script And iframe 변환						//
// content			::	변환 내용				//
//==============================================//
function get_tag_convert($content){

	return preg_replace("/\<([\/]?)(script|iframe)([^\>]*)\>/i", "&lt;$1$2$3&gt;", $content);

}

//==============================================//
// 태그 변환									//
// content			::		변환 내용			//
//==============================================//
function get_tag_encoding($content){

	$source = array();
	$target = array();

	$source[] = "//";
	$target[] = "";

	// 테이블 태그의 갯수를 세어 테이블이 깨지지 않도록 한다.
	$table_begin_count = substr_count(strtolower($content), "<table");
	$table_end_count = substr_count(strtolower($content), "</table");
	for ($i=$table_end_count; $i<$table_begin_count; $i++) {
		$content .= "</table>";
	}

	$content = preg_replace($source, $target, $content);
	$content = get_tag_convert($content);

	// XSS (Cross Site Script) 막기
	// 완벽한 XSS 방지는 없다.
	// 081022 : CSRF 방지
	$content = preg_replace("/(on)([a-z]+)([^a-z]*)(\=)/i", "&#111;&#110;$2$3$4", $content);
	$content = preg_replace("/(dy)(nsrc)/i", "&#100;&#121;$2", $content);
	$content = preg_replace("/(lo)(wsrc)/i", "&#108;&#111;$2", $content);
	$content = preg_replace("/(sc)(ript)/i", "&#115;&#99;$2", $content);
	$content = preg_replace("/(ex)(pression)/i", "&#101&#120;$2", $content);

    return $content;

}

function html_symbol($str){
    return preg_replace("/\&([a-z0-9]{1,20}|\#[0-9]{0,3});/i", "&#038;\\1;", $str);
}


function conv_content($content, $html='')
{
    global $config, $board;

    if ($html)
    {
        $source = array();
        $target = array();

        $source[] = "//";
        $target[] = "";

        // 테이블 태그의 갯수를 세어 테이블이 깨지지 않도록 한다.
        $table_begin_count = substr_count(strtolower($content), "<table");
        $table_end_count = substr_count(strtolower($content), "</table");
        for ($i=$table_end_count; $i<$table_begin_count; $i++)
        {
            $content .= "</table>";
        }

        $content = preg_replace($source, $target, $content);
        $content = bad_tag_convert($content);

        // XSS (Cross Site Script) 막기
        // 완벽한 XSS 방지는 없다.
        // 081022 : CSRF 방지
        //$content = preg_replace("/(on)(abort|blur|change|click|dblclick|dragdrop|error|focus|keydown|keypress|keyup|load|mousedown|mousemove|mouseout|mouseover|mouseup|mouseenter|mouseleave|move|reset|resize|select|submit|unload)/i", "$1<!-- XSS Filter -->$2", $content);

        $content = preg_replace("/(on)([a-z]+)([^a-z]*)(\=)/i", "&#111;&#110;$2$3$4", $content);
        $content = preg_replace("/(dy)(nsrc)/i", "&#100;&#121;$2", $content);
        $content = preg_replace("/(lo)(wsrc)/i", "&#108;&#111;$2", $content);
        $content = preg_replace("/(sc)(ript)/i", "&#115;&#99;$2", $content);
        $content = preg_replace("/(ex)(pression)/i", "&#101&#120;$2", $content);
    }else{// text 이면

        $content = html_symbol($content);
		$content = str_replace("  ", "&nbsp; ", $content);
		$content = str_replace("\n ", "\n&nbsp;", $content);
        $content = get_text($content, 1);
		$content = @preg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\" target=\"_new\">\\0</a>", $content);
    }

    return $content;
}

// 악성태그 변환
function bad_tag_convert($code)
{
    return preg_replace("/\<([\/]?)(script|iframe)([^\>]*)\>/i", "&lt;$1$2$3&gt;", $code);
}
//시스템 값 변환
function get_encode_text($str){

	$source[] = "`";
	$target[] = "&#96;";
	$source[] = "(";
	$target[] = "&#40;";
	$source[] = ")";
	$target[] = "&#41;";
	$source[] = ":";
	$target[] = "&#58;";
	$source[] = ";";
	$target[] = "&#59;";
	$source[] = "=";
	$target[] = "&#61;";
	$source[] = '\"';
	$target[] = "&quot;";
	$source[] = "<";
	$target[] = "&lt;";
	$source[] = ">";
	$target[] = "&gt;";
	$source[] = "\'";
	$target[] = "&#39;";
	$source[] = "\'";
	$target[] = "&#39;";

	for ($i = 0 ; $i < count($source) ; $i++){
		$str = str_replace($source[$i], $target[$i], $str);
	}
	return $str;
}

//시스템 값 변환
function get_decode_text($str){

	$source[] = "`";
	$target[] = "&#96;";
	$source[] = "(";
	$target[] = "&#40;";
	$source[] = ")";
	$target[] = "&#41;";
	$source[] = ":";
	$target[] = "&#58;";
	$source[] = ";";
	$target[] = "&#59;";
	$source[] = "=";
	$target[] = "&#61;";
	$source[] = '\"';
	$target[] = "&quot;";
	$source[] = "<";
	$target[] = "&lt;";
	$source[] = ">";
	$target[] = "&gt;";
	$source[] = "\'";
	$target[] = "&#39;";
	$source[] = "\'";
	$target[] = "&#39;";

	for ($i = 0 ; $i < count($source) ; $i++){
		$str = str_replace($target[$i], $source[$i], $str);
	}
	return $str;
}


// TEXT 형식으로 변환
function get_text($str, $html=0){
    // 3.31
    // TEXT 출력일 경우 &amp; &nbsp; 등의 코드를 정상으로 출력해 주기 위함
    if (!$html) {
        $str = html_symbol($str);
    }

    $source[] = "/</";
    $target[] = "&lt;";
    $source[] = "/>/";
    $target[] = "&gt;";
    $source[] = "/\'/";
    $target[] = "&#039;";

    if ($html) {
        $source[] = "/\n/";
        $target[] = "<br/>";
    }
    return preg_replace($source, $target, $str);
}



//================================== 문자열 치환 [E] ===============================//



//================================== 아이콘 출력 [S] ===============================//

function get_best_icon($skin_name='default'){ // 베스트 아이콘
	return "<img src='/images/bbs/$skin_name/best.gif' align=\"middle\" alt=\"best\" />";
}

function get_gallery_best_icon($skin_name='default'){ // 갤러리 베스트 아이콘
	return "<img src='/images/bbs/$skin_name/best_gallery.gif' alt=\"best\" />";
}

function get_notice_icon($skin_name='default'){ // 공지사항 아이콘
	return "<li style='padding:2px 1px;background:#c0d2ec;color:#000;'>공지</li>";
}

function get_new_icon($skin_name='default'){ // 새글 아이콘
	return '<img src="/images/bbs/news/new.jpg" alt=" 새글" />';
}

function get_secret_icon($skin_name='default'){ // 비밀글 아이콘
	return "<img src='/images/bbs/news/secret.jpg' alt='비밀글' />";
}

function get_adult_icon($skin_name='default'){ // 성인 아이콘
	return "<img src='/images/bbs/$skin_name/19.gif' align=\"middle\" width='18' alt=\"19세\" />";
}

function get_re_icon($skin_name='default'){ // 리플 아이콘
	return "<img src='/images/bbs/$skin_name/reply_icon.gif' align=\"middle\" alt=\"reply\" />";
}

function get_re_blank($skin_name='default'){ // 리플 빈공간 아이콘
	return "<img src='/images/bbs/$skin_name/reply_blank.gif' align=\"middle\" alt=\"blank\" />";
}

function get_hot_icon($skin_name='default'){ // 인기 아이콘
	return "<img src='/images/bbs/$skin_name/hot.jpg' align=\"middle\" alt=\"popular\" />";
}

function get_reply_icon($skin_name='default', $value){ // 리플 아이콘
	if($value){
		return "<li class='listbt_03'>답변완료</li>";
	}else{
		return "<li class='listbt_02'>답변대기</li>";
	}
}
function get_content_img_icon($skin_name='default'){ //이미지 아이콘
	return "<img src='/images/bbs/$skin_name/photo_img_icon.gif' align=\"middle\" alt=\"컨텐츠이미지\" />";
}




//================================== 아이콘 출력 [E] ===============================//


//================================== 메인 출력 함수 [S] ===============================//

//==============================================//
// 메인 출력 게시물								//
// board_cd			::	게시판 코드				//
// srchCate			::	카테고리 코드			//
// limit			::	갯수 제한				//
// cutstr			::	타이틀 글자수			//
// new_day			::	new 게시물			//
// menu_gb			::	해당 게시물 mc값계산용 메뉴구분값			//
// main_fg			::	메인 표출 필터			//
//==============================================//
function board_notice($board_cd, $srchCate, $limit, $cutstr, $new_day, $menu_gb = "KR00000000", $main_fg = "N"){
	//기능/메뉴코드/실행파일명/게시판코드/페이지번호/게시물번호/
	global $FILE;

	$sqlString = "Select * From md_board_mng Where del_dt is null And board_cd = '$board_cd'";
	$board_mng = sql_fetch($sqlString);
	if(is_array($board_mng)){
		$addSqlString = "";
		$addSqlString .= ($main_fg == "Y") ? " And main_fg = 'MAINFG0001' " : " ";
		$addSqlString .= ($srchCate != "") ? " And srchCate = '$srchCate' " : " ";
		$orderString = " Order By " . $board_mng["board_order"];
		$mcString = "select menu_id from md_menu where board_cd = '$board_cd' and del_dt is null and menu_gb = '$menu_gb'";
		$mcRow = sql_fetch($mcString);
		$sqlString = "Select * From md_board Where del_dt is null And board_cd = '$board_cd'  " . $addSqlString . $orderString . " Limit " . $limit ;
		$result = sql_query($sqlString);

		$boardList =Array();
		for($i=0 ; $row = sql_fetch_array($result) ; $i++){

			$boardList[$i]["atcl_seq"]		= $row["atcl_seq"];
			$boardList[$i]["content"]       = strip_tags(substr($row["content"], 0, 250)) . '...';
			$boardList[$i]["movie_src"]		= $row["movie_src"];
			$boardList[$i]["href_list"]		= "/board/list/" . $board_cd . "/" . $mcRow["menu_id"];
			$boardList[$i]["href_view"]		= "/board/view/" . $board_cd . "/" . $mcRow["menu_id"] . "/" . $row["atcl_seq"] . "/cpage/1/" ;
			$boardList[$i]["reg_dt"]		= substr($row["reg_dt"], 0, 10);
			$boardList[$i]["dot_reg_dt"]		= str_replace("-",".",substr($row["reg_dt"], 0, 10));
			$boardList[$i]["sm_reg_dt"]		= substr($row["reg_dt"], 2, 8);
			$boardList[$i]["board_cate"]	= (!empty($row["board_cate"])) ? "[" . $row["board_cate"] . "]" : "";
			$boardList[$i]["title"]			= cut_str(strip_tags($row["title"]), $cutstr);
			$boardList[$i]["secret_icon"]	= (!empty($row["secret_fg"]))?get_secret_icon($css):"";

			if(bNewDate($row[reg_dt],$new_day)){
				if($new_day > 0){
					// $boardList[$i]['title'] = $boardList[$i]['title'].'<img src="/images/common/new.jpg" alt="">';
				}
			}
			$aFile = $FILE->getFiles('md_board', $row["atcl_seq"]);
			$boardList[$i]["preview1"]		= $aFile["0"]["filedata"];
			$boardList[$i]["preview2"]		= $aFile["1"]["filedata"];


		}
		return $boardList;
	}
}

function board_notice_2($board_cd, $srchCate, $limit, $cutstr, $new_day, $menu_gb = "KR00000000", $type_fg = "", $main_fg = "N", $menu_id = ""){

	//기능/메뉴코드/실행파일명/게시판코드/페이지번호/게시물번호/
	global $FILE;

	$sqlString = "Select * From md_board_mng Where del_dt is null And board_cd = '$board_cd'";
	$board_mng = sql_fetch($sqlString);
	if(is_array($board_mng)){
		$addSqlString = "";
		$addSqlString .= ($main_fg == "Y") ? " And main_fg = 'MAINFG0001' " : " ";

		if($type_fg == "erun_main_fg"){
			$addSqlString .= " And erun_main_fg = 'MERUNFG001' ";
		}
		if($type_fg == "hot_main_hg"){
			$addSqlString .= " And hot_main_hg = 'MHOTFG001' ";
		}
		$addSqlString .= ($main_fg == "Y") ? " And main_fg = 'MAINFG0001' " : " ";
		$addSqlString .= ($srchCate != "") ? " And board_cate = '$srchCate' " : " ";

		if($menu_gb == "MB00000000" && $board_cd =="bnf"){
			$sqlString = "select menu_name from md_menu where menu_id = '$menu_id' ";
			$result = sql_fetch($sqlString);
			$sqlString = "select menu_id from md_menu where del_dt is null and disp_fg='DISPFG0001' and menu_gb = 'KR00000000' and menu_name = '".$result['menu_name']."' ";
			$result = sql_fetch($sqlString);

			$menu_id = !empty($result['menu_id']) ? $result['menu_id'] : $menu_id ;
		}
		$addSqlString .= (($board_cd == "bnf") && !empty($menu_id)) ? " And menu_id = '$menu_id' " : " ";


		$orderString = " Order By " . $board_mng["board_order"];
		$mcString = "select menu_id from md_menu where board_cd = '$board_cd' and del_dt is null and menu_gb = '$menu_gb'";
		$mcRow = sql_fetch($mcString);
		$sqlString = "Select * From md_board Where del_dt is null And disp_fg = 'DISPFG0001' And board_cd = '$board_cd'  " . $addSqlString . $orderString . " Limit " . $limit ;
		$result = sql_query($sqlString);

		$boardList =Array();
		for($i=0 ; $row = sql_fetch_array($result) ; $i++){

			$boardList[$i]["num1"]		= $i+1;
			$boardList[$i]["num2"]		= $i+1;
			$boardList[$i]["num3"]		= $i+1;
			$boardList[$i]["num4"]		= $i+1;
			$boardList[$i]["on"]		= empty($i) ? "on" : "" ;
			$boardList[$i]["atcl_seq"]		= $row["atcl_seq"];
			$boardList[$i]["content"]		= nl2br($row["content"]);
			if(empty($mcRow["menu_id"]) && $board_cd == "notice"){
				$mcRow["menu_id"] = "56";
 			}
			$boardList[$i]["href_list"]		= "/index.php/board/list/" . $board_cd . "/" . $mcRow["menu_id"];
			$boardList[$i]["href_view"]		= "/index.php/board/view/" . $board_cd . "/" . $mcRow["menu_id"] . "/" . $row["atcl_seq"] . "/cpage/1/" ;
			$boardList[$i]["reg_dt"]		= substr($row["reg_dt"], 0, 10);
			$boardList[$i]["dot_reg_dt"]		= str_replace("-",".",substr($row["reg_dt"], 0, 10));
			$boardList[$i]["sm_reg_dt"]		= substr($row["reg_dt"], 2, 8);
			$boardList[$i]["board_cate"]	= $row["board_cate"];
			$boardList[$i]["title"]			= cut_str(strip_tags($row["title"]), $cutstr);
			$boardList[$i]["secret_icon"]	= (!empty($row["secret_fg"]))?get_secret_icon($css):"";

			$boardList[$i]["date_diff"]		= dateDiffValue($row["tmp_2"]);
			$boardList[$i]["tmp_3"]		= nl2br($row["tmp_3"]);
			$boardList[$i]["tmp_4"]		= $row["tmp_4"];
			$boardList[$i]["movie_src"]		= $row["movie_src"];
			$boardList[$i]["tmp_5"]		= nl2br($row["tmp_5"]);
			$boardList[$i]["tmp_6"]		= nl2br($row["tmp_6"]);

			$aFile = $FILE->getFiles('md_board', $row["atcl_seq"]);
			$boardList[$i]["preview1"]		= $aFile["0"]["filedata"];
			$boardList[$i]["preview2"]		= $aFile["1"]["filedata"];
			$boardList[$i]["preview_ar"]		= array();
			for ($z=0; $z < count($aFile); $z++) {
				if(!empty($aFile[$z]["filedata"])){
					array_push($boardList[$i]["preview_ar"], $aFile[$z]["filedata"]);
				}
			}
		}
		return $boardList;
	}
}







//================================== 메인 출력 함수 [E] ===============================//



//================================== 회원 가입 함수 [S] ===============================//

//==============================================//
// 회원 아이디 체크								//
// $mb_id			::	회원 아이디				//
// $fields			::	검색 필드				//
//==============================================//
function get_member_by_id($mb_id, $fields='*') {
    return sql_fetch(" select $fields from md_member where mb_id = TRIM('$mb_id') and del_dt is null ");
}


//==============================================//
// 비밀번호 랜덤 리턴							//
// $len				::	리턴 길이				//
//==============================================//
function random_text($len = '8'){
	$chars_array = array_merge(range(0,9));
	shuffle($chars_array);
	$shuffle = implode("", $chars_array);
	return substr($shuffle,0,$len);
}


//================================== 회원 가입 함수 [E] ===============================//


//================================== 모바일 체크 [S] ===============================//
function MobileCheck() {
	global $HTTP_USER_AGENT;
	$MobileArray  = array("iphone","lgtelecom","skt","mobile","samsung","nokia","blackberry","android","android","sony","phone");

	$checkCount = 0;
		for($i=0; $i<sizeof($MobileArray); $i++){
			if(preg_match("/$MobileArray[$i]/", strtolower($HTTP_USER_AGENT))){ $checkCount++; break; }
		}

   return ($checkCount >= 1) ? true : false;
}
//================================== 모바일 체크 [E] ===============================//


//================================== CAPTCHA 이미지 출력부 [S] =================================//
// $vals = array(																				//
//    'word' => '',																				//
//   'img_path' => './filedata/',																//
//    'img_url' => 'http://test.da-vin.net/filedata/',											//
//    'img_width' => '150',																		//
//    'img_height' => 30,																		//
//    'expiration' => 7200																		//
//    );																						//
// $cap = $create_captcha($vals);																//
//																								//
//////////////////////////////////////////////////////////////////////////////////////////////////

function create_captcha($data = '', $img_path = '', $img_url = '', $font_path = ''){
	$defaults = array('word' => '', 'img_path' => '', 'img_url' => '', 'img_width' => '150', 'img_height' => '30', 'font_path' => '', 'expiration' => 7200);

	foreach ($defaults as $key => $val)
	{
		if ( ! is_array($data))
		{
			if ( ! isset($$key) OR $$key == '')
			{
				$$key = $val;
			}
		}
		else
		{
			$$key = ( ! isset($data[$key])) ? $val : $data[$key];
		}
	}

	if ($img_path == '' OR $img_url == '')
	{
		return FALSE;
	}

	if ( ! @is_dir($img_path))
	{
		return FALSE;
	}

	if ( ! is_writable($img_path))
	{
		return FALSE;
	}

	if ( ! extension_loaded('gd'))
	{
		return FALSE;
	}

	// -----------------------------------
	// Remove old images
	// -----------------------------------

	list($usec, $sec) = explode(" ", microtime());
	$now = ((float)$usec + (float)$sec);

	$current_dir = @opendir($img_path);

	while ($filename = @readdir($current_dir))
	{
		if ($filename != "." and $filename != ".." and $filename != "index.html")
		{
			$name = str_replace(".jpg", "", $filename);

			if (($name + $expiration) < $now)
			{
				@unlink($img_path.$filename);
			}
		}
	}

	@closedir($current_dir);

	// -----------------------------------
	// Do we have a "word" yet?
	// -----------------------------------

   if ($word == '')
   {
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		$str = '';
		for ($i = 0; $i < 8; $i++)
		{
			$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
		}

		$word = $str;
   }

	// -----------------------------------
	// Determine angle and position
	// -----------------------------------

	$length	= strlen($word);
	$angle	= ($length >= 6) ? rand(-($length-6), ($length-6)) : 0;
	$x_axis	= rand(6, (360/$length)-16);
	$y_axis = ($angle >= 0 ) ? rand($img_height, $img_width) : rand(6, $img_height);

	// -----------------------------------
	// Create image
	// -----------------------------------

	// PHP.net recommends imagecreatetruecolor(), but it isn't always available
	if (function_exists('imagecreatetruecolor'))
	{
		$im = imagecreatetruecolor($img_width, $img_height);
	}
	else
	{
		$im = imagecreate($img_width, $img_height);
	}

	// -----------------------------------
	//  Assign colors
	// -----------------------------------

	$bg_color		= imagecolorallocate ($im, 255, 255, 255);
	$border_color	= imagecolorallocate ($im, 153, 102, 102);
	$text_color		= imagecolorallocate ($im, 204, 153, 153);
	$grid_color		= imagecolorallocate($im, 255, 182, 182);
	$shadow_color	= imagecolorallocate($im, 255, 240, 240);

	// -----------------------------------
	//  Create the rectangle
	// -----------------------------------

	ImageFilledRectangle($im, 0, 0, $img_width, $img_height, $bg_color);

	// -----------------------------------
	//  Create the spiral pattern
	// -----------------------------------

	$theta		= 1;
	$thetac		= 7;
	$radius		= 16;
	$circles	= 20;
	$points		= 32;

	for ($i = 0; $i < ($circles * $points) - 1; $i++)
	{
		$theta = $theta + $thetac;
		$rad = $radius * ($i / $points );
		$x = ($rad * cos($theta)) + $x_axis;
		$y = ($rad * sin($theta)) + $y_axis;
		$theta = $theta + $thetac;
		$rad1 = $radius * (($i + 1) / $points);
		$x1 = ($rad1 * cos($theta)) + $x_axis;
		$y1 = ($rad1 * sin($theta )) + $y_axis;
		imageline($im, $x, $y, $x1, $y1, $grid_color);
		$theta = $theta - $thetac;
	}

	// -----------------------------------
	//  Write the text
	// -----------------------------------

	$use_font = ($font_path != '' AND file_exists($font_path) AND function_exists('imagettftext')) ? TRUE : FALSE;

	if ($use_font == FALSE)
	{
		$font_size = 5;
		$x = rand(0, $img_width/($length/3));
		$y = 0;
	}
	else
	{
		$font_size	= 16;
		$x = rand(0, $img_width/($length/1.5));
		$y = $font_size+2;
	}

	for ($i = 0; $i < strlen($word); $i++)
	{
		if ($use_font == FALSE)
		{
			$y = rand(0 , $img_height/2);
			imagestring($im, $font_size, $x, $y, substr($word, $i, 1), $text_color);
			$x += ($font_size*2);
		}
		else
		{
			$y = rand($img_height/2, $img_height-3);
			imagettftext($im, $font_size, $angle, $x, $y, $text_color, $font_path, substr($word, $i, 1));
			$x += $font_size;
		}
	}


	// -----------------------------------
	//  Create the border
	// -----------------------------------

	imagerectangle($im, 0, 0, $img_width-1, $img_height-1, $border_color);

	// -----------------------------------
	//  Generate the image
	// -----------------------------------

	$img_name = $now.'.jpg';

	ImageJPEG($im, $img_path.$img_name);

	$img = "<img src=\"$img_url$img_name\" width=\"$img_width\" height=\"$img_height\" style=\"border:0;\" alt=\" \" />";

	ImageDestroy($im);

	return array('word' => $word, 'time' => $now, 'image' => $img);
}
//================================== CAPTCHA 이미지 출력부 [E] ===============================//

//================================== 스킨 HTML 레코딩 [S] ===============================//
function skinReadView($path, $loopName = Array(), $loopDate){
	$path = str_replace("../", "", $path);

	if(is_file($path)){
		$fp = fopen($path, 'r');
		$loopStart = false;
		$loopText = "";
		$loopValue = "";

			while(!feof($fp)){

				$lineText = fgets($fp);
				$changeText = str_replace("}", "", $lineText);
				$changeText = trim(str_replace("{", "", $changeText));

				if(in_array($changeText, $loopName)){
					$loopStart = true;
					$loopValue = $changeText;
				}else if ($loopStart == true && substr(trim($lineText), 0, 2) == "{/"){
					$loopStart = false;
					for($i = 0 ; $i < count($loopDate[$loopValue]) ; $i++){
						$outerText = $loopText;
						foreach($loopDate[$loopValue][$i] as $key => $val){
							$outerText = str_replace("{" . $key . "}", $val, $outerText);
						}
						echo $outerText;
					}
					$loopText = "";
					$outerText = "";
				}else{
					if($loopStart){
						$loopText .= $lineText;
					}else{
						echo $lineText;
					}
				}

			}
		fclose($fp);
	}

}
//================================== 스킨 HTML 레코딩 [E] ===============================//


function in_referer($referer){

	$sqlString = "Select * From md_ad Where del_dt is null Order By ad_seq asc ";
	$result = sql_query($sqlString);

	for ($i = 0 ; $row = sql_fetch_array($result);$i++){
		if(strpos($referer, $row["ad_code"])){
			$sqlString = "Update md_ad Set ad_count = ad_count + 1 Where ad_seq = '" . $row["ad_seq"] . "'";
		}
	}
	sql_query($sqlString);
}

function out_referer($referer){

	$sqlString = "Select * From md_ad Where del_dt is null Order By ad_seq asc ";
	$result = sql_query($sqlString);

	$codeName = "";
	for ($i = 0 ; $row = sql_fetch_array($result);$i++){
		if(strpos($referer, $row["ad_code"])){
			$codeName = $row["ad_name"];
		}
	}

	return $codeName;
}

//===============================================//
// menu_id 리턴				 //
// board_cd		::	메뉴 게시판 구분 값				 //
// menu_gb		::	메뉴 구분 값				 //
//===============================================//

function getBoardMc($board_cd, $menu_gb){

	$sqlString = "select menu_id from md_menu Where del_dt is null and board_cd = '$board_cd' and menu_gb = '$menu_gb'";
	$row = sql_fetch($sqlString);

	return $row["menu_id"];

}

//================================== 게시판 입력값 체크 [S] ===============================//
function checkInput($inputArray, $POST){

	foreach($inputArray as $key => $val){
		if($POST[$key] == ""){
			alert("[" . $val . "] 입력해주세요.");
			exit;
		}
	}
}
//================================== 게시판 입력값 체크 [E] ===============================//



function get_doc($seq){

	global $FILE;
	global $cd;

	$sql_string = "select * from md_medical_team where del_dt is null and md_seq = '$seq' ";
	$result_doctor = sql_fetch($sql_string);

	$a_doctor = array();

	$a_doctor = $result_doctor;

	$sqlString3  = " Select count(*) As maxCount ";
	$sqlString3 .= " From md_board ";
	$sqlString3 .= " Where del_dt is null and re_content is null and md_seq = '".$result_doctor["md_seq"]."'";
	$row3 = sql_fetch($sqlString3);
	$a_doctor["mdConTotalCount"] = $row3["maxCount"];

	$a_doctor["firstshow"] = empty($i) ? "" : " hidden ";
	$a_doctor["center_gb_idx"] = (int)str_replace("CENTERGB0", "", $result_doctor["center_gb"] ) -1;
	$a_file = $FILE->getFiles("md_medical_team",$seq);
	$a_doctor["filedata"] = $a_file[0]["filedata"];
	$a_doctor["filedata1"] = $a_file[1]["filedata"];
	$a_doctor["filedata2"] = $a_file[2]["filedata"];
	$a_doctor["filedata3"] = $a_file[3]["filedata"];
	$a_doctor["filedataon"] = $a_file[2]["filedata"];
	$a_doctor["filedataoff"] = $a_file[3]["filedata"];
	$a_doctor["filedata4"] = $a_file[4]["filedata"];

	$str_div_gb = $cd->getName('div_gb',$result_doctor["div_gb"], false);
	$str_center_gb = $cd->getName('center_gb',$result_doctor["center_gb"], false);

	$a_doctor['str_div_gb'] = $str_div_gb;
	$a_doctor['str_center_gb'] = $str_center_gb;



	$profile_2 = explode(chr(13), $result_doctor["profile_2"]);
	$a_doctor['ex_profile_2'] = $profile_2;

	$profile_3 = explode(chr(13), $result_doctor["profile_3"]);
	$a_doctor['ex_profile_3'] = $profile_3;

	$profile_14 = explode(chr(13), $result_doctor["profile_14"]);
	$a_doctor['ex_profile_14'] = $profile_14;

	$profile_15 = explode(chr(13), $result_doctor["profile_15"]);
	$a_doctor['ex_profile_15'] = $profile_15;


	$a_doctor["profile_14"] = nl2br($a_doctor["profile_14"]);
	$a_doctor["profile_2"] = nl2br($a_doctor["profile_2"]);
	$a_doctor["profile_3"] = nl2br($a_doctor["profile_3"]);
	$a_doctor["profile_15"] = nl2br($a_doctor["profile_15"]);

	$a_doctor['a_timetable'] = explode("|", $a_doctor[timetable]);

	return $a_doctor;
}

function doc_lode($center_gb='',$center='',$scrh_name='',$lmt=''){

	global $FILE;
	global $cd;

	$center = empty($center) ? "center_gb" : $center ;
	$center_id = $cd->getCodeId($center,$scrh_name);



	if(!empty($center_id)){
		$addSqlString .= " and (md_name like '%$scrh_name%' or $center like '%$center_id%') ";
	}else{
		$addSqlString = empty($center_gb)? '': " and $center like '%$center_gb%' ";
		$addSqlString .= empty($scrh_name)? '': " and md_name like '%$scrh_name%' ";
	}


	$addSqlString2 = empty($lmt)? '': " limit $lmt ";
	$sql_string = "select * from md_medical_team where del_dt is null and disp_fg != '' ".$addSqlString." order by disp_ord asc " . $addSqlString2;
	$result_doctor = sql_query($sql_string);


	$a_doctor = array();
	for($i = 0; $row = sql_fetch_array($result_doctor); $i++){

	$a_doctor[$i] = $row;

	$sqlString3  = " Select count(*) As maxCount ";
	$sqlString3 .= " From md_board ";
	$sqlString3 .= " Where del_dt is null and re_content is null and md_seq = '".$row["md_seq"]."'";
	$row3 = sql_fetch($sqlString3);
	$a_doctor[$i]["mdConTotalCount"] = $row3["maxCount"];

	$a_doctor[$i]["firstshow"] = empty($i) ? "" : " hidden ";
	$a_doctor[$i]["center_gb_idx"] = (int)str_replace("CENTERGB0", "", $row["center_gb"] ) -1;
	$a_file = $FILE->getFiles("md_medical_team",$row["md_seq"]);
	$a_file2 = $FILE->getFiles("md_medical_team_etc",$row["md_seq"]);
	$a_doctor[$i]["filedata"] = $a_file[0]["filedata"];
	$a_doctor[$i]["filedata1"] = $a_file[1]["filedata"];
	$a_doctor[$i]["filedata2"] = $a_file[2]["filedata"];
	$a_doctor[$i]["filedata3"] = $a_file[3]["filedata"];
	$a_doctor[$i]["filedataon"] = $a_file[2]["filedata"];
	$a_doctor[$i]["filedataoff"] = $a_file[3]["filedata"];
	$a_doctor[$i]["filedata4"] = $a_file[4]["filedata"];

	$a_doctor[$i]["filedata_etc"] = $a_file2[0]["filedata"];
	$a_doctor[$i]["filedata_etc1"] = $a_file2[1]["filedata"];
	$a_doctor[$i]["filedata_etc2"] = $a_file2[2]["filedata"];
	$a_doctor[$i]["filedata_etc3"] = $a_file2[3]["filedata"];
	$a_doctor[$i]["a_file2"] = $a_file2;

	$a_doctor[$i]["filedata"] = empty($a_doctor[$i]["filedata"]) ? '/filedata/md_medical_team/20210511171808_m3DZcfah_20210511_171722.png' : $a_doctor[$i]["filedata"] ;
	$a_doctor[$i]["filedata1"] = empty($a_doctor[$i]["filedata1"]) ? '/page_KR0/01_sub/images/dr1bg.jpg' : $a_doctor[$i]["filedata1"] ;
	$a_doctor[$i]["filedata2"] = empty($a_doctor[$i]["filedata2"]) ? '/page_KR0/01_sub/images/010101drimg.jpg' : $a_doctor[$i]["filedata2"] ;
	$str_div_gb = $cd->getName('div_gb',$row["div_gb"], false);
	$str_center_gb = $cd->getName('center_gb',$row["center_gb"], false);

	$a_doctor[$i]['cen_class'] = str_replace("|", " ", $row["center_gb"] );
	$a_doctor[$i]['cen_class2'] = str_replace("|", " ", $row["center_gb2"] );

	$a_doctor[$i]['str_div_gb'] = $str_div_gb;
	$a_doctor[$i]['str_center_gb'] = $str_center_gb;
	$a_doctor[$i]['md_position'] = $row["md_position"] . " " . $row["md_position2"];



	$profile_2 = explode(chr(13), $row["profile_2"]);
	$a_doctor[$i]['ex_profile_2'] = $profile_2;

	$profile_3 = explode(chr(13), $row["profile_3"]);
	$a_doctor[$i]['ex_profile_3'] = $profile_3;

	$profile_4 = explode(chr(13), $row["profile_4"]);
	$a_doctor[$i]['ex_profile_4'] = $profile_4;

	if(!empty($row["profile_5"])){

		$profile_5 = explode(chr(13), $row["profile_5"]);
		$profile_5 = explode("|", $row["profile_5"]);
		$a_doctor[$i]['ex_profile_5'] = (empty($profile_5[0])) ? '' : $profile_5;
	}


	if(!empty($row["profile_6"])){

		$profile_6 = explode(chr(13), $row["profile_6"]);
		$profile_6 = explode("|", $row["profile_6"]);
		$a_doctor[$i]['ex_profile_6'] = $profile_6;
	}

	if(!empty($row["profile_7"])){

		$profile_7 = explode(chr(13), $row["profile_7"]);
		$profile_7 = explode("|", $row["profile_7"]);
		$a_doctor[$i]['ex_profile_7'] = $profile_7;
	}


	$profile_14 = explode(chr(13), $row["profile_14"]);
	$a_doctor[$i]['ex_profile_14'] = $profile_14;

	$profile_15 = explode(chr(13), $row["profile_15"]);
	$a_doctor[$i]['ex_profile_15'] = $profile_15;


	$a_doctor[$i]["profile_14"] = nl2br($a_doctor[$i]["profile_14"]);
	$a_doctor[$i]["profile_2"] = nl2br($a_doctor[$i]["profile_2"]);
	$a_doctor[$i]["profile_3"] = nl2br($a_doctor[$i]["profile_3"]);
	$a_doctor[$i]["profile_4"] = nl2br($a_doctor[$i]["profile_4"]);
	$a_doctor[$i]["profile_5"] = nl2br($a_doctor[$i]["profile_5"]);
	$a_doctor[$i]["profile_6"] = nl2br($a_doctor[$i]["profile_6"]);
	$a_doctor[$i]["profile_6"] = nl2br($a_doctor[$i]["profile_6"]);
	$a_doctor[$i]["profile_7"] = nl2br($a_doctor[$i]["profile_7"]);
	$a_doctor[$i]["profile_8"] = nl2br($a_doctor[$i]["profile_8"]);
	$a_doctor[$i]["profile_9"] = nl2br($a_doctor[$i]["profile_9"]);
	$a_doctor[$i]["profile_10"] = nl2br($a_doctor[$i]["profile_10"]);
	$a_doctor[$i]["profile_11"] = nl2br($a_doctor[$i]["profile_11"]);
	$a_doctor[$i]["profile_12"] = nl2br($a_doctor[$i]["profile_12"]);
	$a_doctor[$i]["profile_13"] = nl2br($a_doctor[$i]["profile_13"]);
	$a_doctor[$i]["profile_14"] = nl2br($a_doctor[$i]["profile_14"]);
	$a_doctor[$i]["profile_15"] = nl2br($a_doctor[$i]["profile_15"]);

	$a_doctor[$i]['a_timetable'] = explode("|", $a_doctor[$i][timetable]);

}
	return $a_doctor;
}


function get_br($br_cd) {
	$sql = 'select * from md_br  Where del_dt is null and br_cd = '.$br_cd.' Order By disp_ord Asc ';
	$br_row = sql_fetch($sql);
	$br_row[br_title3_1] = $br_row[br_title3];
	// $br_row[br_title3] = nl2br(str_replace(" ","&nbsp;",$br_row[br_title3]));
	$br_row[br_title3] = nl2br($br_row[br_title3]);
	$br_row[addr_1] = $br_row[addr];
	$br_row[addr] = nl2br(str_replace(" ","&nbsp;",$br_row[addr]));
	return $br_row;
}

function get_br_list() {
	$sql_string = 'select * from md_br  Where del_dt is null Order By disp_ord Asc ';
	$br_result = sql_query($sql_string);
	$br = array();
	for($i = 0; $row = sql_fetch_array($br_result); $i++){
			$br[$i] = $row;
			$br[$i][br_title3_1] = $br[$i][br_title3];
			// $br[$i][br_title3] = nl2br(str_replace(" ","&nbsp;",$br[$i][br_title3]));
			$br[$i][br_title3] = nl2br($br[$i][br_title3]);
			$br[$i][addr_1] = $br[$i][addr];
			$br[$i][addr] = nl2br(str_replace(" ","&nbsp;",$br[$i][addr]));
	}
	return $br;
}

function menuHref($menu){


	if ($menu["menu_content"] == "ETC" || $menu["menu_content"] == "MEMBER"){

		if(!empty($menu["menu_url"])){
			$href = $menu["menu_url"] . "/" . $menu["menu_id"];
		}else{
			$href = menuHref($menu["up_menu_id"]);
		}

	}else if($menu["menu_content"] == "BOARD"){
		$href = "/index.php/board/" . $menu["board_default"] . "/" . $menu["board_cd"] . "/" . $menu["menu_id"];
	}else if($menu["menu_content"] == "CONTENTS"){
		$href = "/index.php/html/" . $menu["menu_id"];
	}

	return $href;
}

function format_phone($phone){
    $phone = preg_replace("/[^0-9]/", "", $phone);
    $length = strlen($phone);
    switch($length){
      case 11 :
          return preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "$1-$2-$3", $phone);
      case 10:
          return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
      default :
          return $phone;
    }
}

function auth_check($menu_id)
{
	global $member;

	$sql	= " SELECT 
					COUNT(*) AS chk 
				FROM 
					md_role_menu 
				WHERE 
					role_seq	= '".$member[mb_role_seq]."' 
					AND menu_id	= '".$menu_id."' 
					AND del_dt IS NULL 
				";
	$row	= sql_fetch($sql);

	return $row["chk"];
}

?>
