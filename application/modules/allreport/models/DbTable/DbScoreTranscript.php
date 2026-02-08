<?php

class Allreport_Model_DbTable_DbScoreTranscript extends Zend_Db_Table_Abstract
{

	function getTranscriptExam($data)
	{
		$db = $this->getAdapter();
		$studentId = $data['studentId'];
		$scoreId = $data['scoreId'];
		$arrStudent = array(
			'studentId' => $studentId
		);
		$studentInfo =  $this->getStudentProfile($arrStudent);
		$resultArray = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId
		);
		$scoreInfo =  $this->getScoreInformation($resultArray);

		$resultScoreArr = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'examType' => $scoreInfo['exam_type']
		);
		$scoreResultList =  $this->getSubjectScoreTranscript($resultScoreArr);
		
		$arreValue = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'groupId' => $scoreInfo['group_id'],
			'forType' => $scoreInfo['exam_type'],
			'forSemester' => $scoreInfo['for_semester'],
			'forMonth' => $scoreInfo['for_month'],
		);
		$resultEvalueAtion = $this->getStudentAssessmentEvaluation($arreValue);

		$result = array(
			'studentInfo' => $studentInfo,
			'scoreInfo' => $scoreInfo,
			'scoreSubjectInfo' => $scoreResultList,
			'EvalueationList' => $resultEvalueAtion,
		);
		return $result;
	}

	function getTranscriptExamTerm($data)
	{
		$db = $this->getAdapter();
		$studentId = $data['studentId'];
		$scoreId = $data['scoreId'];
		$arrStudent = array(
			'studentId' => $studentId
		);
		$studentInfo =  $this->getStudentProfile($arrStudent);
		$resultArray = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId
		);
		$scoreInfo =  $this->getScoreInformationTerm($resultArray);

		$resultScoreArr = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'groupbySubjectId' => 1,
			'examType' => $scoreInfo['exam_type']
		);
		$scoreResultList =  $this->getSubjectScoreTranscriptTerm($resultScoreArr);
		
		$arreValue = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'groupId' => $scoreInfo['group_id'],
			'forType' => $scoreInfo['exam_type'],
			'forSemester' => $scoreInfo['for_semester'],
			'forMonth' => $scoreInfo['for_month'],
			'forTerm' => $scoreInfo['for_term'],
		);
		$resultEvalueAtion = $this->getStudentAssessmentEvaluation($arreValue);

		$result = array(
			'studentInfo' => $studentInfo,
			'scoreInfo' => $scoreInfo,
			'scoreSubjectInfo' => $scoreResultList,
			'EvalueationList' => $resultEvalueAtion,
		);
		return $result;
	}

	function getTranscriptExamFinalTerm($data)
	{
		$db = $this->getAdapter();
		$studentId = $data['studentId'];
		$scoreId = $data['scoreId'];
		$arrStudent = array(
			'studentId' => $studentId
		);
		$studentInfo =  $this->getStudentProfile($arrStudent);
		$resultArray = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId
		);
		$scoreInfo =  $this->getScoreInformationFinalTerm($resultArray);

		$resultScoreArr = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'groupbySubjectId' => 1,
			'examType' => $scoreInfo['exam_type']
		);
		$scoreResultList =  $this->getSubjectScoreTranscriptFinalTerm($resultScoreArr);
		
		$arreValue = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'groupId' => $scoreInfo['group_id'],
			'forType' => $scoreInfo['exam_type'],
			'forSemester' => $scoreInfo['for_semester'],
			'forMonth' => $scoreInfo['for_month'],
		);
		$resultEvalueAtion = $this->getStudentAssessmentEvaluation($arreValue);

		$result = array(
			'studentInfo' => $studentInfo,
			'scoreInfo' => $scoreInfo,
			'scoreSubjectInfo' => $scoreResultList,
			'EvalueationList' => $resultEvalueAtion,
		);
		return $result;
	}

	function getTranscriptSemesterExam($data)
	{
		$db = $this->getAdapter();
		$studentId = $data['studentId'];
		$scoreId = $data['scoreId'];
		$arrStudent = array(
			'studentId' => $studentId
		);
		$studentInfo =  $this->getStudentProfile($arrStudent);


		$resultArray = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId
		);
		$scoreInfo =  $this->getScoreSemesterInformation($resultArray);

		$resultScoreArr = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'examType' => $scoreInfo['exam_type']
		);
		$scoreResultList =  $this->getSubjectScoreTranscript($resultScoreArr);
		$arreValue = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'groupId' => $scoreInfo['group_id'],
			'forType' => $scoreInfo['exam_type'],
			'forSemester' => $scoreInfo['for_semester'],
			'forMonth' => $scoreInfo['for_month'],
		);
		$resultEvalueAtion = $this->getStudentAssessmentEvaluation($arreValue);

		$result = array(
			'studentInfo' => $studentInfo,
			'scoreInfo' => $scoreInfo,
			'scoreSubjectInfo' => $scoreResultList,
			'EvalueationList' => $resultEvalueAtion,
		);
		return $result;
	}

	function getTranscriptAnnaulExam($data)
	{
		$db = $this->getAdapter();
		$studentId = $data['studentId'];
		$scoreId = $data['scoreId'];
		$arrStudent = array(
			'studentId' => $studentId
		);
		$studentInfo =  $this->getStudentProfile($arrStudent);


		$resultArray = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId
		);
		$scoreInfo =  $this->getScoreAnnaulInformation($resultArray);

		$resultScoreArr = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'examType' => $scoreInfo['exam_type']
		);
		$scoreResultList =  $this->getSubjectScoreTranscript($resultScoreArr);

		$arreValue = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'groupId' => $scoreInfo['group_id'],
			'forType' => $scoreInfo['exam_type'],
			'forSemester' => $scoreInfo['for_semester'],
			'forMonth' => $scoreInfo['for_month'],
		);
		$resultEvalueAtion = $this->getStudentAssessmentEvaluation($arreValue);

		$result = array(
			'studentInfo' => $studentInfo,
			'scoreInfo' => $scoreInfo,
			'scoreSubjectInfo' => $scoreResultList,
			'EvalueationList' => $resultEvalueAtion,
		);
		return $result;
	}

	function getAcademicTranscript($data)
	{
		$db = $this->getAdapter();
		$studentId = $data['studentId'];
		$scoreId = $data['scoreId'];
		$arrStudent = array(
			'studentId' => $studentId
		);
		$studentInfo =  $this->getStudentProfile($arrStudent);


		$resultArray = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId
		);
		$scoreInfo =  $this->getScoreAcsdemicInformation($resultArray);

		$resultScoreArr = array(
			'scoreId' => $scoreId,
			'studentId' => $studentId,
			'examType' => $scoreInfo['exam_type']
		);
		$scoreResultList =  $this->getSubjectScoreAcademicTranscript($resultScoreArr);
		
		$result = array(
			'studentInfo' => $studentInfo,
			'scoreInfo' => $scoreInfo,
			'scoreSubjectInfo' => $scoreResultList,
		);
		return $result;
	}

	function getSubScoreList($data)
	{
		$sql = "SELECT 
					totalGrading,
					subCriterialTitleKh,
					subCriterialTitleEng 
				FROM `rms_grading_detail` gd
				WHERE 
					gd.subCriterialTitleEng IS NOT NULL ";
		/*
		if (!empty($data['gradingId'])) {
			$sql .= " AND gd.gradingId=" . $data['gradingId'];
		}
		*/
		$data['gradingId'] = empty($data['gradingId']) ? 0 : $data['gradingId'];
		$sql .= " AND gd.gradingId=" . $data['gradingId'];
		if (!empty($data['studentId'])) {
			$sql .= " AND gd.studentId=" . $data['studentId'];
		}
		if (!empty($data['subjectId'])) {
			$sql .= " AND gd.subjectId=" . $data['subjectId'];
		}
		return $this->getAdapter()->fetchAll($sql);
	}
	function getSubjectScoreTranscriptTerm($data){
		$db = $this->getAdapter();
		
		$subjectId = empty($data['subjectId']) ? 0 : (int)$data['subjectId'];
		$scoreId   = empty($data['scoreId']) ? 0 : (int)$data['scoreId'];
		$examType  = (int)$data['examType'];
		$sql = "
			SELECT 
				subj.subject_lang AS subjectLang,
				g.semesterTotalAverage AS maxScore,
				gs.amount_subject_sem AS multiSubject,
				sd.subject_id,
				sd.gradingTotalId,
				sd.score AS totalAverage,
				
				(SELECT ms.metion_grade
					FROM rms_metionscore_setting_detail AS ms
					INNER JOIN rms_metionscore_setting AS m ON m.id = ms.metion_score_id
					WHERE 
						m.academic_year = g.academic_year AND 
						m.degree = g.degree AND 
						(sd.score / g.semesterTotalAverage * 100) >= ms.max_score
					ORDER BY ms.max_score DESC
					LIMIT 1
				) AS subjectMentionGrade,

				(SELECT ms.mention_in_english
					FROM rms_metionscore_setting_detail AS ms
					INNER JOIN rms_metionscore_setting AS m ON m.id = ms.metion_score_id
					WHERE 
						m.academic_year = g.academic_year AND 
						m.degree = g.degree AND 
						(sd.score / g.semesterTotalAverage * 100) >= ms.max_score
					ORDER BY ms.max_score DESC
					LIMIT 1
				) AS subjectMentionEng,

				sd.score_cut,
				CASE 
					WHEN sd.isCriteria = 1 THEN ext.title 
					ELSE subj.subject_titlekh 
				END AS sub_name,

				CASE 
					WHEN sd.isCriteria = 1 THEN ext.title_en 
					ELSE subj.subject_titleen 
				END AS sub_name_en,

				sd.amount_subject,
				COALESCE(gSS.innerSubject,0) AS innerSubject,
 				gSS.`innerSubjectList`,
				sd.isCriteria

			FROM rms_score_detail AS sd
			INNER JOIN rms_group AS g ON g.id = sd.group_id
			LEFT JOIN rms_group_subject_detail AS gs 
				ON gs.group_id = sd.group_id AND gs.subject_id = sd.subject_id
			LEFT JOIN rms_grade_subject_detail AS gsj 
				ON sd.subject_id = gsj.subject_id AND g.grade = gsj.grade_id
			LEFT JOIN rms_subject AS subj ON subj.id = sd.subject_id
			LEFT JOIN rms_exametypeeng AS ext ON ext.id = sd.subject_id
			  LEFT JOIN v_gradingsubsubjectscore AS gSS ON (gSS.studentId=sd.`student_id` AND gSS.gradingId=sd.gradingTotalId AND gSS.subjectId=sd.`subject_id`)
			WHERE 1
		";

		// Filters
		if (!empty($scoreId)) {
			$sql .= " AND sd.score_id = $scoreId";
		}
		if (!empty($data['studentId'])) {
			$sql .= " AND sd.student_id = " . (int)$data['studentId'];
		}
		if (!empty($subjectId)) {
			$sql .= " AND sd.subject_id = $subjectId";
		}
		if (!empty($data['groupbySubjectId'])) {
			$sql .= " GROUP BY sd.subject_id,sd.isCriteria ";
		}

		// Order
		$sql .= " ORDER BY   sd.isCriteria DESC, subj.subject_lang DESC, gsj.subject_order ASC ";
	//	echo $sql;
		return $db->fetchAll($sql);
	}

	function getSubjectScoreTranscript($data){
		$db = $this->getAdapter();
		
		$subjectId = empty($data['subjectId']) ? 0 : (int)$data['subjectId'];
		$scoreId   = empty($data['scoreId']) ? 0 : (int)$data['scoreId'];
		$examType  = (int)$data['examType'];

		// Determine which fields to use based on examType
		$amountField = ($examType === 2 || $examType === 3) ? 'amount_subject_sem' : 'amount_subject';
		$maxScoreField = ($examType === 2 || $examType === 3) ? 'semester_max_score' : 'max_score';

		// Start building query
		$sql = "
			SELECT 
				subj.subject_lang AS subjectLang,
				gs.$maxScoreField AS maxScore,
				gs.$amountField AS multiSubject,
				sd.subject_id,
				sd.gradingTotalId,
				sd.score AS totalAverage,
				
				(
					SELECT ms.metion_grade
					FROM rms_metionscore_setting_detail AS ms
					INNER JOIN rms_metionscore_setting AS m ON m.id = ms.metion_score_id
					WHERE 
						m.academic_year = g.academic_year AND 
						m.degree = g.degree AND 
						(sd.score / gs.$maxScoreField * 100) >= ms.max_score
					ORDER BY ms.max_score DESC
					LIMIT 1
				) AS subjectMentionGrade,

				FIND_IN_SET(sd.score, (
					SELECT GROUP_CONCAT(insd.score ORDER BY insd.score DESC)
					FROM rms_score_detail AS insd
					WHERE 
						insd.score_id = $scoreId AND 
						insd.subject_id = sd.subject_id
				)) AS rankingSubject,

				sd.score_cut,
				subj.subject_titlekh AS sub_name,
				subj.subject_titleen AS sub_name_en,
				sd.amount_subject,
				COALESCE(gSS.innerSubject,0) AS innerSubject,
 				gSS.`innerSubjectList`

			FROM rms_score_detail AS sd
			INNER JOIN rms_group AS g ON g.id = sd.group_id
			LEFT JOIN rms_group_subject_detail AS gs 
				ON gs.group_id = sd.group_id AND gs.subject_id = sd.subject_id
			LEFT JOIN rms_grade_subject_detail AS gsj 
				ON sd.subject_id = gsj.subject_id AND g.grade = gsj.grade_id
			LEFT JOIN rms_subject AS subj ON subj.id = sd.subject_id
			  LEFT JOIN v_gradingsubsubjectscore AS gSS ON (gSS.studentId=sd.`student_id` AND gSS.gradingId=sd.gradingTotalId AND gSS.subjectId=sd.`subject_id`)
			WHERE 1
		";

		// Filters
		if (!empty($scoreId)) {
			$sql .= " AND sd.score_id = $scoreId";
		}
		if (!empty($data['studentId'])) {
			$sql .= " AND sd.student_id = " . (int)$data['studentId'];
		}
		if (!empty($subjectId)) {
			$sql .= " AND sd.subject_id = $subjectId";
		}
		if (!empty($data['groupbySubjectId'])) {
			$sql .= " GROUP BY sd.subject_id ";
		}

		// Order
		$sql .= " ORDER BY subj.subject_lang ASC, gsj.subject_order ASC";
		//echo $sql;
		return $db->fetchAll($sql);
	}

	function getSubjectScoreAcademicTranscript($data){
		$db = $this->getAdapter();

		$subjectId = empty($data['subjectId']) ? 0 : (int)$data['subjectId'];
		$scoreId = empty($data['scoreId']) ? 0 : (int)$data['scoreId'];
		$studentId = empty($data['studentId']) ? 0 : (int)$data['studentId'];

		// Build main SQL
		$sql = "
			SELECT 
				sj.subject_lang AS subjectLang,
				gsd.semester_max_score AS maxScore,
				gsd.amount_subject_sem AS multiSubject,
				sd.subject_id,
				sd.gradingTotalId,
				sd.score AS totalAverage,

				sem1.score AS totalAvgSemester1,
				sem2.score AS totalAvgSemester2,

				sd.score_cut,
				sj.subject_titlekh AS sub_name,
				sj.subject_titleen AS sub_name_en,
				sd.amount_subject,
				(SELECT ms.metion_grade
					FROM rms_metionscore_setting_detail AS ms
					INNER JOIN rms_metionscore_setting AS m ON m.id = ms.metion_score_id
					WHERE 
						m.academic_year = g.academic_year AND 
						m.degree = g.degree AND 
						(sd.score / gsd.semester_max_score * 100) >= ms.max_score
					ORDER BY ms.max_score DESC
					LIMIT 1
				) AS subjectMentionGrade,
				(SELECT ms.metion_grade
					FROM rms_metionscore_setting_detail AS ms
					INNER JOIN rms_metionscore_setting AS m ON m.id = ms.metion_score_id
					WHERE 
						m.academic_year = g.academic_year AND 
						m.degree = g.degree AND 
						(sem1.score / gsd.semester_max_score * 100) >= ms.max_score
					ORDER BY ms.max_score DESC
					LIMIT 1
				) AS subjectMentionGradeSemester1,
				(SELECT ms.metion_grade
					FROM rms_metionscore_setting_detail AS ms
					INNER JOIN rms_metionscore_setting AS m ON m.id = ms.metion_score_id
					WHERE 
						m.academic_year = g.academic_year AND 
						m.degree = g.degree AND 
						(sem2.score / gsd.semester_max_score * 100) >= ms.max_score
					ORDER BY ms.max_score DESC
					LIMIT 1
				) AS subjectMentionGradeSemester2


			FROM rms_score_detail AS sd

			LEFT JOIN rms_group AS g ON g.id = sd.group_id
			LEFT JOIN rms_grade_subject_detail AS gsj 
				ON g.grade = gsj.grade_id AND sd.subject_id = gsj.subject_id

			LEFT JOIN rms_subject AS sj ON sj.id = sd.subject_id
			LEFT JOIN rms_group_subject_detail AS gsd 
				ON gsd.group_id = sd.group_id AND gsd.subject_id = sd.subject_id

		
			LEFT JOIN (
				SELECT sdt.subject_id, sdt.group_id, sdt.student_id, sdt.score
				FROM rms_score_detail AS sdt
				INNER JOIN rms_score AS s ON s.id = sdt.score_id
				WHERE s.exam_type = 2 AND s.for_semester = 1
			) AS sem1 
				ON sem1.subject_id = sd.subject_id 
				AND sem1.group_id = sd.group_id 
				AND sem1.student_id = sd.student_id
		
			LEFT JOIN (
				SELECT sdt.subject_id, sdt.group_id, sdt.student_id, sdt.score
				FROM rms_score_detail AS sdt
				INNER JOIN rms_score AS s ON s.id = sdt.score_id
				WHERE s.exam_type = 2 AND s.for_semester = 2
			) AS sem2 
				ON sem2.subject_id = sd.subject_id 
				AND sem2.group_id = sd.group_id 
				AND sem2.student_id = sd.student_id

			WHERE 1
		";

		// Apply filters
		if (!empty($scoreId)) {
			$sql .= " AND sd.score_id = $scoreId";
		}
		if (!empty($studentId)) {
			$sql .= " AND sd.student_id = $studentId";
		}
		if (!empty($subjectId)) {
			$sql .= " AND sd.subject_id = $subjectId";
		}
		if (!empty($data['groupbySubjectId'])) {
			$sql .= " GROUP BY sd.subject_id";
		}

		$sql .= " ORDER BY sj.subject_lang ASC, gsj.subject_order ASC";

		return $db->fetchAll($sql);
	}

	function getSubjectScoreTranscriptFinalTerm($data){
		$db = $this->getAdapter();
		
		$subjectId = empty($data['subjectId']) ? 0 : (int)$data['subjectId'];
		$scoreId   = empty($data['scoreId']) ? 0 : (int)$data['scoreId'];
		$examType  = (int)$data['examType'];
		$sql = "
			SELECT 
				subj.subject_lang AS subjectLang,
				g.semesterTotalAverage AS maxScore,
				gs.amount_subject_sem AS multiSubject,
				sd.subject_id,
				sd.gradingTotalId,
				sd.score AS totalAverage,

				term1.score AS totalAvgTerm1,
				term2.score AS totalAvgTerm2,
				term3.score AS totalAvgTerm3,
				
				(SELECT ms.metion_grade
					FROM rms_metionscore_setting_detail AS ms
					INNER JOIN rms_metionscore_setting AS m ON m.id = ms.metion_score_id
					WHERE 
						m.academic_year = g.academic_year AND 
						m.degree = g.degree AND 
						(sd.score / g.semesterTotalAverage * 100) >= ms.max_score
					ORDER BY ms.max_score DESC
					LIMIT 1
				) AS subjectMentionGrade,

				(SELECT ms.mention_in_english
					FROM rms_metionscore_setting_detail AS ms
					INNER JOIN rms_metionscore_setting AS m ON m.id = ms.metion_score_id
					WHERE 
						m.academic_year = g.academic_year AND 
						m.degree = g.degree AND 
						(sd.score / g.semesterTotalAverage * 100) >= ms.max_score
					ORDER BY ms.max_score DESC
					LIMIT 1
				) AS subjectMentionEng,

				sd.score_cut,
				CASE 
					WHEN sd.isCriteria = 1 THEN ext.title 
					ELSE subj.subject_titlekh 
				END AS sub_name,

				CASE 
					WHEN sd.isCriteria = 1 THEN ext.title_en 
					ELSE subj.subject_titleen 
				END AS sub_name_en,

				sd.amount_subject,
				sd.isCriteria

			FROM rms_score_detail AS sd
			INNER JOIN rms_group AS g ON g.id = sd.group_id
			LEFT JOIN rms_group_subject_detail AS gs 
				ON gs.group_id = sd.group_id AND gs.subject_id = sd.subject_id
			LEFT JOIN rms_grade_subject_detail AS gsj 
				ON sd.subject_id = gsj.subject_id AND g.grade = gsj.grade_id
			LEFT JOIN rms_subject AS subj ON subj.id = sd.subject_id
			LEFT JOIN rms_exametypeeng AS ext ON ext.id = sd.subject_id

			LEFT JOIN (
				SELECT 
					sdt.subject_id, 
					sdt.group_id, 
					sdt.student_id, 
					sdt.score
				FROM 
					rms_score_detail AS sdt
					INNER JOIN rms_score AS s ON s.id = sdt.score_id
				WHERE 
					s.exam_type = 4 
					AND s.status = 1
					AND s.for_term = 1
			) AS term1 
				ON term1.subject_id = sd.subject_id 
				AND term1.group_id = sd.group_id 
				AND term1.student_id = sd.student_id
				
			LEFT JOIN (
				SELECT 
					sdt.subject_id, 
					sdt.group_id, 
					sdt.student_id, 
					sdt.score
				FROM 
					rms_score_detail AS sdt
					INNER JOIN rms_score AS s ON s.id = sdt.score_id
				WHERE 
					s.exam_type = 4 
					AND s.status = 1
					AND s.for_term = 2
			) AS term2 
				ON term2.subject_id = sd.subject_id 
				AND term2.group_id = sd.group_id 
				AND term2.student_id = sd.student_id
			
			LEFT JOIN (
				SELECT 
					sdt.subject_id, 
					sdt.group_id, 
					sdt.student_id, 
					sdt.score
				FROM 
					rms_score_detail AS sdt
					INNER JOIN rms_score AS s ON s.id = sdt.score_id
				WHERE 
					s.exam_type = 4 
					AND s.status = 1
					AND s.for_term = 3
			) AS term3 
				ON term3.subject_id = sd.subject_id 
				AND term3.group_id = sd.group_id 
				AND term3.student_id = sd.student_id

			WHERE 1
		";

		// Filters
		if (!empty($scoreId)) {
			$sql .= " AND sd.score_id = $scoreId";
		}
		if (!empty($data['studentId'])) {
			$sql .= " AND sd.student_id = " . (int)$data['studentId'];
		}
		if (!empty($subjectId)) {
			$sql .= " AND sd.subject_id = $subjectId";
		}
		if (!empty($data['groupbySubjectId'])) {
			$sql .= " GROUP BY sd.subject_id ";
		}

		$sql .= " ORDER BY   sd.isCriteria DESC, subj.subject_lang DESC, gsj.subject_order ASC ";
		return $db->fetchAll($sql);
	}

	function getSubjectScoreForSemester($data)
	{ //transcript and score detail
		$db = $this->getAdapter();
		$strSubjectLange = " (SELECT subject_lang FROM `rms_subject` s WHERE 
		s.id=sd.subject_id LIMIT 1) ";
	
		$subjectId = empty($data['subjectId']) ? 0 : $data['subjectId'];
	
		$sql = "SELECT
					sd.`subject_id`,
					sd.`score` AS totalAverage
				FROM rms_score_detail AS sd
				 INNER JOIN rms_score AS s ON s.`id` = sd.score_id
				 LEFT JOIN rms_group AS g ON g.id=sd.group_id 
				 LEFT JOIN rms_grade_subject_detail AS gsj ON sd.subject_id=gsj.subject_id AND g.`grade`=gsj.`grade_id`
				WHERE 1";

		if (!empty($data['studentId'])) {
			$sql .= " AND sd.`student_id`=" . $data['studentId'];
		}
		if (!empty($subjectId)) {
			$sql .= " AND sd.`subject_id`=" . $subjectId;
		}
		if (!empty($data['examType'])) {
			$sql .= " AND s.`exam_type`=" . $data['examType'];
		}
		if (!empty($data['semester'])) {
			$sql .= " AND s.`for_semester`=" . $data['semester'];
		}
		if (!empty($data['groupId'])) {
			$sql .= " AND s.`group_id`=" . $data['groupId'];
		}
		$sql .= " ORDER  BY $strSubjectLange  ASC, gsj.subject_order  ASC ";
		return $db->fetchAll($sql);
	}


	function getScoreInformation($data)
	{
		$db = $this->getAdapter();
		$studentId = empty($data['studentId']) ? 0 : $data['studentId'];
		$scoreId = empty($data['scoreId']) ? 0 : $data['scoreId'];
		$strSubLang = " (SELECT subject_lang FROM `rms_subject` sub WHERE sub.id=sd.subject_id LIMIT 1) ";
		$sql = "SELECT 
					g.degree AS degreeId,
					g.max_average AS max_average,
					g.group_code AS groupCode,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) as academicYearLabel,
					g.academic_year as academicYearId,
					g.branch_id,
					s.id as scoreId,
					s.group_id,
					s.title_score_en,
					s.title_score,
					s.exam_type,
					s.for_month,
					(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthKhLabel,
					(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthEnLabel,
					s.for_semester,
					
					FIND_IN_SET((SELECT sm.total_score FROM rms_score_monthly sm WHERE 
				 				sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(total_score ORDER BY total_score DESC)
						FROM 
							rms_score_monthly AS dd 
						 WHERE
							s.`id`=dd.`score_id`
						)
				 ) AS rank,	
				 
				 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =1
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =1
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInKhmer,
					
					
					
					 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =2
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =2
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInEnglish,
				 
				
					 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =3
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =3
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInChinese,

					sm.total_avg AS totalAvg,
					sm.total_score AS totalScoreAvg,
					(SELECT sd.metion_grade
						FROM `rms_metionscore_setting_detail` AS sd,
							`rms_metionscore_setting` AS s
						WHERE s.id = sd.metion_score_id
							AND s.academic_year= s.for_academic_year
							AND s.degree = `g`.`degree`
							AND (sm.total_avg/g.max_average*100) >=sd.max_score 
							ORDER BY sd.max_score DESC
						LIMIT 1  ) AS mentionGrade,
				 		
				 (SELECT COUNT(sm.`id`) FROM rms_score_monthly AS sm WHERE
					s.`id`=sm.`score_id` LIMIT 1) as amountStudent
		FROM rms_score AS s 
			LEFT JOIN `rms_score_monthly` AS sm  ON ( s.`id` = sm.`score_id` AND sm.`student_id` = " . $studentId . " )
			LEFT JOIN  rms_group AS g ON s.group_id = g.id
			WHERE 1  ";
		if (!empty($data['scoreId'])) {
			$sql .= " AND s.id=" . $data['scoreId'];
		}
	
		return $db->fetchRow($sql);
	}

	function getScoreInformationTerm($data)
	{
		$db = $this->getAdapter();
		$studentId = empty($data['studentId']) ? 0 : $data['studentId'];
		$sql = "SELECT 
					g.degree AS degreeId,
					g.max_average AS max_average,
					g.group_code AS groupCode,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) as academicYearLabel,
					g.academic_year as academicYearId,
					g.branch_id,
					s.id as scoreId,
					s.group_id,
					s.title_score_en,
					s.title_score,
					s.exam_type,
					s.for_month,
					s.for_semester,
					s.for_term,
					(SELECT teacher_name_en FROM rms_teacher WHERE rms_teacher.id = g.teacher_id LIMIT 1) AS teacherName,
					(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthKhLabel,
					(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthEnLabel,
					sm.total_avg AS totalAvg,
					sm.total_score AS totalScoreAvg,
					(SELECT sd.metion_grade
						FROM `rms_metionscore_setting_detail` AS sd,
							`rms_metionscore_setting` AS s
						WHERE s.id = sd.metion_score_id
							AND s.academic_year= s.for_academic_year
							AND s.degree = `g`.`degree`
							AND (sm.total_avg/g.max_average*100) >=sd.max_score 
							ORDER BY sd.max_score DESC
						LIMIT 1  ) AS mentionGrade,
					(SELECT sd.mention_in_english
						FROM `rms_metionscore_setting_detail` AS sd,
							`rms_metionscore_setting` AS s
						WHERE s.id = sd.metion_score_id
							AND s.academic_year= s.for_academic_year
							AND s.degree = `g`.`degree`
							AND (sm.total_avg/g.max_average*100) >=sd.max_score 
							ORDER BY sd.max_score DESC
						LIMIT 1  ) AS mentionGradeEng,
				 		
				 (SELECT COUNT(sm.`id`) FROM rms_score_monthly AS sm WHERE
					s.`id`=sm.`score_id` LIMIT 1) as amountStudent
		FROM rms_score AS s 
			LEFT JOIN `rms_score_monthly` AS sm  ON ( s.`id` = sm.`score_id` AND sm.`student_id` = " . $studentId . " )
			LEFT JOIN  rms_group AS g ON s.group_id = g.id
			WHERE 1  ";
		if (!empty($data['scoreId'])) {
			$sql .= " AND s.id=" . $data['scoreId'];
		}
		return $db->fetchRow($sql);
	}

	function getScoreInformationFinalTerm($data)
	{
		$db = $this->getAdapter();
		$studentId = empty($data['studentId']) ? 0 : $data['studentId'];
		$sql = "SELECT 
					g.degree AS degreeId,
					g.max_average AS max_average,
					g.group_code AS groupCode,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) as academicYearLabel,
					g.academic_year as academicYearId,
					g.branch_id,
					s.id as scoreId,
					s.group_id,
					s.title_score_en,
					s.title_score,
					s.exam_type,
					s.for_month,
					s.for_semester,
					s.for_term,
					(SELECT teacher_name_en FROM rms_teacher WHERE rms_teacher.id = g.teacher_id LIMIT 1) AS teacherName,
					(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthKhLabel,
					(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthEnLabel,
					sm.total_avg AS totalAvg,
					sm.total_score AS totalScoreAvg,
					(SELECT sd.metion_grade
						FROM `rms_metionscore_setting_detail` AS sd,
							`rms_metionscore_setting` AS s
						WHERE s.id = sd.metion_score_id
							AND s.academic_year= s.for_academic_year
							AND s.degree = `g`.`degree`
							AND (sm.total_avg/g.max_average*100) >=sd.max_score 
							ORDER BY sd.max_score DESC
						LIMIT 1  ) AS mentionGrade,
					(SELECT sd.mention_in_english
						FROM `rms_metionscore_setting_detail` AS sd,
							`rms_metionscore_setting` AS s
						WHERE s.id = sd.metion_score_id
							AND s.academic_year= s.for_academic_year
							AND s.degree = `g`.`degree`
							AND (sm.total_avg/g.max_average*100) >=sd.max_score 
							ORDER BY sd.max_score DESC
						LIMIT 1  ) AS mentionGradeEng,
				 		
				 (SELECT COUNT(sm.`id`) FROM rms_score_monthly AS sm WHERE
					s.`id`=sm.`score_id` LIMIT 1) as amountStudent,
					t1.total_avg AS total_avg_term_1,
					t2.total_avg AS total_avg_term_2,
					t3.total_avg AS total_avg_term_3
		FROM rms_score AS s 
			LEFT JOIN `rms_score_monthly` AS sm  ON ( s.`id` = sm.`score_id` AND sm.`student_id` = " . $studentId . " )
			LEFT JOIN  rms_group AS g ON s.group_id = g.id

			LEFT JOIN (
				SELECT ml.student_id, ml.total_avg, d.group_id
				FROM rms_score_monthly AS ml 
				INNER JOIN rms_score AS d ON d.id = ml.score_id
				WHERE d.exam_type = 4 AND d.for_term = 1
			) AS t1 ON t1.student_id = $studentId AND t1.group_id = s.group_id

			LEFT JOIN (
				SELECT ml.student_id, ml.total_avg, d.group_id
				FROM rms_score_monthly AS ml 
				INNER JOIN rms_score AS d ON d.id = ml.score_id
				WHERE d.exam_type = 4 AND d.for_term = 2
			) AS t2 ON t2.student_id = $studentId AND t2.group_id = s.group_id

			LEFT JOIN (
				SELECT ml.student_id, ml.total_avg, d.group_id
				FROM rms_score_monthly AS ml 
				INNER JOIN rms_score AS d ON d.id = ml.score_id
				WHERE d.exam_type = 4 AND d.for_term = 3
			) AS t3 ON t3.student_id = $studentId AND t3.group_id = s.group_id

			WHERE 1  ";
		if (!empty($data['scoreId'])) {
			$sql .= " AND s.id=" . $data['scoreId'];
		}
		return $db->fetchRow($sql);
	}

	function getScoreAnnaulInformation($data)
	{
		$db = $this->getAdapter();
		$studentId = empty($data['studentId']) ? 0 : $data['studentId'];
		$scoreId = empty($data['scoreId']) ? 0 : $data['scoreId'];
		$strSubLang = " (SELECT subject_lang FROM `rms_subject` sub WHERE sub.id=sd.subject_id LIMIT 1) ";
		$sql = "SELECT 
					g.degree AS degreeId,
					g.group_code AS groupCode,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) as academicYearLabel,
					g.academic_year as academicYearId,
					g.semesterTotalAverage as semesterTotalAverage,
					g.branch_id,
					s.id as scoreId,
					s.group_id,
					s.title_score_en,
					s.title_score,
					s.exam_type,
					s.for_month,
					s.note as promoteResult,
					(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthKhLabel,
					(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthEnLabel,
					s.for_semester,
					
					FIND_IN_SET((SELECT sm.total_avg FROM rms_score_monthly sm WHERE 
				 				sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(total_avg ORDER BY total_avg DESC)
						FROM 
							rms_score_monthly AS dd 
						 WHERE
							s.`id`=dd.`score_id`
						)
				 ) AS rank,	

				 FIND_IN_SET((SELECT sm.overallAssessmentSemester FROM rms_score_monthly sm WHERE 
				 sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(overallAssessmentSemester ORDER BY overallAssessmentSemester DESC)
						FROM 
							rms_score_monthly AS dd 
						WHERE
							s.`id`=dd.`score_id`
						)
				) AS rankOverall,	

				FIND_IN_SET( 
					COALESCE((SELECT ms.OveralAvgKh FROM rms_score_monthly AS ms WHERE ms.score_id = s.`id` AND ms.student_id = " . $data['studentId'] . " LIMIT 1),'0'), 
					(    
					  SELECT 
						GROUP_CONCAT( dd.OveralAvgKh ORDER BY dd.OveralAvgKh DESC ) 
					  FROM rms_score_monthly AS dd 
					  WHERE  dd.score_id= s.`id`
					)
				  ) AS rankOverallKh,

			   FIND_IN_SET((SELECT sm.OveralAvgEng FROM rms_score_monthly sm WHERE 
			   sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
				  (SELECT GROUP_CONCAT(OveralAvgEng ORDER BY OveralAvgEng DESC)
					  FROM 
						  rms_score_monthly AS dd 
					  WHERE
						  s.`id`=dd.`score_id`
					  )
			  ) AS rankOverallEng,	

			  FIND_IN_SET((SELECT sm.OveralAvgCh FROM rms_score_monthly sm WHERE 
			  sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
				 (SELECT GROUP_CONCAT(OveralAvgCh ORDER BY OveralAvgCh DESC)
					 FROM 
						 rms_score_monthly AS dd 
					 WHERE
						 s.`id`=dd.`score_id`
					 )
			 ) AS rankOverallCh,	
				 
				 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =1
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =1
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInKhmer,
					
					
					
					 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =2
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =2
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInEnglish,
				 
				
					 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =3
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =3
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInChinese,
				 
				 
				 (SELECT sm.total_avg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalAvg,
				 		
				  (SELECT sm.total_score 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalScoreAvg,

				 (SELECT sm.totalMaxScore 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalMaxScore,


				 (SELECT sm.totalKhAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalKhAvg,
				(SELECT sm.totalEnAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalEnAvg,
				(SELECT sm.totalChAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalChAvg,
				(SELECT sm.OveralAvgKh 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgKh,
				(SELECT sm.OveralAvgEng 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgEng,
				(SELECT sm.OveralAvgCh 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgCh,		

				 (SELECT sm.monthlySemesterAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS monthlySemesterAvg,

				(SELECT sm.overallAssessmentSemester 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS overallAssessmentSemester,
				 		
				 (SELECT COUNT(sm.`id`) FROM rms_score_monthly AS sm WHERE
					s.`id`=sm.`score_id` LIMIT 1) as amountStudent,

				(SELECT COUNT(sm.`id`) 
				 FROM rms_score_monthly AS sm 
				 INNER JOIN rms_score d ON d.id = sm.score_id 
				 WHERE 
				    d.exam_type = 2 
				   AND d.for_semester = 1
				   AND d.group_id = s.group_id
				) AS amountStudentS1,

				(SELECT COUNT(sm.`id`) 
				 FROM rms_score_monthly AS sm 
				 INNER JOIN rms_score d ON d.id = sm.score_id 
				 WHERE  d.exam_type = 2 
				   AND d.for_semester = 2
				   AND d.group_id = s.group_id
				) AS amountStudentS2,

				(SELECT ml.overallAssessmentSemester FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS overalSemester1,
				(SELECT ml.overallAssessmentSemester FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`   LIMIT 1) AS overalSemester2,
				(SELECT ml.total_score FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS totalScoreSemester1,
				(SELECT ml.total_score FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`   LIMIT 1) AS totalScoreSemester2,

				FIND_IN_SET(
					COALESCE((
						SELECT sm.overallAssessmentSemester 
						FROM rms_score_monthly sm 
						INNER JOIN rms_score d ON d.id = sm.score_id 
						WHERE d.exam_type = 2 
						AND d.for_semester = 1 
						AND sm.student_id = " . $studentId . " 
						AND d.group_id = s.group_id 
						LIMIT 1
					), 0),
					(
						SELECT GROUP_CONCAT(dd.overallAssessmentSemester ORDER BY dd.overallAssessmentSemester DESC) 
						FROM rms_score_monthly dd 
						INNER JOIN rms_score d ON d.id = dd.score_id 
						WHERE d.exam_type = 2 
						AND d.for_semester = 1 
						AND d.group_id = s.group_id
					)
				) AS rankSemester1,
				
				FIND_IN_SET(
					COALESCE((
						SELECT sm.overallAssessmentSemester 
						FROM rms_score_monthly sm 
						INNER JOIN rms_score d ON d.id = sm.score_id 
						WHERE d.exam_type = 2 
						AND d.for_semester = 2 
						AND sm.student_id = " . $studentId . " 
						AND d.group_id = s.group_id 
						LIMIT 1
					), 0),
					(
						SELECT GROUP_CONCAT(dd.overallAssessmentSemester ORDER BY dd.overallAssessmentSemester DESC) 
						FROM rms_score_monthly dd 
						INNER JOIN rms_score d ON d.id = dd.score_id 
						WHERE d.exam_type = 2 
						AND d.for_semester = 2 
						AND d.group_id = s.group_id
					)
				) AS rankSemester2

			FROM 
				rms_score AS s,
				rms_group g
			WHERE s.group_id=g.id ";
		if (!empty($data['scoreId'])) {
			$sql .= " AND s.id=" . $data['scoreId'];
		}

		return $db->fetchRow($sql);
	}

	function getScoreAcsdemicInformation($data)
	{
		$db = $this->getAdapter();
		$studentId = empty($data['studentId']) ? 0 : $data['studentId'];
		$scoreId = empty($data['scoreId']) ? 0 : $data['scoreId'];
		$strSubLang = " (SELECT subject_lang FROM `rms_subject` sub WHERE sub.id=sd.subject_id LIMIT 1) ";
		$sql = "SELECT 
					g.degree AS degreeId,
					g.group_code AS groupCode,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) as academicYearLabel,
					g.academic_year as academicYearId,
					g.semesterTotalAverage as semesterTotalAverage,
					g.branch_id,
					s.id as scoreId,
					s.group_id,
					s.title_score_en,
					s.title_score,
					s.exam_type,
					s.for_month,
					s.note as promoteResult,
					(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthKhLabel,
					(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthEnLabel,
					s.for_semester,
				 
					(SELECT sm.total_avg 
							FROM rms_score_monthly sm WHERE 
							sm.score_id=s.id 
							AND sm.student_id=" . $studentId . " LIMIT 1) AS totalAvg,
							
					(SELECT sm.total_score 
							FROM rms_score_monthly sm WHERE 
							sm.score_id=s.id 
							AND sm.student_id=" . $studentId . " LIMIT 1) AS totalScoreAvg,

					(SELECT sm.totalMaxScore 
							FROM rms_score_monthly sm WHERE 
							sm.score_id=s.id 
							AND sm.student_id=" . $studentId . " LIMIT 1) AS totalMaxScore,


					(SELECT sm.totalKhAvg 
							FROM rms_score_monthly sm WHERE 
							sm.score_id=s.id 
							AND sm.student_id=" . $studentId . " LIMIT 1) AS totalKhAvg,
					(SELECT sm.totalEnAvg 
							FROM rms_score_monthly sm WHERE 
							sm.score_id=s.id 
							AND sm.student_id=" . $studentId . " LIMIT 1) AS totalEnAvg,
					(SELECT sm.totalChAvg 
							FROM rms_score_monthly sm WHERE 
							sm.score_id=s.id 
							AND sm.student_id=" . $studentId . " LIMIT 1) AS totalChAvg,

					(SELECT ml.totalKhAvg FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS monthlyAvgKhSemester1,
					(SELECT ml.totalEnAvg FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS monthlyAvgEnSemester1,
					(SELECT ml.totalChAvg FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS monthlyAvgChSemester1,

					(SELECT ml.totalKhAvg FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS monthlyAvgKhSemester2,
					(SELECT ml.totalEnAvg FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS monthlyAvgEnSemester2,
					(SELECT ml.totalChAvg FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS monthlyAvgChSemester2,
					
					(SELECT ml.OveralAvgKh FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS OveralAvgKhSemester1,
					(SELECT ml.OveralAvgEng FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS OveralAvgEnSemester1,
					(SELECT ml.OveralAvgCh FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS OveralAvgChSemester1,

					(SELECT ml.OveralAvgKh FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS OveralAvgKhSemester2,
					(SELECT ml.OveralAvgEng FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS OveralAvgEnSemester2,
					(SELECT ml.OveralAvgCh FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS OveralAvgChSemester2,

					(SELECT sm.OveralAvgKh 
							FROM rms_score_monthly sm WHERE 
							sm.score_id=s.id 
							AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgKh,
					(SELECT sm.OveralAvgEng 
							FROM rms_score_monthly sm WHERE 
							sm.score_id=s.id 
							AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgEng,
					(SELECT sm.OveralAvgCh 
							FROM rms_score_monthly sm WHERE 
							sm.score_id=s.id 
							AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgCh,	
						

					(SELECT sm.monthlySemesterAvg 
							FROM rms_score_monthly sm WHERE 
							sm.score_id=s.id 
							AND sm.student_id=" . $studentId . " LIMIT 1) AS monthlySemesterAvg,

					(SELECT sm.overallAssessmentSemester 
							FROM rms_score_monthly sm WHERE 
							sm.score_id=s.id 
							AND sm.student_id=" . $studentId . " LIMIT 1) AS overallAssessmentSemester,
							
					(SELECT COUNT(sm.`id`) FROM rms_score_monthly AS sm WHERE
						s.`id`=sm.`score_id` LIMIT 1) as amountStudent,

					(SELECT ml.overallAssessmentSemester FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS overalSemester1,
					(SELECT ml.overallAssessmentSemester FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`   LIMIT 1) AS overalSemester2,

					(SELECT ml.monthlySemesterAvg FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS monthlySemester1,
					(SELECT ml.monthlySemesterAvg FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`   LIMIT 1) AS monthlySemester2,

					(SELECT ml.total_score FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=1 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`  LIMIT 1) AS totalScoreSemester1,
					(SELECT ml.total_score FROM rms_score_monthly AS ml INNER JOIN rms_score AS d ON d.`id` = ml.score_id WHERE d.exam_type=2 AND  d.`for_semester`=2 AND ml.student_id= " . $studentId . " AND d.group_id = s.`group_id`   LIMIT 1) AS totalScoreSemester2

				FROM 
					rms_score AS s,
					rms_group g
				WHERE s.group_id=g.id ";
		if (!empty($data['scoreId'])) {
			$sql .= " AND s.id=" . $data['scoreId'];
		}

		return $db->fetchRow($sql);
	}

	function getScoreSemesterInformation($data)
	{
		$db = $this->getAdapter();
		$studentId = empty($data['studentId']) ? 0 : $data['studentId'];
		$scoreId = empty($data['scoreId']) ? 0 : $data['scoreId'];
		$strSubLang = " (SELECT subject_lang FROM `rms_subject` sub WHERE sub.id=sd.subject_id LIMIT 1) ";
		$sql = "SELECT 
					g.degree AS degreeId,
					g.group_code AS groupCode,
					(SELECT CONCAT(fromYear,'-',toYear) FROM rms_academicyear WHERE rms_academicyear.id=g.academic_year LIMIT 1) as academicYearLabel,
					g.academic_year as academicYearId,
					g.semesterTotalAverage as semesterTotalAverage,
					g.branch_id,
					s.id as scoreId,
					s.group_id,
					s.title_score_en,
					s.title_score,
					s.exam_type,
					s.for_month,
					(SELECT month_kh FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthKhLabel,
					(SELECT month_en FROM rms_month WHERE rms_month.id = s.for_month LIMIT 1) AS forMonthEnLabel,
					s.for_semester,
					
					FIND_IN_SET((SELECT sm.total_avg FROM rms_score_monthly sm WHERE 
				 				sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(total_avg ORDER BY total_avg DESC)
						FROM 
							rms_score_monthly AS dd 
						 WHERE
							s.`id`=dd.`score_id`
						)
				 ) AS rank,	

				 FIND_IN_SET((SELECT sm.overallAssessmentSemester FROM rms_score_monthly sm WHERE 
				 sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
					(SELECT GROUP_CONCAT(overallAssessmentSemester ORDER BY overallAssessmentSemester DESC)
						FROM 
							rms_score_monthly AS dd 
						WHERE
							s.`id`=dd.`score_id`
						)
				) AS rankOverall,	

				FIND_IN_SET( 
					COALESCE((SELECT ms.OveralAvgKh FROM rms_score_monthly AS ms WHERE ms.score_id = s.`id` AND ms.student_id = " . $data['studentId'] . " LIMIT 1),'0'), 
					(    
					  SELECT 
						GROUP_CONCAT( dd.OveralAvgKh ORDER BY dd.OveralAvgKh DESC ) 
					  FROM rms_score_monthly AS dd 
					  WHERE  dd.score_id= s.`id`
					)
				  ) AS rankOverallKh,

			   FIND_IN_SET((SELECT sm.OveralAvgEng FROM rms_score_monthly sm WHERE 
			   sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
				  (SELECT GROUP_CONCAT(OveralAvgEng ORDER BY OveralAvgEng DESC)
					  FROM 
						  rms_score_monthly AS dd 
					  WHERE
						  s.`id`=dd.`score_id`
					  )
			  ) AS rankOverallEng,	

			  FIND_IN_SET((SELECT sm.OveralAvgCh FROM rms_score_monthly sm WHERE 
			  sm.score_id=s.id AND sm.student_id=" . $data['studentId'] . " LIMIT 1),
				 (SELECT GROUP_CONCAT(OveralAvgCh ORDER BY OveralAvgCh DESC)
					 FROM 
						 rms_score_monthly AS dd 
					 WHERE
						 s.`id`=dd.`score_id`
					 )
			 ) AS rankOverallCh,	
				 
				 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =1
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =1
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInKhmer,
					
					
					
					 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =2
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =2
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInEnglish,
				 
				
					 FIND_IN_SET((SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
				 	 sd.`score_id`=$scoreId  
					 AND sd.`student_id`=$studentId
					 AND $strSubLang =3
					),
					
					(SELECT GROUP_CONCAT(totalScore ORDER BY totalScore DESC)
					FROM (
						SELECT SUM(sd.score) AS totalScore  FROM rms_score_detail AS sd WHERE 
						sd.`score_id`=$scoreId 
						AND $strSubLang =3
						GROUP BY sd.`student_id`
					) AS StGroupconcateKH)) AS rankingInChinese,
				 
				 
				 (SELECT sm.total_avg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalAvg,
				 		
				  (SELECT sm.total_score 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalScoreAvg,

				 (SELECT sm.totalMaxScore 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalMaxScore,


				 (SELECT sm.totalKhAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalKhAvg,
				(SELECT sm.totalEnAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalEnAvg,
				(SELECT sm.totalChAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS totalChAvg,
				(SELECT sm.OveralAvgKh 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgKh,
				(SELECT sm.OveralAvgEng 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgEng,
				(SELECT sm.OveralAvgCh 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS OveralAvgCh,		

				 (SELECT sm.monthlySemesterAvg 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS monthlySemesterAvg,

				(SELECT sm.overallAssessmentSemester 
				 		FROM rms_score_monthly sm WHERE 
				 		sm.score_id=s.id 
				 		AND sm.student_id=" . $studentId . " LIMIT 1) AS overallAssessmentSemester,
				 		
				 (SELECT COUNT(sm.`id`) FROM rms_score_monthly AS sm WHERE
					s.`id`=sm.`score_id` LIMIT 1) as amountStudent
			FROM 
				rms_score AS s,
				rms_group g
			WHERE s.group_id=g.id ";
		if (!empty($data['scoreId'])) {
			$sql .= " AND s.id=" . $data['scoreId'];
		}

		return $db->fetchRow($sql);
	}

	function getStudentProfile($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT 
			stu_id AS student_id,
			stu_code,
			stu_khname,
			last_name,
			stu_enname,
			sex,
			photo,
			DATE_FORMAT(dob,'%d-%m-%Y') As dob
		 FROM rms_student WHERE 1 ";
		if (!empty($data['studentId'])) {
			$sql .= " AND stu_id=" . $data['studentId'];
		}
		return $db->fetchRow($sql);
	}
	function getStudentAssessmentEvaluation($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT 
				smd.commentId,
				(SELECT cm.`comment` FROM `rms_comment` cm WHERE cm.id=smd.commentId LIMIT 1) commentLabel,
				(SELECT r.rating FROM `rms_rating` r WHERE r.id=smd.ratingId LIMIT 1) ratingLabel,
				(SELECT r.emoji FROM `rms_rating` r WHERE r.id=smd.ratingId LIMIT 1) emoji,
				(SELECT cm.`commentType` FROM `rms_comment` cm WHERE cm.id=smd.commentId LIMIT 1) AS commentTypeId,
				(SELECT CONCAT(v.name_kh,' ',v.name_en) FROM `rms_view` AS v WHERE key_code=(SELECT `commentType` FROM `rms_comment` cm WHERE cm.id=smd.commentId LIMIT 1) AND v.type=36) AS commentType,
				(SELECT v.name_en FROM `rms_view` AS v WHERE v.key_code=(SELECT cm.`commentType` FROM `rms_comment` cm WHERE cm.id=smd.commentId LIMIT 1) AND v.type=36) AS commentTypeEng,
				(SELECT v.name_kh FROM `rms_view` AS v WHERE v.key_code=(SELECT cm.`commentType` FROM `rms_comment` cm WHERE cm.id=smd.commentId LIMIT 1) AND v.type=36) AS commentTypeKh,
				smd.teacherComment
			FROM `rms_studentassessment` AS sm,
				`rms_studentassessment_detail` AS smd
				WHERE smd.assessmentId=sm.id ";
		if (!empty($data['studentId'])) {
			$sql .= " AND smd.studentId=" . $data['studentId'];
		}
		if (!empty($data['scoreId'])) {
			$sql .= " AND sm.scoreId=" . $data['scoreId'];
		}
		if (!empty($data['groupId'])) {
			$sql .= " AND sm.groupId=" . $data['groupId'];
		}
		if (!empty($data['forType'])) {
			$sql .= " AND sm.forType=" . $data['forType'];
		}
		if (!empty($data['forSemester'])) {
			$sql .= " AND sm.forSemester=" . $data['forSemester'];
		}
		if (!empty($data['forTerm'])) {
			$sql .= " AND sm.forTerm=" . $data['forTerm'];
		}
		if (($data['forType']) == 1) {

			$sql .= " AND sm.forType=" . $data['forType'];

			if ($data['forType'] == 1 and !empty($data['forMonth'])) {
				$sql .= " AND sm.forMonth=" . $data['forMonth'];
			}
		}

		$sql .= " ORDER BY (SELECT `commentType` FROM `rms_comment` cm WHERE cm.id=commentId LIMIT 1) ASC,smd.id ASC ";
		//echo $sql;
		return $db->fetchAll($sql);
	}
	
	function getScoreEntrySettingInfo($data){ // for get validation date of monthly result for counting attendence and discipline In Monthly period
		$db = $this->getAdapter();
		$forSemester = empty($data["semesterId"]) ? "0" : $data["semesterId"];
		$forMonth = empty($data["forMonth"]) ? "0" : $data["forMonth"];
		$degree = empty($data["degree"]) ? "0" : $data["degree"];
		$examType = !empty($data['examType'])?$data['examType']:1;
		$sqlGrad="
			SELECT 
				stt.*  
			FROM rms_score_entry_setting AS stt 
			WHERE  stt.`status` =1 
				AND stt.`forSemester` = ".$forSemester."
				AND stt.`forMonth` = ".$forMonth." 
				AND stt.`examType` = ".$examType."
				AND FIND_IN_SET('".$degree."',stt.degreeId) 
			ORDER BY stt.id DESC 
			LIMIT 1
		";
		 return $db->fetchRow($sqlGrad);
	}
	function countAttendenceTranscript($data = null)
	{
		
		$db = $this->getAdapter();
		
		$examType = !empty($data['examType'])?$data['examType']:1;
		$entrySetting = array();
		if($examType==1){
			$entrySetting = $this->getScoreEntrySettingInfo($data);
		}
		
		$sql = "SELECT
			COUNT(satd.id) AS attendence
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE sat.id = satd.attendence_id
			AND sat.type=1 ";

		if (!empty($data['groupId'])) {
			$sql .= " AND sat.group_id=" . $data['groupId'];
		}
		
		if (!empty($data['studentId'])) {
			$sql .= " AND satd.stu_id=" . $data['studentId'];
		}
		
		if (!empty($data['semesterId'])) {
			$sql .= " AND sat.for_semester=" . $data['semesterId'];
		}
		if (!empty($data['attStatus'])) {
			$sql .= " AND satd.attendence_status=" . $data['attStatus'];
		}
		
		if(!empty($entrySetting)){
			$sql.=" AND sat.`date_attendence` >='".$entrySetting["fromDate"]."' AND sat.`date_attendence` <= '".$entrySetting["endDate"]."' ";
		}else{
			if($examType==1){
				if (!empty($data['forMonth'])) {
					$sql .= " AND EXTRACT(MONTH FROM sat.date_attendence)=" . $data['forMonth'];
				}
			}
		}
		
		$sql .= " GROUP BY sat.date_attendence ORDER BY satd.attendence_status DESC";
		//echo $sql;
		return $db->fetchAll($sql);
	}
	function countAnnaulAttendence($data = null)
	{
		$db = $this->getAdapter();
		
		$sql = "SELECT
			COUNT(satd.id) AS attendence
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE sat.id = satd.attendence_id
			AND sat.type=1 ";

		if (!empty($data['groupId'])) {
			$sql .= " AND sat.group_id=" . $data['groupId'];
		}
		
		if (!empty($data['studentId'])) {
			$sql .= " AND satd.stu_id=" . $data['studentId'];
		}
	
		if (!empty($data['attStatus'])) {
			$sql .= " AND satd.attendence_status=" . $data['attStatus'];
		}
		
		$sql .= " GROUP BY sat.date_attendence ORDER BY satd.attendence_status DESC";
		//echo $sql;
		return $db->fetchAll($sql);
	}
	function countDisplineTranscript($data = null)
	{
		$db = $this->getAdapter();
		$examType = !empty($data['examType'])?$data['examType']:1;
		$entrySetting = array();
		if($examType==1){
			$entrySetting = $this->getScoreEntrySettingInfo($data);
		}
		
		$sql = "SELECT
			COUNT(satd.id) AS attendence
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE sat.id = satd.attendence_id
			AND sat.type=2 ";

	
		if (!empty($data['groupId'])) {
			$sql .= " AND sat.group_id=" . $data['groupId'];
		}
	
		if (!empty($data['studentId'])) {
			$sql .= " AND satd.stu_id=" . $data['studentId'];
		}
		if (!empty($data['semesterId'])) {
			$sql .= " AND sat.for_semester=" . $data['semesterId'];
		}

		if (!empty($data['attStatus'])) {
			$sql .= " AND satd.attendence_status=" . $data['attStatus'];
		}
		
		if(!empty($entrySetting)){
			$sql.=" AND sat.`date_attendence` >='".$entrySetting["fromDate"]."' AND sat.`date_attendence` <= '".$entrySetting["endDate"]."' ";
		}else{
			if($examType==1){
				if (!empty($data['forMonth'])) {
					$sql .= " AND EXTRACT(MONTH FROM sat.date_attendence)=" . $data['forMonth'];
				}
			}
		}
		$sql .= " LIMIT 1";
		
		return $db->fetchOne($sql);
	}
	function countAnnaulDispline($data = null)
	{
		$db = $this->getAdapter();
		
		$sql = "SELECT
			COUNT(satd.id) AS attendence
		FROM
			`rms_student_attendence` AS sat,
			`rms_student_attendence_detail` AS satd
		WHERE sat.id = satd.attendence_id
			AND sat.type=2 ";
	
		if (!empty($data['groupId'])) {
			$sql .= " AND sat.group_id=" . $data['groupId'];
		}
	
		if (!empty($data['studentId'])) {
			$sql .= " AND satd.stu_id=" . $data['studentId'];
		}
	
		if (!empty($data['attStatus'])) {
			$sql .= " AND satd.attendence_status=" . $data['attStatus'];
		}
		$sql .= " LIMIT 1";
		
		return $db->fetchOne($sql);
	}
}
