<?php

class Setting_Model_DbTable_DbCertificate extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_certificate_setting'; 
	 public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
    
    function addCertifSetting($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$part= PUBLIC_PATH.'/images/background/';
    		$name = $_FILES['photo']['name'];
    		$size = $_FILES['photo']['size'];
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		$photo='';
    		
    		if (!empty($name)){
    			$ss = 	explode(".", $name);
    			$image_name = "background_".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['photo']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$photo = $image_name;
    			}
    			else
    				$string = "Image Upload failed";
    		}

			$outstanding_bg = $_FILES['outstanding_bg']['name'];

    		$outstanding_photo='';
    		if (!empty($outstanding_bg)){
    			$ss = 	explode(".", $outstanding_bg);
    			$bg_name = "background_label_".date("Y").date("m").date("d").time().".".end($ss);
    			$tmp = $_FILES['outstanding_bg']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$bg_name)){
    				$outstanding_photo = $bg_name;
    			}
    			else
    				$string = "Image Upload failed";
				
    		}

			$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ 
					$dept = $dept.",".$rs;
	    		}
	    	}

	    	$_arr = array(

	    			'type'	    	=> $_data['type'],
					'degreeList'  	=> $dept,		
	    			'branch_id'	    => $_data['branch_id'],
	    			'schoolOption'	=> $_data['schoolOption'],
					'title'			=> $_data['title'],
					'certificate_describe'=>$_data['certificate_describe'],
	    			'describe_font'	=>$_data['font_size'],
	    			'issue_date'	=>$_data['issue_date'],
	    		
	    			'name_left'		=>$_data['name_left'],
					'name_top'		=>$_data['name_top'],
					'gender_left'	=>$_data['gender_left'],
					'gender_top'	=>$_data['gender_top'],
					'date_left'		=>$_data['date_left'],
					'date_top'		=>$_data['date_top'],
					'code_left'		=>$_data['code_left'],
					'code_top'		=>$_data['code_top'],
					'academic_left'	=>$_data['academic_left'],
					'academic_top'	=>$_data['academic_top'],
					'rank_left'		=>$_data['rank_left'],
					'rank_top'		=>$_data['rank_top'],
					'grade_left'	=>$_data['grade_left'],
					'grade_top'		=>$_data['grade_top'],
					'describe_left'	=>$_data['describe_left'],
					'describe_top'	=>$_data['describe_top'],
					'month_left'	=>$_data['month_left'],
					'month_top'		=>$_data['month_top'],
					'day_left'		=>$_data['day_left'],
					'day_top'		=>$_data['day_top'],
					'year_left'		=>$_data['year_left'],
					'year_top'		=>$_data['year_top'],

					'name_eng_left'		=>$_data['name_eng_left'],
					'name_eng_top'		=>$_data['name_eng_top'],
					'gender_eng_left'	=>$_data['gender_eng_left'],
					'gender_eng_top'	=>$_data['gender_eng_top'],
					'date_eng_left'		=>$_data['date_eng_left'],
					'date_eng_top'		=>$_data['date_eng_top'],
					'grade_eng_left'	=>$_data['grade_eng_left'],
					'grade_eng_top'		=>$_data['grade_eng_top'],
					'duration_left'		=>$_data['duration_left'],
					'duration_top'		=>$_data['duration_top'],
					'duration_eng_left'	=>$_data['duration_eng_left'],
					'duration_eng_top'	=>$_data['duration_eng_top'],
					'issue_date_left'	=>$_data['issue_date_left'],
					'issue_date_top'	=>$_data['issue_date_top'],
				
					'default'		=>1,
	    			'status'		=>1,
					'modify_date'	=>date("Y-m-d H:i:s"),
					'create_date'	=>date("Y-m-d H:i:s"),
	    			'user_id'		=>$this->getUserId(),	

					'background' 	=>$photo,
					'outstanding_bg' =>$outstanding_photo,
	    			);
	    	$this->_name ="rms_certificate_setting";
	    	$this->insert($_arr);//insert data
	    	
	    	$_db->commit();
	    	}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    		$_db->rollBack();
	    	}
    }

    public function updateCertificate($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
			$id = $_data['id'];

			$dept = "";
	    	if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
	    		if (empty($dept)){
	    			$dept = $rs;
	    		}else{ $dept = $dept.",".$rs;
	    		}
	    	}
    		
			$_arr = array(
					'type'	    	=> $_data['type'],
					'degreeList'  	=> $dept,
					'branch_id'	    =>$_data['branch_id'],
					'schoolOption'	=>$_data['schoolOption'],
					'title'			=>$_data['title'],
					'certificate_describe'=>$_data['certificate_describe'],
					'describe_font'	=>$_data['font_size'],
					'issue_date'	=>$_data['issue_date'],

	    			'name_left'		=>$_data['name_left'],
					'name_top'		=>$_data['name_top'],
					'gender_left'	=>$_data['gender_left'],
					'gender_top'	=>$_data['gender_top'],
					'date_left'		=>$_data['date_left'],
					'date_top'		=>$_data['date_top'],
					'code_left'		=>$_data['code_left'],
					'code_top'		=>$_data['code_top'],
					'academic_left'	=>$_data['academic_left'],
					'academic_top'	=>$_data['academic_top'],

					'rank_left'		=>$_data['rank_left'],
					'rank_top'		=>$_data['rank_top'],
					'grade_left'	=>$_data['grade_left'],
					'grade_top'		=>$_data['grade_top'],
					'describe_left'	=>$_data['describe_left'],
					'describe_top'	=>$_data['describe_top'],
					'month_left'	=>$_data['month_left'],
					'month_top'		=>$_data['month_top'],
					'day_left'		=>$_data['day_left'],
					'day_top'		=>$_data['day_top'],
					'year_left'		=>$_data['year_left'],
					'year_top'		=>$_data['year_top'],

					'name_eng_left'		=>$_data['name_eng_left'],
					'name_eng_top'		=>$_data['name_eng_top'],
					'gender_eng_left'	=>$_data['gender_eng_left'],
					'gender_eng_top'	=>$_data['gender_eng_top'],
					'date_eng_left'		=>$_data['date_eng_left'],
					'date_eng_top'		=>$_data['date_eng_top'],
					'grade_eng_left'	=>$_data['grade_eng_left'],
					'grade_eng_top'		=>$_data['grade_eng_top'],
					'duration_left'		=>$_data['duration_left'],
					'duration_top'		=>$_data['duration_top'],
					'duration_eng_left'	=>$_data['duration_eng_left'],
					'duration_eng_top'	=>$_data['duration_eng_top'],
					'issue_date_left'	=>$_data['issue_date_left'],
					'issue_date_top'	=>$_data['issue_date_top'],
					
	    			'status'		=>1,
					'modify_date'	=>date("Y-m-d H:i:s"),
					'create_date'	=>date("Y-m-d H:i:s"),
	    			'user_id'		=>$this->getUserId(),		
			);
			
			$part = PUBLIC_PATH . '/images/background/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo_name = $_FILES['photo']['name'];
			if (!empty($photo_name)) {
				//unset old file here
				$tem = explode(".", $photo_name);
				$image_name = "background_" . date("Y") . date("m") . date("d") . time() . "." . end($tem);
				$tmp = $_FILES['photo']['tmp_name'];
				if (move_uploaded_file($tmp, $part . $image_name)) {
					move_uploaded_file($tmp, $part . $image_name);
					$photo = $image_name;
					$_arr['background'] = $photo;
				}
			}
			if (!empty($photo_name) and file_exists($part . $_data['old_photo'])) { //delelete old file
				unlink($part . $_data['old_photo']);
			}

			// outstanding Background
			$outstanding_bg = $_FILES['outstanding_bg']['name'];
			if (!empty($outstanding_bg)) {
				//unset old file here
				$tem = explode(".", $outstanding_bg);
				$image_name = "background_label_" . date("Y") . date("m") . date("d") . time() . "." . end($tem);
				$tmp = $_FILES['outstanding_bg']['tmp_name'];
				if (move_uploaded_file($tmp, $part . $image_name)) {
					move_uploaded_file($tmp, $part . $image_name);
					$photo = $image_name;
					$_arr['outstanding_bg'] = $photo;
				}
			}
			if (!empty($outstanding_bg) and file_exists($part . $_data['old_outstanding_bg'])) { //delelete old file
				unlink($part . $_data['old_outstanding_bg']);
			}
			
			$this->_name ="rms_certificate_setting";
			$where=$this->getAdapter()->quoteInto("id=?", $id);
			$this->update($_arr, $where);
			
			$_db->commit();
    	}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$_db->rollBack();
    	}
    }
   	
    function getAllCertificate($search){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$check = '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
    	$uncheck = '<i class="fa fa-square-o" aria-hidden="true"></i>';

		$letterpraise = $tr->translate("HORNOR_CERTIFICATE");
		$hornorList = $tr->translate("OUTSTANDING_STUDENT");
		$achievement = $tr->translate("STUDENT_ACHIEVEMENT");
		$certificate = $tr->translate("CERTIFICATE");

    	$sql = "SELECT b.id,
		(SELECT bs.branch_nameen FROM rms_branch as bs WHERE bs.br_id =b.branch_id LIMIT 1) as branch_name,
		CASE    
			WHEN  b.type = 1 THEN '$letterpraise'
			WHEN  b.type = 2 THEN '$hornorList'
			WHEN  b.type = 3 THEN '$achievement'
			WHEN  b.type = 4 THEN '$certificate'
		END AS type,
    	b.title,
		(SELECT GROUP_CONCAT(i.shortcut) FROM rms_items AS i WHERE i.type=1 AND FIND_IN_SET(i.id,degreeList) LIMIT 1) AS degreeList,
    	(SELECT sp.title FROM `rms_schooloption` AS sp WHERE sp.id = b.schoolOption LIMIT 1) AS schoolOption,
    	b.issue_date  ";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->caseStatusShowImage("b.status");
    	$sql.=" FROM rms_certificate_setting AS b ";
    	
    	$where = ' WHERE  b.title !="" ';   	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=trim(addslashes($search['adv_search']));
    		$s_where[]=" b.title LIKE '%{$s_search}%'";
    		$s_where[]=" b.note LIKE '%{$s_search}%'";
    		
    		$where.=' AND ('.implode(' OR ',$s_where).')';
    	}
		if($search['status']>-1){
			$where.= " AND b.status = ".$search['status'];
		}
		
		$where.= $dbp->getAccessPermission('b.branch_id');
		
    	$order=' ORDER BY b.id DESC';
   		return $db->fetchAll($sql.$where.$order);
   }
      
 function getCertifById($id){
 	$dbp = new Application_Model_DbTable_DbGlobal();
	 	$sql_str=$dbp->caseStatusShowImage("status");
	 	
    	$db = $this->getAdapter();
    	$sql = "SELECT *
			$sql_str
    	 FROM
    	$this->_name ";
    	$where = " WHERE `id`= $id ";
    	
   		return $db->fetchRow($sql.$where);
    }
    
}  
	  

