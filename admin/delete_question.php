<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /* handle check admin */
  if (!(int)$_GET["question_id"] || (int)$_GET["question_id"] <=
    0) HandleError("", 500);

  $id = (int)$_GET["question_id"];

  $questionQuery = $database->prepare("SELECT
  question_mark,type,img,via,exam_id
FROM questions WHERE id=:id");
  $questionQuery->execute(["id" => $id]);
  if ($questionQuery->rowCount() <= 0)HandleError("", 500);
  $question = $questionQuery->fetch(PDO::FETCH_ASSOC);

  /*unlink img */
  if ($question["via"] == "upload") unlink('../'.explode("/api", $question["img"])[1]);


  /*
    update results
  */





  $database->prepare("UPDATE exam_results,exams,user_questions_answers SET
  exam_results.total_questions= exam_results.total_questions -1
  WHERE  exam_results.user_id = user_questions_answers.user_id AND
  exam_results.exam_id = user_questions_answers.exam_id AND
  user_questions_answers.question_id = :id")->execute(["id" => $id]);

  $database->prepare("
  UPDATE
    exam_results,
    exams,
    user_questions_answers
SET
    exam_results.correct_answers = exam_results.correct_answers -1,
    exam_results.exam_marks = exam_results.exam_marks - :question_mark
WHERE
    exam_results.user_id = user_questions_answers.user_id AND exam_results.exam_id = user_questions_answers.exam_id AND user_questions_answers.question_id = :id AND user_questions_answers.is_success = 1
  ")->execute(["id" =>
    $id, ":question_mark" => $question["question_mark"]]);


  /*
  delete queestion answers
 */



  $database->prepare("DELETE FROM user_questions_answers WHERE question_id=:id")->execute(["id" => $id]);

  /*update exam*/

  $database->prepare("UPDATE exams SET marks= marks - :question_mark
  ,questions_count = questions_count -1 WHERE id=:exam_id")->execute([":exam_id" =>
    $question["exam_id"], ":question_mark" => $question["question_mark"]]);

  /*delete text_answer and question*/
  if ($question["type"] == "text") {
    $database->prepare("DELETE FROM text_answers WHERE question_id=:id")->execute(["id" =>
      $id]);
  }
  $database->prepare("DELETE FROM questions WHERE id=:id")->execute(["id" =>
    $id]);


  /*
تعديل حاله التصحيح الى مصحح اذا كانت باقى الاسئله مصححه فى الاسئله المسلمه
*/
  if ($question["type"] === "text") {


    $database->prepare("
                UPDATE
                    exam_results
                SET
                exam_results.Corrected = IF(
                        (
                        SELECT
                            COUNT(
                                user_questions_answers.is_corrected
                            )
                        FROM
                            user_questions_answers
                        WHERE
                            user_questions_answers.exam_id = :exam_id AND user_questions_answers.is_corrected = 1
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
                 exam_id = :exam_id

  ")->execute([":exam_id" => $question["exam_id"]]);

  }


}else {
  http_response_code(405);

}