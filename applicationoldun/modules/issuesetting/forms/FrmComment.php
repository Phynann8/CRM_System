<?php
class Issuesetting_Form_FrmComment extends Zend_Dojo_Form
{
	protected $tr;
	protected $tvalidate; //text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	protected $textarea = null;
	//protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_date = 'dijit.form.DateTextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->text = 'dijit.form.TextBox';
		$this->textarea = 'dijit.form.Textarea';
	}
	public function FrmCmt($data = null)
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$_dbgb = new Application_Model_DbTable_DbGlobal();

		$_arr_opt = array(""=>$this->tr->translate("SELECT_TYPE"));
    	$Option = $_dbgb->getViewByType(36);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$commentType = new Zend_Dojo_Form_Element_FilteringSelect("commentType");
    	$commentType->setMultiOptions($_arr_opt);
    	$commentType->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'missingMessage'=>'Invalid Module!',
			'class'=>'fullside',
		));

		$comment = new Zend_Dojo_Form_Element_TextBox('comment');
		$comment->setAttribs(array('dojoType' => $this->tvalidate, 'required' => 'true', 'class' => 'fullside',));

		$id = new Zend_Form_Element_Hidden("id");
		
		if (!empty($data)) {
			$comment->setValue($data['comment']);
			$commentType->setValue($data['commentType']);
			$id->setValue($data['id']);
		}
		$this->addElements(array(
			$comment
			,$commentType
			, $id
			
		));
		return $this;
	}
	
}
