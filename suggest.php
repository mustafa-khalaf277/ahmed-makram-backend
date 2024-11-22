<?php
include("./inc/connect.php");
include("./inc/functions.php");

if ($_SERVER['REQUEST_METHOD'] == "GET") {
  if (!(int)$_GET["question_id"]) {
    HandleError("", 205);
  }
  $question_id = (int)$_GET["question_id"];



  $dataQuery = $database->prepare("
       SELECT
          questions.exam_id,
          questions.suggest,
          questions.id,
          exam_results.remaining_suggestions
      FROM
          questions,
          user_questions_answers,
          exam_results
      WHERE
          questions.id = user_questions_answers.question_id AND
          user_questions_answers.user_id = :user_id AND
          user_questions_answers.question_id = :question_id AND exam_results.user_id =
          :user_id AND exam_results.exam_id =
          user_questions_answers.exam_id AND
          exam_results.is_done =0
    ");
  $dataQuery->bindParam(":question_id", $question_id);
  $dataQuery->bindParam(":user_id", $user_id);
  $dataQuery->execute();

  if ($dataQuery->rowCount() <= 0) HandleError("لقد حدث خطاء داخلى رجاء المحاوله لاحقا", 200);

  $data = $dataQuery->fetch(PDO::FETCH_ASSOC);

  if ($data["remaining_suggestions"] <= 0) {
    print_r(json_encode(Array(
      "status" => "error",
      "message" => "لم يعد لديك مساعدات لهذا الامتحان"
    )));
    die();
  }
  if (is_null($data["suggest"])) {
    print_r(json_encode(Array(
      "status" => "success",
      "suggest" => null
    )));
    die();
  } else {

    $updateUserSuggestCount = $database->prepare("
              UPDATE
                  exam_results
              SET
                  remaining_suggestions = remaining_suggestions -1
              WHERE
                  user_id = :user_id AND exam_id = :exam_id
    ");
    $updateUserSuggestCount->bindParam(":user_id", $user_id);
    $updateUserSuggestCount->bindParam(":exam_id", $data["exam_id"]);
    $updateUserSuggestCount->execute();
    if ($updateUserSuggestCount->rowCount() <= 0) {
      HandleError("هناك خطا داخلى", 200);
    }

  }
  /*
التحقق من ان المستخدم باقى له اقتراحات
*/


  print_r(json_encode(Array(
    "status" => "success",
    "suggest" => $data["suggest"],
    "suggestionsCount" => $data["remaining_suggestions"] -1
  )));
  die();
} else {
  http_response_code(405);
}