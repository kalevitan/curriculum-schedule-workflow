<?php
//---------------------------------------------------------------------------------
// query available subjects and list the Abbreviation(add subject name later)
//---------------------------------------------------------------------------------
function list_available_subjects($schedule) {
$subjectAbv = @mysql_query('SELECT DISTINCT subject FROM '.$schedule.' ORDER BY subject');
	$general_subject = ''; //initialize variable general subject
	if (!$subjectAbv)	{
		exit('<p>Error performing subjects query: ' . mysql_error() . '</p>');
	}
	$results = "<form action='index.php?general_subject2=$general_subject' class=\"form\"><div class=\"row\"><div class='form-group col-sm-6'><select name='general_subject2' class=\"form-control\">";
	while ($gensub = mysql_fetch_array($subjectAbv)) {
	  $general_subject = $gensub['subject']; //populate variable general subject
	  $results .= "<option value='$general_subject'>$general_subject - " . getHeading($general_subject) . " </option>";
	}
	$results .= "</select></div><div class='form-group col-sm-6'><button type=\"submit\" class=\"btn btn-default col-sm-3\">Show Subject</button></div></div></form>";
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


	//the schedule query
	$courses = mysql_query($select . $from . $where . $order . $limit) or die(mysql_error()); //for error checking
	//@mysql_query($select . $from . $where . $order . $limit);
	if (!$courses) {
	  exit('<p>Error retrieving schedule from database!<br />'.
	      'Error: ' . mysql_error() . '</p>');
	}
	if (mysql_num_rows($courses) < 1) {
		return '';
	}
	else {
		return $courses;
	}

}



