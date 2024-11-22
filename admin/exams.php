<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /* handle check admin */
  $page = 1;
  if ((int)$_GET["page"]) $page = (int)$_GET["page"];
  $pageLimit = 10;
  $offset = (int)($pageLimit * ($page - 1));
  $totalQuery = $database->prepare("SELECT COUNT(*) as total FROM exams ");

  $totalQuery->execute();

  $examsQuery = $database->prepare("SELECT
  exams.id,
  exams.title as exam_title,
  exams.questions_count ,
  courses.title,
  exams.count_of_suggestions
  FROM
  exams,courses WHERE courses.id = exams.course_id
   ORDER BY exams.id DESC
   LIMIT $offset,$pageLimit
   ");
  $examsQuery->execute();
  print_r(json_encode(Array(
    "total" => $totalQuery->fetch(PDO::FETCH_ASSOC)["total"],
    "exams" => $examsQuery->fetchAll(PDO::FETCH_ASSOC)
  )));



}else {
  http_response_code(405);

}