<?php
include("./inc/connect.php");
include("./inc/functions.php");

$dataResponse = Array(
  "question_data" => Array(),
  "exam_done" => false

);

if ($_SERVER["REQUEST_METHOD"] === "GET") {
  if (!(int)$_GET["exam_id"]) {
    HandleError("", 205);
  }
  /*var*/
  $exam_id = (int)$_GET["exam_id"];
  $time = time();
  Change_expirt_question($user_id, $exam_id);

  $nextQuestionQuery = $database->prepare("SELECT
            user_questions_answers.id,
            user_questions_answers.expirt_at,
            questions.title,
            questions.type,
            questions.a,
            questions.b,
            questions.c,
            questions.d,
            questions.img,
            questions.expirt_time,
            user_questions_answers.question_id,
            user_questions_answers.is_save
        FROM
            user_questions_answers,
            questions
        WHERE
            user_questions_answers.user_id = :user_id AND
            user_questions_answers.exam_id = :exam_id AND
            user_questions_answers.is_completed = 0 AND questions.id =
            user_questions_answers.question_id
           LIMIT 1
    ");

  $nextQuestionQuery->execute(["user_id" => $user_id, "exam_id" =>
    $exam_id]);
  $nextQuestion = $nextQuestionQuery->fetch(PDO::FETCH_ASSOC);
  // اذا لم يكن هناك اسئله اذا الامتحان انتهى
  if (!$nextQuestion["id"]) {
    $dataResponse["exam_done"] = true;
    Handle_exam_done($user_id, $exam_id);
    print_r(json_encode($dataResponse));
    http_response_code(201);
    die();
  }
  if ($nextQuestion["expirt_at"] === 0) {

    // بدء وقت السؤال الحالى
    $nextQuestion["expirt_at"] = $nextQuestion["expirt_time"] + $time + 10;
    $changeQuestionExpirtTime = $database->prepare("
                UPDATE
                user_questions_answers
            SET
                expirt_at = :expirt_at
            WHERE
                user_id = :user_id AND question_id = :question_id
            LIMIT 1
      ");
    $changeQuestionExpirtTime->bindParam(":expirt_at", $nextQuestion["expirt_at"]);
    $changeQuestionExpirtTime->bindParam(":user_id", $user_id);
    $changeQuestionExpirtTime->bindParam(":question_id",
      $nextQuestion["question_id"]);
    $changeQuestionExpirtTime->execute();
   
    if ($changeQuestionExpirtTime->rowCount() <= 0) HandleError("لقد حدث خطاء داخلى رجاء المحاوله لاحقا", 200);
  }

  print_r(json_encode($nextQuestion));
  die();


} else {
  http_response_code(405);

}