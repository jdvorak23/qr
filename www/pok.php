<?php

use App\SVGConvert;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
require_once '../vendor/autoload.php';

// SVG from the basic example
$options = new QROptions([
	'version' => 7,
	'outputInterface' => SVGConvert::class,
	'imagickFormat' => 'png32',
	'scale' => 20,
	'outputBase64' => false,
	'drawLightModules' => true,
	'svgUseFillAttributes' => false,
	'connectPaths' => true,
	'keepAsSquare' => [
		QRMatrix::M_FINDER_DARK,
		QRMatrix::M_FINDER_DOT,
		QRMatrix::M_ALIGNMENT_DARK,
	],
]);




// render the SVG and convert to the desired ImageMagick format
$image = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

//header('Content-type: image/png');

echo $image;