<?php

use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
require_once '../vendor/autoload.php';

function debug(mixed $value, $dump = false)
{
	echo '<pre>';
	if ($dump)
		var_dump($value);
	else
		print_r($value);
	echo '</pre>';
}

$data = 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net';

$options = new QROptions([
	'returnResource' => true,
	'outputType' => QROutputInterface::GDIMAGE_PNG//QROutputInterface::GDIMAGE_JPG
]);



//

$qrCode = new QRCode($options);
header('Content-Type: image/png');
imagegd($qrCode->render($data));
