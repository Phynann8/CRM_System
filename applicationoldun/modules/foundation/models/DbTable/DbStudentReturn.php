<?php

class Foundation_Model_DbTable_DbStudentReturn extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_student_return';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getAllStudentDropReturn($search){
		$_db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		
		$branch = $dbp->getBranchDisplay();

		$colunmname='title_en';
		$label="name_en";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "SELECT  
					sdr.id,
					b.$branch AS branch_name,
					s.stu_code AS titleRecord,
					CONCAT(ac.fromYear, '-', ac.toYear) AS academic,

					id1.$colunmname AS grade,
					g.group_code AS group_name,

					
					
					id2.$colunmname AS gradeReturn,
					gr.group_code AS groupReturn,

					sdr.returnDate
					,CASE 
						WHEN sdr.returnType =1  THEN '".$tr->translate("RETURN_TO_CRM")."' 
						WHEN sdr.returnType =2  THEN '".$tr->translate("RETURN_TO_GROUP")."' 
						WHEN sdr.returnType =3  THEN '".$tr->translate("RETURN_TO_TEST")."' 
						ELSE ''
					END AS typeReturn
					,u.first_name AS user_name
						
				";
				$dbUser = new Application_Model_DbTable_DbUsers();
				$permission = $dbUser->getAccessUrl("foundation","studentreturn","rollback");
				if (empty($permission)){
					$sql.=" 
						,'' AS slqButton
					";
				}else{
					$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
					$urlRolllback = $base_url."/foundation/studentreturn/rollback/id/";
					$arr=[
						"id"=>"sdr.id",
						"urlEdit"=>$urlRolllback,
						"title"=>"ROLLBACK",
						"btnIcon"=>"fa-repeat",
					];
					$sqlBtn=$dbp->slqRowButton($arr);
					$sql.=" 
						,CASE 
							WHEN sdr.status = 0 THEN '' 
							ELSE ".$sqlBtn." 
						END AS slqButton
					";
				}
				$sql.=" 
					, sdr.status statusRecord 
					,CONCAT(COALESCE(s.stu_khname,''),' / ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS subTitleRecord
					,i2.$colunmname AS gradeReturnSmallInfo
					,i1.$colunmname AS gradeSmallInfo
				";
				
				$sql .= "
				FROM `rms_student_return` AS sdr
				LEFT JOIN `rms_branch` AS b ON b.br_id = sdr.branchId
				LEFT JOIN `rms_student` AS s ON s.stu_id = sdr.stuId
				LEFT JOIN `rms_academicyear` AS ac ON ac.id = sdr.academicYear

				LEFT JOIN `rms_items` AS i1 ON i1.id = sdr.degree AND i1.type = 1
				LEFT JOIN `rms_itemsdetail` AS id1 ON id1.id = sdr.grade AND id1.items_type = 1
				LEFT JOIN `rms_group` AS g ON g.id = sdr.group

				LEFT JOIN `rms_items` AS i2 ON i2.id = sdr.degreeReturn AND i2.type = 1
				LEFT JOIN `rms_itemsdetail` AS id2 ON id2.id = sdr.gradeReturn AND id2.items_type = 1
				LEFT JOIN `rms_group` AS gr ON gr.id = sdr.groupReturn

				LEFT JOIN `rms_users` AS u ON u.id = sdr.userId 
				WHERE 1
				";

				$from_date = (empty($search['start_date'])) ? '1' : " sdr.returnDate >= '".$search['start_date']." 00:00:00'";
				$to_date   = (empty($search['end_date'])) ? '1' : " sdr.returnDate <= '".$search['end_date']." 23:59:59'";
				$where     = " AND " . $from_date . " AND " . $to_date;

				$sql .= $where;
				$order_by = " ORDER BY sdr.id DESC";

		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " REPLACE((SELECT s.stu_code FROM `rms_student` AS s WHERE s.stu_id=sdr.stuId LIMIT 1),' ','')LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT s.stu_khname FROM `rms_student` AS s WHERE s.stu_id=sdr.stuId LIMIT 1),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=sdr.stuId LIMIT 1),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.group LIMIT 1 ),' ','') LIKE '%{$s_search}%'";
			$s_where[] = " REPLACE((SELECT g.group_code FROM `rms_group` AS g WHERE g.id=sdr.groupReturn LIMIT 1 ),' ','') LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=" AND sdr.branchId = ".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$where.=" AND sdr.academicYear = ".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$where.=" AND sdr.degree=".$search['degree'];
		}
		if(!empty($search['grade'])){
			$where.=" AND sdr.grade=".$search['grade'];
		}
		if(!empty($search['dropReturnType'])){
			$where.=" AND sdr.returnType=".$search['dropReturnType'];
		}
		
		$where.=$dbp->getAccessPermission('sdr.branchId');
		$where.=$dbp->getDegreePermission('sdr.degree');
		// echo $sql.$where.$order_by;
		// exit();
		return $_db->fetchAll($sql.$where.$order_by);
	}
	
	public function getStudentDropReturnById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_return WHERE id =".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branchId');
		$sql.=$dbp->getDegreePermission('degree');
		return $db->fetchRow($sql);
	}
	function addStudentDropReturn($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
			$_dbgb = new Application_Model_DbTable_DbGlobal();
			$return_type = $_data['return_type'];
			$is_new = $_data['is_new'];
			$oldGroupId = $_data['groupId'];
			$newGroupId = $_data['group'];

			$stu_id = $_data['stu_id'];
			$newDegreeId = $_data['degree'];
			$newGradeId = $_data['grade'];
			$feeId = $_data['feeId'];
			
			$academic_year=0;
			$dbGroup = new Foundation_Model_DbTable_DbGroup();
			$group_info = $dbGroup->getGroupById($newGroupId);
			if(!empty($group_info)){
				$academic_year = $group_info['academic_year'];
			}
			$_arr= array(
						'branchId'		=> $_data['branch_id'],
						'dropId'		=>$_data['drop_id'],
						'stuId'			=>$stu_id,
						'group'			=>$oldGroupId,
						'degree'		=>$_data['degreeId'],
						'grade'			=>$_data['gradeId'],
						
						'academicYear'	=> $_data['academic_year'],
						'degreeReturn'	=> $newDegreeId,
						'gradeReturn'	=> $newGradeId,
						'groupReturn'	=> $newGroupId,
						'returnType' 	=>$_data['return_type'],
						'returnDate'	=> $_data['return_date'],
						'note'	 		=> $_data['note'],						
						
						'userId'		=> $this->getUserId(),
						'createDate'	=> date('Y-m-d H:i:s'),
						'modifyDate'	=> date('Y-m-d H:i:s'),
				);
				$this->_name="rms_student_return";
				$dropReturnId = $this->insert($_arr);

				$_arrStuDropRecord=array(
						'isReturn'	=>1,
				);
				$this->_name="rms_student_drop";
				$whereStuDropRecord="id = ".$_data['drop_id'];
				$this->update($_arrStuDropRecord,$whereStuDropRecord);

				if($return_type ==1){
					/*Copy Student to crm */
					if($is_new==1){ // return to test regiser new id
						$stuInfo = $this->getStudentById($stu_id);
						$_arr=array(
							'branch_id'	  	 => $stuInfo['branch_id'],
							'kh_name' 	   	 => $stuInfo['stu_khname'],
							'first_name'   	 => $stuInfo['stu_khname'],
							'last_name'    	 => $stuInfo['last_name'],
							'sex'         	 => $stuInfo['sex'],
							'know_by'		 => $stuInfo['know_by'],
							'tel'            => $stuInfo['tel'],
							'current_address'=> $stuInfo['address'],
							'note'           => 'From Student Return',
							'crm_status'     => 1,
							'create_date'    => date("Y-m-d H:i:s"),
							'modify_date'    => date("Y-m-d H:i:s"),
							'user_id'	     => $this->getUserId()
						);
						$this->_name="rms_crm";
						$crmId =  $this->insert($_arr);

						$stuToken = $_dbgb->getStudentToken();
						$array = array(
								'oldStudentId'	 => $stu_id,
								'branch_id'	 	 => $stuInfo['branch_id'],
								'studentType'	 => $stuInfo['studentType'],
								'academicYearEnroll'=> $stuInfo['academicYearEnroll'],
								'familyId'	 	 => $stuInfo['familyId'],
								'goHomeType'	 => $stuInfo['goHomeType'],
								'crm_id'	 	 => $crmId,
								'customer_type'	 =>3,
								'stu_khname'	 => $stuInfo['stu_khname'],
								'stu_enname'     => $stuInfo['stu_enname'],
								'last_name'		 => $stuInfo['last_name'],
								'sex'			 => $stuInfo['sex'],
								'tel'		     => $stuInfo['tel'],
								'crm_degree'     => $newDegreeId,
								'crm_grade'      => $newGradeId,
								'age'            => $stuInfo['age'],
								'nationality'    => $stuInfo['nationality'],
								'nation'         => $stuInfo['nation'],
								'dob'            => $stuInfo['dob'],
								'pob'            => $stuInfo['pob'],
								'email'          => $stuInfo['email'],
								'address'        => $stuInfo['address'],
								'home_num'       => $stuInfo['home_num'],
								'street_num'     => $stuInfo['street_num'],
								'village_name'   => $stuInfo['village_name'],
								'commune_name'   => $stuInfo['commune_name'],
								'province_id'    => $stuInfo['province_id'],
								'father_enname'  => $stuInfo['father_enname'],
								'father_khname'  => $stuInfo['father_khname'],
								'father_dob'     => $stuInfo['father_dob'],
								'father_nation'  => $stuInfo['father_nation'],
								'father_job'     => $stuInfo['father_job'],
								'father_phone'   => $stuInfo['father_phone'],
								'mother_khname'  => $stuInfo['mother_khname'],
								'mother_enname'  => $stuInfo['mother_enname'],
								'mother_nation'  => $stuInfo['mother_nation'],
								'mother_phone'   => $stuInfo['mother_phone'],
								'guardian_first_name' => $stuInfo['guardian_first_name'],
								'guardian_enname'=> $stuInfo['guardian_enname'],
								'guardian_khname'=> $stuInfo['guardian_khname'],
								'guardian_dob'   => $stuInfo['guardian_dob'],
								'guardian_nation'=> $stuInfo['guardian_nation'],
								'guardian_tel'   => $stuInfo['guardian_tel'],
								'street'         => $stuInfo['street'],
								'comm_id'        => $stuInfo['comm_id'],
								'vill_id'        => $stuInfo['vill_id'],
								'dis_id'         => $stuInfo['dis_id'],
								'pro_id'         => $stuInfo['pro_id'],
								'photo'          => $stuInfo['photo'],
								'father_photo'   => $stuInfo['father_photo'],
								'mother_photo'   => $stuInfo['mother_photo'],
								'guardian_photo' => $stuInfo['guardian_photo'],
								'from_school'    => $stuInfo['from_school'],
								'know_by'        => $stuInfo['know_by'],
								'studentToken'   =>$stuToken,
								'create_date'    => date("Y-m-d H:i:s"),
								'modify_date'    => date("Y-m-d H:i:s"),
								'user_id'	     => $this->getUserId()
								);
						$this->_name="rms_student";
						$newStuId = $this->insert($array);

						/* insert student to rms_group_detail_student */
						$school_option = $_dbgb->getSchoolOptionbyDegree($newDegreeId);
						$_arr = array(
							'branch_id'			=>$_data['branch_id'],
							'academic_year'		=>$_data['academic_year'],
							'stu_id'			=>$newStuId,
							'is_newstudent'		=>1,
							'status'			=>1,
							'group_id'			=>0,
							'old_group'			=>$oldGroupId,	
							'degree'			=>$newDegreeId,
							'grade'				=>$newGradeId,
							'school_option'		=>$school_option,
							'is_current'		=>1,
							'is_setgroup'		=>0,
							'is_maingrade'		=>1,
							'create_date'		=>date("Y-m-d H:i:s"),
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
							'itemType'			=>1,
							'entryFrom'			=>7, // student return 
						);
						$this->_name="rms_group_detail_student";
						$this->insert($_arr);

					}else if($is_new==2){ // return to test but use old ID

						$_arrTest = array(
							'customer_type'        =>4,
							'is_studenttest'       =>1,
							'crm_degree'           =>$newDegreeId,
							'crm_grade'            =>$newGradeId,
							'create_date_stu_test' =>date("Y-m-d H:i:s"),
							'modify_date'		   =>date("Y-m-d H:i:s"),
							'user_id'			   =>$this->getUserId(),
						);
						$this->_name="rms_student";
						$wheretest=" stu_id = ".$stu_id;
						$this->update($_arrTest,$wheretest);


						$_arrOldGroupDetail = array(
							'is_current'		=>0,
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
						);
						$this->_name="rms_group_detail_student";
						$whereOldGroupDetail=" stu_id = ".$stu_id." AND group_id = ".$oldGroupId."  AND itemType=1 AND is_current=1 ";
					
						$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
						$isSetgroup=0;
						(!empty($newGroupId))?$isSetgroup=1:($isSetgroup=0);
						$school_option = $_dbgb->getSchoolOptionbyDegree($newDegreeId);
						$_arrNewGroupDetail = array(
								'branch_id'			=>$_data['branch_id'],
								'stu_id'			=>$stu_id,
								'status'			=>1,
								'old_group'			=>$oldGroupId,	
								'degree'			=>$newDegreeId,
								'grade'				=>$newGradeId,
								'academic_year'		=>$_data['academic_year'],
								'is_current'		=>1,
								'stop_type'			=>10, // return to test but use old ID
								'feeId'				=>$feeId,
								'is_setgroup'		=>$isSetgroup,
								'school_option'		=>$school_option,
								'is_maingrade'		=>1,
								'note'				=>"New Group Detail From Student Return",
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$this->getUserId(),
								'entryFrom'			=>7, // student return 
						);
						$this->_name="rms_group_detail_student";
						$this->insert($_arrNewGroupDetail);
					}
				}else if($return_type ==3){  /// return to testing Department
				
					/*Copy Student to crm */
					if($is_new==1){ // return to test regiser new id
						$stuInfo = $this->getStudentById($stu_id);
						$stuToken = $_dbgb->getStudentToken();
						$newSerial = $_dbgb->getTestStudentId($_data['branch_id']);
						
						$array = array(
								'serial'	 => $newSerial,
								'oldStudentId'	 => $stu_id,
								'branch_id'	 	 => $stuInfo['branch_id'],
								'studentType'	 => $stuInfo['studentType'],
								'academicYearEnroll'=> $stuInfo['academicYearEnroll'],
								'familyId'	 	 => $stuInfo['familyId'],
								'goHomeType'	 => $stuInfo['goHomeType'],
								'customer_type'	 =>4,
								'is_studenttest'	 =>1,
								'stu_khname'	 => $stuInfo['stu_khname'],
								'stu_enname'     => $stuInfo['stu_enname'],
								'last_name'		 => $stuInfo['last_name'],
								'sex'			 => $stuInfo['sex'],
								'tel'		     => $stuInfo['tel'],
								'age'            => $stuInfo['age'],
								'nationality'    => $stuInfo['nationality'],
								'nation'         => $stuInfo['nation'],
								'dob'            => $stuInfo['dob'],
								'pob'            => $stuInfo['pob'],
								'email'          => $stuInfo['email'],
								'address'        => $stuInfo['address'],
								'home_num'       => $stuInfo['home_num'],
								'street_num'     => $stuInfo['street_num'],
								'village_name'   => $stuInfo['village_name'],
								'commune_name'   => $stuInfo['commune_name'],
								'province_id'    => $stuInfo['province_id'],
								'father_enname'  => $stuInfo['father_enname'],
								'father_khname'  => $stuInfo['father_khname'],
								'father_dob'     => $stuInfo['father_dob'],
								'father_nation'  => $stuInfo['father_nation'],
								'father_job'     => $stuInfo['father_job'],
								'father_phone'   => $stuInfo['father_phone'],
								'mother_khname'  => $stuInfo['mother_khname'],
								'mother_enname'  => $stuInfo['mother_enname'],
								'mother_nation'  => $stuInfo['mother_nation'],
								'mother_phone'   => $stuInfo['mother_phone'],
								'guardian_first_name' => $stuInfo['guardian_first_name'],
								'guardian_enname'=> $stuInfo['guardian_enname'],
								'guardian_khname'=> $stuInfo['guardian_khname'],
								'guardian_dob'   => $stuInfo['guardian_dob'],
								'guardian_nation'=> $stuInfo['guardian_nation'],
								'guardian_tel'   => $stuInfo['guardian_tel'],
								'street'         => $stuInfo['street'],
								'comm_id'        => $stuInfo['comm_id'],
								'vill_id'        => $stuInfo['vill_id'],
								'dis_id'         => $stuInfo['dis_id'],
								'pro_id'         => $stuInfo['pro_id'],
								'photo'          => $stuInfo['photo'],
								'father_photo'   => $stuInfo['father_photo'],
								'mother_photo'   => $stuInfo['mother_photo'],
								'guardian_photo' => $stuInfo['guardian_photo'],
								'from_school'    => $stuInfo['from_school'],
								'know_by'        => $stuInfo['know_by'],
								'studentToken'   =>$stuToken,
								'create_date_stu_test'    => date("Y-m-d H:i:s"),
								'create_date'    => date("Y-m-d H:i:s"),
								'modify_date'    => date("Y-m-d H:i:s"),
								'user_id'	     => $this->getUserId()
								);
						$this->_name="rms_student";
						$newStuId = $this->insert($array);

						/* insert student to rms_group_detail_student */
						$school_option = $_dbgb->getSchoolOptionbyDegree($newDegreeId);
						$_arr = array(
							'branch_id'			=>$_data['branch_id'],
							'academic_year'		=>$_data['academic_year'],
							'stu_id'			=>$newStuId,
							'is_newstudent'		=>1,
							'status'			=>1,
							'group_id'			=>0,
							'old_group'			=>$oldGroupId,	
							'degree'			=>$newDegreeId,
							'grade'				=>$newGradeId,
							'school_option'		=>$school_option,
							'is_current'		=>1,
							'is_setgroup'		=>0,
							'is_maingrade'		=>1,
							'create_date'		=>date("Y-m-d H:i:s"),
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
							'itemType'			=>1,
							'entryFrom'			=>7, // student return 
						);
						$this->_name="rms_group_detail_student";
						$this->insert($_arr);

					}else if($is_new==2){ // return to test but use old ID

						$_arrTest = array(
							'customer_type'        =>4,
							'is_studenttest'       =>1,
							'crm_degree'           =>$newDegreeId,
							'crm_grade'            =>$newGradeId,
							'create_date_stu_test' =>date("Y-m-d H:i:s"),
							'modify_date'		   =>date("Y-m-d H:i:s"),
							'user_id'			   =>$this->getUserId(),
						);
						$this->_name="rms_student";
						$wheretest=" stu_id = ".$stu_id;
						$this->update($_arrTest,$wheretest);

						$_arrOldGroupDetail = array(
							'is_current'		=>0,
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
						);
						$this->_name="rms_group_detail_student";
						$whereOldGroupDetail=" stu_id = ".$stu_id." AND group_id = ".$oldGroupId."  AND itemType=1 AND is_current=1 ";
					
						$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
						$isSetgroup=0;
						(!empty($newGroupId))?$isSetgroup=1:($isSetgroup=0);
						$school_option = $_dbgb->getSchoolOptionbyDegree($newDegreeId);
						$_arrNewGroupDetail = array(
								'branch_id'			=>$_data['branch_id'],
								'stu_id'			=>$stu_id,
								'status'			=>1,
								'old_group'			=>$oldGroupId,	
								'degree'			=>$newDegreeId,
								'grade'				=>$newGradeId,
								'academic_year'		=>$_data['academic_year'],
								'is_current'		=>1,
								'stop_type'			=>10, // return to test but use old ID
								'feeId'				=>$feeId,
								'is_setgroup'		=>$isSetgroup,
								'school_option'		=>$school_option,
								'is_maingrade'		=>1,
								'note'				=>"New Group Detail From Student Return",
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$this->getUserId(),
								'entryFrom'			=>7, // student return 
						);
						$this->_name="rms_group_detail_student";
						$this->insert($_arrNewGroupDetail);
					}
				
				}else{  /// return to class

					if($is_new==1){  // new Id 

						$stuInfo = $this->getStudentById($stu_id);
						$stuToken = $_dbgb->getStudentToken();
						$stuCode = $_dbgb->getnewStudentId($stuInfo['branch_id'],$newDegreeId);
						
						
							
						$array = array(
								'oldStudentId'	 => $stu_id,
								'branch_id'	 	 => $stuInfo['branch_id'],
								'studentType'	 => $stuInfo['studentType'],
								'academicYearEnroll'=> $stuInfo['academic_year'],
								'familyId'	 	 => $stuInfo['familyId'],
								'goHomeType'	 => $stuInfo['goHomeType'],
								'customer_type'	 => 1,
								'stu_code'	 	 => $stuCode,
								'stu_khname'	 => $stuInfo['stu_khname'],
								'stu_enname'     => $stuInfo['stu_enname'],
								'last_name'		 => $stuInfo['last_name'],
								'sex'			 => $stuInfo['sex'],
								'tel'		     => $stuInfo['tel'],
								'crm_degree'     => $newDegreeId,
								'crm_grade'      => $newGradeId,
								'age'            => $stuInfo['age'],
								'nationality'    => $stuInfo['nationality'],
								'nation'         => $stuInfo['nation'],
								'dob'            => $stuInfo['dob'],
								'pob'            => $stuInfo['pob'],
								'email'          => $stuInfo['email'],
								'address'        => $stuInfo['address'],
								'home_num'       => $stuInfo['home_num'],
								'street_num'     => $stuInfo['street_num'],
								'village_name'   => $stuInfo['village_name'],
								'commune_name'   => $stuInfo['commune_name'],
								'province_id'    => $stuInfo['province_id'],
								'father_enname'  => $stuInfo['father_enname'],
								'father_khname'  => $stuInfo['father_khname'],
								'father_dob'     => $stuInfo['father_dob'],
								'father_nation'  => $stuInfo['father_nation'],
								'father_job'     => $stuInfo['father_job'],
								'father_phone'   => $stuInfo['father_phone'],
								'mother_khname'  => $stuInfo['mother_khname'],
								'mother_enname'  => $stuInfo['mother_enname'],
								'mother_nation'  => $stuInfo['mother_nation'],
								'mother_phone'   => $stuInfo['mother_phone'],
								'guardian_first_name' => $stuInfo['guardian_first_name'],
								'guardian_enname'=> $stuInfo['guardian_enname'],
								'guardian_khname'=> $stuInfo['guardian_khname'],
								'guardian_dob'   => $stuInfo['guardian_dob'],
								'guardian_nation'=> $stuInfo['guardian_nation'],
								'guardian_tel'   => $stuInfo['guardian_tel'],
								'street'         => $stuInfo['street'],
								'comm_id'        => $stuInfo['comm_id'],
								'vill_id'        => $stuInfo['vill_id'],
								'dis_id'         => $stuInfo['dis_id'],
								'pro_id'         => $stuInfo['pro_id'],
								'photo'          => $stuInfo['photo'],
								'father_photo'   => $stuInfo['father_photo'],
								'mother_photo'   => $stuInfo['mother_photo'],
								'guardian_photo' => $stuInfo['guardian_photo'],
								'from_school'    => $stuInfo['from_school'],
								'know_by'        => $stuInfo['know_by'],
								'studentToken'   =>$stuToken,
								'create_date'    => date("Y-m-d H:i:s"),
								'modify_date'    => date("Y-m-d H:i:s"),
								'user_id'	     => $this->getUserId()
								);
						$this->_name="rms_student";
						$newStuId = $this->insert($array);
						
						if(!empty($newStuId)){
							$_dbStCode = new Application_Model_DbTable_DbStudentCode();
							$arrUp =[
								"referenceType" =>4,
								"referenceId" 	=>$dropReturnId,
								"stuCode" 		=>$stuCode,
								"stuId" 		=>$newStuId,
								"branchId" 		=>$stuInfo['branch_id'],
								"acadedmicYear"	=>$academic_year,
								"degree" 		=>$newDegreeId,
							];
							$_dbStCode->insertStudentCode($arrUp);
						}
						

						$_arrOldGroupDetail = array(
							'is_current'		=>0,
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
						);
						$this->_name="rms_group_detail_student";
						$whereOldGroupDetail=" stu_id = ".$stu_id." AND itemType=1 AND is_current=1 ";
					
						$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
						$isSetgroup=0;
						(!empty($newGroupId))?$isSetgroup=1:($isSetgroup=0);
						$school_option = $_dbgb->getSchoolOptionbyDegree($newDegreeId);

						$_arrNewGroupDetail = array(
								'stu_id'			=>$newStuId,
								'status'			=>1,
								'branch_id'			=>$_data['branch_id'],
								'group_id'			=>$newGroupId,
								'old_group'			=>$oldGroupId,	
								'degree'			=>$newDegreeId,
								'grade'				=>$newGradeId,
								'academic_year'		=>$academic_year,
								'is_current'		=>1,
								'stop_type'			=>0, // return to test but use old ID
								'feeId'				=>$feeId,
								'is_setgroup'		=>$isSetgroup,
								'school_option'		=>$school_option,
								'is_maingrade'		=>1,
								'note'				=>"New Group Detail From Student Return",
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$this->getUserId(),
								'entryFrom'			=>7, // student return 
						);
						$this->_name="rms_group_detail_student";
						$this->insert($_arrNewGroupDetail);

					}else{ //  old id

						$_arrOldGroupDetail = array(
							'is_current'		=>0,
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
						);
						$this->_name="rms_group_detail_student";
						$whereOldGroupDetail=" stu_id = ".$stu_id." AND itemType=1 AND is_current=1 ";
					
						$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
						$isSetgroup=0;
						(!empty($newGroupId))?$isSetgroup=1:($isSetgroup=0);
						$_arrNewGroupDetail = array(
								'stu_id'			=>$stu_id,
								'branch_id'			=>$_data['branch_id'],
								'status'			=>1,
								'group_id'			=>$newGroupId,
								'old_group'			=>$oldGroupId,	
								'degree'			=>$newDegreeId,
								'grade'				=>$newGradeId,
								'academic_year'		=>$academic_year,
								'is_current'		=>1,
								'stop_type'			=>0, // return to test but use old ID
								'feeId'				=>$feeId,
								'is_setgroup'		=>$isSetgroup,
								'is_maingrade'		=>1,
								'note'				=>"New Group Detail From Student Return",
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$this->getUserId(),
								'entryFrom'			=>7, // student return 
						);
						$this->_name="rms_group_detail_student";
						$this->insert($_arrNewGroupDetail);

					}
				}
			$_db->commit();
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
			exit();
		}
	}

	public function checkNewStudentbyOldId($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student WHERE oldStudentId =".$id;
		return $db->fetchRow($sql);
	}
	


	public function rollBackStudentReturn($data=[]){
		
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			
			$id = empty($data["returnId"]) ? 0 : $data["returnId"];
			$returnInfo = $this->getStudentDropReturnById($id);
			if(!empty($returnInfo)){

                $newStudenInfo= $this->checkNewStudentbyOldId($returnInfo['stuId']);

				if(!empty($newStudenInfo)){

					$stuArr=array(
					 'is_current'	=> 1,
					);
					$whereStu=" itemType=1 ";
					$whereStu.=" AND is_current=0 ";
					$whereStu.=" AND stu_id=".$returnInfo['stuId'];
					$whereStu.=" AND group_id=".$returnInfo["group"];

					$this->_name='rms_group_detail_student';
					$this->update($stuArr, $whereStu);


					$this->_name = 'rms_group_detail_student';
					$whereDeletegsd=" itemType=1 ";
					$whereDeletegsd.= " AND stu_id = " . $newStudenInfo['stu_id'];
					$this->delete($whereDeletegsd);

					if(!empty($newStudenInfo['crm_id'])){
						$this->_name = 'rms_crm';
						$wherecrm = " id = " . $newStudenInfo['crm_id'];
						$this->delete($wherecrm);
					}

					$this->_name = 'rms_student';
					$whereDeleteSt = " oldStudentId = " . $returnInfo['stuId'];
					$this->delete($whereDeleteSt);
					
					$_dbStCode = new Application_Model_DbTable_DbStudentCode();
					$arrUp =[
						"referenceType" =>4,
						"referenceId" 	=>$id ,
						"stuId" 		=>$newStudenInfo['stu_id'],
						"branchId" 		=>$newStudenInfo['branch_id'],
					];
					$_dbStCode->reverseStudCodeOfStudent($arrUp);

				}else{

					if(($returnInfo['returnType'] == 1) OR ($returnInfo['returnType'] == 3) ){

						$_arrTest = array(
							'is_studenttest'  =>0,
						);
						$this->_name="rms_student";
						$wheretest=" stu_id = ".$returnInfo['stuId'];
						$this->update($_arrTest,$wheretest);
					}

					$stuArr=array(
						
							'is_current'	=> 1,
							'note'	=>'RollBack From Grade Upgrade',
					);
					$whereStu=" itemType=1 ";
					$whereStu.=" AND is_current=0 ";
					$whereStu.=" AND stu_id=".$returnInfo['stuId'];
					$whereStu.=" AND group_id=".$returnInfo["group"];
					
					$this->_name='rms_group_detail_student';
					$this->update($stuArr, $whereStu);

					// delete new  group
						
					$this->_name = 'rms_group_detail_student';
					$whereDelete=" itemType=1 ";
					$whereDelete.=" AND is_current=1 ";
					$whereDelete.=" AND  stu_id=".$returnInfo['stuId'];
					$whereDelete.=" AND old_group=".$returnInfo["group"];
					$this->delete($whereDelete);

				}
				
				// is return 0
				$DropArr=array(
						'isReturn' => 0,
				);
				$whereDrop=" id= ".$returnInfo['dropId'];
				$this->_name='rms_student_drop';
				$this->update($DropArr, $whereDrop);

				// status 0
				$returnArr=array(
						'status' => 0,
				);
				$whereReturn=" id= ".$id;
				$this->_name='rms_student_return';
				$this->update($returnArr, $whereReturn);
				
			}
			
			$_db->commit();
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
			return false;
		}
		
	}
	
	function updateStudentDropReturn($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
		
			$id=$_data['id'];
			$_arr= array(
					'returnDate'		 => $_data['return_date'],
					'note'	 => $_data['note'],						
					'userId'	 => $this->getUserId(),
					'modifyDate'=> date('Y-m-d H:i:s'),
					'status' 	=>$_data['status']
			);
			$this->_name="rms_student_return";
			$whereArr="id =".$id;
			$this->update($_arr,$whereArr);
			
			if($_data['status']==0){
				
				// $record = $this->getStudentDropReturnById($id);
				// $dropId = $record['dropId'];

				$dropId = $_data['drop_id'];
				$_arrStuDropRecord=array(
						'isReturn'	=>0,
				);
				$whereStuDropRecord=$this->getAdapter()->quoteInto("id=?", $dropId);
				$this->_name="rms_student_drop";
				$this->update($_arrStuDropRecord,$whereStuDropRecord);
				
				$oldGroupId = $_data['groupId'];
			
				$stu_id = $_data['stu_id'];
				$newGroupId = $_data['group'];
				
				
				$dbStuDrop= new Foundation_Model_DbTable_DbStudentDrop();
				$rowDrop = $dbStuDrop->getStudentDropById($dropId);
				$stopType = $rowDrop['type'];
				if($oldGroupId!=$newGroupId){
					
					// $this->_name = 'rms_group_detail_student';
					// $whereDeleteNewStudy="stu_id = ".$stu_id." AND is_current=1 ";
					// if(!empty($newGroupId)){
					// 	$whereDeleteNewStudy.=" AND group_id=$newGroupId ";
					// }
					// $this->delete($whereDeleteNewStudy);
					
					$_arrOldGroupDetail = array(
							'group_id'			=>$oldGroupId,
							'stop_type'			=>$stopType,
							'is_current'		=>1,
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name="rms_group_detail_student";
					$whereOldGroupDetail="stu_id = ".$stu_id." AND itemType=1 AND is_current=1 ";
					if(!empty($oldGroupId)){
						$whereOldGroupDetail.=" AND group_id=$newGroupId ";
					}
					$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
				}else{
					$_arrOldGroupDetail = array(
							'stop_type'		=>$stopType,
							'note'			=>"Update StopType Group Detail From Student Return",
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name="rms_group_detail_student";
					$whereOldGroupDetail="stu_id = ".$stu_id." AND itemType=1 AND is_current=1 ";
					if(!empty($oldGroupId)){
						$whereOldGroupDetail.=" AND group_id=$oldGroupId ";
					}
					$this->update($_arrOldGroupDetail,$whereOldGroupDetail);
				}
			}
		
			$_db->commit();
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getAllStudentDrop($_data){
		$db = $this->getAdapter();
		
		$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
		$sql="
		SELECT 
			dr.id,
			(SELECT CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=dr.stu_id LIMIT 1) AS `name` 
		FROM `rms_student_drop` AS dr 
		WHERE dr.status=1
			AND dr.isReturn=0
			AND dr.branch_id=$branch_id
		";
		if(!empty($_data['dropIdSelected'])){
			$sql.=" OR dr.id=".$_data['dropIdSelected'];
		}
		return $db->fetchAll($sql);
	}
	
	function getStudentDropInfo($_data){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$label="name_en";
		if ($currentLang==1){
			$colunmname='title';
			$label="name_kh";
		}
		
		$drop_id = empty($_data['drop_id'])?0:$_data['drop_id'];
		$sql="
		SELECT 
			dr.*,
			(SELECT its.$colunmname FROM `rms_items` AS its WHERE its.id=dr.degree AND its.type=1 LIMIT 1) AS degreeTitle,
			(SELECT CONCAT(itsd.$colunmname) FROM `rms_itemsdetail` AS itsd WHERE itsd.id=dr.grade AND itsd.items_type=1 LIMIT 1) AS gradeTitle,
			(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = dr.group LIMIT 1) AS groupCode,
			(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = dr.academic_year LIMIT 1) AS academicYearTitle,
			(SELECT v.$label FROM `rms_view` AS v WHERE v.type=5 AND key_code = dr.type LIMIT 1) AS typeTitle,
			(SELECT CONCAT(COALESCE(s.stu_code,''),'-',COALESCE(s.stu_khname,''),'-',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) FROM `rms_student` AS s WHERE s.stu_id=dr.stu_id LIMIT 1) AS `name` 
		FROM `rms_student_drop` AS dr 
		WHERE dr.status=1
			AND dr.id=$drop_id 
			LIMIT 1
		";
		return $db->fetchRow($sql);
	}
	function getStudentById($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `rms_student` WHERE stu_id = $id ";
		return $db->fetchRow($sql);
	}
}