<?php

namespace App;
/**
 * SVG to raster conversion example using ImageMagick
 *
 * Please note that conversion via ImageMagick may not always produce ideal results,
 * especially when using CSS styling (external or via <defs>), also it depends on OS and Imagick version.
 *
 * Using the Inkscape command line may be the better option:
 *
 * @see https://wiki.inkscape.org/wiki/Using_the_Command_Line
 * @see https://github.com/chillerlan/php-qrcode/discussions/216
 *
 * @created      19.09.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

use Imagick;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRMarkupSVG;

require_once __DIR__.'/../vendor/autoload.php';

class SVGConvert extends QRMarkupSVG{

	/** @inheritDoc */
	protected function header():string{
		[$width, $height] = $this->getOutputDimensions();

		// we need to specify the "width" and "height" attributes so that Imagick knows the output size
		$header = sprintf(
			'<svg xmlns="http://www.w3.org/2000/svg" class="qr-svg %1$s" viewBox="%2$s" preserveAspectRatio="%3$s" width="%5$s" height="%6$s">%4$s',
			$this->options->cssClass,
			$this->getViewBox(),
			$this->options->svgPreserveAspectRatio,
			$this->options->eol,
			($width * $this->scale), // use the scale option to modify the size
			($height * $this->scale)
		);

		if($this->options->svgAddXmlHeader){
			$header = sprintf('<?xml version="1.0" encoding="UTF-8"?>%s%s', $this->options->eol, $header);
		}

		return $header;
	}

	/** @inheritDoc */
	public function dump(string $file = null):string{
		$base64 = $this->options->outputBase64;
		// we don't want the SVG in base64
		$this->options->outputBase64 = false;

		$svg = $this->createMarkup($file !== null);

		// now convert the output
		$im = new Imagick;
		$im->readImageBlob($svg);
		$im->setImageFormat($this->options->imagickFormat);

		if($this->options->quality > -1){
			$im->setImageCompressionQuality(max(0, min(100, $this->options->quality)));
		}

		$imageData = $im->getImageBlob();

		$im->destroy();
		$this->saveToFile($imageData, $file);

		if($base64){
			// use finfo to guess the mime type
			$imageData = $this->toBase64DataURI($imageData, (new finfo(FILEINFO_MIME_TYPE))->buffer($imageData));
		}

		return $imageData;
	}

}


