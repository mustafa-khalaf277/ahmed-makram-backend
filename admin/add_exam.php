<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  /* handle check admin */
  if (!(int)$_POST["course_id"] || (int)$_POST["course_id"] <=
    0 ||!$_POST["title"] || !$_POST["description"] ||
    (int)$_POST["count_of_suggestions"] < 0) HandleError("", 500);


  $course_id = (int)$_POST["course_id"];
  $count_of_suggestions = (int)$_POST["count_of_suggestions"];
  $title = htmlspecialchars($_POST["title"]);
  $description = htmlspecialchars($_POST["description"]);
  $relation = (int)$_POST["relation"]?(int)$_POST["relation"]:null;
  $created_at = date("Y-m-j");
  $addExamQuery = $database->prepare("INSERT INTO
  exams(course_id,title,description,relation_exam_id,count_of_suggestions,created_at)
  VALUES(:course_id,:title,:description,:relation_exam_id,:count_of_suggestions,:created_at)");
  $addExamQuery->bindParam(":course_id",
    $course_id);
  $addExamQuery->bindParam(":title",
    $title);
  $addExamQuery->bindParam(":description",
    $description);
  $addExamQuery->bindParam(":relation_exam_id",
    $relation);
  $addExamQuery->bindParam(":count_of_suggestions",
    $count_of_suggestions);
  $addExamQuery->bindParam(":created_at",
    $created_at);
  try {

    $addExamQuery->execute();
    if ($addExamQuery->rowCount() <= 0) HandleError("", 500);
  }catch(Exception $e) {
    HandleError("", 500);
  }









}else {
  http_response_code(405);

}