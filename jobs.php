<?php
require_once 'config.php';
require_once "functions.php";

promptLogin();

$g_link = mysql_connect('localhost', $g_username, $g_password); //TODO use a persistant database connections

mysql_select_db('stt', $g_link);

$query = "SELECT name, id FROM students WHERE active=1";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result)) {
	$studentarray[$row['id']]=$row['name'];
}	

if(isset($_GET["Jobid"]) && isset($_SESSION['loginid'])){
	$StudentID = $_SESSION['loginid'];
	$numjobs = 0;
	$query = "SELECT COUNT(*) as count FROM jobs WHERE STATUS<4 && claimedby='".$StudentID."'";
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
	
	if(isset($_GET['Unclaim']) && $_GET['Unclaim']){
		$query = "UPDATE  `jobs` SET  `claimedby` =0 WHERE id =".$_GET["Jobid"];
		$result = mysql_query($query);
		if($result) echo "<font color=white>You have UNclaimed a job successfully.</font><BR>";
		else echo "<font color=white>ERROR: Something went wrong you have not UNclaimed a job.</font><BR>";
	}
	else if($row['count']>2){ // If you already have a job you are working on
		echo"<font color=white>You can't claim this job, you are already working on ".$row['count']." jobs.</font>";
	}
	else { // You don't have a job you are working on
		$query = "UPDATE  `jobs` SET  `claimedby` =".$StudentID." WHERE id =".$_GET["Jobid"];
		$result = mysql_query($query);
		if($result) echo "<font color=white>You have claimed a job successfully.</font><BR>";
		else echo "<font color=white>ERROR: Something went wrong you have not claimed a job.</font><BR>";
	}
}

function printjobs($result, $claimable) {
  while ($row = mysql_fetch_assoc($result)) { // TODO format to look better
    if($row['priority']>7)
	echo "<tr bgcolor='red'>";
    else if($row['priority']>3)
	echo "<tr bgcolor='orange'>";
    else
	echo "<tr>";
    echo "<td>".$row['sname']."</td><td>".$row['description']."</td><td>".$row['points']."</td><td>".$row['category']."</td><td>";
    if($row['name'] != 'Steavie'){
        if($showall){
		echo $row['name']."</td></tr>";
	}
	else{
		echo 
		"<button type='button' id='button' onclick='claimjobfunction(".$row['id'].", 1)'>
		UNclaim Job
		</button>
		<button type='button' id='button' onclick='resolvejobfunction(".$row['id'].", 1)'>
		Resolve Job
		</button>
		</td></tr>";
	}
    }
    else if($row['repeatable']){
	echo "all</td></tr>";
    }
    else if($claimable){
	echo 
		"<button type='button' id='button' onclick='claimjobfunction(".$row['id'].", 0)'>
		Claim Job
	</button>
	</td></tr>";
    }
  } // End While
}
	

$script = "
		<script>

	function claimjobfunction(jobid, unclaim) {
		document.Theform.Jobid.value=jobid
		document.Theform.Unclaim.value=unclaim
		document.getElementById('Theform').submit();
	}	
	</script>
	
	<style>
	
	.meat td{
		color:white;
	}
	
	.meat a{
	color:white;
	}
	
	</style>";
makeHeader("Job List","Job List",2,"jobs.php",$script);
?>
	<body>
		<form name="Theform" id="Theform">
			<input type="hidden" name="Jobid">
			<input type="hidden" name="Unclaim">
		</form>
<?php

$showall=false;

$g_link = mysql_connect('localhost', $g_username, $g_password); //TODO use a persistant database connections
mysql_select_db('stt', $g_link);

	$toggle="";
	if(!$showall) $toggle=" (a.claimedby=0 OR a.claimedby=".$_SESSION['loginid'].") AND ";

	$query = "SELECT a.name as sname, a.description, b.category, a.points, c.name, a.priority, a.repeatable, a.id
	FROM jobs a, skillcategories b, students c
	WHERE $toggle status<4 AND a.skillcatid=b.id AND a.status=1 AND (a.claimedby=c.id OR (a.claimedby=0 AND c.id=9))";
if ($_GET['sortby']=='category') {///order by category 
	$query = $query ." ORDER BY category";
}
else if ($_GET['sortby']=='points') {
	$query= $query ."order by points";
}	
else {  /// order by default 
	$query = $query . " ORDER BY priority DESC, points";
}	
	
if ($_GET['order']=='DESC'){ 
	$query= $query ." DESC";		 
}

	

	
	
	
	
$query2 = "SELECT owner as sname, problem as description, 'Computer Hardware' as category, point_value as points, b.name, 1 as priority, 0 as repeatable, a.id 
FROM devices a, students b 
WHERE status_id < 6 AND (a.assignedto_id = b.id OR (a.assignedto_id=0 AND b.id=9))
ORDER BY status_id";
	


$result = mysql_query($query);

if (!$result) {
    die('Invalid query: ' . mysql_error());
}

// prints one row at a time, the results from the database.
echo "<table border=1>";
if ($_GET['order']=='ASC') {
echo "<tr><td>Job</td><td>Description</td><td>
<a  href='jobs.php?sortby=points&order=DESC'>Points</a> v</td><td>
<a href='jobs.php?sortby=category'>Category</a></td><td>Claimed By</td></tr>";
}
		
else if ($_GET['order']=='DESC') {
echo "<tr><td>Job</td><td>Description</td><td>
<a  href='jobs.php?sortby=points&order=ASC'>Points</a> ^</td><td>
<a href='jobs.php?sortby=category'>Category</a></td><td>Claimed By</td></tr>";
}	
		else {
			echo "<tr><td>Job</td><td>Description</td><td>
<a  href='jobs.php?sortby=points&order=ASC'>Points</a></td><td>
<a href='jobs.php?sortby=category'>Category</a></td><td>Claimed By</td></tr>";
}	
		
		
///printjobs($result2, false);

///echo "<tr><td>Job</td><td>Description</td><td>Points</td><td>Category</td><td>Claimed By</td></tr>";

printjobs($result, true);
	
echo "</table>";


mysql_close($g_link);

?>

		<?php
makefooter("&#169; Copyright Cherokee Washington Highschool <a href='index.php'> Home Page<a/><a href='' onclick='initIt()'>About us</a><a href='create_jobs.php'>Create Job</a> <style>#footer a{color:black; margin-left:3px;}#footer p{color:black; text-decoration:underlined;}</style>",0,"true");
?>
