<?php
include("./inc/connect.php");
include("./inc/functions.php");

if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /* check data*/
  $id = (int)$_GET["id"] != 0 ?(int)$_GET["id"]:HandleError("", 404);
  /*get exam details*/
  $getExamQuery = $database->prepare("
                        SELECT
                          exams.course_id,
                          exams.created_at,
                          exams.title,
                          exams.description,
                          exams.questions_count,
                          exams.marks,
                          exams.questions_count,
                          exams.count_of_suggestions,
                          exams.relation_exam_id,
                          courses.title AS course_title
                      FROM
                          exams,
                          courses
                      WHERE
                          exams.id = :id AND courses.id = exams.course_id
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
  /*return course details*/
  if ($exam_data["relation_exam_id"] >= 1) {

    Check_the_last_relation_exam_is_done($user_id, $exam_data["relation_exam_id"]);
  }
  print_r(json_encode(Array(
    "status" => "success",
    "data" => $exam_data
  )));
}else {
  http_response_code(405);

}