<?php
class Api_TelegramController extends Zend_Controller_Action
{
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	$this->_helper->layout()->disableLayout();
    	header('Content-type:application/json;charset=utf-8');
		
		$_dbAction = new Api_Model_DbTable_DbTelegramApi();
    	$GetData = $this->getRequest()->getParams();
			
    	if ($_SERVER['REQUEST_METHOD'] == "GET"){
    		if($GetData['url']=="aboutUs"){
    			$arrResult= $_dbAction->getAboutUs($GetData);
				print_r(Zend_Json::encode($arrResult));
    		}else if ($GetData['url']=="startBot"){
    			$arrResult= $_dbAction->getStartBotInfo($GetData);
				print_r(Zend_Json::encode($arrResult));
			}else if ($GetData['url']=="help"){
    			$arrResult= $_dbAction->getHelp($GetData);
				print_r(Zend_Json::encode($arrResult));
			}else if ($GetData['url']=="getInfo"){
    			$arrResult= $_dbAction->getCurrentChatInfoMessage($GetData);
				print_r(Zend_Json::encode($arrResult));
    		}
    		else{
    			echo Zend_Http_Response::responseCodeAsText(401,true);
    		}
    	}else if ($_SERVER['REQUEST_METHOD'] == "POST"){
    		if($this->getRequest()->isPost()){
    			$postData = $this->getRequest()->getPost();
    			if ($GetData['url']=="add"){
    				$arrResult =  $_dbAction->submitTokenTelegram($postData);
					print_r(Zend_Json::encode($arrResult));
    			}else if ($GetData['url']=="remove"){
    				$arrResult = $_dbAction->removeTokenTelegram($postData);
					print_r(Zend_Json::encode($arrResult));
    			}
    			else{
    				echo Zend_Http_Response::responseCodeAsText(401,true);
    			}
				
			}else{
				echo Zend_Http_Response::responseCodeAsText(405,true);
			}
    	}else{
    		echo Zend_Http_Response::responseCodeAsText(405,true);
    	}
    	exit();
    }
   
}