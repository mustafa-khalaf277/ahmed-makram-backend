<?php
include("./inc/connect.php");
include("./inc/functions.php");


if ($_SERVER["REQUEST_METHOD"] === "GET") {
  $page = 1;
  if ((int)$_GET["page"]) $page = (int)$_GET["page"];
  $pageLimit = 15;
  $offset = (int)($pageLimit * ($page - 1));

  $totalExamsQuery = $database->prepare("SELECT count(*) as total from
  user_questions_answers WHERE
user_questions_answers.user_id=:user_id AND  user_questions_answers.is_success
=0 AND user_questions_answers.is_completed = 1 OR
user_questions_answers.user_id=:user_id AND  user_questions_answers.is_save
=1 AND user_questions_answers.is_completed = 1");
  $totalExamsQuery->execute([":user_id" => $user_id]);
  $total = $totalExamsQuery->fetch()["total"];
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
    questions.title,
    questions.type,
    questions.a,
    questions.b,
    questions.c,
    questions.d,
    questions.img,
    questions.choose_answer,
    questions.answer_details,
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
user_questions_answers.user_id=:user_id AND  user_questions_answers.is_success
=0 AND user_questions_answers.is_completed = 1 OR
user_questions_answers.user_id=:user_id AND  user_questions_answers.is_save
=1 AND user_questions_answers.is_completed = 1
ORDER BY
    questions.id DESC
       LIMIT $offset,$pageLimit
    ");
  $resultsQuery->bindParam(":user_id", $user_id);

  $resultsQuery->execute();
  $results = $resultsQuery->fetchAll(PDO::FETCH_ASSOC);
  print_r(json_encode(Array(
    "status" => "success",
    "total" => $total,
    "data" => $results,
  )));
  die();


} else {
  http_response_code(405);

}