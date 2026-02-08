<?php

class Global_Model_DbTable_DbRating extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_rating';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;  	 
    }
	public function addNewRating($_data){
	//	print_r($_data); exit();
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
	  		$sql="SELECT id FROM rms_rating where status =1 ";
	  		$sql.=" AND rating='".$_data['rating']."'";
	  		$rs = $db->fetchOne($sql);
	  		if(!empty($rs)){
	  			return -1;
	  		}

			$part= PUBLIC_PATH.'/images/emoji/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo = "";
			$name = $_FILES['emoji']['name'];
			if(!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "emoji_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['emoji']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$photo = $image_name;
				}else{
					$string = "Image Upload failed";
				}
			}

	  		$arr = array(
	  				'rating'		=> $_data['rating'],
	  				'emoji'			=> $photo,
	  				'createDate' 	=>date("Y-m-d"),
					'modifyDate' 	=>date("Y-m-d"),
	  				'status'		=> 1,
					'userId'		=> $this->getUserId()
	  		);
			$this->insert($arr);
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getRatingById($id){
		$db = $this->getAdapter();
		$sql = " SELECT * FROM rms_rating WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function updateRating($_data){
		
		$db = $this->getAdapter();
		//$db->beginTransaction();
		try{
			$status = empty($_data['status'])?0:1;

			$part= PUBLIC_PATH.'/images/emoji/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}

			$_arr=array(
					'rating'		=> $_data['rating'],
					'createDate' 	=>date("Y-m-d"),
					'modifyDate' 	=>date("Y-m-d"),
					'status'	    => $status,
					'userId'	    => $this->getUserId()
			);

			$name = $_FILES['emoji']['name'];
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "emoji_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['emoji']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					if (file_exists($part.$_data["old_photo"])) {
						if (!empty($_data["old_photo"])){
							unlink($part.$_data["old_photo"]);//delete old file
						}
					}
					$_arr['emoji'] = $image_name;
				}
			}
			$this->_name = "rms_rating";
			$where=$this->getAdapter()->quoteInto("id=?", $_data['id']);
			$this->update($_arr,$where);
		
		}catch (Exception $e){
			//$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	
	function getAllRating($search){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  id, rating, createDate,
				  (SELECT  CONCAT(first_name) FROM rms_users WHERE id=userId )AS user_name
				";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM `rms_rating`  WHERE 1 ";
		
		$where = '';
		$order = ' ORDER BY id ASC ';
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['title'])){
			$s_where = array();
			$s_search = addslashes(trim($search['title']));
			$s_where[] = " rating LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1 AND $search['status']!=''){
			$where.=' AND status='.$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}	
}