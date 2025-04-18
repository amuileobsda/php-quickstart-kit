<?
//===============================================//
// Code Class									 //
//===============================================//

	class codedata
	{

		var $code;
		//==========================================
		// 생성자
		//==========================================
		public function __construct(){
			//mysql result object
			global $code;

			$this->code = $code;
		}

		public function getName($up_cd_id, $selectValue='',$use_color=true)
		{

			if($this->code[$up_cd_id]==null) return "";

			foreach($this->code[$up_cd_id] as $key => $value){

				if(!empty($selectValue) && $selectValue == $value[cd_id]){
					$color = $value[cd_color];
					if(!empty($color) && $use_color){
						$returnString = "<span style=\"color:$color;\">".$value[cd_name]."</span>";
					}else{
						$returnString = $value[cd_name];
					}
					break;
				}
			}
			return $returnString;
		}

		public function getColor($up_cd_id, $selectValue='')
		{

			if($this->code[$up_cd_id]==null) return "";

			foreach($this->code[$up_cd_id] as $key => $value){

				if(!empty($selectValue) && $selectValue == $value[cd_id]){
					$returnString = $value[cd_color];
					break;
				}
			}

			return $returnString;
		}

		public function getId($up_cd_id, $selectValue='')
		{
			if($this->code[$up_cd_id]==null) return "";

			foreach($this->code[$up_cd_id] as $key => $value){
				if(!empty($selectValue) && $selectValue == $value[cd_name]){
					$returnString = $value[cd_id];
					break;
				}
			}

			return $returnString;
		}

		public function getValue($up_cd_id, $selectValue='')
		{
			if($this->code[$up_cd_id]==null) return "";

			foreach($this->code[$up_cd_id] as $key => $value){
				if(!empty($selectValue) && $selectValue == $value[cd_name]){
					$returnString = $value[cd_id];
					break;
				}
			}

			return $returnString;
		}

		public function getCodeArray($up_cd_id='')
		{

			if($this->code[$up_cd_id]==null) return "";

			$aCodeArray = array();
			foreach($this->code[$up_cd_id] as $key => $value){
				if($up_cd_id == $value[up_cd_id]){
					$cd_id = $value[cd_id];
					$cd_name = $value[cd_name];
					$aCodeArray[$cd_id] = $cd_name;
				}
			}
			return $aCodeArray;
		}

		public function getCodeId($up_cd_id, $selectName='')
		{

			if($this->code[$up_cd_id]==null) return "";

			foreach($this->code[$up_cd_id] as $key => $value){
					if(strpos($value[cd_name], $selectName) !== false) {
						$returnString = $value[cd_id];
						break;
					}
				}
			return $returnString;
		}

		
		public function getOptionString($up_cd_id='', $selectValue='', $addOption='', $addString='')
		{

			if($this->code[$up_cd_id]==null) return "";

			$iLoopCount = 0;
			foreach($this->code[$up_cd_id] as $key => $value){
				if($up_cd_id == $value[up_cd_id]){
					$check_status = (!empty($selectValue) && $selectValue == $value[cd_id])?"selected":"";
					$returnString .= "<option value='".$value[cd_id]."' $check_status>".$value[cd_name]."</option>";
				}

				$iLoopCount++;
			}
			return $returnString;
		}

		public function getOptionStringColor($up_cd_id='', $selectValue='', $addOption='', $addString='')
		{

			if($this->code[$up_cd_id]==null) return "";

			$iLoopCount = 0;
			foreach($this->code[$up_cd_id] as $key => $value){
				if($up_cd_id == $value[up_cd_id]){
					$check_status = (!empty($selectValue) && $selectValue == $value[cd_id])?"selected":"";
					$returnString .= "<option style='color:".$value[cd_color].";' value='".$value[cd_id]."' $check_status>".$value[cd_name]."</option>";
				}

				$iLoopCount++;
			}
			return $returnString;
		}

		public function getRadioString($up_cd_id='', $selectValue='', $addOption='', $addString='', $st_string='', $end_string='')
		{
			if($this->code[$up_cd_id]==null) return "";
			$iLoopCount = 0;
			foreach($this->code[$up_cd_id] as $key => $value){
				if($up_cd_id == $value[up_cd_id]){
					$check_status = (empty($selectValue) && $iLoopCount == 0)?"checked":"";
					$check_status = (!empty($selectValue) && $selectValue == $value[cd_id])?"checked":$check_status;
					$returnString .= $st_string."<input style=\"vertical-align:middle;\" type=\"radio\" ".$addOption." value=\"".$value[cd_id]."\" id=\"".$value[cd_id]."\" $check_status><label class=\"input_radio\" for=\"".$value[cd_id]."\"> ".$value[cd_name]."</label>".$end_string.$addString."\n";

				}
				$iLoopCount++;
			}
			return $returnString;
		}

		public function getCheckBoxString($up_cd_id='', $selectValue='', $addOption='', $addString='', $st_string='', $end_string='')
		{

			if($this->code[$up_cd_id]==null) return "";

			foreach($this->code[$up_cd_id] as $key => $value){
				if($up_cd_id == $value[up_cd_id]){

					$check_status = (!empty($selectValue) && $selectValue == $value[cd_id])?"checked":"";
					$check_status = (!empty($selectValue) && strstr($selectValue, $value[cd_id]))?"checked":$check_status;

					$returnString .= $st_string."<input style=\"vertical-align:middle;\" type=\"checkbox\" ".$addOption." value=\"".$value[cd_id]."\" id=\"".$value[cd_id]."\" $check_status>&nbsp;<label
					for=\"".$value[cd_id]."\">".$value[cd_name]."</label>".$end_string.$addString."\n";
				}
			}
			return $returnString;
		}

		public function getHiddenCheckBoxString($up_cd_id='', $selectValue='', $addOption='', $addString='')
		{
			if($this->code[$up_cd_id]==null) return "";

			foreach($this->code[$up_cd_id] as $key => $value){
				if($up_cd_id == $value[up_cd_id]){
					$check_status = (!empty($selectValue) && $selectValue == $value[cd_id])?"checked":"";
					$check_status = (!empty($selectValue) && strstr($selectValue, $value[cd_id]))?"checked":$check_status;
					$returnString .= "<input style=\"vertical-align:middle;\" type=\"hidden\" ".$addOption." value=\"".$value[cd_id]."\" id=\"".$value[cd_id]."\" >".$addString."\n";
				}
			}
			return $returnString;
		}
	}
?>