//---------------------------------------------------------------------------------
//convert query data to an array and add xhtml tags to it
//---------------------------------------------------------------------------------
/*
function query_to_xhtml_two($courses, $schedule)
{

	$results = '';
	//$results = '<table frame="none">';
	$classID = '';
	$subject = '';
	$row = '';
	$lastSubject = '';
	$lastSubjectNumber = '';
	$prerequisite = '';
	$corequisite = '';
	$description = '';
	$credit_hours = '';
	$contact_hours = '';
	$i='';
	$rows_count = '';

	$last = ''; //initializing variable last, setting a blank value
	$rows_count = 0;
	while($row = mysql_fetch_assoc($courses))  {
		$classID = $row['ID'];
		$desc = "desc" . $classID;
		$subject = $row['subject'];
		$subjectNumber = $row['course'];
		if($last != '' && $lastSubject.$lastSubjectNumber != $subject.$subjectNumber) {
			$results .= '</table>'."\n";
		}
		if($lastSubject != $subject) {
			$results .=	'<table1 aid:table="table" aid:trows="1" aid:tcols="1" aid:ccolwidth="520">
			<Cell aid:table="cell" aid:ccolwidth="520"><heading aid:pstyle="heading">' . getHeading($subject) . '</heading></Cell>
						  </table1>';
		}
		if($lastSubject.$lastSubjectNumber != $subject.$subjectNumber) {
			$i = 1;
			$results=str_replace('rows_count'.$lastSubject.$lastSubjectNumber, $rows_count, $results);
			$rows_count=0;
			$title = getCourseDetails("title", $subject, $subjectNumber);
			$title = ereg_replace('&', '&amp;', $title);
			$prerequisite = getCourseDetails("prerequisite", $subject, $subjectNumber);
			$corequisite = getCourseDetails("corequisite", $subject, $subjectNumber);
			$description = getCourseDetails("description", $subject, $subjectNumber);
			$credit_hours = getCourseDetails("credit_hours", $subject, $subjectNumber);
			$contact_hours = getCourseDetails("contact_hours", $subject, $subjectNumber);

			$results .= '<table2 aid:table="table" aid:trows="2" aid:tcols="2" aid:ccolwidth="520pt">

            <Cell aid:table="cell" aid:theader="" aid:crows="1" aid:ccols="1" aid:ccolwidth="350"><title aid:pstyle="title">' .$subject." ".$subjectNumber." ". $title . '</title></Cell>' .'
            <Cell aid:table="cell" aid:theader="" aid:crows="1" aid:ccols="1" aid:ccolwidth="170"><creditHours aid:pstyle="credit">Credit hours: '.$credit_hours.' </creditHours> : <contactHours aid:pstyle="contact">Contact hours: '.$contact_hours.'</contactHours></Cell>'."\n".'
            <Cell aid:table="cell" aid:theader="" aid:crows="1" aid:ccols="1" aid:ccolwidth="260"><prerequisite aid:pstyle="prerequisite">Prerequisite: '. $prerequisite .'</prerequisite></Cell>'."\n".'
            <Cell aid:table="cell" aid:theader="" aid:crows="1" aid:ccols="1" aid:ccolwidth="260"><corequisite aid:pstyle="corequisite">Corequisite: '. $corequisite .'</corequisite></Cell></table2>'."\n";
			$results .= '  <table aid:table="table" aid:trows="rows_count'.$subject.$subjectNumber.'" aid:tcols="7" aid:ccolwidth="520pt">';
//						   <Cell aid:table="cell" aid:cstyle="note" aid:ccolwidth="13"><numeral>#</numeral></Cell>
//						   <Cell aid:table="cell" aid:cstyle="emphasis" aid:ccolwidth="91"><delivery>Delivery</delivery></Cell>
//						   <Cell aid:table="cell" aid:cstyle="emphasis" aid:ccolwidth="117"><section>Course Section</section></Cell>
//						   <Cell aid:table="cell" aid:cstyle="emphasis" aid:ccolwidth="72"><faculty>Faculty</faculty></Cell>
//						   <Cell aid:table="cell" aid:cstyle="emphasis" aid:ccolwidth="80"><location>Location</location></Cell>
//						   <Cell aid:table="cell" aid:cstyle="emphasis" aid:ccolwidth="39"><days>Days</days></Cell>
//						   <Cell aid:table="cell" aid:cstyle="emphasis" aid:ccolwidth="84"><times>Times</times></Cell>
//						   <Cell aid:table="cell" aid:cstyle="emphasis" aid:ccolwidth="34"><weeks>Weeks</weeks></Cell>';
		}
		$rows_count++;
//		$results .= "\n".'<Cell aid:table="cell">'.$i++. '</Cell>' ."\n". select_course_rows($classID, $schedule) . "\n";
		$results .= "\n". select_course_rows($classID, $schedule) . "\n";
		$last=$row;
		$lastSubject=$subject;
		$lastSubjectNumber=$subjectNumber;
	}
	//print the ending table tag
	$results .= '</table>';
	return $results;
}
*/

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


	while($row = mysql_fetch_assoc($courses))  {
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

	//the schedule query
	$courses = @mysql_query($select . $from . $where);
	if (!$courses) {
		 exit('<p>Error retrieving schedule from database!<br />'.
		'Error: ' . mysql_error() . '</p>');
	}

	$symbol = '&#10003;';

	$num_rows = mysql_num_rows($courses);
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

	if ($num_rows < 1) {
		return '';
	}

	else {
		$last = '';
		$rowcount = mysql_num_rows($courses);

		while($row = mysql_fetch_array($courses)) {
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
}//populate_course_columns


//---------------------------------------------------------------------------------
// query rows based on ID
//---------------------------------------------------------------------------------
function select_course_rows($ID, $schedule) {
	$select = "SELECT ID, delivery, subject, course, section, faculty, building, room, days, begin, end, StartDate, EndDate, CoReqSecName";
	$from   = " FROM $schedule";
	$where  = " WHERE ID='$ID'";

	//the schedule query
	$courses = @mysql_query($select . $from . $where);
	if (!$courses) {
	  exit('<p>Error retrieving schedule from database!<br />'.
	      'Error: ' . mysql_error() . '</p>');
	}

	$num_rows = mysql_num_rows($courses);
	$section = '';
	$faculty = '';
	$location = '';
	$method = '';
	$start_date = '';
	$end_date = '';
	$dates = '';
	$days = '';
	$times = '';
	$weeks = '';
	$i = '';

	if ($num_rows < 1) {
		return '';
	}
	else {
		$last = '';
		$rowcount = mysql_num_rows($courses);
		$i = 1;
		while($row = mysql_fetch_array($courses))  {
				$subject = $row['subject'];
				$course_number = $row['course'];
					if($course_number{0} > 3){
					$course_number = "0" . $course_number;
					}

				$ID = $row['0'];
				$desc = "desc" . $ID;
				$delivery = parseSection($subject, $row['delivery'], $row['section']);
				$ind_class_key = $subject . " " . $row['course'] . " " . $row['section'];
				$faculty .= checkFaculty($row['faculty']) . "\n";
				$location .= $row['building'] . " " . $row['room'] . "\n";
				$dates .= $row['StartDate'] . "-" . $row['EndDate'] . "\n";
				$days .= $row['days'] . "\n";
				$times .= $row['begin'] . "-" . $row['end'] . "\n";
				if($row['CoReqSecName'] != '') {
					$section = $ind_class_key . " This course must be taken with: " .$row['CoReqSecName'];
				}
				else {
					$section = $ind_class_key;
				}

				$results = '<Cell aid:table="cell" aid:ccolwidth="78"><delivery>'. $delivery . '</delivery></Cell>
	                        <Cell aid:table="cell" aid:ccolwidth="117"><section>'. $section .'</section></Cell>
		                    <Cell aid:table="cell" aid:ccolwidth="72"><faculty>'. rtrim($faculty) .'</faculty></Cell>
	                        <Cell aid:table="cell" aid:ccolwidth="86"><location>'. rtrim($location) .'</location></Cell>
	                        <Cell aid:table="cell" aid:ccolwidth="43"><dates>'. rtrim($dates) .'</dates></Cell>
		                    <Cell aid:table="cell" aid:ccolwidth="39"><days>'. rtrim($days) .'</days></Cell>
	                        <Cell aid:table="cell" aid:ccolwidth="84"><times>'. rtrim($times) .'</times></Cell>';
	//			$results = array($delivery, $section, $faculty, $location, $method, $days, $times, $weeks);
		}//while
		return $results;
	}//else
}//select_course_rows


//---------------------------------------------------------------------------------
// check if faculty field is blank, replace with Staff Value
//---------------------------------------------------------------------------------
function checkFaculty($arg) {
	if (empty($arg)) {
		$faculty = "Staff";
	}
	else {
		$faculty = $arg;
	}
	return $faculty;
	mysql_free_result($faculty);
}//checkFaculty


//----------------------------------------------
// clean zeroes
//----------------------------------------------
function removeZeroes($data) {
	$data=ereg_replace('0', '', $data);
	return $data;
}//removeZeroes


//---------------------------------------------------------------------------------
// get heading titles
//---------------------------------------------------------------------------------
function getHeading($prefix) {
	$select = "SELECT subject FROM subject_prefix_key";

	if (!$prefix) {// A prefix is selected
		$where = " WHERE 1=1";
	}
	else {
		$where = " WHERE subject_prefix_key.prefix LIKE '%$prefix%'";
	}

	$heading = @mysql_query($select . $where);

	if (!$heading) {
		exit('<p>Error retrieving headings from database!<br />'.
		'Error: ' . mysql_error() . '</p>');
	}
	if (mysql_num_rows($heading) < 1)	{
		return ('No headings found for '. "$prefix");
	}
	else {
		while ($row = mysql_fetch_assoc($heading)) {
			return $row['subject'];
		}
		mysql_free_result($result);
	}
}//getHeading


//---------------------------------------------------------------------------------
// Turn Section into Array and Parse section codes into section terms
//---------------------------------------------------------------------------------
function parseSection($subject, $delivery, $section, $StartDate) { //start function
    $section_code = '';
	if ($delivery == 'TR') { $section_code = 'Traditional ';
		//return $section_code;
	}
	if ($delivery == 'CP' || $subject == 'WBL') { $section_code = 'Work Based Learning ';
		//return $section_code;
	}
	if ($delivery == 'HY') { $section_code = 'Hybrid ';
		//return $section_code;
	}
	if ($delivery == 'WB') { $section_code = 'Classroom ';
		//return $section_code;
	}
	if ($delivery == 'IN') { $section_code = 'Online ';
		//return $section_code;
	}
	else {
		$section_code = '';
	}

//    $arr_section = str_split($section);
//
//			switch($arr_section) { //case statment to turn section codes into words
//				case 'D' :
//					$section_code .= 'Day<br />';
//					break;
//				case 'N' :
//					$section_code .= 'Night<br />';
//					break;
//				case 'B' :
//					$section_code .= 'Learning Community';
//					break;
//				case 'E' :
//					$section_code .= 'Enka Site ';
//					break;
//				case 'G' :
//					$section_code .= 'Reserved ';
//					break;
//				case 'W' :
//					$section_code .= 'Web Assisted ';
//					break;
//				case 'O' :
//					$section_code .= 'Online ';
//					break;
//				case 'Y' :
//					$section_code .= 'Hybrid ';
//					break;
//				case 'L' :
//					$section_code .= 'Late Start ';
//					break;
//				case 'M' :
//					$section_code .= 'Minimester';
//					break;
//				case 'R' :
//					$section_code .= 'Madison Site ';
//					break;
//				case '+' :
//					$section_code .= 'Honors ';
//					break;
//				case 'X' :
//					$section_code .= 'Mall ';
//					break;
//				case 'B' :
//					$section_code .= 'Interdisciplinary Studies ';
//					break;
//				default :
//					$section_code .= '';
//					break;
//			} //end switch

		return $section_code;
}//parseSection


//---------------------------------------------------------------------------------
// get course description detail
//---------------------------------------------------------------------------------
function getCourseDetails($which, $subject, $course) {
	$select = "SELECT * FROM course_descriptions WHERE 1=1 AND course_prefix='$subject' AND course_number='$course'";
	$requisite = @mysql_query($select);

	if (!$requisite) {
		exit('<p>Error retrieving requisites from database!<br />'.
		'Error: ' . mysql_error() . '</p>');
	}

	if (mysql_num_rows($requisite) < 1) {
		return ("See Advisor");
	}
	else {
		while ($row = mysql_fetch_assoc($requisite)) {
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
		mysql_free_result($result);
	}
}//getCourseDetails

?>
