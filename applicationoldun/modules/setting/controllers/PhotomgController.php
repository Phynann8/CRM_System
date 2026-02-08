<?php
class Setting_PhotomgController extends Zend_Controller_Action {
	const REDIRECT_URL='/setting';
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		
		$request = $this->getRequest();
    	$db = new Setting_Model_DbTable_DbPhotoMg();
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/foundation/register/index");
					exit();
				}
				
				$exist = $db->addPhotoMg($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/setting/photomg");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		
		$tsub=new Setting_Form_FrmPhotoMg();
		 // Get values from URL
		$branchIdrq   = $request->getParam("branchId");
		$recordTyperq  = $request->getParam("recordType");
		$photoStatusrq = $request->getParam("photoStatus");
		$recordIdrq    = $request->getParam("recordId");

		$branchId	= empty($branchIdrq) ? 0 : $branchIdrq;
		$recordType	= empty($recordTyperq) ? 1 : $recordTyperq;
		$photoStatus	= empty($photoStatusrq) ? 1 : $photoStatusrq;
		$recordId	= empty($recordIdrq) ? 0 : $recordIdrq;

		// Pass to view if needed
		$this->view->branchId    = $branchId;
		$this->view->recordType  = $recordType;
		$this->view->photoStatus = $photoStatus;
		$this->view->recordId    = $recordId;

		$param = array(
			'branchId'    => $branchId,
			'recordType'  => $recordType,
			'photoStatus' => $photoStatus,
			'recordId'    => $recordId,
		);

		$frm=$tsub->FrmPhotoMg($param);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	
	function getRecordListAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Setting_Model_DbTable_DbPhotoMg();
			
			$rows = $db->getAllListRecord($data);
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	
}