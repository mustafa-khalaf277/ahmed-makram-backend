<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  /* handle check admin */
  if (!(int)$_POST["question_id"] || (int)$_POST["question_id"] <=
    0) HandleError("", 500);
  $question_id = (int)$_POST["question_id"];
  $questionQuery = $database->prepare("SELECT * FROM questions WHERE id =:id LIMIT 1");
  $questionQuery->bindParam(":id", $question_id);
  try {
    $questionQuery->execute();
    if ($questionQuery <= 0)HandleError("", 500);
    $question_data = $questionQuery->fetch(PDO::FETCH_ASSOC);
    $question_data["title"] = $_POST["title"]?htmlspecialchars($_POST["title"])
    :$question_data["title"];
    $question_data["expirt_time"] = (int)["expirt_time"] &&
    (int)["expirt_time"] <= 0?(int)$_POST["expirt_time"] *60:$question_data["expirt_time"];
    $question_data["suggest"] = $_POST["suggest"]?htmlspecialchars($_POST["suggest"])
    :$question_data["suggest"];
    $question_data["answer_details"] = $_POST["answer_details"]?htmlspecialchars($_POST["answer_details"])
    :$question_data["answer_details"];
    if ($question_data["type"] == "choose") {
      $question_data["a"] = $_POST["a"]?htmlspecialchars($_POST["a"]) :$question_data["a"];
      $question_data["b"] = $_POST["b"]?htmlspecialchars($_POST["b"]) :$question_data["b"];
      $question_data["c"] = $_POST["c"]?htmlspecialchars($_POST["c"]) :$question_data["c"];
      $question_data["d"] = $_POST["d"]?htmlspecialchars($_POST["d"]) :$question_data["d"];
    } else if ($question_data["type"] == "text" && $question_data["text_answers"]) {
      $database->prepare("UPDATE FROM text_answers SET text = :text WHERE
      question_id =:id LIMIT 1")->execute(["id" => $question_id, "text" => htmlspecialchars($question_data["text_answers"])]);
    }
    /*handle change img*/
    if ($_FILES["image_file"] && $_FILES["image_file"]["name"] || $_POST["img_url"]) {
      if ($_POST["img_url"] != $question_data["img"] && $question_data["via"] == "upload") unlink('../'.explode("/api", $question_data["img"])[1]);
    }



    if ($_FILES["image_file"] && $_FILES["image_file"]["name"]) {
      /*upload image*/
      $target_dir = "../inc/static/img/";
      $imageFileType = strtolower(pathinfo($_FILES["image_file"]["name"], PATHINFO_EXTENSION));
      $allowedExtensions = array('jpg', 'jpeg', 'png');
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mimeType = finfo_file($finfo, $_FILES['image_file']['tmp_name']);
      finfo_close($finfo);
      if (!in_array($imageFileType, $allowedExtensions) ||
        !in_array($mimeType, array("image/jpeg", "image/png", "image/jpg"))) {
        HandleError("", 500);
      }

      /* ulpoad file*/

      $file_name = rand(10000000, 99999999)."_".time()."_".rand(1000, 9999).".".$imageFileType;
      $target_file = $target_dir.$file_name;

      if (!move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
        HandleError("", 500);
      }
      $question_data["via"] = "upload";
      $question_data["img"] = $api_file_url."/inc/static/img/$file_name";
    } else if ($_POST["img_url"] || $_POST["img_url"] != $question_data["img"]) {
      $question_data["via"] = "url";

      $question_data["img"] = htmlspecialchars($_POST["img_url"]);
    }




    /*handle change answer*/

    if ($_POST["choose_answer"] &&
      in_array($_POST["choose_answer"], array("a", "b", "c", "d")) &&
      $_POST["choose_answer"] != $question_data["choose_answer"]) {
      /*handle change answer*/

      $database->prepare("UPDATE user_questions_answers SET   is_success
      =0,question_mark =0
      WHERE  JSON_EXTRACT(details, '$.student_answer') != :answer AND question_id =
      :question_id")->execute([":answer" => $_POST["choose_answer"],
        "question_id" => $question_id]);



      $database->prepare("UPDATE user_questions_answers SET   is_success
      =1,question_mark = :question_mark
      WHERE  JSON_EXTRACT(details, '$.student_answer') = :answer AND question_id =
      :question_id")->execute([":answer" => $_POST["choose_answer"],
        "question_id" => $question_id, ":question_mark" =>
        $question_data["question_mark"]]);

      /*
      اعاده تصحيح الامتحان لجميع الذين اتموه
      */
      $database->prepare("
  UPDATE
    user_questions_answers,
    exam_results
SET
    exam_results.exam_marks = (
        SELECT
            SUM(question_mark)
        FROM
            user_questions_answers
        WHERE
            user_id = user_questions_answers.user_id
            AND exam_id = user_questions_answers.exam_id
    ),
    exam_results.correct_answers = (
        SELECT
            SUM(is_corrected)
        FROM
            user_questions_answers
        WHERE
            exam_id = user_questions_answers.exam_id
            AND user_id = user_questions_answers.user_id
    )
WHERE
    exam_results.exam_id = user_questions_answers.exam_id
  AND exam_results.user_id = user_questions_answers.user_id
    AND is_completed = 1 AND user_questions_answers.question_id =
    :question_id")->execute([":question_id" => $question_id]);


      $question_data["choose_answer"] = $_POST["choose_answer"];

    }
    $question_data["question_mark"] = (int)["question_mark"] &&
    (int)["question_mark"] <= 0?(int)$_POST["question_mark"]:$question_data["question_mark"];

    /*upadte question data*/

    $updateQuestionQuery = $database->prepare("
                    UPDATE questions SET
                    choose_answer= :choose_answer,
                    a= :a,
                    b= :b,
                    c= :c,
                    d= :d,
                    title= :title,
                    question_mark= :question_mark,
                    expirt_time= :expirt_time,
                    suggest= :suggest,
                    answer_details= :answer_details,
                    via= :via,
                    img=:img
                    WHERE
                    id=:question_id
  ");
    $updateQuestionQuery->bindParam(":choose_answer", $question_data["choose_answer"]);
    $updateQuestionQuery->bindParam(":a", $question_data["a"]);
    $updateQuestionQuery->bindParam(":b", $question_data["b"]);
    $updateQuestionQuery->bindParam(":c", $question_data["c"]);
    $updateQuestionQuery->bindParam(":d", $question_data["d"]);
    $updateQuestionQuery->bindParam(":title", $question_data["title"]);
    $updateQuestionQuery->bindParam(":question_mark", $question_data["question_mark"]);
    $updateQuestionQuery->bindParam(":expirt_time", $question_data["expirt_time"]);
    $updateQuestionQuery->bindParam(":suggest", $question_data["suggest"]);
    $updateQuestionQuery->bindParam(":answer_details", $question_data["answer_details"]);
    $updateQuestionQuery->bindParam(":via", $question_data["via"]);
    $updateQuestionQuery->bindParam(":img", $question_data["img"]);
    $updateQuestionQuery->bindParam(":question_id", $question_id);

    $updateQuestionQuery->execute();
    // if ($updateQuestionQuery->rowCount() <= 0)HandleError("", 500);
    print_r(json_encode(array("status" => "success")));
  }catch(Exception $e) {
    HandleError("", 500);

  }


}else {
  http_response_code(405);

}