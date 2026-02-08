<?php

class Issue_Form_FrmReqpermission extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmReqpermission($data=array()){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userid = $_dbgb->getUserId();
    	$userinfo = $_dbuser->getUserInfo($userid);
		
		$dbAchievement = new Issue_Model_DbTable_DbAchievement();
			
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$branch_id->setMultiOptions($_arr_opt_branch);
    	$branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getallgroupby();',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$branch_id->setValue($request->getParam("branch_id"));
    	if (count($optionBranch)==1){
    		$branch_id->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$branch_id->setValue($row['id']);
    		}
    	}
		
		$amountDay = new Zend_Dojo_Form_Element_TextBox('amountDay');
    	$amountDay->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'onChange'=>'calculateToDate()',
				
    	));
		$amountDay->setValue(1);
		
		$fromDate = new Zend_Dojo_Form_Element_DateTextBox('fromDate');
    	$date = date("Y-m-d");
    	$fromDate->setAttribs(array(
    			'data-dojo-Type'=>"dijit.form.DateTextBox",
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',
				'onChange'=>'calculateToDate()',
    			'required'=>true));
    	$fromDate->setValue($date);
		
		$toDate = new Zend_Dojo_Form_Element_DateTextBox('toDate');
    	$toDateVal = date("Y-m-d");
    	$toDate->setAttribs(array(
    			'data-dojo-Type'=>"dijit.form.DateTextBox",
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',
    			'readonly'=>'readonly',
    			'required'=>true));
    	$toDate->setValue($toDateVal);
		
    	
		$reason=  new Zend_Form_Element_Textarea('reason');
    	$reason->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'width:99%; font-family: inherit;  min-height:100px !important;'));
		
    	$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
    	$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
    	$_status_opt = array(
    			1=>$this->tr->translate("ACTIVE"),
    			0=>$this->tr->translate("DACTIVE"));
    	$_status->setMultiOptions($_status_opt);
    	$_status->setValue($request->getParam("status"));
    	
    	$id = new Zend_Form_Element_Hidden('id'); 
		
    	if(!empty($data)){

    		$branch_id->setValue($data["branchId"]);
    		$branch_id->setAttribs(array('readonly'=>'readonly'));
			
    		$amountDay->setValue($data["amountDay"]);
    		$fromDate->setValue($data["fromDate"]);
    		$toDate->setValue($data["toDate"]);
    		$reason->setValue($data["reason"]);
    		$_status->setValue($data["status"]);
    		$id->setValue($data["id"]);
			
    	}
    	
    	$this->addElements(
			array(
    			$branch_id,
				$amountDay ,
    			$fromDate,
    			$toDate,
    			$reason,
    			$id,
    			$_status,
    			
    			)
			);
    	return $this;
    }
}

