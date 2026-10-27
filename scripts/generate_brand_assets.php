<?php

declare(strict_types=1);

function color(GdImage $image, string $hex): int
{
    $hex = ltrim($hex, '#');

    return imagecolorallocate(
        $image,
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    );
}

function roundedRectangle(GdImage $image, int $x1, int $y1, int $x2, int $y2, int $radius, int $fill): void
{
    imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y2, $fill);
    imagefilledrectangle($image, $x1, $y1 + $radius, $x2, $y2 - $radius, $fill);
    imagefilledellipse($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $fill);
    imagefilledellipse($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $fill);
    imagefilledellipse($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $fill);
    imagefilledellipse($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $fill);
}

function drawMark(GdImage $image, int $x, int $y, int $size): void
{
    $primary = color($image, '#176b87');
    $white = color($image, '#ffffff');
    roundedRectangle($image, $x, $y, $x + $size, $y + $size, (int) round($size * .28), $primary);

    imagesetthickness($image, max(2, (int) round($size * .08)));
    imageline($image, $x + (int) ($size * .29), $y + (int) ($size * .29), $x + (int) ($size * .71), $y + (int) ($size * .29), $white);

    roundedRectangle(
        $image,
        $x + (int) ($size * .20),
        $y + (int) ($size * .39),
        $x + (int) ($size * .80),
        $y + (int) ($size * .82),
        max(2, (int) round($size * .12)),
        $white,
    );

    imagesetthickness($image, max(2, (int) round($size * .08)));
    imageline($image, $x + (int) ($size * .34), $y + (int) ($size * .59), $x + (int) ($size * .45), $y + (int) ($size * .70), $primary);
    imageline($image, $x + (int) ($size * .45), $y + (int) ($size * .70), $x + (int) ($size * .67), $y + (int) ($size * .47), $primary);
}

function icon(int $size, string $path): string
{
    $image = imagecreatetruecolor($size, $size);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    imagealphablending($image, true);
    drawMark($image, 0, 0, $size - 1);
    imagepng($image, $path, 9);
    ob_start();
    imagepng($image, null, 9);
    $png = (string) ob_get_clean();
    imagedestroy($image);

    return $png;
}

$root = dirname(__DIR__);
$images = $root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images';
if (! is_dir($images) && ! mkdir($images, 0775, true) && ! is_dir($images)) {
    throw new RuntimeException('Folder gambar tidak dapat dibuat.');
}

$faviconPng = icon(32, $images.DIRECTORY_SEPARATOR.'icon-32.png');
icon(180, $root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'apple-touch-icon.png');
icon(192, $images.DIRECTORY_SEPARATOR.'icon-192.png');
icon(512, $images.DIRECTORY_SEPARATOR.'icon-512.png');

$icoHeader = pack('vvv', 0, 1, 1);
$icoEntry = pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($faviconPng), 22);
file_put_contents($root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'favicon.ico', $icoHeader.$icoEntry.$faviconPng);

$share = imagecreatetruecolor(1200, 630);
$canvas = color($share, '#f4f7fb');
$ink = color($share, '#172033');
$muted = color($share, '#667085');
imagefill($share, 0, 0, $canvas);
drawMark($share, 90, 135, 180);

$fontRegular = PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows\\Fonts\\segoeui.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
$fontBold = PHP_OS_FAMILY === 'Windows' ? 'C:\\Windows\\Fonts\\segoeuib.ttf' : '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
if (is_file($fontRegular) && is_file($fontBold)) {
    imagettftext($share, 58, 0, 330, 265, $ink, $fontBold, 'SuaraKita');
    imagettftext($share, 27, 0, 334, 325, $muted, $fontRegular, 'Aplikasi voting internal');
    imagettftext($share, 22, 0, 334, 378, $muted, $fontRegular, 'Aman, ringkas, dan mudah digunakan.');
} else {
    imagestring($share, 5, 330, 250, 'SuaraKita', $ink);
    imagestring($share, 4, 332, 300, 'Aplikasi voting internal', $muted);
}
imagepng($share, $images.DIRECTORY_SEPARATOR.'suarakita-share.png', 9);
imagedestroy($share);

echo "Brand assets generated.\n";
