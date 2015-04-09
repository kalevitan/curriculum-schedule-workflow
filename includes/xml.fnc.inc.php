<?php
//---------------------------------------------------------------------------------
// query available subjects and list the Abbreviation(add subject name later)
//---------------------------------------------------------------------------------
function list_available_subjects($schedule) {
	global $db;
	$general_subject = '';
	$query = 'SELECT DISTINCT subject FROM '.$schedule.' ORDER BY subject';
	$result = $db->query($query);

	$results = "
	<form action='index.php?general_subject2=$general_subject' class=\"form\">
		<div class=\"row\">
			<div class='form-group col-sm-6'>
				<select name='general_subject2' class=\"form-control\">";
	while ($gensub = $result->fetch()) {
	  $general_subject = $gensub['subject']; //populate variable general subject
	  $results .= "<option value='$general_subject'>$general_subject - " . getHeading($general_subject) . " </option>";
	}
	$results .= "
				</select>
			</div>
			<div class='form-group col-sm-6'>
				<button type=\"submit\" class=\"btn btn-default col-sm-3\">Show Subject</button>
			</div>
		</div>
	</form>";
	return $results;
}


//---------------------------------------------------------------------------------
// The script to query the individual courses by subject and number
//---------------------------------------------------------------------------------
function listCoursesTwo($general_subject, $general_subject2, $cnum, $title, $delivery, $location, $campus, $building, $weeks, $schedule, $days) {
	// The basic SELECT by search criteria stored as variables
	$select = "SELECT DISTINCT ID, subject, course";
	$from   = " FROM $schedule";
	$where  = " WHERE 1=1";
	$order	= ' ORDER BY subject, course'; //removed 'section' to keep section designators in order
	$limit  = '';

	if ($title !='') { // A title is selected
	  $where .= " AND short_title LIKE '%$title%'";
	}

	if ($general_subject !='') { // A general subject is selected
	  $where .= " AND subject LIKE '%$general_subject%'";
	}

	if ($general_subject2 !='') { // A general subject is selected
		$where .= " AND subject='$general_subject2'";
	}

	if ($cnum !='') { // A course number is selected
	  $where .= " AND course='$cnum'";
	}

	if ($title !='') { // title is selected
	  $where .= " AND short_title LIKE '%$title%'";
	}

	if ($delivery !='') { // delivery is specified
	  $where .= " AND delivery ='$delivery'";
	}

	if ($location !='') { // Location is specified
	  $where .= " AND location ='$location'";
	}

	if ($campus !='') { // campus
	  $where .= " AND section LIKE '%$campus%'";
	}

	if ($building !='') { // building is specified
	  $where .= " AND building ='$building'";
	}

	if ($days !='') { // check for days offered. This instruction is first inserted to search for weekend classes
	  $where .= " AND days LIKE 'S' OR days LIKE 'SSU' OR days LIKE 'FS'";
	}

	if ($weeks !='') { // checking for short term flexible classes
	  if($weeks=='14') {
			$where .= " AND weeks = '$weeks'";
	  }
	  if($weeks=='MM') { // checking for minimester classes
	  	$where .= " AND weeks BETWEEN 4 AND 13";
	  }
	}

	global $db;
	$query = $select . $from . $where . $order . $limit;
	$courses = $db->query($query);
	return $courses;

}


