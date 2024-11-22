<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /* handle check admin */
  if (!(int)$_GET["question_id"] || (int)$_GET["question_id"] <=
    0) HandleError("", 500);
  $question_id = (int)$_GET["question_id"];
  $questionQuery = $database->prepare("SELECT * FROM questions WHERE id =:id LIMIT 1");
  $questionQuery->bindParam(":id", $question_id);
  $questionQuery->execute();
  print_r(json_encode($questionQuery->fetch(PDO::FETCH_ASSOC)));
}else {
  http_response_code(405);

}