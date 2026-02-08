<?php

class Foundation_Form_FrmChangeProgram extends Zend_Dojo_Form
{
	protected  $tr;
	protected $filter;
	protected $textarea=null;

	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->filter = 'dijit.form.FilteringSelect';
		$this->textarea = 'dijit.form.Textarea';
	}
	function FrmChangeProgram($data = null)
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$_dbgb = new Application_Model_DbTable_DbGlobal();

		$_arr_opt_branch = array("" => $tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if (!empty($optionBranch)) foreach ($optionBranch as $row) $_arr_opt_branch[$row['id']] = $row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'required' => 'true',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
			'queryExpr' => '*${0}*',
			'autoComplete' => 'false'
		));
		if (count($optionBranch) == 1) {
			$_branch_id->setAttribs(array('readonly' => 'readonly'));
			if (!empty($optionBranch)) foreach ($optionBranch as $row) {
				$_branch_id->setValue($row['id']);
			}
		}
		
		$_academicYear = new Zend_Dojo_Form_Element_FilteringSelect('academicYear');
		$_academicYear->setAttribs(array(
			'dojoType' => $this->filter,
			'placeholder' => $this->tr->translate("SERVIC"),
			'class' => 'fullside',
			'required' => false,
			'queryExpr' => '*${0}*',
			'autoComplete' => 'false',
		));
		$rows =  $_dbgb->getAllAcademicYear();

		$opt = array();
		array_unshift($rows, array('id' => '', 'name' => $this->tr->translate("SELECT_YEAR")));
		if (!empty($rows)) foreach ($rows as $row) $opt[$row['id']] = $row['name'];
		$_academicYear->setMultiOptions($opt);

		$chagneDate = new Zend_Dojo_Form_Element_DateTextBox('chagneDate');
		$date = date("Y-m-d");
		$chagneDate->setAttribs(array(
			'data-dojo-Type' => "dijit.form.DateTextBox",
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'class' => 'fullside',
			'required' => true
		));
		$chagneDate->setValue($date);

		$note = new Zend_Dojo_Form_Element_Textarea('note');
		$note->setAttribs(array(
			'dojoType' => $this->textarea, 'class' => 'fullside',
			'style' => 'min-height: 65px !important;',
		));

		
		$status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$opt_status = array(
			1 => $tr->translate('ACTIVE'),
			0 => $tr->translate('DEACTIVE'),
		);
		$status->setMultiOptions($opt_status);
		$status->setAttribs(array(
			'dojoType' => $this->filter,
			'class' => 'fullside',
		));

		$id = new Zend_Form_Element_hidden('id');
		$id->setAttribs(array(
			'dojoType' => "dijit.form.TextBox",
			'class' => 'fullside'
		));

		if (!empty($data)) {
			$_branch_id->setValue($data['branch_id']);
			$_branch_id->setAttribs(array('readonly' => 'readonly'));
			$_academicYear->setValue($data['academicYear']);
			$chagneDate->setValue($data['chagneDate']);
			$note->setValue($data['note']);
			$id->setValue($data['id']);
			$status->setValue($data['status']);
		}
		$this->addElements(array(
			$_branch_id,
			$_academicYear,
			$chagneDate,
			$note,
			$id,
			$status,
		));
		return $this;
	}
}