//---------------------------------------------------------------------------------
// convert query data to an array and add xml tags to it
//---------------------------------------------------------------------------------
function query_to_xhtml_three($courses, $schedule) {
	$results = '';
	$column_checks = array();
	$classID = '';
	$subject = '';
	$number = '';
	$row = '';
	$lastSubject = '';
	$lastSubjectNumber = '';
	$subjectNumber='';
	$prerequisite = '';
	$corequisite = '';
	$description = '';
	$credit_hours = '';
	$contact_hours = '';
	$i='';
	$rows_count = '';
	$last = ''; //initializing variable last, setting a blank value

	while($row = $courses->fetch())  {
		$classID = $row['ID'];
		$desc = "desc" . $classID;
		$subject = $row['subject'];
		//Add preceding zero to developmental courses
		if(strlen($row['course']) == 2){
			$subjectNumber = "0" . $row['course'];
		}
		else {
			$subjectNumber = $row['course'];
		}

		if($lastSubject != $subject) {
        $results=str_replace('aid:trows=placeholder', 'aid:trows="' . $rows_count . '"', $results);
        $rows_count = 1;
			if($results != '') { $results .= '</table>'."\n"; }
			//changed number of columns (tcols) from "19" to "20" (traditional) - klevitan 9-24-2014
			$results .= '<table aid:table="table" aid:trows=placeholder aid:tcols="20" aid:ccolwidth="520">';
			$results .= '<Cell aid:table="cell" aid:theader="" aid:crows="1" aid:ccols="20" aid:ccolwidth="520"><heading aid:pstyle="heading">' . $subject . " : " . getHeading($subject) . '</heading></Cell>';
		}

		if($lastSubject.$lastSubjectNumber != $subject.$subjectNumber) {
			$title = getCourseDetails("title", $subject, $subjectNumber);
			$title = str_replace('&', '&amp;', $title);
			$prerequisite = getCourseDetails("prerequisite", $subject, $subjectNumber);
			$corequisite = getCourseDetails("corequisite", $subject, $subjectNumber);
			$description = getCourseDetails("description", $subject, $subjectNumber);
			$credit_hours = getCourseDetails("credit_hours", $subject, $subjectNumber);
			$contact_hours = getCourseDetails("contact_hours", $subject, $subjectNumber);
			//changed column width (ccolwidth) from 196pt to 177pt to accomodate new column (traditional) - klevitan 9-24-2014
			$results .= '<Cell aid:table="cell" aid:crows="1" aid:ccols="1" aid:ccolwidth="177"><title aid:cstyle="Title">' .$subject." ".$subjectNumber." ". $title . '</title>'."\n".'<creditHours aid:cstyle="creditHours">Credit hours: '.$credit_hours.' | </creditHours><contactHours aid:cstyle="contactHours">Contact hours: '.$contact_hours.'</contactHours>'."\n".'<prerequisite aid:cstyle="prerequisite">Prerequisite: '. $prerequisite .' | </prerequisite><corequisite aid:cstyle="corequisite">Corequisite: '. $corequisite .'</corequisite></Cell>';
		    $column_checks = populate_course_columns($subject, $subjectNumber, $schedule);
		    $results .= '<cell aid:table="cell" aid:crows="1" aid:ccols="1" aid:ccolwidth="19">' . implode('</cell><cell aid:table="cell" aid:crows="1" aid:ccols="1" aid:ccolwidth="18">', (array)$column_checks) . '</cell>';
    	    $rows_count++;
		}

		$last=$row;
		$lastSubject=$subject;
		$lastSubjectNumber=$subjectNumber;
	}
	//print the ending table tag
	$results .= '</table>';
	return $results;
}//query_to_xhtml_three


