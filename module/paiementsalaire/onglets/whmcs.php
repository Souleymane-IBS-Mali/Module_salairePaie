<?php




$file_content = file_get_contents('./onglets/societe_paies.php');
$encoded = base64_encode($file_content);
file_put_contents('./onglets/societe_paies_encode.php', "<?php eval(base64_decode('$encoded')); ?>");

$file_content = file_get_contents('./onglets/bulletin.php');
$encoded = base64_encode($file_content);
file_put_contents('./onglets/bulletin_encode.php', "<?php eval(base64_decode('$encoded')); ?>");