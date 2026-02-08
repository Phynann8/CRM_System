<?php

class Scan_Form_FrmScanSetting extends Zend_Dojo_Form
{
	protected  $tr;
	protected $filter;

	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->filter = 'dijit.form.FilteringSelect';
	}
	function FrmAddScanSetting($data)
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$typeItems = empty($typeItems) ? 1 : $typeItems;
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_dbuser = new Application_Model_DbTable_DbUsers();
		$userid = $_dbgb->getUserId();
		$userinfo = $_dbuser->getUserInfo($userid);

		$_arr_opt_branch = array("" => $this->tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if (!empty($optionBranch)) foreach ($optionBranch as $row) $_arr_opt_branch[$row['id']] = $row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'required' => 'true',
			'autoComplete' => 'false',
			'queryExpr' => '*${0}*',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
		));
		if (count($optionBranch) == 1) {
			$_branch_id->setAttribs(array('readonly' => 'readonly'));
			if (!empty($optionBranch)) foreach ($optionBranch as $row) {
				$_branch_id->setValue($row['id']);
			}
		}
		

		$title = new Zend_Dojo_Form_Element_TextBox('title');
		$title->setAttribs(array(
			'dojoType' => 'dijit.form.ValidationTextBox',
			'required' => 'true',
			'class' => 'fullside height-text',
			'placeholder' => $this->tr->translate("TITLE"),
			'autoComplete' => 'false',
			'queryExpr' => '*${0}*',
			'missingMessage' => $this->tr->translate("Forget Enter Title")
		));

		$titleEn = new Zend_Dojo_Form_Element_TextBox('titleEn');
		$titleEn->setAttribs(array(
			'dojoType' => 'dijit.form.ValidationTextBox',
			'required' => 'true',
			'class' => 'fullside height-text',
			'placeholder' => $this->tr->translate("TITLE_EN"),
			'autoComplete' => 'false',
			'queryExpr' => '*${0}*',
			'missingMessage' => $this->tr->translate("Forget Enter Title")
		));

		$type = new Zend_Dojo_Form_Element_FilteringSelect("type");
		$type->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'required' => 'true',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
		));
		$_arr = array(
			'' => $this->tr->translate("SELECT_TYPE"),
			1 => $this->tr->translate("COME"),
			2=> $this->tr->translate("LEAVE")
		);
		$type->setMultiOptions($_arr);
		
		$fromTime = new Zend_Dojo_Form_Element_TimeTextBox('fromTime');
		$fromTime->setAttribs(array(
			'placeholder' => $this->tr->translate("FROM_TIME"),
			'dojoType' => "dijit.form.TimeTextBox",
			'class' => 'fullside',
			'required' => 'true',
		));
	

		$toTime = new Zend_Dojo_Form_Element_TimeTextBox('toTime');
		$toTime->setAttribs(array(
			'placeholder' => $this->tr->translate("TO_TIME"),
			'dojoType' => "dijit.form.TimeTextBox",
			'class' => 'fullside',
			'required' => 'true',
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
		
		$_arr = array(1 => $this->tr->translate("MORNING"), 2 => $this->tr->translate("AFTERNOON"));
		$_shift = new Zend_Dojo_Form_Element_FilteringSelect("shift");
		$_shift->setMultiOptions($_arr);
		$_shift->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'required' => 'true',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
		));
		
		$condictionTime = new Zend_Dojo_Form_Element_TimeTextBox('condictionTime');
		$condictionTime->setAttribs(array(
			'placeholder' => $this->tr->translate("condictionTime"),
			'dojoType' => "dijit.form.TimeTextBox",
			'class' => 'fullside',
			'required' => 'true',
		));

		$id = new Zend_Form_Element_Hidden('id');

		$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
		$advance_search->setAttribs(array(
			'dojoType' => 'dijit.form.TextBox',
			'class' => 'fullside height-text',
			'placeholder' => $this->tr->translate("SEARCH_HERE"),
			'missingMessage' => $this->tr->translate("SEARCH_HERE")
		));
		$advance_search->setValue($request->getParam("advance_search"));

		$_arr = array(-1 => $this->tr->translate("ALL"), 1 => $this->tr->translate("ACTIVE"), 0 => $this->tr->translate("DEACTIVE"));
		$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
		$_status_search->setMultiOptions($_arr);
		$_status_search->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
		));
		$_status_search->setValue($request->getParam("status_search"));

		$exam_from_date = new Zend_Dojo_Form_Element_DateTextBox('exam_from_date');
		$exam_from_date->setAttribs(array(
			'placeholder' => $this->tr->translate("FROM_DATE"),
			'dojoType' => "dijit.form.DateTextBox",
			'value' => 'now',
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'class' => 'fullside',
		));
		$date = date("Y-m-d");
		$exam_from_date->setValue($date);

		$exam_end_date = new Zend_Dojo_Form_Element_DateTextBox('exam_end_date');
		$exam_end_date->setAttribs(array(
			'placeholder' => $this->tr->translate("END_DATE"),
			'dojoType' => "dijit.form.DateTextBox",
			'value' => 'now',
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'class' => 'fullside',
		));
		$date = date("Y-m-d");
		$exam_end_date->setValue($date);

		$from_date = new Zend_Dojo_Form_Element_DateTextBox('from_date');
		$from_date->setAttribs(array(
			'placeholder' => $this->tr->translate("FROM_DATE"),
			'dojoType' => "dijit.form.DateTextBox",
			'value' => 'now',
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'class' => 'fullside',
		));
		$date = date("Y-m-d");
		$from_date->setValue($date);

		$start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
			'placeholder' => $this->tr->translate("START_DATE"),
			'dojoType' => "dijit.form.DateTextBox",
			'value' => 'now',
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'class' => 'fullside',
		));
		$_date = $request->getParam("start_date");
		$start_date->setValue($_date);

		$end_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(array(
			'placeholder' => $this->tr->translate("END_DATE"),
			'dojoType' => "dijit.form.DateTextBox",
			'class' => 'fullside',
			'constraints' => "{datePattern:'dd/MM/yyyy'}",
			'required' => false
		));
		$_date = $request->getParam("end_date");
		if (empty($_date)) {
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);

		$_arr_opt_branch = array("" => $this->tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if (!empty($optionBranch)) foreach ($optionBranch as $row) $_arr_opt_branch[$row['id']] = $row['name'];
		$_branch_search = new Zend_Dojo_Form_Element_FilteringSelect("branch_search");
		$_branch_search->setMultiOptions($_arr_opt_branch);
		$_branch_search->setAttribs(array(
			'dojoType' => 'dijit.form.FilteringSelect',
			'required' => 'true',
			'autoComplete' => 'false',
			'queryExpr' => '*${0}*',
			'missingMessage' => 'Invalid Module!',
			'class' => 'fullside height-text',
		));
		if (count($optionBranch) == 1) {
			$_branch_search->setAttribs(array('readonly' => 'readonly'));
			if (!empty($optionBranch)) foreach ($optionBranch as $row) {
				$_branch_search->setValue($row['id']);
			}
		}
		$_branch_search->setValue($request->getParam("branch_search"));
		

		if (!empty($data)) {
			$_branch_id->setValue($data["branchId"]);
			$titleEn->setValue($data["titleEn"]);
			$title->setValue($data["title"]);
			$type->setValue($data["type"]);
			$fromTime->setValue("T".$data["fromTime"]);
			$toTime->setValue("T".$data["toTime"]);
			$condictionTime->setValue("T".$data["condictionTime"]);
			$_shift->setValue($data["shift"]);
			$_status->setValue($data["status"]);
			$id->setValue($data["id"]);
		
		}
		$this->addElements(array(
			$_branch_id,
			$title,
			$titleEn,
			$type,
			$fromTime,
			$toTime,
			$_status,
			$_shift,
			$condictionTime,
			$id ,

			$advance_search,
			$_branch_search,
			$start_date,
			$end_date,
			$_status_search

		
		));
		return $this;
	}
}