//---------------------------------------------------------------------------------
// query type, time, format, and location
//---------------------------------------------------------------------------------
function populate_course_columns($subject, $number, $schedule) {
	$select = "SELECT ID, delivery, location, subject, course, section, weeks, StartDate, CoReqSecName";
	$from   = " FROM $schedule";
	$where  = " WHERE subject='$subject' AND course = '$number'";

	global $db;
	$count = $db->query('SELECT COUNT(*)' . $from . $where);
	$numrows = $count->fetchColumn();

	if ($numrows) {
		$query = $select . $from . $where;
		$courses = $db->query($query);

		$symbol = '&#10003;';

		$subject = '';
		$section = '';
		$weeks = '';
		$delivery = '';
		$location = '';
		$avlCheck = '';
		$StartDate ='';

		//Initialize variables
		$traditional = ''; //1 - 0
		$workBasedLearning = '';  // 2 - 1
		$hybrid = '';  // 3 - 2
		$online = '';  // 4 - 3
		$webSupported = '';  // 5 - 4
		$day = '';  // 6 - 5
		$night = '';  // 7 - 6
		$weekend = '';  // 8 - 7
		$fullSemester = '';  // 9 - 8
		$eightweekone = '';  // 10 - 9
		$eightweektwo = '';  // 11 - 10
		$other = '';  // 12 - 11
		$asheville = '';  // 13 - 12
		$enka = '';  // 14 - 13
		$madision = '';  // 15 - 14
		$south = '';  // 16 - 15
		$woodfin = ''; // 17 - 16
		$gwtc = ''; // 18 - 17
		$offsite = '';  // 19 -18

		//Assign variables to columns
		$columns = array(
			$traditional,
			$workBasedLearning,
			$hybrid,
			$online,
			$webSupported,
			$day,
			$night,
			$weekend,
			$fullSemester,
			$eightweekone,
			$eightweektwo,
			$other,
			$asheville,
			$enka,
			$madision,
			$south,
			$woodfin,
			$gwtc,
			$offsite
		);

		if ($numrows < 1) {
			return '';
		}

		else {
			$last = '';
			$rowcount = $numrows;

			while($row = $courses->fetch()) {
				//Assign rows to column specific variables
				$ID = $row['ID'];
				$subject = $row['subject'];
				$delivery = $row['delivery'];
				$weeks = $row['weeks'];
				$section = $row['section'];
				$StartDate = $row['StartDate'];
				$location = $row['location'];

				//Find delivery letter code to meet column criteria
				switch($delivery) {
					case 'TR' :
						$columns[0] = $symbol;
						break;
					case 'CP' :
						$columns[1] = $symbol;
						break;
					case 'HY' :
						$columns[2] = $symbol;
						break;
					case 'IN' :
						$columns[3] = $symbol;
						break;
					case 'WB' :
						$columns[4] = $symbol;
						break;
				}
				//Find subject letter code to meet column criteria
				if ($subject == 'WBL') {
					$columns[1] = $symbol;
				}

				//Find datetime letter code to meet column criteria
				$arr_section = str_split($section);
				foreach ($arr_section as $value) {
					switch($value) {
					case 'D' :
						$columns[5] = $symbol;
						break;
					case 'N' :
						$columns[6] = $symbol;
						break;
					case 'S' :
						$columns[7] = $symbol;
						break;
					}
				}

				//Full Semester - 16/10 week classes
				if(($weeks == '16') || ($weeks == '10')) {  $columns[8] = $symbol; }

				//Spring 8 week Minimesters
				if($weeks == '8' && $StartDate[0] == '1') { $columns[9] = $symbol; }
				if($weeks == '8' && $StartDate[0] == '3') { $columns[10] = $symbol; }

				//Summer Minimeters
				if($weeks == '8' && $StartDate[0] == '5') { $columns[9] = $symbol; }
				if($weeks == '8' && $StartDate[0] == '6') { $columns[10] = $symbol; }

				//Fall 8 week Minimesters
				if($weeks == '8' && $StartDate[0] == '8') { $columns[9] = $symbol; }
				if($weeks == '8' && substr($StartDate, 0, 2) == '10') { $columns[10] = $symbol; }

				//Other column 12/5/4/3/2 week classes
				else { $columns[11] = $symbol; }

				//Find location letter code to meet column criteria
				$arr_location = str_split($location);
				foreach ($arr_location as $value) {
					switch($value) {
					case 'A' :
						$columns[12] = $symbol;
						break;
					case 'E' :
						$columns[13] = $symbol;
						break;
					case 'M' :
						$columns[14] = $symbol;
						break;
					case 'S' :
						$columns[15] = $symbol;
						break;
					case 'W' :
						$columns[16] = $symbol;
						break;
					case 'GWTC' :
						$columns[17] = $symbol;
						break;
					case 'O' :
						$columns[18] = $symbol;
						break;
					}
				}

			}
			return $columns;
		}//else
	}//numrows

	else {
		exit('<p>Error retrieving schedule from database!<br />'.
		'Error: ' . mysql_error() . '</p>');
	}
}//populate_course_columns


//---------------------------------------------------------------------------------
// get heading titles
//---------------------------------------------------------------------------------
function getHeading($prefix) {
	global $db;

	if (!$prefix) {// A prefix is selected
		$where = " WHERE 1=1";
	}
	else {
		$where = " WHERE subject_prefix_key.prefix LIKE '%$prefix%'";
	}

	$count = $db->query('SELECT COUNT(*) FROM subject_prefix_key' . $where);
	$numrows = $count->fetchColumn();
	
	if ($numrows) {
		$query = "SELECT subject FROM subject_prefix_key" . $where;
		$heading = $db->query($query);

		if ($numrows < 1) {
			return ('No headings found for '. "$prefix");
		}
		else {
			while ($row = $heading->fetch()) {
				return $row['subject'];
			}
			$heading->free();
		}
	}

	else {
		exit('<p>Error retrieving headings from database!<br />'.
			'Error: ' . mysql_error() . '</p>');
	}
}//getHeading


//---------------------------------------------------------------------------------
// get course description detail
//---------------------------------------------------------------------------------
function getCourseDetails($which, $subject, $course) {
	global $db;
	$count = $db->query("SELECT COUNT(*) FROM course_descriptions WHERE 1=1 AND course_prefix='$subject' AND course_number='$course'");
	$numrows = $count->fetchColumn();
	$query = "SELECT * FROM course_descriptions WHERE 1=1 AND course_prefix='$subject' AND course_number='$course'";
	$requisite = $db->query($query);

	if (!$requisite) {
		exit('<p>Error retrieving requisites from database!<br />'.
		'Error: ' . mysql_error() . '</p>');
		}
	else {
		if ($numrows < 1) {
			return ("See Advisor");
		}
		else {
			while ($row = $requisite->fetch()) {
				if($which == "contact_hours") {
					$class_hours = $row['class_hours'];
					$lab_hours = $row['lab_hours'];
					$clinic_coop_shop = $row['clinic_coop_shop'];
					$contact_hours = $class_hours + $lab_hours + $clinic_coop_shop;
					return $contact_hours;
				}
				else {
					return $row["$which"];
				}
			}
		}
	}
}//getCourseDetails

?>
