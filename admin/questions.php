<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /* handle check admin */
  $page = 1;
  if ((int)$_GET["page"]) $page = (int)$_GET["page"];
  if (!(int)$_GET["exam_id"] || (int)$_GET["exam_id"] <= 0) HandleError("", 500);
  $examId = (int)$_GET["exam_id"];
  $pageLimit = 15;
  $offset = (int)($pageLimit * ($page - 1));


  $totalQuery = $database->prepare("SELECT COUNT(*) as total FROM questions WHERE exam_id = :exam_id ");

  $totalQuery->execute([":exam_id" => $examId]);

  $questions = $database->prepare("SELECT
  id,
  type,
  title
  FROM
  questions WHERE exam_id = :exam_id
   LIMIT $offset,$pageLimit
   ");
  $questions->execute([":exam_id" => $examId]);
  print_r(json_encode(Array(
    "total" => $totalQuery->fetch(PDO::FETCH_ASSOC)["total"],
    "questions" => $questions->fetchAll(PDO::FETCH_ASSOC)
  )));


}else {
  http_response_code(405);

}