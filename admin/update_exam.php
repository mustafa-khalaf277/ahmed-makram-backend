<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  /* handle check admin */

  if (!(int)$_POST["exam_id"] || (int)$_POST["exam_id"] <=
    0) HandleError("f", 500);
  $exam_id = (int)$_POST["exam_id"];
  $examQuery = $database->prepare("SELECT * FROM exams WHERE id =:id LIMIT 1");
  $examQuery->bindParam(":id", $exam_id);
  try {
  $examQuery->execute();
  $examData = $examQuery->fetch(PDO::FETCH_ASSOC);
  $_POST["title"] && htmlspecialchars($_POST["title"])
  ?$examData["title"] = htmlspecialchars($_POST["title"]):'';

  $_POST["description"] && htmlspecialchars($_POST["description"])
  ?$examData["description"] = htmlspecialchars($_POST["description"]):'';
  $_POST["count_of_suggestions"] && (int)($_POST["count_of_suggestions"])
  ?$examData["count_of_suggestions"] = (int)($_POST["count_of_suggestions"]):'';
  $_POST["relation"] && (int)($_POST["relation"])
  ?$examData["relation_exam_id"] = (int)($_POST["relation"]):null;


  $updateData = $database->prepare("UPDATE exams SET
    title = :title, description= :description, count_of_suggestions =
    :count_of_suggestions ,relation_exam_id=:relation_exam_id WHERE id=:id
    LIMIT 1 ");
  $updateData->bindParam(":id", $exam_id);
  $updateData->bindParam(":title", $examData["title"]);
  $updateData->bindParam(":description", $examData["description"]);
  $updateData->bindParam(":count_of_suggestions", $examData["count_of_suggestions"]);
  $updateData->bindParam(":relation_exam_id", $examData["relation_exam_id"]);
  $updateData->execute();
   }catch(Exception $e) {
  HandleError("", 500);
  
}


}else {
  http_response_code(405);

}