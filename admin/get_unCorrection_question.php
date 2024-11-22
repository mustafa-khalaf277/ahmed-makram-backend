<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /* handle check admin */
  if (!(int)$_GET["exam_id"] || (int)$_GET["exam_id"] <=
    0) HandleError("", 500);
  $exam_id = (int)$_GET["exam_id"];
  $questionQuery = $database->prepare("
SELECT
    questions.question_mark,
    questions.title,
    questions.img,
    JSON_EXTRACT(user_questions_answers.details,'$.student_answer') as
    answer,
    users.name,
    user_questions_answers.id
FROM
    questions
INNER JOIN user_questions_answers ON questions.id = user_questions_answers.question_id
INNER JOIN users ON users.id = user_questions_answers.user_id
WHERE
    user_questions_answers.exam_id = :exam_id
    AND user_questions_answers.is_corrected = 0
    AND user_questions_answers.is_completed = 1
GROUP BY
    user_questions_answers.id
 ");
  $questionQuery->bindParam(":exam_id", $exam_id);
  $questionQuery->execute();

  $totalQuery = $database->prepare("SELECT COUNT(*) as total from user_questions_answers
WHERE   user_questions_answers.exam_id = :exam_id
    AND user_questions_answers.is_corrected = 0
    AND user_questions_answers.is_completed = 1 ");
  $totalQuery->bindParam(":exam_id", $exam_id);
  $totalQuery->execute();

  print_r(json_encode(array("total" =>
    $totalQuery->fetch(PDO::FETCH_ASSOC)["total"],
    "data" => $questionQuery->fetch(PDO::FETCH_ASSOC))));
}else {
  http_response_code(405);

}