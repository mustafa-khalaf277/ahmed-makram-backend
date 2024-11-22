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
  if ($questionQuery->rowCount() <= 0) HandleError("", 404);
  $data = $questionQuery->fetch(PDO::FETCH_ASSOC);
  if ($data["type"] == "text") {

    $text_answer_query = $database->prepare("SELECT text from text_answers WHERE
    question_id=$question_id ");
    $text_answer_query->execute();
    $data["text_answer"]=$text_answer_query->fetch(PDO::FETCH_ASSOC)["text"];
  }
  print_r(json_encode($data));
}else {
  http_response_code(405);

}