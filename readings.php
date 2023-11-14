<?php
$config = parse_ini_file("/var/www/db.ini");
$server = $config["hostname"];
$user = $config["username"];
$password = $config["password"];
$database = $config["database"];

$db = new mysqli($server, $user, $password, $database);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $request = json_decode(file_get_contents("php://input"));
  print_r($request);
  $statement = $db->prepare("INSERT INTO plant_data1 (temp, humidity, moisture) VALUES (?, ?, ?)");
  $statement->bind_param("ddd", $request->temp, $request->humidity, $request->moisture);
  $statement->execute();
  die();
}

$statement = $db->prepare("SELECT * FROM (SELECT * FROM plant_data1 ORDER BY reading_id DESC LIMIT 40)Var1 ORDER BY reading_id ASC");
$statement->execute();
$statement->bind_result($reading_id, $temp, $humidity, $moisture, $reading_time);

$temperature = array();
$humid = array();
$moist = array();
$date = array();

while($statement->fetch()) {
	$temperature[] = $temp;
	$humid[] = $humidity;
	$moist[] = $moisture;
	$date[] = $reading_time;
	
}

$data["temp"] = $temperature;
$data["humid"] = $humid;
$data["moist"] = $moist;
$data["date"] = $date;

echo json_encode($data);

?>

