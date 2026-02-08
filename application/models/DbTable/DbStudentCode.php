<?php

class Application_Model_DbTable_DbStudentCode extends Zend_Db_Table_Abstract
{
	// set name value
	public function setName($name)
	{
		$this->_name = "rms_student_code";
	}
	function getPrefixCode($data=[]){
		$prefixOpt = Setting_Model_DbTable_DbGeneral::geValueByKeyName('studentPrefixOpt');
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$pre="";
		if ($prefixOpt == 1) {//branch
			$branch_id = empty($data["branchId"]) ? 0 : $data["branchId"];
			$pre = $dbGb->getPrefixCode($branch_id);
			//$settingFor = 100; //PSIS CHV
			$settingFor = 1; //General
			if($settingFor==100){
				$lastedAca = $dbGb->getLatestAcadmicYear();
				if(!empty($lastedAca)){
					$fromYear = empty($lastedAca["fromYear"]) ? "" : $lastedAca["fromYear"]."-";
					$pre = $fromYear.$pre."-";
				}
			}
			$prefixChar =$pre;
		} elseif ($prefixOpt == 2) {
			$degree = empty($data["degree"]) ? 0 : $data["degree"];
			$pre = $dbGb->getPrefixByDegree($degree);
		} elseif ($prefixOpt == 3) {
			$pre = '';
		} else {//by entry
			$pre = Setting_Model_DbTable_DbGeneral::geValueByKeyName('studentIPrefix');
		}
		
		$optionType = Setting_Model_DbTable_DbGeneral::geValueByKeyName('settingStuID'); // count type setting
		if ($optionType == 3) {
			$degree = empty($data["degree"]) ? 0 : $data["degree"];
			$dbdeg = new Global_Model_DbTable_DbItems();
			$type = 1;
			$row = $dbdeg->getDegreeById($degree, $type);
			$schoolOption = empty($row['schoolOption']) ? 0 : $row['schoolOption'];

			$info = $dbGb->getSchoolOptionInfo($schoolOption);
			if (!empty($info['prefix'])) {
				$pre .= $info['prefix'];
			}
		}

		return $pre;
	}
	function checkGapStuCode($data=[]){
		$stuCode = $data['stuCode'];
		$sql="
				SELECT 
					stc.id
				FROM rms_student_code AS stc 
				WHERE 1 
				AND stc.isUse = 0
				AND stc.isCurrent =1
			";
		$sql.=" AND stc.branchId=".$data["branchId"];
		$sql.= " AND ".$this->getAdapter()->quoteInto("stc.stuCode=?", $stuCode);
		$sql.=" LIMIT 1 ";
		$db=$this->getAdapter();
		return $db->fetchOne($sql);
	}
	function insertStudentCode($data=[]){
		try {
			$dbGb = new Application_Model_DbTable_DbGlobal();
			$userId = $dbGb->getUserId();
			$userId = empty($userId) ? 0 : $userId;
			
			$degree = empty($data["degree"]) ? 0 : $data["degree"];
			$dbdeg = new Global_Model_DbTable_DbItems();
			$rowDegee = $dbdeg->getDegreeById($degree, 1);
			$schoolOption = empty($rowDegee['schoolOption']) ? 0 : $rowDegee['schoolOption'];
				
			$checking = $this->checkGapStuCode($data);
			if(!empty($checking)){
				$paramUp=[
					'stuId'			=>$data['stuId'],
					'schoolOption'	=>$schoolOption,
					'acadedmicYear'	=>$data['acadedmicYear'],
					'degree'		=>$degree,
					'isUse'			=>1,
					'isCurrent'		=>1,
					'modifyDate'	=>date("Y-m-d H:i:s"),
					'userId'		=>$userId,
					'referenceType'	=>empty($data['referenceType']) ? 0 : $data['referenceType'],//1=from pmg, 2 = student foundation, 3 = ឆ្លងភូមិសិក្សា , 4=Student Return, 5=Change Branch',
					'referenceId'	=>empty($data['referenceId']) ? 0 : $data['referenceId']
				];
				$this->_name = "rms_student_code";
				$where = $this->getAdapter()->quoteInto("id=?", $checking);
				$this->update($paramUp, $where);
			}else{
				$startNum = 0;
				$prefix = $this->getPrefixCode($data);
				$stuNum = str_replace($prefix,"",$data['stuCode']);
			
				$param=[
					'branchId'		=>$data['branchId'],
					'stuId'			=>$data['stuId'],
					'stuCode'		=>$data['stuCode'],
					'schoolOption'	=>$schoolOption,
					'acadedmicYear'	=>$data['acadedmicYear'],
					'degree'		=>$degree,
					'prefix'		=>$prefix,
					'startNum'		=>$startNum,
					'stuNum'		=>$stuNum,
					'isUse'			=>1,
					'isCurrent'		=>1,
					'createDate'	=>date("Y-m-d H:i:s"),
					'modifyDate'	=>date("Y-m-d H:i:s"),
					'userId'		=>$userId,
					'referenceType'	=>empty($data['referenceType']) ? 0 : $data['referenceType'],//1=from pmg, 2 = student foundation, 3 = ឆ្លងភូមិសិក្សា , 4=Student Return, 5=Change Branch',
					'referenceId'	=>empty($data['referenceId']) ? 0 : $data['referenceId']
				];
				$this->_name = "rms_student_code";
				$this->insert($param);
			}
			
			return true;
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	
	function reverseStudCodeOfStudent($data=[]){
		try {
			$paramUp = [
				'stuId'			=>0,
				'isUse'			=>0,
				'referenceType'	=>$data['referenceType'],//1=from pmg, 2 = student foundation, 3 = ឆ្លងភូមិសិក្សា , 4=Student Return, 5=Change Branch',
				'referenceId'	=>$data['referenceId'],
				'modifyDate'	=>date("Y-m-d H:i:s"),
			];
			$this->_name = "rms_student_code";
			
			$where = $this->getAdapter()->quoteInto("stuId=?", $data['stuId']);
			$where.= " AND referenceType = ".$data['referenceType'];
			$where.= " AND referenceId = ".$data['referenceId'];
			$where.= " AND branchId = ".$data['branchId'];
			$this->update($paramUp, $where);
			return true;
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	
	function updateStuCodeData($data=[]){
		try {
			$paramUp = [
				'stuCode'		=>$data['stuCode'],
			];
			if(!empty($data['referenceId'])){
				$paramUp["referenceId"]=$data['referenceId'];
				$paramUp["referenceType"]=$data['referenceType'];
			}
			$this->_name = "rms_student_code";
			
			$where = $this->getAdapter()->quoteInto("stuId=?", $data['stuId']);
			$where.= " AND isCurrent = 1 ";
			$where.= " AND isUse = 1 ";
			$where.= " AND branchId = ".$data['branchId'];
			$this->update($paramUp, $where);
			
			return true;
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	
	function getCheckGapStuCode($data=[]){
		try {
			$sql="
				SELECT 
					stc.id
					,stc.stuCode
				FROM rms_student_code AS stc 
				WHERE 1 
				AND stc.isUse = 0
				AND stc.isCurrent =1
			";
			$sql.=" AND stc.branchId=".$data["branchId"];
			
			$optionType = Setting_Model_DbTable_DbGeneral::geValueByKeyName('settingStuID');// count type setting
			if ($optionType != 1) {
				if ($optionType == 2) {
					$sql.=" AND stc.degree=".$data["degree"];
				}else if ($optionType == 3) {
					$sql.=" AND stc.schoolOption=".$data["schoolOption"];
				}
			}
			$sql.=" LIMIT 1 ";
			$db=$this->getAdapter();
			return $db->fetchRow($sql);
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	
}
?>