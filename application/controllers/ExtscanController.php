<?php

class ExtscanController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/home';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');  
    }
	
	public function indexAction()
    {
    	$this->_helper->layout()->disableLayout();
		$dbFront = new Application_Model_DbTable_DbScanning();
		$rs = $dbFront->getAllEntrance();
		$this->view->rs = $rs;
	}
	
	
	public function scanningAction()
	{
		$this->_helper->layout()->disableLayout();
		$settingId = $this->getRequest()->getParam('settingId');
		$settingId = empty($settingId)?0:$settingId;
		
		$gatewayOption = $this->getRequest()->getParam('gatewayOption');
		$gatewayOption = empty($gatewayOption)?0:$gatewayOption;
		
		$dbSc = new Application_Model_DbTable_DbScanning();
		$row = $dbSc->getEntranceById($gatewayOption);
		$this->view->row = $row;
		
		$arrFilter = array(
			"scanDate" =>date("Y-m-d H:i:s"),
		);
		$row = $dbSc->getSettinInfo($arrFilter);
		$this->view->setting = $row;
		
		
		$playListRs = $dbSc->getAllPlaylistvideo();
		$this->view->playListRs = $playListRs;
	}
	
	//Scanning
	public function scanningcardAction(){
		try{
			if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				$data['keyword'] = empty($data['keyword'])?0:$data['keyword'];
				
				$dbdoc = new Application_Model_DbTable_DbScanning();
				$arrReturn = $dbdoc->getScanningResult($data);
				
				print_r(Zend_Json::encode($arrReturn));
				exit();
			}
		}catch(Exception $e){
			Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	public function scanByscannerAction()
    {
    	
    	$this->_helper->layout()->disableLayout();
		$gatewayOption = $this->getRequest()->getParam('gatewayOption');
		$gatewayOption = empty($gatewayOption)?0:$gatewayOption;
		
		$dbFront = new Application_Model_DbTable_DbFront();
		$row = $dbFront->getEntranceById($gatewayOption);
		$this->view->row = $row;
		
		$playListRs = $dbFront->getAllPlaylistvideo();
		$this->view->playListRs = $playListRs;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
	}
	
}