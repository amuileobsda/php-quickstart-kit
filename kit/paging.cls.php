<?
//==========================================//
// paging 클래스 사용법						//
// $p = new paging();						//
// $aPage = $p->calculate_pages(70, 10, 1); //
//==========================================//

class paging
{
	//==========================================
	// 생성자
	//==========================================
	public function __construct(){}

	//==============================================//
	// 페이징 생성 계산								//
	// iTotalCount		::	총 개수					//
	// iPagePerList		::	한페이지 표출 개수		//
	// iPagePerGroup	::	페이징 표출 개수		//
	// iCurPage			::	현재 페이지번호			//
	// link				::	추가 링크				//
	//==============================================//
	public function getPageArray($iTotalCount, $iPagePerList, $iPagePerGroup, $iCurPage, $link)
	{
		$aPage = array();
		// calculate last page
		$iLastPage = ceil($iTotalCount / $iPagePerList);
		$iLastPage = ($iLastPage == 0)?1:$iLastPage;
		
		// make sure we are within limits
		$iCurPage = (int) $iCurPage;
		if ($iCurPage < 1)
		{
		   $iCurPage = 1;
		} 
		elseif ($iCurPage > $iLastPage)
		{
		   $iCurPage = $iLastPage;
		}
		$upto = ($iCurPage - 1) * $iPagePerList;
		$aPage['limit'] = 'LIMIT '.$upto.',' .$iPagePerList;
		$aPage['current'] = $iCurPage;

		if (($iCurPage - 1) < 1)
			$aPage['prev'] = 1;
		else
			$aPage['prev'] = $iCurPage - 1;
		
		if (($iCurPage + $iPagePerGroup) < $iLastPage)
			$aPage['next'] = $iCurPage + $iPagePerGroup;	
		else
			$aPage['next'] = $iCurPage + 1;
			

		$aPage['last'] = $iLastPage;
		$aPage['info'] = 'Page ['.$iCurPage.' / '.$iLastPage.']';
		$aPage['link'] = $link;
		$aPage['pages'] = $this->getSurroundingPages($iCurPage, $iPagePerGroup, $iLastPage);

		return $aPage;
	}

	//==============================================//
	// 페이징 배열 생성 계산						//
	// iCurPage			::	현재 페이지 번호		//
	// iPagePerGroup	::	페이징 표출 개수		//
	// iLastPage		::	마지막 페이지 번호		//
	//==============================================//
	function getSurroundingPages($iCurPage, $iPagePerGroup=10,$iLastPage)
	{
		$aPage = array();
		$iPagePerGroup=10;

		if($iCurPage % $iPagePerGroup == 0){
			$iStNum = (((int)($iCurPage/$iPagePerGroup) -1) * $iPagePerGroup) + 1;
		}else{
			$iStNum = ((int)($iCurPage/$iPagePerGroup)) * $iPagePerGroup + 1;
		}
		
		$iStNum = ($iStNum >= $iLastPage)?$iLastPage:$iStNum;

		$iStNum = ($iCurPage <= $iPagePerGroup)?1:$iStNum;

		$iEndNum = (($iStNum + $iPagePerGroup ) < $iLastPage)?$iStNum + $iPagePerGroup:$iLastPage;


		if($iLastPage == 1){
		}else if($iLastPage < $iPagePerGroup){

			for($i=$iStNum; $i<=$iEndNum; $i++){
				array_push($aPage, $i);
			}
		}else{
			$iLoopCount = 1;

			for($i=$iStNum; $i<=$iEndNum; $i++){

				if($iLoopCount > $iPagePerGroup) break;
				array_push($aPage, $i);
				$iLoopCount++;
			}
		}

		if($iStNum == $iEndNum){
			array_push($aPage, $iEndNum);
		}

		return $aPage;
	}
}
?>
