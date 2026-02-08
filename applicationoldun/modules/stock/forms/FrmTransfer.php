<?php 
Class Stock_Form_FrmTransfer extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
	}
	public function FrmTransfer($data=null){
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		 
		$dbt = new Stock_Model_DbTable_DbTransferstock();
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		

		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];

		$from_location = new Zend_Dojo_Form_Element_FilteringSelect("from_location");
		$from_location->setMultiOptions($_arr_opt_branch);
		$from_location->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("FROM_BRANCH"),
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside height-text',));
		$from_location->setValue($request->getParam("from_location"));
		if (count($optionBranch)==1){
			$from_location->setAttribs(array('readonly'=>'readonly'));
			if(!empty($optionBranch))foreach($optionBranch AS $row){
				$from_location->setValue($row['id']);
			}
		}

		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
		$optionBranch = $_dbgb->getAllLocation();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$to_location = new Zend_Dojo_Form_Element_FilteringSelect("to_location");
		$to_location->setMultiOptions($_arr_opt_branch);
		$to_location->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("FROM_BRANCH"),
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside height-text',));
		$to_location->setValue($request->getParam("to_location"));
		
		
		$transfer_no = new Zend_Dojo_Form_Element_TextBox('transfer_no');
		$transfer_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside height-text',
				'readonly'=>'readonly',
				'style'=>'color: red; font-weight: 600;',
				'placeholder'=>$this->tr->translate("TRANSFER_NO"),
		));

		if(empty($data)){
			$transfer_no->setValue($dbt->getTransferNo());	
		}else{
			$transfer_no->setValue($data["transfer_no"]);
		}
		
		
		$transfer_date= new Zend_Dojo_Form_Element_DateTextBox('transfer_date');
		$transfer_date->setAttribs(
			array(
				'dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'required'=>false)
			);
		$_date = $request->getParam("transfer_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$transfer_date->setValue($_date);

		$note=  new Zend_Form_Element_Textarea('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'font-family: inherit;  min-height:100px !important;'));

		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$id = new Zend_Form_Element_Hidden('id');
		
		if(!empty($data)){
			if (!empty($data["transfer_date"])){
				$transfer_date->setValue(date("Y-m-d",strtotime($data["transfer_date"])));
			}
			$id->setValue($data["id"]);
			$transfer_no->setValue($data["transfer_no"]);
			$from_location->setValue($data["from_location"]);
			$from_location->setAttribs(array(
				'readonly'=>'readonly',
			));
			$to_location->setValue($data["to_location"]);
			$to_location->setAttribs(array(
				'readonly'=>'readonly',
			));
			$note->setValue($data["note"]);
			$note->setValue($data["note"]);
			$_status->setValue($data["status"]);
		}
		$this->addElements(array(
				$id,
				$from_location,
				$to_location,
				$transfer_no,
				$transfer_date,
				$note,
				$_status
			));
		
		return $this;
		
	}
	
	
	
	
}