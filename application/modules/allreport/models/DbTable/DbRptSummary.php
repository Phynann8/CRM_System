<?php

class Allreport_Model_DbTable_DbRptSummary extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_student';
	function getAllSummaryStudentReport($search){//using
    	$db = $this->getAdapter();
		
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$lang = $dbp->currentlang();
		$branch= $dbp->getBranchDisplay();
		
    	$label = "name_en";
		$grade = "rms_itemsdetail.title_en";
		$degree = "rms_items.title_en";
		if($lang==1){
    		$label = "name_kh";
    		$grade = "rms_itemsdetail.title";
    		$degree = "rms_items.title";
    	}
		
		$stringJsonQue="
		CONCAT( 
			'[',
			GROUP_CONCAT('{',
				'".'"grade"'.":','".'"'."',COALESCE(st.`grade`,''),'".'"'."',
				'".',"gradeSh"'.":','".'"'."',TRIM(COALESCE(st.`gradeShortcut`,'')),'".'"'."',
				'".',"totalSt"'.":','".'"'."',COALESCE(st.`totalSt`,0),'".'"'."',
				'".',"activeSt"'.":','".'"'."',COALESCE(st.`activeSt`,0),'".'"'."',
				'".',"inactiveSt"'.":','".'"'."',COALESCE(st.`inactiveSt`,0),'".'"'."',
				'".',"totalM"'.":','".'"'."',COALESCE(st.`totalM`,0),'".'"'."',
				'".',"totalF"'.":','".'"'."',COALESCE(st.`totalF`,0),'".'"'."',
				'".',"activeStM"'.":','".'"'."',COALESCE(st.`activeStM`,0),'".'"'."',
				'".',"activeStF"'.":','".'"'."',COALESCE(st.`activeStF`,0),'".'"'."',
				
			'}' ORDER BY st.`degreeOrdering` ASC,st.`gradeOrdering` ASC )
			,']'
			) jsData
		";
		
    	$sql="
			SELECT 
				st.`branchId`
				,b.$branch AS branchName
				,st.`academicYear`
				,CONCAT(COALESCE(ac.fromYear,''),'-',COALESCE(ac.toYear,'')) AS academicYearTitle
		";
		$sql.=",$stringJsonQue";
		$sql.="
			FROM 
					`v_study_main_statistic` AS st 
					JOIN rms_branch AS b ON b.br_id = st.`branchId`
					LEFT JOIN rms_academicyear AS ac ON ac.id = st.academicYear
				WHERE 
					1 
		";
    	$where=' ';
    	if(!empty($search['branch_id'])){
    		$where.=' AND st.`branchId`='.$search['branch_id'];
    	}
		if(!empty($search['branchList'])){
			$branchList = implode(",", $search['branchList']);
			$where.= " AND FIND_IN_SET(st.`branchId`,'" . $branchList . "' ) ";
	   	}
    	if(!empty($search['academic_year'])){
    		$where.=' AND st.`academicYear`='.$search['academic_year'];
    	}
    	
    	$where.= $dbp->getAccessPermission("st.`branchId`");
    	$where.= $dbp->getSchoolOptionAccess('(SELECT i.schoolOption FROM `rms_items` AS i WHERE i.type=1 AND i.id = st.`degree` LIMIT 1)');
		$where.= $dbp->getDegreePermission('st.`degree`');

    	$groupBy = " GROUP BY st.`branchId` ";
    	$orderBy = " ORDER BY st.`branchId` ASC ";
    	return $db->fetchAll($sql.$where.$groupBy.$orderBy);
    }
	
	function getDailyIncomeSummary($search){
		$db = $this->getAdapter();
		
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$lang = $dbp->currentlang();
		$branch= $dbp->getBranchDisplay();
		
		$currentDate = date("Y-m-d");
		if(!empty($search['end_date'])){
			$currentDate = date("Y-m-d",strtotime($search['end_date']));
		}
		
		$startDate = date("Y-m-01",strtotime($currentDate));
		$endDate = date("Y-m-t",strtotime($currentDate));
		$branchId = empty($search['branch_id']) ? 0 : $search['branch_id'];
		
		$sql="
			SELECT 
				b.$branch AS branchName
				,b.`br_id` AS branchId
				,i.`createDate`
				,d.selected_date AS createDateFmt
				,COALESCE(i.`ttMGrandTotal`,0) AS ttMGrandTotal
				,COALESCE(i.`ttMBalanceDue`,0) AS ttMBalanceDue
				,COALESCE(i.`ttMPaidAmt`,0) AS ttMPaidAmt
				,COALESCE(i.`tPaidAmountCash`,0) AS tPaidAmountCash
				,COALESCE(i.pmtDetailJson,'[]') AS pmtDetailJson
		";
		$sql.="
			FROM rms_branch b
				CROSS JOIN (
					SELECT DATE('".$startDate."') + INTERVAL n DAY AS selected_date
					FROM (
						SELECT a.N + b.N * 10 + c.N * 100 AS n
						FROM 
							(SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 
							 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 
							 UNION SELECT 8 UNION SELECT 9) a,
							(SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) b,
							(SELECT 0 AS N) c
					) numbers
					WHERE DATE('".$startDate."') + INTERVAL n DAY <= '".$endDate."'
				) d
				LEFT JOIN v_income_daily_total_smr i 
					ON b.br_id = i.branchId AND i.createDateFmt = d.selected_date
			WHERE 1
			";
		
    	if(!empty($search['branch_id'])){
    		$sql.=' AND b.`br_id`='.$search['branch_id'];
    	}
		if(!empty($search['branchList'])){
			$branchList = implode(",", $search['branchList']);
			$sql.= " AND FIND_IN_SET(b.`br_id`,'" . $branchList . "' ) ";
	   	}
		$sql.= $dbp->getAccessPermission("b.`br_id`");

    	$orderBy = " ORDER BY d.selected_date ASC,b.`br_id` ASC ";
		
    	return $db->fetchAll($sql.$orderBy);
	}
	
	function getMonthlyIncomeYearSummary($search){
		$db = $this->getAdapter();
		
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$lang = $dbp->currentlang();
		$branch= $dbp->getBranchDisplay();
		
		$currentDate = date("Y-m-d");
		if(!empty($search['end_date'])){
			$currentDate = date("Y-m-d",strtotime($search['end_date']));
		}
		
		$year = date("Y",strtotime($currentDate));

		$sql="
			SELECT 
				b.br_id AS branchId
				,b.$branch AS branchName 
				,m.month_num
				,m.month_name AS monthTitle
				,COALESCE(i.`grandTotal`, 0) AS grandTotal
				,COALESCE(i.`totalPaid`, 0) AS totalPaid
				,COALESCE(i.`totalBalanceDue`, 0) AS totalBalanceDue
				,COALESCE(i.`totalCredimemo`, 0) AS totalCredimemo
				,COALESCE(i.`totalPaidCash`, 0) AS totalPaidCash
				,i.`pmtDetailJson`
		";
		$sql.="
			FROM `rms_branch` b
				CROSS JOIN (
					SELECT 1 AS month_num, 'January' AS month_name UNION ALL
					SELECT 2, 'February' UNION ALL
					SELECT 3, 'March' UNION ALL
					SELECT 4, 'April' UNION ALL
					SELECT 5, 'May' UNION ALL
					SELECT 6, 'June' UNION ALL
					SELECT 7, 'July' UNION ALL
					SELECT 8, 'August' UNION ALL
					SELECT 9, 'September' UNION ALL
					SELECT 10, 'October' UNION ALL
					SELECT 11, 'November' UNION ALL
					SELECT 12, 'December'
				) m
				LEFT JOIN `vpm_mothly_total_smr` i 
					ON i.`branchId` = b.`br_id` 
					AND YEAR(i.`pmtDate`) = $year
					AND MONTH(i.pmtDate) = m.month_num 
			WHERE 1
		";
		
    	if(!empty($search['branch_id'])){
    		$sql.=' AND b.`br_id`='.$search['branch_id'];
    	}
		if(!empty($search['branchList'])){
			$branchList = implode(",", $search['branchList']);
			$sql.= " AND FIND_IN_SET(b.`br_id`,'" . $branchList . "' ) ";
	   	}
		$sql.= $dbp->getAccessPermission("b.`br_id`");

    	$orderBy = " ORDER BY  m.month_num ASC,b.`br_id` ASC ";
    	return $db->fetchAll($sql.$orderBy);
	}
	
		
}