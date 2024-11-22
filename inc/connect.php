<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: *");
date_default_timezone_set('Africa/Cairo');
/*database connect*/
$username = "root";
$password = "";

try {

  $database = new
  PDO("mysql:host=localhost; dbname=makram;charset=utf8;", $username, $password);
}catch(Exception $e) {
  http_response_code(500);
  die(json_encode(Array(
    "status" => "error"
  )));
}
$api_file_url = "http://localhost:8080/ahmed-makram/api/";
$user_id = 3;