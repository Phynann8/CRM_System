<?php

class Application_Form_FrmGlobal
{
	public function getReceiptFooter()
	{
		$_dbmodel = new Application_Model_DbTable_DbKeycode();
		$keycode = $_dbmodel->getKeyCodeMiniInv(TRUE);
		$str = "";
		$str .= "<tr bgcolor='6D5CDD'>";
		$str .= '<td colspan="4" style="text-align: center; color:#fff;background:#6D5CDD;">';
		$brachs = explode('/', $keycode['footer_branch']);
		$str .= '<ul style="list-style-type: none;float:left; text-align: left;padding-left:10px;">';
		foreach ($brachs as $key => $branch) {
			$str .= "<li> $branch;</li>";
		}
		$str .= '</ul>';
		$phones = explode('/', $keycode['foot_phone']);
		$str .= '<ul style="list-style-type: none;float:left;text-align: left;padding-left:10px;">';
		foreach ($phones as $key => $phone)
			$str .= "<li> $phone </li>";
		$str .= '</ul>';
		$contacts = explode('/', $keycode['f_email_website']);
		$str .= '<ul style="list-style-type: none;float:left;text-align: left;padding-left:10px;">';
		foreach ($contacts as $key => $contact) {
			$str .= "<li> $contact </li>";
		}
		$str .= '</ul>';
		$str .= '</td>';
		$str .= '</tr>';
		return $str;
	}
	public function getHeaderReceipt($branch_id = null, $forGenerate = 0)
	{
		$key = new Application_Model_DbTable_DbKeycode();
		$setting = $key->getKeyCodeMiniInv(TRUE);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str = "";

		if ($forGenerate == 1) {
			$baseUrl = PUBLIC_PATH;
			$styleLogo = "width:120px;";
		} else {
			$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
			$styleLogo = "max-width: 98%;max-height:90px;min-height:50px;";
		}
		$img = 'logo.png';

		$logo = $baseUrl . '/images/Logo/Logo.png';
		if (!empty($setting['logo'])) {
			if (file_exists(PUBLIC_PATH . "/images/logo/" . $setting['logo'])) {
				$logo = $baseUrl . '/images/logo/' . $setting['logo'];
			}
		}

		$school_khname = $tr->translate('SCHOOL_NAME');
		$school_name = $tr->translate('CUSTOMER_BRANCH_EN');
		$address = $tr->translate('CUSTOMER_ADDRESS');
		$tel = $tr->translate('CUSTOMER_TEL');
		$email =  $tr->translate('CUSTOMER_EMAIL');
		$website = $tr->translate('CUSTOMER_WEBSITE');
		if ($branch_id == null) {

			$school_khname = $tr->translate('SCHOOL_NAME');
			$school_name = $tr->translate('CUSTOMER_BRANCH_EN');
			$address = $tr->translate('CUSTOMER_ADDRESS');
			$tel = $tr->translate('CUSTOMER_TEL');
			$email =  $tr->translate('CUSTOMER_EMAIL');
			$website = $tr->translate('CUSTOMER_WEBSITE');
		} else {
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getBranchInfo($branch_id);
			if (!empty($rs)) {

				if (!empty($rs['photo'])) {
					if (file_exists(PUBLIC_PATH . "/images/logo/" . $rs['photo'])) {
						$logo = $baseUrl . '/images/logo/' . $rs['photo'];
					}
				}
				$school_khname = $rs['school_namekh'];
				$school_name = $rs['school_nameen'];

				$address = $rs['br_address'];
				$tel = $rs['branch_tel'];
				$email = $rs['email'];
				$website = $rs['website'];
			}
		}

		if ($setting['show_header_receipt'] == 1) {
			$str = "<table style='width:100%;white-space:nowrap;position: absolute;'>
				<tr>
					<td style='width:22%;' valign='top'>
						&nbsp;<img style='" . $styleLogo . "' src=" . $logo . ">
					</td>
					<td valign='top' style='width:48%;font-size:11px;line-height: 18px;font-family: Khmer OS Battambang;' >
						<div style='font-size:16px;margin-top: 10px;line-height:25px;font-family:Khmer OS Muol Light'>" . $school_khname . "</div>
						<div style='font-size:14px;font-family:Times New Roman'>" . $school_name . "</div>
						<div style='font-size:10px;line-height: 10px;margin-top: 2px;max-width:100%;white-space:pre-line;'>" . $address . "</div>
					</td>
					<td valign='top' style='width:30%;font-size:10px;line-height: 15px;font-family: Khmer OS Battambang;' >
						<div style='line-height: 16px;'>&nbsp;</div>
						<div style='line-height: 16px;'>" . $tel . "</div>
						<div style='line-height: 16px;'>" . $email . "</div>
						<div style='line-height: 16px;'>" . $website . "</div>
					</td>
				</tr>
			</table>";
		}
		return $str;
	}
	function getLetterHeaderReport($branch_id)
	{
		$db = new Application_Model_DbTable_DbGlobal();
		if (empty($branch_id)) {
			$optionBranch = $db->getAllBranch();
			if (count($optionBranch) == 1) {
				if (!empty($optionBranch)) foreach ($optionBranch as $row) {
					$branch_id = $row['id'];
				}
			} else {
				$branch_id = 1;
			}
		}
		$rs = $db->getBranchInfo($branch_id);
		//$logo = Zend_Controller_Front::getInstance()->getBaseUrl() . '/images/logo/' . $rs['photo'];
		
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$logo = $baseUrl . '/images/Logo/Logo.png';
		if (!empty($rs['logo'])) {
			if (file_exists(PUBLIC_PATH . "/images/logo/" . $rs['logo'])) {
				$logo = $baseUrl . '/images/logo/' . $rs['logo'];
			}
		}
		$color = empty($rs['color']) ? "#000" : "#" . $rs['color'];
		$type_header = HEADER_REPORT_TYPE;
		$str = "";


		$email_icon = Zend_Controller_Front::getInstance()->getBaseUrl() . '/images/icon/email.png';
		$global_icon = Zend_Controller_Front::getInstance()->getBaseUrl() . '/images/icon/global.png';
		$home_icon = Zend_Controller_Front::getInstance()->getBaseUrl() . '/images/icon/home.png';

		if ($type_header == 1) {
			$str = "
		<style>
				table{
				color:" . $color . "
				}
		</style>
		<table width='100%'>
				<tr>
					<td width='20%' align='center'>
						<img style='max-height:100px;' src=" . $logo . "><br>
					</td>
					<td width='80%' valign='top'>
						<div class='schoo-headkh' style='text-align: center;'>
							<h2 style=" . '"' . "padding: 0;margin: 0; font-family: 'Times New Roman','Khmer OS Muol Light';font-size:12px;background: $color;color: #fff;" . '"' . ">" . $rs['school_namekh'] . "</h2>
						</div>
						<table width='100%' >
							<tr>
								<td width='60%' align='center' valign='top'>
									<h2 style='white-space:nowrap; font-weight:bold; font-size:12px; padding: 0;margin: 0; font-family: Times New Roman , Khmer OS Muol; color: #000;'>" . $rs['school_nameen'] . "</h2>
								</td>
								<td width='40%' align='left' valign='top' style='white-space:nowrap;font-size: 12px;line-height: 14px;font-family: Times New Roman , Khmer OS Battambang;'>
									Contacts: " . $rs['branch_tel'] . "<br />
									<span style='visibility: hidden;'>Contacts: </span>" . $rs['branch_tel1'] . "
								</td>
							</tr>
						</table>
						<div class='schoo-add' style='text-align: center; font-size: 13px;font-family: Times New Roman , Khmer OS Battambang;'>
							 " . $rs['br_address'];
			if (!empty($rs['email'])) {
				$str .= ", E-mail: " . $rs['email'];
			}
			if (!empty($rs['website'])) {
				$str .= ", Website: " . $rs['website'];
			}
			$str .= "
						</div>
					</td>
				</tr>
		</table>
		";
		$str .='<table class="tablerBorderLine" width="100%"><tbody><tr class="line"><td colspan="3"></td></tr></tbody></table>';
		
		} else if ($type_header == 2) {
			$str = '
				<style>
					table{
					color:".$color."
					}
					span.space {
						padding:0;
						padding-right: 10px;
						margin:0;
							line-height: inherit;
					}
					img.icon-head {
						width: 12px;
						filter: sepia(100%) hue-rotate(190deg) saturate(500%);
					}
					ul.headReport,
					ul.reportTitle{
						margin: 0;
						padding: 0;
						list-style: none;
					}
					ul.headReport li span,
					ul.headReport li{
						line-height: 12px;
						text-align:left; 
						font-size:10px;
						font-family:' . '"Times New Roman"' . ',' . '"Khmer OS Muol Light"' . ';
						
					}
					ul.headReport li.small-text,
					ul.headReport li.small-text span{
						line-height: 14px;
						text-align:center; 
						font-size:11px;
						font-family:' . '"Times New Roman"' . ',' . '"Khmer OS Battambang"' . ';
						
					}
					</style>
			';
			$str .= "
			
				
			
			<table class='tableTop' width='100%'>
					<tr>
						<td width='20%' align='center'>
							<img style='max-height:65px;' src=" . $logo . "><br>
						</td>
						<td width='80%' valign='top'>
							<table width='100%' >
								<tr>
									<td width='60%' align='left' valign='top'>
										<h2 style=" . '"' . "padding: 0;margin: 0; font-weight:normal; font-family: 'Times New Roman' , 'Khmer OS Muol Light';font-size:10px; color: inherit;" . '"' . ">" . $rs['school_namekh'] . "</h2>
										<h2 style='white-space:nowrap; font-weight:bold; font-size:10px; padding: 0;margin: 0; font-family: Times New Roman , Khmer OS Muol; color: #inherit;'>" . $rs['school_nameen'] . "</h2>
									</td>
									<td width='40%' align='left' valign='top'>
										<ul class='headReport'>
											<li><span class='space'>&#9742;</span> " . $rs['branch_tel'] . "</li>";
			if (!empty($rs['email'])) {
				$str .= "<li><span class='space'>&#128386;</span> " . $rs['email'] . "</li>";
			}
			if (!empty($rs['website'])) {
				$str .= "<li><span class='space'>🌐</span> " . $rs['website'] . "</li>";
			}
			$str .= "<li><span class='space'>&#127988;</span> " . $rs['br_address'] . "</li>
										</ul>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					</table>";
					$str .='<table class="tablerBorderLine" width="100%"><tbody><tr class="line"><td colspan="3"></td></tr></tbody></table>';
		} else if ($type_header == 3) {
			$str = "
			<style>
				span.space {
					padding:0;
				    padding-right: 10px;
				    margin:0;
				        line-height: inherit;
				}
			</style>
			<table width='100%' class='tableTop'>
				<tr>
					<td width='100%' valign='center' style='vertical-align: middle;  display: flex;    align-items: center;' >
						<img style='max-height: 55px;display: inline-block;margin-right: 10px;' src=" . $logo . ">
						<div style='display: inline-block;width:90%;vertical-align: middle;'>
							<h2 style='padding: 0;margin: 0; font-weight:normal; font-family: Times New Roman , Khmer OS Muol Light;font-size:12px; color: inherit;'>" . $rs['school_namekh'] . "</h2>
							<h2 style='white-space:nowrap; font-weight:bold; font-size:12px; padding: 0;margin: 0; font-family: Times New Roman , Khmer OS Muol; color: #inherit;'>" . $rs['school_nameen'] . "</h2>
						</div>
					</td>
				</tr>
			</table>";
		} else if ($type_header == 4) {
			$str = '
		<style>
			tr.line td {
				    display: none !important;
			}
		</style>
		<table width="100%" style="white-space:nowrap;">
			<tbody>
				<tr>
					<td width="25%" valign="top">
						<img style="max-width: 98%;max-height:140px;  margin-top:25px;" src="' . $logo . '">
					</td>
					<td width="35%" valign="top" style="font-size:11px;line-height: 18px;font-family: Khmer OS Battambang;">
					</td>
					<td width="40%" valign="top" style="font-family: ' . "'Times New Roman'" . ',' . "'Khmer OS Muol Light'" . ';">
						
					</td>
				</tr>
			</tbody>
		</table>
		';
		}
		return $str;
	}

