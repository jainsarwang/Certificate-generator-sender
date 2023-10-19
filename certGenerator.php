<?php 
	header('Content-Type: application/json');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: POST');
	header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Authorization, X-Requested-With');

	require_once "assets/php/config.php";

	// if($_SERVER['REQUEST_METHOD'] !== 'POST'){
	// 	http_response_code(404);
	// 	die(json_encode([
	// 		"status" => "error",
	// 		"message" => "Only POST method is Allowed"
	// 	]));
	// }

    $importData = json_decode(file_get_contents('importData.json'), true);
    $postData = json_decode(file_get_contents("php://input"), true) ?? $importData;
	
	if(!isset($postData['templateId']) || empty($postData['templateId'])) {
		http_response_code(401);

		die(json_encode(
			[
				'status' => 'error',
				'message' => 'templateId is missing'
			]
		));
	}
	
	$templateId = $postData['templateId'];
	$textCoords = $postData['coordinates'] ?? send_response(403, "coordinates are required");
	$textColor = $postData['textColor'] ?? send_response(403, "textColor is required");
	$fontSizes = $postData['fontSizes'] ?? send_response(403, "fontSizes are required");
	$fonts = $postData['fonts'] ?? send_response(403, "fonts are required");
	$certificateTexts = $postData['texts'] ?? send_response(403, "texts are required");
	$emails = $postData['emails'] ?? send_response(401, "emails is required");
	$receiversName = $postData['receiversName'] ?? send_response(401, "receiversName is required");

	$query = mysqli_query($con, "SELECT * FROM templates WHERE id = '$templateId'") or die("Unable to fetch template Id " . mysqli_error($query));

	if(mysqli_num_rows($query) == 0) {
		http_response_code(401);

		die(json_encode(
			[
				'status' => 'error',
				'message' => 'Inalid templateId'
			]
		));
	}
	$templateData = mysqli_fetch_assoc($query);
	
	$outputPath = __DIR__ . CERTIFICATE_OUTPUT_ROOT . encodeEventName($templateData['event']) . "\/";
	if(!file_exists($outputPath)) mkdir($outputPath);

	$certificate = __DIR__ . CERTIFICAE_ROOT . $templateData['certificate_file'];
	if(!file_exists($certificate)) {
		http_response_code(403);

		die(json_encode(
			[
				'status' => 'error',
				'message' => 'Certificate template not Exists'
			]
		));
	}
	$certificateInfo = getimagesize($certificate);
	$originalWidth = $certificateInfo[0];
	$originalHeight = $certificateInfo[1];
	$mimeType = $certificateInfo['mime'];

	if($mimeType == 'image/png') 
		$originalCertificate = imagecreatefrompng($certificate);
	else if($mimeType == 'image/jpeg') 
		$originalCertificate = imagecreatefromjpeg($certificate);
	else 
		$originalCertificate = imagecreatetruecolor(1280, 720);
	
	$textColor = imagecolorallocate($originalCertificate, ...$textColor);

	$generatedCertCount = 0;	
	foreach($certificateTexts as $imageIxd => $imageTexts){
		$cert = imagecreatetruecolor($originalWidth, $originalHeight);
		imagecopy($cert, $originalCertificate, 0, 0, 0, 0, $originalWidth, $originalHeight);

		// adding all texts to Image
		foreach($imageTexts as $ixd => $text) {
			$x = $textCoords[$ixd][0];
			$y = $textCoords[$ixd][1];
			$text = ucwords($text);
			$font = __DIR__ . FONTS_ROOT . ($fonts[$ixd] ?? 'Ache-Bold-Italic.ttf');
			$textBox = imagettfbbox($fontSizes[$ixd], 0, $font, $text);
			$textWidth = $textBox[4];
			$textHeight = $textBox[1] - $textBox[5];
			
			if(isset($textCoords[$ixd][2]) && strtolower(trim($textCoords[$ixd][2])) == 'center'){
				
				$x -= $textWidth / 2;
				// $y -= $textHeight / 2;
				
			}
			
			imagettftext($cert, $fontSizes[$ixd], 0, $x, $y, $textColor, $font, $text);

		}
		
		$fileName = $emails[$imageIxd] . ".png";
		$outputFile = $outputPath . $fileName;
		
		if(isset($postData['store']) && !$postData['store']){
			header("Content-Type: image/png");
			imagepng($cert,null,9);
			break;
		}else if(imagepng($cert, $outputFile, 9)) {
			$query = mysqli_query($con, "SELECT * FROM certificates WHERE email = '" . $emails[$imageIxd] . "' AND template_id = '$templateId' AND send_at IS NULL");
	
			if(mysqli_num_rows($query) == 0){
				$query = "INSERT INTO 
					certificates(name, email, template_id, file) 
					VALUES(
						'" . $receiversName[$imageIxd] . "',
						'" . $emails[$imageIxd] . "',
						'$templateId',
						'$fileName'
					)
				";
				
				if(mysqli_query($con, $query)) 
					$generatedCertCount++;
				else
					unlink($outputFile);
			}else
				$generatedCertCount++;
		}
		
		imagedestroy($cert);
	}
	imagedestroy($originalCertificate);

	if(!isset($postData['store']) || $postData['store']) 
		send_response(201, 
			[
				'message' => $generatedCertCount . " / " . count($certificateTexts) . " are generated",
				'generated' => $generatedCertCount,
				'total' => count($certificateTexts)
			]
		);
?>