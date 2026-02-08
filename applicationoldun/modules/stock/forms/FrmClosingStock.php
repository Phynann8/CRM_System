<?php 
Class Stock_Form_FrmClosingStock extends Zend_Dojo_Form {
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
	public function FrmClosingStock($data=null){
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		 
		$_dbcht = new Stock_Model_DbTable_DbCutStock();
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		

		$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'false',
				'placeholder'=>$this->tr->translate("SELECT_BRANCH"),
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside height-text',));
		$_branch_id->setValue($request->getParam("branch_id"));
		if (count($optionBranch)==1){
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($optionBranch))foreach($optionBranch AS $row){
				$_branch_id->setValue($row['id']);
			}
		}
		
		$date= new Zend_Dojo_Form_Element_DateTextBox('date');
		$date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'value'=>'now',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
				'readonly' => 'readonly',
			));
		$date->setValue(date("Y-m-d"));

		$note=  new Zend_Form_Element_Textarea('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'font-family: inherit;  min-height:100px !important;'));
		
		$this->addElements(array(
				$_branch_id,
				$date,
				$note

				));
		
		return $this;
		
	}
	
	
	
	
}