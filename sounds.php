<?php
include("./inc/connect.php");
include("./inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "GET") {
  $qsoundsQuery = $database->prepare("SELECT url,type,id FROM sounds");
  $qsoundsQuery->execute();
  print_r(json_encode($qsoundsQuery->fetchAll(PDO::FETCH_ASSOC)));
}else {
  http_response_code(405);

}