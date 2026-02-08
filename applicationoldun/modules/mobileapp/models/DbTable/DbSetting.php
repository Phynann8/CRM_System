<?php
class Mobileapp_Model_DbTable_DbSetting extends Zend_Db_Table_Abstract
{
	
	protected $_name = 'rms_student';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	function updateMobileLabel($data){
		try{
			if(!empty($data['spIdetity'])){
				$ids = explode(',', $data['spIdetity']);
				foreach ($ids as $i){
					$rowStatus = empty($data['sFt'.$i]) ? 0 : 1;
					$_arr = array(
						'status'		=>$rowStatus,	
					);
					$whereFt = ' id = '.$i;
					$this->_name='mobile_special_feature';
					$this->update($_arr, $whereFt);
				}
			}
			if(!empty($data['tIdetity'])){
				$idsT = explode(',', $data['tIdetity']);
				foreach ($idsT as $t){
					$rowStatus = empty($data['tFt'.$t]) ? 0 : 1;
					$_arr = array(
						'status'		=>$rowStatus,	
					);
					$whereFt = ' id = '.$t;
					$this->_name='mobile_special_feature';
					$this->update($_arr, $whereFt);
				}
			}
			
			$rows = $this->getLabelByKeyNamesetting('amountRequestPermission');
			$amountRequestPermission = empty($data['amountRequestPermission']) ? 1 : $data['amountRequestPermission'];
			if (empty($rows)){
				$arr = array(
						'keyName'		=>'amountRequestPermission',
						'keyValue'		=>$amountRequestPermission,
						'keyValueEn'	=>$amountRequestPermission,
						'user_id'		=>$this->getUserId(),
				);
				$this->_name='moble_label';
				$this->insert($arr);
			}else{
				$arr = array(
						'keyValue'		=>$amountRequestPermission,
						'keyValueEn'	=>$amountRequestPermission,
				);
				$where=" keyName= 'amountRequestPermission'";
				$this->_name='moble_label';
				$this->update($arr, $where);
			}
			
			$rows = $this->getLabelByKeyNamesetting('displayPmtValidate');
			$displayPmtValidate = empty($data['displayPmtValidate']) ? 0 : 1;
			if (empty($rows)){
				
				$arr = array(
						'keyName'		=>'displayPmtValidate',
						'keyValue'		=>$displayPmtValidate,
						'keyValueEn'	=>$displayPmtValidate,
						'user_id'		=>$this->getUserId(),
				);
				$this->_name='moble_label';
				$this->insert($arr);
			}else{
				$arr = array(
						'keyValue'		=>$displayPmtValidate,
						'keyValueEn'	=>$displayPmtValidate,
				);
				$where=" keyName= 'displayPmtValidate'";
				$this->_name='moble_label';
				$this->update($arr, $where);
			}
			
			$rows = $this->getLabelByKeyNamesetting('intervalMinAtt');
			$intervalMinAtt = empty($data['intervalMinAtt']) ? 0 : $data['intervalMinAtt'];
			if (empty($rows)){
				$arr = array(
						'keyName'		=>'intervalMinAtt',
						'keyValue'		=>$intervalMinAtt,
						'keyValueEn'	=>$intervalMinAtt,
						'user_id'		=>$this->getUserId(),
				);
				$this->_name='moble_label';
				$this->insert($arr);
			}else{
				$arr = array(
						'keyValue'		=>$intervalMinAtt,
						'keyValueEn'	=>$intervalMinAtt,
				);
				$where=" keyName= 'intervalMinAtt'";
				$this->_name='moble_label';
				$this->update($arr, $where);
			}
			
			$rows = $this->getLabelByKeyNamesetting('settingEvaluation');
			$settingEvaluation = empty($data['settingEvaluation']) ? 0 : $data['settingEvaluation'];
			if (empty($rows)){
				$arr = array(
						'keyName'		=>'settingEvaluation',
						'keyValue'		=>$settingEvaluation,
						'keyValueEn'	=>$settingEvaluation,
						'user_id'		=>$this->getUserId(),
				);
				$this->_name='moble_label';
				$this->insert($arr);
			}else{
				$arr = array(
						'keyValue'		=>$settingEvaluation,
						'keyValueEn'	=>$settingEvaluation,
				);
				$where=" keyName= 'settingEvaluation'";
				$this->_name='moble_label';
				$this->update($arr, $where);
			}

		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getLabelByKeyNamesetting($keyName){
    	$db = $this->getAdapter();
    	$sql = " SELECT 
					s.`code`
					,s.keyName
					,s.keyValue 
					,s.keyValueEn 
				FROM `moble_label` AS s
				WHERE s.status=1 
				AND s.`keyName` ='$keyName' LIMIT 1";
    	return $db->fetchRow($sql);
    }
	
	public function getSpecialFeature($type="1"){
    	$db = $this->getAdapter();
    	$sql = " 
			SELECT 
				sp.id
				,sp.`title`
				,sp.`description`
				,sp.`status`
				,sp.`type`
			FROM `mobile_special_feature` AS sp 
			WHERE sp.status > -1
				AND sp.type = $type 
			ORDER BY sp.`ordering` ASC
		";
    	return $db->fetchAll($sql);
    }
	
	
	
}

