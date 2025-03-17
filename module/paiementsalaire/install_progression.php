<?php
require './../main.inc.php';
// Récupération de la progression
// Fonction pour mettre à jour la progression
$instSql = "SELECT niveau, total FROM ".MAIN_DB_PREFIX."installation";
$result = $db->query($instSql);//= $db->query($covSql);
$obj = $db->fetch_object($result);

$progress = ['effectue' => $obj->niveau , 'total' => $obj->total];
header('Content-Type: application/json');
echo json_encode($progress);