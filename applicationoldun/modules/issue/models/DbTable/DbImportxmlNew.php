<?php

class Issue_Model_DbTable_DbImportxmlNew extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_group_schedule';
	
	
	function uploadXMLFile($data){
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$last = $dbGb->getLatestAcadmicYear();
		$academicYear = 0;
		if(!empty($last)){
			$academicYear = empty($last["id"]) ? 0 : $last["id"];
		}
			
		$urlPart= PUBLIC_PATH.'/xml/';
		if (!file_exists($urlPart)) {
			mkdir($urlPart, 0777, true);
		}
		$fileName = $_FILES['xml_file']['name'];
		$tmp = $_FILES['xml_file']['tmp_name'];
			if(move_uploaded_file($tmp, $urlPart.$fileName)){
				$sessionXml=new Zend_Session_Namespace('xmlFile');
				$sessionXml->xml_ImportType=2;
				$sessionXml->xml_FileName=$fileName;
				$sessionXml->xml_Step=0;
				$sessionXml->xml_branch=1;
				$sessionXml->xml_setting=0;
				$sessionXml->xml_academicYear=$academicYear;
				$this->truncateStringCode();
				return 1;
			}
			else{
				return 0;
			}
		
	}
	function getXmlDataFromFile(){
	
		$sessionXml = new Zend_Session_Namespace('xmlFile');
		$fileName = $sessionXml->xml_FileName;//for creat sess
		if(!empty($fileName)){
			$urlPart= PUBLIC_PATH.'/xml/';
			$xml = simplexml_load_file($urlPart.$fileName,'SimpleXMLElement', LIBXML_NOCDATA) or die("Error: Cannot create object");
			$dbxml = new Issue_Model_DbTable_DbImportxml;
			return json_decode(json_encode((array)$xml), TRUE);
		} else {
			return false;
		}
    	

	}
	function truncateStringCode(){
		$db = $this->getAdapter();
		$sql = " TRUNCATE `rms_cards` ";
		$db->query($sql);
		$sql = " TRUNCATE `import_rms_group` ";
		$db->query($sql);
		$sql = " TRUNCATE `import_rms_group_reschedule` ";
		$db->query($sql);
		$sql = " TRUNCATE `import_rms_group_schedule` ";
		$db->query($sql);
		$sql = " TRUNCATE `import_rms_subject` ";
		$db->query($sql);
		$sql = " TRUNCATE `import_teacher` ";
		$db->query($sql);
		$sql = " TRUNCATE `import_rms_period` ";
		$db->query($sql);
		
	}
	
	function getNewSubjectImport($isHtml="1"){
		$db=$this->getAdapter();
		$sql="
			SELECT 
				s.id AS id
				,s.subject_titleen AS nameEn
				,s.subject_titlekh AS nameKh
				,s.shortcut
				,s.subject_lang as subjectLang
			FROM import_rms_subject AS s 
			WHERE s.subjectId = 0
			ORDER BY s.shortcut ASC
		";
		$rs = $db->fetchAll($sql);
		
		if($isHtml=="1"){
			
			$class="bg-label-success";
			if(!empty($rs)){
				$class="bg-label-warning";
			}
			
			$string="";
			$string.='
			<div class="card-body '.$class.'"> 
				<div class="row"> 
					<div class="col-md-12 col-sm-12 col-xs-12"> 
						<div class="d-flex"> 
							<div class="settings-main-icon ">
								<i class="fa fa-book"></i>
							</div> 
							<div class="col-md-10 col-sm-10 col-xs-12"> 
								<p class="tx-20 font-weight-semibold d-flex ">មុខវិជ្ជា / Subject</p>
							</div> 
						</div>
					</div>
				';
			if(!empty($rs)){
				$string.='
						<small class="control-label bold col-md-12 col-sm-12 col-xs-12">
							<strong>មុខវិជ្ជាថ្មីៗដែលមិនមានក្នុងប្រព័ន្ធ</strong>
					   </small>
				   ';
				foreach($rs as $key => $row){
					$string.='
						<small class="control-label bold col-md-12 col-sm-12 col-xs-12">
							'.($key+1).'. <strong>'.$row["nameKh"].' / '.$row["nameEn"].'</strong>
					   </small>
				   ';
				}
			}else{
				$string.='
						<small class="control-label bold col-md-12 col-sm-12 col-xs-12 text-center">
							<i class="fa fa-check-circle-o"></i> <strong>ព័ត៌មានមុខវិជ្ជាបានផ្ទៀងផ្ទាត់រួចរាល់</strong>
					   </small>
				   ';
			}
			$string.='
				</div>
			</div>';
			return $string;
		}else{
			return $rs;
		}
	}
	
	function getNewTeacherImport($isHtml="1"){
		$db=$this->getAdapter();
		$sql="
			SELECT 
				s.id AS id
				,s.teacher_name_en AS nameEn
				,s.teacher_name_kh AS nameKh
				,s.teacher_code AS shortcut
				,s.sex AS sex
				,s.tel 
				,s.branch_id AS branch_id
			FROM import_teacher AS s 
			WHERE s.teacherId = 0
			ORDER BY s.teacher_name_en ASC
		";
		$rs = $db->fetchAll($sql);
		
		if($isHtml=="1"){
			
			$class="bg-label-success";
			if(!empty($rs)){
				$class="bg-label-warning";
			}
			
			$string="";
			$string.='
			<div class="card-body '.$class.'"> 
				<div class="row"> 
					<div class="col-md-12 col-sm-12 col-xs-12"> 
						<div class="d-flex"> 
							<div class="settings-main-icon ">
								<i class="fa fa-user"></i>
							</div> 
							<div class="col-md-10 col-sm-10 col-xs-12"> 
								<p class="tx-20 font-weight-semibold d-flex ">គ្រូ / Teacher</p>
							</div> 
						</div>
					</div>
				';
			if(!empty($rs)){
				$string.='
						<small class="control-label bold col-md-12 col-sm-12 col-xs-12">
							<strong>គ្រូថ្មីៗដែលមិនមានក្នុងប្រព័ន្ធ</strong>
					   </small>
				   ';
				foreach($rs as $key => $row){
					$string.='
						<small class="control-label bold col-md-12 col-sm-12 col-xs-12">
							'.($key+1).'. <strong>'.$row["nameKh"].' / '.$row["nameEn"].'</strong>
					   </small>
				   ';
				}
			}else{
				$string.='
						<small class="control-label bold col-md-12 col-sm-12 col-xs-12 text-center">
							<i class="fa fa-check-circle-o"></i> <strong>ព័ត៌មានគ្រូបានផ្ទៀងផ្ទាត់រួចរាល់</strong>
					   </small>
				   ';
			}
			$string.='
				</div>
			</div>';
			return $string;
		}else{
			return $rs;
		}
	}
	
	function getNewGroupImport($isHtml="1"){
		$db=$this->getAdapter();
		$sql="
			SELECT 
				s.group_code AS nameEn
				,s.group_code AS nameKh
				,s.group_code AS shortcut
				,s.teacher_id AS teacherId
				,s.teacher_id AS teacherId
				,s.degree 
				,s.branch_id 
			FROM import_rms_group AS s 
			WHERE s.groupId = 0
			ORDER BY s.group_code ASC
		";
		$rs = $db->fetchAll($sql);
		
		if($isHtml=="1"){
			$string="";
			$class="bg-label-success";
			if(!empty($rs)){
				$class="bg-label-warning";
			}
			$string.='
			<div class="card-body '.$class.'"> 
				<div class="row"> 
					<div class="col-md-12 col-sm-12 col-xs-12"> 
						<div class="d-flex"> 
							<div class="settings-main-icon ">
								<i class="fa fa-desktop"></i>
							</div> 
							<div class="col-md-10 col-sm-10 col-xs-12"> 
								<p class="tx-20 font-weight-semibold d-flex ">ក្រុម / Class</p>
							</div> 
						</div>
					</div>
				';
			if(!empty($rs)){
				$string.='
						<small class="control-label bold col-md-12 col-sm-12 col-xs-12">
							<strong>ក្រុមថ្មីៗដែលមិនមានក្នុងប្រព័ន្ធ</strong>
					   </small>
				   ';
				foreach($rs as $key => $row){
					$string.='
						<small class="control-label bold col-md-12 col-sm-12 col-xs-12">
							'.($key+1).'. <strong>'.$row["nameKh"].'</strong>
					   </small>
				   ';
				}
			}else{
				$string.='
						<small class="control-label bold col-md-12 col-sm-12 col-xs-12 text-center">
							<i class="fa fa-check-circle-o"></i> <strong>ព័ត៌មានក្រុមបានផ្ទៀងផ្ទាត់រួចរាល់</strong>
					   </small>
				   ';
			}
			$string.='
				</div>
			</div>';
			return $string;
		}else{
			return $rs;
		}
	}
	
	function importxmlSubject($data)
	{
		$array = $this->getXmlDataFromFile();
	
		
		$step = $data['step'];
		$branchId = empty($data['branchId']) ? 1 : $data['branchId'];
		$academicYear = empty($data['academicYear']) ? 8 : $data['academicYear'];
		$setting = empty($data['setting']) ? 2 : $data['setting'];
		$sessionXml = new Zend_Session_Namespace('xmlFile');
		$sessionXml->xml_Step=0;
		$sessionXml->xml_branch=$branchId;
		$sessionXml->xml_academicYear=$academicYear;
		$sessionXml->xml_setting=$setting;
		
		if(!empty($array)){
			//$step = 7;
			if ($step == 1) {
				$tableData = $array["subjects"]["subject"];
				$subjectColumn = $array["subjects"]["@attributes"]["columns"];
				if (!empty($tableData)) {
					foreach ($tableData as $row) {//use
						$subjectTitle = $row["@attributes"]['name'];
						$strId = $row["@attributes"]['id'];
						$shortcut = $row["@attributes"]['short'];
						$this->getSubjectId($subjectTitle, $strId, $shortcut);
					}
					$sessionXml->xml_Step=$step;
					$step=2;
					$array = array(
						'index'=>$step,
						'content'=>$this->getNewSubjectImport(1),
						'msg'=>"",
					);
					
					return $array;
				}
			}elseif($step==2){
				/**
			 * check teacher
			 */
				$tableData = $array["teachers"]["teacher"];
				$periodsColumn = $array["teachers"]["@attributes"]["columns"];
				if(!empty($tableData)) foreach($tableData as $row){//use

					$teacherTitle =$row["@attributes"]['name'];
					$strId =$row["@attributes"]['id'];
					$gender =$row["@attributes"]['gender'];
					$shortcut = $row['@attributes']['short'];
					$teacher = $this->getTeacherId($teacherTitle,$gender,$strId,$shortcut);
				}
					$sessionXml->xml_Step=$step;
					$step=3;
					$array = array(
						'index'=>$step,
						'content'=>$this->getNewTeacherImport(1),
						'msg'=>"",
					);
					
				return $array;
			} elseif ($step == 3) {
				$tableData = $array["classes"]["class"];
				$messageReturn="";
				$returnCod="";
				if(!empty($tableData)) foreach($tableData as $row){//use
					$groupCode = str_replace('Grade ','',$row["@attributes"]['name']);
					$strId =$row["@attributes"]['id'];
					$arr = array(
						'strId'=>$strId,
						'title'=>$groupCode,
						'academicYear'=>$academicYear,
						'branchId'=>$branchId,
					);
					$return = $this->getGroupId($arr);
					
					$messageReturn = $return["msg"];
					$returnCod = $return["code"];
					if($return["code"]=="ERR"){
						break;
					}
				}
				if($returnCod !="ERR"){
					$sessionXml->xml_Step=$step;
					$step=4;
					$array = array(
						'index'=>$step,
						'content'=>$messageReturn,
						'msg'=>"",
					);
				}else{
					$step=2;
					$sessionXml->xml_Step=$step;
					$array = array(
						'index'=>$step,
						'content'=>$messageReturn,
						'msg'=>"",
					);
				}
				return $array;
			} elseif ($step == 4){
				// $db = $this->getAdapter();
				// $db->fetchRow($sql);
				$periodsData = $array["periods"]["period"];
				$count = count($periodsData);
				$messageReturn="";
				$returnCod="";
				if(!empty($periodsData)) foreach($periodsData as $key => $rowP){//use
					$starttime = $rowP["@attributes"]['starttime'];
					$endtime = $rowP["@attributes"]['endtime'];
					$period = $rowP["@attributes"]['period'];
					
					$data = array(
							'period'=>$period,
							'setting'=>$setting,
							'starttime'=>$starttime,
							'endtime'=>$endtime,
						);
					$return = $this->addPeriod($data);
					$messageReturn = $return["msg"];
					$returnCod = $return["code"];
					if($return["code"]=="ERR"){
						break;
					}
					if(($key+1) == $count){
						$tableData = $array["cards"]["card"];
						if(!empty($tableData)) foreach($tableData as $row){//use
								$lessionId = $row["@attributes"]['lessonid'];
								$period = $row["@attributes"]['period'];
								$days = $row["@attributes"]['days'];
								
								$data = array(
									'lessionId'=>$lessionId,
									'period'=>$period,
									'days'=>$days,
									'academicYear'=>$academicYear,
									'setting'=>$setting,
								);
								$this->addcardNew($data);
						}
					}
						
				}
				if($returnCod !="ERR"){
					$sessionXml->xml_Step=$step;
					$step=5;
					$array = array(
						'index'=>$step,
						'content'=>$messageReturn,
						'msg'=>"",
					);
				}else{
					$step=3;
					$sessionXml->xml_Step=$step;
					$array = array(
						'index'=>$step,
						'content'=>$messageReturn,
						'msg'=>"",
					);
				}
				return $array;
				
			} elseif ($step == 5) {

				//update
				$tableData = $array["lessons"]["lesson"];
				$periodsColumn = $array["classes"]["@attributes"]["columns"];
				$columnList = explode(',', $periodsColumn);
				
				$strPrview="";
				if(!empty($tableData)) foreach($tableData as $row){
					$subjectId= $this->getSubjectIdbyStrId($row["@attributes"]['subjectid']);
					$teacherId = $this->getTeacherIdbyStrId($row["@attributes"]['teacherids']);
					
					$arr = array(
						'strId'=>$row["@attributes"]['classids'],
						'academicYear'=>$academicYear,
					);
					$groupId = $this->getGroupIdbyStrId($arr);
					
					$lessionId = $row["@attributes"]['id'];
					$data = array(
						'subject_id'=>$subjectId,
						'techer_id' =>$teacherId,
						'note'=>'abc',
						'academicYear'=>$academicYear,
						'setting'=>$setting,
						'branchId'=>$branchId,
						);
					$scheduleId = $this->importGroupSchedule($lessionId,$data,$groupId);
				}
				$sessionXml->xml_Step=$step;
				$step=6;
				$array = array(
					'index'=>$step,
					'content'=>$strPrview,
					'msg'=>"",
				);
				return $array;
			} elseif ($step == 6) {
				$data = array(
					'branchId'=>$branchId,
					'academicYear'=>$academicYear,
					'setting'=>$setting,
				);
				$return = $this->submitFinalSchedule($data);
				$messageReturn = $return["msg"];
				$returnCod = $return["code"];
				if($return["code"]=="ERR"){
					$step=6;
					$array = array(
						'index'=>$step,
						'content'=>"",
						'msg'=>"invalid",
					);
				}else{
					$step=0;
					$sessionXml=new Zend_Session_Namespace('xmlFile');
					$sessionXml->unsetAll();
					
					$this->truncateStringCode();
					$array = array(
						'index'=>$step,
						'content'=>"",
						'msg'=>"completed",
					);
				}
				return $array;
				
			} else {
				return 0;
			}
		}else{
			return 0;
		}
	}
	function addSchedule($data)
	{
		$this->_name = "rms_group_schedule";
		$arr = array(
			'academic_year'=>8,
			'group_id'=>$data['groupId'],
			'create_date'=>date("Y-m-d")
		);
		return $this->insert($arr);
	}
	public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }

	public function getSubjectId($title=null,$strId,$shortcut=null){
		
		$db = $this->getAdapter();
		$sql=" SELECT 
		
			id 
			,subject_titlekh
			,subject_titleen
			,type_subject
			FROM `rms_subject` WHERE 1 ";
		$sublang=1;
		if(!empty($title)){
			$sublang = !empty(strpos($title,'(EN)'))?2:1;
			$title = str_replace('(EN)','', $title);
			$title = str_replace('(KH)','', $title);
			
			//$sql.=" AND subject_titlekh = '".$title."' OR  subject_titleen='".$title."'";
			//$sql.=" AND subject_lang= $sublang AND type_subject=1";
		}
		if(!empty($shortcut)){
			$sql.=" AND shortcut='".$shortcut."'";
		}
		$rsSubj =  $db->fetchRow($sql);
		$subjectIdOri = empty($rsSubj["id"]) ? 0 : $rsSubj["id"];
		$subject_titlekh = empty($rsSubj["subject_titlekh"]) ? $title : $rsSubj["subject_titlekh"];
		$subject_titleen = empty($rsSubj["subject_titleen"]) ? $title : $rsSubj["subject_titleen"];
		$typeSubject = empty($rsSubj["type_subject"]) ? 1 : $rsSubj["type_subject"];
		
		$sql=" SELECT id FROM `import_rms_subject` WHERE shortcut ='".$shortcut."' ";
		$subject_id = $db->fetchOne($sql);
		if(empty($subject_id)){
			$arr = array(
				'subject_titlekh'=>$subject_titlekh,
				'subject_titleen'=>$subject_titleen,
				'shortcut'		=>$shortcut,
				'schoolOption'	=>1,
				'is_parent'		=>0,
				'subject_lang'	=>$sublang ,
				'type_subject'	=>$typeSubject,
				'date'			=>date("Y-m-d"),
				'status'		=>1,
				'user_id'		=>$this->getUserId(),
				'strId'			=>$strId,
			);
			if(!empty($subjectIdOri)){
				$arr["subjectId"] = $subjectIdOri;
			}
			$this->_name='import_rms_subject';
			$subject_id = $this->insert($arr); 
		}
		return $subject_id;
	}
	public function getTeacherId($title,$gender,$strId,$shortcut=null){
	
		$db = $this->getAdapter();
		
			$sql="SELECT 
				id 
				,teacher_name_en
				,teacher_name_kh
				FROM 
				`rms_teacher` WHERE 1 ";
			if(!empty($title)){
				$sql.=" AND (teacher_name_kh = '".$title."' OR teacher_name_en = '".$title."')"; 
			}
			elseif(!empty($shortcut)){
				$sql.=" AND teacher_code = '".$shortcut."'";
			}
			
			$rsT =  $db->fetchRow($sql);
			$teacherIdOri = empty($rsT["id"]) ? 0 : $rsT["id"];
			$teacher_name_kh = empty($rsT["teacher_name_kh"]) ? $title : $rsT["teacher_name_kh"];
			$teacher_name_en = empty($rsT["teacher_name_en"]) ? $title : $rsT["teacher_name_en"];
        
			$dbg = new Application_Model_DbTable_DbGlobal();
			$sex= ($gender=='M')?1:2;
			$code = $dbg->getTeacherCode(1);
			
			$sessionXml = new Zend_Session_Namespace('xmlFile');
			$branchId = $sessionXml->xml_branch;
			$branchId = empty($branchId) ? 1 : $branchId;
			
			$sql=" SELECT id FROM `import_teacher` WHERE 1 ";
			$sql.=" AND (teacher_name_kh = '".$teacher_name_kh."' OR teacher_name_en = '".$teacher_name_en."')";
			
			$teacherIds = $db->fetchOne($sql);
			if(empty($teacherIds)){
				$_arr=array(
					'branch_id' 		 => $branchId,
					'strId'	 			 => $strId,
					'teacher_name_en'	 => $teacher_name_en,
					'teacher_name_kh'	 => $teacher_name_kh,
					'teacher_code'		 => $shortcut,
					'sex'				 => $sex,
					'nation' 			 => 1,
					'create_date' 		 => date("Y-m-d"),
					'user_id'	  		 => $this->getUserId(),
					'strId'	  		 	 => $strId,
				);	
				if(!empty($teacherIdOri)){
					$_arr["teacherId"] = $teacherIdOri;
				}
				$this->_name = "import_teacher";
				$teacherIds =  $this->insert($_arr);
			}
		
		return $teacherIds;
	}
	
	public function getGroupId($data){
		$db = $this->getAdapter();
		$title = empty($data["title"]) ? "" : $data["title"];
		$academicYear = empty($data["academicYear"]) ? "0" : $data["academicYear"];
		$strId = empty($data["strId"]) ? "0" : $data["strId"];
		$branchId = empty($data["branchId"]) ? "0" : $data["branchId"];
		
		$sql=" SELECT 
				id
				,degree
				,grade
			FROM `rms_group` 
			WHERE school_option=1 
			AND group_code like '%".$title."%' 
			AND academic_year=$academicYear
			AND branch_id=$branchId
		";
		$quer =  $db->fetchRow($sql);
		if(empty($quer)){
			$string='
				<div class="card-body bg-label-warning"> 
					<div class="row"> 
						<div class="col-md-12 col-sm-12 col-xs-12"> 
							<div class="d-flex"> 
								<div class="settings-main-icon ">
									<i class="fa fa-clock-o"></i>
								</div> 
								<div class="col-md-10 col-sm-10 col-xs-12"> 
									<p class="tx-20 font-weight-semibold d-flex ">ក្រុម / Class</p>
								</div> 
							</div>
							<small class="control-label bold col-md-12 col-sm-12 col-xs-12 text-center">
								<i class="fa fa-times-circle-o"></i> ព័ត៌មានក្រុម <strong>'.$title.'</strong> មិនមានក្នុងប្រព័ន្ធឡើយ។ សូមធ្វើការបញ្ចូលក្រុមថ្មីជាមុនសិន។
							</small>
						</div>
					</div>
				</div>';
			$sql = " TRUNCATE `import_rms_group` ";
			$db->query($sql);
			return array(
				"code" => "ERR",
				"msg" => $string,
			);
		}
				
		
		$groupId = $quer["id"];
		$degree = $quer["degree"];
		$grade = $quer["grade"];
		$dbg = new Application_Model_DbTable_DbGlobal();
		$_arr=array(
					'branch_id' 		 => $branchId,
					'group_code' 		 => $title,
					'academic_year' 	=> $academicYear,
					'degree' 	=> $degree,
					'grade' 	=> $grade,
					'strId' 		 	=> $strId,
					'create_date' 		 => date("Y-m-d"),
					'user_id'	  		 => $this->getUserId(),
					'strId'	  		 => $strId,
			);
		if(!empty($groupId)){
			$_arr["groupId"] = $groupId;
		}
		$this->_name = "import_rms_group";
		$groupIdS =  $this->insert($_arr);
		$string='
			<div class="card-body bg-label-success"> 
				<div class="row"> 
					<div class="col-md-12 col-sm-12 col-xs-12"> 
						<div class="d-flex"> 
							<div class="settings-main-icon ">
								<i class="fa fa-clock-o"></i>
							</div> 
							<div class="col-md-10 col-sm-10 col-xs-12"> 
								<p class="tx-20 font-weight-semibold d-flex ">ក្រុម / Class</p>
							</div> 
						</div>
						<small class="control-label bold col-md-12 col-sm-12 col-xs-12 text-center">
							<i class="fa fa-check-circle-o"></i> <strong>ព័ត៌មានក្រុមបានផ្ទៀងផ្ទាត់រួចរាល់</strong>
					   </small>
					</div>
				</div>
			</div>';
		return array(
			"code" => "SUCCESS",
			"msg" => $string,
		);
	}
	
	public function getSubjectIdbyStrId($strId){
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `import_rms_subject` WHERE strId = '".$strId."'" ;
		return $db->fetchOne($sql);
	}
	function getTeacherIdbyStrId($strId){
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `import_teacher` WHERE strId = '".$strId."'" ;
		return $db->fetchOne($sql);
	}
	function getGroupIdbyStrId($data){
		$db = $this->getAdapter();
		$academicYear = empty($data["academicYear"]) ? "0" : $data["academicYear"];
		$strId = empty($data["strId"]) ? "0" : $data["strId"];
		$sql=" SELECT id FROM `import_rms_group` WHERE academic_year =$academicYear AND strId = '".$strId."'" ;
		
		return $db->fetchOne($sql);
	}
	
	function dayofWeek($str){
		$days = array(
				'10000'=>1,//mon
				'01000'=>2,
				'00100'=>3,
				'00010'=>4,
				'00001'=>5,//fri
				);
		return $days[$str];
	}
	
	function getCardList($strId){
		$db = $this->getAdapter();
		$sql="SELECT
			periodstrId,
			fromhr,
			tohr,
			daystrId,
			settingDetailId
		FROM `rms_cards`
			WHERE lessionstrId='".$strId."'";
		$sql.=" ORDER BY daystrId ASC,fromhr ASC";
		return $db->fetchAll($sql);
	}
	function getSchedultSettingDetail($data){
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `rms_schedulesetting_detail` WHERE setting_id ='".$data['setting']."' AND from_hour =".$data['starttime']." AND to_hour = ".$data['endtime'];
		$checkSch = $db->fetchOne($sql);
		return $checkSch;
	}
	function addPeriod($data){
			$db = $this->getAdapter();
			$period = empty($data["period"]) ? "0" : $data["period"];
			$setting = empty($data["setting"]) ? "0" : $data["setting"];
			$starttime = empty($data["starttime"]) ? "0" : $data["starttime"];
			$data["starttime"] = str_replace(":", ".", $starttime);
			
			$endtime = empty($data["endtime"]) ? "0" : $data["endtime"];
			$data["endtime"] = str_replace(":", ".", $endtime);
		
		$db = $this->getAdapter();
		$sql=" SELECT id 
				FROM `import_rms_period` 
			WHERE period ='".$period."' 
				AND setting ='".$setting."' 
				AND starttime =".$data["starttime"]."
				AND endtime = ".$data["endtime"]."";
			$checkSch = $db->fetchOne($sql);
			
			if(empty($checkSch)){
				$detailSetting = $this->getSchedultSettingDetail($data);
				if(empty($detailSetting)){
					$string='
					<div class="card-body bg-label-warning"> 
						<div class="row"> 
							<div class="col-md-12 col-sm-12 col-xs-12"> 
								<div class="d-flex"> 
									<div class="settings-main-icon ">
										<i class="fa fa-clock-o"></i>
									</div> 
									<div class="col-md-10 col-sm-10 col-xs-12"> 
										<p class="tx-20 font-weight-semibold d-flex ">ម៉ោង / Time</p>
									</div> 
								</div>
								<small class="control-label bold col-md-12 col-sm-12 col-xs-12 text-center">
									<i class="fa fa-times-circle-o"></i> ព័ត៌មានម៉ោង <strong>'.$starttime.'-'.$endtime.'</strong> មិនមាន នៅក្នុងការកំណត់កាលវិភាគនេះឡើយ។ សូមធ្វើការបញ្ចូលបញ្ចីរម៉ោងសិក្សា
								</small>
							</div>
						</div>
					</div>';
					$sql = " TRUNCATE `import_rms_period` ";
					$db->query($sql);
					return array(
						"code" => "ERR",
						"msg" => $string,
					);
				}else{
					
					$_arr=array(
							'setting' 		 	=> $setting,
							'period' 		 	=> $period,
							'starttime'			=>$data["starttime"],
							'endtime'			=>$data["endtime"],
							'settingDetailId'	 =>$detailSetting,
					);
					$this->_name = "import_rms_period";
					$this->insert($_arr);
				}
			}
		
			
			$string='
				<div class="card-body bg-label-success"> 
					<div class="row"> 
						<div class="col-md-12 col-sm-12 col-xs-12"> 
							<div class="d-flex"> 
								<div class="settings-main-icon ">
									<i class="fa fa-clock-o"></i>
								</div> 
								<div class="col-md-10 col-sm-10 col-xs-12"> 
									<p class="tx-20 font-weight-semibold d-flex ">ម៉ោង / Time</p>
								</div> 
							</div>
							<small class="control-label bold col-md-12 col-sm-12 col-xs-12 text-center">
								<i class="fa fa-check-circle-o"></i> <strong>ព័ត៌មានម៉ោងបានផ្ទៀងផ្ទាត់រួចរាល់</strong>
							</small>
						</div>
					</div>
				</div>';
			return array(
				"code" => "SUCCESS",
				"msg" => $string,
			);
	}
	
	function getPeriodInfo($data){
		$db = $this->getAdapter();
		
		$period = empty($data["period"]) ? "0" : $data["period"];
		$setting = empty($data["setting"]) ? "0" : $data["setting"];
			
		$sql=" SELECT * FROM `import_rms_period` WHERE period =".$period."
		AND setting =".$setting."
		LIMIT 1" ;
		
		return $db->fetchRow($sql);
	}
	function addcardNew($data){
			$db = $this->getAdapter();
			$lessionId = empty($data["lessionId"]) ? "0" : $data["lessionId"];
			$days = empty($data["days"]) ? "0" : $data["days"];
			$period = empty($data["period"]) ? "0" : $data["period"];
			$prInfo = $this->getPeriodInfo($data);
			$_arr=array(
					'lessionstrId' 		 => $lessionId,
					'periodstrId'	 => $period,
					'fromhr'		=>$prInfo["starttime"],
					'tohr'			=>$prInfo["endtime"],
					'daystrId'	 	=>$this->dayofWeek($days),
					'settingDetailId'	 =>$prInfo["settingDetailId"],
			);
			$this->_name = "rms_cards";
			$this->insert($_arr);
			return array(
				"code" => "SUCCESS",
				"msg" => "",
			);
	}
	
	function importGroupSchedule($lessionId,$_data,$groupId){
		
		$db = $this->getAdapter();
		$sql=" SELECT id FROM `import_rms_group_schedule` WHERE academic_year ='".$_data['academicYear']."' AND group_id = '".$groupId."'" ;
		$checkSch = $db->fetchOne($sql);
		if(empty($checkSch)){
			
			$sessionXml = new Zend_Session_Namespace('xmlFile');
			$fileName = $sessionXml->xml_FileName;
			$_arr = array(
					'branch_id'=>$_data['branchId'],
					'academic_year'=>$_data['academicYear'],
					'group_id'=>$groupId,
					'schedule_setting'=>$_data['setting'],
					'note'=>$_data['note'],
					'status'=>0,
					'create_date'=>date("Y-m-d H:i:s"),
					'modify_date'=>date("Y-m-d H:i:s"),
					'user_id'=>$this->getUserId(),
					'fileName'=>$fileName,
			);
			$this->_name='import_rms_group_schedule';
			$checkSch = $this->insert($_arr);
		}
		$_data["main_schedule_id"] = $checkSch;
		$this->insertSubjectSchedule($lessionId,$_data,$groupId);
		return $checkSch;
	}
	
	function insertSubjectSchedule($lessionId,$data,$groupId){
		$db = $this->getAdapter();
		$results = $this->getCardList($lessionId);
		foreach($results as $rs){
			$arr = array(
					'main_schedule_id'		=>$data['main_schedule_id'],
					'branch_id'		=>$data['branchId'],
					'group_id'		=>$groupId,
					'year_id'		=>$data['academicYear'],
					'subject_id'		=>$data['subject_id'],
					'techer_id'		=>$data['techer_id'],
					
					'schedule_setting_id'	=>$rs['settingDetailId'],
					'from_hour'		=>$rs['fromhr'],
					'to_hour'		=>$rs['tohr'],
					'day_id'		=>$rs['daystrId'],
					
					'create_date'	=>date("Y-m-d H:i:s"),
					'status'		=>1,
					'user_id'		=>$this->getUserId(),
					
			);
			$this->_name='import_rms_group_reschedule';
			$checkSch = $this->insert($arr);
			
		}
	}
	
	
	
	function getAllGroupSchedule($search=null){
    	$db=$this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
		
		$label = "name_en";
		$subject = "subject_titleen";
		$branch = "branch_nameen";
		$teacherRoom = "teacher_name_en";
		$school_name = "school_nameen";
		$dayTitle="dTitleEn";
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$subject = "subject_titlekh";
    		$branch = "branch_namekh";
			$teacherRoom = "teacher_name_kh";
			$school_name = "school_namekh";
			$dayTitle="dTitleKh";
			
    	}
		
		$stringJsonSubject="
		,CONCAT(
			'[',
			GROUP_CONCAT( DISTINCT '{',
				'".'"subjId"'.":','".'"'."',COALESCE(gr.`subject_id`,''),'".'"'."',
				'".',"dayId"'.":','".'"'."',COALESCE(gr.`day_id`,''),'".'"'."',
				'".',"frTime"'.":','".'"'."',COALESCE(gr.`from_hour`,''),'".'"'."',
				'".',"toTime"'.":','".'"'."',COALESCE(gr.`to_hour`,''),'".'"'."',
				'".',"frTimeT"'.":','".'"'."',COALESCE((SELECT t.title FROM rms_timeseting AS t WHERE t.value =gr.from_hour LIMIT 1),''),'".'"'."',
				'".',"toTimeT"'.":','".'"'."',COALESCE((SELECT t.title FROM rms_timeseting AS t WHERE t.value =gr.to_hour LIMIT 1),''),'".'"'."',
				'".',"sTitleEn"'.":','".'"'."',COALESCE(subj.`subject_titleen`,''),'".'"'."',
				'".',"sTitleKh"'.":','".'"'."',COALESCE(subj.`subject_titlekh`,''),'".'"'."',
				'".',"sShort"'.":','".'"'."',COALESCE(subj.`shortcut`,''),'".'"'."',
				'".',"sLang"'.":','".'"'."',COALESCE(subj.`subject_lang`,''),'".'"'."',
				'".',"tNameEn"'.":','".'"'."',COALESCE(t.`teacher_name_en`,''),'".'"'."',
				'".',"tNameKh"'.":','".'"'."',COALESCE(t.`teacher_name_kh`,''),'".'"'."',
				'".',"tTel"'.":','".'"'."',COALESCE(t.`tel`,''),'".'"'."',
				
			'}' ORDER BY gr.`day_id` ASC,gr.`from_hour` ASC )
			,']'
			) AS jsonSubject
		";
		
		$stringJsonDay="
		,CONCAT(
			'[',
			GROUP_CONCAT( DISTINCT '{',
				'".'"dTitleEn"'.":','".'"'."',COALESCE(dsch.`dTitleEn`,''),'".'"'."',
				'".',"dTitleKh"'.":','".'"'."',COALESCE(dsch.`dTitleKh`,''),'".'"'."',
				'".',"dTitle"'.":','".'"'."',COALESCE(dsch.$dayTitle,''),'".'"'."',
				'".',"dayShort"'.":','".'"'."',COALESCE(dsch.`dayShort`,''),'".'"'."',
				'".',"dayId"'.":','".'"'."',COALESCE(dsch.`dayId`,''),'".'"'."',
			'}' ORDER BY dsch.`dayId` ASC )
			,']'
			) AS jsonDay
		";
    	$sql="
			SELECT 
			gr.id
			,gr.year_id
			,gr.branch_id
			,gr.group_id AS groupId
			,g.degree as degree
			,g.group_code as group_code
			,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id=gr.year_id LIMIT 1) AS academicYear
			
			,(SELECT photo FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_logo
			,(SELECT branch_nameen FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS branch_name
			,(SELECT $school_name FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS school_name
			,(SELECT school_namekh FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS school_namekh
			,(SELECT school_nameen FROM `rms_branch` WHERE br_id=gr.branch_id LIMIT 1) AS school_nameen
			
			,(SELECT $teacherRoom  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_id )AS teacher_room
			,(SELECT tel  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_id )AS teacher_tel
			,(SELECT $teacherRoom  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_assistance )AS teacher_ta
			,(SELECT tel  FROM `rms_teacher` WHERE  rms_teacher.id=g.teacher_assistance )AS ta_tel
			
			
			,(SELECT room_name AS NAME FROM `rms_room` WHERE is_active=1 AND room_name!='' AND rms_room.room_id=g.room_id ) AS room_name
			,CONCAT(itd.title,' (',ite.title,')') AS grade_name
			,REPLACE(CONCAT(gr.from_hour,'-',to_hour),' ','') AS times
			
    	
		
		";
		$sql.=$stringJsonDay;
		$sql.=$stringJsonSubject;
		$sql.="
			FROM 
			import_rms_group_reschedule AS gr 
			JOIN import_rms_group AS g ON g.id = gr.group_id
				LEFT JOIN rms_items AS ite ON ite.id  = g.degree
				LEFT JOIN rms_itemsdetail AS itd ON itd.id  = g.grade AND itd.title!=''
				LEFT JOIN import_teacher AS t ON t.id = gr.`techer_id`
				LEFT JOIN `import_rms_subject` AS subj ON subj.id = gr.`subject_id`
				LEFT JOIN v_sch_daybygroup_impt AS dsch ON  dsch.`groupId`  = gr.`group_id` AND gr.`branch_id` = dsch.`branchId`
				
		";
    	 
    	$where =' WHERE 1';
   
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " gr.`note` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    
    	$order=" 
		GROUP BY gr.year_id,gr.group_id
    	ORDER BY gr.year_id,COALESCE(ite.ordering,'0'),COALESCE(itd.ordering,'0'),g.group_code,times ASC 
		";
    	if(!empty($search['branch_id'])){
    		$where.=' AND gr.branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=' AND gr.year_id='.$search['academic_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND gr.group_id='.$search['group'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND g.degree='.$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=' AND g.grade='.$search['grade'];
    	}
    	if(!empty($search['room'])){
    		$where.=' AND g.room_id='.$search['room'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("gr.branch_id");
    	return $db->fetchAll($sql.$where.$order);
    }
	
	function getCountingSubjectByGroup($search=array()){
		
		$db=$this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
    	$lang = $dbp->currentlang();
		
		$subject = "subject_titleen";
    	if($lang==1){// khmer
    		$subject = "subject_titlekh";
    	}
		$sql="
			SELECT 
				gr.id
				,gr.year_id AS academicYear
				,gr.group_id AS groupId
				,gr.subject_id
				,CASE 
					WHEN s.subject_lang =1 THEN s.subject_titlekh
					ELSE s.subject_titleen END AS subjectName
				,s.subject_titlekh AS subjectNameKh
				,s.subject_titleen AS subjectNameEn
				,s.subject_lang AS subjectLang
				,COUNT(*)AS totalHour
			FROM 
				(import_rms_group_reschedule AS gr  JOIN rms_group AS g ON g.id = gr.group_id) 
				LEFT JOIN import_rms_subject AS s ON s.id=gr.subject_id
		";
    	 
    	$where =' WHERE 1';
		
		if(!empty($search['branch_id'])){
    		$where.=' AND gr.branch_id='.$search['branch_id'];
    	}
    	if(!empty($search['academic_year'])){
    		$where.=' AND gr.year_id='.$search['academic_year'];
    	}
    	if(!empty($search['group'])){
    		$where.=' AND gr.group_id='.$search['group'];
    	}
    	if(!empty($search['degree'])){
    		$where.=' AND g.degree='.$search['degree'];
    	}
    	if(!empty($search['grade'])){
    		$where.=' AND g.grade='.$search['grade'];
    	}
    	if(!empty($search['room'])){
    		$where.=' AND g.room_id='.$search['room'];
    	}
    	$where.=$dbp->getAccessPermission("gr.branch_id");
		$order="
		GROUP BY  gr.group_id, gr.subject_id
		ORDER BY  s.subject_lang  ASC , CASE 
					WHEN s.subject_lang =1 THEN s.subject_titlekh
					ELSE s.subject_titleen END ASC ";
    	return $db->fetchAll($sql.$where.$order);
	}
	
	function insertSubject($data){
    	$db = $this->getAdapter();
    	try{
	    	$arr = array(
	    			'subject_titleen'=>$data['nameEn'],
	    			'subject_titlekh'=>$data['nameKh'],
	    			'shortcut'=>$data['shortcut'],
	    			'subject_lang'=>$data['subjectLang'],
	    			'user_id'=>$this->getUserId(),
	    			'date'=>date("Y-m-d H:i:s"),
	    			'type_subject'=>1,
	    			'status'=>1,
	    			'is_parent'=>1,
	    		);
	    	$this->_name='rms_subject';
			$subjectId = $this->insert($arr);
			
			
			$arrUpdate = array(
	    			'subjectId'=>$subjectId,
	    		);
			$where = 'id = '.$data['id'];
			$this->_name='import_rms_subject';
			$this->update($arrUpdate, $where);
			
			return $subjectId;
			
    	}catch(exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	
	function insertTeacher($data){
    	$db = $this->getAdapter();
    	try{
			
			$data['branch_id'] = empty($data['branch_id']) ? 1 : $data['branch_id'];
			$dbg = new Application_Model_DbTable_DbGlobal();
			$code = $dbg->getTeacherCode($data['branch_id']);
			
	    	$arr = array(
	    			'teacher_code'=>$code,
	    			'branch_id'=>$data['branch_id'],
	    			'teacher_name_en'=>$data['nameEn'],
	    			'teacher_name_kh'=>$data['nameKh'],
	    			'tel'=>$data['tel'],
	    			'sex'=>$data['sex'],
	    			'user_id'=>$this->getUserId(),
	    			'create_date'=>date("Y-m-d H:i:s"),
	    			'status'=>1,
	    		);
	    	$this->_name='rms_teacher';
			$teacherId = $this->insert($arr);
			
			
			$arrUpdate = array(
	    			'teacherId'=>$teacherId,
	    		);
			$where = 'id = '.$data['id'];
			$this->_name='import_teacher';
			$this->update($arrUpdate, $where);
			
			return $teacherId;
			
    	}catch(exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	
	function insertGroup($data){
    	$db = $this->getAdapter();
    	try{
			
			$data['branch_id'] = empty($data['branch_id']) ? 1 : $data['branch_id'];
			$dbg = new Application_Model_DbTable_DbGlobal();
			$code = $dbg->getTeacherCode($data['branch_id']);
			
	    	$arr = array(
	    			'branch_id'=>$data['branch_id'],
	    			'group_code'=>$data['nameEn'],
	    			'degree'=>$data['degree'],
	    			'teacher_id'=>$data['teacher_id'],
	    			'create_date'=>date("Y-m-d H:i:s"),
	    			'status'=>1,
	    		);
	    	$this->_name='rms_group';
			$groupId = $this->insert($arr);
			
			
			$arrUpdate = array(
	    			'groupId'=>$groupId,
	    		);
			$where = 'id = '.$data['id'];
			$this->_name='import_rms_group';
			$this->update($arrUpdate, $where);
			
			return $groupId;
			
    	}catch(exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	
	function getAllImportSchedule(){
		$db=$this->getAdapter();
		$sql="
			SELECT 
				sch.`id`
				,sch.`branch_id`
				,sch.`academic_year`
				,g.id AS groupId
				,g.`groupId` AS originalGroupId
				,g.`degree`
				,g.`grade`
				,sch.`schedule_setting`
				,sch.`settingScoreAttId`
				,sch.`status`
				,sch.`fileName`
			FROM `import_rms_group_schedule` AS sch
				LEFT JOIN `import_rms_group` AS g ON g.id = sch.`group_id` 
			ORDER BY g.`degree`, g.`grade`
		";
		return $db->fetchAll($sql);
	}
	function getAllImportScheduleDetail(){
		$db=$this->getAdapter();
		$sql="
			SELECT 
				sd.id
				,sd.`branch_id`
				,sd.`year_id`
				,sd.`main_schedule_id` AS mainScheuldId
				,sd.`day_id`
				,sd.`from_hour`
				,sd.`to_hour`
				,sd.`group_id` AS groupId
				,g.`groupId` AS originalGroupId
				,sd.`subject_id` AS subjectId
				,sj.`subjectId` AS originalSubjectId
				,sd.`techer_id` AS teacherId
				,t.`teacherId` AS originalTeacherId
				,sd.`schedule_setting_id`
				,'1' AS `study_type`

				FROM `import_rms_group_reschedule` AS sd 
					LEFT JOIN `import_rms_group` AS g ON g.id = sd.`group_id` 
					LEFT JOIN `import_rms_subject` AS sj ON sj.id = sd.`subject_id`
					LEFT JOIN `import_teacher` AS t ON t.id = sd.`techer_id` 
				ORDER BY g.`degree`,g.`grade`,sd.`group_id`,sd.`day_id`,sd.`from_hour`
		";
		return $db->fetchAll($sql);
	}
	function submitFinalSchedule($data){
		try{
			$subjectNewList = $this->getNewSubjectImport(2);
			if(!empty($subjectNewList)){
				if(!empty($subjectNewList)) foreach($subjectNewList as $rowSub){
					$this->insertSubject($rowSub);
				}
			}
			
			$teacherNewList = $this->getNewTeacherImport(2);
			if(!empty($teacherNewList)){
				if(!empty($teacherNewList)) foreach($teacherNewList as $rowTeacher){
					$this->insertTeacher($rowTeacher);
				}
			}
			
			$newGroupImpList = $this->getNewGroupImport(2);
			if(!empty($newGroupImpList)){
				if(!empty($newGroupImpList)) foreach($newGroupImpList as $rowGroup){
					$this->insertGroup($rowGroup);
				}
			}
			
			$allScheudeImpt = $this->getAllImportSchedule();
			$allScheudeDetail = $this->getAllImportScheduleDetail();
			
			if(!empty($allScheudeImpt)) foreach($allScheudeImpt as $sch){
				$strFilter =  (string) $sch['id'];
				$detailSchedule = array_filter($allScheudeDetail, function ($item) use ($strFilter) {
					if ($item['mainScheuldId'] == $strFilter) {
						return $item;
					}
					return;
				});
				$this->insertSchedule($sch,$detailSchedule);
			}
			
			return array(
				"code" => "SUCCESS",
				"msg" => "",
			);
		
		}catch(exception $e){
			
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return array(
				"code" => "ERR",
				"msg" => $e->getMessage(),
			);
    	}
	}
	
	function checkingSubjectInGroupDetail($data){
		$db=$this->getAdapter();
		
		$groupId = $data['originalGroupId'];
		$subjectId = $data['originalSubjectId'];
		$sql="
			SELECT 
				sd.id
			FROM `rms_group_subject_detail` AS sd 
			WHERE sd.group_id = $groupId AND sd.subject_id = $subjectId
		";
		return $db->fetchOne($sql);
	}
	function insertSchedule($data,$detailSchedule){
    	$db = $this->getAdapter();
    	try{
	    	$arr = array(
	    			'branch_id'		=>$data['branch_id'],
	    			'academic_year'	=>$data['academic_year'],
	    			'group_id'		=>$data['originalGroupId'],
	    			'schedule_setting'			=>$data['schedule_setting'],
	    			'settingScoreAttId'			=>$data['settingScoreAttId'],
					
	    			'user_id'		=>$this->getUserId(),
	    			'create_date'	=>date("Y-m-d H:i:s"),
	    			'modify_date'	=>date("Y-m-d H:i:s"),
	    			'note'			=>$data['fileName'],
	    			'status'=>0,
	    		);
	    	$this->_name='rms_group_schedule';
			$mainScheduleId = $this->insert($arr);
			
			if(!empty($detailSchedule)) foreach($detailSchedule as $detail){
				$arrDetail = array(
	    			'main_schedule_id'	=>$mainScheduleId,
	    			'branch_id'		=>$detail['branch_id'],
	    			'group_id'		=>$detail['originalGroupId'],
	    			'year_id'		=>$detail['year_id'],
	    			'day_id'		=>$detail['day_id'],
	    			'from_hour'		=>$detail['from_hour'],
	    			'to_hour'		=>$detail['to_hour'],
	    			'subject_id'		=>$detail['originalSubjectId'],
	    			'techer_id'			=>$detail['originalTeacherId'],
	    			'schedule_setting_id'		=>$detail['schedule_setting_id'],
	    			'study_type'				=>$detail['study_type'],
	    			'note'				=>$data['fileName'],
	    			
					'status'		=>1,
	    			'user_id'		=>$this->getUserId(),
	    			'create_date'	=>date("Y-m-d H:i:s"),
	    		);
				$this->_name='rms_group_reschedule';
				$this->insert($arrDetail);
				
				/*
				ប្តូរទៅកន្លែង Public Schedule វិញ ចំពោះការ Update Group Detail Subject
				$detail['originalGroupId'] = empty($detail['originalGroupId']) ? 0 : $detail['originalGroupId'];
				$detail['originalSubjectId'] = empty($detail['originalSubjectId']) ? 0 : $detail['originalSubjectId'];
				if( !empty($detail['originalGroupId']) && !empty($detail['originalSubjectId']) ){
					$checking = $this->checkingSubjectInGroupDetail($detail);
					if(!empty($checking)){
						$this->_name = "rms_group_subject_detail";
						$dataSubj = array(
							'teacher' =>$detail['originalTeacherId'],
						);
						$where = "group_id=".$detail['originalGroupId']." AND subject_id=".$detail['originalSubjectId'];
						$this->update($dataSubj,$where);
					}else{
						$this->_name = "rms_group_subject_detail";
						$dataSubj = array(
							'teacher' =>$detail['originalTeacherId'],
							'group_id' =>$detail['originalGroupId'],
							'subject_id' =>$detail['originalSubjectId'],
							'amount_subject' =>1,
							'max_score' =>0,
							'score_short' =>0,
							
							'semester_max_score' =>0,
							'amount_subject_sem' =>1,
							'note'   		=> 'new subject import',
							'date' 			=> date("Y-m-d"),
							'user_id'		=> $this->getUserId(),
						);
						$this->_name='rms_group_subject_detail';
						$this->insert($dataSubj);
					}
					
				}
				*/
				
			}
			return $mainScheduleId;
			
    	}catch(exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	
	
	public function getScheduleSettingDetailForExcel($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				s.*
			FROM 
				`rms_schedulesetting_detail` AS s 
			WHERE s.setting_id=".$data["scheduleSetting"]." 
				AND from_hour=".$data["fromHour"]." 
				AND to_hour=".$data["toHour"]." ";
		return $db->fetchRow($sql);
	}
	public function getTeacherIdExcel($data){
		$db = $this->getAdapter();
		$sex=1;
		
		$title = $data["teacherName"];
		$phone = empty($data["teacherPhone"]) ? null : $data["teacherPhone"];
		
		$titleSub=explode(",",$title);
		if(count($titleSub)==1){
			$teacher_Code=trim($titleSub[0]);
			$kh_name=trim($titleSub[0]);
			$eng_name=trim($titleSub[0]);
		}else if(count($titleSub)==2){
			$teacher_Code=trim($titleSub[0]);
			$kh_name=trim($titleSub[1]);
			$eng_name=trim($titleSub[1]);
		}else{
			$teacher_Code=trim($titleSub[0]);
			$kh_name=trim($titleSub[1]);
			$eng_name=trim($titleSub[2]);

			$sex=1;
			
		}

		$tel='';
		if(!empty($phone)){
			$tel=$phone;
		}else{
			$tel='';
		}
		if(!empty($title)){
			$sql=" 
				SELECT 
					s.id 
					,s.teacher_name_en AS nameEn
					,s.teacher_name_kh AS nameKh
					,s.teacher_code AS teacherCode
					,s.teacher_code AS shortcut
					,s.sex AS sex
				FROM 
					`rms_teacher` AS s
				WHERE s.teacher_code = '".$teacher_Code."' ";
			$rs =  $db->fetchRow($sql);
			
			$teacherIdOri = empty($rs["id"]) ? 0 : $rs["id"];
			$teacher_name_en = empty($rs["nameEn"]) ? $eng_name : $rs["nameEn"];
			$teacher_name_kh = empty($rs["nameKh"]) ? $kh_name : $rs["nameKh"];
			$teacher_code_im = empty($rs["teacherCode"]) ? $teacher_Code : $rs["teacherCode"];
			$sex = empty($rs["sex"]) ? $sex : $rs["sex"];
			
			$sql=" SELECT id FROM `import_teacher` WHERE teacher_code ='".$teacher_code_im."' ";
			$teacherId = $db->fetchOne($sql);
			if(empty($teacherId)){
				$_arr=array(
						'branch_id' 		 => $data['branchId'],
						'teacher_name_en'	 => $teacher_name_en,
						'teacher_name_kh'	 => $teacher_name_kh,
						'teacher_code'	 	 => $teacher_code_im,
						'sex'				 => $sex,
						'tel'				 => $tel,
						'nation' 			 => 1,
						'create_date' 		 => date("Y-m-d"),
						'user_id'	  		 => $this->getUserId(),
				);	
				if(!empty($teacherIdOri)){
					$_arr["teacherId"] = $teacherIdOri;
				}
				$this->_name = "import_teacher";
				$teacherId =  $this->insert($_arr);
			}else{
				if(!empty($phone)){
					$_arr=array(
						'tel' => $tel,
					);
					$this->_name = "import_teacher";
					$where = 'id=' . $teacherId;
					$this->update($_arr, $where);
				}
			}
		}else{
			$teacherId=0;
		}
		return $teacherId;
	}
	public function getGroupIdExcel($data){
		$db = $this->getAdapter();
		
		$branchId = $data["branchId"];	
		$groupName = $data["groupName"];	
		$academicYear = $data["academicYear"];	
		$degreeD = $data["degree"];	
		if(!empty($groupName)){
			$sql=" 
				SELECT 
					s.id 
					,s.degree
					,s.grade
				FROM 
					`rms_group` AS s
				WHERE 
					s.group_code = '".$groupName."' 
					AND s.academic_year = '".$academicYear."' 
					AND s.branch_id = '".$branchId."' 
				";
			$rs =  $db->fetchRow($sql);
			
			$groupIdori = empty($rs["id"]) ? 0 : $rs["id"];
			if(!empty($groupIdori)){
				$_arr=array(
						'teacher_id' => $data['teacherId'],
				);
				$this->_name = "rms_group";
				$where = 'id=' . $groupIdori;
				$this->update($_arr, $where);
			}

			$degree = empty($rs["degree"]) ? $degreeD : $rs["degree"];
			$grade = empty($rs["grade"]) ? 0 : $rs["grade"];
			
			$sql=" SELECT id FROM `import_rms_group` WHERE group_code ='".$groupName."' ";
			$groupId = $db->fetchOne($sql);
			if(empty($groupId)){
				$_arr=array(
						'branch_id'   	 => $branchId,
						'group_code'  	 => $groupName,
						'academic_year'  => $academicYear,
						'teacher_id'  	 => $data['teacherId'],
						'degree'  		 => $degree,
						'is_use'     	 => 1,
						'school_option'  => 1,
						'is_pass'     	 => 0,
						'status'     	 => 1,
						'create_date' 	 => date("Y-m-d H:i:s"),
						'user_id'	 	 => $this->getUserId()
					);
				
				if(!empty($groupIdori)){
					$_arr["groupId"] = $groupIdori;
				}
				$this->_name = "import_rms_group";
				$groupId =  $this->insert($_arr);
			}
		}else{
			$groupId=0;
		}
		return $groupId;
	}
	
	public function getSubjectIdExcel($data){
		$db = $this->getAdapter();
		
		$title = $data["subjectTitle"];
		$subLang = $data["subLang"];
		
		if(!empty($subLang)){
			if($subLang=='k' || $subLang=='K'){
				$subject_lang=1;
			}elseif($subLang=='e'|| $subLang=='E'){
				$subject_lang=2;
			}elseif($subLang=='c'|| $subLang=='C'){
				$subject_lang=3;
			}
		}
		$titleSub=explode(",",$title);
		if(count($titleSub)==1){
			$kh_name=trim($titleSub[0]);
			$eng_name=trim($titleSub[0]);
		}else{
			$kh_name=trim($titleSub[0]);
			$eng_name=trim($titleSub[1]);
		}
		
		$subject_type=1;
		if($kh_name=="ចេញលេង"){
			$subject_type=2;
			$subject_lang='';
		}
		
		if(!empty($title)){
			$sql=" 
			SELECT 
					s.id
					,s.subject_titlekh
					,s.subject_titleen
					,s.shortcut
					,s.type_subject
					,s.subject_lang
			FROM 
				`rms_subject` AS s
			WHERE 
				s.subject_titlekh = '".$kh_name."' ";
			if($subject_type==1){
				$sql.="  
					AND s.subject_lang= $subject_lang 
					AND s.type_subject=1 
					";
			}elseif($subject_type==2){
				$sql.=" AND s.type_subject=2 ";
			}
			$rs =  $db->fetchRow($sql);
			
			$subjectIdOri = empty($rs["id"]) ? 0 : $rs["id"];
			$subjectKH = empty($rs["subject_titlekh"]) ? $kh_name : $rs["subject_titlekh"];
			$subjectEn = empty($rs["subject_titleen"]) ? $eng_name : $rs["subject_titleen"];
			$subjectShortcut = empty($rs["shortcut"]) ? $eng_name : $rs["shortcut"];
			$subjectLang = empty($rs["subject_lang"]) ? $subject_lang : $rs["subject_lang"];
			$subjectType = empty($rs["type_subject"]) ? $subject_type : $rs["type_subject"];
			
			
			$sql=" SELECT id FROM `import_rms_subject` WHERE subject_titlekh ='".$subjectKH."' ";
			$subjectId = $db->fetchOne($sql);
			if(empty($subjectId)){
				$_arr=array(
						'subject_titlekh'=>$subjectKH,
						'subject_titleen'=>$subjectEn,
						'shortcut'		=>$subjectShortcut,
						'schoolOption'	=>1,
						'is_parent'		=>0,
						'subject_lang'	=>$subjectLang,
						'type_subject'	=>$subjectType,
						'date'			=>date("Y-m-d"),
						'status'		=>1,
						'user_id'		=>$this->getUserId(),
					);
				
				if(!empty($subjectIdOri)){
					$_arr["subjectId"] = $subjectIdOri;
				}
				$this->_name = "import_rms_subject";
				$subjectId =  $this->insert($_arr);
			}
		}else{
			$subjectId=0;
		}
		return $subjectId;
	}
	public function ScheduleByImport($sheetData,$data){
		
		$this->truncateStringCode();
    	$db = $this->getAdapter();
    	$count = count($sheetData);
		
		$sessionXml = new Zend_Session_Namespace('xmlFile');
		$fileName = $sessionXml->xml_FileName;
		$data['branchId'] = empty($data['branchId']) ? 1 : $data['branchId'];
		$data['academicYear'] = empty($data['academicYear']) ? 8 : $data['academicYear'];
		$data['scheduleSetting'] = empty($data['scheduleSetting']) ? 0 : $data['scheduleSetting'];
		
		$academicYear = $data['academicYear'];
		$branchId = $data['branchId'];
		$scheduleSetting = $data['scheduleSetting'];
		
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$allDay = $dbg->getAllDay(1);
	
    	for($i=3; $i<=$count; $i++){

		
			$time = $sheetData[$i]['D'];
			$teacherPhone = $sheetData[$i]['B'];
			
			$titleTime=explode("-",$time);
			$startTime=$titleTime[0];
			$endTime=$titleTime[1];
			
			$starttime = empty($startTime) ? "0" : $startTime;
			$fromHour = str_replace(":", ".", $starttime);
			$data["fromHour"] = $fromHour;
			
			$endtime = empty($endTime) ? "0" : $endTime;
			$toHour = str_replace(":", ".", $endtime);
			$data["toHour"] = $toHour;
			
			$scheduleid_detail=$this->getScheduleSettingDetailForExcel($data);

			$groupName=$sheetData[$i]['C'];
			$numbers = preg_replace('/[^0-9]/', '', $groupName);

			if($groupName[0]=='K'){
				$degree= 1;
			}else if($numbers<=6){
              $degree= 2;
			}else if($numbers > 6 AND $numbers< 10){
				$degree= 3;
			}else{
				$degree= 4;
			}
			if(!empty($groupName)){
				$data['groupName'] = $groupName;
				$data['teacherId'] = 0;
				$data['degree'] = $degree;
				if(!empty($sheetData[$i]['A'])){
					$data["teacherPhone"] = $teacherPhone;
					$data["teacherName"] = $sheetData[$i]['A'];
					$teacher_id = $this->getTeacherIdExcel($data);
					$data['teacherId'] = $teacher_id;
				}
				$groupId = $this->getGroupIdExcel($data);
				
				$sql2="
					SELECT 
						id 
					FROM 
						`import_rms_group_schedule` 
					WHERE 
					group_id = ".$groupId." 
					AND branch_id = ".$branchId." 
					AND  academic_year=".$academicYear;
				$scheduleId =  $db->fetchOne($sql2);
				
				if(empty($scheduleId)){
					$_arr = array(
						'branch_id'     	=>$branchId,
						'academic_year'  	=>$academicYear,
						'group_id'		 	=>$groupId,
						'schedule_setting'	=>$scheduleSetting,
						'status'			=>1,
						'fileName'			=>$fileName,
						'create_date'		=>date("Y-m-d H:i:s"),
						'modify_date'		=>date("Y-m-d H:i:s"),
						'user_id'			=>$this->getUserId(),
					);
					$this->_name='import_rms_group_schedule';
					$scheduleId = $this->insert($_arr);
				}
				
				$dayData = array( 
					$sheetData[$i]['E'],
					$sheetData[$i]['H'],
					$sheetData[$i]['K'],
					$sheetData[$i]['N'],
					$sheetData[$i]['Q'],
				//	$sheetData[$i]['T'],
				);
				
				$teacherData = array( 
					$sheetData[$i]['F'],
					$sheetData[$i]['I'],
					$sheetData[$i]['L'],
					$sheetData[$i]['O'],
					$sheetData[$i]['R'],
				//	$sheetData[$i]['U'],
				);
				$subLang = array( 
					$sheetData[$i]['G'],
					$sheetData[$i]['J'],
					$sheetData[$i]['M'],
					$sheetData[$i]['P'],
					$sheetData[$i]['S'],
				//	$sheetData[$i]['V'],
				);
				
				
				if(!empty($sheetData[$i]['T'])){
					array_push($dayData,$sheetData[$i]['T']);
					array_push($teacherData,$sheetData[$i]['U']);
					array_push($subLang,$sheetData[$i]['V']);
				}
				
				$dayId=1;
				for($j=0; $j<count($dayData); $j++){

						$subject_id=0;
						if(!empty($dayData[$j])){
							$data["subjectTitle"] = $dayData[$j];
							$data["subLang"] = $subLang[$j];
							$subject_id = $this->getSubjectIdExcel($data);
						}
						$teacherId = 0;
						if(!empty($teacherData[$j])){
							$data["teacherPhone"] = null;
							$data["teacherName"] = $teacherData[$j];
							$teacherId = $this->getTeacherIdExcel($data);
						}
						
						if(!empty($dayData[$j])){
							$arr = array(
								'main_schedule_id'		=>$scheduleId,
								'branch_id'				=>$branchId,
								'group_id'				=>$groupId,
								'year_id'				=>$academicYear,
								'day_id'				=>$dayId,
								'techer_id'				=>$teacherId ,
								'subject_id'			=>$subject_id,
								'schedule_setting_id'	=>$scheduleid_detail['id'],
								'from_hour'				=>$fromHour,
								'to_hour'				=>$toHour,	
								'create_date'			=>date("Y-m-d H:i:s"),
								'study_type'			=>1,
								'status'				=>1,
								'user_id'				=>$this->getUserId(),
							);
							$this->_name='import_rms_group_reschedule';
							$this->insert($arr); 
						}
					$dayId++;
					
				}
			
			}

			
    	}
		
    }
	
	
	function submitDataFinalSchedule(){
		try{
			$data = array();
			$return = $this->submitFinalSchedule($data);
			$messageReturn = $return["msg"];
			$returnCod = $return["code"];
			if($return["code"]=="ERR"){
				$array = array(
					'content'=>"",
					'msg'=>"invalid",
				);
			}else{
				$sessionXml=new Zend_Session_Namespace('xmlFile');
				$sessionXml->unsetAll();
				
				$this->truncateStringCode();
				$array = array(
					'content'=>"",
					'msg'=>"completed",
				);
			}
			return $array;
		}catch(exception $e){
			
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return array(
				"code" => "ERR",
				"msg" => $e->getMessage(),
			);
    	}
	}
	
}   

