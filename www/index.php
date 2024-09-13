<?php

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

require_once '../vendor/autoload.php';
const defaultWidth = 150;
const defaultHeight = 150;

const maxWidth = 800;

const maxHeight = 800;
const defaultMargin = 4;

const defaultCorrectionLevel = ErrorCorrectionLevel::Low;

const defaultCoding = 'UTF-8';

/**
 * Při jakékoli chybě zobrazí prázdný obrázek, což je aktuálně bílá plocha
 * width a height jsou z parametrů, pokud chybí v parametru bere se defaultní
 * @param int $width
 * @param int $height
 * @return never
 */
function showErrorImage(int $width, int $height): never
{
	$whiteImage = imagecreatetruecolor($width, $height);
	$white = imagecolorallocate($whiteImage, 255, 255, 255);
	imagefill($whiteImage,0,0, $white);
	header('Content-Type: image/png');
	imagepng($whiteImage);
	exit;
}

/**
 * Vytvoří a zobrazí qr kód
 * @param string $data Co má výt zakódováno v QR kódu
 * @param int $width
 * @param int $height
 * @param ErrorCorrectionLevel $correctionLevel Zkusit s "low" když jsou $data příliš velká pro vyšší korekci
 * @param int $margin Margin okolo obrázku, snížit na 0 pokud jsou moc velká $data
 * @param string $coding
 * @return never
 * @throws Exception
 */
function showQrImage(string $data, int $width, int $height, ErrorCorrectionLevel $correctionLevel, int $margin, string $coding): never
{
	// Knihovna kreslí pouze čtvercové qr kódy, vezmeme menší z width a height a odečteme margin
	$size = min($width, $height) - 2 * $margin;
	// Vytvoření qr kódu
	$qr = Builder::create()
		->writer(new PngWriter())
		->writerOptions([])
		->data($data)
		->encoding(new Encoding($coding)) //'ISO-8859-1' - lepší kompatibilita ale nezvládá české znaky apod.
		->errorCorrectionLevel($correctionLevel)
		->size($size)
		->margin($margin)
		->roundBlockSizeMode(RoundBlockSizeMode::Margin)
		->validateResult(false)
		->build();
	// Pokud je width === height, máme hotovo jen zobrazíme
	if ($width === $height) {
		header('Content-Type: image/png');
		echo $qr->getString();
	} else { // Pokud width !== height, přidáme bílé místo kam je potřeba
		$image = imagecreatefromstring($qr->getString());
		$sizedImage = imagecreatetruecolor($width, $height);
		$white = imagecolorallocate($sizedImage, 255, 255, 255);
		imagefill($sizedImage,0,0, $white);
		imagecopy($sizedImage, $image, round(($width - imagesx($image)) / 2), round(($height - imagesy($image)) / 2) , 0, 0, imagesx($image), imagesy($image));
		header('Content-Type: image/png');
		imagepng($sizedImage);
	}
	exit;
}

// Získání parametrů
$parts = parse_url($_SERVER['REQUEST_URI']);
parse_str($parts['query'] ?? '', $params);

// Parametr 'chs' který by měl být např. 100x100 - velikost obrázku
// Zkusíme získat, pokud není parametr, nebo jsou v něm nesmysly (není číslo, není 'x'), bere defaultní
// Pokud není část 'x100', ale jen např chs=100, height veme stejné jako width
if(isset($params['chs'])) {
	$dims = explode('x', trim($params['chs']));
	if ($dims[0] === (string)(int) $dims[0]) {
		$width = (int) $dims[0];
	} else {
		$width = defaultWidth;
	}
	if (isset($dims[1]) && $dims[1] === (string)(int) $dims[1]) {
		$height = (int) $dims[1];
	} else {
		$height = $width;
	}
} else {
	$width = defaultWidth;
	$height = defaultHeight;
}
$width = min($width, maxWidth);
$height = min($height, maxHeight);

// Parametr 'chl' jsou data, která mají být v obrázku, urlencoded
// Pokud parametr chybí, zobrazíme defaultní error image
if( ! isset($params['chl'])) {
	showErrorImage($width, $height);
}
$data = urldecode($params['chl']);

// Parametr 'chld' je error correction level a margin ve formátu 'level|margin'
// level může být 'L' (default) - low, 'M' - medium, 'Q' - quartile, 'H' - high
$correctionLevel = defaultCorrectionLevel;
$margin = defaultMargin;
if(isset($params['chld'])) {
	[$level, $insertedMargin] = explode('|', $params['chld']);
	$correctionLevel = match ($level) {
		'M' => ErrorCorrectionLevel::Medium,
		'Q' => ErrorCorrectionLevel::Quartile,
		'H' => ErrorCorrectionLevel::High,
		default => ErrorCorrectionLevel::Low
	};
	if (isset($insertedMargin) && $insertedMargin === (string)(int) $insertedMargin) {
		$margin = (int) $insertedMargin;
	}
}
// Kódování defaultně 'UTF-8', lze vybrat 'ISO-8859-1' parametrem 'choe'
$coding = match ($params['choe'] ?? '') {
	'ISO-8859-1' => 'ISO-8859-1',
	default => defaultCoding
};

try {
	// Pokusíme se vytvořit a zobrazit QR k s danými daty
	showQrImage($data, $width, $height, $correctionLevel, $margin, $coding);
} catch (Throwable $e) {
	// Pokud se nepovedlo, nejčastěji kvůli moc dlouhému $data. Snížením $correctionLevel a $margin tam můžeme nacpat víc dat
	// Nebo coding, někdo zadá ISO a dá tam nepovolené znaky
	try {
		showQrImage($data, $width, $height, ErrorCorrectionLevel::Low, 0,  'UTF-8');
	} catch (Throwable $e) {
		// Pokud ani to se nepovedlo, error
		showErrorImage($width, $height);
	}
}
