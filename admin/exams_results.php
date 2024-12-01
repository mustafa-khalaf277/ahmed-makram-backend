<?php
include("../inc/connect.php");
include("../inc/functions.php");
if ($_SERVER['REQUEST_METHOD'] == "GET") {
  /* handle check admin */
  $page = 1;
  if ((int)$_GET["page"]) $page = (int)$_GET["page"];
  if (!(int)$_GET["exam_id"] || (int)$_GET["exam_id"] <=
    0) HandleError("", 500);

  $exam_id = (int)$_GET["exam_id"];
  $q = $_GET["q"];
  $order_by = $_GET["order_by"];
  $order_method = $_GET["order_method"] == "ASC"?" ASC":" DESC";
  $pageLimit = 15;
  $offset = (int)($pageLimit * ($page - 1));

  $sql = "SELECT
       exam_results.correct_answers,
       exam_results.Corrected,
       exam_results.exam_marks,
       users.name,
       users.phone,
       users.parent_phone,
       exams.marks,
       users.id
     FROM exam_results,exams,users
     WHERE exams.id = exam_results.exam_id AND exam_results.is_done = 1 AND
     users.id = exam_results.user_id ";

  $params = [];
  if ($exam_id) {
    $sql .= " AND exam_results.exam_id = ?";
    $params[] = $exam_id;
  }
  if ($q) {
    $sql .= " AND users.name LIKE ?";
    $params[] = '%'.htmlspecialchars($q).'%';
    $sqlParams = " AND users.name LIKE '%".htmlspecialchars($q)."%'";
  }
  if ($exam_id) {
    $sql .= " AND exam_results.exam_id = ?";
    $params[] = $exam_id;
  }


  in_array($order_by, array("id",
    "exam_marks")) ? $sql .= " ORDER BY exam_results.".$order_by: $sql .= " ORDER
    BY exam_results.id ";
  $sql .= $order_method;
  $sql .= "   LIMIT $offset,$pageLimit  ";



  $exams_query = $database->prepare($sql);
  $exams_query->execute($params);

  $totalQuery = $database->prepare("SELECT COUNT(exam_results.id) as total FROM
  exam_results,users WHERE exam_results.is_done = 1 AND
  exam_results.exam_id=$exam_id AND users.id=exam_results.user_id  $sqlParams");

  $totalQuery->execute();
  print_r(json_encode(array(
    "total" => $totalQuery->fetch(PDO::FETCH_ASSOC)["total"],
    "data" => $exams_query->fetchAll(PDO::FETCH_ASSOC),
  )));

} else {
  http_response_code(405);

}