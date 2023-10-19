<?php

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Authorization, X-Requested-With');

    require_once "assets/php/config.php";

    $importData = json_decode(file_get_contents('importData.json'), true);

    $templates = json_decode(file_get_contents("php://input"), true) ?? $importData['templates'];

    if(extract($templates) < 4 || !isset($event) || !isset($certificateFile) || !isset($htmlFile) || !isset($textFile)) {
        http_response_code(401);

        die(json_encode(
            [
                'status' => 'error',
                'message' => 'event, certificateFile, htmlFile, textFile are required'
            ]
        ));
    }

    if(empty($event)) {
        http_response_code(401);

        die(json_encode(
            [
                'status' => 'error',
                'message' => 'event can\'t be empty'
            ]
        ));
    }
    
    $files = [
        "certificate_file" => [
            __DIR__ . "/templates/certificates",
            trim($certificateFile) 
        ],
        "html_file" => [
            __DIR__ . "/templates/html",
            trim($htmlFile)
        ],
        "text_file" => [
            __DIR__ . "/templates/text",
            trim($textFile) 
        ],
    ];

    $fileExists = true;
    foreach($files as $key => $file) {
        if(!file_exists(join("/", $file))) {
            $fileExists = false;
            break;
        }
    }

    if(!$fileExists) {
        http_response_code(401);

        die(json_encode(
            [
                'status' => "error",
                'message' => $key . " not exists"
            ]
        ));
    }
    
    $templateId = uniqid();
    $event = mysqli_real_escape_string($con, trim($event));
    $certificateFile = mysqli_real_escape_string($con, trim($certificateFile));
    $htmlFile = mysqli_real_escape_string($con, trim($htmlFile));
    $textFile = mysqli_real_escape_string($con, trim($textFile));

    $query = mysqli_query($con, "SELECT *,id as templateId FROM templates WHERE event = '$event' AND certificate_file = '$certificateFile' AND html_file = '$htmlFile' AND text_file = '$textFile'");

    if(mysqli_num_rows($query) > 0) {
        http_response_code(200);

        $data = mysqli_fetch_assoc($query);

        die(json_encode(
            [
                'status' => 'success',
                'data' => [
                    $data
                ] 
            ]
        ));
    }

    $query = mysqli_query($con, "
        INSERT INTO 
            templates(id, event, certificate_file, html_file, text_file) 
            VALUES(
                '$templateId',
                '$event',
                '$certificateFile',
                '$htmlFile',
                '$textFile'
            )
    ");

    
    $data = [
        'templateId' => $templateId,
        'event' => $event,
        'certificate_file' => $certificateFile,
        'html_file' => $htmlFile,
        'text_file' => $textFile
    ];

    http_response_code(201);
    
    die(json_encode(
        [
            'status' => 'success',
            'data' => [
                $data
            ] 
        ]
    ));
?>