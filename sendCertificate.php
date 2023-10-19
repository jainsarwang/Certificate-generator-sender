<?php

require_once 'assets/php/config.php';
require_once 'assets/emailManager/mailConnection.php';

$query = mysqli_query($con, "
    SELECT 
        GROUP_CONCAT(id) all_ids,
        GROUP_CONCAT(name) all_names,
        GROUP_CONCAT(email) all_emails,
        GROUP_CONCAT(file) all_certificates,
        count(*) total,
        template_id
    FROM certificates
    WHERE
        send_at IS NULL
        GROUP BY template_id
");

if(mysqli_num_rows($query) == 0) {
    send_response(200, "All mails are already sent");
}

$totalMails = 0;
$sentCount = 0;
$mail = new MailSender();
$mail->setFrom(MAIL_ACCOUNT, "GDSC UECU TEAM");  

while($record = mysqli_fetch_assoc($query)){
    // loop through each template ID

    $totalMails += $record['total'];
    $ids = explode(',',$record['all_ids']);
    $names = explode(',', $record['all_names']);
    $emails = explode(',', $record['all_emails']);
    $certificates = explode(',', $record['all_certificates']);
    $templateId = $record['template_id'];

    $t = mysqli_query($con, "SELECT * FROM templates WHERE id = '$templateId'");
    if(mysqli_num_rows($t) == 0) continue;
    
    ['html_file' => $htmlFile, 'text_file' => $textFile, 'event' => $event, 'subject' => $mailSubject] = mysqli_fetch_assoc($t);
    $htmlFile = __DIR__ . HTML_ROOT . $htmlFile;
    $textFile = __DIR__ . TEXT_ROOT . $textFile;
    $mail->addSubject($mailSubject);

    if(!file_exists($htmlFile) || !file_exists($textFile)) {
        send_response(403, "Files are missing");
    }

    $htmlTemplate = file_get_contents($htmlFile);
    $plainTemplate = file_get_contents($textFile);

    foreach($emails as $ixd => $email) {
        // loop through unsend mails
        $mailData = [
            'name' => $names[$ixd],
            'email' => $email
        ];
        
        $htmlData = $htmlTemplate;
        $plainData = $plainTemplate;

        foreach($mailData as $key => $val) {
            $htmlData = str_replace('{{' . $key . "}}", $val, $htmlData);
            $plainData = str_replace('{{' . $key . "}}", $val, $plainData);
        }
            
        $outputPath = __DIR__ . CERTIFICATE_OUTPUT_ROOT . encodeEventName($event) . "\/";
        $outputFile = $outputPath . $certificates[$ixd];

        $mail->addRecipent($mailData['email'], $mailData['name']);
        $mail->addHTMLBody($htmlData);
        $mail->addAltBody($plainData);
        $mail->addAttachment($outputFile, "GDSC_Certificate.png");
        
        $dataId = $ids[$ixd];
        $callBack = function ($error) {   
            global $con, $dataId;
            mysqli_query($con, "UPDATE certificates SET error = '$error' WHERE id = '" . $dataId . "' ");
        };

        if($mail->send($callBack)){
            $sentCount++;
            mysqli_query($con, "UPDATE certificates SET send_at = now() WHERE id = '" . $dataId . "' ");
        }else{
            break;
        }
    }
}

send_response(200, [
    'sent_mails' => $sentCount,
    'total_mails' => $totalMails
]);
?>