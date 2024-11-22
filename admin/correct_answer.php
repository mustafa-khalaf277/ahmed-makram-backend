<?php
include "../inc/connect.php";
include "../inc/functions.php";
if ($_SERVER["REQUEST_METHOD"] == "GET") {
  /* handle check admin */
  if (
    !(int) $_GET["id"] ||
    !(float) $_GET["mark"] ||
    (int) $_GET["id"] <= 0 ||
    (float) $_GET["mark"] <= 0
  ) {
    HandleError("", 500);
  }
  $id = (int) $_GET["id"];
  $mark = (float) $_GET["mark"];
  $is_success = $mark == 0 ? 0 : 1;

  $dataQuery = $database->prepare("SELECT
  user_questions_answers.exam_id,user_id
  , questions.question_mark FROM  questions,user_questions_answers WHERE
  questions.id = user_questions_answers.question_id AND
  user_questions_answers.id = :id AND user_questions_answers.is_corrected=0");
  $dataQuery->bindParam(":id", $id);
  $dataQuery->execute();
  if ($dataQuery->rowCount() <= 0)HandleError("", 500);
  $data = $dataQuery->fetch(PDO::FETCH_ASSOC);

  if ($mark < 0 || $mark > $data["question_mark"]) {
    HandleError("", 500);
  }
  $database->prepare(
    "UPDATE user_questions_answers SET is_corrected =1 , is_success
    = $is_success , question_mark = $mark WHERE id = $id"
  )->execute();
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

  ")->execute([":user_id" => $data["user_id"], ":exam_id" => $data["exam_id"]]);

}else {
  http_response_code(405);

}