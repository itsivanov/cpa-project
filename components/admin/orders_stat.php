﻿<?php

$filters = [];

// Офферы
$filters['offers'] = [];

$q = "SELECT t1.id, t1.name
     FROM goods AS t1 INNER JOIN users2goods as t2 on t1.id = t2.g_id
      WHERE available_in_offers = 1";
$stmt = $GLOBALS['DB']->query($q);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $filters['offers'][$row['id']] = $row['name'];
}

// Потоки
$filters['streams'] = [];
$stmt = $GLOBALS['DB']->query("SELECT f_id as id, name FROM flows");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $filters['streams'][$row['id']] = $row['name'];
}

// Лендинги --- выводим все лендинги, которые поключены к офферам вебмастера
$filters['landings'] = [];

$q = "SELECT DISTINCT t1.c_id as id, t1.name, t4.name as offer_name, t3.g_id as offer_id
    FROM content as t1 INNER JOIN offer_content as t2 ON t1.c_id = t2.landing_id
         INNER JOIN users2goods as t3 ON t3.g_id = t2.offer_id
         INNER JOIN goods AS t4 ON t4.id = t3.g_id
    ORDER BY t4.name, t1.name";
$stmt = $GLOBALS['DB']->query($q);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $filters['landings'][] = [
    "offer" => [
      "id" => $row['offer_id'],
      "name" => $row['offer_name']
    ],
    "landing" => [
      "id" => $row['id'],
      "name" => $row['name']
    ],
  ];
}

// Блоги --- выводим все лендинги, которые поключены к офферам вебмастера
$filters['blogs'] = [];
$q = "SELECT t1.c_id as id, t1.name, t4.name as offer_name, t3.g_id as offer_id
    FROM content as t1 INNER JOIN offer_content as t2 ON t1.c_id = t2.blog_id
         INNER JOIN users2goods as t3 ON t3.g_id = t2.offer_id
         INNER JOIN goods AS t4 ON t4.id = t3.g_id
    ORDER BY t4.name, t1.name";
$stmt = $GLOBALS['DB']->query($q);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $filters['blogs'][] = [
    "offer" => [
      "id" => $row['offer_id'],
      "name" => $row['offer_name']
    ],
    "blog" => [
      "id" => $row['id'],
      "name" => $row['name']
    ],
  ];
}

// Источники
$filters['spaces'] = [];

$stmt = $GLOBALS['DB']->query("SELECT id, name FROM spaces");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $filters['spaces'][$row['id']] = $row['name'];
}

// SUBID
$filters['subid'] = [
  "subid1" => [],
  "subid2" => [],
  "subid3" => [],
  "subid4" => [],
  "subid5" => [],
];

$q = "SELECT t1.subid_name, t1.subid_val
      FROM subid_stat AS t1 INNER JOIN pfinder_keys AS t2 ON t1.pfinder_id = t2.pfinder_id";
$stmt = $GLOBALS['DB']->prepare($q);
$stmt->execute([$uid]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  if (!in_array($row['subid_val'], $filters['subid'][$row['subid_name']])) {
    $filters['subid'][$row['subid_name']][] = $row['subid_val'];
  }
}

$smarty->assign('users', User::get_by_role_name("webmaster"));
$smarty->assign('today', date("d-m-Y"));


$filters['status'] = [
  "canceled" => "Отклонено",
  "approved" => "Принято",
  "processing" => "В ожидании",
];

$filters['status2'] = [];
$query = "SELECT DISTINCT status2_name
          FROM orders
          WHERE status2_name NOT IN ('Перезаказ', 'Доставка невозможна', 'Нет а наличии', '')";

$stmt = $GLOBALS['DB']->query($query);
$filters['status2'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

$smarty->assign('filters', $filters);
$smarty->display("admin" . DS . "stats" . DS . "orders.tpl");

enqueue_scripts( array(
  "/assets/global/plugins/datatables/datatables.min.js",
    "/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js",
    "/assets/global/scripts/datatable.js",
  "/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js",
  "/assets/global/plugins/select2/js/select2.min.js",
  "/misc/js/page-level/ordersStat.js"));

?>