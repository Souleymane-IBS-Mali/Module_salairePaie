<?php

// Récupération de la progression
// Fonction pour mettre à jour la progression
    $filename = "tmp_sal.txt";
    $handle = fopen($filename, "r");

    if ($handle) {
        $i = 1;
        $val = "";
        $total = 0;
        while (($line = fgets($handle)) !== false) {
            if($i == 2)
                $val = $line;
            if($i == 4)
                $total = $line;

            $i++;
        }
        fclose($handle);
    } else {
        echo "Impossible d'ouvrir le fichier.";
    }

    $tab_id = explode('/', $val);
    if(count($tab_id) == 0)
        $progress = ['effectue' => $total , 'total' => $total];
    else
        $progress = ['effectue' => ($total - count($tab_id)+1), 'total' => $total];

header('Content-Type: application/json');
echo json_encode($progress);