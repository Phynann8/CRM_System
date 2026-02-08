<?php

class Foundation_Model_DbTable_DbGroupStudentChangeGroup extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_group_student_change_group';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getfromGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT g.id,
				CONCAT(g.group_code,' ',(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1)) AS group_code,
			    COUNT(stu_id) 
			  FROM
			   `rms_group` AS g 
			   LEFT JOIN
			   `rms_group_detail_student` AS gds
			   ON  g.id = gds.group_id WHERE group_code!='' ";
			$request=Zend_Controller_Front::getInstance()->getRequest();
			if($request->getActionName()=='add'){
				$sql.=" AND gds.is_pass=2 ";
			}
			$sql.=" GROUP BY g.id  ORDER BY g.id DESC";
		return $db->fetchAll($sql);
	}
	public function gettoGroup(){
		$db = $this->getAdapter();
		$sql = "SELECT group_code,id FROM `rms_group` where status = 1 and is_pass IN (0,2) AND group_code!=''";
		return $db->fetchAll($sql);
	}
	
	public function selectAllStudentChangeGroup($search){
		$_db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();
		$branch = $dbp->getBranchDisplay();
		
		$lastestYear = 0;
		$last = $dbp->getLatestAcadmicYear();
		if(!empty($last)){
			$lastestYear = empty($last["id"]) ? 0 : $last["id"];
		}
		
		$colunmname='title_en';
		$label = 'name_en';
		$month = "month_en";
		$titleCol = "title";
		if ($currentLang==1){
			$colunmname='title';
			$label = 'name_kh';
			$month = "month_kh";
			$titleCol = "titleKh";
		}
		$sql = "SELECT 
					gscg.id
					,(SELECT b.$branch FROM `rms_branch` AS b  WHERE b.br_id = g.branch_id LIMIT 1) AS branch_name
					,CONCAT(COALESCE(g.group_code,''),' ',ac.fromYear,'-',ac.toYear)  as group_code
					,(SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=g.grade ) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) as grade
				
					,(select $label from rms_view where rms_view.type=17 and rms_view.key_code=gscg.change_type LIMIT 1) as changeType
				
					
					,CASE
		   				WHEN  change_type = 3 THEN (SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gscg.academic_year LIMIT 1)
		  				ELSE CONCAT(COALESCE(toG.group_code,''),' ',acT.fromYear,'-',acT.toYear)
		   			END  AS to_academic
		   			,CASE
		   				WHEN  change_type = 3 THEN (SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=gscg.grade ) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1)
		  				ELSE (SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=toG.grade ) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1)
		   			END  AS to_grade
					,(SELECT p.$titleCol FROM `rms_parttime_list` AS p WHERE p.id = toG.session LIMIT 1) AS to_session
					,moving_date
					,gscg.note
			";
		$sql.=$dbp->caseStatusShowImage("gscg.status");
		$sql.=" ,u.first_name as userName ";
		
		$dbUser = new Application_Model_DbTable_DbUsers();
		$permission = $dbUser->getAccessUrl("foundation","groupstudentchangegroup","rollback");
		if (empty($permission)){
			$sql.=" 
				,'' AS slqButton
			";
		}else{
			$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
			$urlEdit = $base_url."/foundation/groupstudentchangegroup/rollback/id/";
			$arr=[
				"id"=>"gscg.id",
				"urlEdit"=>$urlEdit,
				"title"=>"ROLLBACK",
				"btnIcon"=>"fa-repeat",
			];
			$sqlBtn=$dbp->slqRowButton($arr);
			$sql.=" 
				,CASE 
					WHEN gscg.status = 0 THEN '' 
					ELSE 
						CASE 
							WHEN change_type = 3  
								THEN CASE 
									WHEN gscg.academic_year < $lastestYear 
										THEN ''
									ELSE ".$sqlBtn." 
								END
							WHEN change_type = 1  
								THEN CASE 
									WHEN toG.academic_year < $lastestYear 
										THEN ''
									ELSE ".$sqlBtn." 
								END
							WHEN change_type = 2  
								THEN CASE 
									WHEN toG.academic_year < $lastestYear 
										THEN ''
									ELSE ".$sqlBtn." 
								END
							WHEN change_type = 6  
								THEN CASE 
									WHEN toG.academic_year < $lastestYear 
										THEN ''
									ELSE ".$sqlBtn." 
								END
							ELSE 
								CASE 
									WHEN g.academic_year < $lastestYear 
										THEN ''
									ELSE ".$sqlBtn." 
								END
						END
				END AS slqButton
			";
		}
		
		
		
		$sql.=" FROM 
					`rms_group_student_change_group` as gscg
					 JOIN rms_group as g ON g.id=gscg.from_group 
					 LEFT JOIN `rms_academicyear` AS ac ON ac.id = g.academic_year
					 LEFT JOIN rms_group AS toG ON toG.id=gscg.to_group 
					 LEFT JOIN `rms_academicyear` AS acT ON acT.id = gscg.academic_year 
					 LEFT JOIN rms_users AS u ON u.id= gscg.`user_id`
				WHERE 1 ";
				
		$from_date =(empty($search['start_date']))? '1': "gscg.moving_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "gscg.moving_date <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND ".$from_date." AND ".$to_date;
		
		$order_by=" order by id DESC";
		$where=" ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " g.group_code LIKE '%{$s_search}%'";
			$s_where[] = " toG.group_code LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=g.grade) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT rms_itemsdetail.$colunmname FROM `rms_itemsdetail` WHERE (`rms_itemsdetail`.`id`=toG.grade) AND (`rms_itemsdetail`.`items_type`=1) LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.=" AND g.branch_id=".$search['branch_id'];
		}
		if(!empty($search['academic_year'])){
			$where.=" AND g.academic_year=".$search['academic_year'];
		}
		if(!empty($search['degree'])){
			$where.=" AND g.degree=".$search['degree'];
		}
		if(!empty($search['grade'])){
			$where.=" AND g.grade=".$search['grade'];
		}
		if(!empty($search['partTimeList'])){
			$where.=" AND toG.session=".$search['partTimeList'];
		}
		if(!empty($search['change_type'])){
			$where.=" AND gscg.change_type=".$search['change_type'];
		}
		$where.=$dbp->getAccessPermission('g.branch_id');
		$where.=$dbp->getDegreePermission('g.degree');
		return $_db->fetchAll($sql.$where.$order_by);
	}
	
	public function getAllGroupStudentChangeGroupById($id){
		$db = $this->getAdapter();
		$sql = "
		SELECT 
			gsc.*,
			(SELECT g.degree FROM rms_group AS g WHERE g.id = gsc.from_group  LIMIT 1) AS from_degree 
		FROM 
			rms_group_student_change_group  AS gsc 
		WHERE gsc.id =".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('gsc.branch_id');
		$sql.=$dbp->getDegreePermission('gsc.degree');
		return $db->fetchRow($sql);
	}
	
	public function getCondition($data){
		$db=$this->getAdapter();
		$sql="SELECT gsc.* 
				FROM 
					rms_group_student_change_group AS gsc 
						WHERE gsc.from_group=".$data['from_group']." 
							AND gsc.to_group=".$data['groupId'];
		return $db->fetchRow($sql);
	}
	
	public function addGroupStudentChangeGroup($_data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{	
			$_dbFee = new Accounting_Model_DbTable_DbFee();
			$feeId = empty($_data['academic_year'])?0:$_data['academic_year'];
			$stopType = 0;
			$isCurrent=0;
			if($_data['change_type']==3){
				$stopType = $_data['change_type'];//ឆ្លងភូមិសិក្សា
				$isCurrent=1;
			}
			$con='';
			if ($stopType==1){
				$con = $this->getCondition($_data);
			}
			$changeGroupId = 0;
			if($con!=''){
				$identity = explode(',', $_data['identity']);
				$array_checkbox=explode(',', $con['array_checkbox']);
				$result = array_merge($array_checkbox,$identity);
				$final_array = implode(",", $result);
				$arra=array(
					'array_checkbox'	=>	$final_array,
						);
				$where = ' from_group='.$_data['from_group'].' and to_group='.$_data['groupId'];
				$this->update($arra, $where);
			}else{
				$_data['groupId'] = empty($_data['groupId'])?0:$_data['groupId'];
				$_arr= array(
					'user_id'		=>$this->getUserId(),
					'branch_id'	=>$_data['branch_id'],
					'degree'		=>$_data['degree'],
					'grade'			=>$_data['grade'],
					'from_group'	=>$_data['from_group'],
						
					'toDegree'		=>$_data['degreeId'],
					'toGrade'		=>$_data['gradeId'],
					'to_group'		=>$_data['groupId'],
					'change_type'	=>$_data['change_type'],
					'moving_date'	=>$_data['moving_date'],
					'note'			=>$_data['note'],
					'status'		=>1,
					'array_checkbox'=>$_data['identity'],
					//'fee_id'		=>$feeId,
					'academic_year'	=>$_data['study_year'],
				);
				if($_data['change_type']!=1 AND $_data['change_type']!=4){
					$_arr['fee_id'] = $feeId;
				}
				$this->_name = "rms_group_student_change_group";
				$id = $this->insert($_arr);
				$changeGroupId = $id;
			}
				
			$this->_name='rms_group_detail_student';
			$dbg = new Application_Model_DbTable_DbGlobal();
			
			$ids=explode(',', $_data['identity']);
			
			if(!empty($_data['groupId']) AND $_data['groupId']>0){
				$toGroupResult = $this->getGroupDetailInStudentChangeGroup($_data['groupId']);//new group
				$schoolOption=$toGroupResult['school_option'];
				$gradeId=$toGroupResult['grade'];
				$degreeId=$toGroupResult['degree'];
				$academicYear=$toGroupResult['academic_year'];
				$sesionId=$toGroupResult['session'];
			}else{
				$degreeId=$_data['degreeId'];
				$gradeId=$_data['gradeId'];
				$academicYear=$_data['study_year'];
				$sesionId='';
				$schoolOption='';//next to check more
			}
			
			if($_data['change_type']==1){ //ផ្លាស់ប្ដូរថ្នាក់ /worked
				/*
				$fromGroupDetail = $this->getGroupDetailInStudentChangeGroup($_data['from_group']);
				
				foreach ($ids as $k){
					if (!empty($_data['stu_id_'.$k])){
					
						$rsexist =$dbg->ifStudentinGroupReady($_data['stu_id_'.$k],$_data['groupId']);//check to group existing or not 
						
						if(empty($rsexist)){
							
							$stu=array(
								'group_id'		=> $_data['groupId'],
								'degree'		=> $degreeId,
								'grade'			=> $gradeId,
								'academic_year'	=> $academicYear,
								'session'		=> $sesionId,
								'old_group'		=> $_data['from_group'],
								'is_maingrade'	=> 1,//next to check old class in group detail student
								'is_setgroup'	=> 1,
								'is_current'	=> 1,
								'stop_type'		=> 0,
								'is_pass'		=> 0,
								'user_id'		=> $this->getUserId(),
								'status'		=> 1,
								'modify_date'	=> date("Y-m-d H:i:s")
							);
							$where="stu_id=".$_data['stu_id_'.$k]." AND group_id=".$_data['from_group']." AND grade=".$fromGroupDetail['grade']." AND itemType=1 ";
							$this->_name='rms_group_detail_student';
							$this->update($stu, $where);
							
							$this->moveCurrentScoreToOtherGroup($_data['from_group'], $_data['groupId'], $_data['stu_id_'.$k]);//move score from current group to new group
						}
					}
				}
				//new to group
				$this->_name = 'rms_group';
				$group=array(
						'is_use'	=>1,//using now
						'is_pass'	=>2,//studing
				);
				$where=" id=".$_data['groupId'];
				$this->update($group, $where);
				*/
				
				$this->_name='rms_group_detail_student';
				foreach ($ids as $k){
					if(!empty($_data['stu_id_'.$k])){
						$rsOldGroup =$dbg->ifStudentinGroupReady($_data['stu_id_'.$k],$_data['from_group']);//okay
						$is_maingrade = empty($rsOldGroup['is_maingrade'])?0:$rsOldGroup['is_maingrade'];
					
						$rsexist =$dbg->ifStudentinGroupReady($_data['stu_id_'.$k],$_data['from_group']);
						if(!empty($rsexist)){//old infor
							$stu=array(
									'is_pass'		=> 1,
									'is_current'	=> 0,
									'movedType'	=> $_data['change_type'],
							);
							$where=" stu_id=".$_data['stu_id_'.$k]." AND group_id=".$_data['from_group']." AND itemType=1";
							$this->_name='rms_group_detail_student';
							$this->update($stu, $where);
							
							$arr = array(
									'branch_id'	=>$_data['branch_id'],
									'studentId'		=>$_data['stu_id_'.$k],//$_data['stu_id_'.$k],
									'itemType'		=>1,
									'groupId'		=>$_data['groupId'],
									'oldGroup'		=>$_data['from_group'],
									'academicYear'	=>$_data['study_year'],
									'feeId'			=>$rsexist['feeId'],
									'oldFeeId'		=>$rsexist['feeId'],
									'schoolOption'  =>$schoolOption,
									'degree'		=>$degreeId,
									'grade'			=>$gradeId,
									'session'		=>$sesionId,
									'startDate'		=>$rsexist['startDate'],
									'endDate'		=>$rsexist['endDate'],
									'balance'		=>$rsexist['balance'],
									'discountType'	=>$rsexist['discount_type'],
									'discountAmount'=>$rsexist['discount_amount'],
									'user_id'		=>$this->getUserId(),
									'status'		=>1,
									'create_date'	=>date('Y-m-d H:i:s'),
									'modify_date'	=>date('Y-m-d H:i:s'),
									'old_group'		=>$_data['from_group'],
									'isSetGroup'	=>empty($_data['groupId'])?0:1,
									'stopType'		=>0,
									'isCurrent'		=>1,
									'isNewStudent'	=>0,
									'isMaingrade'	=>1,//not sure
									'entryFrom'	=>4,//not sure
									'remark'	=>'Change Group',
									'changeGroupId'	=>$changeGroupId,
							);
							 $dbg->AddItemToGroupDetailStudent($arr);
							 $this->moveCurrentScoreToOtherGroup($_data['from_group'], $_data['groupId'], $_data['stu_id_'.$k]);//move score from current group to new group
						}
					}
				}
				
				//new to group
				$this->_name = 'rms_group';
				$group=array(
						'is_use'	=>1,//using now
						'is_pass'	=>2,//studing
				);
				$where=" id=".$_data['groupId'];
				$this->update($group, $where);
				
				
			}elseif ( ($_data['change_type']==2) || ($_data['change_type']==6) ){
				//2=ឡើងថ្នាក់//done , 6=ត្រួតថ្នាក់
				
				$noteGroupDetail = "grade upgrade";
				if($_data['change_type']==6){
					$noteGroupDetail = "Repeat Class";
				}
				$this->_name='rms_group_detail_student';
				foreach ($ids as $k){
					if(!empty($_data['stu_id_'.$k])){
						$rsOldGroup =$dbg->ifStudentinGroupReady($_data['stu_id_'.$k],$_data['from_group']);//okay
						
						$is_maingrade = empty($rsOldGroup['is_maingrade'])?0:$rsOldGroup['is_maingrade'];
					
						$rsexist =$dbg->ifStudentinGroupReady($_data['stu_id_'.$k],$_data['from_group']);
						if(!empty($rsexist)){//old infor
							$stu=array(
									'is_pass'		=> 1,
									'is_current'	=> 0,
									'movedType'	=> $_data['change_type'],
							);
							$where=" stu_id=".$_data['stu_id_'.$k]." AND group_id=".$_data['from_group']." AND itemType=1";
							$this->_name='rms_group_detail_student';
							$this->update($stu, $where);
							
							$arr = array(
									'branch_id'	=>$_data['branch_id'],
									'studentId'		=>$_data['stu_id_'.$k],//$_data['stu_id_'.$k],
									'itemType'		=>1,
									'groupId'		=>$_data['groupId'],
									'oldGroup'		=>$_data['from_group'],
									'academicYear'	=>$_data['study_year'],
									'feeId'			=>$_data['academic_year'],//feeId
									'oldFeeId'		=>$rsexist['feeId'],//Old Fee Id
									'schoolOption'  =>$schoolOption,
									'degree'		=>$degreeId,
									'grade'			=>$gradeId,
									'session'		=>$sesionId,
									'startDate'		=>'',
									'endDate'		=>'',
									'balance'		=>0,
									'discountType'	=>'',
									'discountAmount'=>'',
									'user_id'		=>$this->getUserId(),
									'status'		=>1,
									'create_date'	=>date('Y-m-d H:i:s'),
									'modify_date'	=>date('Y-m-d H:i:s'),
									'old_group'		=>$_data['from_group'],
									'isSetGroup'	=>empty($_data['groupId'])?0:1,
									'stopType'		=>0,
									'isCurrent'		=>1,
									'isNewStudent'	=>0,
									'isMaingrade'	=>1,//not sure
									'entryFrom'	=>4,//not sure
									'remark'	=>$noteGroupDetail,//'grade upgrade',
									'changeGroupId'	=>$changeGroupId,
							);
							 $dbg->AddItemToGroupDetailStudent($arr);
						}
					}
				}
				
				if(!empty($_data['groupId']) AND $_data['groupId']>0){
					//new group
					$this->_name = 'rms_group';
					$group=array(
						'is_use'	=>1,
						'is_pass'	=>2,//studing
					);
					$where=" id=".$_data['groupId'];
					$this->update($group, $where);
				}
				
				$this->updateOldGrouptoFinish($_data['from_group']);//old group
				
			}elseif($_data['change_type']==3){//ឆ្លងភូមិសិក្សា
				$newStuId = '';
				foreach ($ids as $k){
					if(!empty($_data['stu_id_'.$k])){
						
						$rsexist =$dbg->ifStudentinGroupReady($_data['stu_id_'.$k],$_data['from_group']);
						if(!empty($rsexist)){//old infor
							$stu=array(
									'is_pass'		=> 1,
									'stop_type'		=> 3,//??????????????
									'is_current'	=> 1,//still show in front desk
									'movedType'	=> $_data['change_type'],
							);
							$where=" stu_id=".$_data['stu_id_'.$k]." AND group_id=".$_data['from_group']." AND itemType=1";
							$this->_name='rms_group_detail_student';
							$this->update($stu, $where);
						}
						
						$newStuId = $this->duplicateStudent($_data['stu_id_'.$k]);//duplicate student
						
						if(!empty($newStuId)){
							$dbGg = new Application_Model_DbTable_DbGlobal();
							$stuCode = $dbGg->getnewStudentId($_data['branch_id'],$degreeId);
							$_arrStu=[
								'branch_id'		=>$_data['branch_id'],
								'stu_code'		=>$stuCode,
							];
							$this->_name = 'rms_student';
							$where=" stu_id=".$newStuId;
							$this->update($_arrStu, $where);
							
							$_dbStCode = new Application_Model_DbTable_DbStudentCode();
							$arrUp =[
								"referenceType" =>3,
								"referenceId" 	=>$changeGroupId,
								"stuCode" 		=>$stuCode,
								"stuId" 		=>$newStuId,
								"branchId" 		=>$_data['branch_id'],
								"acadedmicYear"	=>$academicYear,
								"degree" 		=>$degreeId,
							];
							$_dbStCode->insertStudentCode($arrUp);
						}
						
							
				
						$arr = array(
								'branch_id'		=> $_data['branch_id'],
								'studentId'		=>$newStuId,
								'itemType'		=>1,
								'academicYear'	=>$academicYear,
								'groupId'		=>$_data['groupId'],
								'oldGroup'		=>$_data['from_group'],
								'feeId'			=>$_data['academic_year'],
								'oldFeeId'		=>$rsexist['feeId'],//Old Fee Id
								'degree'		=>$degreeId,
								'grade'			=>$gradeId,
								'session'		=>0,
								'startDate'		=>'',
								'endDate'		=>'',
								'balance'		=>0,
								'discountType'	=>'',
								'discountAmount'=>'',
								'user_id'		=>$this->getUserId(),
								'status'		=>1,
								'create_date'	=>date('Y-m-d H:i:s'),
								'modify_date'	=>date('Y-m-d H:i:s'),
								'old_group'		=>$_data['from_group'],
								'isSetGroup'	=>empty($_data['groupId'])?0:1,
								'stopType'		=>0,
								'isCurrent'		=>1,
								'isNewStudent'	=>1,
								'isMaingrade'	=>1,//not sure
								'entryFrom'	=>4,//not sure
								'remark'	=>'ឆ្លងភូមិសិក្សា',
								'changeGroupId'	=>$changeGroupId,
						);
						$dbg->AddItemToGroupDetailStudent($arr);
						
					}
				}
				
				if(!empty($_data['groupId'])){//To group
					$this->_name = 'rms_group';
					$group=array(
							'is_use'	=>1,
							'is_pass'	=>2,//studing
					);
					$where=" id=".$_data['groupId'];
					$this->update($group, $where);
				}
				
				$this->updateOldGrouptoFinish($_data['from_group'],3);
			}elseif($_data['change_type']==4){//បញ្ចប់ការសិក្សា
				$newStuId = '';
				$isCurrent=1; //still show in front desk
				if(!empty($_data['from_group'])){
					$dbGrp = new Foundation_Model_DbTable_DbGroup();
					$groupInfo = $dbGrp->getGroupById($_data['from_group']);
					if(!empty($groupInfo)){
						if(!empty($groupInfo["isExtraCourse"])){
							//បញ្ចប់ការសិក្សាសិស្ស ករណីសកម្រិតជា Extra Course (Summer Course....) តែបង្ហាញនៅប្រវត្តិសិក្សាសោសិស្ស
							$isCurrent=0;
						}
						
					}
				}
				
				foreach ($ids as $k){
					if(!empty($_data['stu_id_'.$k])){
						$rsexist =$dbg->ifStudentinGroupReady($_data['stu_id_'.$k],$_data['from_group']);
						if(!empty($rsexist)){//old infor
							$stu=array(
									'is_pass'		=> 1,
									'stop_type'		=> 4,//បញ្ចប់ការសិក្សា
									'is_current'	=> $isCurrent,
									'note'	=>'បញ្ចប់ការសិក្សា',
									'movedType'	=> $_data['change_type'],
									'changeGroupId'	=>$changeGroupId,
							);
							$where=" stu_id=".$_data['stu_id_'.$k]." AND group_id=".$_data['from_group']." AND itemType=1";
							$this->_name='rms_group_detail_student';
							$this->update($stu, $where);
						}
					}
				}
				if(!empty($_data['from_group'])){ // Current Class
				
					$sql=" SELECT gd_id FROM rms_group_detail_student WHERE group_id =".$_data['from_group'];
					$sql.=" AND stop_type=0";
					$sql.=" ORDER BY is_pass ASC LIMIT 1 ";
					$resultGroup = $this->getAdapter()->fetchOne($sql);
					// check student empty class to completed class
					if(empty($resultGroup)){
						$this->_name = 'rms_group';
						$group=array(
								'is_use'	=>1,
								'is_pass'	=>4,
						);
						$where=" id=".$_data['from_group'];
						$this->update($group, $where);
					}
				}
			}elseif($_data['change_type']==5){  // Update Student

				$fromGroupDetail = $this->getGroupDetailInStudentChangeGroup($_data['from_group']);
				
				foreach ($ids as $k){
					if (!empty($_data['stu_id_'.$k])){
					
						$rsexist =$dbg->ifStudentinGroupReady($_data['stu_id_'.$k],$_data['groupId']);//check to group existing or not 
						
						if(empty($rsexist)){
							
							$stu=array(
								'group_id'		=> $_data['groupId'],
								'degree'		=> $degreeId,
								'grade'			=> $gradeId,
								'academic_year'	=> $academicYear,
								'session'		=> $sesionId,
								'old_group'		=> $_data['from_group'],
								'is_maingrade'	=> 1,//next to check old class in group detail student
								'is_setgroup'	=> 1,
								'is_current'	=> 1,
								'stop_type'		=> 0,
								'is_pass'		=> 0,
								'user_id'		=> $this->getUserId(),
								'status'		=> 1,
								'note'			=>'ធ្វើបច្ចុប្បន្នព័ត៌មានសិស្ស',
								'modify_date'	=> date("Y-m-d H:i:s"),
								'changeGroupId'	=>$changeGroupId,
							);
							$where="stu_id=".$_data['stu_id_'.$k]." AND group_id=".$_data['from_group']." AND grade=".$fromGroupDetail['grade']." AND itemType=1 ";
							$this->_name='rms_group_detail_student';
							$this->update($stu, $where);
							
							$this->moveCurrentScoreToOtherGroup($_data['from_group'], $_data['groupId'], $_data['stu_id_'.$k]);//move score from current group to new group
						}
					}
				}
				
			}
			$_db->commit();
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
	}
	function getScorebyGroup($data){
		$db = $this->getAdapter();
		$sql="SELECT * FROM rms_score WHERE 1";
		if(!empty($data['groupId'])){
			$sql.=" AND group_id=".$data['groupId'];
		}
		if(!empty($data['exam_type'])){
			if($data['exam_type']==1){
				$sql.=" AND exam_type=".$data['exam_type'];
				if(!empty($data['for_month'])){
					$sql.=" AND for_month=".$data['for_month'];
				}
			}else{
				$sql.=" AND exam_type=".$data['exam_type'];
			}
		}
		if(!empty($data['for_semester'])){
			$sql.=" AND for_semester=".$data['for_semester'];
		}
		if(!empty($data['fetchRow'])){
			return $db->fetchRow($sql);
		}else{
			return $db->fetchAll($sql);
		}
	}
	function moveCurrentScoreToOtherGroup($FromGroupId,$toGroupId,$studentId){
		$db = $this->getAdapter();
		$param = array(
				'groupId'=>$FromGroupId//from group
				);
		$rsOldScores = $this->getScorebyGroup($param);
		
		if(!empty($rsOldScores)){
			foreach($rsOldScores as $rsScoreFrom){
				$arr = array(
						'fetchRow'=>1,
						'groupId'=>$toGroupId,
						'exam_type'=>$rsScoreFrom['exam_type'],
						'for_month'=>$rsScoreFrom['for_month'],
						'for_semester'=>$rsScoreFrom['for_semester'],
					);	
				$rsScoreToGroup = $this->getScorebyGroup($arr);
				
				
				if(!empty($rsScoreToGroup)){
					
					$scoreId = $rsScoreToGroup['id'];

					$sqlColumn = '
							gradingTotalId,
							student_id,
							subject_id,
							score,
							score_cut,
							amount_subject,
							`status`,
							note,
							subjectExam,
							orgScore ';
					$sql="
					INSERT INTO rms_score_detail (score_id,group_id,$sqlColumn)
						SELECT 
						'".$scoreId."' AS score_id ,
						'".$toGroupId."' AS group_id ,
						".$sqlColumn."
					FROM `rms_score_detail`
						WHERE score_id=".$rsScoreFrom['id']." AND student_id=".$studentId;
					$db->query($sql);
					
					//score monthly here 
					
					$sqlColumnMonthly='
					student_id,
					amount_subject,
					total_score,
					total_avg,
					totalMaxScore,
					remark,
					isRead,
					readDate,
					totalKhAvg,
					totalEnAvg,
					totalChAvg,
					OveralAvgKh,
					OveralAvgEng,
					OveralAvgCh,
					monthlySemesterAvg,
					overallAssessmentSemester
					';
					
					$sql="
						INSERT INTO rms_score_monthly (score_id,$sqlColumnMonthly,type)
							SELECT '".$scoreId."' AS score_id ,".$sqlColumnMonthly." , 3 AS type
						FROM `rms_score_monthly`
							WHERE score_id=".$rsScoreFrom['id']." AND student_id=".$studentId;
					$db->query($sql);
					
				}
			}
		}
	}
	function updateOldGrouptoFinish($groupId,$stopType=null){
		$sql=" SELECT is_pass FROM rms_group_detail_student WHERE group_id =".$groupId;
		if($stopType!=null){
			$sql.=" AND stop_type= ".$stopType;
		}else{
			$sql.=" AND stop_type=0";
		}
		$sql.=" ORDER BY is_pass ASC LIMIT 1 ";
		
		$resultOldGroup = $this->getAdapter()->fetchOne($sql);
		if(!empty($resultOldGroup)){
			$this->_name = 'rms_group';
			$group=array(
					'is_use'	=>1,
					'is_pass'	=>1,
			);
			$where=" id=".$groupId;
			$this->update($group, $where);
		}
	}
	function duplicateStudent($stu_id){
		$db = $this->getAdapter();
		//user_id
		//create_date
		$userId = $this->getUserId();
		$sql="INSERT INTO rms_student(
					branch_id,
					stu_code,
					stu_khname,
					last_name,
					stu_enname,
					sex,
					nationality,
					nation,
					dob,
					tel,
					primary_phone,
					pob,
					home_num,
					street_num,
					village_name,
					commune_name,
					district_name,
					province_id,
					studentType,
					familyId,
					
					lang_level,
					from_school,
					know_by,
					sponser,
					sponser_phone,
					status,
					remark,
					photo,
					customer_type,
					date_bacc,
					province_bacc,
					center_bacc,
					room_bacc,
					table_bacc,
					grade_bacc,
					score_bacc,
					certificate_bacc,
					calture,
					
					create_date,
					is_setstudentid,
					street,
					vill_id,
					comm_id,
					dis_id,
					pro_id,
					audioTitle,
					studentToken,
					is_vaccined,
					is_covidTested,
					dateUpdatedCovidFeature,
					setBy,
					crm_degree,
					crm_grade,
					crm_id,
					email,
					emergency_name,
					emergency_tel,
					
					is_studenttest,
					modify_date,
					
					password,
					relationship_to_student,
					serial,
					student_option,
					student_status,
					test_id,
					test_setting_id,
					test_type,
					academicYearEnroll,
					goHomeType,
					oldStudentId,
					user_id				
				)
					SELECT
					branch_id,
					stu_code,
					stu_khname,
					last_name,
					stu_enname,
					sex,
					nationality,
					nation,
					dob,
					tel,
					primary_phone,
					pob,
					home_num,
					street_num,
					village_name,
					commune_name,
					district_name,
					province_id,
					studentType,
					familyId,
					
					lang_level,
					from_school,
					know_by,
					sponser,
					sponser_phone,
					status,
					remark,
					photo,
					customer_type,
					date_bacc,
					province_bacc,
					center_bacc,
					room_bacc,
					table_bacc,
					grade_bacc,
					score_bacc,
					certificate_bacc,
					calture,
					
					create_date,
					is_setstudentid,
					street,
					vill_id,
					comm_id,
					dis_id,
					pro_id,
					audioTitle,
					studentToken,
					is_vaccined,
					is_covidTested,
					dateUpdatedCovidFeature,
					setBy,
					crm_degree,
					crm_grade,
					crm_id,
					email,
					emergency_name,
					emergency_tel,
					
					is_studenttest,
					modify_date,
					
					password,
					relationship_to_student,
					serial,
					student_option,
					student_status,
					test_id,
					test_setting_id,
					test_type,
					academicYearEnroll,
					goHomeType,
					$stu_id,
					$userId
		FROM rms_student WHERE stu_id=$stu_id LIMIT 1";
		 $db->query($sql);
		return $db->lastInsertId();
	}
	
	function getGroupDetailInStudentChangeGroup($group_id){
		$db = $this->getAdapter();
		$sql="SELECT * 
			FROM rms_group 
		WHERE rms_group.id=".$group_id;
		return $db->fetchRow($sql);
	}
	function getAllStudentOldGroup($from_group){
		$db = $this->getAdapter();
		$sql="select gd_id from 
		
			rms_group_detail_student 
			where itemType=1  AND rms_group_detail_student.group_id=".$from_group;
		return $db->fetchAll($sql);
	}
	
	public function updateStudentChangeGroup($_data){
		
		$_db= $this->getAdapter();
 		$_db->beginTransaction();
		try{	
			$_dbFee = new Accounting_Model_DbTable_DbFee();
			$feeId = empty($_data['academic_year'])?0:$_data['academic_year'];
			$stopType = 0;
			if ($_data['change_type']==3){
				$stopType = $_data['change_type'];//??????????????
			}
			
			if($_data['status']==1){
				$_arr=array(
						'user_id'		=>$this->getUserId(),
						'from_group'	=>$_data['from_group'],
						'degree'		=>$_data['degree'],
						'grade'			=>$_data['grade'],
						
						'to_group'		=>$_data['groupId'],
						'toDegree'		=>$_data['degree'],
						'toGrade'		=>$_data['grade'],
						
						'change_type'	=>$_data['change_type'],
						'moving_date'	=>$_data['moving_date'],
						'note'			=>$_data['note'],
						'array_checkbox'=>$_data['identity'],
						'status'		=>$_data['status'],
						'fee_id'		=>$feeId,
						'academic_year'	=>$_data['study_year'],
						
				);
				$where=" id = ".$_data['id'];
				$this->update($_arr, $where);
				
				$this->_name='rms_group_detail_student';
				$StudentOldGroup = $this->getAllStudentOldGroup($_data['from_group']);
				if(!empty($StudentOldGroup)){
					foreach($StudentOldGroup as $result){
						$arra=array(
								'stop_type'		=>0,
								'is_pass'		=>0,
								'is_current'	=>1,
								);
						$where=" gd_id=".$result['gd_id'];
						$this->_name='rms_group_detail_student';
						$this->update($arra, $where);
						
						$this->_name = 'rms_student_fee_history';
						$whereStudyFee = 'student_id = '.$result['stu_id']." AND is_current=1 AND academic_year=".$result['academic_year'];
						$this->delete($whereStudyFee);
					}
				}
				$this->_name='rms_group_detail_student';
				$where = "old_group = ".$_data['from_group']." and group_id = ".$_data['old_to_group'];
				if ($stopType==3){
					$where = "old_group = ".$_data['from_group'];
				}
				$this->delete($where);
				
				$group_detail = $this->getGroupDetailInStudentChangeGroup($_data['groupId']);
				if(empty($_data['identity'])){
					$_data['identity'] = $_data['old_array_checkbox'];
				}
				
				
				if(!empty($_data['identity'])){
					$idsss=explode(',', $_data['identity']);
					
					foreach ($idsss as $k){
						if (!empty($_data['stu_id_'.$k])){
							$stu=array(
									'stop_type'		=>$stopType,
									'is_pass'		=>1,
									'is_current'	=>0,
									'modify_date'	=> date("Y-m-d H:i:s")
							);
							$where=" stu_id=".$_data['stu_id_'.$k]." AND group_id=".$_data['from_group'];
							$this->_name='rms_group_detail_student';
							$this->update($stu, $where);
							
							if ($stopType==3){//??????????????
								$this->_name = 'rms_student_fee_history';
								$data_gro = array(
									'is_current'=> 0,
								);
								
								$where = 'student_id = '.$_data['stu_id_'.$k]." AND is_current=1";
								$this->update($data_gro, $where);
								
								$arr = array(
										'user_id'			=>$this->getUserId(),
										'branch_id'			=>$_data['branch_id'],
										'student_id'		=>$_data['stu_id_'.$k],
										'status'			=>1,
										'academic_year'		=>$academicYear,
										'fee_id'			=>$feeId,
										'is_current'		=>1,
										'create_date'		=>date("Y-m-d H:i:s"),
										'modify_date'		=>date("Y-m-d H:i:s"),
								);
								$this->_name='rms_student_fee_history';
								$feeHistortyId = $this->insert($arr);
								
								$arr=array(
										'stu_id'		=>$_data['stu_id_'.$k],
										'group_id'		=>0,
										'session'		=>0,
										'degree'		=>$_data['degree'],
										'grade'			=>$_data['grade'],
										'academic_year'	=>$academicYear,
										'user_id'		=>$this->getUserId(),
										'status'		=>1,
										'create_date'	=>date('Y-m-d H:i:s'),
										'modify_date'	=>date('Y-m-d H:i:s'),
										'old_group'		=>$_data['from_group'],
										'is_setgroup'	=>1,
										'is_current'	=>1,
										'is_maingrade'	=>1,
								);
								$this->_name='rms_group_detail_student';
								$this->insert($arr);
								
							}
						}
					}
				}
				
				if ($stopType!=3){
					$group_detail = $this->getGroupDetailInStudentChangeGroup($_data['groupId']);
					$dbg = new Application_Model_DbTable_DbGlobal();
					$this->_name='rms_group_detail_student';
					$ids=explode(',', $_data['identity']);
					foreach ($ids as $i){
						if (!empty($_data['stu_id_'.$i])){
							$rsOldGroup =$dbg->ifStudentinGroupReady($_data['stu_id_'.$i],$_data['from_group']);
							$is_maingrade = empty($rsOldGroup['is_maingrade'])?0:$rsOldGroup['is_maingrade'];
								
							$rsexist =$dbg->ifStudentinGroupReady($_data['stu_id_'.$i],$_data['groupId']);
							if(empty($rsexist)){
								$arr=array(
										'stu_id'		=>$_data['stu_id_'.$i],
										'group_id'		=>$_data['groupId'],
										'session'		=>$group_detail['session'],
										'degree'		=>$group_detail['degree'],
										'grade'			=>$group_detail['grade'],
										'academic_year'	=>$group_detail['academic_year'],
				
										'user_id'		=>$this->getUserId(),
										'status'		=>1,
										'create_date'		=>date('Y-m-d H:i:s'),
										'modify_date'		=>date('Y-m-d H:i:s'),
										'type'			=>1,
										'old_group'	=>$_data['from_group'],
										'is_setgroup'		=>1,
										'is_current'		=>1,
										'is_maingrade'		=>$is_maingrade,
								);
								if($_data['change_type']==2){
									$array['is_newstudent']=0;
								}
								$this->insert($arr);
							}
						}
					}
					$this->_name = 'rms_group';
					$group=array(
							'is_use'	=>1,
							'is_pass'	=>2,
					);
					$where=" id=".$_data['groupId'];
					$this->update($group, $where);
				}
				
				$this->_name = 'rms_group';
				$group=array(
						'is_use'	=>0,
						'is_pass'	=>2,
					);
				$where=" id=".$_data['old_to_group'];
				$this->update($group, $where);
	
				$ident = explode(',', $_data['identity']);
				$selected_student = count($ident);
				$all_student = $_data['all_student'];
				if($all_student == $selected_student){
					$from_group=array(
						'is_pass' => 1,
					);
				}else{
					$from_group=array(
						'is_pass' => 0,
					);
				}
				$this->_name = 'rms_group';
				$where=" id=".$_data['from_group'];
				$this->update($from_group, $where);
				
				
				
			}else{  //////// status == 0 => deactive    ===> so update all student to old info
				$_arr=array(
						'user_id'=>$this->getUserId(),
						'status'=>$_data['status']
				);
				$where=" id = ".$id;
				$this->update($_arr, $where);
				
			//// Update student to study in old_group in group_detail_student  ///
				$this->_name='rms_group_detail_student';
				$StudentOldGroup = $this->getAllStudentOldGroup($_data['from_group']);
				if(!empty($StudentOldGroup)){
					foreach($StudentOldGroup as $result){
						$arra=array(
								'stop_type'		=>0,
								'is_pass'		=>0,
								'is_current'	=>1,
								'modify_date'	=> date("Y-m-d H:i:s")
						);
						$where=" gd_id=".$result['gd_id'];
						$this->_name='rms_group_detail_student';
						$this->update($arra, $where);
						
						$this->_name = 'rms_student_fee_history';
						$whereStudyFee = 'student_id = '.$result['stu_id']." AND is_current=1 AND academic_year=".$result['academic_year'];
						$this->delete($whereStudyFee);
						
						
						$lasFeeHis=  $this->getLastFeeStudentHistory($result['stu_id']);
						if (!empty($lasFeeHis)){
							$this->_name = 'rms_student_fee_history';
							$data_gro = array(
									'is_current'=> 1,
							);
							$where = 'id = '.$lasFeeHis['id']." AND is_current=0 ";
							$this->update($data_gro, $where);
						}
					}
				}
			///// delete record student that added to new group ///////////////	
				$this->_name='rms_group_detail_student';
				$where = "old_group = ".$_data['from_group']." and group_id = ".$_data['old_to_group'];
				$this->delete($where);

			/////// get group_detail_info to update student info back to old group /////	
				$group_detail = $this->getGroupDetailInStudentChangeGroup($_data['from_group']);
				$this->_name = 'rms_group';
				$group=array(
						'is_use'	=>0,
						'is_pass'	=>2,
				);
				$where=" id=".$_data['old_to_group'];
				$this->update($group, $where);
				
				$from_group=array(
					'is_pass' => 0,
				);
				$this->_name = 'rms_group';
				$where=" id=".$_data['from_group'];
				$this->update($from_group, $where);
			}
			return $_db->commit();
			
		}catch(Exception $e){
			$_db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getLastFeeStudentHistory($student_id){
		$db=$this->getAdapter();
		$sql="SELECT sh.* FROM rms_student_fee_history AS sh WHERE sh.student_id=$student_id  ORDER BY sh.id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getAllStudentFromGroupChangeGroup($data){
		$db=$this->getAdapter();
		$sql="SELECT 
				gds.stu_id as stu_id,
				st.stu_enname,
				st.last_name,
				st.stu_khname,
				st.stu_code,
			 	(SELECT name_en FROM rms_view WHERE rms_view.type=2 AND rms_view.key_code=st.sex LIMIT 1) as sex
			FROM rms_group_detail_student as gds,
				rms_student as st 
			WHERE 
				gds.itemType=1 
				AND st.customer_type=1 
				AND gds.stop_type = 0 
				AND gds.stu_id=st.stu_id 
				AND gds.is_pass=0 ";
		
		if($data['group_id']){
			$sql.=" AND gds.group_id=".$data['group_id'];
		}

		if(!empty($data['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($data['adv_search']));
			$s_where[]=" REPLACE(st.stu_code,' ','')   	LIKE '%{$s_search}%'";
			$s_where[]=" st.stu_khname 	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(st.stu_enname,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(st.last_name,' ','')  	LIKE '%{$s_search}%'";
			$s_where[]=" CONCAT(COALESCE(st.last_name,''),' ',COALESCE(st.stu_enname,'')) LIKE '%{$s_search}%'";
			$sql.=' AND ( '.implode(' OR ',$s_where).')';
		}
		
		$studentName="CONCAT(COALESCE(st.last_name,''),' ',COALESCE(st.stu_enname,''))";
		
		$order=" ORDER BY st.stu_khname ASC ";
		if(!empty($data['sortStundent'])){
			if($data['sortStundent']==1){
				$order=" ORDER BY st.stu_code ASC ";
			}else if($data['sortStundent']==2){
				$order=" ORDER BY st.stu_khname ASC ";
			}else if($data['sortStundent']==3){
				$order=" ORDER BY $studentName ASC ";
			}
		}
		return $db->fetchAll($sql.$order);
	}
	
	function getAllStudentFromGroupUpdate($from_group){
		$db=$this->getAdapter();
		$sql="select 
					gds.stu_id as stu_id,
					st.stu_enname,
					st.last_name,
					st.stu_khname,
					st.stu_code,
					(select name_en from rms_view where rms_view.type=2 and rms_view.key_code=st.sex) as sex
				from 
					rms_group_detail_student as gds,
					rms_student as st 
				where 
					gds.itemType=1 
					AND gds.stu_id=st.stu_id 
					and gds.group_id=$from_group
			";
		return $db->fetchAll($sql);
	}
	
	function getGroupStudentChangeGroup1ById($id,$type){
		$db = new Application_Model_DbTable_DbGlobal();
		return $db->getStudentGroupInfoById($id);
	}
	
	function getAllYears(){
		$db = $this->getAdapter();
		$sql = "SELECT id,CONCAT((SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=rms_tuitionfee.academic_year LIMIT 1),'(',generation,')') AS years FROM rms_tuitionfee WHERE `status`=1 ";
		$order=' ORDER BY id DESC';
		return $db->fetchAll($sql.$order);
	}
	
	function selectStudentPass($id){
		$db = $this->getAdapter();
		$sql = "SELECT stu_id  FROM rms_group_detail_student as gds WHERE 
			gds.itemType=1 
			AND gds.old_group=$id";
		return $db->fetchAll($sql);
	}

	
	
	
	public function getGroupNewAll(){
		$db=$this->getAdapter();
		$sql="SELECT id,group_code As name FROM `rms_group` WHERE STATUS = 1 AND is_pass IN (0,2) AND group_code!=''";
		return $db->fetchAll($sql);
	}
	
	public function getChangeType(){
		$_db  = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$label = "name_kh";
		}else{ // English
			$label = "name_en";
		}
		$db=$this->getAdapter();
		$sql="SELECT key_code as id, $label as name from rms_view where type=17 and status=1 ";
		return $db->fetchAll($sql);
	}
	
	public function getCheckingPmtInfo($data=[]){
		
		
		$changeGroupId = empty($data["changeGroupId"]) ? 0 : $data["changeGroupId"];
		if(!empty($changeGroupId)){
			$db= $this->getAdapter();
			$sql="
				SELECT 
					gds.`gd_id`
					,gds.`stu_id`
					,gds.`group_id`
					,gds.`degree`
					,gds.`grade`
					,gds.`old_group`
					,gds.`is_current`
					,gds.`is_maingrade`
					,COALESCE(s.`oldStudentId`,0) oldStudentId
					,COALESCE((SELECT sp.id FROM `rms_student_payment` AS sp WHERE sp.`student_id` = gds.`stu_id` LIMIT 1 ),0) AS paymentId
				FROM `rms_group_detail_student` AS gds 
					JOIN `rms_student` AS s ON s.`stu_id` = gds.`stu_id` 
				WHERE 
				gds.`itemType`=1
				AND gds.`changeGroupId` = $changeGroupId
			";
			$sql.=" ORDER BY COALESCE((SELECT sp.id FROM `rms_student_payment` AS sp WHERE sp.`student_id` = gds.`stu_id` LIMIT 1 ),0) DESC ";
			$sql.=" LIMIT 1 ";
			return $db->fetchRow($sql);
		}else{
			return [];
		}
	}
	
	public function getStudentStudyChangeGroup($data=[]){
		
		
		$changeGroupId = empty($data["changeGroupId"]) ? 0 : $data["changeGroupId"];
		if(!empty($changeGroupId)){
			$db= $this->getAdapter();
			$sql="
				SELECT 
					gds.`gd_id`
					,gds.`branch_id`
					,gds.`stu_id`
					,gds.`group_id`
					,gds.`degree`
					,gds.`grade`
					,gds.`old_group`
					,gds.`is_current`
					,gds.`is_maingrade`
					,COALESCE(s.`oldStudentId`,0) oldStudentId
				FROM `rms_group_detail_student` AS gds 
					JOIN `rms_student` AS s ON s.`stu_id` = gds.`stu_id` 
				WHERE 
				gds.`itemType`=1
				AND gds.`changeGroupId` = $changeGroupId
			";
			$sql.=" ORDER BY gds.`gd_id` ASC ";
			return $db->fetchAll($sql);
		}else{
			return [];
		}
	}
	public function rollBackGroupStudentChangeGroup($data=[]){
		
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			
			$id = empty($data["changeGroupId"]) ? 0 : $data["changeGroupId"];
			$changeGroupInfo = $this->getAllGroupStudentChangeGroupById($id);
			if(!empty($changeGroupInfo)){
				$changeType = $changeGroupInfo["change_type"];
				if($changeType==1){
					$allStudent = $this->getStudentStudyChangeGroup($data);
					if(!empty($allStudent)){
						foreach($allStudent AS $rowSt){
							$stuArr=array(
									'is_pass'		=> 0,
									'stop_type'		=> 0,
									'is_current'	=> 1,
									'note'			=>'RollBack From ផ្លាស់ប្ដូរថ្នាក់',
									'movedType'		=> 0,
							);
							$whereStu=" itemType=1 ";
							$whereStu.=" AND is_current=0 ";
							$whereStu.=" AND movedType=".$changeType;
							$whereStu.=" AND stu_id=".$rowSt['stu_id'];
							$whereStu.=" AND group_id=".$changeGroupInfo["from_group"];
							
							$this->_name='rms_group_detail_student';
							$this->update($stuArr, $whereStu);
							
							$arrScore = [
								"groupId" =>$rowSt['group_id'],
								"studentId" =>$rowSt['stu_id'],
							];
							$this->reversMoveCurrentScoreGroup($arrScore);						
						}
						
						$this->_name = 'rms_group_detail_student';
						$whereDelete = " changeGroupId = ".$id;
						$whereDelete.= " AND group_id =  ".$changeGroupInfo["to_group"];
						$this->delete($whereDelete);
						
						if(!empty($changeGroupInfo["from_group"])){
							$this->_name = 'rms_group';
							$group=array(
									'is_use'	=>1,
									'is_pass'	=>2,//កំពុងសិក្សា
							);
							$where=" id=".$changeGroupInfo["from_group"];
							$this->update($group, $where);
						}
					}
					
				}else if( ($changeType==2) OR ($changeType==6) ){
					$noteRollBack = "RollBack From Grade Upgrade";
					if($changeType==6){
						$noteRollBack = "RollBack From Repeat Class";
					}
					$allStudent = $this->getStudentStudyChangeGroup($data);
					if(!empty($allStudent)){
						foreach($allStudent AS $rowSt){
							$stuArr=array(
									'is_pass'		=> 0,
									'stop_type'		=> 0,
									'is_current'	=> 1,
									'note'			=>$noteRollBack,//'RollBack From Grade Upgrade',
									'movedType'		=> 0,
							);
							$whereStu=" itemType=1 ";
							$whereStu.=" AND is_current=0 ";
							$whereStu.=" AND movedType=".$changeType;
							$whereStu.=" AND stu_id=".$rowSt['stu_id'];
							$whereStu.=" AND group_id=".$changeGroupInfo["from_group"];
							
							$this->_name='rms_group_detail_student';
							$this->update($stuArr, $whereStu);
						}
						
						$this->_name = 'rms_group_detail_student';
						$whereDelete = " changeGroupId = ".$id;
						$whereDelete.= " AND group_id =  ".$changeGroupInfo["to_group"];
						$this->delete($whereDelete);
						
						if(!empty($changeGroupInfo["from_group"])){
							$this->_name = 'rms_group';
							$group=array(
									'is_use'	=>1,
									'is_pass'	=>2,//កំពុងសិក្សា
							);
							$where=" id=".$changeGroupInfo["from_group"];
							$this->update($group, $where);
						}
					}
				}else if($changeType==3){
					$allStudent = $this->getStudentStudyChangeGroup($data);
					if(!empty($allStudent)){
						foreach($allStudent AS $rowSt){
							$stuArr=array(
									'is_pass'		=> 0,
									'stop_type'		=> 0,
									'is_current'	=> 1,
									'note'			=>'RollBack From ឆ្លងភូមិសិក្សា',
									'movedType'		=> 0,
							);
							$whereStu=" itemType=1 ";
							$whereStu.=" AND is_current=1 ";
							$whereStu.=" AND movedType=".$changeType;
							$whereStu.=" AND stu_id=".$rowSt['oldStudentId'];
							$whereStu.=" AND group_id=".$changeGroupInfo["from_group"];
							
							$this->_name='rms_group_detail_student';
							$this->update($stuArr, $whereStu);
							
							if(!empty($rowSt['oldStudentId'])){
								$this->_name = 'rms_student';
								$whereDeleteSt = " oldStudentId = " . $rowSt['oldStudentId'];
								$this->delete($whereDeleteSt);
							}
							
							$_dbStCode = new Application_Model_DbTable_DbStudentCode();
							$arrUp =[
								"referenceType" =>3,
								"referenceId" 	=>$id,
								"stuId" 		=>$rowSt["stu_id"],
								"branchId" 		=>$rowSt['branch_id'],
							];
							$_dbStCode->reverseStudCodeOfStudent($arrUp);
						}
						$this->_name = 'rms_group_detail_student';
						$whereDelete = " changeGroupId = ".$id;
						$this->delete($whereDelete);
						
						if(!empty($changeGroupInfo["from_group"])){
							$this->_name = 'rms_group';
							$group=array(
									'is_use'	=>1,
									'is_pass'	=>2,//កំពុងសិក្សា
							);
							$where=" id=".$changeGroupInfo["from_group"];
							$this->update($group, $where);
						}
					}				
					
				}else if($changeType==4){
					$allStudent = $this->getStudentStudyChangeGroup($data);
					if(!empty($allStudent)) {
						foreach($allStudent AS $rowSt){
							$stu=array(
									'is_pass'		=> 0,
									'stop_type'		=> 0,
									'is_current'	=> 1,
									'note'			=>'RollBack From បញ្ចប់ការសិក្សា',
									'movedType'		=> 0,
							);
							$where="stu_id=".$rowSt['stu_id']." AND gd_id=".$rowSt['gd_id']." AND movedType=$changeType ";
							$this->_name='rms_group_detail_student';
							$this->update($stu, $where);
						}
						if(!empty($changeGroupInfo["from_group"])){
							$this->_name = 'rms_group';
							$group=array(
									'is_use'	=>1,
									'is_pass'	=>2,//កំពុងសិក្សា
							);
							$where=" id=".$changeGroupInfo["from_group"];
							$this->update($group, $where);
						}
					}
					
					
				}else if($changeType==5){
					$allStudent = $this->getStudentStudyChangeGroup($data);
					if(!empty($allStudent)) {
						foreach($allStudent AS $rowSt){
							$stu=array(
								'group_id'		=> $rowSt['old_group'],
								'degree'		=> $rowSt['degree'],
								'grade'			=> $rowSt['grade'],
								'old_group'		=> 0,
								
								'is_setgroup'	=> empty($rowSt['old_group']) ? 0 : 1,
								'is_current'	=> 1,
								'stop_type'		=> 0,
								'is_pass'		=> 0,
								'user_id'		=> $this->getUserId(),
								'status'		=> 1,
								'note'			=>'RollBack From ធ្វើបច្ចុប្បន្នព័ត៌មានសិស្ស',
								'modify_date'	=> date("Y-m-d H:i:s"),
								'movedType'		=> 0,
							);
							$where="stu_id=".$rowSt['stu_id']." AND group_id=".$rowSt['group_id']." AND gd_id=".$rowSt['gd_id'];
							$this->_name='rms_group_detail_student';
							$this->update($stu, $where);
							
							$arrScore = [
								"groupId" =>$rowSt['group_id'],
								"studentId" =>$rowSt['stu_id'],
							];
							$this->reversMoveCurrentScoreGroup($arrScore);
						}
					}
				}
				
				$changeArr=array(
						'status' => 0,
				);
				$whereGrCh=" id= ".$id;
				$this->_name='rms_group_student_change_group';
				$this->update($changeArr, $whereGrCh);
				
			}
			
			$_db->commit();
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$_db->rollBack();
			return false;
		}
		
		
	}
	
	function reversMoveCurrentScoreGroup($data=[]){
		$db = $this->getAdapter();
		
		$groupId = $data["groupId"];
		$param = array(
				'groupId'=>$groupId,
				);
		$rsScores = $this->getScorebyGroup($param);
		if(!empty($rsScores)){
			
			$studentId = $data["studentId"];
			$this->_name = 'rms_score_detail';
			$wDeleteScrDetail = " score_id = " . $rowSt['id'];
			$wDeleteScrDetail = " AND group_id = " . $groupId;
			$wDeleteScrDetail = " AND student_id = " . $studentId;
			$this->delete($wDeleteScrDetail);
			
			
			$this->_name = 'rms_score_monthly';
			$whereDeleteScore = " score_id = " . $rowSt['id'];
			$whereDeleteScore = " AND student_id = " . $studentId;
			$this->delete($whereDeleteScore);
					
		}
	}
	
}

