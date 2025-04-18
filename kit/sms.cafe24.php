<?
	//==============================================//
	// SMS 갯수 리턴								//
	//==============================================//
	function sms_resource(){
		global $config;
		/******************** 인증정보 ********************/
		$sms_url = "http://sslsms.cafe24.com/sms_remain.php"; // 전송요청 URL
		$sms['user_id'] = base64_encode($config[sms_id]); // SMS 아이디
		$sms['secure'] = base64_encode($config[sms_key]) ;//인증키
		$sms['mode'] = base64_encode("1"); // base64 사용시 반드시 모드값을 1로 주셔야 합니다.

		$host_info = explode("/", $sms_url);
		$host = $host_info[2];
		$path = $host_info[3]."/".$host_info[4];
		srand((double)microtime()*1000000);
		$boundary = "---------------------".substr(md5(rand(0,32000)),0,10);

		// 헤더 생성
		$header = "POST /".$path ." HTTP/1.0\r\n";
		$header .= "Host: ".$host."\r\n";
		$header .= "Content-type: multipart/form-data, boundary=".$boundary."\r\n";

		// 본문 생성
		foreach($sms AS $index => $value){
			$data .="--$boundary\r\n";
			$data .= "Content-Disposition: form-data; name=\"".$index."\"\r\n";
			$data .= "\r\n".$value."\r\n";
			$data .="--$boundary\r\n";
		}
		$header .= "Content-length: " . strlen($data) . "\r\n\r\n";

		$fp = fsockopen($host, 80);
			


		if ($fp) {
			fputs($fp, $header.$data);
			$rsp = '';
			while(!feof($fp)) {
				$rsp .= fgets($fp,8192);
			}
			fclose($fp);
			$msg = explode("\r\n\r\n",trim($rsp));
			$Count = $msg[1]; //잔여건수
		}
		else {
			$Count = "Connection Failed";
		}
		$Count = ($Count < 0)?"0":$Count;
		return $Count;
	}


	//==============================================//
	// SMS 발송 함수								//
	// sphone			::	보낸이 번호				//
	// rphone			::	받는이 번호				//
	// msg				::	메시지					//
	// rdate			::	발송일자				//
	// rtime			::	발송시간				//
	//==============================================//

	function sms_send($sphone,$rphone,$msg,$rdate='',$rtime=''){
		global $config;
		$sphone = $config[site_mng_sms];
		$aSphone = explode("-", $sphone);

		$sphone1 = $aSphone[0];//발송자
		$sphone2 = $aSphone[1];//발송자
		$sphone3 = $aSphone[2];//발송자

	   /******************** 인증정보 ********************/
		$sms_url = "http://sslsms.cafe24.com/sms_sender.php"; // 전송요청 URL
		// $sms_url = "https://sslsms.cafe24.com/sms_sender.php"; // HTTPS 전송요청 URL
		$sms['user_id'] = base64_encode($config[sms_id]); //SMS 아이디.
		$sms['secure'] = base64_encode($config[sms_key]) ;//인증키
		$sms['msg'] = base64_encode(stripslashes($msg));

		$sms['rphone'] = base64_encode($rphone);
		$sms['sphone1'] = base64_encode($sphone1);
		$sms['sphone2'] = base64_encode($sphone2);
		$sms['sphone3'] = base64_encode($sphone3);
		if(!empty($rdate) && !empty($time)){
			$sms['rdate'] = base64_encode($rdate);
			$sms['rtime'] = base64_encode($rtime);
		}
		$sms['mode'] = base64_encode("1"); // base64 사용시 반드시 모드값을 1로 주셔야 합니다.
		$sms['returnurl'] = base64_encode($returnurl);
		$sms['destination'] = base64_encode($destination);
		$returnurl = $returnurl;
		$sms['repeatFlag'] = base64_encode($repeatFlag);
		$sms['repeatNum'] = base64_encode($repeatNum);
		$sms['repeatTime'] = base64_encode($repeatTime);
		$nointeractive = $nointeractive; //사용할 경우 : 1, 성공시 대화상자(alert)를 생략

		$host_info = explode("/", $sms_url);
		$host = $host_info[2];
		$path = $host_info[3]."/".$host_info[4];

		srand((double)microtime()*1000000);
		$boundary = "---------------------".substr(md5(rand(0,32000)),0,10);

		// 헤더 생성
		$header = "POST /".$path ." HTTP/1.0\r\n";
		$header .= "Host: ".$host."\r\n";
		$header .= "Content-type: multipart/form-data, boundary=".$boundary."\r\n";

		// 본문 생성
		foreach($sms AS $index => $value){
			$data .="--$boundary\r\n";
			$data .= "Content-Disposition: form-data; name=\"".$index."\"\r\n";
			$data .= "\r\n".$value."\r\n";
			$data .="--$boundary\r\n";
		}
		$header .= "Content-length: " . strlen($data) . "\r\n\r\n";

		$fp = fsockopen($host, 80);

		if ($fp) {
			fputs($fp, $header.$data);
			$rsp = '';
			while(!feof($fp)) {
				$rsp .= fgets($fp,8192);
			}
			fclose($fp);
			$msg = explode("\r\n\r\n",trim($rsp));
			$rMsg = explode(",", $msg[1]);
			$Result= $rMsg[0]; //발송결과
			$Count= $rMsg[1]; //잔여건수
		}
		else {	
			$Result = "CONN_ERR";
		}

		
		return $Result;
	}

	//==============================================//
	// SMS 오류 상태 리턴							//
	// result			::	상태 값					//
	//==============================================//
	function sms_status($result){
		if($result == "success") return "즉시 전송 성공";
		if($result == "reserved") return "예약 성공";
		if($result == "-100") return "서버 에러";
		if($result == "-101") return "변수 부족 에러";
		if($result == "-102") return "인증 에러";
		if($result == "-105") return "예약 시간 에러";
		if($result == "-110") return "1000건 이상 발송 불가";
		if($result == "-201") return "sms 건수 부족 에러";
		if($result == "-202") return "문자 '됬'은 사용불가능한 문자입니다.";
		if($result == "-203") return "sms 대량 발송 에러";
		if($result == "1") return "서비스 번호 오류";
		if($result == "2") return "메지시 구성 결여";
		if($result == "2") return "메지시 구성 결여";
		if($result == "3") return "메시지 포맷 오류";
		if($result == "4") return "메시지 body길이 오류";
		if($result == "5") return "Connect 필요";
		if($result == "99") return "기타 오류(DB오류시스템장애)";
		if($result == "44") return "스팸메시지 차단(배팅, 바카라, 도박, 섹스, liveno1 ,카지노 등을 포함한 스팸메시지는 발송이 실패됩니다.)";
		if($result == "3201") return "발송시각 오류";
		if($result == "3202") return "폰넘버 오류";
		if($result == "3203") return "SMS 메시지 Base64 Encoding 오류";
		if($result == "3204") return "CallBack메시지 Base64 Encoding 오류)";
		if($result == "3205") return "번호형식 오류";
		if($result == "3206") return "전송 성공";
		if($result == "3207") return "비가입자 결번 서비스정지";
		if($result == "3208") return "단말기 Power-off 상태";
		if($result == "3209") return "음영";
		if($result == "3210") return "단말기 메시지 FULL";
		if($result == "3211") return "기타에러(이통사)";
		if($result == "3214") return "기타에러(무선망)";
		if($result == "3213") return "번호이동관련";
		if($result == "3217") return "조합메시지 형식오류";
		if($result == "3218") return "메시지 중복 오류";
		if($result == "3219") return "월 송신건수 초과";
		if($result == "3220") return "UNKNOWN";
		if($result == "3221") return "착신번호 에러(자리수 에러)";
		if($result == "3222") return "착신번호 에러(없는 국번)";
		if($result == "3223") return "수신거부 메시지 부분 없음";
		if($result == "3224") return "21시 이후 광고";
	}



	//==============================================//
	// SMS 로그 저장								//
	// TR_SENDSTAT		::	상태 반환값				//
	// TR_CALLBACK		::	보낸이 번호				//
	// TR_PHONE			::	받는이 번호				//
	// TR_MSG			::	메시지					//
	// rdate			::	보낸날짜				//
	// rtime			::	보낸시간				//
	// TR_ETC1			::	보낸이이름				//
	// TR_ETC2			::	받는이이름				//
	//==============================================//
	function sms_insert($TR_SENDSTAT='',$TR_CALLBACK='',$TR_PHONE='',$TR_MSG='',$rdate='',$rtime='',$TR_ETC1='',$TR_ETC2=''){
		
		$TR_ETC3 = $rdate." ".$rtime;

		$sqlString  = "";	
		$sqlString .= " INSERT INTO SC_TRAN SET ";
		$sqlString .= " 	TR_SENDDATE	= now(),";
		$sqlString .= " 	TR_SERIALNUM	= '$TR_SERIALNUM',";
		$sqlString .= " 	TR_ID	= '$TR_ID',";
		$sqlString .= " 	TR_SENDSTAT	= '$TR_SENDSTAT',";
		$sqlString .= " 	TR_RSLTSTAT	= '$TR_RSLTSTAT',";
		$sqlString .= " 	TR_MSGTYPE	= '$TR_MSGTYPE',";
		//$sqlString .= " 	TR_PHONE	= '$TR_PHONE',";
		//$sqlString .= " 	TR_CALLBACK	= '$TR_CALLBACK',";
		$sqlString .= " 	TR_PHONE	= '',";
		$sqlString .= " 	TR_CALLBACK	= '',";
		$sqlString .= " 	TR_RSLTDATE	= '$TR_RSLTDATE',";
		$sqlString .= " 	TR_MODIFIED	= '$TR_MODIFIED',";
		$sqlString .= " 	TR_MSG	= '$TR_MSG',";
		$sqlString .= " 	TR_NET	= '$TR_NET',";
		$sqlString .= " 	TR_ETC1	= '$TR_ETC1',";
		$sqlString .= " 	TR_ETC2	= '$TR_ETC2',";
		$sqlString .= " 	TR_ETC3	= '$TR_ETC3',";
		$sqlString .= " 	TR_ETC4	= '$TR_ETC4',";
		$sqlString .= " 	TR_ETC5	= '$TR_ETC5',";
		$sqlString .= " 	TR_ETC6	= '$TR_ETC6' ";
		sql_query($sqlString);
	}

?>
