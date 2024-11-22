<?php

include("./inc/connect.php");
include("./inc/functions.php");

if ($_SERVER['REQUEST_METHOD'] == "GET") {
  $id = (int)$_GET["id"];
  if ($id <= 0) {
    HandleError("", 500);
  }

  $getExamQuery = $database->prepare("SELECT id from exam_results  where id = :id
  AND user_id=:user_id AND is_done =0 Limit 1");
  $getExamQuery->bindParam(":id", $id, PDO::PARAM_INT);
  $getExamQuery->bindParam(":user_id", $user_id, PDO::PARAM_INT);
  $getExamQuery->execute();
  $exam_data = $getExamQuery->fetch(PDO::FETCH_ASSOC);
  if (!$exam_data["id"]) {
    HandleError("", 404);
  }

  die(Handle_exam_done($user_id, $id));
} else {
  http_response_code(405);
}