<?
/* 	달력 소스 시작  */

class calenderView {

	var $startEmpty, $lastEmpty, $startWeek, $lastDay, $weekLine, $prevDate, $nextDate, $nowDate, $prevDateVal, $nextDateVal, $nowDateVal, $nowYear, $nowMonth, $nowDay, $totalEmpty, $checkDay = 1;
	

	///////////////////////////////////////////////////////////
	///		startEmpty	: 시작 공백							///
	///		lastEmpty	: 끝 공백							///
	///		startWeek	: 시작 요일							///
	///		lastDay		: 마지막날짜						///
	///		weekLine	: 총 주갯수							///
	///		prevDate	: 이전년월표기						///
	///		nextDate	: 다음년월표기						///
	///		nowDate		: 현재년월표기						///
	///		prevDateVal	: 이전년월시스템					///
	///		nextDateVal	: 다음년월시스템					///
	///		nowDateVal	: 기준년월시스템					///
	///		nowYear		: 기준년도							///
	///		nowMonth	: 기준월 							///
	///		nowDay		: 기준일 							///
	///		checkDay	: 계산일 							///
	///////////////////////////////////////////////////////////

	function settingCalender($inputDate){
		
		$Date = explode("-", $inputDate);
		$this->nowDateVal = $inputDate;
		$this->nowDate = $Date[0] . ". " . $Date[1];
		$this->nowYear = $Date[0];
		$this->nowMonth = $Date[1];
		$this->nowDay = $Date[2];

		$this->lastDay = date("t", mktime(0, 0, 0, $this->nowMonth, $this->nowDay, $this->nowYear));
		$this->startWeek = date("N", mktime(0, 0, 0, $this->nowMonth, 1, $this->nowYear));
		$this->startEmpty = $this->startWeek % 7;
		$this->weekLine = ceil(($this->lastDay + $this->startEmpty) / 7);
		$this->totalEmpty = $this->weekLine * 7;
		$this->lastEmpty = $this->totalEmpty - ($this->lastDay + $this->startEmpty);


		$this->prevDate = date("Y. m", mktime(0, 0, 0, $this->nowMonth - 1, $this->nowDay, $this->nowYear));
		$this->nextDate = date("Y. m", mktime(0, 0, 0, $this->nowMonth + 1, $this->nowDay, $this->nowYear));

		$this->prevDateVal = date("Y-m-d", mktime(0, 0, 0, $this->nowMonth - 1, $this->nowDay, $this->nowYear));
		$this->nextDateVal = date("Y-m-d", mktime(0, 0, 0, $this->nowMonth + 1, $this->nowDay, $this->nowYear));

	}

	function getWeekNumber($day){
		
		$returnValue = date("N", mktime(0, 0, 0, $this->nowMonth, $day, $this->nowYear));
		return $returnValue;

	}
	
}

function valueSize($day){

	if(strlen($day) == 1){
		return "0" . $day;
	}else{
		return $day;
	}

}
?>
