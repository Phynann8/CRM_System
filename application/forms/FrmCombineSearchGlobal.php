<?php 
Class Application_Form_FrmCombineSearchGlobal extends Zend_Dojo_Form {
	function FormIncomeStatisticFilter($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$registrationDate = $frm->getStartDateSearch($search,'Registration Date');
		$studentTypeFilter = $frm->getStudentTypeStatusTypeSearch($search);
		$paymentTermFilter = $frm->getPaymentTermSearch($search);
		$paymentTermFilter->removeMultiOption(1);
		$paymentTermFilter->removeMultiOption(5);
		$paymentTermFilter->removeMultiOption(6);
		$startDateFilter = $frm->getPaymentDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$studentstatusFilter = $frm->getStudyTypeSearch($search);
		$activeFilter = $frm->getActiveTypeSearch($search);
		$paymentstatusFilter = $frm->getPaymentStatusSearch($search);
		$termListFilter = $frm->getTermListSearch($search);

		$this->addElements(array(
			$endDateFilter,
			$activeFilter,
			$textSearch,
			$yearFilter,
			$branchFilter,
			$studentstatusFilter,
			$degreeFilter,
			$registrationDate,
			$studentTypeFilter,
			$paymentTermFilter,
			$startDateFilter,
			$paymentstatusFilter,
			$termListFilter
		));
		return $this;
	}

	function FormSearchStudentInfo($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$studentStudystatus = $frm->getStudentStudyStatus($search);
		$mainGradeFilter = $frm->getMainGradeTypeSearch($search);
		$sessionFilter = $frm->getSessionSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$statusFilter = $frm->getStatusSearch($search);
		

		$this->addElements(array(
			$textSearch,
			$yearFilter,
			$branchFilter,
			$degreeFilter,
			$studentStudystatus,
			$mainGradeFilter,
			$sessionFilter,
			$startDateFilter,
			$endDateFilter,
			$statusFilter,
			
		));
		return $this;
	}
	function FormSearchCrm($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$askForFilter = $frm->getAskForSearch($search);
		$khnowByFilter = $frm->getKnowBySearch($search);
		$followUpStatusFilter = $frm->getFollowStatusSearch($search);
		$crmStatusFilter = $frm->getCrmStatusSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$askForFilter,
			$khnowByFilter ,
			$followUpStatusFilter,
			$crmStatusFilter ,
			$startDateFilter,
			$endDateFilter,
		));
		return $this;
	}
	function FormSearchTest($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$testTypeFilter = $frm->getTestTypeSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$degreeFilter,
			$testTypeFilter,
			$startDateFilter,
			$endDateFilter,
		));
		return $this;
	}
	function FormSearchGroup($search=null)// group, student change Group, Student Stop, Student Return
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$statusFilter = $frm->getStatusSearch($search);
		$teacherFilter = $frm->getTeacherSearch($search);
		$isPassFilter = $frm->getIsPassSearch($search);
		$dropTypeFilter = $frm->getStudentDropTypeSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$startDateFilter,
			$endDateFilter,
			$statusFilter,
			$teacherFilter,
			$isPassFilter,
			$dropTypeFilter
		));
		return $this;
	}

	function FormSearchTeacher($search=null)// group, student change Group, Student Stop, Student Return
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$staffTypeFilter = $frm->staffTypeSearch($search);
		$nationFilter = $frm->getNationSearch($search);
		$teacherTypeFilter = $frm->getTeacherTypeSearch($search);
		$activeTypeFilter = $frm->getActiveTypeSearch($search);
		$departmentFilter = $frm->getDepartSearch($search);
		$statusFilter = $frm->getStatusSearch($search);
		
		$this->addElements(array(
			$textSearch,
			$branchFilter ,
			$degreeFilter,
			$nationFilter,
			$staffTypeFilter,
			$teacherTypeFilter,
			$activeTypeFilter,
			$departmentFilter ,
			$statusFilter,
		
		));
		return $this;
	}
	
	function FormNealyPaymentFilter($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$branchList = $frm->getBranchListSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeMulitiOptionSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$getServiceTypeSearch = $frm->getServiceTypeSearch($search);
		$nearlyPaymetySort = $frm->getNearlyPaymetySortSearch($search);
		$periodDay = $frm->getPeriodDaySearch($search);
		$nearlyFilterType = $frm->getNearlyPaymetyFilterType($search);
		
		$paymentTermFilter = $frm->getPaymentTermSearch($search);
		$paymentTermFilter->removeMultiOption(1);
		$paymentTermFilter->removeMultiOption(5);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$endDateFilter,
			$getServiceTypeSearch,
			$nearlyPaymetySort,
			$periodDay,
			$nearlyFilterType,
			$paymentTermFilter,
			$branchList,
		));
		return $this;
	}
	
	function FormSearchCalendar($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$calendarTypeFilter = $frm->getCalendarTypeSearch($search);
		$statusFilter = $frm->getStatusSearch($search);

		$this->addElements(array(
			$textSearch,
			$startDateFilter,
			$endDateFilter,
			$calendarTypeFilter,
			$statusFilter,
		));
		return $this;
	}
	function FormSearchTeacherDasboard($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$examTypeFilter = $frm->getExamTypeSearch($search);
		$forSemesterFilter = $frm->getForSemesterSearch($search);
		$forMonthFilter = $frm->getForMonthSearch($search);
		$forTermFilter = $frm->getForTermSearch($search);
		$criterialFilter = $frm->getCriteriaIDSearch($search);
		$teacherFilter = $frm->getTeacherSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$IssueScoreStatusFilter = $frm->getIssueScoreStatusSearch($search);
		$CombineStatusFilter = $frm->getCombineStatusSearch($search);
		$EvaluationStatusFilter = $frm->getEvaluationStatusSearch($search);
		
		$this->addElements(array(
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$examTypeFilter,
			$forSemesterFilter,
			$forMonthFilter,
			$forTermFilter,
			$criterialFilter,
			$teacherFilter,
			$startDateFilter,
			$endDateFilter,
			$IssueScoreStatusFilter,
			$CombineStatusFilter ,
			$EvaluationStatusFilter ,
		));
		return $this;
	}

	function FormSearchSubjectStatistic($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$examTypeFilter = $frm->getExamTypeSearch($search);
		$forSemesterFilter = $frm->getForSemesterSearch($search);
		$forMonthFilter = $frm->getForMonthSearch($search);
		$forTermFilter = $frm->getForTermSearch($search);
		$teacherFilter = $frm->getTeacherSearch($search);
		$departmentFilter = $frm->getDepartSearch($search);
		$degreeSortFilter = $frm->getSortDegreeSearch($search);
		
		$this->addElements(array(
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$examTypeFilter,
			$forSemesterFilter,
			$forMonthFilter,
			$forTermFilter,
			$departmentFilter,
			$teacherFilter,
			$degreeSortFilter
		));
		return $this;
	}
	
	function FormPreAttendance($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$queryOption = $frm->getPreAttOptionSearch($search);
		$shiftOption = $frm->getPreAttShiftSearch($search);
		$format = $frm->getPreAttFormatTypeSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$endDateFilter,
			$queryOption,
			$shiftOption,
			$format,
		));
		return $this;
	}
	
	function FormSearchDashboadMoblie($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$passwordStatus = $frm->getPasswordOptionSearch($search);
		$usedAppStatus = $frm->getUsedAppSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$passwordStatus,
			$usedAppStatus
		));
		return $this;
	}
	function FormFilterExpensebyType($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$getExpenseCategoryFilter = $frm->getExpenseCategory($search);
		$displayCategory = $frm->getdisplayCategory($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$yearFilter,
			$endDateFilter,
			$getExpenseCategoryFilter,
			$displayCategory
		));
		return $this;
	}
	
	function FormSearchTeachingClass($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$yearFilter,
			$degreeFilter,
		));
		return $this;
	}
	
	function FormImportSchedule($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$branchFilter = $frm->getBranchSearch($search);
		$branchId = $frm->getBranchIdSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$year = $frm->getAcademicYearIdSearch($search);

		$this->addElements(array(
			$branchFilter,
			$branchId,
			$yearFilter,
			$year,
		));
		return $this;
	}
	function FormgetProdcutSold($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchId = $frm->getBranchSearch($search);
		$productCategoryFilter = $frm->getProductCategory($search);
		$userFilter = $frm->getUserListSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchId,
			$productCategoryFilter,
			$userFilter,
			$startDateFilter,
			$endDateFilter,
		));
		return $this;
	}
	function FormSearchProductLocation($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchId = $frm->getBranchSearch($search);
		$productCategoryFilter = $frm->getProductCategory($search);
		$productTypeFilter = $frm->getProductType($search);
		$displayProductBy = $frm->sortDisplayProduct($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchId,
			$productCategoryFilter,
			$productTypeFilter,
			$displayProductBy,
			$startDateFilter,
			$endDateFilter,
		));
		return $this;
	}
	function FormLowStockReport($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchId = $frm->getBranchSearch($search);
		$productCategoryFilter = $frm->getProductCategory($search);
		$productTypeFilter = $frm->getProductType($search);

		$this->addElements(array(
			$textSearch,
			$branchId,
			$productCategoryFilter,
			$productTypeFilter
		));
		return $this;
	}

	function FormSearchPurchaseReport($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchId = $frm->getBranchSearch($search);
		$supplierFilter = $frm->frmGetSupplier($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		
		$this->addElements(array(
			$textSearch,
			$branchId,
			$supplierFilter,
			$startDateFilter,
			$endDateFilter
		));
		return $this;
	}
	function FormSearchRequestReport($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchId = $frm->getBranchSearch($search);
		$requestForFilter = $frm->frmGetRequestFor($search);
		$forSectionFilter = $frm->frmGetForSection($search);
		$categoryFilter = $frm->getProductCategory($search);
		$productTypeFilter = $frm->getProductType($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$requestStatusFilter = $frm->getRequestStatusSearch($search);
		
		$this->addElements(array(
			$textSearch,
			$branchId,
			$requestForFilter,
			$forSectionFilter,
			$categoryFilter,
			$productTypeFilter,
			$startDateFilter,
			$endDateFilter,
			$requestStatusFilter
		));
		return $this;
	}

	function FormSearchSupplierBalanceReport($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchId = $frm->getBranchSearch($search);
		$filterSupplier = $frm->frmGetSupplier($search);
		$filterPaymentstaus = $frm->getPaymentStatusSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		
		
		$this->addElements(array(
			$textSearch,
			$branchId,
			$filterSupplier,
			$filterPaymentstaus,
			$startDateFilter,
			$endDateFilter
		));
		return $this;
	}

	function FormSearchSumarryReport($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchId = $frm->getBranchSearch($search);
		$productTypeFilter = $frm->getProductType($search);
		$getProductCategory = $frm->getProductCategory($search);
		$sortDisplayProduct = $frm->sortDisplayProduct($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		
		$this->addElements(array(
			$textSearch,
			$branchId,
			$sortDisplayProduct,
			$productTypeFilter,
			$getProductCategory,
			$startDateFilter,
			$endDateFilter
		));
		return $this;
	}
	function FormSearchTransferStock($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchId = $frm->getBranchSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$transferStatusFilter = $frm->getTransferStatusSearch($search);
		
		$this->addElements(array(
			$textSearch,
			$branchId,
			$startDateFilter,
			$endDateFilter,
			$transferStatusFilter,
		));
		return $this;
	}
	function FormSearchStudentGetReport($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchId = $frm->getBranchSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$stockStatusFilter = $frm->getStockStatusSearch($search);
		
		$this->addElements(array(
			$textSearch,
			$branchId,
			$startDateFilter,
			$endDateFilter,
			$stockStatusFilter
		));
		return $this;
	}
	function FormSearchProduct($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchId = $frm->getBranchSearch($search);
		$itemsFilter = $frm->getItemSearch($search);
		$productTypeFilter = $frm->getProductType($search);
		$isOnePaymantFilter = $frm->getIsOnePaymentSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$statusFilter = $frm->getStatusSearch($search);
		$isCountStockFilter = $frm->frmIsCountStock($search);
		$isProductSetFilter = $frm->frmIsProductSet($search);
		$inLocationStatus = $frm->getProInLocationSearch($search);
		
		$this->addElements(array(
			$textSearch,
			$branchId,
			$itemsFilter,
			$productTypeFilter,
			$isOnePaymantFilter ,
			$startDateFilter,
			$endDateFilter,
			$statusFilter,
			$isCountStockFilter,
			$isProductSetFilter,
			$inLocationStatus,
		));
		return $this;
	}
	function FormSearchProductSet($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$itemsFilter = $frm->getItemSearch($search);
		$productTypeFilter = $frm->getProductType($search);
		$isOnePaymantFilter = $frm->getIsOnePaymentSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$statusFilter = $frm->getStatusSearch($search);
		
		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$itemsFilter,
			$productTypeFilter,
			$isOnePaymantFilter ,
			$startDateFilter,
			$endDateFilter,
			$statusFilter 
		));
		return $this;
	}
	function FormSearchProductPurchase($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchId = $frm->getBranchSearch($search);
		$supplierFilter = $frm->frmGetSupplier($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		
		$this->addElements(array(
			$textSearch,
			$branchId,
			$supplierFilter,
			$startDateFilter,
			$endDateFilter
		));
		return $this;
	}
	
	
	function FormSearchReqpermission($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		

		$this->addElements(array(
			$textSearch,
			$yearFilter,
			$branchFilter,
			$degreeFilter,
			$startDateFilter,
			$endDateFilter,
			
		));
		return $this;
	}
	
	function FormSearchStuConnectedTelegram($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$connectedTelegram = $frm->getConnectTelegramStatusearch($search);
		

		$this->addElements(array(
			$textSearch,
			$yearFilter,
			$branchFilter,
			$degreeFilter,
			$connectedTelegram,
			
		));
		return $this;
	}
	
	function FormSearchCreditTransaction($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$byDate = $frm->getByDateOptionSearch($search);
		$creditType = $frm->getCreditTypeSearch($search);
		$cashType = $frm->getCashTypeSearch($search);
		$crOpertationType = $frm->getCrOpertationType($search);
		
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$byDate,
			$creditType,
			$cashType,
			$crOpertationType,
			$startDateFilter,
			$endDateFilter,
			
		));
		return $this;
	}
	
	function FormSearchSuspendService($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			
			$startDateFilter,
			$endDateFilter,
			
		));
		return $this;
	}

	function FormSearchExpenseReport($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$supplierFilter = $frm->frmGetSupplier($search);
		$expenseCategoryId = $frm->getExpenseType($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$receiptOrder = $frm->getReceiptOrder($search);
		$expRecordOrdering = $frm->getExpenseRecordOrdering($search);
		$receiptStatus = $frm->getReceiptStatusSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$supplierFilter,
			$expenseCategoryId,
			$startDateFilter,
			$endDateFilter,
			$receiptOrder,
			$expRecordOrdering,
			$receiptStatus
		));
		return $this;
	}
	
	function FormSearchStuSummaryReport($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$hideInactive = $frm->getHideInactiveOpt($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$yearFilter,
			$hideInactive
		));
		return $this;
	}

	function FormSearchStuDropReport($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$stopTypeFilter = $frm->getStopTypeSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$stopTypeFilter,
			$startDateFilter,
			$endDateFilter
		));
		return $this;
	}
	
	function FormSearchGeneral($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$statusFilter = $frm->getStatusSearch($search);
		
		$this->addElements(array(
			$textSearch,
			$yearFilter,
			$branchFilter,
			$degreeFilter,
			$startDateFilter,
			$endDateFilter,
			$statusFilter,
		));
		return $this;
	}
	
	function FormSearchReferralReport($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$statusFilter = $frm->getStatusSearch($search);
		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$startDateFilter,
			$endDateFilter,
			$statusFilter,
		));
		return $this;
	}
	function FormSearchStudentPaymentList($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$SessionSearchhFilter = $frm->getSessionSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$pmtStatus = $frm->getPmtStatusSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$SessionSearchhFilter,
			$degreeFilter,
			$startDateFilter,
			$endDateFilter,
			$pmtStatus,
		));
		return $this;
	}
	function FormSearchFoundation($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$SessionSearchhFilter = $frm->getSessionSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$Status = $frm->getStatusSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$SessionSearchhFilter,
			$degreeFilter,
			$startDateFilter,
			$endDateFilter,
			$Status,
		));
		return $this;
	}
	function FormSearchFontDesk($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$AcademicYear = $frm->getAcademicYearSearch($search);
		$pickUpOption = $frm->pickUpOption($search);
		$getStudyTypeSearch = $frm->getStudyTypeSearch($search);
		$studyYear = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$AcademicYear,
			$degreeFilter,
			$studyYear,
			$endDateFilter,
			$pickUpOption,
			$getStudyTypeSearch
		));
		return $this;
	}
	
	function FormSearchStudentReport($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$academicYear = $frm->getAcademicYearSearch($search);
		
		$getStudyTypeSearch = $frm->getStudyTypeSearch($search);
		$isPassClass = $frm->isPassClassSearch($search);
		$studyYear = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$stuReportTypeSearch = $frm->stuReportTypeSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$academicYear,
			$degreeFilter,
			$studyYear,
			$endDateFilter,
			$isPassClass,
			$getStudyTypeSearch,
			$stuReportTypeSearch
		));
		return $this;
	}
	
	function FormSearchPaymentDetailReport($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$academicYear = $frm->getAcademicYearSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$itemFilter = $frm->getItemsSearch($search);
		$paymentTermFilter = $frm->getPaymentTermSearch($search);
		$serviceFilter = $frm->getServiceTypeSearch($search);
		$receiptStatusFilter = $frm->getReceiptStatusSearch($search);
		
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$userIdFilter = $frm->getUserListSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$academicYear,
			
			$degreeFilter,
			$itemFilter,
			$paymentTermFilter,
			$serviceFilter,
			$receiptStatusFilter,
			
			$startDateFilter,
			$endDateFilter,
			$userIdFilter,
		));
		return $this;
	}
	function FormSearchPaymentSummaryReport($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$branchFilter = $frm->getBranchSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$this->addElements(array(
			$branchFilter,
			$yearFilter,
		));
		return $this;
	}
	function FormTelegramInfo($search=null) 
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$degreeFilter = $frm->getDegreeSearch($search);
		$yearFilter = $frm->getAcademicYearSearch($search);
		$startDateFilter = $frm->getStartDateSearch($search);
		$endDateFilter = $frm->getEndDateSearch($search);
		$format = $frm->getTelegramReportLayoutSearch($search);

		$this->addElements(array(
			$textSearch,
			$branchFilter,
			$yearFilter,
			$degreeFilter,
			$startDateFilter,
			$endDateFilter,
			$format,
		));
		return $this;
	}
	function FormSearchScoreReport($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$branchFilter = $frm->getBranchSearch($search);
		$academicYear = $frm->getAcademicYearSearch($search);
		$monthFilter = $frm->getForMonthSearch($search);
		
		$this->addElements(array(
			$branchFilter,
			$academicYear,
			$monthFilter
		));
		return $this;
	}
	function FormSearchStudentListReport($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);
		$academicYear = $frm->getAcademicYearSearch($search);
		$shortStudentByType = $frm->getShortStudentByTypeSearch($search);

		$this->addElements([
			$textSearch,
			$branchFilter,
			$academicYear,
			$shortStudentByType
		]);
		return $this;
	}
	function FormSearchIncomeReport($search=null)
	{
		$frm = new Application_Form_FrmSearchGlobalNew();
		$textSearch = $frm->controlTextSearch($search);
		$branchFilter = $frm->getBranchSearch($search);

		$getIncomeCategory = $frm->getIncomeCategory($search);
		$startDate= $frm->getStartDateSearch($search);
		$endDate = $frm->getEndDateSearch($search);

		$getReceiptOrder = $frm->getReceiptOrder($search);
		$getIncomeOrdering = $frm->getIncomeOrdering($search);
		$getReceiptStatus = $frm->getReceiptStatusSearch($search);


		$this->addElements([
			$textSearch,
			$branchFilter,
			$startDate,
			$endDate,
			$getReceiptOrder,
			$getIncomeOrdering,
			$getIncomeCategory,
			$getReceiptStatus
		]);
		return $this;
	}
}

