<?php
include("./inc/connect.php");
include("./inc/functions.php");


// functions
function Update_question_answer_data($is_success, $details, $question_id,
  $user_id, $is_corrected, $question_mark) {
  global $database;
  $updateCurrentQuestion = $database->prepare("
              UPDATE
                user_questions_answers
            SET
                is_success = :is_success,
                details = :details,
                is_completed = 1,
                is_corrected = :is_corrected,
                question_mark = :question_mark
            WHERE
                question_id = :question_id AND user_id = :user_id
  ");
  $updateCurrentQuestion->bindParam(":is_success", $is_success, PDO::PARAM_INT);

  $updateCurrentQuestion->bindParam(":details", json_encode($details));
  $updateCurrentQuestion->bindParam(":question_id", $question_id, PDO::PARAM_INT);
  $updateCurrentQuestion->bindParam(":user_id", $user_id, PDO::PARAM_INT);
  $updateCurrentQuestion->bindParam(":is_corrected", $is_corrected, PDO::PARAM_INT);
  $updateCurrentQuestion->bindParam(":question_mark", $question_mark, PDO::PARAM_INT);
  $updateCurrentQuestion->execute();
  if ($updateCurrentQuestion->rowCount() <= 0) {
    HandleError("حدث خطاء داخلى", 200);

  }
}






$dataResponse = Array(
  "question_success" => 0,
);


/*check method*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (!(int)$_POST["exam_id"] || !(int)$_POST["question_id"]) {
    HandleError("", 205);
  }

  /*var*/

  $exam_id = (int)$_POST["exam_id"];
  $question_id = (int)$_POST["question_id"];
  $answer = $_POST["answer"] || $_POST["answer"] != null?htmlspecialchars($_POST["answer"]):null;
  $time = time();
  /*check if exam is finished*/
  Handle_check_exam_is_done($user_id, $exam_id);


  /*get question data*/
  $question_data_query = $database->prepare("
            SELECT
              questions.*,
              user_questions_answers.id,
              user_questions_answers.question_id,
              user_questions_answers.expirt_at,
              user_questions_answers.exam_id
          FROM
              questions,
              user_questions_answers
          WHERE
              questions.exam_id = :exam_id AND user_questions_answers.user_id = :user_id AND questions.id = user_questions_answers.question_id AND user_questions_answers.is_completed = 0
          LIMIT 1
    ");
  $question_data_query->bindParam(":exam_id", $exam_id);
  $question_data_query->bindParam(":user_id", $user_id);
  $question_data_query->execute();
  $question_data = $question_data_query->fetch(PDO::FETCH_ASSOC);
  /*
  التحقق من ان الطالب ادخل  اجابه متوافقه
  */
  if ($question_data["type"] == "choose") {
    if (!in_array($answer,
      Array("a", "b", "c", "d", "null")))
      HandleError("الاجابه غير صالحه", 200);

  }

  /*التحقق من  ان السؤال الذى سيجيب عليه الطالب هو السؤال الذى يجب عليه ان يجيبه*/
  if ($question_data["id"] != $question_id) {
    HandleError("", 204);
  }

  /*جعل الاسئاله التى انتهى وقتها مكتمله*/
  Change_expirt_question($user_id, $exam_id);
  /*اذا كان وقت السؤال الحالى منتهى  اعاده الاجابه الخاصه بالسؤال
*/

  if ($question_data["expirt_at"] <= $time) {
    // اذ كان سؤال اختيارى ارجاع الاجابه الصحيحه
    if ($question_data["type"] == "choose") {
      $dataResponse["answer"] = $question_data["choose_answer"];
    }
    // جعل السؤال الحالى مكتمل
    $database->prepare("
              UPDATE
              user_questions_answers
          SET
              is_completed = 1,
              is_corrected = 1
          WHERE
              user_id = :user_id AND id = :id
  ")->execute([":user_id" => $user_id, ":id" => $question_data["id"]]);
    print_r(json_encode($dataResponse));
    die();
  }
  /*
التحقق من الاجابه
*/
  $details = Array(
    "student_answer" => $answer,
  );
  /*handle choose answer*/
  if ($question_data["type"] == "choose") {
    if ($answer == $question_data["choose_answer"]) {
      $dataResponse["question_success"] = true;
      $dataResponse["question_mark"] = $question_data["question_mark"];
    } else {
      $dataResponse["answer"] = $question_data["choose_answer"];
      $dataResponse["answer_details"] = $question_data["answer_details"];
      $dataResponse["question_mark"] = 0;
    }
    Update_question_answer_data($dataResponse["question_success"], $details,
      $question_data["question_id"], $user_id, 1, $dataResponse["question_mark"]);
  }

  /*handle text answer*/
  if ($question_data["type"] == "text") {
    if ($answer) {

      Update_question_answer_data(0, $details, $question_data["question_id"],
        $user_id, 0, 0);
    } else {
      Update_question_answer_data(0, $details, $question_data["question_id"],
        $user_id, 1, 0);

    }
    print_r(json_encode($dataResponse));
    die();
  }




  print_r(json_encode($dataResponse));



} else {
  http_response_code(405);

}