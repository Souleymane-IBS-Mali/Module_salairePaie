<?php


require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/paiementsalaire/lib/paiementsalaire.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

$file_content = file_get_contents('societe_paies.php');
$encoded = base64_encode($file_content);
file_put_contents('encoded.php', "<?php eval(base64_decode('$encoded')); ?>");