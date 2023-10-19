<?php 

    $con = mysqli_connect("localhost", "root", "", "certificae_generator_sender");

    define('FONTS_ROOT', '/assets/fonts/');
    define('CERTIFICAE_ROOT', '/templates/certificates/');
    define('HTML_ROOT', '/templates/html/');
    define('TEXT_ROOT', '/templates/text/');
    define('CERTIFICATE_OUTPUT_ROOT', '/outputs/');

    function encodeEventName($name) {
        return str_replace(' ' , '_', $name);
    }

    function send_response($status, $messageOrData){
        http_response_code($status);

        if(intdiv($status, 100) == 2) {
            die(json_encode([
                'status' => 'success',
                'data' => $messageOrData
            ]));
        }else{
            die(json_encode([
                'status' => 'error',
                'message' => $messageOrData
            ]));
        }
    }
?>