	function getLeftLogo($branch_id)
	{

		$db = new Application_Model_DbTable_DbGlobal();
		if (empty($branch_id)) {
			$optionBranch = $db->getAllBranch();
			if (count($optionBranch) == 1) {
				if (!empty($optionBranch)) foreach ($optionBranch as $row) {
					$branch_id = $row['id'];
				}
			} else {
				$branch_id = 1;
			}
		}
		$rs = $db->getBranchInfo($branch_id);
		$logo = Zend_Controller_Front::getInstance()->getBaseUrl() . '/images/logo/' . $rs['photo'];
		$color = empty($rs['color']) ? "" : "#" . $rs['color'];
		$str = "
			<style>
				span.space {
					padding:0;
					padding-right: 10px;
					margin:0;
					line-height: inherit;
				}
			</style>
			<table width='100%' style='white-space:nowrap;'>
				<tr>
					<td width='20%' align='left'>
						<img style='width:80%' src=" . $logo . "><br>
					</td>
					<td width='60%' valign='top'>
						<h2 style='margin: 0; font-weight:normal; font-family: Times New Roman , Khmer OS Muol Light;font-size:11px; color: inherit;padding: 5px 0px 5px 0px;'>" . $rs['school_namekh'] . "</h2>
						<h2 style='white-space:nowrap; font-weight:bold; font-size:10px; margin: 0; font-family: Times New Roman , Khmer OS Muol; color: #inherit;'>" . $rs['school_nameen'] . "</h2>
					</td>
					<td width='20%' >
					</td>
				</tr>
			</table>
		";
		return $str;
	}
	function getFooterAccount($footerType = 1, $spacing = 1, $font_size = "12px", $font_family = "Times New Roman,Khmer OS Muol Light;")
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str = "<table width='100%' style='font-size: $font_size;font-family:$font_family'>";
		for ($i = 1; $i <= $spacing; $i++) {
			$str .= "<tr><td>&nbsp;</td></tr>";
		}
		if ($footerType == 1) { //Account General
			$footerAccountType=1;//PSIS CHV
			if($footerAccountType==1){
				$str .= "	
				<tr>
					<td width='25%' align='center' style='font-family:$font_family'>
						<span>" . $tr->translate('checkedBy') . "</span>
					</td>
					<td width='25%' align='center' style='font-family:$font_family'>
						<span>" . $tr->translate('receivedBy') . "</span>
					</td>
					<td width='25%' align='center' style='font-family:$font_family'>
						<span>" . $tr->translate('chiefCashier') . "</span>
					</td>
					<td width='25%' align='center' style='font-family:$font_family'>
						<span>" . $tr->translate('cashier') . "</span>
					</td>
				</tr>";
			}else{ // General
				$str .= "	
				<tr>
					<td width='25%' align='center'>
						<span>" . $tr->translate('APPROVED_BY') . "</span>
					</td>
					<td width='50%' align='center'>
						<span>" . $tr->translate('VERIFIED_BY') . "</span>
					</td>
					<td width='25%' align='center'>
						<span>" . $tr->translate('PREPARED_BY') . "</span>
					</td>
				</tr>";
			}
		} else if ($footerType == 2) { //Foundation General
			$str .= '
					<tr>
						<td width="35%" align="center">
							<span style="font-size: 14px;font-family:' . "'" . 'Times New Roman' . "'" . ',' . "'" . 'Khmer OS Battambang' . "'" . ';">' . $tr->translate("CHECKANDAPPROVED") . '</span><br />
							<span style="font-size: 14px;font-family:' . "'" . 'Times New Roman' . "'" . ',' . "'" . 'Khmer OS Muol Light' . "'" . ';">' . $tr->translate("PRINCIPAL") . '</span>
						</td>
						<td width="30%">&nbsp;</td>
						<td width="35%" align="center">
							<span style=" font-family:' . "'" . 'Times New Roman' . "'" . ',' . "'" . 'Khmer OS Battambang' . "'" . ';">' . $tr->translate("CREATE_WORK_DATE") . '</span><br />
							<span style=" font-family:' . "'" . 'Times New Roman' . "'" . ',' . "'" . 'Khmer OS Muol Light' . "'" . ';">' . $tr->translate("PREPARED_BY") . '</span>
						</td>
					</tr>
				';
		}
		$str .= "	</table>";
		return $str;
	}

	function getFormatReceipt()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		$last_name = $session_user->last_name;
		$username = $session_user->first_name;
		$receipt_type = RECEIPT_TYPE;
		if ($receipt_type == 1) { //elt
			$str = "<style>
				.hearder_table{height:20px !important;}
				.defaulheight{line-height:10px !important;}
				.bold{
					font-weight:bold;
				}
				.blogbranchlogo{
						margin:0 auto;position:absolute;top:10px !important;left:100px;
					}
			</style>
			<div id='PrintReceipt' style='width:100% !important; padding: 0px;'>
				<style>
					.noted{
					    white-space: pre-wrap;     
						word-wrap: break-word;      
						word-break: break-all;
						white-space: pre;
						font:12px 'Khmer OS Battambang';
						border: 1px solid #000;
	                    line-height:20px;
						font-weight: normal !important;
						padding:2px;
					    white-space: normal;
					}
					.blogbranchlogo{
						margin:0 auto;position:absolute;top:10px;left:100px;
					}
					.boxnorefund{
						color: #fff;
	    				background: #d42727;
	    				border: 2px solid fff;
	    				font-size: 11px;
	    				padding:10px 2px;
	   	 				border-radius: 2px;
	    				border: 6px double #fff;
	    				font-weight:bold !important;
	    				font-family:Times New Roman;	
					}
					#printfooter {
					    position: absolute;
					    bottom: 0;
					    position: fixed;
					    display: block ;
					    width:100%;
					}
					table{ border-collapse:collapse; margin:0 auto;
								border-color:#000;font-size:12px; }
					@page {
					  margin:0.5cm 1cm 0.3cm 1cm; '
					   page-break-before: avoid;
					   /*size: 21cm 14.8cm; */
					}
					   
				</style>
				<table  width='100%'  class='print' cellspacing='0'  cellpadding='0' style='font-family:Khmer OS Battambang,Times New Roman !important; font-size:11px !important; margin-top: -5px;white-space:nowrap;'>
					<tr>
						<td align='center' valign='top' colspan='3'>
							<label id='lbl_header'></label>
						</td>
					</tr>
					<tr>
						<td width='30%' style='position:relative'>
							<div id='lbl_branchlogo'></div>
							<div class='blogbranchlogo' style='font-family:Khmer OS Muol Light;font-size:12px;'>
								<label id='lb_branchname'></label>
								<div style='line-height:10px;'><label id='lb_branchnameen'></label></div>
							</div>
						</td>
						<td align='center' valign='bottom' width='40%'>
							<div style='font-family:Khmer OS Muol Light;line-height:15px;font-size:12px;position:relative'>បង្កាន់ដៃបង់ប្រាក់</div>
							<div style='font-family:Times New Roman;font-size:12px;font-weight:bold'>Official Receipt</div>
						</td>
						<td width='30%'>&nbsp;</td>
					</tr>
					<tr>
						<td align='center' valign='bottom' colspan='3'>
							<table  width='100%' style='font-size: 11px;line-height:12px !important;margin-bottom:10px;'>
								<tr>
									<td width='12%'>Student ID/Test ID </td>
									<td width='18%'> : &nbsp;<label id='lb_stu_id' class='one bold'></label></td>
									<td><div style='font-family: Times New Roman'>Academic Year	</div></td>
									<td> : &nbsp;<label id='lb_academic_year' class='one'>&nbsp;</label>
									<td width='15%'><div style='font-size: 12px;font-family:Times New Roman;'><u>Receipt N<sup>o</sup></u></div></td>
									<td width='15%'> : &nbsp;<label id='lb_receipt_no'></label></td>
									<td  width='15%'><div style='border:1px solid #000;margin:0 auto;position:absolute;top:35px;width:70px;height:85px;right:0.2cm'><label id='lb_photo'></label></div></td>
								</tr>
								<tr>
									<td>Student Name</td>
									<td colspan='1'> : &nbsp;<label id='lb_name' class='one bold'></label></td>
									<td><div style='font-family: Times New Roman'>Session Type</div></td>
									<td> : &nbsp;<label id='lb_sesiontype' class='one'>&nbsp;</label></td>
									<td><div style='font-size: 12px;font-weight: bold;font-family: Times New Roman'>Pay Date</div></td>
									<td> : &nbsp;<label id='lb_date' class='one bold'></label></td>
								</tr>
								<tr>
									<td>Gender </td>
									<td> : &nbsp;<label id='lb_sex' class='one bold'></label></td>
									<td><div style='font-family: Times New Roman'>Grade	</div></td>
									<td style='white-space: nowrap;'> : &nbsp;<label id='lb_grade' class='one'>&nbsp;</label>
									<td>Print Date</td>
									<td> : &nbsp;" . date('d-m-Y g:i A') . "</td>
								</tr>
								<tr>
									<td>Tel</td>
									<td> : &nbsp;<label id='lb_phone' class='one bold'></label><label id='lb_session' class='one bold'></label><label id='lb_study_year' class='one bold'></label></td>
									<td>Class</td>
									<td> : &nbsp;<label id='lb_group' class='one'>&nbsp;</td>
									<td>Print By :</td>
									<td> : &nbsp;" . $username . "</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan='3'><div id='t_amountmoneytype'></div></td>
					</tr>
					<tr>
						<td valign='top' style='font-size:10px;'>Note
							<div style='width:99%;float: left;'>
							 	<div style='font-size:10px;min-height:70px;border:1px solid #000;' id='lbl_note' class='noted' ></div>
							 </div>
						</td>
						<td valign='top' style='font-size:10px;'>
							Say in US Dollars
							<div style='font-size:10px;min-height: 70px;border:1px solid #000;' id='lb_read_khmer' class='noted' ></div>
						</td>
						<td>
							<table width='98%' style='margin-left:4px;marin-top:5px;font-size:inherit; white-space:nowrap;line-height:12px;border-collapse:collapse;'>
								<tr>
									<td>Penalty</td>
									<td>: $</td>
									<td align='right'>&nbsp;&nbsp; <label id='lb_fine'></label></td>
								</tr>
								<tr>
									<td>Total Payment</td>
									<td>: $</td>
									<td align='right' style='font-weight: bold;font-family:Times New Roman;'>&nbsp;&nbsp; <label id='lb_total_payment'></label></td>
								</tr>
								<tr>
									<td><div>Credit Memo</div></td>
									<td>: $</td>
									<td align='right'>&nbsp;&nbsp; <label id='lb_credit_memo'></label></td>
								</tr>
								<tr>
									<td><div><strong>Paid Amount</strong></div></td>
									<td>: $</td>
									<td align='right' style='font-weight: bold;font-family:Times New Roman;'>&nbsp;&nbsp;<strong><label id='lb_paid_amount'></label></strong></td>
								</tr>
								<tr>
									<td><div>Balance</div></td>
									<td>: $</td>
									<td align='right'>&nbsp;&nbsp;<label id='lb_balance_due'></label></td>
								</tr>
								<tr>
									<td><div>Payment Method</div></td>
									<td></td>
									<td align='right'>&nbsp;&nbsp;<label id='lb_paymentmethod'></label></td>
								</tr>
								<tr>
									<td><div>Number/Bank</div></td>
									<td></td>
									<td align='right'>&nbsp;&nbsp;<label id='lb_paymentnumber'></label></td>
								</tr>
								<tr>
									<td colspan='3'><div class='boxnorefund'>Non-Refundable / Transferable</div></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td valign='top' colspan='3'>
							<table class='defaulheight' width='100%' border='0' style='font-family: Khmer OS Battambang,Times New Roman;font-size:12px;white-space:nowrap;margin-top:-5px;line-height: 11px;'>
								<tr>
									<td colspan='5'>
										<table width='100%' style='marin-top:5px;font-size:12px; white-space:nowrap;line-height:15px;border-collapse:collapse;'>
											<tr>
												<td align='center'>Cashier</td>
												<td align='center'>Head of Cashier</td>
												<td align='center'>Customer</td>
											</tr>
											<tr>
												<td align='center'>
													<div style='font-size:10px;border-bottom: 1px solid #000;margin-top:40px;'><label id='lb_byuser'></label>";
												$str .= "</div>
													Signature/Name/Date
												</td>
												<td align='center' valign='bottom'>
													<div style='border-bottom: 1px solid #000;width:85%;margin:0 auto;'></div>
													Signature/Name/Date
												</td>
												<td align='center' valign='bottom'>
													<div style='border-bottom: 1px solid #000;width:85%;margin:0 auto;'></div>
													Signature/Name/Date
												</td>
											</tr>
										</table>
									</td>
									<td valign='top'>
									</td>
								</tr>
							</table>
						</td>
					</tr>
			        <tr>
					    <td valign='top' colspan='3'>
						    <div id='printfooter' style='display:block;font-family:khmer os battambang'>
				        		<table width='100%' style='background: #fff;border-top: 2px solid #000;font-family: 'Times New Roman','Khmer OS Battambang'; font-size:8px;line-height: 12px;white-space:nowrap;'> 
									<tr style='text-align:center;white-space:nowrap;line-height: 15px;font-size:7px !important;font-family: 'Times New Roman','Khmer OS Battambang'>
										<td width='100%'>&#9742; <label id='lbl_branchphone' style='width:20%;display:in-line;'></label> &#9993; <label id='lbl_email' style='width:20%;display:in-line;'></label> &#127758 <label id='lbl_website'style='width:20%;display:in-line;'></label> &#127963 <label id='lbl_address' style='font-family:'Times New Roman,Khmer OS Battambang !important'></label> </td>
									</tr>
								</table>
				        	</div>
			        	</td>
					</tr>
				</table>
				<div class='no_display'>
					<span id='lb_grade'></span>
					<span id='lb_academic_year'></span>
					
					<span id='lbParentName' >&nbsp;</span>
					<span id='lbParentPhone' >&nbsp;</span>
					<span class='brContactInfo' >&nbsp;</span>
				</div>
			</div>
			";
			$key = new Application_Model_DbTable_DbKeycode();
			$result = $key->getKeyCodeMiniInv(TRUE);
			if ($result['receipt_print'] > 1) {
				$str .= "<div id='divPrint1'>
				<div style='border:1px dashed #000; vertical-align: middle;margin:10px 0px 10px 0px'></div>
				<div id='printblog2'></div>
				</div>";
			}
			return $str;
		} elseif ($receipt_type == 2) { //psis chv
			defined('NEW_STU_ID_FROM_TEST') || define('NEW_STU_ID_FROM_TEST', Setting_Model_DbTable_DbGeneral::geValueByKeyName('new_stuid_test')); //0=default,1=show stu_id register to enter
			defined('SHOW_GROUP_INPAYMENT') || define('SHOW_GROUP_INPAYMENT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('show_groupin_payment'));
			defined('AMOUNT_RECEIPT') || define('AMOUNT_RECEIPT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('receipt_print'));
			defined('SHOW_PIC_INRECEIPT') || define('SHOW_PIC_INRECEIPT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('show_pic_receipt'));
			defined('PADDINGTOP_RECEIPT') || define('PADDINGTOP_RECEIPT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('receipt_paddingtop'));
			defined('SHOW_HEADER_RECEIPT') || define('SHOW_HEADER_RECEIPT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('show_header_receipt'));
			
			$dbGStt = new Setting_Model_DbTable_DbGeneral();
			$rsReceiptDescTitle = $dbGStt->geLabelByKeyName('receiptDescTitle');
			$rsReceiptDesc = $dbGStt->geLabelByKeyName('receiptDesc');
			
			$receiptDescTitle = empty($rsReceiptDescTitle["keyValue"]) ? "" : $rsReceiptDescTitle["keyValue"];
			$receiptDescTitleEn = empty($rsReceiptDescTitle["keyValueEn"]) ? "" : $rsReceiptDescTitle["keyValueEn"];
			
			$receiptDesc = empty($rsReceiptDesc["keyValue"]) ? "" : $rsReceiptDesc["keyValue"];
			$receiptDescEn = empty($rsReceiptDesc["keyValueEn"]) ? "" : $rsReceiptDesc["keyValueEn"];
			
			$paddingTop = PADDINGTOP_RECEIPT . 'px';
			$showPic = SHOW_PIC_INRECEIPT;
			$showPic = ($showPic == 1) ? 'display:block;' : 'display:none;';
			$settingAmtReceipt = AMOUNT_RECEIPT;
			$pageSetup = ($settingAmtReceipt == 1) ? 'page:A5;' : 'page:A4;';
			
			
			$settingFor = 100; //PSIS CHV
			if($settingFor==100){
				//margin-bottom : 7.5cm;
				//size:A4 portrait; 
				
			}
//
			$showReport = (SHOW_HEADER_RECEIPT == 1) ? 'visibility:visible' : 'visibility:hidden';
			$str = "<style>
					.defaulheight{line-height:10px !important;}
					.bold{
						font-weight:bold;
					}
						@page {
							margin: 0;
							padding:0;
						}
						
						.half-page{
							height: calc(50%);
							padding:0mm 10mm;
							box-sizing: border-box;
							display: flex;
							flex-direction: column;
							justify-content: flex-start;
						}
					
						.print{
							font-family:Khmer OS Battambang,Times New Roman !important;
							font-size:10px !important;
							white-space:nowrap;
						 }
						.tableDetail{
							table-layout: fixed;
							border-collapse: collapse;
							margin:0 auto;	
							width:100%;
							font-size:10px;
							font-family: Khmer OS Battambang,Times New Roman !important;
					    }	
						.bordered{
							border:1px solid #000;
						}
						.wt-tdrowNo{
							width:30px;
						}
						.wt-td-record{
							width:65px;
						}
						.wt-td40{
							width:40px;
						}
						.bold{
							font-weight:bold;
						}
						.center{
							text-align:center;
						}
						.textleft{
							text-align:left;
						}	
						.p-r5{
							padding-right:5px;
						}
						.p-r3{
							padding-right:3.3px;
						}
						.separateblog{
							display:flex;
 							width: 95%;
							gap: 10px; 
						}
						.school_tuition {
							width: 45%; 
							padding: 2px;
							border-right: 1px solid black;
							text-align: center;
						}
						.seperatenote{
							width: 55%; 
							padding: 2px;
						}

						.noted{
							white-space: pre-wrap;
							word-wrap: break-word;
							word-break: break-all;
							white-space: pre;
							font:12px 'Khmer OS Battambang';
							border: 1px solid #000;
							line-height:15px;
							font-weight: normal !important;
							padding:2px;
							white-space: normal;
							width:95%;
						}
						.notedDescription{
							white-space: pre-wrap;
							word-wrap: break-word;
							word-break: break-all;
							white-space: pre;
							font:10px 'Khmer OS Battambang';
							line-height:15px;
							font-weight: normal !important;
							white-space: normal;
							width:200px;
							padding:2px;
						}
						.descriptionDetail{
							white-space: pre-wrap;
							word-wrap: break-word;
							word-break: break-all;
							white-space: pre;
							font:10px 'Khmer OS Battambang';
							line-height:15px;
							font-weight: normal !important;
							white-space: normal;
							padding:2px;
						}
						table.print{ 
								border-collapse:collapse; margin:0 auto;
								border-color:#000;
								line-height:14px;
			 			}
						.blogbranchlogo{
							margin:0 auto;position:absolute;top:10px;left:100px;
						}
						.boxnorefund{
							color: #fff;
							background: #d42727;
							border: 2px solid fff;
							padding:10px 2px;
							border-radius: 2px;
							border: 6px double #fff;
							font-weight:bold !important;
							font-family:Times New Roman;
						}
						.hearder_table small{
							display:block;
							line-height:10px;
						}
						table.tableDetail{
							border-collapse: collapse; border: solid 1px #000; line-height:22px;
							font-family: Khmer OS Battambang;
							text-align:center;
						}
						table tr.hearder_table{
								background:#f2f2f2;
								font-weight:bold;
								line-height:22px;
								text-align:center;
								white-space:nowrap;
						}
						.spanBlog{
							display:block !important;
							line-height:12px;
						}
						.print label{
							margin-bottom: 0px !important;
						}
							

						@media print{
							html, body {
								maring:0;
								padding:0;
							}
							@page{
								$pageSetup
								page-break-before: avoid;
								@top-right {
									content:'';
								}
								@top-center {
									content:'';
								}
								@bottom-left {
									content:'';
								}
								
							}
							a{text-decoration:none;color:#000;}
							
							.footerNote {
								width: 100%;
							}
						}
						
						.no_display{
							display: none;
						}
						.display-top{position:relative;vertical-align: top}
						.display-top div{ position:absolute;}
						.smallsize{font-size:10px;}
						.lbl_periodstudy ul{
							display: flex;
							flex-direction: column;
							gap: 5px;
							/*flex-direction: column;
							
							margin-left:-40px;*/
						}
						.lbl_periodstudy ul li{
							list-style:none;
							line-height:9px;
							display: flex;
							justify-content: space-between;
							list-style-position: inside; 
						}
						.displayinline{
						white-space:nowrap;padding:1px;
						font-size:10px;
						display:inline-block;}
						.aligncenter{text-align:center;}
						.all-border{border:1px solid #000;}
						.textright{text-align:right;}
						.paddingright{padding-right:1px;}
						
						div.lbl_periodstudy {
							display: flex;
							/*justify-content: center; */
						}
						div.lbl_periodstudy span.terminf {
							line-height: inherit;
							display: inline-block;
						}
						div.lbl_periodstudy span.terminf.titleTerm {
							width: 100%;
						}						
						div.lbl_periodstudy span.terminf.termValidePmt {
							width: 50%;
							text-align: right;
							padding-left: 10px;
						}
						.footerNote{
							width:100%;
						}
						.footerNote table.noteTable td {
							vertical-align: top;
						}
						.footerNote table.noteTable p {
							white-space: pre-wrap;
							font-size: 8px;
							margin: 0;
							line-height: 12px;
						}
						.footerNote table.noteTable p.desc-en {
							font-size: 12px;
						}
						.footerNote table.noteTable strong {
							font-size: 8px;
							font-family: inherit;
							font-weight: 600;
							margin: 0;
							line-height: 12px;
						}
						.footerNote .footerAddress {
							font-size: 8px;
							margin: 0;
							line-height: 12px;
							margin-top: 10px;
							border-top: solid 2px #000;
							padding-top: 5px;
							font-family:"."'Times New Roman'".","."'Khmer OS Battambang'"." !important;
							text-align: left;
						}
					
				</style>
					<div id='PrintReceipt' style='width:100% !important; padding: 0px;'>
						<div class='half-page'>
							<table broder='1' width='100%' class='print' cellspacing='0'  cellpadding='0'>
								<thead>
									<tr colspan='4' style='height:$paddingTop'>
										<td id='lbl_header'></td>
									</tr>
									<tr>
										<td colspan='4'></td>
										<td  class='smallsize'>លេខបង្កាន់ដៃ/Receipt No : <strong><label id='lb_receipt_no'></label></strong></td>
									</tr>
									<tr>
										<td colspan='4'></td>
										<td class='smallsize'>ថ្ងៃបង់ប្រាក់/Pay Date : <label id='lb_date' class='one bold'></label></td>
									</tr>
									<tr>
										<td colspan='5' align='center' valign='bottom'>
											<div style='font-family:Khmer OS Muol Light;line-height:15px;font-size:11px;position:relative;'><u>បង្កាន់ដៃបង់ប្រាក់</u></div>
										</td>
									</tr>
									<tr>
										<td colspan='5' align='center' valign='bottom'>
											<div style='font-family:Times New Roman;font-size:11px;font-weight:bold'>Official Receipt</div>
										</td>
									</tr>
									<tr>
										<td width='18%'></td>
										<td width='22%'></td>
										<td></td>
										<td width='18%'></td>
										<td width='22%'></td>
									</tr>
									<tr>
										<td>អត្តលេខ,Student ID/Test ID</td>
										<td> : &nbsp;<label id='lb_stu_id' class='one bold'></label></td>
										<td></td>
										<td>ឆ្នាំសិក្សា/Academic Year</td>
										<td>: <label id='lb_academic_year' class='one'>&nbsp;</label></td>
									</tr>
									<tr>
										<td style='vertical-align: top;'>គោត្តនាម-នាម</td>
										<td> : &nbsp;<label id='lb_name' class='one bold' style='display: inline-block; vertical-align: top;' ></label></td>
										<td></td>
										<td>ពេលសិក្សា/Study Time</td>
										<td> : &nbsp;<label id='lb_fee_type' class='one bold'></label></td>
									</tr>
									<tr>
										<td>Full Name</td>
										<td> : &nbsp;<label id='lb_namelatin' class='one' style='display: inline-block; vertical-align: top;'></label></td>
										<td></td>
										
										<td>ថ្នាក់/Class</td>
										<td style='white-space: nowrap;'>: <label id='lb_group' class='one'></label><label id='lb_grade' class='one'>&nbsp;</label></td>
									</tr>
								</thead>
								<tr>
									<td colspan='5' style='padding-top:10px;'><div id='t_amountmoneytype'></div></td>
								</tr>
								<tr>
									<td colspan='3'></td>
									<td class='textright bold'><div><strong>សរុបត្រូវបង់/Total Payment :</strong></div></td>
									<td class='textright p-r3 bold' style='font-family:Times New Roman;border-bottom:1px solid #000;'>
										<table width='100%' style='font-size:12px !important;'>
											<tr>
												<td class='textleft' width='30%'>&nbsp;$</td>
												<td width='70%' class='textright'><strong><label id='lb_paid_amount'></label></strong></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td valign='top' colspan='3' style='font-size:10px;'>
										<div class='separateblog'>
											<div class='school_tuition' style='font-size:8px;min-height:60px;border:1px solid #000;'  >
													<div class='displayinline aligncenter bold' style='margin:auto;'>ថ្ងៃត្រូវបង់ថ្លៃសិក្សា/Schedule Tuition Due</div>
													<div id='lbl_period_year' class='lbl_periodstudy' ></div>
											</div>
											<div class='seperatenote' style='min-height:60px;border:1px solid #000;'  class='notedDescription' >
												<div class='displayinline'>សម្គាល់/Note</div>
												<div id='lbl_note'><div>
											</div>
										</div>
									</td>
									<td class='display-top textright p-r5'><div style='right:1px'>បង់ជា/Payment Method :</div></td>
									<td class='display-top p-r5'>&nbsp;&nbsp;<label id='lb_paymentmethod'></label>&nbsp;<label id='lb_paymentnumber' style='white-space: normal;'></label></td>
								</tr>
								<tr>
									<td colspan='3'></td>
									<td align='right' class='p-r5'><strong>អ្នកទទួល/Received By : </strong></td>
									<td class='center'><div style='border-bottom: 1px solid #000;margin-top:15px;'></div>
										<label id='lb_byuser'></label>
									</td>
								</tr>
								<tr>
									<td colspan='5'>";
										$str.='<div class="footerNote no_display">
											<table class="noteTable" broder="0" width="100%" cellspacing="0"  cellpadding="0" style="font-family:'."'Times New Roman'".','."'Khmer OS Battambang'".' !important;  white-space:nowrap;" >
												<tr>
													<td width="45%">
														<strong>'.$receiptDescTitle.'</strong>
														<p>'.$receiptDesc.'</p>
													</td>
													<td width="10%">
													</td>
													<td width="45%">
														<strong>'.$receiptDescTitleEn.'</strong>
														<p class="desc-en">'.$receiptDescEn.'</p>
													</td>
												</tr>
											</table>
											<div class="footerAddress">ទីតាំងនៅក្នុង៖ <span class="brContactInfo">បុរីពិភពថ្មី: KB Tel: 088 788 7979, 096 269 7888, 023 984 777</span></div>
										</div>';
									$str.="</td>
								</tr>
							</table>
							<div class='no_display'>
								<div id='printfooter' style='display:block;font-family:khmer os battambang;position: absolute; bottom:0px;width:100%'>
									<table style='width:100%;margin-top:10px;background: #fff;border-top: 1px solid #000;font-family: 'Times New Roman','Khmer OS Battambang'; font-size:8px;line-height: 12px;white-space:nowrap;'>
										<tr style='text-align:center;white-space:nowrap;line-height: 15px;font-size:7px !important;font-family: 'Times New Roman','Khmer OS Battambang'>
											<td >&#9742; <label id='lbl_branchphone' style='width:20%;display:in-line;'></label> &#9993; <label id='lbl_email' style='width:20%;display:in-line;'></label> &#127758 <label id='lbl_website'style='width:20%;display:in-line;'></label> &#127963 <label id='lbl_address' style='font-family:'Times New Roman,Khmer OS Battambang !important'></label> </td>
										</tr>
										
									</table>
									<div style='float:right;border:1px solid #000;width:70px;height:85px;text-align:right; $showPic '></div>
									<div style='font-size:10px;min-height:40px;border:1px solid #000;' id='lb_read_khmer' class='noted' ></div>
									<label id='lbl_header'></label>
									
									<label id='lb_total_payment'></label>
									<label id='lb_study_year' class='one bold'></label>
									<label id='lb_fee_title' class='one bold'></label>
									<label id='lb_session' class='one bold'></label>
									<label id='lb_part_time' class='one'>&nbsp;</label>
									<label id='lb_sex' class='one bold'></label>
									<label id='lb_phone' class='one bold'></label>
									<label id='lb_photo'></label>
									<label id='lb_balance_due'></label>
									<span id='lbParentName' >&nbsp;</span>
									<span id='lbParentPhone' >&nbsp;</span>
									<span id='lbFather' style='display: inline-block; vertical-align: top; line-height: 16px;'></span><br />
									<span id='lbFatherTel'></span>
									<span id='lbMother' style='display: inline-block; vertical-align: top; line-height: 16px;'></span><br />
									<span id='lbMotherTel'></span>
									<div id='lbl_branchlogo'></div>
									<div class='blogbranchlogo' style='font-family:Khmer OS Muol Light;font-size:10px;'>
									<label id='lb_branchname'></label>
									<label id='lb_fine'></label>
									<label id='lb_credit_memo'></label>
									<div style='line-height:10px;'><label id='lb_branchnameen'></label></div>
								</div>
							</div>
						</div>
					</div>";
			
			if ($settingAmtReceipt > 1) {
				$str .= "<div id='divPrint1'>
							<div id='printblog2'></div>
						</div>";
			}
			
			return $str;
		} elseif ($receipt_type == 3) { //psis​ first
			defined('NEW_STU_ID_FROM_TEST') || define('NEW_STU_ID_FROM_TEST', Setting_Model_DbTable_DbGeneral::geValueByKeyName('new_stuid_test')); //0=default,1=show stu_id register to enter
			defined('SHOW_GROUP_INPAYMENT') || define('SHOW_GROUP_INPAYMENT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('show_groupin_payment'));
			defined('AMOUNT_RECEIPT') || define('AMOUNT_RECEIPT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('receipt_print'));
			defined('SHOW_PIC_INRECEIPT') || define('SHOW_PIC_INRECEIPT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('show_pic_receipt'));
			defined('PADDINGTOP_RECEIPT') || define('PADDINGTOP_RECEIPT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('receipt_paddingtop'));
			defined('SHOW_HEADER_RECEIPT') || define('SHOW_HEADER_RECEIPT', Setting_Model_DbTable_DbGeneral::geValueByKeyName('show_header_receipt'));

			$paddingTop = PADDINGTOP_RECEIPT . 'px';
			$showPic = SHOW_PIC_INRECEIPT;
			$showPic = ($showPic == 1) ? 'display:block;' : 'display:none;';
			$settingAmtReceipt = AMOUNT_RECEIPT;
			$pageSetup = ($settingAmtReceipt == 1) ? 'page:A5;size:landscape;' : 'page:A4;size:portrait;';

			$showReport = (SHOW_HEADER_RECEIPT == 1) ? 'visibility:visible' : 'visibility:hidden';

			$str = "<style>
					.hearder_table{height:20px !important;}
					.defaulheight{line-height:10px !important;}
					.bold{
						font-weight:bold;
					}
					.blogbranchlogo{
						margin:0 auto;position:absolute;top:10px !important;left:100px;
					}
				</style>
				<div id='PrintReceipt' style='width:100%cm !important; padding: 0px;'>
					<style>
						.print{
							font-family:Khmer OS Battambang,Times New Roman !important;
							font-size:10px !important;
							white-space:nowrap;
						 }
						.tableDetail{
							table-layout: fixed;
							border-collapse: collapse;
							margin:0 auto;	
							width:100%;
							font-size:10px;
							font-family: Khmer OS Battambang,Times New Roman !important;
					    }	
						.bordered{
							border:1px solid #000;
						}
						.wt-tdrowNo{
							width:30px;
						}
						.wt-td-record{
							width:60px;
						}
						.wt-td-note-record{
							width:120px;
						}
						.wt-td40{
							width:40px;
						}
						.bold{
							font-weight:bold;
						}
						.center{
							text-align:center;
						}
						.textleft{
							text-align:left;
						}	
						.p-r5{
							padding-right:5px;
						}
						.p-r3{
							padding-right:3.3px;
						}
						.separateblog{
								display:flex;
								width: 95%;
								gap: 10px; 
						}
						.school_tuition {
							width: 45%; 
							padding: 2px;
							border-right: 1px solid black;
							text-align: center;
						}
						.seperatenote{
							width: 55%; 
							padding: 2px;
						}
							
						.noted{
							white-space: pre-wrap;
							word-wrap: break-word;
							word-break: break-all;
							white-space: pre;
							font:12px 'Khmer OS Battambang';
							border: 1px solid #000;
							line-height:15px;
							font-weight: normal !important;
							padding:2px;
							white-space: normal;
							width:95%;
						}
						.notedDescription{
							white-space: pre-wrap;
							word-wrap: break-word;
							word-break: break-all;
							white-space: pre;
							font:12px 'Khmer OS Battambang';
							line-height:15px;
							font-weight: normal !important;
							white-space: normal;
							width:200px;
							padding:2px;
						}
						.descriptionDetail{
							white-space: pre-wrap;
							word-wrap: break-word;
							word-break: break-all;
							white-space: pre;
							font:10px 'Khmer OS Battambang';
							line-height:15px;
							font-weight: normal !important;
							white-space: normal;
							padding:2px;
						}
						table.print{ 
								border-collapse:collapse; margin:0 auto;
								border-color:#000;
								line-height:18px;
			 			}
						.blogbranchlogo{
							margin:0 auto;position:absolute;top:10px;left:100px;
						}
						.boxnorefund{
							color: #fff;
							background: #d42727;
							border: 2px solid fff;
							font-size: 10px;
							padding:10px 2px;
							border-radius: 2px;
							border: 6px double #fff;
							font-weight:bold !important;
							font-family:Times New Roman;
						}
						.hearder_table small{
							display:block;
							line-height:10px;
						}
						table.tableDetail{
							border-collapse: collapse; border: solid 1px #000; line-height:22px;
							font-family: Khmer OS Battambang;
							text-align:center;
						}
						table tr.hearder_table{
								background:#f2f2f2;
								font-weight:bold;
								line-height:22px;
								text-align:center;
								white-space:nowrap;
						}
						.spanBlog{
							display:block !important;
							line-height:12px;
						}
						.print label{
							margin-bottom: 0px !important;
						}
						@media print{
							@page{
								margin:0cm 0.7cm 0cm 0.7cm;
								$pageSetup
								page-break-before: avoid;
								transform: scale(0.5);
								@top-right {
									content:'';
								}
								@top-center {
									content:'';
								}
								@bottom-left {
									content:'';
								}
							}
						a{text-decoration:none;color:#000;}
						}
						#page {
						   border-collapse: collapse;
						}
						#page td {
						   padding: 0; 
						   margin: 0;
						}
						.no_display{
							display: none;
						}
						.textright{text-align:right;}
						.paddingright{padding-right:1px;}
					</style>
					<table width='100%' class='print' cellspacing='0'  cellpadding='0' style='font-family:Khmer OS Battambang,Times New Roman !important;  white-space:nowrap;'>
						<tr style='height:$paddingTop'>
							<td id='lbl_header' align='center' valign='top' style='" . $showReport . "' colspan='5'>
							</td>
						</tr>
						<tr>
							<td width='20%'></td>
							<td width='20%'>&nbsp;</td>
							<td align='center' valign='bottom'>
								<div style='font-family:Khmer OS Muol Light;line-height:15px;font-size:11px;position:relative'>បង្កាន់ដៃបង់ប្រាក់</div>
							</td>
							<td width='20%'>&nbsp;លេខបង្កាន់ដៃ/Receipt No</td>
							<td width='15%'><strong style='font-size:12px;'><label id='lb_receipt_no'></label></strong></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td align='center' valign='bottom'>
								<div style='font-family:Times New Roman;font-size:11px;font-weight:bold'>Official Receipt</div>
							</td>
							<td>&nbsp;ថ្ងៃបង់ប្រាក់/Pay Date</td>
							<td><label id='lb_date' class='one bold'></label></td>
						</tr>
						<tr>
							<td>អត្តលេខ,Student ID/Test ID</td>
							<td> : &nbsp;<label id='lb_stu_id' class='one bold'></label></td>
							<td></td>
							<td><span class='spanBlog'>&nbsp;Print By : " . $username . "</span></td>
							<td><span class='spanBlog'>Print Date:" . date('d-m-Y g:i') . "</span></td>
						</tr>
						<tr>
							<td style='vertical-align: top;'>គោត្តនាម-នាម</td>
							<td> : &nbsp;<label id='lb_name' class='one bold' style='display: inline-block; vertical-align: top;' ></label></td>
							<td>ឆ្នាំសិក្សា/Academic Year</td>
							<td>: <label id='lb_academic_year' class='one'>&nbsp;</label></td>
							<td rowspan='4' valign='top'>
								<div style='float:right;border:1px solid #000;width:70px;height:85px;text-align:right; $showPic '>
									<label id='lb_photo'></label>
								</div>
							</td>
						</tr>
						<tr>
							<td>Family Name-Name</td>
							<td> : &nbsp;<label id='lb_namelatin' class='one bold' style='display: inline-block; vertical-align: top;'></label></td>
							<td>ថ្នាក់/Class</td>
							<td style='white-space: nowrap;'>: <label id='lb_grade' class='one'>&nbsp;</label></td>
						</tr>
						<tr>
							<td>ភេទ/Gender </td>
							<td> : &nbsp;<label id='lb_sex' class='one bold'></label></td>
							<td>ថ្នាកទី/Grade/Level</td>
							<td valign='top'>: &nbsp;<label id='lb_group' class='one'>&nbsp;</td>
						</tr>
						<tr>
							<td>លេខទូរសព្ទ/Tel</td>
							<td> : &nbsp;<label id='lb_phone' class='one bold'></label><label id='lb_session' class='one bold'></label><label id='lb_study_year' class='one bold'></label></td>
							<td>ប្រភេទ/Type </td>
							<td> : &nbsp;<label id='lb_part_time' class='one'>&nbsp;</label></td>
						</tr>
					<tr>
						<td colspan='5'><div id='t_amountmoneytype'></div></td>
					</tr>
					<tr>
						<td rowspan='3' valign='top' style='font-size:10px;'>សម្គាល់/Note
							<div style='width:99%;float: left;'>
								<div style='font-size:10px;min-height:40px;border:1px solid #000;' id='lbl_note' class='noted' ></div>
							</div>
						</td>
						<td rowspan='3' colspan='2' valign='top' style='font-size:10px;' >
							ទឹកប្រាក់ជាអក្សរ/Say in US Dollars
							<div style='font-size:10px;min-height:40px;border:1px solid #000;' id='lb_read_khmer' class='noted' ></div>
						</td>
						<td>ត្រូវបង់/Total Payment</td>
						<td align='right' style='font-weight: bold;font-family:Times New Roman;'>&nbsp;&nbsp; <label id='lb_total_payment'></label></td>
					</tr>
					<tr>
						<td>ប្រាក់សល់មុន/Credit Memo</td>
						<td align='right'>&nbsp;&nbsp; <label id='lb_credit_memo'></label></td>
					</tr>
					<tr>
						<td><div style='font-weight: bold;font-size:11px;'><strong>បានបង់/Paid Amount</strong>: $</div></td>
						<td align='right' style='font-weight: bold;font-family:Times New Roman;font-size:12px;'>&nbsp;&nbsp; <strong><label id='lb_paid_amount'></label></strong></td>
					</tr>
					<tr>
						<td align='center'><strong>បេឡាករ/Cashier</strong></td>
						<td align='center'>ប្រធានបេឡា/Head of Cashier</td>
						<td align='center'>អតិថិជន/Customer</td>
						<td><div>ជំពាក់/Balance</div></td>
						<td align='right'>&nbsp;&nbsp;<label id='lb_balance_due'></label></td>
					</tr>
					<tr>
						<td colspan='3'></td>
						<td><div>បង់ជា/Payment by</div></td>
						<td align='right'>&nbsp;&nbsp;<label id='lb_paymentmethod'></label></td>
					</tr>
					<tr>
						<td align='center'>
							<div style='font-size:10px;border-bottom: 1px solid #000;margin-top:15px;'><label id='lb_byuser'></label>";
			$str .= "</div>
							Signature/Name/Date
						</td>
						<td align='center' valign='bottom'>
							<div style='border-bottom: 1px solid #000;width:85%;margin:0 auto;'></div>
							Signature/Name/Date
						</td>
						<td align='center' valign='bottom'>
							<div style='border-bottom: 1px solid #000;width:85%;margin:0 auto;'></div>
							Signature/Name/Date
						</td>
						<td valign='top'><div>Bank Name</div></td>
						<td align='right' valign='top'>&nbsp;&nbsp;<label id='lb_paymentnumber'></label></td>
					</tr>
			</table>
				<div class='no_display'>
					<div id='printfooter' style='display:block;font-family:khmer os battambang;position: absolute; bottom:0px;width:100%'>
						<table style='width:100%;margin-top:10px;background: #fff;border-top: 1px solid #000;font-family: 'Times New Roman','Khmer OS Battambang'; font-size:8px;line-height: 12px;white-space:nowrap;'>
							<tr style='text-align:center;white-space:nowrap;line-height: 15px;font-size:7px !important;font-family: 'Times New Roman','Khmer OS Battambang'>
								<td >&#9742; <label id='lbl_branchphone' style='width:20%;display:in-line;'></label> &#9993; <label id='lbl_email' style='width:20%;display:in-line;'></label> &#127758 <label id='lbl_website'style='width:20%;display:in-line;'></label> &#127963 <label id='lbl_address' style='font-family:'Times New Roman,Khmer OS Battambang !important'></label> </td>
							</tr>
						</table>
						<label id='lb_fee_title' class='one bold'></label>
						<label id='lb_fee_type' class='one bold'></label>
						<span id='lbParentName' >&nbsp;</span>
						<span id='lbParentPhone' >&nbsp;</span>
						<label id='lb_fee_title' class='one bold'></label>
						<span id='lbFather' style='display: inline-block; vertical-align: top; line-height: 16px;'></span><br />
						<span id='lbFatherTel'></span>
						<span id='lbMother' style='display: inline-block; vertical-align: top; line-height: 16px;'></span><br />
						<span id='lbMotherTel'></span>
											
						<div id='lbl_branchlogo'></div>
						<div class='blogbranchlogo' style='font-family:Khmer OS Muol Light;font-size:12px;'>
						<label id='lb_branchname'></label>
						<label id='lb_fine'></label>
						<div style='line-height:10px;'><label id='lb_branchnameen'></label></div>
						</div>
					</div>
					<span class='brContactInfo' >&nbsp;</span>
				</div>
			</div>";
			if ($settingAmtReceipt > 1) {
				$str .= "<div id='divPrint1'>
				<div style='vertical-align: middle;margin:10px 0px 10px 0px'></div>
				<div id='printblog2'></div>
				</div>";
			}
			return $str;
		} elseif ($receipt_type == 4) { //A4 Receipt
			$str = "<style>
					.hearder_table{height:20px !important;}
					.defaulheight{line-height:10px !important;}
					.bold{
						font-weight:bold;
					}
					.blogbranchlogo{
						margin:0 auto;position:absolute;top:10px !important;left:100px;
					}
				</style>
				<div id='PrintReceipt' style='width:100%cm !important; padding: 0px;'>
					<style>
						.noted{
							white-space: pre-wrap;
							word-wrap: break-word;
							word-break: break-all;
							white-space: pre;
							font:12px 'Times New Roman','Khmer OS Battambang';
							border: 1px solid #000;
							line-height:20px;
							font-weight: normal !important;
							padding:2px;
							white-space: normal;
						}
						table{ border-collapse:collapse; margin:0 auto;
								border-color:#000;font-size:10px;font-family:'Times New Roman','Khmer OS Battambang'; }
						.blogbranchlogo{
							margin:0 auto;position:absolute;top:10px;left:100px;
						}
						.boxnorefund{
							color: #fff;
							background: #d42727;
							border: 2px solid fff;
							font-size: 11px;
							padding:10px 2px;
							border-radius: 2px;
							border: 6px double #fff;
							font-weight:bold !important;
							font-family:'Times New Roman','Khmer OS Battambang';
						}
						@page {
							/* Chrome sets own margins, we change these printer settings */
							margin:0.5cm 1cm 0.3cm 1cm; '
							page-break-before: avoid;
							/*size: 21cm 14.8cm; */
						}
						.no_display{
							display: none;
						}
						div#lbl_branchlogo img {
							max-width: 265px !important;
							max-height: initial !important;
						}
						table.headTable{
							border: solid 1px #000; 
							font-size: 11px;
							line-height:10px !important;
							width:100%;
							border-collapse: collapse;
						}
						
						table.headTable td{
							padding: 2px 4px;
							line-height: 16px;
						}
						table.headTable td.bgHead {
							background: #519fe2;
							line-height: 16px;
							font-weight: 600;
							padding: 6px 4px;
							vertical-align: top;
						}
						
						tr.hearder_table {
							background: #acd8fe;
							font-weight: bold;
							border-top: none;
						}
						tr.hearder_table td {
							padding: 6px 4px;
							text-align: center;
							border-top: none;
							font-size: inherit !important;
						}
						
						table.tableDetail {
							margin-top: -1px !important;
						}
						ul.termCondition {
							padding: 0;
							margin: 0;
							list-style: none;
							font-family:'Times New Roman','Khmer OS Battambang';
							margin-top: 5px;
						}
						li.headUl {
							font-weight: 600;
							text-decoration: underline;
							font-size: 11px;
						}
						
						table.totalFooter {
							font-size: inherit;
							white-space: nowrap;
							line-height: 10px;
							border-collapse: collapse;
							    margin-top: -1px;
						}
						table.totalFooter td {
							padding: 2px;
						}
						table.totalFooter td.titleLb {
							background: #519fe2;
							padding: 4px 2px;
							font-weight: 600;
							line-height: 13px;
							
						}
						
						.footerAddress {
							display: block;
							width: 100%;
							text-align: center;
							background: #519fe2;
							padding: 10px;
							position: absolute;
							bottom: 0;
						}
					</style>
					<table width='100%'  class='print' cellspacing='0'  cellpadding='0' style='height:13.97cm; font-family:'Times New Roman','Khmer OS Battambang' !important;  white-space:nowrap;'>
						<tr>
							<td colspan='3' style='position:relative'>
								<div style='height:100px;'></div>
							</td>
						</tr>
						<tr>
							<td width='30%' style='position:relative'>
								<div class='no_display'>
								<div id='lbl_branchlogo'></div>
									<div class='blogbranchlogo' style='font-family:Khmer OS Muol Light;font-size:12px;'>
									<label id='lb_branchname'></label>
									<div style='line-height:10px;'><label id='lb_branchnameen'></label></div>
									</div>
								</div>
							</td>
							<td align='center' valign='top' width='40%' >
								<div style='font-family:Khmer OS Muol Light;line-height:15px;font-size:12px;position:relative'>វិក្ក័យបត្រ</div>
								<div style='font-family:Times New Roman;font-size:12px;font-weight:bold'>INVOICE</div>
							</td>
							<td width='30%'>&nbsp;</td>
						</tr>
						<tr>
							<td colspan='3' style='position:relative'>
							</td>
						</tr>
						<tr>
						<td align='center' valign='bottom' colspan='3'>
							<table class='headTable' border='1' >
								<tr>
									<td class='bgHead'>អតិថិជន / Customer</td>
									<td class='bgHead'>Students ID</td>
									<td rowspan='2' class='bgHead'>
										កាលបរិច្ឆេទ <br />
										Date:
									</td>
									<td rowspan='2'><label id='lb_date' class='one bold'></label></td>
								</tr>
								<tr>
									<td style='border-bottom: none; border-right: none;'><span id='lb_name' class='one bold' style='display: inline-block; vertical-align: top; line-height: 16px;' ></span></td>
									<td style='border-bottom: none; border-left: none; text-align: center;'><span id='lb_stu_id' class='one bold'></span></td>
								</tr>
								<tr>
									<td style='border-top: none; ' colspan='2'><span id='lb_phone' class='one bold' style='display: inline-block; vertical-align: top; line-height: 13px;' ></span></td>
									<td rowspan='2' class='bgHead'>
										លេខវិក្ក័យបត្រ <br />
										Invoice No.
									</td>
									<td rowspan='2'><span id='lb_receipt_no' class='one bold'></span></td>
								</tr>
								<tr>
									<td class='bgHead' colspan='2'>អ្នកជាអាណាព្យាបាល / Parent's Name</td>
								</tr>
								<tr>
									<td style='border-bottom: none;border-right:none;'>
											<span id='lbFather' style='display: inline-block; vertical-align: top; line-height: 16px;'></span><br />
											<span id='lbFatherTel'></span>
									</td>
									<td style='border-bottom: none;border-left:none;'>
											<span id='lbMother' style='display: inline-block; vertical-align: top; line-height: 16px;'></span><br />
											<span id='lbMotherTel'></span>
									</td>
									<td rowspan='2' class='bgHead'>
										កាលបរិច្ឆេទបោះពុម្ភ<br />
										Print Date:
									</td>
									<td rowspan='2'>
									" . date('d-m-Y g:i A') . "<br />
									ដោយ / By: <span class='bold'>" . $username . "</span>
									</td>
								</tr>
								<tr>
									<td style='border-top: none;' colspan='2'><span id='lbParentPhone' style='display: inline-block; vertical-align: top; line-height: 16px;'></span></td>
								</tr>
							</table>
							<div class='no_display'>
								<span id='lb_academic_year' class='one'>&nbsp;</span>
								<span id='lb_grade' class='one'>&nbsp;</span>
								<span id='lb_sesiontype' class='one'>&nbsp;</span>
								<div style='border:1px solid #000;margin:0 auto;position:absolute;top:35px;width:70px;height:85px;right:0.2cm'>
									<span id='lb_photo'></span>
								</div>
								
								<span id='lb_sex' class='one bold'></span>
								<span id='lb_session' class='one bold'></span>
								<span id='lb_study_year' class='one bold'></span>
								<span id='lb_group' class='one'></span>
								
								Note
								<div style='width:99%;float: left;'>
									<div style='font-size:10px;min-height:70px;border:1px solid #000;' id='lbl_note' class='noted' ></div>
								</div>
								
								Say in US Dollars
								<div style='font-size:10px;min-height: 70px;border:1px solid #000;' id='lb_read_khmer' class='noted' ></div>
						
							</div>
							
						</td>
					</tr>
					<tr>
						<td colspan='3'><div id='t_amountmoneytype'></div></td>
					</tr>
					<tr>
						<td colspan='2' valign='top' style='font-size:10px;'>
							<ul class='termCondition'>
								<li class='headUl'>
									គោលការណ៍ និងលក្ខខណ្ឌ / Term of Condition:
								</li>
								<li>
									1). ទឹកប្រាក់ដែលបានបង់ហើយមិនអាចផ្ទេរ ឬដកវិញបានទេ<br />
									All fees are not transferable or refundable.
								</li>
								<li>
									2). ទឹកប្រាក់ដែលបានបង់ហើយមិនអាចផ្ទេរ ឬដកវិញបានទេ<br />
									All fees are not transferable or refundable.
								</li>
							</ul>
						</td>
						
						<td>
							<table class='totalFooter' border='1' width='100%' >
								<tr>
									<td class='titleLb'>
									ពិន័យ:<br />
									Penalty:
									</td>
									<td style='border-right: none;'>$</td>
									<td align='right' style='border-left: none;'>&nbsp;&nbsp; <label id='lb_fine'></label></td>
								</tr>
								<tr>
									<td class='titleLb'>
									តម្លៃសរុប:<br />
									Total Payment:</td>
									<td style='border-right: none;'>$</td>
									<td align='right' style='border-left: none;font-weight: bold;font-family:Times New Roman;'>&nbsp;&nbsp; <label id='lb_total_payment'></label></td>
								</tr>
								<tr>
									<td class='titleLb'>Credit Memo:</td>
									<td style='border-right: none;'>$</td>
									<td align='right' style='border-left: none;'>&nbsp;&nbsp; <label id='lb_credit_memo'></label></td>
								</tr>
								<tr>
									<td class='titleLb'>
										ប្រាក់បានបង់:<br />
										Paid Amount:
									</td>
									<td style='border-right: none;'>$</td>
									<td align='right' style='border-left: none; '>&nbsp;&nbsp; <strong><label id='lb_paid_amount'></label></strong></td>
								</tr>
								<tr>
									<td class='titleLb'>
									នៅខ្វះ:<br />
									Balance:</td>
									<td style='border-right: none;'>$</td>
									<td align='right' style='border-left: none;'>&nbsp;&nbsp;<label id='lb_balance_due'></label></td>
								</tr>
								<tr>
									<td class='titleLb'>
										វិធីសាស្រ្តទូទាត់:<br />
										Payment Method:
									</td>
									<td colspan='2' align='right'>&nbsp;&nbsp;<label id='lb_paymentmethod'></label></td>
								</tr>
								<tr>
									<td class='titleLb'>
										លេខសម្គាល់ / លេខគណនី:<br />
										Number/Bank No.
									</td>
									<td colspan='2' align='right'>&nbsp;&nbsp;<label id='lb_paymentnumber'></label></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td valign='top' colspan='3'>
							<br />
							<table class='defaulheight' width='100%' border='0' style='font-family: Khmer OS Battambang,Times New Roman;font-size:12px;white-space:nowrap;margin-top:50px;line-height: 11px;'>
								<tr>
									<td colspan='5'>
										<table width='100%' style='marin-top:5px;font-size:12px; white-space:nowrap;line-height:15px;border-collapse:collapse;'>
											<tr>
												<td style='width:30%' align='center'></td>
												<td style='width:30%' align='center'></td>
												<td style='width:30%' align='center'></td>
											</tr>
											
											
											<tr>
												<td align='center'>
													<div style='border-bottom: 1px solid #000;margin-top:30px;'>
														<span style='font-size:12x;' >&nbsp;</span>
													</div>
													<span style='line-height: 18px;font-size: 14px;font-weight: 600;margin-top: 5px;display: block;'>ហត្ថលេខាអតិថិជន / Customer's Signature</span>
												</td>
												<td align='center' valign='bottom'>
													
												</td>
												<td align='center' valign='bottom'>
													<div style='border-bottom: 1px solid #000;margin-top:30px;'>
														<span style='font-size:12x; font-weight: 600;'  id='lb_byuser'></span>
													</div>
													<span style='line-height: 18px;font-size: 14px;font-weight: 600;margin-top: 5px;display: block;'>Authorized Signature</span>
												</td>
											</tr>
										</table>
									</td>
									<td valign='top'>
									</td>
								</tr>
							</table>
						</td>
					</tr>
			</table>
			
				<div class='no_display'>
					<div class='footerAddress'>
					<span id='lbl_address' ></span><br />
					&#9742; <span id='lbl_branchphone' ></span>
					</div>
				
				
					<div id='printfooter' style='display:block;font-family:khmer os battambang;position: absolute; bottom:0px;width:100%'>
						<table style='width:100%;margin-top:10px;background: #fff;border-top: 1px solid #000;font-family: 'Times New Roman','Khmer OS Battambang'; font-size:8px;line-height: 12px;white-space:nowrap;'>
							<tr style='text-align:center;white-space:nowrap;line-height: 15px;font-size:7px !important;font-family: 'Times New Roman','Khmer OS Battambang'>
								<td > &#9993; <label id='lbl_email' style='width:20%;display:in-line;'></label> &#127758 <label id='lbl_website'style='width:20%;display:in-line;'></label> &#127963  </td>
							</tr>
						</table>
					</div>
				</div>
				<div class='no_display'>
					<span class='brContactInfo' >&nbsp;</span>
				</div>
			</div>";
			$key = new Application_Model_DbTable_DbKeycode();
			$result = $key->getKeyCodeMiniInv(TRUE);
			if ($result['receipt_print'] > 1) {
				$str .= "<div id='divPrint1'>
				<div style='border:1px dashed #000; vertical-align: middle;margin:10px 0px 10px 0px'></div>
				<div id='printblog2'></div>
				</div>";
			}
			return $str;
		}
	}

	public function getHeaderReportScore($branch_id = null, $forGenerate = 0)
	{
		$key = new Application_Model_DbTable_DbKeycode();
		$setting = $key->getKeyCodeMiniInv(TRUE);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str = "";

		if ($forGenerate == 1) {
			$baseUrl = PUBLIC_PATH;
			$styleLogo = "width:120px;";
		} else {
			$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
			$styleLogo = "max-width: 98%;max-height:90px;min-height:50px;";
		}

		$logo = $baseUrl . '/images/Logo/Logo.png';
		if (!empty($setting['logo'])) {
			if (file_exists(PUBLIC_PATH . "/images/logo/" . $setting['logo'])) {
				$logo = $baseUrl . '/images/logo/' . $setting['logo'];
			}
		}

		$school_khname = $tr->translate('SCHOOL_NAME');
		$school_name = $tr->translate('CUSTOMER_BRANCH_EN');
		$address = $tr->translate('CUSTOMER_ADDRESS');
		$tel = $tr->translate('CUSTOMER_TEL');
		$email =  $tr->translate('CUSTOMER_EMAIL');
		$website = $tr->translate('CUSTOMER_WEBSITE');
		if ($branch_id == null) {

			$school_khname = $tr->translate('SCHOOL_NAME');
			$school_name = $tr->translate('CUSTOMER_BRANCH_EN');
			$address = $tr->translate('CUSTOMER_ADDRESS');
			$tel = $tr->translate('CUSTOMER_TEL');
			$email =  $tr->translate('CUSTOMER_EMAIL');
			$website = $tr->translate('CUSTOMER_WEBSITE');
		} else {
			$db = new Application_Model_DbTable_DbGlobal();
			$rs = $db->getBranchInfo($branch_id);
			if (!empty($rs)) {

				if (!empty($rs['photo'])) {
					if (file_exists(PUBLIC_PATH . "/images/logo/" . $rs['photo'])) {
						$logo = $baseUrl . '/images/logo/' . $rs['photo'];
					}
				}

				$school_khname = $rs['school_namekh'];
				$school_name = $rs['school_nameen'];

				$address = $rs['br_address'];
				$tel = $rs['branch_tel'];
				$email = $rs['email'];
				$website = $rs['website'];
			}
		}

		$imgSing = "agreementsign.jpg";
		$str = '
		<table width="100%" style="white-space:nowrap;">
			<tbody>
				<tr>
					<td width="35%" valign="top"  style="text-align: left; font-family: ' . "'Times New Roman'" . ',' . "'Khmer OS Muol Light'" . ';">
						<ul style="color:#002c7b;list-style: none;padding: 0;text-align: center;line-height: 18px;font-size: 12px; margin-right: 300px; margin-top:30px; margin-bottom: 0px;">
							<li><img style="' . $styleLogo . '" src="' . $logo . '"></li>
							<li>' . $school_khname . '</li>
							<li><span style=" margin:0; padding:0; font-weight: 600; color: #002c7b;font-size: 10px; ">' . $school_name . '</span></li>
						</ul>
					</td>
					<td width="30%" valign="top" style="font-size:11px;line-height: 18px;font-family: Khmer OS Battambang;">
					</td>
					<td width="35%" valign="top" style="font-family: ' . "'Times New Roman'" . ',' . "'Khmer OS Muol Light'" . ';">
						<ul style="color:#002c7b;list-style: none;padding: 0;text-align: center;line-height: 18px;margin-left: 200px;">
							<li style="font-size: 14px;">ព្រះរាជាណាចក្រកម្ពុជា</li>
							<li><span style="margin:0;padding:0;font-weight: 600; color: #002c7b; font-size: 12px;">KINGDOM OF CAMBODIA</span></li>
							<li style="font-size: 12px;">ជាតិ សាសនា ព្រះមហាក្សត្រ</li>
							<li><span style=" margin:0; padding:0; font-weight: 600; color: #002c7b;font-size: 10px; ">NATION RELIGION KING</span></li>
							<li><img style=" height: 12px; " src="' . $baseUrl . '/images/' . $imgSing . '"></li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
		';
		return $str;
	}
	function getFooterPrincipalSigned($branch_id, $group_id)
	{

		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Application_Model_DbTable_DbGlobal();
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

		$sql = "SELECT
		   	g.`branch_id`,
		   	b.stamp,
		   	b.signature,
		   	b.principal,
		   	b.workat,
		   	g.`group_code`,
			(SELECT teacher_name_kh from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher,
		   	(SELECT signature from rms_teacher as t where t.id = g.teacher_id LIMIT 1) as teacher_sigature
		   	
   		FROM
		   	`rms_branch` AS b,
		   	`rms_group` AS g
   		WHERE
		   	b.br_id=g.`branch_id`
		   	AND b.br_id= $branch_id
		   	AND g.`id` = " . $group_id;
		$rs = $db->getGlobalDbRow($sql);

		$font = 'Khmer OS Muol Light';
		$fontbtb = 'Khmer os battambang';

		$str = '<table width="100%">
				<tr>
					<td valign="top" width="33%" style="font-family:' . $font . ';font-size:14px;text-align: center;">
						បានឃើញ និងឯកភាព<br />
					</td>
					<td width="33%" valign="top" style="font-family:' . $fontbtb . ';font-size:14px;text-align: center;">បានពិនិត្យត្រឹមត្រូវ</td>
					<td width="33%" style="white-space: nowrap;font-family:' . $fontbtb . '" valign="top">' . $rs['workat'] . $tr->translate("CREATE_WORK_DATE") . '</td>
				</tr>
				<tr>
					<td valign="top" style="font-family:' . $font . ';font-size:14px;text-align: center;">' . $rs["principal"] . '</td>
					<td valign="top" style="font-family:' . $fontbtb . ';font-size:14px;text-align: center;">ការិយាល័យសិក្សាធិការ</td>
					<td valign="top" style=" text-align: center;font-family:' . $fontbtb . '">' . $tr->translate("TEACHER_ROOM") . '</td>
				</tr>
				<tr>
					<td valign="top"  style="font-family:' . $font . ';font-size:14px;text-align: center;">
						<div>
							<img src="' . $baseUrl . '/images/' . $rs['stamp'] . '" style="max-height:100px;position:relative;left:100px;" />
						</div>
							<img src="' . $baseUrl . '/images/' . $rs['signature'] . '" style="left:100px;bottom:50px;width:200px;position:relative;margin-left:150px;" />
					</td>
					<td></td>
					<td valign="top" style="font-family:' . $font . ';font-size:14px;text-align: center;">';
		if (!empty($rs['teacher_sigature'])) {
			$str .= '<div><img src="' . $baseUrl . '/images/photo/' . $rs['teacher_sigature'] . '" style="height:40px;position:relative;margin-bottom:20px;" /></div>';
		} else {
			$str .= '<div style="height: 100px;"></div>';
		}
		$str .= '<span style="font-family:' . $font . ';font-size:14px;text-align: center;padding-left:20px;">' . $rs['teacher'] . '</span>
					</td>
				</tr>
			</table>';
		return $str;
	} //

	function getPrintPageFormat($data=array())
	{
		//$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$pageSize = empty($data["pageSize"]) ? "" : $data["pageSize"];
		$marginTop = empty($data["marginTop"]) ? "0.7cm" : $data["marginTop"];
		$marginRight = empty($data["marginRight"]) ? "0.5cm" : $data["marginRight"];
		$marginBottom = empty($data["marginBottom"]) ? "2.5cm" : $data["marginBottom"]; //2.5cm fixed when print cut charecter
		$marginLeft = empty($data["marginLeft"]) ? "0.5cm" : $data["marginLeft"];
		$borderTopFooter = empty($data["borderTopFooter"]) ? "0px" : $data["borderTopFooter"];
		$footerLeftContent = empty($data["footerLeftContent"]) ? "" : $data["footerLeftContent"];
		$footerLeftTextTransform = empty($data["footerLeftTextTransform"]) ? "initial" : $data["footerLeftTextTransform"];
		//content: "ទំព័រ " counter(page) " / " counter(pages);
		$str="@page {";
				//$str.="size: $pageSize;";
				if(!empty($pageSize)){
					$str.="size: $pageSize;";
				}
				$str.="margin: $marginTop $marginRight $marginBottom $marginLeft;";
				
				$str.='
				counter-increment: page;
				@bottom-right {
					font-family: '."'Times New Roman'".','."'Khmer OS Battambang'".';
					border-top: '.$borderTopFooter.' solid #000000;
					padding-right:20px;
					font-size: 11px !important;
					content: " " counter(page) " / " counter(pages);
				}
				@bottom-left {
					padding-left:20px;
					content: "'.$footerLeftContent.'";
					text-transform: '.$footerLeftTextTransform.';
					font-family: '."'Times New Roman'".','."'Khmer OS Battambang'".';
					font-size: 11px !important;
					border-top: '.$borderTopFooter.' solid #000000;
				}
				';
		$str.="}";
		return $str;
	}
	
	function getAchievementTemplate($data=array())
	{
		//$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$key = new Application_Model_DbTable_DbKeycode();
		$setting = $key->getKeyCodeMiniInv(TRUE);
		
		$str = "";
		$logo = $baseUrl . '/images/Logo/Logo.png';
		if (!empty($setting['logo'])) {
			if (file_exists(PUBLIC_PATH . "/images/logo/" . $setting['logo'])) {
				$logo = $baseUrl . '/images/logo/' . $setting['logo'];
			}
		}
		$schooolNameKh = empty($setting['schooolNameKh']) ? "សាលាបញ្ញាសាស្រ្តអន្តរជាតិ" : $setting['schooolNameKh'];
		$schooolNameEng = empty($setting['schooolNameEng']) ? "Paññāsāstra International School" : $setting['schooolNameEng'];
		
		
		$defaultBg = $baseUrl.'/images/background/default-achievement.png';
		$defaultLabelNameBg = $baseUrl.'/images/background/default-achievement-label.png';

		$branchId = empty($data["branchId"]) ? "1" : $data["branchId"];
		$degreeId = empty($data["degreeId"]) ? 0 : $data["degreeId"];
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$arrFilter = array(
			'type' => 3,
			'branch_id' => $branchId,
			'degree' => $degreeId,
		);
		$bgRs = $_dbgb->getBackgroundSetting($arrFilter);
		$backgroundImage = $defaultBg;
		$labelNameBg = $defaultLabelNameBg;
		if(!empty($settingBg)){
			if (file_exists(PUBLIC_PATH."/images/background/".$settingBg)){
				$backgroundImage = $baseUrl.'/images/background/'.$settingBg;
			}
		}
		if(!empty($settingBgName)){
			if (file_exists(PUBLIC_PATH."/images/background/".$settingBgName)){
				$labelNameBg = $baseUrl.'/images/background/'.$settingBgName;
			}
		}
		
		$profilePhoto = $baseUrl.'/images/no-profile.png';
		$sex = empty($data["sex"]) ? 1 : $data["sex"];
		if($sex==2){
			$profilePhoto = $baseUrl.'/images/no-profile-female.png';
		}
		if(!empty($data["photo"])){
			if (file_exists(PUBLIC_PATH."/images/photo/".$data["photo"])){
				$profilePhoto = $baseUrl.'/images/photo/'.$data["photo"];
			}
		}
		$id = empty($data["id"]) ? "0" : $data["id"];
		$indexKey = empty($data["indexKey"]) ? "0" : $data["indexKey"];
		$branchName = empty($data["branchName"]) ? "" : " ".$data["branchName"];
		$stuCode = empty($data["stuCode"]) ? "" : $data["stuCode"];
		$stuNameKh = empty($data["stuNameKh"]) ? "" : $data["stuNameKh"];
		$stuNameEn = empty($data["stuNameEn"]) ? "" : $data["stuNameEn"];
		$achievementTitle = empty($data["achievementTitle"]) ? "" : $data["achievementTitle"];
		$achievementTypeTitle = empty($data["achievementTypeTitle"]) ? "" : $data["achievementTypeTitle"];
		$groupCode = empty($data["groupCode"]) ? "" : $data["groupCode"];
		$achievementDescription = empty($data["achievementDescription"]) ? "" : $data["achievementDescription"];


		$str='
			<style>
				
				.certificate{
					background-image: url('."'".$backgroundImage."'".');
					background-position: center;
					background-repeat: no-repeat;
					background-size: cover;
					position: relative;
					display:inline-block; 
					line-height: 23px;   
					top:-2px;   
					left:-4px;   
					color: #6d542b; 
					width: 210mm;
					min-height: 296mm;
					font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';
					margin: 0 auto;
					text-align:center;
				}
				
				.fw-bold{
					font-weight:bold !important;
				}

				.schoolInfo img {
					max-height: 95px !important;
					margin-bottom: 10px;
				}

				.schoolInfo {
					position: absolute;
					width: 100%;
					top: 55px;
				}

				.schoolName {
					color: #0a3e58;
					margin: 0;
					margin-bottom: 10px;
				}
				.schoolName.schollNameKh {
					font-size: 32px;
					line-height: 50px;
					font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';
					font-weight: normal;
				}
				.schoolName.schollNameEn {
					font-size: 24px;
					line-height: 26px;
					font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';
					text-transform: uppercase;
					font-weight: 500;
				}
				
				.studentInfo {
					top: 275px;
					position: absolute;
					width: 100%;
				}
				.studentInfo img.studentProfile {
					width: 160px;
					height: 185px !important;
					border-radius: 50% !important;
					border: solid 4px #0a3e58;
					margin-bottom: 10px;
				}
				.studentName {
					color: #0a3e58;
					margin: 0;
					margin-bottom: 10px;
				}
				.studentName.studentNameKh {
					font-size: 30px;
					line-height: 36px;
					font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';
					font-weight:normal;
				}
				.studentName.studentNameEn {
					font-size: 28px;
					line-height: 26px;
					font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';
					font-weight: 600;
				}
				
				.achievement-title-info {
					top: 585px;
					position: absolute;
					width: 100%;
					height: 180px;
					margin: 0 auto;
					
					background-image: url('."'".$labelNameBg."'".');
					background-position: center;
					background-repeat: no-repeat;
					
					background-size: 90%;
					display: flex;
					justify-content: center;
					flex-wrap: nowrap;
				}
				.achievement-blg{
					width: 71%;
					display: flex;
					align-content: center;
					flex-wrap: nowrap;
					justify-content: center;
					align-items: center;
					height: 105px;
				}

				.achievement-title-info img.trophy {
					width: 45px;
					margin-right: 5px;
				}

				h2.achievement-title {
					color: #fff;
					text-align: center;
					margin: 0;
					font-size: 24px;
					line-height: 44px;
					font-family: '."'Times New Roman'".','."'Khmer OS Battambang'".';
					text-overflow: ellipsis;
					overflow: hidden;
					display: -webkit-box !important;
					white-space: normal;
					-webkit-line-clamp: 2;
					-webkit-box-orient: vertical;
				}
				
				.achievement-info {
					top: 752px;
					position: absolute;
					width: 100%;
					text-align: center;
				}

				.achievement-info span {
					display: block;
					color: #0a3e58;
					margin: 0;
					font-family: '."'Times New Roman'".','."'Khmer OS Battambang'".';
					font-size: 22px;
					line-height: 34px;
				}
				.achievement-info span.achievement-type{
					display: flex;
					align-content: center;
					flex-wrap: nowrap;
					justify-content: center;
					align-items: center;
				}
				.achievement-info span.achievement-desc{
					margin-top: 10px;
				}
				.achievement-info span img.book {
					height: 28px;
					margin-right: 5px;
				}
				
				.padding{
					padding:0 20px;
				}
				@media print {
					@page {
						size: A4 portrait;
						margin: 0; 
					}
				}  
			</style>
		';
		$str.='<div class="html-content-holder"  >';
			$str.='<input type="hidden" id="title-'.$indexKey.'" name="title-'.$indexKey.'" value="'.$stuCode.'">';
			$str.='<div class="certificate">';
				$str.='
					<div class="schoolInfo">
						<img  src="'.$logo.'">
						<h2 class="schoolName schollNameKh">'.$schooolNameKh.'</h2>
						<h4 class="schoolName schollNameEn">'.$schooolNameEng.'</h4>
					</div>';
				$str.='
					<div class="studentInfo">
						<img class="studentProfile" src="'.$profilePhoto.'">
						<h2 class="studentName studentNameKh">'.$stuNameKh.'</h2>
						<h4 class="studentName studentNameEn">'.$stuNameEn.'</h4>
					</div>
				';
				$str.='
					<div class="achievement-title-info">
						<div class="achievement-blg">
							<img class="trophy" src="'.$baseUrl.'/images/background/trophy.png">
							<h2 class="achievement-title">'.$achievementTitle.'</h2>
						</div>
					</div>
				';
				$str.='
					<div class="achievement-info">
							<div class="padding">
								<span class="achievement-type fw-bold"><img class="book" src="'.$baseUrl.'/images/background/book.png">'.$achievementTypeTitle.'</span>
								<span class="studyClass fw-bold">Class: '.$groupCode.''.$branchName.'</span>
								<span class="achievement-desc">
								'.$achievementDescription.' 
								</span>
							</div>
						</div>
				';
				$str.='';
				$str.='';
			$str.='</div>';
		$str.='</div>';
				
		return $str;
	}

	function getLetterPraiseTemplate($data=array())
	{
		//$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		$branchId = empty($data['branch_id']) ? 0 : $data['branch_id'];
		$dregreeId = empty($data['degree']) ? 0 : $data['degree'];
		$param = array(
			'type' => 1,
			'branch_id'=> $branchId ,
			'dregreeId'=> $dregreeId ,
		);
		$setting = $dbg->getBackgroundSetting($param);
		
		$str = "";

		$image = $baseUrl.'/images/card/certificate/LetterOfPraise.jpg';
		if (!empty($setting['background'])) {
			if (file_exists(PUBLIC_PATH . "/images/background/" . $setting['background'])) {
				$image = $baseUrl . '/images/background/' . $setting['background'];
			}
		} 

		$id = empty($data["id"]) ? "0" : $data["id"];
		$indexKey = empty($data["indexKey"]) ? "0" : $data["indexKey"];

		$rank = empty($data["rank"]) ? "" : $data["rank"];
		if($rank==1){
			$sub = "st";
		}elseif($rank==2){
			$sub = "nd";
		}elseif($rank==3){
			$sub = "rd";
		}else{
			$sub = "th";
		}

		$stuNameKh = empty($data["stuNameKh"]) ? "" : $data["stuNameKh"];
		$stuNameEn = empty($data["stu_name_en"]) ? "" : $data["stu_name_en"];

		$stuCode = empty($data["stu_code"]) ? "" : $data["stu_code"];

		$group = empty($data["group_code"]) ? "" : $data["group_code"];
		preg_match('/\((.*?)\)/', $group, $matches);
		$groupCode= $matches[1];

		$academic_year = empty($data["academic_year"]) ? "" : $data["academic_year"];
		$describe = empty($setting["certificate_describe"]) ? "" : $setting["certificate_describe"];

		$issue_date = $data["issue_date"];
		$month= date("F", strtotime($issue_date));
		$day = date("j", strtotime($issue_date));
		$sup_date = date("S", strtotime($issue_date));
		$year = date("Y", strtotime($issue_date));
		$embasy =  $baseUrl.'/font/EmbassyBT.ttf';
		$str='
			<style>
				@font-face{font-family:"Embassy BT";src:url("'.$embasy.'");}
				
				.certificate{
					background-image: url("'.$image.'");
					background-size: 29.7cm 20.6cm;
					background-repeat: no-repeat;
					position: relative;
					display:inline-block; 
					line-height: 20px;
					top:2px;
					width: 29.7cm; 
					height:20.6cm;   
					color: #3f3f95; 
					font-family: "Times New Roman", sans-serif,"Khmer OS Muol Light";
				}
				.one-row-certif{
					text-align:center; 
					margin: 0 auto; 
					padding: 0;
		
				}
					
				span.value{
					 font-size: 17px;
					 display: inline-block;
					     color: #1e3b77;
				}
				span.rank {
					font-family: "Old English Text MT";
					font-size: 36px;
				    position: absolute;
				    min-width: 70px;
				    top:'.$setting['rank_top'].'px;
				    left: '.$setting['rank_left'].'px;
				    text-align: center;
					color: #042975 !important;
				}	

				.rank sup {
					font-size: 16px; /* Smaller superscript */
					top: -1.3em !important; 
				}
				/* sup {
					top: -1.3em !important; 
				} */
				span.student_name {
				   position: absolute;
				   top:'.$setting['name_top'].'px;
				   width: 100%;
				   font-family: "Lucida Calligraphy";
				   font-size: 34px;
				   color: #00AEEF; /* Light blue */
				   text-align: center;
				   font-weight: bold;
				   display: block;
					
				}
				span.class {
					width: 100%;
					display: block;
					position: absolute;
				    top:'.$setting['grade_top'].'px;
					font-family: "Lucida Calligraphy";
					font-size: 24px;
					font-style: italic;
					color: #00AEEF;
					text-align: center;
					font-weight: bold;
					width: 100%;
					display: block;
				}
				span.academic_year{
					font-family:"Times New Roman";
					position: absolute;
				    top:'.$setting['academic_top'].'px;
				    left: '.$setting['academic_left'].'px;
				    font-size: 20px;
				    min-width: 120px;
				    text-align: center;
				    color: #042975 !important;
					font-weight: bold;
				}
				span.noted{
					font-family: "Embassy BT";
					font-size: 24px;
					position: absolute;
				    top:'.$setting['describe_top'].'px;
				    left: '.$setting['describe_left'].'px;
				
				    min-width: 40px;
				    text-align: center;
					color:#040404;
				}
				
				* {
					margin: 0;
					padding: 0;
				}
				
				body{padding:0;margin:0}

				@media print{
					@page{
						page: A4;
						size: landscape;
						margin:0;
						padding:0;
					}
					body {
						-webkit-print-color-adjust: exact; /* Ensures colors and backgrounds are printed */
						print-color-adjust: exact; /* Standard version for color printing */
					}
					.certificate{
						background-image: url("'.$image.'");
						/* background-size: 29.7cm 20.6cm; */
						background-repeat: no-repeat;
						position: relative;
						display:inline-block; 
					
						top:2px;
						width: 29.7cm; 
						height:20.6cm;   
						color: #3f3f95; 
						font-family: "Times New Roman", sans-serif,"Khmer OS Muol Light";
					}
					
				}
			</style>
		';
		$str.='<div class="html-content-holder one-row-certif"  >';
			$str.='<input type="hidden" id="title-'.$indexKey.'" name="title-'.$indexKey.'" value="'.$stuCode.'">';
			$str.='<div class="certificate">';
				$str.='<span class="value rank">'.$rank.'<sup>'.$sub.'</sup></span>';
				$str.='<span class="value student_name">'.$stuNameEn.'</span>';
				$str.='<span class="value class">Grade '.$groupCode.'</span>';
				$str.='<span class="value academic_year">'.$academic_year.'</span>';
				$str.='<span class="value noted">'.$describe.' '.$month.' '.$day.'<sup>'.$sup_date.'</sup>, '.$year.'</span>';
				$str.='';
				$str.='';
			$str.='</div>';
		$str.='</div>';
		return $str;
	}
	
	
	function reportControl($data=array())
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$printFunction = empty($data["printFunction"]) ? "doPrint()" : $data["printFunction"];
		$exportFunction = empty($data["exportFunction"]) ? "exportExcel()" : $data["exportFunction"];
		$previewFunction = empty($data["previewFunction"]) ? "preview()" : $data["previewFunction"];
		
		$str="
			<div class=\"controls\">
				<div class=\"start\">
					<i class=\"fa fa-list \"></i> <span class=\"title-report\"></span>
				</div>
			    <div class=\"center\">
					<div class=\"blog-control\">
						<button title=\"" . $tr->translate("First Page") . "\" id=\"firstPage\">
							<span class=\"glyphicon glyphicon-step-backward\"></span>
						</button>
						<button title=\"".$tr->translate("Previous")."\" id=\"prevPage\"><span class=\"glyphicon glyphicon-menu-left\"></span></button>
						<input type=\"text\" id=\"pageIndicatorValue\"  name=\"pageIndicatorValue\" minlength=\"1\"  pattern=\"[1-9\s]{13,19}\" /><span id=\"pageIndicator\"></span>
						<button title=\"".$tr->translate("Next")."\" id=\"nextPage\"><span class=\"glyphicon glyphicon-menu-right\"></span></button>
						<button title=\"" . $tr->translate("Last Page") . "\" id=\"lastPage\">
							<span class=\"glyphicon glyphicon-step-forward\"></span>
						</button>
					</div>
					
					<div class=\"blog-control\">
						<button title=\"".$tr->translate("ZOOM_OUT")."\" id=\"zoomOut\"><span class=\"fa fa-search-minus\"></span></button>
						<span id=\"zoomPercent\">100%</span>
						<button title=\"".$tr->translate("ZOOM_IN")."\" id=\"zoomIn\"><span class=\"fa fa-search-plus\"></span></button>
						<button class=\"d-none\" title=\"".$tr->translate("RESET")."\" id=\"zoomReset\"><span class=\"fa fa-search-plus\"></span></button>
					</div>
					
					<div class=\"blog-control\">
						<button title=\"".$tr->translate("PORTRAIT")."\" id=\"portraitBtn\" ><i class=\"fa fa-tablet\" ></i></button>
						<button title=\"".$tr->translate("LANSCAPE")."\" id=\"landscapeBtn\" ><i class=\"fa fa-ticket-simple\"></i></button>
					</div>
					  
				</div>
				<div class=\"end\">
					<button class=\"btn-print d-none\" id=\"rebuild\" title=\"".$tr->translate("REBUILD")."\" ><span class=\"glyphicon glyphicon-print\"></span></button>
					<button class=\"btn-print\" title=\"".$tr->translate("PRINT")."\" onclick=\"$printFunction\"><span class=\"glyphicon glyphicon-print\"></span></button>
					<button class=\"btn-export\" title=\"".$tr->translate("Export")."\" onclick=\"$exportFunction\"><span class=\"fa fa-file-excel\" ></span></button>
					<button class=\"btn-preview\" title=\"".$tr->translate("Preview")."\" onclick=\"$previewFunction\"><span class=\"glyphicon glyphicon-zoom-in\" ></span></button>
					<button id=\"togglePanel\" title=\"".$tr->translate("SEACH")."\" class=\"btn-search\" ><span class=\"glyphicon glyphicon-search\" ></span></button>
				</div>
			</div>
		";
		return $str;
	}
	
	function reportControlWhite($data=array())
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$printFunction = empty($data["printFunction"]) ? "doPrint()" : $data["printFunction"];
		$exportFunction = empty($data["exportFunction"]) ? "exportExcel()" : $data["exportFunction"];
		$previewFunction = empty($data["previewFunction"]) ? "preview()" : $data["previewFunction"];
		$footerLeftContent = empty($data["footerLeftContent"]) ? "" : $data["footerLeftContent"];
		
		
		$contentPrint = empty($data["contentPrint"]) ? "divPrint" : $data["contentPrint"];
		$pdfContent = empty($data["pdfContent"]) ? "modal-pdf-content" : $data["pdfContent"];
		
		$functionPrePdf = "previewPDF('portrait','".$footerLeftContent."','".$contentPrint."','".$pdfContent."')";
		$functionPrePdfLandscape = "previewPDF('landscape','".$footerLeftContent."','".$contentPrint."','".$pdfContent."')";
		$functionChange = "";
		$str="
			<div class=\"controls white\">
				<div class=\"start\">
					<i class=\"fa fa-list \"></i> <span class=\"title-report\"></span>
				</div>
				<div class=\"end\">";
					if($pdfContent!="modal-pdf-content"){
						$str.="
							<div class=\"blog-control\">
								<a href=\"#\" onClick=\"changeReportView('1','".$footerLeftContent."','".$contentPrint."','".$pdfContent."')\" class=\"button-control dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" >".$tr->translate("NORMAL_VIEW")." <i class=\"report-icon fa fa-file-text\"></i></a>
							</div>
						";
					}
					$str.="<div class=\"blog-control\">
							<span>".$tr->translate("PDF_LAYOUT")."</span>
							<button title=\"".$tr->translate("PORTRAIT")."\" onclick=\"$functionPrePdf\" ><i class=\"fa fa-tablet\" ></i></button>
							<button title=\"".$tr->translate("LANSCAPE")."\" onclick=\"$functionPrePdfLandscape\"  ><i class=\"fa fa-ticket-simple\"></i></button>
						</div>";
					
			$str.="	<button class=\"btn-print\" title=\"".$tr->translate("PRINT")."\" onclick=\"$printFunction\"><span class=\"glyphicon glyphicon-print\"></span></button>
					<button class=\"btn-export\" title=\"".$tr->translate("Export")."\" onclick=\"$exportFunction\"><span class=\"fa fa-file-excel\" ></span></button>
					<button class=\"btn-preview\" title=\"".$tr->translate("Preview")."\" onclick=\"$previewFunction\"><span class=\"glyphicon glyphicon-zoom-in\" ></span></button>
					<button id=\"togglePanel\" title=\"".$tr->translate("SEACH")."\" class=\"btn-search\" ><span class=\"glyphicon glyphicon-search\" ></span></button>
				</div>
			</div>
		";
		return $str;
	}

}
