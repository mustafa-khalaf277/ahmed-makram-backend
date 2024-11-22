<?php
include("./inc/connect.php");
include("./inc/functions.php");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
  /*var*/
  $page = 1;
  if ((int)$_GET["page"]) $page = (int)$_GET["page"];
  $pageLimit = 10;
  $offset = (int)($pageLimit * ($page - 1));

  $totalExamsQuery = $database->prepare("SELECT count(exam_results.id) as
  total,users.img from exam_results,users WHERE
user_id=:user_id AND exam_results.is_done = 1 AND users.id=:user_id");
  $totalExamsQuery->execute([":user_id" => $user_id]);
  $data = $totalExamsQuery->fetch(PDO::FETCH_ASSOC);
  $total = $data["total"];
  $img = $data["img"];
  if ($total == 0) {
    print_r(json_encode(Array(
      "status" => "success",
      "data" => Array(),
      "total" => 0
    )));
    die();
  }

  $resultsQuery = $database->prepare("
     SELECT
    exam_results.exam_id AS id,
    exam_results.exam_marks,
    exam_results.created_at,
    exam_results.Corrected,
    exam_results.total_questions,
    exam_results.correct_answers,
    exams.title,
    exams.marks,
    COUNT(user_questions_answers.is_completed) as completed,
    SUM(user_questions_answers.is_success ) as success
FROM
    exam_results,
    exams,
    user_questions_answers
WHERE
    exam_results.user_id = :user_id AND exams.id = exam_results.exam_id AND exam_results.is_done = 1 AND
    user_questions_answers.user_id = exam_results.user_id AND
    user_questions_answers.is_completed = 1 AND
    user_questions_answers.exam_id = exam_results.exam_id
    GROUP By exam_results.id DESC
  ");
  $resultsQuery->bindparam(":user_id",
    $user_id);
  $resultsQuery->execute();
  $results = $resultsQuery->fetchAll(PDO::FETCH_ASSOC);

  print_r(json_encode(Array(
    "status" => "success",
    "img" => $img,
    "total" => $total,
    "data" => $results,
  )));
  die();

} else {
  http_response_code(405);

}