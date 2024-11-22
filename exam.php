<?php
include("./inc/connect.php");
include("./inc/functions.php");

if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /* check data*/
  $id = (int)$_GET["id"] != 0 ?(int)$_GET["id"]:HandleError("", 404);
  /*get exam details*/
  $getExamQuery = $database->prepare("
                SELECT
                  course_id,
                  title,
                  marks,
                  questions_count,
                  relation_exam_id,
                  count_of_suggestions
              FROM
                  exams
              WHERE
                  id = :id
              LIMIT 1
  ");
  $getExamQuery->bindParam(":id", $id, PDO::PARAM_INT);
  $getExamQuery->execute();
  $exam_data = $getExamQuery->fetch(PDO::FETCH_ASSOC);
  if (!$exam_data['course_id']) {
    HandleError("", 404);
  }





  /*check if user pay the course*/
  course_paid($user_id, $exam_data['course_id']);




  /*check if user has completed exam */
  if ($exam_data["relation_exam_id"]) {
    Check_the_last_relation_exam_is_done($user_id, $exam_data["relation_exam_id"]);
  }






  $check_user_completed_exam = Handle_check_exam_is_done($user_id, $id);
  if ($check_user_completed_exam->rowCount() <= 0) {
    /*create new result*/
    $created_at = time();
    $create_result_records = $database->prepare("INSERT INTO
   exam_results (user_id, exam_id, exam_marks, is_done,
   created_at,total_questions,correct_answers,remaining_suggestions)VALUES
  (:user_id, :exam_id,0, false, :time,:total_questions,0,:count_of_suggestions)");
    $create_result_records->bindParam(":user_id", $user_id);
    $create_result_records->bindParam(":exam_id", $id);
    $create_result_records->bindParam(':time', $created_at);
    $create_result_records->bindParam(':count_of_suggestions', $exam_data["count_of_suggestions"]);
    $create_result_records->bindParam(':total_questions', $exam_data["questions_count"]);
    $create_result_records->execute();
    if ($create_result_records->rowCount() <= 0) {
      HandleError("Something went wrong.", 200);
    }
  }





  /*if create the exam now add user question answers*/
  if ($check_user_completed_exam->rowCount() <= 0) {
    $examQuestions = $database->prepare("SELECT
  id,type,a,b,c,d,title,img,question_mark,expirt_time from questions WHERE exam_id =
  :exam_id");
    $examQuestions->bindParam(":exam_id", $id);
    $examQuestions->execute();
    if ($examQuestions->rowCount() >= 0) {
      $questions = $examQuestions->fetchAll(PDO::FETCH_ASSOC);
      foreach ($questions as $question) {
        $data = Array();
          $data["textAnswer"] = "";
        $data = json_encode($data);
        $question_marks = 0;
        $inserNewQuestionAnswer = $database->prepare("INSERT INTO
    user_questions_answers(is_success,is_corrected,details, question_mark,user_id,question_id,expirt_at,is_completed,exam_id)
    VALUES(0,0,:details,:question_mark,:user_id,:question_id,0,0,:exam_id)");
        $inserNewQuestionAnswer->bindParam(":details", $data);
        $inserNewQuestionAnswer->bindParam(":question_mark", $question_marks);
        $inserNewQuestionAnswer->bindParam(":user_id", $user_id);
        $inserNewQuestionAnswer->bindParam(":question_id", $question["id"]);
        $inserNewQuestionAnswer->bindParam(":exam_id", $id);
        if (!$inserNewQuestionAnswer->execute()) {
          $database->prepare("DLETE from user_questions_answers WHEHE
     user_id=:user_id AND
     exam_id=:exam_id")->execute([":user_id" => $user_id, ":exam_id" => $id]);
          HandleError("Something went wrong.", 200);
        }
      }
    }
  }

  /////////
  Change_expirt_question($user_id, $id);
  /*get questions not completed count*/
  $questionUnCompleted = $database->prepare("SELECT
  COUNT(*) as UnCompleted from user_questions_answers
  WHERE user_id=:user_id AND
  exam_id=:exam_id AND is_completed =0");
  $questionUnCompleted->execute([":user_id" => $user_id, ":exam_id" => $id]);
  $countOfQuestionsUnCompleted = $questionUnCompleted->fetch(PDO::FETCH_ASSOC);
  /*handle exam done*/
  if ($countOfQuestionsUnCompleted["UnCompleted"] == 0) {
    die(Handle_exam_done($user_id, $id));
  }

  /*user suggestions*/
  $suggestionsQuery = $database->prepare("
              SELECT
                  remaining_suggestions
              FROM
                  exam_results
              WHERE
                   user_id = :user_id AND exam_id = :exam_id
    ");
  $suggestionsQuery->execute([":user_id" => $user_id, ":exam_id" => $id]);
  $suggestions = $suggestionsQuery->fetch(PDO::FETCH_ASSOC);
  if ($suggestions["remaining_suggestions"]) {
    $suggestions = $suggestions["remaining_suggestions"];
  } else {
    $suggestions = 0;
  }
  /* Get the last answered question if any*/
  print_r(json_encode(Array(
    "status" => "success",
    "exam_done" => false,
    "title" => $exam_data["title"],
    "marks" => $exam_data["marks"],
    "count_of_suggestions" => $suggestions,
    "questions_count" => $exam_data["questions_count"],
    "un_completed" => $countOfQuestionsUnCompleted["UnCompleted"]
  )));

} else {
  http_response_code(405);
}