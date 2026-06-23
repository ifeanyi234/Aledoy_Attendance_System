<?php

function long_date($date)
{
    return date('jS M Y', strtotime($date));
}
function long_datetime($date)
{
    return date('jS M Y g:i:s', strtotime($date));
}
function short_date($date)
{
    return date('d M Y', strtotime($date));
}

function host_name()
{
    return 'aledoyhost.com';
}

function sender_name()
{
    return 'noreply@aledoyhost.com';
}

function admin_email()
{
    return 'solutions@aledoy.com';
}

function sender_email()
{
    return 'contact@notionworks.com.ng';
}

function get_end_time($start)
{
    return date("Y-m-d: H:i:s", strtotime("+ 29 mins", strtotime($start)));
}


function get_token($name)
{
    return md5($name);
}

function organisation()
{
    return 'Aledoy';
}


function check_extension($filename)
{
    if ($filename) {
        $file = explode('.', $filename);
        $file2 = array_reverse($file);

        $ext = strtolower($file2[0]);

        if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
            return 'valid';
        } else {
            return 'invalid';
        }
    }
}

function get_extension($filename)
{
    $file = explode('.', $filename);
    $file2 = array_reverse($file);
    $ext = strtolower($file2[0]);
    return $ext;
}


function get_category_name($token)
{
    global $db;
    $query = "select * from category where token = '$token'";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_array($result);
    return $row['category_name'];
}

function case_study_img($token)
{
    global $db;
    $query = "select * from casestudy_images where casestudy_id = '$token'";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_array($result);
    return $row['image_url'];
}

function short_summary($tab, $limit)
{
    global $db;
    if ($tab == 'blog') {
        $query = "select * from $tab order by id desc limit $limit,1";
        $result = mysqli_query($db, $query);
        $row = mysqli_fetch_array($result);

        return strip_tags(substr($row['content'], 0, 100));
    } else {
        $query = "select * from $tab order by rand() limit $limit,1";
        $result = mysqli_query($db, $query);
        $row = mysqli_fetch_array($result);

        return strip_tags(substr($row['title'], 0, 100));
    }
}

function case_study_img2($token, $limit)
{
    global $db;
    $query = "select * from casestudy_images where casestudy_id = '$token' limit $limit,1";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_array($result);
    $path = 'acms/' . $row['image_url'];
    if (file_exists($path)) {
        return '<img src="' . $path . '" class="img-fluid">';
    } else {
        return 'rfwer';
    }
}

function blog_date($date)
{
    $date = date('F jS, Y', strtotime($date));
    return $date;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($to, $name, $fromName, $subject, $message, $file)
{
    // Mail Template
    $mailcontent = '<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title></title>
          <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:500,700,400,300" type="text/css">
          <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
    </head>
    
    <bodystyle="font-family: Calibri;">
    <div style="width:100%; background-color:#FFF; padding:20px;">
        <div style="width:100%; margin:auto; padding:10px; background:#FFFFFF;">
             <div style="clear:both"></div>
             
                 <div id="white_area" style="background-color:#FFFFFF; ">
                <div style="font-size:16px; color:#010E42; padding-top:10px;">
                
                <div>
                <div style="margin-bottom:15px;" id="username">
                <input type="image" src="images/log.png" style="width:150px;" />
                
                    <p>Hello ' . ucwords($name) . ',</p>
    
                </div>
                </div>
                
                <div style="font-size:16px;"> <p></p>' . $message . '
                  
    
                </p>
                </div>
                <br>
               </div>
                  </div><!-- White area ends here -->
        <div style="color:#FFF; margin-top:20px; margin-bottom:20px;">
            <div style="text-align:center; font-size:36px;"></div>
        </div>
    
        <div style="clear:both;"></div>
        
        <div id="copyright" style="font-size:13px; margin-top:5px;">Copyright &copy; - ' . date('Y') . '</div>
        <div style="clear:both;"></div>
        </div>
    </div>
    </body>
    </html>';

    // More headers
    //    $headers = "MIME-Version: 1.0" . "\r\n";
    //    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    //    // More headers
    //    $headers .= "From: $fromName <".sender_email().">";
    //mail($to, $subject, $mailcontent, $headers);


    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPDebug = 0; // For debugging purposes; set to 0 for production
    $mail->Port = 465;
    $mail->SMTPAuth = true;
    //sendgrid
    $mail->Username = 'contact@notionworks.com.ng';
    $mail->Password = 'notion@2024';
    $mail->Host = 'mail.notionworks.com.ng';
    $mail->SMTPSecure = 'ssl';
    $mail->setFrom(sender_email(), $fromName);
    $mail->AddAddress($to, $name);

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    if ($file) {
        $mail->addAttachment($file);
    }

    $mail->CharSet = 'UTF-8';
    $mail->IsHTML(true);
    $mail->Body    = $mailcontent;
    $mail->Subject = $subject;
    $mail->IsHTML(true);
    $mail->Send();

    return $mailcontent;
}
