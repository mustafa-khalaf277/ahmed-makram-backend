<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /*handle check admin*/
  $examsQuery = $database->prepare("SELECT id,title FROM courses");
  try {

    $examsQuery->execute();
    print_r(json_encode($examsQuery->fetchAll(PDO::FETCH_ASSOC)));
  }catch(Exception $e) {
    HandleError("", 500);
  }
}else {
  http_response_code(405);

}