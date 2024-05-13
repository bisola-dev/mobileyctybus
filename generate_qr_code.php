<?php

require 'vendor/autoload.php';

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Image\Png;
use BaconQrCode\Writer;

// Data to encode
$data = 'Hello, World!';

// Configure the QR code
$renderer = new Png();
$renderer->setHeight(256);
$renderer->setWidth(256);
$renderer->setMargin(10);

$writer = new Writer($renderer);
$qrCode = Encoder::encode($data, ErrorCorrectionLevel::L());

// Save QR code to file
$writer->writeFile($qrCode, 'qrcode.png');
