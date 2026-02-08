<?php
class Issue_ImportxmlscheduleController extends Zend_Controller_Action {
	
	
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			// include  PUBLIC_PATH.'/Classes/PHPExcel/IOFactory.php';
			$db=new Issue_Model_DbTable_DbImportxmlNew();
			// $db->truncateStringCode();
			if($this->getRequest()->isPost()){
				// $adapter = new Zend_File_Transfer_Adapter_Http();
				// $adapter->receive();
				// $file = $adapter->getFileInfo();
				// $inputFileName = $file['file_xml']['tmp_name'];
 				// try {
				// 	$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
				// } catch(Exception $e) {
				// 	die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
				// }
				// $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
				$data = $this->getRequest()->getPost();
				$db->uploadXMLFile($data);
				Application_Form_FrmMessage::Sucessfull("Upload Successfully","/issue/importxmlschedule");
			}
			
			$form = new Application_Form_FrmCombineSearchGlobal();
			$forms = $form->FormImportSchedule();
			Application_Model_Decorator::removeAllDecorator($forms);
			$this->view->form = $form;
			$this->view->subjMessageHtml = $db->getNewSubjectImport(1);
			$this->view->teacherMessageHtml = $db->getNewTeacherImport(1);
			$this->view->groupMessageHtml = $db->getNewGroupImport(1);
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
	}
	function importxmlAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbImportxmlNew();
			$result = $db->importxmlSubject($data);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
	

	function addAction(){
		$this->_redirect("/issue/importxmlschedule");
	}
	public function editAction(){
		$this->_redirect("/issue/importxmlschedule");
	}
	public function previewAction(){
		
		$group = new Issue_Model_DbTable_DbImportxmlNew();
		
		$search = array();
		$rs_rows = $group->getAllGroupSchedule($search);
		$rsCouning = $group->getCountingSubjectByGroup($search);
		
		$this->view->rs = $rs_rows;
		$this->view->rsCouning = $rsCouning;
		$this->view->search = $search;
		
	}
	
	public function impexcelAction(){
		try{
			include  PUBLIC_PATH.'/Classes/PHPExcel/IOFactory.php';
			$db=new Issue_Model_DbTable_DbImportxmlNew();
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
				
				$data["scheduleSetting"] = empty($data["scheduleSetting"]) ? 0 : $data["scheduleSetting"];
				$data["branchId"] = empty($data["branchId"]) ? 1 : $data["branchId"];
				$data["academicYear"] = empty($data["academicYear"]) ? 0 : $data["academicYear"];

				if(!empty($data["scheduleSetting"])){
					$adapter = new Zend_File_Transfer_Adapter_Http();
					$adapter->receive();
					$file = $adapter->getFileInfo();
					$inputFileName = $file['file_excel']['tmp_name'];
					
					$sessionXml=new Zend_Session_Namespace('xmlFile');
					$sessionXml->xml_ImportType=1;
					$sessionXml->xml_Step=0;
					$sessionXml->xml_FileName=$file['file_excel']['name'];
					$sessionXml->xml_branch=$data["branchId"];
					$sessionXml->xml_setting=$data["scheduleSetting"];
					$sessionXml->xml_academicYear=$data["academicYear"];
					
					try {
						$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
					} catch(Exception $e) {
						die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
					}
					$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
					$db->ScheduleByImport($sheetData,$data);
					Application_Form_FrmMessage::Sucessfull("Import Successfully", "/issue/importxmlschedule");
				}else{
					Application_Form_FrmMessage::Sucessfull("Not Schedule Setting Selected", "/issue/importxmlschedule");
				}
				
			}
			else{
			
			}
			
			$frm = new Issue_Form_FrmSchedule();
			$frm->FrmAddSchedule(null);
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_items = $frm;
			
		}catch (Exception $e){
			
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function submitexcelAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_DbImportxmlNew();
			$result = $db->submitDataFinalSchedule($data);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
	
}

