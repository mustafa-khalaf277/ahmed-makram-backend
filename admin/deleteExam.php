<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /* handle check admin */
  if (!(int)$_GET["exam_id"] || (int)$_GET["exam_id"] <=
    0) {
    http_response_code(500);
    die();
  }

  $id = (int)$_GET["exam_id"];
  /*
  التحقق ما ان كان هناك علاقه بين الامتحان و امتحان اخر
  */

  $relationsQuery = $database->prepare("SELECT title FROM exams WHERE
relation_exam_id=:id");
  $relationsQuery->execute([":id" => $id]);
  if ($relationsQuery->rowCount() > 0) {
    print_r(json_encode($relationsQuery->fetchAll(PDO::FETCH_ASSOC)));
    http_response_code(201);
    die();
  }


  $database->prepare(" DELETE FROM user_questions_answers WHERE exam_id =:id")->execute([":id" => $id]);
  $database->prepare(" DELETE FROM text_answers WHERE exam_id =:id")->execute([":id" => $id]);
  /*handle delete questions wih img*/
  $questionsQuery = $database->prepare("SELECT via,img FROM questions WHERE
  exam_id= :id");
  $questionsQuery->execute([":id" => $id]);
  foreach ($questionsQuery->fetchAll(PDO::FETCH_ASSOC) as $question) {
    if ($question["via"] == "upload") unlink('../'.explode("/api", $question["img"])[1]);

  }








  $database->prepare(" DELETE FROM questions WHERE exam_id =:id")->execute([":id" => $id]);
  $database->prepare(" DELETE FROM exam_results WHERE exam_id =:id")->execute([":id" => $id]);
  $database->prepare("DELETE FROM exams WHERE id =:id")->execute([":id" => $id]);



}else {
  http_response_code(405);

}