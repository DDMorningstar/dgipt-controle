<?php
/**
 * G�n�re une ic�ne PNG (192 / 512) � partir du logo DGIPT.
 */
$size = (int)($_GET['s'] ?? 192);
if ($size !== 192 && $size !== 512) $size = 192;

$srcPath = __DIR__ . '/logo-dgipt.jpg';
if (!is_file($srcPath) || !function_exists('imagecreatetruecolor') || !function_exists('imagecreatefromjpeg')) {
    header('Location: logo-dgipt.jpg');
    exit;
}

$src = @imagecreatefromjpeg($srcPath);
if (!$src) {
    header('Location: logo-dgipt.jpg');
    exit;
}

header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');

$sw = imagesx($src);
$sh = imagesy($src);
$im = imagecreatetruecolor($size, $size);
imagealphablending($im, true);
imagesavealpha($im, true);
$white = imagecolorallocate($im, 255, 255, 255);
imagefilledrectangle($im, 0, 0, $size, $size, $white);

$pad = (int) round($size * 0.08);
$inner = $size - ($pad * 2);
$scale = min($inner / $sw, $inner / $sh);
$dw = (int) round($sw * $scale);
$dh = (int) round($sh * $scale);
$dx = (int) round(($size - $dw) / 2);
$dy = (int) round(($size - $dh) / 2);
imagecopyresampled($im, $src, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh);

imagepng($im);
imagedestroy($im);
imagedestroy($src);
