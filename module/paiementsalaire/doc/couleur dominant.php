<?php
function getDominantColor($imagePath, $reduceFactor = 10) {
    // Vérifie si l'image existe
    if (!file_exists($imagePath)) {
        die("Fichier introuvable.");
    }

    // Récupère les infos sur l'image
    $info = getimagesize($imagePath);
    $mime = $info['mime'];

    // Crée une ressource image selon le type MIME
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($imagePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($imagePath);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($imagePath);
            break;
        default:
            die("Format non supporté.");
    }

    // Réduction de la taille pour accélérer l'analyse
    $width = imagesx($image);
    $height = imagesy($image);
    $resized = imagecreatetruecolor($reduceFactor, $reduceFactor);
    imagecopyresampled($resized, $image, 0, 0, 0, 0, $reduceFactor, $reduceFactor, $width, $height);

    $colors = [];

    for ($x = 0; $x < $reduceFactor; $x++) {
        for ($y = 0; $y < $reduceFactor; $y++) {
            $rgb = imagecolorat($resized, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            $key = "$r,$g,$b";
            if (!isset($colors[$key])) {
                $colors[$key] = 1;
            } else {
                $colors[$key]++;
            }
        }
    }

    // Trie les couleurs par fréquence décroissante
    arsort($colors);
    $dominantRGB = array_key_first($colors);

    list($r, $g, $b) = explode(',', $dominantRGB);

    return [
        'r' => (int)$r,
        'g' => (int)$g,
        'b' => (int)$b,
        'hex' => sprintf("#%02x%02x%02x", $r, $g, $b)
    ];
}

// Exemple d'utilisation
$imagePath = 'logo.png'; // Chemin vers votre logo
$result = getDominantColor($imagePath);

echo "Couleur dominante (RGB): R={$result['r']} G={$result['g']} B={$result['b']}\n";
echo "Couleur dominante (HEX): {$result['hex']}\n";
