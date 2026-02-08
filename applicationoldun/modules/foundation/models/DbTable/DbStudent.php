<?php

class Foundation_Model_DbTable_DbStudent extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	function uploadFile($data){
		$part= PUBLIC_PATH.'/images/photo/';
		if (!file_exists($part)) {
			mkdir($part, 0777, true);
		}
	
		$photo = "";
		$name = $_FILES['webcam']['name'];
		if (!empty($name)){
			$ss = 	explode(".", $name);
			$image_name = "profile_".date("Y").date("m").date("d").time().".".end($ss);
			$tmp = $_FILES['webcam']['tmp_name'];
			if(move_uploaded_file($tmp, $part.$image_name)){
				$photo = $image_name;
				return $photo;
			}
			else
				$string = "Image Upload failed";
		}
		return null;
	}
	
	public function getAllStudent($search){
		$_db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$branch = $dbp->getBranchDisplay();
		
		$from_date =(empty($search['start_date']))? '1': "s.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "s.create_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql = "SELECT  
				s.stu_id AS id
				,b.$branch branch_name
				,s.stu_code titleRecord
				,v.shortcut sex
				,CASE
					WHEN s.primary_phone = 1 THEN s.tel
					WHEN s.primary_phone = 2 THEN COALESCE(fam.fatherPhone,'')
					WHEN s.primary_phone = 3 THEN COALESCE(fam.motherPhone,'')
					ELSE COALESCE(fam.guardianPhone,'')
				END as tel
				,CONCAT(acy.fromYear, '-', acy.toYear) AS academic
				,g.group_code AS group_name
				,COALESCE(fam.familyCode,'') AS familyCode
				,u.first_name AS user_name ";
		$sql.=" 
				, s.status AS statusRecord 
				,CONCAT(COALESCE(s.stu_khname,''),' / ',COALESCE(s.last_name,''),' ',COALESCE(s.stu_enname,'')) AS subTitleRecord
				, s.familyId AS familyId 
				,ds.group_id AS groupId
		";
		$sql.=" FROM rms_student AS s
					LEFT JOIN rms_group_detail_student AS ds ON ds.itemType=1 AND ds.stu_id=s.stu_id AND ds.is_maingrade=1 AND ds.is_current=1 
					LEFT JOIN rms_family AS fam ON fam.id = s.familyId
					LEFT JOIN rms_branch b ON b.br_id=s.branch_id
					LEFT JOIN `rms_view` v ON v.type=2 AND v.key_code = s.sex
					LEFT JOIN rms_academicyear AS acy ON acy.id = ds.academic_year
					LEFT JOIN rms_group AS g ON g.id = ds.group_id
					LEFT JOIN rms_users AS u ON u.id = s.user_id
				WHERE  s.customer_type=1 ";
		
		

		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(s.stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(CONCAT(s.last_name,s.stu_enname),' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(CONCAT(s.stu_enname,s.last_name),' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(s.stu_enname,' ',s.last_name)  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(s.last_name,' ',s.stu_enname)  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.tel,' ','') LIKE '%{$s_search}%'";
			
			$s_where[]=" REPLACE(COALESCE(fam.familyCode,''),' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.remark,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.home_num,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.street_num,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.village_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.commune_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.district_name,' ','') LIKE '%{$s_search}%'";
			
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}

		if(!empty($search['groupId'])){
			$where.=" AND COALESCE(ds.group_id,0) = ".$search['groupId'];
		}
		if(!empty($search['degree'])){
			$where.=" AND COALESCE(ds.degree,0) = ".$search['degree'];
		}
		if(!empty($search['gradeId'])){
			$where.=" AND COALESCE(ds.grade,0) = ".$search['gradeId'];
		}
		if(!empty($search['session'])){
			$where.=" AND COALESCE(ds.session,0) = ".$search['session'];
		}
		if($search['status']>-1){
			$where.=" AND s.status=".$search['status'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND s.branch_id=".$search['branch_id'];
		}
		if(!empty($search['study_year'])){
			$where.=" AND COALESCE(ds.academic_year,0) = ".$search['study_year'];
		}
		$where.=$dbp->getAccessPermission('s.branch_id');
		$where.=$dbp->getDegreePermission('COALESCE(ds.degree,0)');

		$orderby = " ORDER BY LEFT(s.stu_code, LENGTH(s.stu_code) - 5),CAST(RIGHT(s.stu_code, 5) AS UNSIGNED) DESC ";
		return $_db->fetchAll($sql.$where.$orderby);
	}
	public function getStudentById($id){
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		
		$village_name = "village_name";
		$commune_name = "commune_name";
		$district_name = "district_name";
		$province = "province_en_name";
		$occuTitle='occu_enname';
		$viewTitle = 'name_en';
		$fatherName = 'fatherName';
		$motherName = 'motherName';
		$label = "title_eng";
		if($lang==1){// khmer
			$village_name = "village_namekh";
			$commune_name = "commune_namekh";
			$district_name = "district_namekh";
			$province = "province_kh_name";
			$occuTitle = 'occu_name';
			$viewTitle = 'name_kh';
			$motherName = 'motherNameKh';
			$label = "title_kh";
		}

		$titleFee = "CONCAT(ac.fromYear,'-',ac.toYear,st.$label,'(',f.generation,')')";
		$sql = "SELECT s.*,
					(SELECT $viewTitle FROM rms_view where type=21 and key_code=s.nationality LIMIT 1) AS nationality_title,
	    			(SELECT $viewTitle FROM rms_view where type=21 and key_code=s.nation LIMIT 1) AS nation_title,

					fam.familyCode,
					fam.$fatherName AS fatherName,
					fam.fatherPhone AS fatherPhone,
					fam.$motherName AS motherName,
					fam.motherPhone AS motherPhone,

	    			(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.fatherJob LIMIT 1) fath_job,
					(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.motherJob LIMIT 1) moth_job,
					(SELECT $occuTitle FROM rms_occupation WHERE occupation_id=fam.guardianJob LIMIT 1) guard_job,
					(SELECT v.$village_name FROM `ln_village` AS v WHERE v.vill_id = s.village_name LIMIT 1) AS village_title,
			    	(SELECT c.$commune_name FROM `ln_commune` AS c WHERE c.com_id = s.commune_name LIMIT 1) AS commune_title,
			    	(SELECT d.$district_name FROM `ln_district` AS d WHERE d.dis_id = s.district_name LIMIT 1) AS district_title,
					(SELECT $province FROM rms_province WHERE province_id=s.province_id LIMIT 1) AS province_title
					,gd.programType
					,gd.degree
					,gd.grade
					,gd.group_id
					,gd.selectiveSubject
					,gd.englishLevel
					,gd.feeId
					,dc.`discountGroupId`
					,dst.`discountTitle`
					,dst.`discountCode`
					,dst.`discountValue`
					,d.`dis_name`
					,$titleFee AS studentFee
				FROM rms_student as s JOIN rms_group_detail_student AS gd ON gd.stu_id = s.stu_id AND gd.is_current =1 AND gd.is_maingrade =1
					LEFT JOIN rms_family AS fam ON fam.id = s.familyId
					LEFT JOIN `rms_discount_student` AS dc ON  dc.studentId = s.stu_id  AND dc.isCurrent = 1
					LEFT JOIN `rms_dis_setting` AS dst ON dst.id = dc.`discountGroupId`
					LEFT JOIN `rms_discount`  AS d ON  d.`disco_id` = dst.`DisValueType`
					LEFT JOIN `rms_tuitionfee` AS f ON f.`id` = gd.`feeId`
					LEFT JOIN rms_academicyear ac ON ac.id=f.academic_year
					LEFT JOIN rms_studytype st ON  st.id =f.term_study
				WHERE s.stu_id =".$id." 
				AND s.customer_type=1";
			$dbp = new Application_Model_DbTable_DbGlobal();
			$sql.=$dbp->getAccessPermission("s.branch_id");
			//echo $sql;exit();
			return $db->fetchRow($sql);
	}
	
	public function getStudentDocumentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM rms_student_document as s WHERE s.stu_id =".$id;
		return $db->fetchAll($sql);
	}
	
	function getStudentExist($_data,$idStu=null){
		$db = $this->getAdapter();
		$name_en = $_data['name_kh'];
		$sex  = $_data['sex'];
		$dob  = $_data['date_of_birth'];
		$stu_code  = $_data['student_id'];
		$sql = "SELECT * FROM rms_student WHERE customer_type=1 AND stu_code="."'$stu_code'"." AND stu_khname="."'$name_en'"." AND sex=".$sex." AND dob="."'$dob'";
		if (!empty($idStu)){
			$sql.=" AND stu_id !=$idStu";
		}                  
		return $db->fetchRow($sql);
	}
	function ifStudentIdExisting($stu_code,$id=null){
		$db = $this->getAdapter();
		$sql=" SELECT stu_id FROM rms_student WHERE stu_code='".$stu_code."'";
		if (!empty($id)){
			$sql.=" AND stu_id !=$id";
		}
		return $db->fetchOne($sql);
	}
	public function addStudent($_data)
	{
			$_db = $this->getAdapter();
			$_db->beginTransaction();
			
			
			$mainRecord = empty($_data['is_main'])?0:$_data['is_main'];
			
			$id = $this->getStudentExist($_data);	
			if(!empty($id)){
				Application_Form_FrmMessage::Sucessfull("STUDENT_EXISTRING","/foundation/register/add");
				return -1;
			}

			$dbg = new Application_Model_DbTable_DbGlobal();
			$degree_id = empty($_data['degree_'.$mainRecord])?0:$_data['degree_'.$mainRecord];

			$canEntry = Setting_Model_DbTable_DbGeneral::geValueByKeyName('entry_stuid');
			if($canEntry==1){//entry by self not need generate for user
				$stu_code=$_data['student_id'];
			}else{
				$stu_code = $dbg->getnewStudentId($_data['branch_id'],$degree_id);
			}

			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}	
			$photo = "";
			if(!empty($_data['old_photo'])){
				$photo = $_data['old_photo'];
			}
			$name = $_FILES['photo']['name'];
			if (!empty($_data['uploaded'])){
				$photo=$_data['uploaded'];
			}else if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = $stu_code."-student-".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['photo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$photo = $image_name;
				}
				else
					$string = "Image Upload failed";
			}
			
			try{	
				$_data['degreeStudent'] = $degree_id;//For Insert To Tale Count ID
				$dbg->updateAmountStudetByDegree($_data);//For Insert To Tale Count ID
				$stuToken = $dbg->getStudentToken();
				$_arr= array(
						'branch_id'		=>$_data['branch_id']
						,'stu_code'		=>$stu_code
						,'user_id'		=>$this->getUserId()
						,'stu_khname'	=>$_data['name_kh']
						,'last_name'		=>ucfirst($_data['last_name'])
						,'stu_enname'	=>ucfirst($_data['name_en'])
						,'sex'			=>$_data['sex']
						,'nationality'	=>$_data['studen_national']
						,'nation'		=>$_data['nation']
						,'dob'			=>$_data['date_of_birth']
						,'tel'			=>$_data['phone']
						,'primary_phone'	=>$_data['primary_phone']
						,'pob'			=>$_data['pob']
						,'home_num'		=>$_data['home_note']
						,'street_num'	=>$_data['way_note']
						,'village_name'	=>$_data['village_note']
						,'commune_name'	=>$_data['commun_note']
						,'district_name'	=>$_data['distric_note']
						,'province_id'	=>$_data['student_province']
						
						
						,'lang_level'	=>$_data['lang_level']
						,'from_school'	=>$_data['from_school']
						,'know_by'		=>$_data['know_by']
						,'sponser'		=>$_data['sponser']
						,'sponser_phone'	=>$_data['sponser_phone'] 
						
						,'status'		=>1
						,'remark'		=>$_data['remark']
						,'create_date'	=>date("Y-m-d H:i:s")
						,'enrollDate'	=>date("Y-m-d")
						,'photo'  			 => $photo
						,'customer_type'			=>1//Student
						
						,'date_bacc'	=>$_data['date_baccexam']
						,'province_bacc'	=>$_data['school_province']
						,'center_bacc'	=>$_data['center_baccexam']
						,'room_bacc'	=>$_data['room_baccexam']
						,'table_bacc'	=>$_data['table_baccexam']
						,'grade_bacc'	=>$_data['grade_baccexam']
						,'score_bacc'	=>$_data['score_baccexam']
						,'certificate_bacc'	=>$_data['certificate_baccexam']
						,'studentToken'=>$stuToken

						,'studentType'	=>$_data['studentType']
						,'familyId'		=>empty($_data['familyId']) ? 0 : $_data['familyId']
						,'goHomeType'	=>$_data['goHomeType']
						,'academicYearEnroll'	=>$_data['academicYearEnroll']
						

						);
				if (EDUCATION_LEVEL==1){
					$_arr['calture'] = $_data['calture'];
				}
				
				$partAudio= PUBLIC_PATH.'/images/frontFile/audio/';
				if (!file_exists($partAudio)) {
					mkdir($partAudio, 0777, true);
				}
				$audiofileName = $_FILES['audiofile']['name'];
				if (!empty($audiofileName)){
					$tem =explode(".", $audiofileName);
					$newFileName = $stu_code."-audio-".date("Y").date("m").date("d").time().".".end($tem);
					$tmp = $_FILES['audiofile']['tmp_name'];
					if(move_uploaded_file($tmp, $partAudio.$newFileName)){
						$_arr['audioTitle']=$newFileName;
					}
				}
				
				$id = $this->insert($_arr);
				
				$this->_name = 'rms_student_document';
				if(!empty($_data['identity'])){
					$part= PUBLIC_PATH.'/images/document/student/';
					if (!file_exists($part)) {
						mkdir($part, 0777, true);
					}
					$ids = explode(',', $_data['identity']);
					foreach ($ids as $i){
						$_arr = array(
								'stu_id'		=>$id,
								'document_type'	=>$_data['document_type_'.$i],
								'date_give'		=>$_data['date_give_'.$i],
								'date_end'		=>$_data['date_end_'.$i],
								'is_receive'	=>$_data['is_receive_'.$i],
								'note'			=>$_data['note_'.$i],
						);
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$image_name = $stu_code."-att-".date("Y").date("m").date("d").time().$i.".".end($ss);
							$tmp = $_FILES['attachment'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$photo = $image_name;
								$_arr['attachment_file'] = $photo;
							}
						}
						$this->insert($_arr);
					}
				}
				
				
				
				$feeID=0;
				if(!empty($_data['academic_year'])){
					$_dbfee = new Accounting_Model_DbTable_DbFee();
					$feeID = empty($_data['academic_year'])?0:$_data['academic_year'];
					$rowfee = $_dbfee->getFeeById($feeID);
					$academicYear = empty($rowfee['academic_year'])?0:$rowfee['academic_year'];
				}else{
					$academicYear = empty($_data['academicYearEnroll'])?0:$_data['academicYearEnroll'];
					if(empty($_data['academicYearEnroll'])){
						$dbGb = new Application_Model_DbTable_DbGlobal();
						$last = $dbGb->getLatestAcadmicYear();
						if(!empty($last)){
							$academicYear = empty($last["id"]) ? 0 : $last["id"];
						}
					}
					
				}
				
				
				$dbGroup = new Foundation_Model_DbTable_DbGroup();
				$programType = empty($_data['programType']) ? 1 : $_data['programType'];
				if(!empty($_data['degree'])){
					$isSetGroup = empty($_data['group'])? 0 : 1;
					$groupId = empty($_data['group'])? 0 : $_data['group'];
					$groupInfo = $dbGroup->getGroupById($groupId);
					
					$_arr = array(
							'stu_id'			=>$id,
							'branch_id'			=>$_data['branch_id'],
							'feeId'				=>$feeID,
							'is_newstudent'		=>$_data['stu_denttype'],
							'itemType'			=>1,
							'status'			=>1,
							
							'degree'			=>$_data['degree'],
							'grade'				=>$_data['grade'],
							'group_id'			=>$groupId,

							'selectiveSubject'	=>$_data['selectiveSubject'],
							'englishLevel'		=>$_data['englishLevel'],
							
							'is_setgroup'		=>$isSetGroup,
							'is_current'		=>1,
							'is_maingrade'		=>1,
							'entryFrom'			=>3,
							'create_date'		=>date("Y-m-d H:i:s"),
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
							'programType'		=>$programType,
						);
						if (!empty($groupInfo)){
							$_arr['session'] = $groupInfo['session'];
							$academic_year = $groupInfo['academic_year'];
						}else{
							$academic_year = $academicYear;
						}
						$_arr['academic_year'] = $academic_year;
						$this->_name="rms_group_detail_student";
						$this->insert($_arr);
						if($groupId>0){
							$this->_name = 'rms_group';
							$dataGro = array(
									'is_use'=> 1,//ប្រើប្រាស់
									'is_pass'=> 2,//កំពុងសិក្សា
							);
							$whereGroup = ' is_use = 0 AND id = '.$groupId;
							$this->update($dataGro, $whereGroup);
						}
				}
				
				if(!empty($_data['identity_study'])){
					$ids = explode(',', $_data['identity_study']);
					foreach ($ids as $i){
						$group_id = empty($_data['group_'.$i])?0:$_data['group_'.$i];
						$is_setgroup = empty($_data['group_'.$i])?0:1;
						$group_info = $dbGroup->getGroupById($group_id);
						
						$_arr = array(
								'stu_id'			=>$id,
								'branch_id'			=>$_data['branch_id'],
								'feeId'				=>$feeID,
								'is_newstudent'		=>$_data['stu_denttype'],
								'itemType'			=>1,
								'status'			=>1,
								'group_id'			=>$group_id,
								'degree'			=>$_data['degree_'.$i],
								'grade'				=>$_data['grade_'.$i],
								'is_current'		=>1,
								'is_setgroup'		=>$is_setgroup,
								'is_maingrade'		=>0,
								'entryFrom'			=>3,
								'create_date'		=>date("Y-m-d H:i:s"),
								'modify_date'		=>date("Y-m-d H:i:s"),
								'user_id'			=>$this->getUserId(),
								'programType'		=>$programType,
						);
						if (!empty($group_info)){
							$_arr['session'] = $group_info['session'];
							$academic_year = $group_info['academic_year'];
						}else{
							$academic_year = $academicYear;
						}
						$_arr['academic_year'] = $academic_year;
						$this->_name="rms_group_detail_student";
						$this->insert($_arr);
						
						if($group_id>0){
							$this->_name = 'rms_group';
							$data_gro = array(
									'is_use'=> 1,//ប្រើប្រាស់
									'is_pass'=> 2,//កំពុងសិក្សា
							);
							$whereGroup = ' is_use = 0 AND id = '.$group_id;
							$this->update($data_gro, $whereGroup);
						}
					}
				}
				
				//record data student code
				if( $canEntry != 1 ){
					$_dbStCode = new Application_Model_DbTable_DbStudentCode();
					$arrUp =[
						"referenceType" =>2,
						"referenceId" 	=>$id,
						"stuCode" 		=>$stu_code,
						"stuId" 		=>$id,
						"branchId" 		=>$_data['branch_id'],
						"acadedmicYear"	=>$academicYear,
						"degree" 		=>$_data['degree'],
					];
					$_dbStCode->insertStudentCode($arrUp);
				}
				
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
			Application_Form_FrmMessage::message("INSERT_FAILE");
		}
	}
	public function updateStudent($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{	
			
			$studentCode = empty($_data['student_id']) ? "" : $_data['student_id'];
			$_arr=array(
 					'branch_id'		=>$_data['branch_id']
 					,'stu_code'		=>$_data['student_id']
					,'user_id'		=>$this->getUserId()
					,'stu_khname'	=>$_data['name_kh']
					,'last_name'		=>ucfirst($_data['last_name'])
					,'stu_enname'	=>ucfirst($_data['name_en'])
					,'sex'			=>$_data['sex']
					
					,'nationality'	=>$_data['studen_national']
					,'nation'		=>$_data['nation']
					,'dob'			=>$_data['date_of_birth']
					,'tel'			=>$_data['phone']
					,'primary_phone'	=>$_data['primary_phone']
					
					,'pob'			=>$_data['pob']
					,'home_num'		=>$_data['home_note']
					,'street_num'	=>$_data['way_note']
					,'village_name'	=>$_data['village_note']
					,'commune_name'	=>$_data['commun_note']
					,'district_name'	=>$_data['distric_note']
					,'province_id'	=>$_data['student_province']
					
					/////other infomation tab /////
					,'lang_level'	=>$_data['lang_level']
					,'from_school'	=>$_data['from_school']
					,'know_by'		=>$_data['know_by']
					,'sponser'		=>$_data['sponser']
					,'sponser_phone'	=>$_data['sponser_phone']
					
					,'status'		=>$_data['status']
					,'remark'		=>$_data['remark']
					,'date_bacc'	=>$_data['date_baccexam']
					,'province_bacc'	=>$_data['school_province']
					,'center_bacc'	=>$_data['center_baccexam']
					,'room_bacc'	=>$_data['room_baccexam']
					,'table_bacc'	=>$_data['table_baccexam']
					,'grade_bacc'	=>$_data['grade_baccexam']
					,'score_bacc'	=>$_data['score_baccexam']
					,'certificate_bacc'	=>$_data['certificate_baccexam']
					
					,'studentType'	=>$_data['studentType']
					,'familyId'	=>empty($_data['familySelectId']) ? 0 : $_data['familySelectId']
					,'goHomeType'	=>$_data['goHomeType']
					,'academicYearEnroll'	=>$_data['academicYearEnroll']
					,'modify_date'	=>date("Y-m-d H:i:s")
				);
			if (EDUCATION_LEVEL==1){
				$_arr['calture'] = $_data['calture'];
			}
			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo = "";
			$name = $_FILES['photo']['name'];
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = $studentCode."-student-".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['photo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$_arr['photo']=$image_name;
					
					$oldPhoto = empty($_data['old_photo']) ? "" : $_data['old_photo'];
					if( $oldPhoto!=""){
						$thisFile = $part.$oldPhoto;
						if (file_exists($thisFile)){
							if (is_file($thisFile)){
								unlink($thisFile);
							}
						}
					}
				}
			}
			
			$partAudio= PUBLIC_PATH.'/images/frontFile/audio/';
			if (!file_exists($partAudio)) {
				mkdir($partAudio, 0777, true);
			}
			$audiofileName = $_FILES['audiofile']['name'];
			if (!empty($audiofileName)){
				$tem =explode(".", $audiofileName);
				$newFileName = $studentCode."-audio-".date("Y").date("m").date("d").time().".".end($tem);
				$tmp = $_FILES['audiofile']['tmp_name'];
				if(move_uploaded_file($tmp, $partAudio.$newFileName)){
					$_arr['audioTitle']=$newFileName;
				}
			}
			
			$stu_id = $_data["id"];
			$where=$this->getAdapter()->quoteInto("stu_id=?", $stu_id);
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$this->update($_arr, $where);
			
			//Student Document Block
			$detailidlist = '';
			if(!empty($_data['identity'])){
				$ids = explode(',', $_data['identity']);
	    		foreach ($ids as $i){
	    			if (empty($detailidlist)){
	    				if (!empty($_data['detailid'.$i])){
	    					$detailidlist= $_data['detailid'.$i];
	    				}
	    			}else{
	    				if (!empty($_data['detailid'.$i])){
	    					$detailidlist = $detailidlist.",".$_data['detailid'.$i];
	    				}
	    			}
	    		}
			}
			
			$this->_name = 'rms_student_document';
			$where="stu_id = ".$_data["id"];
			if (!empty($detailidlist)){ // check if has old payment detail  detail id
				$where.=" AND id NOT IN (".$detailidlist.")";
			}
			$this->delete($where);
			
			if(!empty($_data['identity'])){
				$part= PUBLIC_PATH.'/images/document/student/';
				if (!file_exists($part)) {
					mkdir($part, 0777, true);
				}
				
				$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
					if (!empty($_data['detailid'.$i])){
						$_arr = array(
								'stu_id'		=>$_data["id"],
								'document_type'	=>$_data['document_type_'.$i],
								'date_give'		=>$_data['date_give_'.$i],
								'date_end'		=>$_data['date_end_'.$i],
								'is_receive'	=>$_data['is_receive_'.$i],
								'note'			=>$_data['note_'.$i]
						);
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$image_name = $studentCode."-att-".date("Y").date("m").date("d").time().$i.".".end($ss);
							$tmp = $_FILES['attachment'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$photo = $image_name;
								$_arr['attachment_file'] = $photo;
							}
						}
						$where=" id=".$_data['detailid'.$i];
						$this->update($_arr, $where);
						
					}else{
						$_arr = array(
								'stu_id'		=>$_data["id"],
								'document_type'	=>$_data['document_type_'.$i],
								'date_give'		=>$_data['date_give_'.$i],
								'date_end'		=>$_data['date_end_'.$i],
								'is_receive'	=>$_data['is_receive_'.$i],
								'note'			=>$_data['note_'.$i]
						);
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$image_name = $studentCode."-att-".date("Y").date("m").date("d").time().$i.".".end($ss);
							$tmp = $_FILES['attachment'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$photo = $image_name;
								$_arr['attachment_file'] = $photo;
							}
						}
						$this->insert($_arr);
					}
				}
			}
			if( $studentCode != "" ){
				$oldStudentCode = empty($_data['oldStudentCode']) ? "" : $_data['oldStudentCode'];
				if($oldStudentCode != $studentCode){
					$_dbStCode = new Application_Model_DbTable_DbStudentCode();
					$arrUp =[
						"referenceType" =>2,
						"stuId" 		=>$stu_id,
						"branchId" 		=>$_data['branch_id'],
						"stuCode" 		=>$studentCode,
					];
					$_dbStCode->updateStuCodeData($arrUp);
				}
			}


			$_arr_detail = array();
			if (!empty($_data['selectiveSubject'])) {
				$_arr_detail['selectiveSubject'] = $_data['selectiveSubject'];
			}

			if (!empty($_data['englishLevel'])) {
				$_arr_detail['englishLevel'] = $_data['englishLevel'];
			}

			if (!empty($_arr_detail)) {
				$this->_name = 'rms_group_detail_student';

				$where = array();
				$where[] = 'stu_id = ' . (int)$_data['id'];
				$where[] = 'itemType = 1';
				$where[] = 'is_current = 1';

				$this->update($_arr_detail, $where);
			}


			$db->commit();//if not errore it do....
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}

	function getStudentInfoById($stu_id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `rms_student` WHERE stu_id=$stu_id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
	function getProvince(){
		$_db = new Application_Model_DbTable_DbGlobal();
		return $_db->getAllProvince();
	}
	
	function getAllgroup(){
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		return $_dbgb->getAllGroupByBranch();
	}
	function getGroupInforByID($group_id){
		$db = $this->getAdapter();
		$sql ="SELECT * FROM `rms_group` AS g WHERE g.`id`=$group_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getStudentViewDetailById($id){
		$db=$this->getAdapter();
		
		$sql="
			SELECT 
				s.*
				,fam.fatherNameKh AS father_khname 
				,fam.fatherName AS father_enname  
				,fam.fatherNation AS father_nation
				,fam.fatherPhone AS father_phone
				
				,fam.motherNameKh AS mother_khname 
				,fam.motherName AS mother_enname  
				,fam.motherPhone AS mother_phone  
				
				,fam.guardianNameKh AS guardian_khname 
				,fam.guardianName AS guardian_enname 
				,fam.guardianPhone AS guardian_tel  
				
				,(SELECT province_kh_name FROM rms_province AS p WHERE p.province_id=s.province_id LIMIT 1) AS province_name
				,(SELECT occu_name FROM rms_occupation WHERE occupation_id=fam.fatherJob LIMIT 1) AS fa_job
				,(SELECT occu_name FROM rms_occupation WHERE occupation_id=fam.motherJob LIMIT 1) AS mo_job
				,(SELECT occu_name FROM rms_occupation WHERE occupation_id=fam.guardianJob LIMIT 1) AS gu_job
			
				,(SELECT rms_itemsdetail.title FROM rms_itemsdetail WHERE rms_itemsdetail.id=(SELECT gds.grade FROM rms_group_detail_student AS gds WHERE gds.itemType=1 AND gds.stu_id=s.stu_id AND gds.is_current=1 AND gds.is_maingrade=1 ORDER BY gds.gd_id DESC LIMIT 1) AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade_name
				,(SELECT rms_items.title FROM rms_items WHERE rms_items.id=(SELECT gds.degree FROM rms_group_detail_student AS gds WHERE gds.itemType=1 AND  gds.stu_id=s.stu_id AND gds.is_current=1 AND gds.is_maingrade=1 ORDER BY gds.gd_id DESC  LIMIT 1) AND rms_items.type=1 LIMIT 1) AS degree_name
				,(SELECT rms_items.title FROM rms_items WHERE rms_items.id=(SELECT gds.degree FROM rms_group_detail_student AS gds WHERE gds.itemType=1 AND  gds.stu_id=s.stu_id AND gds.is_current=1 AND gds.is_maingrade=1 ORDER BY gds.gd_id DESC LIMIT 1) AND rms_items.type=1 LIMIT 1) AS degreeTitle
				
				,(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=s.nationality) AS nationality
				,(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=fam.fatherNation) AS father_nation
				,(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=fam.motherNation) AS mother_nation
				,(SELECT name_kh FROM rms_view WHERE rms_view.type=21 AND rms_view.key_code=fam.guardianNation) AS guardian_nation
			
			FROM 
				rms_student AS s 
				LEFT JOIN rms_family AS fam ON fam.id = s.familyId
			WHERE s.stu_id=$id
		";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('s.branch_id');
		return $db->fetchRow($sql);
	}
	
	function getCurentStudentStudy($student_id){
		$db=$this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		$sql="SELECT sh.*,
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=sh.degree AND type=1 LIMIT 1) AS degreeTitle,
				(SELECT CONCAT(rms_itemsdetail.$colunmname) FROM `rms_itemsdetail` WHERE `id`=sh.grade AND items_type=1 LIMIT 1) AS gradeTitle,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = sh.group_id LIMIT 1) AS groupCode
			FROM rms_group_detail_student AS sh 
			WHERE 
				sh.itemType=1 
				AND sh.stu_id=$student_id 
				AND sh.is_current=1 AND sh.is_pass=0";
		$sql.=" AND sh.is_maingrade=0 ";
		$sql.=" ORDER BY sh.gd_id ASC ";
		return $db->fetchAll($sql);
	}
	function getMainGradeStudy($student_id){
		$db=$this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		$sql="SELECT sh.*,
				(SELECT rms_items.$colunmname FROM `rms_items` WHERE `id`=sh.degree AND type=1 LIMIT 1) AS degreeTitle,
				(SELECT CONCAT(rms_itemsdetail.$colunmname) FROM `rms_itemsdetail` WHERE `id`=sh.grade AND items_type=1 LIMIT 1) AS gradeTitle,
				(SELECT g.group_code FROM `rms_group` AS g WHERE g.id = sh.group_id LIMIT 1) AS groupCode
			FROM rms_group_detail_student AS sh 
			WHERE 
				sh.itemType=1 
				AND sh.stu_id=$student_id 
				AND sh.is_current=1 
				AND sh.is_pass=0
				AND sh.is_maingrade=1
				";
		return $db->fetchRow($sql);
	}
	function getStudentStudyInfo($studyId){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$session='title';
		$colunmname='title_en';
		$nameTitle='name_en';
		if ($currentLang==1){
			$session='titleKh';
			$colunmname='title';
			$nameTitle='name_kh';
		}
		
		$sql="
			SELECT
				s.*
				,v.$nameTitle AS goHomeType
				,gds.academic_year
				,gds.group_id
				,g.group_code AS groupName
				,gds.degree 
				,gds.grade
				,gds.session
				,g.session AS studyTime
				,g.room_id AS room
				,CONCAT(fromYear,'-',toYear) AS academicYearTitle
				,(SELECT `r`.`room_name` FROM `rms_room` `r`	WHERE (`r`.`room_id` = `g`.`room_id`) LIMIT 1) AS `roomName`
				,(SELECT p.$session FROM rms_parttime_list AS p WHERE  p.id = g.session LIMIT 1) AS `sessionTitle`
				,itd.$colunmname AS gradeTitle
				,i.$colunmname AS degreeTitle
			FROM
				rms_student AS s 
				JOIN rms_group_detail_student AS gds ON gds.stu_id = s.stu_id
				LEFT JOIN rms_view AS v ON v.key_code = s.goHomeType AND v.type=44
				LEFT JOIN rms_academicyear AS ac ON ac.id = gds.academic_year
				LEFT JOIN rms_group AS g ON g.id=gds.group_id
				LEFT JOIN rms_itemsdetail AS itd ON itd.id = g.grade
				LEFT JOIN rms_items AS i ON i.id = g.degree
			WHERE
				gds.itemType=1 
				AND s.status=1
				AND s.customer_type=1
				AND gds.gd_id = $studyId
			LIMIT 1
		";
		//AND gds.stop_type=0
		return $db->fetchRow($sql);
	}
	function getAllStudyByStudent($_data){
		$db = $this->getAdapter();
		$stu_id = empty($_data['stu_id'])?0:$_data['stu_id'];
		$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
		$sql="
		SELECT
			gds.*,
			gds.academic_year,
			gds.group_id,
			gds.degree,
			gds.grade,
			gds.session,
			(SELECT g.room_id FROM `rms_group` AS g WHERE g.id = gds.group_id LIMIT 1) AS room
		
			FROM
				rms_group_detail_student AS gds
			WHERE
				gds.itemType=1 
				AND gds.is_current =1
				AND gds.stu_id = $stu_id
				AND (SELECT g.branch_id FROM `rms_group` AS g WHERE g.id = gds.group_id LIMIT 1) = $branch_id
		";
		
		return $db->fetchAll($sql);
	}

	
	function getAllStudentBybranch($branch_id){
    	$db = $this->getAdapter();
		
    	$sql = "SELECT 
		stu_id AS id, 
			CONCAT(stu_khname,'-',last_name,' ',stu_enname,'-',stu_code) AS name
		 FROM `rms_student` WHERE status =1 AND branch_id = $branch_id ";
    	return $db->fetchAll($sql);
    }
	function getStdyInfoById($stu_id){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$nameTitle='name_en';
		if ($currentLang==1){
			$colunmname='title';
			$nameTitle='name_kh';
		}
		$sql = "SELECT *,	
					CONCAT(stu_khname,'-',last_name,' ',stu_enname) AS studentName,
					(SELECT $colunmname FROM rms_items WHERE rms_items.id=gds.degree AND rms_items.type=1 LIMIT 1) AS degree,
					(SELECT $colunmname FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
					(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS academic_year,
					v.$nameTitle AS goHomeTypeTitle
				FROM 
					rms_student AS s
					INNER JOIN rms_group_detail_student AS gds ON s.stu_id = gds.stu_id
					LEFT JOIN rms_view AS v ON v.key_code = s.goHomeType AND v.type = 44
				WHERE 
					gds.itemType=1 
					AND s.status=1 
					AND gds.is_current =1
					AND gds.is_maingrade =1
					AND s.customer_type=1
					AND  s.stu_id=$stu_id ";
		return $db->fetchRow($sql);
	}

	function getSearchStudent($search){
		$db=$this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$colunmname='title_en';
		$nameTitle='name_en';
		if ($currentLang==1){
			$colunmname='title';
			$nameTitle='name_kh';
		}
		$sql="SELECT 
				s.stu_id,
				s.stu_code,
				s.photo,
				CONCAT(stu_khname,'-',last_name,' ',stu_enname) AS studentName,
				(SELECT $colunmname FROM rms_items WHERE rms_items.id=gds.degree AND rms_items.type=1 LIMIT 1) AS degree,
				(SELECT $colunmname FROM rms_itemsdetail WHERE rms_itemsdetail.id=gds.grade AND rms_itemsdetail.items_type=1 LIMIT 1) AS grade,
				(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gds.academic_year LIMIT 1) AS academic_year,
				v.$nameTitle AS goHomeTypeTitle
				FROM 
					rms_student AS s
					INNER JOIN rms_group_detail_student AS gds ON s.stu_id = gds.stu_id
					LEFT JOIN rms_view AS v ON v.key_code = s.goHomeType AND v.type = 44
				WHERE 
				gds.itemType=1 
				AND s.stu_id = gds.stu_id
				AND s.status=1 
				AND s.customer_type = 1 
				AND gds.stop_type=0
				AND gds.is_pass=0
				AND s.stu_id=gds.stu_id
				AND gds.is_current=1 ";

		if(!empty($search['branch_id'])){
			$sql.=" AND s.branch_id =".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$sql.=" AND gds.academic_year =".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$sql.=" AND gds.degree =".$search['degree'];
		}
		if(!empty($search['grade'])){
			$sql.=" AND gds.grade =".$search['grade'];
		}
			if(!empty($search['group'])){
			$sql.=" AND gds.group_id =".$search['group'];
		}
		$where=" ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(s.stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_khname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(s.last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(s.last_name,s.stu_enname) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		$where.=" GROUP BY s.stu_id,gds.degree,gds.grade";
		$where.=" ORDER BY gds.degree,gds.grade,s.stu_id DESC ";
		// if(!empty($search['limit'])){
		// 	$where.=" LIMIT  ".$search['limit'];
		// }
		return $db->fetchAll($sql.$where);
	}
}