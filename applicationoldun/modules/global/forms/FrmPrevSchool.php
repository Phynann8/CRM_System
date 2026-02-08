<?php

class Global_Form_FrmPrevSchool extends Zend_Dojo_Form
{
	protected  $tr;
	protected $filter;

	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->filter = 'dijit.form.FilteringSelect';
	}
	function FrmAddPrevSchool($data)
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$typeItems = empty($typeItems) ? 1 : $typeItems;
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_dbuser = new Application_Model_DbTable_DbUsers();
		$userid = $_dbgb->getUserId();
		$userinfo = $_dbuser->getUserInfo($userid);


		$schoolName = new Zend_Dojo_Form_Element_TextBox('schoolName');
		$schoolName->setAttribs(array(
			'dojoType' => 'dijit.form.ValidationTextBox',
			'required' => 'true',
			'class' => 'fullside height-text',
			'placeholder' => $this->tr->translate("SCHOOL_TITLE"),
			'autoComplete' => 'false',
			'queryExpr' => '*${0}*',
			'onKeyup' => 'checkDuplicateRecord();',
			'missingMessage' => $this->tr->translate("Forget Enter School Name")
		));
		
		$description =  new Zend_Form_Element_Textarea('description');
		$description->setAttribs(array(
			'dojoType' => 'dijit.form.Textarea',
			'class' => 'fullside',
			'style' => 'font-family: inherit;  min-height:100px !important;'
		));

		$_arr = array(1 => $this->tr->translate("ACTIVE"), 0 => $this->tr->translate("DEACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'required' => 'true',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
		));
		$id = new Zend_Form_Element_Hidden('id');
		
		if (!empty($data)) {
			$schoolName->setValue($data["schoolName"]);
			$_status->setValue($data["status"]);
			$id->setValue($data["id"]);
		}
		$this->addElements(array(
			$schoolName,
			$description,
			$_status,
			$id,
		
		));
		return $this;
	}
}
