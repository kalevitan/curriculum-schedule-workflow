<?php
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

include 'includes/db.inc.php';
include 'includes/xml.fnc.inc.php';

if($_GET) {
  $search_terms=$_GET;
  if(isset($_GET['general_subject'])){
     $general_subject = $_GET['general_subject'];
  } else {
	  $general_subject = '';
  }
  if(isset($_GET['cnum'])){
     $cnum = $_GET['cnum'];
  } else {
	  $cnum = '';
  }
  if(isset($_GET['general_subject2'])){
     $general_subject2 = $_GET['general_subject2'];
  } else {
	  $general_subject2 = '';
  }
  if(isset($_GET['title'])){
     $title = $_GET['title'];
    } else {
	  $title = '';
  }
  if(isset($_GET['delivery'])){
     $delivery = $_GET['delivery'];
  } else {
     $delivery = '';
  }
  if(isset($_GET['location'])){
     $location = $_GET['location'];
  } else {
     $location = '';
  }
  if(isset($_GET['campus'])){
     $campus = $_GET['campus'];
  } else {
	  $campus = '';
  }
  if(isset($_GET['building'])){
     $building = $_GET['building'];
  } else {
	  $building = '';
  }
  if(isset($_GET['weeks'])){
     $weeks = $_GET['weeks'];
  } else {
     $weeks = '';
  }
  if(isset($_GET['days'])){
     $days = $_GET['days'];
  } else {
     $days = '';
  }
  if(isset($_GET['ID'])){
     $ID = $_GET['ID'];
  } else {
  	$ID = '';
  }
  if(isset($_GET['ofSubject'])){
     $ofSubject = $_GET['ofSubject'];
  } else {
     $ofSubject = '';
  }
} else {
	 $general_subject = '';
	 $cnum = '';
	 $general_subject2 = '';
	 $title = '';
   $delivery = '';
   $location = '';
	 $campus = '';
	 $building = '';
   $weeks = '';
   $ID = '';
   $ofSubject = '';
	 $days = '';
}
$database = 'curschedule';
$table_names = array('fall2015cu');
$page_title = 'Fall 2015 Curriculum Schedule';


$conn = db_connect($database);
if (!$conn) {
	echo "no database connection >> ";
	} else {
	//line used to identify what table the script is connecting to. It is commented out in a live production
	//echo "database connection returned >> ";
	//blank line used in a live production model
	echo "";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php print($page_title); ?></title>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
</head>

<body>

  <div class="container">

		<h1><?php print($page_title); ?></h1>

		<h3>Find a class by its Subject Abbreviation and Number or its Title</h3>
		<div id="search_box">
			<form action="index.php" method="get" class="form-horizontal">
        <div class="form-group">
					<label for="general_subject2" class="col-sm-2 control-label">Subject Abbreviation</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" size="5" name="general_subject2" placeholder="ACA"/>
         </div>
        </div>
        <div class="form-group">
					<label for="cnum" class="col-sm-2 control-label">Course Number</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" size="5" name="cnum" placeholder="115"/>
          </div>
        </div>
        <div class="form-group">
					<label for="title" class="col-sm-2 control-label">Course Title</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" size="55" name="title" placeholder="Success and Study Skills"/>
          </div>
				</div>
      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
      		<button type="submit" class="btn btn-default">Search</button>
          <button type="reset" class="btn btn-default">Reset</button>
        </div>
      </div>
  		</form>
		</div>

  	<h3>Or browse by subject</h3>
  	<div id="search_box">
  	  <?php //Subject drop down
        foreach ($table_names as $value) {
          $courses = listCoursesTwo($general_subject, $general_subject2, $cnum, $title, $delivery, $location, $campus, $building, $weeks, $value, $days);
          if (!$courses) {
            echo '<h3 class="float_left">No courses could be found. Try entering fewer search items for broader results.</h3>';
            echo '<div class="float_right" style="float:right; padding: 10px 10px 0px 10px;">
            <form action="index.php" method="get">
            <button type="submit" class="btn btn-default">Return</button>
            </form>
            </div>';
          }
          else {
            $subjects = list_available_subjects($value, $general_subject, $general_subject2);
            print($subjects);
          }
        }
      ?>
    </div>

  <h3>Or browse through delivery methods</h3>
    <div id="search_box">
      <a href="<?php $_SERVER['PHP_SELF'] ?>?">All</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?campus=D">Day</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?campus=N">Night</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?campus=S">Weekend</a>
      <br>
      <a href="<?php $_SERVER['PHP_SELF'] ?>?location=M">Madison</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?location=E">Enka</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?location=S">A-B Tech South</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?location=W">Woodfin</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?location=GWTC">Goodwill Training Center</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?location=O">Off Site</a>
      <br>
      <a href="<?php $_SERVER['PHP_SELF'] ?>?delivery=TR">Traditional</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?delivery=HY">Hybrid</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?delivery=IN">Online</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?weeks=8&weeks=5">Minimesters</a> | <a href="<?php $_SERVER['PHP_SELF'] ?>?weeks=15&weeks=14&weeks=13">Late Start</a>
    </div>

</body>
</html>

<?php
//---------------------------------------------------------------------------------
// Write the results to file
//---------------------------------------------------------------------------------
$file_name = $table_names[0].".xml";
$path = "./xml_output/" . $file_name;

$file_top = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><'.$table_names[0].' xmlns:aid="http://ns.adobe.com/AdobeInDesign/4.0/">';
$file_bottom = "</".$table_names[0].">";

$fp = fopen($path, 'w');
	fwrite($fp, $file_top);
	fclose($fp);


	$XHTMLcourses = query_to_XHTML_three($courses, $value); //call xml generation function
	echo '<textarea rows="75" cols="175">';
	print $XHTMLcourses;
	echo "</textarea>";

  $XHTMLcourses = str_replace('&nbsp;', '&#160;', $XHTMLcourses);

$fp = fopen($path, 'a');
	fwrite($fp, $XHTMLcourses);
	fwrite($fp, $file_bottom);
	fclose($fp);

// and close the database connection
unset($value);
mysql_close($conn);

?>
