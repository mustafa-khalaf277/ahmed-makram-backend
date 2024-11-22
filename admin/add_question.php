<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  /* handle check admin */


  /*data array*/
  $question = array(
    "exam_id" => null,
    "title" => null,
    "expirt_time" => 300,
    "question_mark" => null,
    "img" => null,
    "via" => null,
    "type" => null,
    "choose_answer" => null,
    "a" => null,
    "b" => null,
    "c" => null,
    "d" => null,
    "answer_details" => null,
    "text_answer" => null,
    "suggest" => null,
  );


  /*
  validations
*/

  if (!(int)$_POST["exam_id"] || (int)$_POST["exam_id"] === 0 || ! $_POST["type"] || !$_POST["title"] ||
    !(int)$_POST["expirt_time"] || (int)$_POST["expirt_time"] <= 0 ||
    !(int)$_POST["question_mark"] || (int)$_POST["question_mark"] <= 0 ) HandleError("s", 500);


  $question["title"] = htmlspecialchars($_POST["title"]);
  $question["question_mark"] = (int)$_POST["question_mark"];
  $question["exam_id"] = (int)$_POST["exam_id"];
  $question["expirt_time"] = (int)$_POST["expirt_time"] * 60;
  $_POST["suggest"]?$question["suggest"] =
  htmlspecialchars($_POST["suggest"]):null;
  /*check exam */
  $checkExamQuery = $database->prepare("SELECT id FROM exams WHERE id =
  $question[exam_id] LIMIT 1");
  $checkExamQuery->execute();
  if ($checkExamQuery->rowCount() <= 0) HandleError("", 404);



  /* handle validate choose type*/
  if ($_POST["type"] == "choose") {
    $question["type"] == "choose";
    /*handle check question asnswers*/
    if (! $_POST["a"] ||! $_POST["b"] ||! $_POST["c"] ||! $_POST["d"] ||!
      $_POST["choose_answer"] || !in_array($_POST["choose_answer"],
        array("a", "b", "c", "d"))) HandleError("", 500);
    $question["type"] = "choose";
    $question["a"] = htmlspecialchars($_POST["a"]);
    $question["b"] = htmlspecialchars($_POST["b"]);
    $question["c"] = htmlspecialchars($_POST["c"]);
    $question["d"] = htmlspecialchars($_POST["d"]);
    $question["answer_details"] = htmlspecialchars($_POST["answer_details"]);
    $question["choose_answer"] = htmlspecialchars($_POST["choose_answer"]);





  } else if ($_POST["type"] == "text") {
    if (! $_POST["text_answer"]) HandleError("", 500);
    $question["type"] = "text";
    $question["text_answer"] = htmlspecialchars($_POST["text_answer"]);
  } else HandleError("", 500);


  /* handle img */

  if ($_POST["img_url"] && filter_var($_POST["img_url"], FILTER_VALIDATE_URL)) {
    $question["img"] = htmlspecialchars($_POST["img_url"]);
    $question["via"] = "url";

  } else if ($_FILES["image_file"] && $_FILES["image_file"]["name"]) {
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
    $question["via"] = "upload";
    $question["img"] = $api_file_url."/inc/static/img/$file_name";


  }


  /*handle insert file*/

  $insertQestionQuery = $database->prepare("
      INSERT INTO questions(
          exam_id,
          title,
          expirt_time,
          question_mark,
          img,
          via,
          TYPE,
          choose_answer,
          a,
          b,
          c,
          d,
          suggest,
          answer_details
      )
      VALUES(
          :exam_id,
          :title,
          :expirt_time,
          :question_mark,
          :img,
          :via,
          :type,
          :choose_answer,
          :a,
          :b,
          :c,
          :d,
          :suggest,
          :answer_details
      )
  ");
  $insertQestionQuery->bindParam(":exam_id", $question["exam_id"]);
  $insertQestionQuery->bindParam(":title", $question["title"]);
  $insertQestionQuery->bindParam(":expirt_time", $question["expirt_time"]);
  $insertQestionQuery->bindParam(":question_mark", $question["question_mark"]);
  $insertQestionQuery->bindParam(":img", $question["img"]);
  $insertQestionQuery->bindParam(":via", $question["via"]);
  $insertQestionQuery->bindParam(":type", $question["type"]);
  $insertQestionQuery->bindParam(":choose_answer", $question["choose_answer"]);
  $insertQestionQuery->bindParam(":a", $question["a"]);
  $insertQestionQuery->bindParam(":b", $question["b"]);
  $insertQestionQuery->bindParam(":c", $question["c"]);
  $insertQestionQuery->bindParam(":d", $question["d"]);
  $insertQestionQuery->bindParam(":suggest", $question["suggest"]);
  $insertQestionQuery->bindParam(":answer_details", $question["answer_details"]);


  $insertQestionQuery->execute();
  if ($insertQestionQuery->rowCount() <= 0) {
    $question["via"] == "upload"?unlink($target_file):"";
    HandleError("", 500);
  }

  $lastQuestionid = $database->lastInsertId();

  if ($question["type"] == "text") {
    $database->prepare("INSERT INTO text_answers(question_id,exam_id,text)
    VALUES(?,?,?)")->execute(array($lastQuestionid, $question["exam_id"],
      $question["text_answer"]));
  }

  $database->prepare("
         UPDATE exams SET
         questions_count = questions_count +1,
         marks = marks +:mark
         WHERE id=:id
         LIMIT 1
  ")->execute([":mark" => $question["question_mark"], ":id" => $question["exam_id"]]);



  print_r(json_encode(Array(
    "status" => "success"
  )));
}else {
  http_response_code(405);

}