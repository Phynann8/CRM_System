<?php

class Issue_Model_DbTable_DbSchedule extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_group';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllScheduleGroup($search){
    	$db = $this->getAdapter();
    	try{
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$branch = $dbp->getBranchDisplay();
    		$sql="
    		SELECT 
				gsch.id
				,(SELECT b.$branch FROM `rms_branch` AS b WHERE  b.br_id=gsch.branch_id LIMIT 1) AS branch_name
				,(SELECT CONCAT(ac.fromYear,'-',ac.toYear) FROM `rms_academicyear` AS ac WHERE ac.id = gsch.academic_year LIMIT 1) AS years
				,g.group_code AS group_code
				,(SELECT st.title FROM `rms_schedulesetting` AS st WHERE st.id = gsch.schedule_setting LIMIT 1) AS sch_setting
				,DATE_FORMAT(gsch.create_date,'%d-%m-%Y') AS create_date
				,(SELECT first_name FROM rms_users WHERE rms_users.id = gsch.user_id LIMIT 1) AS user_name
				,gsch.status as scheduleStatus
			
    		";
    		$sql.=$dbp->caseStatusShowImage("gsch.status");
			$sql.=" 
				FROM 
					`rms_group_schedule` AS gsch 
					JOIN rms_group AS g ON g.id =gsch.group_id
				WHERE 1 ";
    		$where =' ';
			$from_date =(empty($search['start_date']))? '1': "gsch.create_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "gsch.create_date <= '".$search['end_date']." 23:59:59'";
			$where.= " AND ".$from_date." AND ".$to_date;
		
    		$order =  ' ORDER BY `gsch`.`id` DESC ' ;
    		if(!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['adv_search']));
    			$s_where[] = " gsch.`note` LIKE '%{$s_search}%'";
    			$s_where[] = " g.group_code LIKE '%{$s_search}%'";
    			$s_where[] = " (SELECT branch_nameen FROM `rms_branch` WHERE br_id=gsch.branch_id LIMIT 1) LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['branch_id'])){
    			$where.=' AND gsch.branch_id='.$search['branch_id'];
    		}
    		if(!empty($search['academic_year'])){
    			$where.=' AND gsch.academic_year='.$search['academic_year'];
    		}
    		if(!empty($search['group'])){
    			$where.=' AND gsch.group_id='.$search['group'];
    		}
			if($search['status']>-1 ){
				$where.= " AND gsch.status = ".$db->quote($search['status']);
			}
    		$where.=$dbp->getAccessPermission('gsch.branch_id');
    		$where.=$dbp->getDegreePermission('g.degree');
    		return $db->fetchAll($sql.$where.$order);
    		
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function addScheduleGroup($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		
    		$model = new Application_Model_DbTable_DbGlobal();
    		$allDay = $model->getAllDay(1);
    		
    		$_arr = array(
    				'branch_id'=>$_data['branch_id'],
    				'academic_year'=>$_data['academic_year'],
    				'group_id'=>$_data['group_code'],
    				'schedule_setting'=>$_data['schedule_setting'],
    				'note'=>$_data['note'],
    				'status'=>1,
    				'create_date'=>date("Y-m-d H:i:s"),
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'user_id'=>$this->getUserId(),
    		);
    		$this->_name='rms_group_schedule';
    		
    		$id = $this->insert($_arr);
    		
    		if(!empty($_data['identity1'])){
    			$ids = explode(',', $_data['identity1']);
    			foreach ($ids as $i){
    				$arr = array(
    						'main_schedule_id'		=>$id,
    						'branch_id'		=>$_data['branch_id'],
    						'group_id'		=>$_data['group_code'],
    						'year_id'		=>$_data['academic_year'],
    						
    						'schedule_setting_id'	=>$_data['settingdetail_'.$i],
    						'from_hour'		=>$_data['from_hour_'.$i],
    						'to_hour'		=>$_data['to_hour_'.$i],
    						
    						'create_date'	=>date("Y-m-d H:i:s"),
    						'status'		=>1,
    						'user_id'		=>$this->getUserId(),
    						
    				);
    				if (!empty($allDay)) foreach ($allDay as $day){
    					$arr['study_type'] =  $_data['type_'.$i."_".$day['id']];
    					if ( $_data['type_'.$i."_".$day['id']] ==1){
		    				$arr['day_id'] = $day['id'];
		    				$arr['subject_id'] =  $_data['subject_'.$i."_".$day['id']];
		    				$arr['techer_id'] =  $_data['teacher_'.$i."_".$day['id']];
    					}else{
    						$arr['day_id'] = $day['id'];
    						$arr['subject_id'] =  "";
    						$arr['techer_id'] =  "";
    					}
	    				$this->_name='rms_group_reschedule';
	    				if($_data['type_'.$i."_".$day['id']]==1){ // 1=study , 2=no study
	    					$this->insert($arr);
	    				}
    				}
    			}
    		}
    		$db->commit();
	   	}catch (Exception $e){
	   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		$db->rollBack();
	   	}
    }
    function updateScheduleGroup($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    
    		$model = new Application_Model_DbTable_DbGlobal();
    		$allDay = $model->getAllDay(1);
			$status = empty($_data['status'])?0:1;
    		$_arr = array(
    				'branch_id'			=>$_data['branch_id'],
    				'academic_year'		=>$_data['academic_year'],
    				'group_id'			=>$_data['group_code'],
    				'schedule_setting'	=>$_data['schedule_setting'],
    				'note'				=>$_data['note'],
    				'status'			=>$status,
    				'modify_date'		=>date("Y-m-d H:i:s"),
    				'user_id'			=>$this->getUserId(),
    		);
    		$this->_name='rms_group_schedule';
    		$id = $_data['id'];
    		$where=" id = ".$id;
    		$this->update($_arr, $where);
    
    		$this->_name="rms_group_reschedule";
    		$where = " main_schedule_id=$id ";
    		$this->delete($where);
    		
    		if(!empty($_data['identity1'])){
    			$ids = explode(',', $_data['identity1']);
    			foreach ($ids as $i){
    				$arr = array(
    						'main_schedule_id'		=>$id,
    						'branch_id'		=>$_data['branch_id'],
    						'group_id'		=>$_data['group_code'],
    						'year_id'		=>$_data['academic_year'],
    
    						'schedule_setting_id'	=>$_data['settingdetail_'.$i],
    						'from_hour'		=>$_data['from_hour_'.$i],
    						'to_hour'		=>$_data['to_hour_'.$i],
    
    						'create_date'	=>date("Y-m-d H:i:s"),
    						'status'		=>1,
    						'user_id'		=>$this->getUserId(),
    
    				);
    				if (!empty($allDay)) foreach ($allDay as $day){
    					$arr['study_type'] =  $_data['type_'.$i."_".$day['id']];
    					if ( $_data['type_'.$i."_".$day['id']] ==1){
    						$arr['day_id'] = $day['id'];
    						$arr['subject_id'] =  $_data['subject_'.$i."_".$day['id']];
    						$arr['techer_id'] =  $_data['teacher_'.$i."_".$day['id']];
    					}else{
    						$arr['day_id'] = $day['id'];
    						$arr['subject_id'] =  "";
    						$arr['techer_id'] =  "";
    					}
    					$this->_name='rms_group_reschedule';
    					
    					if($_data['type_'.$i."_".$day['id']]==1){ // 1=study , 2=no study
	    					$this->insert($arr);
	    				}
    				}
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function getScheduleGroupById($id){
    	$db = $this->getAdapter();
    	$sql=" 
			SELECT 
				gsch.* 
			FROM  
					`rms_group_schedule` AS gsch 
					JOIN rms_group AS g ON g.id =gsch.group_id
			WHERE gsch.id= $id ";
			
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('gsch.branch_id');
		$sql.=$dbp->getDegreePermission('g.degree');
		$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
    
    function checking($data){
    	
    	$group_id = empty($data['group_id'])?0:$data['group_id'];
    	$db = $this->getAdapter();
    	$sql="SELECT gs.id FROM rms_group_schedule AS gs
			WHERE gs.group_id=$group_id ";
    	if (!empty($data['id'])){
    		$sql.=" AND gs.id != ".$data['id'];
    	}
    	$row = $db->fetchOne($sql);
    	if (!empty($row)){
    		return 1;
    	}
    	return 0;
    }
    function getAllTeacher(){
    	$db = $this->getAdapter();
    	$sql = "SELECT id,teacher_name_kh as name FROM rms_teacher WHERE status=1 and teacher_name_kh!='' ";
    	return $db->fetchAll($sql);
    }
	
	function getAllScheduleSubjectForPublic($data){
    	$db = $this->getAdapter();
		
		$schduleId = empty($data['selectId']) ? "0" : $data['selectId']; 
    	$sql = "
			SELECT 
				sch.`id`
				,sch.`group_id` AS groupId
				,sch.`main_schedule_id` AS mainSchId
				,sch.`subject_id` AS subjectId
				,sch.`techer_id` AS teacherId

			FROM `rms_group_reschedule` AS sch
			WHERE  FIND_IN_SET(sch.`main_schedule_id`,'".$schduleId."')
			GROUP BY sch.`group_id`,sch.`subject_id`,sch.`techer_id`
			ORDER BY sch.`main_schedule_id`,sch.`id`

		";
    	return $db->fetchAll($sql);
    }
	function checkingSubjectInGroupDetail($data){
		$db=$this->getAdapter();
		
		$groupId = $data['groupId'];
		$subjectId = $data['subjectId'];
		$sql="
			SELECT 
				sd.id
			FROM `rms_group_subject_detail` AS sd 
			WHERE sd.group_id = $groupId AND sd.subject_id = $subjectId
		";
		return $db->fetchOne($sql);
	}
	function publicSchedule($data){
		if(!empty($data['selectId'])){
			$ids = explode(',', $data['selectId']);
			foreach ($ids as $schId) {
				$row = $this->getScheduleGroupById($schId);
				if(!empty($row)){
					$_arr = array(
							'status'			=>0,
							'modify_date'		=>date("Y-m-d H:i:s"),
							'user_id'			=>$this->getUserId(),
					);
					$this->_name='rms_group_schedule';
					$where=" group_id = ".$row["group_id"];
					$this->update($_arr, $where);
				}
				$_arr = array(
						'status'			=>1,
						'modify_date'		=>date("Y-m-d H:i:s"),
						'user_id'			=>$this->getUserId(),
				);
				$this->_name='rms_group_schedule';
				$where=" id = ".$schId;
				$this->update($_arr, $where);
			}
			
			$subjectInSch = $this->getAllScheduleSubjectForPublic($data);
			if(!empty($subjectInSch)) foreach($subjectInSch AS $rowSub){
				$checking = $this->checkingSubjectInGroupDetail($rowSub);
				if(!empty($checking)){
						
						$dataSubj = array(
							'teacher' =>$rowSub['teacherId'],
						);
						$where = "group_id=".$rowSub['groupId']." AND subject_id=".$rowSub['subjectId'];
						$this->_name = "rms_group_subject_detail";
						$this->update($dataSubj,$where);
				}else{
					$dataSubj = array(
						'teacher' =>$rowSub['teacherId'],
						'group_id' =>$rowSub['groupId'],
						'subject_id' =>$rowSub['subjectId'],
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
			
					
			return 1;
		}
    	return 0;
    }
}

