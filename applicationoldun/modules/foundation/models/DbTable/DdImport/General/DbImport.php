<?php

class Foundation_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    public function updateItemsByImport($formData,$data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	for($i=2; $i<=$count; $i++){
    		$stu_code = $dbg->getnewStudentId($formData['branch'],1);
    		// $remark = $data[$i]['P'];
    		if(!empty($data[$i]['Q'])){
    			// $remark = "(".$data[$i]['Q'].") ".$data[$i]['P'];
    		}

			$param = array(
				'student_code'=>$data[$i]['B'],
				'stu_khname'=>$data[$i]['C']
			);

			$studentId = $this->checkStudentIdExist($param);

			if (!empty($studentId)) {
				if ($data[$i]['V'] == 'quit') {
					$array=array(
						'stop_type'=>1,
						'note'=>'version3'
					);
					$where="stu_id=".$studentId." AND academic_year=2";
					$this->_name = 'rms_group_detail_student';
					$this->update($array,$where);
				}
			}

			// 	$arr = array(
			// 		'branch_id' => $formData['branch'],
			// 		'user_id' => 1,
			// 		'stu_code' => $data[$i]['B'],
			// 		'stu_khname' => $data[$i]['C'],
			// 		'stu_enname' => trim($data[$i]['D']),
			// 		'last_name' => trim($data[$i]['E']),
			// 		// 'sex' => ($data[$i]['F'] == "M") ? 1 : 2,
			// 		'tel' => $data[$i]['G'],
			// 		'dob' => date("Y-m-d", strtotime($data[$i]['H'])),
			// 		'pob' => $data[$i]['I'],

			// 		'father_enname' => $data[$i]['J'],
			// 		'father_khname' => $data[$i]['J'],
			// 		'father_phone' => $data[$i]['K'],

			// 		'mother_khname' => $data[$i]['L'],
			// 		'mother_enname' => $data[$i]['L'],
			// 		'mother_phone' => $data[$i]['M'],

			// 		'guardian_first_name' => $data[$i]['N'],
			// 		'guardian_enname' => $data[$i]['N'],
			// 		'guardian_khname' => $data[$i]['N'],
			// 		'guardian_tel' => $data[$i]['O'],
			// 		'remark' => 'version2',
			// 		'customer_type' => 1,
			// 		'status' => 1,
			// 		'modify_date' => date("Y-m-d H:i:s"),
			// 		'create_date' => date("Y-m-d", strtotime($data[$i]['S']))
			// 	);
			// 	if (!empty($data[$i]['F'])) {
			// 		$arr['sex'] = ($data[$i]['F'] == "M") ? 1 : 2;
			// 	}
			// 	$this->_name = 'rms_student';
			// 	$studentId = $this->insert($arr);
			}

			// $degreeId = $this->degreeList($data[$i]['P']);
			// $gradeId =$data[$i]['Q'];

			// $param = array(
			// 	'room_name'=>$data[$i]['W']
			// );
			// $roomId = $this->getRoomId($param);

			// $param = array(
			// 		'branch_id'=>$formData['branch'],
			// 		'group_code'=>$data[$i]['R'],
			// 		'room_id'=>$roomId,
			// 		'academic_year'=>2,
			// 		'degree'=>$degreeId,
			// 		'grade'=>$gradeId,
			// 		'is_pass'=>1,
			// 		'is_use'=>1,
			// 		'tuitionfee_id'=>2,
			// 		'school_option'=>1,
			// 		'note'=>'version2',

			// );
			// $groupResult = $this->checkGroupExits($param);
			// if (!empty($groupResult)) {
			// 	$groupId = $groupResult['id'];
			// }else{
			// 	$groupId = $this->insertGroup($param);
			// }

			// $this->_name='rms_group_detail_student';
			// $arr = array(
			// 		'branch_id'		=>$formData['branch'],
			// 		'studentId'		=>$studentId,//$_data['stu_id_'.$k],
			// 		'itemType'		=>1,
			// 		'groupId'		=>$groupId,
			// 		'academicYear'	=>2,
			// 		'feeId'			=>2,//feeId
			// 		'oldFeeId'		=>1,//Old Fee Id
			// 		'schoolOption'  =>1,
			// 		'degree'		=>$degreeId,
			// 		'grade'			=>$gradeId,
			// 		'session'		=>'',
			// 		'startDate'		=>'',
			// 		'endDate'		=>'',
			// 		'balance'		=>0,
			// 		'discountType'	=>'',
			// 		'discountAmount'=>'',
			// 		'user_id'		=>$this->getUserId(),
			// 		'status'		=>1,
			// 		'create_date'	=>date('Y-m-d H:i:s'),
			// 		'modify_date'	=>date('Y-m-d H:i:s'),
			// 		'old_group'		=>'',
			// 		'isSetGroup'	=>empty($groupId)?0:1,
			// 		'stopType'		=>0,
			// 		'isCurrent'		=>1,
			// 		'isNewStudent'	=>0,
			// 		'isMaingrade'	=>1,//not sure
			// 		'entryFrom'	=>4,//not sure
			// 		'remark'	=>'version2'
			// );
			// if($data[$i]['V']=='quit'){
			// 	$arr['stopType'] = 1;
			// }
			// $dbg->AddItemToGroupDetailStudent($arr);
    	// }
    }
	function getRoomId($data){
		$sql = "SELECT room_id FROM rms_room WHERE 1 ";
		
		if(!empty($data['room_name'])){
			$sql.=" AND room_name='".$data['room_name']."'";
		}
		$db = $this->getAdapter();
		$roomId = $db->fetchOne($sql);
		if(empty($result)){
			$this->_name='rms_room';
			$arr = array(
				'branch_id'=>1,
				'room_name'=>$data['room_name']
			);
			return $this->insert($arr);

		}else{
			return $roomId;
		}
	}
	function checkStudentIdExist($data)
	{
		$sql = "SELECT stu_id From rms_student where 1";
		if(!empty($data['student_code'])){
			$sql .= " AND stu_code='" . $data['student_code']."'";
		}
		if(!empty($data['stu_khname'])){
			$sql .= " AND stu_khname='" . $data['stu_khname']."'";
		}
		return $this->getAdapter()->fetchOne($sql);
	}
	function degreeList($strDegree)
	{
		$degreeListArray = array(
			'Primary'=>1,
			'Secondary'=>2,
			'High School'=>3,
			'Kindergarten'=>4,
		);
		return $degreeListArray[$strDegree];

	}
	function insertGroup($_data)
	{

		$this->_name = 'rms_group';
		$_arr = array(
			'branch_id' 	=> $_data['branch_id'],
			'group_code' 	=> $_data['group_code'],
			'academic_year' => $_data['academic_year'],
			'degree' 		=> $_data['degree'],
			'grade' 		=> $_data['grade'],
			'school_option' => 1,
			'date' 			=> date("Y-m-d"),
			'status'   		=> 1,
			'user_id'	 	=> $this->getUserId(),
			'is_use' 		=> 1,
			'is_pass'		=> 1,
			'note'			=>'1'
		);
		return $this->insert($_arr);
	}
	function checkGroupExits($_data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_group where 1";
		if(!empty($_data['branch_id'])){
			$sql .= " AND branch_id=".$_data['branch_id'];
		}
		if(!empty($_data['academic_year'])){
			$sql .= " AND academic_year=".$_data['academic_year'];
		}
		if(!empty($_data['group_code'])){
			$sql .= " AND group_code='".$_data['group_code']."'";
		}
		if(!empty($_data['degree'])){
			$sql .= " AND degree='".$_data['degree']."'";
		}
		if(!empty($_data['grade'])){
			$sql .= " AND grade='".$_data['grade']."'";
		}
		// echo $sql;
		// exit();
		return $db->fetchRow($sql);
	}
	function importProduct($formData,$data)
	{
		
		$count = count($data);
		for ($i = 2; $i <= $count; $i++) {
			$param = array(
				'title'=>$data[$i]['D'],
				'title_en'=>$data[$i]['D'],
				'schoolOption'=>'1',

			);
			$categoryId = $this->checkProductCategory($param);

				$array = array(
					'items_id' => $categoryId,
					'code' => $data[$i]['B'],
					'title' => $data[$i]['C'],
					'title_en' => $data[$i]['C'],
					'product_type' => 1,
					'items_type' => 3,//product
					'schoolOption' => '1',
					'is_onepayment' => 1,
					'degreeOption' => '1,2,3,4',
				);

				$this->_name='rms_itemsdetail';
				$proId = $this->insert($array);

				$arr = array(
					'pro_id'=>$proId,
					'branch_id'=>$data['branch_id'],
					'pro_qty'=>0,
					'costing'=>$data[$i]['E'],
					'price'=>$data[$i]['F'],
					'price_set'=>$data[$i]['G'],
				);
				$this->_name='rms_product_location';
			$this->insert($arr);
		}
	}
	function checkProductCategory($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.id FROM rms_items AS i
			WHERE 1 ";
		
		if (!empty($data['title'])){
			$sql.=" AND i.title= '".$data['title']."'";
		}
		if (!empty($data['title_en'])){
			$sql.=" AND i.title_en = '".$data['title_en']."'";
		}
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		if (empty($itemId)) {
			$arr = array(
				'title'=>$data['title'],
				'title_en'=>$data['title_en'],
				'schoolOption'=>$data['schoolOption'],
				'type'=>3,
			);
			$this->_name = 'rms_items';
			return $this->insert($arr);
		} else {
			return $itemId;
		}
	}
	
	
	function checkFamilyInfo($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.id FROM rms_family AS i
			WHERE 1 ";
		$sql.=" AND i.fatherNameKh= '".$data['fatherNameKh']."'";
		$sql.=" AND i.fatherPhone = '".$data['fatherPhone']."'";
		$sql.=" AND i.motherNameKh = '".$data['motherNameKh']."'";
		$sql.=" AND i.motherPhone = '".$data['motherPhone']."'";
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		return $itemId;
	}
	function importFamily($formData,$data)
	{
		
		$count = count($data);
		for ($i = 2; $i <= $count; $i++) {
			$fatherPhone = empty($data[$i]['E']) ? "" : $data[$i]['E'];
			$fatherPhone = empty($data[$i]['F']) ? $fatherPhone : $fatherPhone." / ".$data[$i]['F'];
			
			$motherPhone = empty($data[$i]['I']) ? "" : $data[$i]['I'];
			$motherPhone = empty($data[$i]['J']) ? $motherPhone : $motherPhone." / ".$data[$i]['J'];
		
			
			$param = array(
				'familyType'	=>$data[$i]['P'],
				'laonNumber'	=>$data[$i]['O'],
				'familyCode'	=>$data[$i]['B'],
				
				'fatherName'	=>$data[$i]['C'],
				'fatherNameKh'	=>$data[$i]['D'],
				'fatherPhone'	=>$fatherPhone,
				'fatherNation'	=>1,
				'fatherJob'		=>0,
				
				'motherName'	=>$data[$i]['G'],
				'motherNameKh'	=>$data[$i]['H'],
				'motherPhone'	=>$motherPhone,
				'motherNation'	=>1,
				'motherJob'		=>0,
				
				'street'		=>$data[$i]['M'],
				'houseNo'		=>$data[$i]['N'],
				'villageId'		=>0,
				'communeId'		=>0,
				'districtId'	=>0,
				'provinceId'	=>12,
				'note'	=>$data[$i]['R'],
				'createDate'	=>date("Y-m-d H:i:s"),
				'modifyDate'	=>date("Y-m-d H:i:s"),
				'userId'		=>1,

			);
			$familyId = $this->checkFamilyInfo($param);
			if(empty($familyId)){
				$this->_name = 'rms_family';
				$this->insert($param);
			}
		}
	}
	
	
	function checkProductLocation($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.* FROM v_product_location AS i
			WHERE 1 ";
		
		if (!empty($data['code'])){
			$sql.=" AND i.code= '".$data['code']."'";
		}
		$sql.=" LIMIT 1 ";
		return $itemId= $db->fetchRow($sql);
		
	}
	function importUpdateProductLocation($formData,$data)
	{
		
		
		
		$count = count($data);
		for ($i = 2; $i <= $count; $i++) {
			$param = array(
				'code'=>$data[$i]['C'],
			);
			$rs = $this->checkProductLocation($param);
			
			if(!empty($rs)){
				$array=array(
					'costing'=>$data[$i]['G'],
					'price'=>$data[$i]['H'],
					'price_set'=>$data[$i]['I'],
					'pro_qty'=>$data[$i]['F'],
					'note'=>'imp updated stock info',
				);
				$where="pro_id=".$rs["pro_id"]." AND branch_id=1";
				$where.=" AND id=".$rs["id"];
				$this->_name = 'rms_product_location_copy';
				$this->update($array,$where);
			}
		}
	}
	
	
	function checkPreImport($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.id FROM rms_importing_record AS i
			WHERE 1 ";
		$sql.=" AND i.branchCode= '".$data['branchCode']."'";
		$sql.=" AND i.enrollAcademicTitle = '".$data['enrollAcademicTitle']."'";
		$sql.=" AND i.stuCode = '".$data['stuCode']."'";
		$sql.=" AND i.groupCode = '".$data['groupCode']."'";
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		return $itemId;
	}
	function checkBranch($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.br_id AS id FROM rms_branch AS i
			WHERE 1 ";
		$sql.=" AND i.abbreviations= '".$data['branchCode']."'";
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		if(empty($itemId)){
			$arr = array(
				'abbreviations'	=>$data['branchCode'],
				'branch_namekh'	=>$data['branchCode'],
				'branch_nameen'	=>$data['branchCode'],
				'status'	=>1,
			);
			$this->_name = 'rms_branch';
			return $this->insert($arr);
		}
		return $itemId;
	}
	function checkAcademic($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.id FROM rms_academicyear AS i
			WHERE 1 ";
		$sql.=" AND i.fromYear= '".$data['fromYear']."'";
		$sql.=" AND i.toYear = '".$data['toYear']."'";
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		if(empty($itemId)){
			$arr = array(
				'fromYear'	=>$data['fromYear'],
				'toYear'	=>$data['toYear'],
				'status'	=>1,
				'createDate'	=>date("Y-m-d H:i:s"),
				'modifyDate'	=>date("Y-m-d H:i:s"),
			);
			$this->_name = 'rms_academicyear';
			return $this->insert($arr);
		}
		return $itemId;
	}
	function checkDegree($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.id FROM rms_items AS i
			WHERE 1 ";
		$sql.=" AND i.title_en= '".$data['title']."'";
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		if(empty($itemId)){
			$arr = array(
				'title'		=>$data['title'],
				'title_en'	=>$data['title'],
				'type'		=>1,
				'status'	=>1,
				'create_date'	=>date("Y-m-d H:i:s"),
				'modify_date'	=>date("Y-m-d H:i:s"),
			);
			$this->_name = 'rms_items';
			return $this->insert($arr);
		}
		return $itemId;
	}
	
	function checkGrade($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.id FROM rms_itemsdetail AS i
			WHERE 1 ";
		$sql.=" AND TRIM(REPLACE(i.`title_en`,'Grade ','')) = '".$data['title']."'";
		$sql.=" AND i.items_id = ".$data['degree'];
		if (!empty($data['items_type'])){
			$sql.=" AND i.items_type = ".$data['items_type'];
		}
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		if(empty($itemId)){
			$arr = array(
				'title'			=>$data['title'],
				'title_en'		=>$data['title'],
				'items_id'		=>$data['degree'],
				'items_type'		=>1,
				'status'		=>1,
				'create_date'	=>date("Y-m-d H:i:s"),
				'modify_date'	=>date("Y-m-d H:i:s"),
			);
			$this->_name = 'rms_itemsdetail';
			return $this->insert($arr);
		}
		return $itemId;
	}
	
	function checkRoom($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.room_id AS id FROM rms_room AS i
			WHERE 1 ";
		$sql.=" AND i.room_name= '".$data['roomName']."'";
		$sql.=" AND i.branch_id = ".$data['branchId'];
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		if(empty($itemId)){
			$arr = array(
				'branch_id'		=>$data['branchId'],
				'room_name'		=>$data['roomName'],
				'is_active'		=>1,
				'modify_date'	=>date("Y-m-d H:i:s"),
			);
			$this->_name = 'rms_room';
			$itemId = $this->insert($arr);
		}
		return $itemId;
	}
	
	function checkSession($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.id FROM rms_parttime_list AS i
			WHERE 1 ";
		$sql.=" AND i.title= '".$data['title']."'";
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		if(empty($itemId)){
			$arr = array(
				'degreeId'		=>"3,2,1,4",
				'title'			=>$data['title'],
				'titleKh'		=>$data['title'],
				'status'		=>1,
				'createDate'	=>date("Y-m-d H:i:s"),
				'modifyDate'	=>date("Y-m-d H:i:s"),
			);
			$this->_name = 'rms_parttime_list';
			return $this->insert($arr);
		}
		return $itemId;
	}
	
	function checkGroup($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.id FROM rms_group AS i
			WHERE 1 ";
		$sql.=" AND i.group_code= '".$data['groupCode']."'";
		$sql.=" AND i.academic_year= '".$data['academicYear']."'";
		$sql.=" AND i.branch_id = ".$data['branchId'];
		$sql.=" AND i.degree = ".$data['degree'];
		$sql.=" AND i.grade = ".$data['grade'];
		$sql.=" LIMIT 1 ";
		
		$groupId= $db->fetchOne($sql);
		if(empty($groupId)){
			
			$dbGb = new Application_Model_DbTable_DbGlobal();
			$last = $dbGb->getLatestAcadmicYear();
			$currentAcademic = 0;
			if(!empty($last)){
				$currentAcademic = empty($last["id"]) ? 0 : $last["id"];
			}
			$this->_name = 'rms_group';	
			$arrGroup = array(
				'branch_id'		=>$data['branchId'],
				'academic_year'	=>$data['academicYear'],
				'group_code'	=>$data['groupCode'],
				'degree'		=>$data['degree'],
				'grade'			=>$data['grade'],
				'session'		=>$data['sessionId'],
				'room_id'		=>$data['roomId'],
				'status'		=>1,
				'is_use'		=>1,
				'is_pass'		=> ($data['academicYear'] == $currentAcademic ) ? 2 : 1,
				'create_date'	=>date("Y-m-d H:i:s"),
				'date'	=>date("Y-m-d H:i:s"),
			);
			
			$this->insert($arrGroup);
			
			$sql="
			SELECT 
				i.id FROM rms_group AS i
				WHERE 1 ";
			$sql.=" AND i.group_code= '".$data['groupCode']."'";
			$sql.=" AND i.academic_year= '".$data['academicYear']."'";
			$sql.=" AND i.branch_id = ".$data['branchId'];
			$sql.=" AND i.degree = ".$data['degree'];
			$sql.=" AND i.grade = ".$data['grade'];
			$sql.=" LIMIT 1 ";
			
			return $db->fetchOne($sql);
		}
		return $groupId;
	}
	
	function checkViewType($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.key_code AS id 
			FROM rms_view AS i
			WHERE 1 ";
		$sql.=" AND i.type= ".$data['type'];
		$sql.=" AND i.name_en = '".$data['title']."' ";
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		if(empty($itemId)){
			
			$sqlRow="
				SELECT 
					i.key_code 
				FROM rms_view AS i
				WHERE 1 ";
			$sqlRow.=" AND i.type= ".$data['type'];
			$sqlRow.=" ORDER BY i.key_code DESC LIMIT 1 ";
			$keyCode = $db->fetchOne($sqlRow);
			$keyCode = empty($keyCode) ? 1 : $keyCode+1;
		
			$arr = array(
				'name_en'			=>$data['title'],
				'name_kh'			=>$data['title'],
				'type'			=>$data['type'],
				'status'		=>1,
				'key_code'		=>$keyCode
			);
			$this->_name = 'rms_view';
			$this->insert($arr);
			
			return $keyCode;
		}
		return $itemId;
	}
	
	function checkFamilyImport($data){
		$db = $this->getAdapter();
		
		$fatherPhone = empty($data['fatherPhone']) ? "" : $data['fatherPhone'];
		$fatherPhone = empty($data['fatherPhone2']) ? $fatherPhone : $fatherPhone." / ".$data['fatherPhone2'];
		
		$motherPhone = empty($data['motherPhone']) ? "" : $data['motherPhone'];
		$motherPhone = empty($data['motherPhone2']) ? $motherPhone : $motherPhone." / ".$data['motherPhone2'];
		
			
		$sql="
			SELECT 
				i.id FROM rms_family AS i
			WHERE 1 ";
		$sql.=" AND i.fatherNameKh= '".$data['fatherNameKh']."'";
		$sql.=" AND i.fatherPhone = '".$fatherPhone."'";
		$sql.=" AND i.motherNameKh = '".$data['motherNameKh']."'";
		$sql.=" AND i.motherPhone = '".$motherPhone."'";
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		
		if(empty($itemId)){
			
			
			
			$arr = array(
				'familyType'	=>$data['familyType'],
				'laonNumber'	=>$data['loanCode'],
				'familyCode'	=>$data['familyCode'],
				
				'fatherName'	=>$data['fatherName'],
				'fatherNameKh'	=>$data['fatherNameKh'],
				'fatherPhone'	=>$fatherPhone,
				'fatherNation'	=>1,
				'fatherJob'		=>0,
				
				'motherName'	=>$data['motherName'],
				'motherNameKh'	=>$data['motherNameKh'],
				'motherPhone'	=>$motherPhone,
				'motherNation'	=>1,
				'motherJob'		=>0,
				
				'street'		=>$data['street'],
				'houseNo'		=>$data['homeNum'],
				'villageId'		=>0,
				'communeId'		=>0,
				'districtId'	=>0,
				'provinceId'	=>12,
				'createDate'	=>date("Y-m-d H:i:s"),
				'modifyDate'	=>date("Y-m-d H:i:s"),
				'userId'		=>1,
			);
			$this->_name = 'rms_family';
			return $this->insert($arr);
		}
		
		return $itemId;
	}
	
	function importStudentAndFamliy($formData,$data)
	{
		
		$count = count($data);
		for ($i = 2; $i <= $count; $i++) {
			$fatherPhone = empty($data[$i]['Z']) ? "" : $data[$i]['Z'];
			$fatherPhone = empty($data[$i]['AA']) ? $fatherPhone : $fatherPhone." / ".$data[$i]['AA'];
			
			$motherPhone = empty($data[$i]['AD']) ? "" : $data[$i]['AD'];
			$motherPhone = empty($data[$i]['AE']) ? $motherPhone : $motherPhone." / ".$data[$i]['AE'];
		
			$enrollAcademicTitle = $data[$i]['B'];
			$exploAca = 	explode("-", $enrollAcademicTitle);
			$arrCheck=[
				'fromYear'	=>empty($exploAca[0]) ? "" : $exploAca[0],
				'toYear'	=>empty($exploAca[1]) ? "" : $exploAca[1],
			];
			$enrollAcademic = $this->checkAcademic($arrCheck);
			
			$academicYearTitle = $data[$i]['P'];
			$exploAcaYear = 	explode("-", $academicYearTitle);
			$arrCheckYear=[
				'fromYear'	=>empty($exploAcaYear[0]) ? "" : $exploAcaYear[0],
				'toYear'	=>empty($exploAcaYear[1]) ? "" : $exploAcaYear[1],
			];
			$academicYear = $this->checkAcademic($arrCheckYear);
			
			$branchCode = $data[$i]['A'];
			$arrCheckBranch=[
				'branchCode'	=>	$branchCode,
			];
			$branchId = $this->checkBranch($arrCheckBranch);
			
			$degreeTitle = $data[$i]['Q'];
			$arrCheckDegree=[
				'title'	=>	$degreeTitle,
			];
			$degreeId = $this->checkDegree($arrCheckDegree);
			
			$gradeTitle = $data[$i]['R'];
			$arrCheckGrade=[
				'title'		=>	$gradeTitle,
				'degree'	=>	$degreeId,
				'items_type'	=>	1,
			];
			$gradeId = $this->checkGrade($arrCheckGrade);
			
			$roomName = $data[$i]['U'];
			$arrRoomName=[
				'roomName'		=>	$roomName,
				'branchId'	=>	$branchId,
			];
			$roomId = $this->checkRoom($arrRoomName);
			
			$sessionTitle = ($data[$i]['T'] =="Full Time") ? "Full Day" : $data[$i]['T'];
			$arrRoomName=[
				'title'		=>	$sessionTitle,
				'branchId'	=>	$branchId,
			];
			$sessionId = $this->checkSession($arrRoomName);
			
			$groupCode = $data[$i]['S'];
			$groupId = 0;
			if(!empty($groupCode)){
				$arrCheckGroup=[
					'branchId'		=>	$branchId,
					'academicYear'	=>	$academicYear,
					'groupCode'		=>	$groupCode,
					'degree'		=>	$degreeId,
					'grade'			=>	$gradeId,
					'roomId'		=>	$roomId,
					'sessionId'		=>	$sessionId,
				];
				$groupId = $this->checkGroup($arrCheckGroup);	
			}
			
			
			
			$studentTypeTitle = $data[$i]['M'];
			$studentType = 0;
			if(!empty($studentTypeTitle)){
				$arrCheckStuType=[
					'title'		=>	$studentTypeTitle,
					'type'		=>	40,
				];
				$studentType = $this->checkViewType($arrCheckStuType);
			}
			
			
			
			$familyTypeTitle = $data[$i]['AI'];
			$familyType = 0;
			if(!empty($familyTypeTitle)){
				$arrCheckFamily=[
					'title'		=>	$familyTypeTitle,
					'type'		=>	41,
				];
				$familyType = $this->checkViewType($arrCheckFamily);
			}
			
			$goHomeTypeTitle = $data[$i]['N'];
			$goHomeType = 1;
			if($goHomeTypeTitle=="By Parents"){
				$goHomeType = 2;
			}
			$param = array(
				'branchCode'			=>$branchCode,
				'branchId'				=>$branchId,
				'enrollAcademic'		=>$enrollAcademic,
				'enrollAcademicTitle'	=>$enrollAcademicTitle,
				'enrollDate'			=>empty($data[$i]['C']) ? null : date("Y-m-d",strtotime($data[$i]['C'])),
				
				'stuCode'		=>$data[$i]['D'],
				'lastName'		=>$data[$i]['E'],
				'firstName'		=>$data[$i]['F'],
				'lastNameKh'	=>$data[$i]['G'],
				'firstNameKh'	=>$data[$i]['H'],
				'sex'			=>($data[$i]['I']=="F") ? 2 : 1,
				'dob'			=>empty($data[$i]['J']) ? null : date("Y-m-d",strtotime($data[$i]['J'])),
				'pob'			=>$data[$i]['K'],
				'tel'			=>$data[$i]['L'],
				
				'studentType'			=>$studentType,
				'studentTypeTitle'		=>$studentTypeTitle,
				'goHomeType'		=>$goHomeType,
				'goHomeTypeTitle'		=>$goHomeTypeTitle,
				
				'createDate'			=>empty($data[$i]['O']) ? null : date("Y-m-d H:i:s",strtotime($data[$i]['O'])),
				'academicYear'			=>$academicYear,
				'academicYearTitle'		=>$academicYearTitle,
				
				'degree'			=>$degreeId,
				'degreeTitle'		=>$degreeTitle,
				
				'grade'				=>$gradeId,
				'gradeTitle'		=>$gradeTitle,
				
				'groupId'			=>$groupId,
				'groupCode'			=>$groupCode,
				
				'sessionId'		=>$sessionId,
				'sessionTitle'		=>$sessionTitle,
				
				'roomId'			=>$roomId,
				'roomName'			=>$roomName,
				
				'studyStatus'			=>($data[$i]['V']=="Active") ? 1 : 0,
				
				'familyCode'			=>$data[$i]['W'],
				'familyCode'			=>$data[$i]['W'],
				'fatherName'			=>$data[$i]['X'],
				'fatherNameKh'			=>$data[$i]['Y'],
				'fatherPhone'			=>$data[$i]['Z'],
				'fatherPhone2'			=>$data[$i]['AA'],
				
				
				'motherName'	=>$data[$i]['AB'],
				'motherNameKh'	=>$data[$i]['AC'],
				'motherPhone'	=>$data[$i]['AD'],
				'motherPhone2'	=>$data[$i]['AE'],
				
				'street'	=>$data[$i]['AF'],
				'homeNum'	=>$data[$i]['AG'],
				'loanCode'	=>$data[$i]['AH'],
				'photoName'	=>empty($data[$i]['AO']) ? null : $data[$i]['AO'],
				
				'familyType'		=>$familyType,
				'familyTypeTitle'	=>$familyTypeTitle,
				
				'testResult'	=>$data[$i]['AK'],
				'age'	=>empty($data[$i]['AN']) ? null : $data[$i]['AN'],
				'importingDate'	=>date("Y-m-d H:i:s"),

			);
			
			$familyId = $this->checkFamilyImport($param);
			$param["familyId"] = empty($familyId) ? 0 : $familyId;
			
			$studentId = $this->checkStudentImport($param);
			$param["studentId"] = empty($studentId) ? 0 : $studentId;
			
			$groupDetailId = $this->checkStudentStudyImport($param);
			$param["groupDetailId"] = empty($groupDetailId) ? 0 : $groupDetailId;
			
			
			$checkingPre = $this->checkPreImport($param);
			if(empty($checkingPre)){
				$this->_name = 'rms_importing_record';
				$this->insert($param);
			}
			
			
		}
	}
	
	function checkStudentImport($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.stu_id AS id FROM rms_student AS i
			WHERE 1 ";
		$sql.=" AND i.branch_id= ".$data['branchId'];
		$sql.=" AND i.stu_code= '".$data['stuCode']."'";
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		
		if(empty($itemId)){
			
			$fatherPhone = empty($data['fatherPhone']) ? "" : $data['fatherPhone'];
			$fatherPhone = empty($data['fatherPhone2']) ? $fatherPhone : $fatherPhone." / ".$data['fatherPhone2'];
			
			$motherPhone = empty($data['motherPhone']) ? "" : $data['motherPhone'];
			$motherPhone = empty($data['motherPhone2']) ? $motherPhone : $motherPhone." / ".$data['motherPhone2'];
			
			$lastNameKh = empty($data["lastNameKh"]) ? "" : $data["lastNameKh"];
			$firstNameKh = empty($data["firstNameKh"]) ? "" : $data["firstNameKh"];
			$fullNameKh = $lastNameKh." ".$firstNameKh;
			
			$studentToken = 'PSIS'.$data['branchCode']. date('YmdHis');
			$arr = array(
				'branch_id'		=>$data['branchId'],
				'stu_code'		=>$data['stuCode'],
				'last_name'		=>$data['lastName'],
				'stu_enname'	=>$data['firstName'],
				'stu_khname'	=>$fullNameKh,
				'customer_type'	=>1,
				
				'sex'			=>$data['sex'],
				'nationality'	=>1,
				'nation'		=>1,
				'dob'			=>$data['dob'],
				'pob'			=>$data['pob'],
				'age'			=>$data['age'],
				'tel'			=>$data['tel'],
				
				'academicYearEnroll'	=>$data['enrollAcademic'],
				'studentType'			=>$data['studentType'],
				'goHomeType'			=>$data['goHomeType'],
				'familyId'				=>$data['familyId'],
				
				'home_num'			=>$data['homeNum'],
				'street_num'		=>$data['street'],
				'province_id'		=>12,
				'district_name'		=>0,
				'commune_name'		=>0,
				'village_name'		=>0,
				
				'status'		=>1,
				'create_date'	=>date("Y-m-d H:i:s"),
				'modify_date'	=>date("Y-m-d H:i:s"),
				'user_id'		=>1,
				'studentToken'		=>$studentToken,
				'photo'		=>empty($data['photoName']) ? null : $data['photoName'].".png",
			);
			$this->_name = 'rms_student';
			return $this->insert($arr);
		}
		
		return $itemId;
	}
	
	function checkStudentStudyImport($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				i.gd_id AS id FROM rms_group_detail_student AS i
			WHERE 1 ";
		$sql.=" AND i.branch_id= ".$data['branchId'];
		$sql.=" AND i.stu_id= ".$data['studentId'];
		$sql.=" AND i.academic_year= ".$data['academicYear'];
		$sql.=" AND i.group_id= ".$data['groupId'];
		$sql.=" AND i.degree= ".$data['degree'];
		$sql.=" AND i.grade= ".$data['grade'];
		$sql.=" LIMIT 1 ";
		$itemId= $db->fetchOne($sql);
		
		if(empty($itemId)){
			
			$sqlRow="
				SELECT 
					i.* 
				FROM rms_group_detail_student AS i
				WHERE 1 ";
			$sqlRow.=" AND i.is_current= 1 ";
			$sqlRow.=" AND i.stu_id= ".$data['studentId'];
			$sqlRow.=" ORDER BY i.gd_id DESC LIMIT 1 ";
			$row = $db->fetchRow($sqlRow);
			$oldGroupId = empty($row["group_id"]) ? 0 : $row["group_id"];
			
			$arrUpdate = array(
				'is_current'	=>0,
				'is_pass'		=>1,
			);
			$whereUpdate = " itemType = 1 AND is_current = 1 AND stu_id=".$data['studentId'];
			$whereUpdate.= " AND group_id=".$oldGroupId;
			$this->_name = 'rms_group_detail_student';
			$this->update($arrUpdate, $whereUpdate);
			
			$arr = array(
				'branch_id'		=>$data['branchId'],
				'stu_id'		=>$data['studentId'],
				'academic_year'	=>$data['academicYear'],
				'old_group'		=>$oldGroupId,
				'group_id'		=>$data['groupId'],
				'degree'		=>$data['degree'],
				'grade'			=>$data['grade'],
				'session'		=>$data['sessionId'],
				'is_setgroup'	=>empty($data['groupId']) ? 0 : 1,
				
				'itemType'		=>1,
				'is_maingrade'	=>1,
				'is_current'	=>1,
				'status'		=>1,
				'is_pass'		=>0,
				'stop_type'		=>($data['studyStatus']==1) ? 0 : 1,
				'entryFrom'		=>7,
				
				'status'		=>1,
				'create_date'	=>date("Y-m-d H:i:s"),
				'modify_date'	=>date("Y-m-d H:i:s"),
				'user_id'		=>1,
			);
			$this->_name = 'rms_group_detail_student';
			$detailId = $this->insert($arr);
			
			$stopType = ($data['studyStatus']==1) ? 0 : 1;
			if($stopType==1){
				$arrDrop = array(
					'branch_id'		=>$data['branchId'],
					'type'			=>$stopType,
					'study_id'		=>$detailId,
					'stu_id'		=>$data['studentId'],
					'gender'		=>$data['studentId'],
					'date_stop'		=>date("Y-m-d"),
					
					'group'			=>$data['groupId'],
					'academic_year'	=>$data['academicYear'],
					'degree'		=>$data['degree'],
					'grade'			=>$data['grade'],
					'session'		=>$data['sessionId'],
					'room'			=>$data['roomId'],
					'note'			=>'Stop From Import',
					'reason'		=>'Stop From Import',
					
					'modify_date'	=>date("Y-m-d H:i:s"),
					'isReturn'		=>0,
					'reasonId'		=>0,
					'user_id'		=>1,
				);
				$this->_name = 'rms_student_drop';
				$this->insert($arrDrop);
			}
			return $detailId;
		}
		
		return $itemId;
	}
}   

