<?php

class Issue_Model_DbTable_DbDashboardScore extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_group';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}

	function getAllGroups($search)
	{
		$db  = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$currentLang = $dbp->currentlang();

		$criterialTitle = $currentLang == 1 ? "criterialTitle" : "criterialTitleEn";
		$subjectTitle   = $currentLang == 1 ? "subject_titlekh" : "subject_titleen";
		$teacherName    = $currentLang == 1 ? "teacher_name_kh" : "teacher_name_en";

		$scoreJoin  = "";
		if (!empty($search['settingEntryId'])) {
			$scoreJoin .= " AND vgs.settingEntryId = {$search['settingEntryId']} ";
		}
		if ($search['exam_type'] > 0) {
			$scoreJoin .= " AND vgs.examType = {$search['exam_type']} ";
		}
		if ($search['for_month'] > 0) {
			$scoreJoin .= " AND vgs.forMonth = {$search['for_month']} ";
		}
		if ($search['for_semester'] > 0) {
			$scoreJoin .= " AND vgs.forSemester = {$search['for_semester']} ";
		}
		if ($search['for_term'] > 0) {
			$scoreJoin .= " AND vgs.forTerm = {$search['for_term']} ";
		}

		$amountCon = "";
		if ($search['exam_type'] > 0) {
			if ($search['exam_type'] == 1) {
				$amountCon = " AND gs.amount_subject > 0 ";
			} else {
				$amountCon = " AND gs.amount_subject_sem > 0 ";
			}
		}

		/* Additional filters inside group subject list */
		$extraGS = "";
		if (!empty($search['teacher'])) {
			$extraGS .= " AND gs.teacher = {$search['teacher']} ";
		}
		if (!empty($search['subjectId'])) {
			$extraGS .= " AND gs.subject_id = {$search['subjectId']} ";
		}
		if (!empty($search['issueScoreStatus'])) {
			if ($search['issueScoreStatus'] == 1) {
				$extraGS .= " AND vgs.gradingId > 0 ";
			} elseif ($search['issueScoreStatus'] == 2) {
				$extraGS .= " AND (vgs.gradingId = 0 OR vgs.jsonScoreTmp IS NULL) ";
			}
		}

		$scoreSubjectTmpList = "
			(
				SELECT
					gs.group_id,
					CONCAT(
						'[',
						GROUP_CONCAT(
							CONCAT(
								'{',
									'\"subjectId\":', COALESCE(gs.subject_id,0), ',',
									'\"teacherId\":', COALESCE(gs.teacher,0), ',',
									'\"gradingId\":', COALESCE(vgs.gradingId,0), ',',
									'\"subject_lang\":', COALESCE(sj.subject_lang,0), ',',
									'\"shortcut\":\"', COALESCE(sj.$subjectTitle,''), '\",',
									'\"teacher\":\"', COALESCE(t.$teacherName,''), '\",',
									'\"dateInput\":\"', COALESCE(vgs.dateInput,''), '\",',
									'\"jsonScoreTmp\":', COALESCE(vgs.jsonScoreTmp,'[]'),
								'}'
							)
							ORDER BY sj.subject_lang, sj.id
						),
						']'
					) AS jsonScoreTmpList
				FROM rms_group_subject_detail AS gs
					LEFT JOIN v_grading_tmp_by_subject AS vgs
						ON vgs.subjectId = gs.subject_id
					AND vgs.groupId = gs.group_id
					$scoreJoin
					LEFT JOIN rms_subject AS sj ON sj.id = gs.subject_id
					LEFT JOIN rms_teacher AS t ON t.id = gs.teacher
				WHERE 1
					$amountCon
					$extraGS
				GROUP BY gs.group_id
			) AS sst
		";

		$sql = "
			SELECT
				g.id,
				g.gradingId,
				g.group_code,

				CONCAT(ac.fromYear,'-',ac.toYear) AS academic_year,
				g.semester,

				r.room_name,
				tc.$teacherName AS teacher,

			
				CONCAT(
					'[',
						GROUP_CONCAT(
							CONCAT(
								'{',
									'\"criteriaId\":', vs.criteriaId, ',',
									'\"criteriaType\":', vs.criteriaType, ',',
									'\"criterialTitle\":\"', vs.$criterialTitle, '\"',
								'}'
							)
							ORDER BY vs.rank
						),
					']'
				) AS criterialList,

				sst.jsonScoreTmpList AS scoreSubjectTmpList,

				COALESCE(s.id,0) AS scoreId,
				COALESCE(sts.id,0) AS assementId
				" . $dbp->caseStatusShowImage("g.status") . "

			FROM rms_group AS g

			
			LEFT JOIN rms_academicyear AS ac ON ac.id = g.academic_year
			LEFT JOIN rms_room AS r ON r.room_id = g.room_id
			LEFT JOIN rms_teacher AS tc ON tc.id = g.teacher_id
			LEFT JOIN  `rms_items` AS i ON i.type=1 AND i.id = `g`.`degree`

		
			LEFT JOIN v_criterial_setting AS vs
				ON vs.score_setting_id = g.gradingId
			AND vs.criteriaType > 0
			" . (!empty($search['exam_type']) ? " AND vs.forExamType = {$search['exam_type']}" : "") . "

			
			LEFT JOIN rms_score AS s
				ON s.status = 1
			AND s.group_id = g.id
		";
		
		if (!empty($search['settingEntryId'])) $sql .= " AND s.settingId = {$search['settingEntryId']} ";
		if ($search['exam_type'] > 0) $sql .= " AND s.exam_type = {$search['exam_type']} ";
		if ($search['for_month'] > 0)  $sql .= " AND s.for_month = {$search['for_month']} ";
		if ($search['for_semester'] > 0)$sql .= " AND s.for_semester = {$search['for_semester']} ";
		if ($search['for_term'] > 0)    $sql .= " AND s.for_term = {$search['for_term']} ";

		$sql .= "
		
			LEFT JOIN rms_studentassessment AS sts
				ON sts.scoreId = s.id

			LEFT JOIN $scoreSubjectTmpList
				ON sst.group_id = g.id

			WHERE g.status = 1
		";

		/* ------------------------------------------
		SEARCH FILTERS
		------------------------------------------ */
		if (!empty($search['academic_year'])) $sql .= " AND g.academic_year = {$search['academic_year']} ";
		if (!empty($search['grade']))         $sql .= " AND g.grade = {$search['grade']} ";
		if (!empty($search['degree']))        $sql .= " AND g.degree = {$search['degree']} ";

		if (!empty($search['degreeList']))        $sql .= " AND g.degree IN ( {$search['degreeList']} )";

		/* Score combine status */
		if (!empty($search['scoreCombineStatus'])) {
			if ($search['scoreCombineStatus'] == 1) $sql .= " AND s.id IS NOT NULL ";
			if ($search['scoreCombineStatus'] == 2) $sql .= " AND s.id IS NULL ";
		}

		/* Evaluation status */
		if (!empty($search['evaluationStatus'])) {
			if ($search['evaluationStatus'] == 1) $sql .= " AND sts.id IS NOT NULL ";
			if ($search['evaluationStatus'] == 2) $sql .= " AND sts.id IS NULL ";
		}

		/* Remove NULL TMP groups */
		$sql .= " AND sst.jsonScoreTmpList IS NOT NULL ";

		/* Access control */
		$sql .= $dbp->getAccessPermission('g.branch_id');
		$sql .= $dbp->getDegreePermission('g.degree');
		$sql .= $dbp->getSchoolOptionAccess('i.schoolOption');

		$sql .= "
			GROUP BY g.id
			ORDER BY g.degree, g.grade, g.group_code ASC
		";
       // echo $sql; exit();
		return $db->fetchAll($sql);
	}
	function getScoreTmpById($score_id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT s.*,
		(SELECT c.criteriaType FROM  `rms_exametypeeng` AS c WHERE c.id = s.`criteriaId` LIMIT 1 ) AS criteriaType
		FROM rms_grading_tmp AS s WHERE s.id =$score_id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbp->getAccessPermission('branchId');
		return $db->fetchRow($sql);
	}
	function deleteTmpScore($_data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();

		try {
			
			$id= $_data['id'];

			$rs = $this->getScoreTmpById($id);
			if(empty($rs)){
				return 2;//not permission
			}
			$this->_name = "rms_grading_tmp";
			$where = " id = $id";
			$this->delete($where);

			$this->_name = 'rms_grading_detail_tmp';
			$this->delete("gradingId=" . $id);
			if(!empty($rs)){
				if ($rs['criteriaType'] == 2) {  // EXAM

					$this->_name = 'rms_grading';
					$this->delete("gradingTmpId=" . $id);
	
					$this->_name = 'rms_grading_detail';
					$this->delete("gradingTmpId=" . $id);
	
					$this->_name = 'rms_grading_total';
					$this->delete("gradingTmpId=" . $id);
				}

			}
			$db->commit();
			return 1;
		} catch (exception $e) {
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
}
