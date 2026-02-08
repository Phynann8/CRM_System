<?php
class Mobileapp_SettingController extends Zend_Controller_Action {
	
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}

	public	function indexAction(){
		try{
			$db = new Mobileapp_Model_DbTable_DbSetting();
			if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				$db ->updateMobileLabel($data);
				Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS','/mobileapp/setting');
			}
			
			$row = array();
			
			$row['amountRequestPermission']= $db->getLabelByKeyNamesetting("amountRequestPermission");
			$row['intervalMinAtt'] = $db->getLabelByKeyNamesetting("intervalMinAtt");
			
			$row['settingEvaluation']= $db->getLabelByKeyNamesetting("settingEvaluation");
			$row['displayPmtValidate'] = $db->getLabelByKeyNamesetting("displayPmtValidate");
			
			$this->view->row =$row;
			$this->view->stuFeature =$db->getSpecialFeature();
			$this->view->tFeature =$db->getSpecialFeature(3);
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function addAction(){
		$this->_redirect('/mobileapp/index');
		
	}
	
}

