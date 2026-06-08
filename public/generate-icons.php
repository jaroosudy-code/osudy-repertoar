<?php
// JEDNORAZOVÝ SKRIPT – po spustení ZMAŽ tento súbor!
if (!is_dir(__DIR__ . '/icons')) {
    mkdir(__DIR__ . '/icons', 0755, true);
}

function makeIcon(int $size): GdImage
{
    $img = imagecreatetruecolor($size, $size);
    $bg  = imagecolorallocate($img, 15, 23, 42);    // slate-900 #0f172a
    $fg  = imagecolorallocate($img, 245, 158, 11);  // amber-400 #f59e0b

    imagefill($img, 0, 0, $bg);

    $u = $size / 100.0;

    // Nota – hlava
    imagefilledellipse($img, (int)(58 * $u), (int)(76 * $u), (int)(30 * $u), (int)(22 * $u), $fg);

    // Nota – stopka
    imagesetthickness($img, max(1, (int)(5 * $u)));
    imageline($img, (int)(72 * $u), (int)(67 * $u), (int)(72 * $u), (int)(22 * $u), $fg);

    // Nota – vlajka
    imagesetthickness($img, max(1, (int)(3 * $u)));
    imageline($img, (int)(72 * $u), (int)(22 * $u), (int)(88 * $u), (int)(34 * $u), $fg);
    imageline($img, (int)(72 * $u), (int)(34 * $u), (int)(88 * $u), (int)(46 * $u), $fg);

    return $img;
}

$icons = [
    'icons/icon-192.png'   => 192,
    'icons/icon-512.png'   => 512,
    'apple-touch-icon.png' => 180,
];

foreach ($icons as $file => $size) {
    $img = makeIcon($size);
    imagepng($img, __DIR__ . '/' . $file);
    imagedestroy($img);
    echo "✅ Vytvorený: {$file}<br>";
}

echo "<br><strong>Hotovo! Teraz ZMAŽ tento súbor (generate-icons.php) z FTP!</strong>";
