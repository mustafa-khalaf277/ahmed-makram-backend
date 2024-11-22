<?php
include("../inc/connect.php");
include("../inc/functions.php");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
  if ((int)$_GET["exam_id"] <= 0 || (int)$_GET["user_id"] <= 0) HandleError("", 404);
  $exam_id = (int)$_GET["exam_id"];
  $user_id = (int)$_GET["user_id"];

  $examDataQuery = $database->prepare("
  SELECT
  exams.id
  From
   exams,
   exam_results,
   courses
  WHERE
   exam_results.exam_id = :exam_id AND
   exams.id = :exam_id AND
   exam_results.is_done = 1 AND
   courses.id = exams.course_id 
 ");
  $examDataQuery->bindParam(":exam_id", $exam_id);
  $examDataQuery->execute();
  $examdata = $examDataQuery->fetch(PDO::FETCH_ASSOC);
  if ($examDataQuery->rowCount() <= 0)HandleError("", 404);

  $examdata = Array();
  $questionQuery = $database->prepare("
 SELECT
    questions.title,
    questions.type,
    questions.a,
    questions.b,
    questions.c,
    questions.d,
    questions.img,
    questions.choose_answer,
    user_questions_answers.is_success,
    user_questions_answers.details,
    user_questions_answers.question_mark,
    user_questions_answers.is_corrected,
    questions.question_mark AS marks,
    COALESCE(text_answers.text, '') AS TEXT
FROM
    user_questions_answers
INNER JOIN questions ON questions.id = user_questions_answers.question_id
LEFT JOIN text_answers ON text_answers.question_id = questions.id
WHERE
    user_questions_answers.user_id = :user_id AND user_questions_answers.exam_id
    = :exam_id
ORDER BY
    questions.id");
  $questionQuery->bindParam(":user_id", $user_id);
  $questionQuery->bindParam(":exam_id", $exam_id);
  $questionQuery->execute();

  $questions = $questionQuery->fetchAll(PDO::FETCH_ASSOC);
  $examdata["questions"] = $questions;
  print_r(json_encode(
    Array(
      "status" => "success",
      "data" => $examdata
    )
  ));
  die();

} else {
  http_response_code(405);

}