<!DOCTYPE html>
<html>
<meta charset="utf-8">
<title>Police Emergency Service System</title>
<link href="header_style.css" rel="stylesheet" type="text/css">
<link href="content_stile.css" rel="stylesheet" type="text/css">
</head>
<body>
<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
<table class="ContentStile">
<tr>
<td colspan="2">Incident Detail</td>
</tr>
<tr>
<td>Caller's Name: </td>
<td><?php echo $_POST["callerName"]; ?>
<input type="hidden" name="callerName" id="callerName" value="<?php echo $_POST["callerName"]; ?>">
</td>
</tr>
<tr>
<td>Contact No: </td>
<td><?php echo $_POST["callerNo"]; ?>
<input type="hidden" name="callerNo" id="callerNo" value="<?php echo $_POST["callerNo"]; ?>">
</td>
</tr>
<tr>
<td>Location: </td>
<td><?php echo $_POST["location"] ?>
<input type="hidden" name="location" id="location" value="<?php echo $_POST["location"]; ?>">
</td>
</tr>
<tr>
<td>Incident Type: </td>
<td><?php echo $_POST["incidentType"] ?>
<input type="hidden" name="incidentType" id="incidentType" value="<?php echo $_POST["incidentType"]; ?>">
</td>
</tr>
<tr>
<td>Description: </td>
<td><textarea name="incidentDesc" col="45"
rows="5" readonly id="incidentDesc">
<?php echo $_POST["incidentDesc"];?></textarea>
<input name="incidentDesc" type="hidden"
id="incidentDesc" value="<?php echo $_POST['incidentDesc'] ?>"></td>
</tr>
</table>
<?php
require_once 'db.php';

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
	die("Connection failed: " .  $conn->connect_error);
}

$sql = "SELECT patrolcar_id, patrolcar_status_desc FROM patrolcar 
JOIN
patrolcar_status
ON patrolcar.patrolcar_status_id=patrolcar_status.patrolcar_status_id
WHERE patrolcar.patrolcar_status_id='2' OR patrolcar.patrolcar_status_id='3'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()){
	$patrolcarArray[$row['patrolcar_id']] = $row['patrolcar_status_desc'];
}
}
$conn->close();
?>

<table class="ContentStile">
<tr>
<td colspan="3">Dispatch Patrolcar Panel</td>
</tr>
<?php
foreach($patrolcarArray as $key=>$value){
	?>
	<tr>
	<td><input type="checkbox" name="chkPatrolcar[]" value="<?php echo $key?>"></td>
	<td><?php echo $key;?></td>
	<td><?php echo $value;?></td>
	</tr>
<?php
 }
 ?>
<tr>
<td><input type="reset" name="btnCancel" id="btnCancel" value="Reset"></td>
<td colspan="2"><input type="submit" name="btnDispatch" id="btnDispatch" value="Dispatch"></td>
</td>
</tr>
</table>
<?php
if (isset($_POST["btnDispatch"]))
{
	require_once 'db.php';
	
	$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	
	if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$patrolcarDispatched = $_POST["chkPatrolcar"];

$numOfPatrolcarDispatched = count($patrolcarDispatched);

if ($numOfPatrolcarDispatched > 0) {
	$incidentStatus='2';
} else {
	$incidentStatus='1';
}
$sql = "INSERT INTO incident (caller_name, phone_number, incident_type_id, incident_location, incident_desc, incident_status_id) VALUES('".$_POST['callerName']."', '".$_POST['contactNo']."', '".$_POST['incidentType']."', '".$_POST['location']."', '".$_POST['incidentDesc']."', $incidentStatus)";
if ($conn->query($sql)==FALSE) {
	echo "Error: " . $sql . "<br>" . $conn->error;
}

$incidentID=mysqli_insert_id($conn);;

for($i=0; $i < $numOfPatrolcarDispatched; $i++)
{
	$sql = "UPDATE patrolcar SET patrolcar_status_id='1' WHERE patrolcar_id = '".
	$patrolcarDispatched[$i]."'";
	if ($conn->query($sql)===FALSE) {
		echo "Error: " . $sql . "<br>" . $conn->error;
	}
	
	$sql = "INSERT INTO dispatch (incident_id, patrol_id, time_dispatched)
VALUES ($incidentID, '".$patrolcarDispatched[$i]."', NOW())";
if ($conn->query($sql)===FALSE) {
	echo "Error: " . $sql . "<br>" . $conn->error;
}
}
$conn->close();
?>

<script type="text/javascript">window.location="./logcall.php";</script>

<?php } ?>

<?php
if (!isset($_POST["btnProcessCall"]) && !isset($_POST["btnDispatch"]))
	header("Location: logcall.php");
?>
</form>
</body>
</html>