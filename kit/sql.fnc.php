<?
	
//==============================================//
// 데이터 베이스 접속							//
// host				::	접속 서버				//
// user				::	사용자 아이디			//
// pass				::	사용자 비밀번호			//
//==============================================//

function sql_connect($host, $user, $pass, $db){
	global $md;

	if (strtoupper($md['charset']) == 'UTF-8') @mysqli_query(" set names utf8 ");
	else if (strtoupper($md['charset']) == 'EUC-KR') @mysqli_query(" set names euckr ");
	return @mysqli_connect($host, $user, $pass, $db);
}
//==============================================//
// 데이터 베이스 선택							//
// db				::	데이터베이스명			//
// connect			::	접속 링크				//
//==============================================//
function sql_select_db($db, $connect){
	global $md;
	if (strtoupper($md['charset']) == 'UTF-8') @mysqli_query(" set names utf8 ");
	else if (strtoupper($md['charset']) == 'EUC-KR') @mysqli_query(" set names euckr ");
	return @mysqli_select_db($db, $connect);
}
//==============================================//
// 쿼리 실행									//
// sql				::	쿼리구문				//
// error			::	에러반환 여부			//
//==============================================//
function sql_query($sql, $error=TRUE){
	global $md;
	if ($error)
		$result = @mysqli_query($md[connect],$sql) or die("<p>$sql<p>" . mysqli_errno($md[connect]) . " : " .  mysqli_error($md[connect]) . "<p>error file : $_SERVER[PHP_SELF]");
	else
		$result = @mysqli_query($md[connect],$sql);
			

	return $result;
}

//==============================================//
// 데이터 베이스 한행 반환						//
// sql				::	쿼리구문				//
// error			::	에러반환 여부			//
//==============================================//
function sql_fetch($sql, $error=TRUE){
	$result = sql_query($sql, $error);
	$row = sql_fetch_array($result);
	return $row;
}


// 결과값에서 한행 연관배열(이름으로)로 얻는다.
function sql_fetch_array($result){
	$row = @mysqli_fetch_assoc($result);
	return $row;
}
// $result에 대한 메모리(memory)에 있는 내용을 모두 제거한다.
// sql_free_result()는 결과로부터 얻은 질의 값이 커서 많은 메모리를 사용할 염려가 있을 때 사용된다.
// 단, 결과 값은 스크립트(script) 실행부가 종료되면서 메모리에서 자동적으로 지워진다.
function sql_free_result($result){
	return mysqli_free_result($result);
}


function sql_password($value){
	// mysql 4.0x 이하 버전에서는 password() 함수의 결과가 16bytes
	// mysql 4.1x 이상 버전에서는 password() 함수의 결과가 41bytes
	$row = sql_fetch(" select md5('$value') as pass ");
	return $row[pass];
}

function sql_password2($value){
	// mysql 4.0x 이하 버전에서는 password() 함수의 결과가 16bytes
	// mysql 4.1x 이상 버전에서는 password() 함수의 결과가 41bytes
	$row = sql_fetch(" select password('$value') as pass ");
	return $row[pass];
}

?>
