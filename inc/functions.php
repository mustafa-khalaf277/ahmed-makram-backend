<?php
include("./connect.php");


/*check user paid the course*/
function course_paid($user_id, $course_id) {
  global $database;
  $userPayCourse = $database->prepare("SELECT id from subscribers WHERE user_id
  = :user_id AND course_id = :course_id");
  $userPayCourse->bindParam(":user_id", $user_id, PDO::PARAM_INT);
  $userPayCourse->bindParam(":course_id", $course_id, PDO::PARAM_INT);
  $userPayCourse->execute();
  $data = $userPayCourse->fetch();
 // if ($data["id"] <= 0) {
//    HandleError("انت لست مشترك فى هذا الكورس", 204);
//  }

}

function HandleError($text, $statue_code = 200) {
  print_r(json_encode(Array("status" => "error", "message" => $text)));
  http_response_code($statue_code);
  die();
}
function Handle_check_exam_is_done($user_id, $id) {
  global $database;
  $check_user_completed_exam = $database->prepare("SELECT is_done from exam_results
  WHERE user_id =:user_id AND exam_id = :exam_id ");
  $check_user_completed_exam->bindParam(":user_id", $user_id, PDO::PARAM_INT);
  $check_user_completed_exam->bindParam(":exam_id", $id, PDO::PARAM_INT);
  $check_user_completed_exam->execute();
  if ($check_user_completed_exam->rowCount() >= 0 && $check_user_completed_exam->fetch()["is_done"] == 1) {
    HandleError("لقد اتممت هذا الاختبار من قبل", 201);
  } else {
    return(
      $check_user_completed_exam
    );
  }
}

function Handle_exam_done($user_id, $exam_id) {
  global $database;
  $database->prepare("
                UPDATE
                    exam_results
                SET
                    exam_results.is_done = 1,
                    exam_results.exam_marks =(
                    SELECT
                        SUM(question_mark)
                    FROM
                        user_questions_answers
                    WHERE
                        user_id = :user_id AND exam_id = :exam_id
                ),
                exam_results.correct_answers =(
                    SELECT
                        SUM(is_corrected)
                    FROM
                        user_questions_answers
                    WHERE
                        user_id = :user_id AND exam_id = :exam_id
                ),
                exam_results.Corrected = IF(
                        (
                        SELECT
                            COUNT(
                                user_questions_answers.is_corrected
                            )
                        FROM
                            user_questions_answers
                        WHERE
                            user_questions_answers.exam_id = :exam_id AND user_questions_answers.user_id = :user_id AND user_questions_answers.is_corrected = 1
                    ) <(
                    SELECT
                        exams.questions_count
                    FROM
                        exams
                    WHERE
                        exams.id = :exam_id
                    LIMIT 1
                ),
                0,
                1
                    )
                WHERE
                    user_id = :user_id AND exam_id = :exam_id

  ")->execute([":user_id" => $user_id, ":exam_id" => $exam_id]);
  return (json_encode(Array(
    "status" => "success",
    "data" => Array(),
    "exam_done" => true,
  )));

}


function Change_expirt_question($user_id, $exam_id) {
  global $database;
  $questionsExpirt = $database->prepare("UPDATE user_questions_answers SET
  is_completed = 1 WHERE expirt_at <= :time AND expirt_at !=
  0 AND user_id =:user_id AND exam_id=:exam_id")->execute([":time" => time(), ":user_id" =>
    $user_id, "exam_id" => $exam_id]);
}




function Check_the_last_relation_exam_is_done($user_id, $relation_exam_id) {
  global $database;
  $exam = $database->prepare("SELECT is_done from exam_results WHERE
  user_id=:user_id
  AND exam_id=:exam_id LIMIT 1");
  $exam->bindParam(":user_id", $user_id);
  $exam->bindParam(":exam_id", $relation_exam_id);
  $exam->execute();
  if (!$exam->fetch(PDO::FETCH_ASSOC)["is_done"]) {

    HandleError("يجب عليك اجتياز الامتحان السابق لدخول الاختبار", 200);
  }
}