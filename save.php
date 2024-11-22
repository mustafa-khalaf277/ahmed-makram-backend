<?php
include("./inc/connect.php");
include("./inc/functions.php");

if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /* check data*/
  $id = (int)$_GET["id"] != 0 ?(int)$_GET["id"]:HandleError("", 500);
  $saveQuery = $database->prepare("UPDATE user_questions_answers SET is_save =
  !is_save WHERE question_id=$id AND user_id =$user_id");
  $saveQuery->execute();
  if ($saveQuery->rowCount() <= 0)HandleError("", 500);

}else {
  http_response_code(405);

}