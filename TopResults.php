<?php
include("./inc/connect.php");
include("./inc/functions.php");

if ($_SERVER["REQUEST_METHOD"] === "GET") {

  $examsQuery = $database->prepare("
    SELECT
      *
    From
      exams
    WHERE
      show_top_students = 1
 ");
  $examsQuery->execute();
  $data = $examsQuery->fetchAll(PDO::FETCH_ASSOC);
  print_r(json_encode(
    Array(
      "status" => "success",
      "data" => $data
    )
  ));
  die();

} else {
  http_response_code(405);

}