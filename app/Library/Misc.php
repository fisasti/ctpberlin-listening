<?php
namespace App\Library;

class Misc {
    static function cleanDomain($domain) {
        $search = array('http://www.', 'https://www.', 'http://', 'https://', 'www.');

        return str_replace($search, '', $domain);
    }

    static function clean($string, $cleanHtml = false) {
        $string = (get_magic_quotes_gpc()) ? $string : addslashes($string);
        $string = trim($string);

        if ($cleanHtml)
                //$string = htmlentities(mb_convert_encoding($string, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8");
                $string = htmlentities($string, ENT_SUBSTITUTE, "UTF-8");
        /*else
                $string = Misc::getHtmlEntities($string);*/

        return $string;
    }

    static function getHtmlEntities($string) {
        /*for ($i=0;$i<strlen($string);$i++)
                print ord($string[$i]) . " ";*/

        $string = stripslashes($string);
        $normalAccents = array(chr(225) => '&aacute;', chr(233) => '&eacute;', chr(237) => '&iacute;', chr(243) => '&oacute;', chr(250) => '&uacute;');
        $accents = array(chr(161) => '&aacute;', chr(169) => '&eacute;', chr(173) => '&iacute;', chr(179) => '&oacute;', chr(186) => '&uacute;');
        $longChars = array(chr(239) . chr(191) . chr(189) => '&aacute;', chr(195) . chr(177) => '&ntilde;', chr(195) . chr(145) => '&Ntilde;', 
                                 chr(194) . chr(191) => '&iquest;',
                                 chr(195) . chr(154) => '&Uquest;',
                                 chr(195) . chr(141) => 'I',
                                 chr(194) . chr(161) => '&iexcl;',
                                 chr(226) . chr(128) . chr(156) => '&quot;',
                                 chr(226) . chr(128) . chr(157) => '&quot;',
                                 chr(226) . chr(128) . chr(166) => '');
        $chars = array(chr(191) => '&iquest;', chr(241) => '&ntilde;', chr(209) => '&Ntilde;', chr(193) => '&Aacute;',
                         chr(201) => '&Eacute;',
                         chr(205) => '&Iacute;',
                         chr(211) => '&Oacute;',
                         chr(218) => '&Uacute;',
                         chr(161) => '&iexcl;',
                         chr(34) => '&quot;');

        foreach ($longChars as $letter => $replacement)
                $string = str_replace($letter, $replacement, $string);

        foreach ($accents as $letter => $replacement)
                $string = str_replace(chr(195) . $letter, $replacement, $string);

        foreach ($normalAccents as $letter => $replacement)
                $string = str_replace($letter, $replacement, $string);

        foreach ($chars as $letter => $replacement)
                $string = str_replace($letter, $replacement, $string);

        $string = addslashes($string);

        return $string;
    }

    static function hashPassword($password) {
        return md5(sha1($password));
    }

    static function isValidEmail($email){
        return preg_match("|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$|i", $email);
    }
    
    static function isValidUsername($username) {
        return (bool) preg_match('/^[a-z\d_]{6,15}$/i', $username);
    }
    
    static function redirect($url, $js = true) {
        if ($js)
            print "<script type='text/javascript'>document.location.href='" . $url . "';</script>";
        else
            header("Location: " . $url);
        
        die();
    }

    static function leftZero($n) {
        return intval($n) < 10 ? '0' . $n : $n;
    }

    static function numberToLetters($n, $masc = true) {
        global $lang;

        $m = array('1' => 'primer', '2' => 'segundo', '3' => 'tercer', '4' => 'cuarto', 5 => 'quinto', 6 => 'sexto', 7 => 's&eacute;ptimo', 8 => 'octavo', 9 => 'noveno', 10 => 'd&eacute;cimo', 11 => 'und&eacute;cimo', 12 => 'duod&eacute;cimo', 13 => 'decimocuarto', 14 => 'decimoquinto', 15 => 'decimoquinto', 16 => 'decimosexto', 17 => 'decimos&eacute;ptimo', 18 => 'decimooctavo', 19 => 'decimonoveno', 20 => 'vig&eacute;simo', 21 => 'vig&eacute;simo primer', 22 => 'vig&eacute;simo segundo', 23 => 'vig&eacute;simo tercero', 24 => 'vig&eacute;simo cuarto');
        $f = array(1 => 'primera', 2 => 'segunda', 3 => 'tercera', 4 => 'cuarta', 5 => 'quinta', 6 => 'sexta', 7 => 's&eacute;ptima', 8 => 'octava', 9 => 'novena', 10 => 'd&eacute;cima', 11 => 'decimoprimera', 12 => 'decimosegunda', 13 => 'decimotercera');
        $e = array('1' => 'first', '2' => 'secondth', '3' => 'third', '4' => 'fourth', '5' => 'fifth', '6' => 'sixth', '7' => 'seventh', '8' => 'eighth', '9' => 'ninth', '10' => 'tenth', '11' => 'eleventh', '12' => 'twelfth', '13' => 'thirteenth', '14' => 'fourteenth', '15' => 'fifteenth', '16' => 'sixteenth', '17' => 'seventeenth', '18' => 'eighteenth', '19' => 'nineteenth', '20' => 'twentieth', '21' => 'twenty first', '22' => 'twenty secondth', '23' => 'twenty third', '24' => 'twenty fourth', '25' => 'twenty fifth');

        return $lang == 'en' ? $e[$n] : ($masc ? $m[$n] : $f[$n]);
    }

    static function dayToLetters($day, $mode=0) {
        $days = array(1 => 'domingo', 2 => 'lunes', 3 => 'martes', 4 => 'mi&eacute;rcoles', 5 => 'jueves', 6 => 'viernes', 7 => 's&aacute;bado');

        if ($mode == 1)
                $day = $day == 7 ? 1 : $day + 1;

        if ($day < 1 || $day > 7)
                return false;

        return $days[$day];
    }

    function getHourOptions($hour = -1, $minutes = -1) {
        for ($i=0;$i<24;$i++)
            for ($j=0;$j<60;$j+=15) {
                if ($i == 0 && $j == 0)
                    $j = 15;

                $selected = $i == $hour && $j == $minutes ? ' selected' : '';

                $hours_options .= '<option value="' . Misc::leftZero($i) . ':' . Misc::leftZero($j) . '"' . $selected . '>' . Misc::leftZero($i) . ':' . Misc::leftZero($j) . '</option>';
            }
            
        return $hours_options;
    }
    
    static function timeToLetters($time, $time2 = -1, $lang = 'es') {
        $l = array('es' => array('month' => 'mes', 'day' => 'd&iacute;a', 'hour' => 'hora', 'minute' => 'minuto', 'second' => 'segundo'),
                    'en' => array('month' => 'month', 'day' => 'day', 'hour' => 'hour', 'minute' => 'minute', 'second' => 'second'));

        $l = $lang == 'es' ? $l['es'] : $l['en'];

        if ($time2 == -1)
                $time2 = time();

        $diff = $time2 - $time;
        $ret = '';

        if ($diff < 0)
                $diff *= -1;

        $days = floor($diff / (60*60*24));
        $diff %= 60*60*24;

        if ($days != 0) {
                $plu = $days > 1 ? 's' : '';

                return $days . " " . $l['day'] . $plu;
        }

        $hours = floor($diff / (60 * 60));
        $diff %= (60 * 60);

        if ($hours != 0) {
                $plu = $hours > 1 ? 's' : '';
                return $hours . " " . $l['hour'] . $plu;
        }

        $mins = floor($diff/60);
        $diff %= 60;

        if ($mins != 0) {
                $plu = $mins > 1 ? 's' : '';
                return $mins . ", " . $l['minute'] . $plu;
        }

        if ($diff != 0) {
                $plu = $diff > 1 ? 's' : '';
                return $diff . " " . $l['second'] . "$plu";
        }
    }

    static function isJson($string) {
        if (!is_string($string))
            return false;
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
     }

    static function httpRequest($url, $data=false, $cookie = false, $header = true, $file = '', $headers = array()) {
        global $cookiefile;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);

        if ($cookie) {
            if (!file_exists($cookiefile))
                    fopen($cookiefile, "w+");
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
        }
        if (strlen($file) > 0) {
            if (function_exists('curl_file_create'))
                $cFile = curl_file_create($file);
            else
                $cFile = '@' . realpath($file);

            if (!is_array($data))
                $data = array();
            
            $data['file'] = $cFile;
        }
        if ($data) {
            if (Misc::isJson($data)) {
                $headers[] = 'Content-Type:application/json';
            }
            curl_setopt($ch, CURLOPT_POST, 2);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            // var_dump($data);
        }

        if (substr($url, 0, 5) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        if (count($headers) > 0)
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);

        $header = curl_getinfo($ch);
        $header_content = substr($response, 0, $header['header_size']);
        $response = substr($response, $header['header_size']);
        /*$pattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m"; 
        preg_match_all($pattern, $header_content, $matches); 
        $cookie = implode("; ", $matches['cookie']);*/

        return array('response' => $response, 'cookie' => $cookie, 'header' => $header, 'header_content' => $header_content);
    }

    static function getVideoDuration($file) {
        $ffmpeg = '/usr/local/bin/ffmpeg';
        exec("$ffmpeg -i \"" . $file . "\" 2>&1", $output);
        $expr = '|Duration\: ([^\,]+)|';

        foreach ($output as $line)
                if (strstr($line, "Duration: ")) {
                        preg_match_all($expr, $line, $matches);
                        $duration = $matches[1][0];
                        $duration = substr($duration, 0, 2) * 3600 + substr($duration, 3, 2) * 60 + substr($duration, 6, 2);
                        return $duration;
                }
    }

    static function getVideoInfo($file) {
        $ffmpeg = '/usr/local/bin/ffmpeg';
        exec("$ffmpeg -i \"" . $file . "\" 2>&1", $output);
        $expr = '|Duration\: ([^\,]+)|';
        //$expr_b = '|, ([0-9]+) kb/s|';
        $expr_b = '|bitrate: ([0-9]+)|';
        $ret = array('bitrate' => false, 'duration' => false);

        foreach ($output as $line)
                if (strstr($line, "Duration: ")) {
                        preg_match_all($expr, $line, $matches);
                        preg_match_all($expr_b, $line, $matches_b);
                        $duration = $matches[1][0];
                        $duration = substr($duration, 0, 2) * 3600 + substr($duration, 3, 2) * 60 + substr($duration, 6, 2);
                        $bitrate = $matches_b[1][0];
                        $ret['duration'] = $duration;
                        $ret['bitrate'] = $bitrate;
                }
                /*elseif (strstr($line, 'Stream #0') && preg_match_all($expr_b, $line,$matches)) {
                        $ret['bitrate'] = $matches[1][0];
                }*/

        return $ret;
    }

    static function isFileBeingWritten($file) {
        $cmd = "lsof -f -- $file";
        exec($cmd, $output);
/*		print $cmd . "<br />";
        var_dump($output);*/
        foreach ($output as $line) {		
                if (preg_match_all('/ ([0-9]+)(u|w) /', $line, $matches)) {
                        return true;
                }
        }
        //print "<br />";
        return false;
    }

    static function schemaPublisher($data) {
            return '<div itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
<div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
    <meta itemprop="url" content="' . $data['image_url'] . '">
    <meta itemprop="width" content="' . $data['image_width'] . '">
    <meta itemprop="height" content="' . $data['image_height'] . '">
</div>
<meta itemprop="name" content="' . $data['name'] . '">
</div>';
            }

    static function schemaImage($data) {
            return '<div itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
<meta itemprop="url" content="' . $data['image_url'] . '">
<meta itemprop="width" content="' . $data['image_width'] . '">
    <meta itemprop="height" content="' . $data['image_height'] . '">
</div>';
    }

    static function schemaRating($data) {
            return '<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"><meta itemprop="bestRating" content="5"/><meta itemprop="worstRating" content="1"/><meta itemprop="ratingValue" content="' . $data['ratingValue'] . '"/><meta itemprop="ratingCount" content="' . $data['ratingCount'] .'"/>
    </div>';
    }
    static function caesarCipher($str, $offset=10) {
        $str = '';

        for ($i=0;$i<strlen($str);$i++)
                $ret .= chr(ord($str[$i])+$offset);

        return $str;
    }

    static function get_ip() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip=$_SERVER['HTTP_CLIENT_IP'];
        else $ip=$_SERVER['REMOTE_ADDR'];
        
        if (strstr($ip, ", ")) {
                $array=explode(", ", $ip);
                $ip=$array[0];
        }
        return Misc::clean($ip);
    }
    
    static function phpMailer($to, $from, $subject, $body, $altbody = '') {
        global $host;
        
        if (empty($from) || empty($to) || empty($subject) || empty($body))
            return "Por favor, completa todos los campos.";
        
        if ($host == 'localhost') {
            $response = Misc::httpRequest('http://alternativamusical.com.ar/scripts/phpmailer_tunnel.php', array('to' => $to, 'from' => $from, 'subject' => $subject, 'body' => $body, 'altbody' => $altbody));
            return $response['response'];
        }
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            //Server settings
        /*    $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'user@example.com';                 // SMTP username
            $mail->Password = 'secret';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('from@example.com', 'Mailer');
            $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
            $mail->addAddress('ellen@example.com');               // Name is optional
            $mail->addReplyTo('info@example.com', 'Information');
            $mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');

            //Attachments
            $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name*/

            //Content
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->IsSMTP();
            $mail->Host = "alternativamusical.com.ar";
            $mail->SMTPAuth = true;
            $mail->Username = 'registro@alternativamusical.com.ar';
            $mail->Password = 'amus1c4lr3g';
            $mail->setFrom($from);
            $mail->addAddress($to, 'Joe User'); 
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $altbody;

            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }
    
    static function logError($error, $error_details = '', $user_id = 0) {
        global $db;
        $user_id = intval($user_id);
        // $error y $error_details NO ESTAN SIENDO FILTRADA
        if (!$db)
            return false;
       
        return $db->insert('errors', array('user_id' => $user_id, 'ip' => Misc::get_ip(), 'date' => date("Y-m-d H:i:s"), 'error' => $error, 'error_details' => $error_details));
    }

    static function escapeDbTableName($table) {
        if (strstr($table, ',')) {
            $data = explode(',', $table);
            foreach ($data as $k => $v) {
                    $d = explode(' ', $v);

                    if (count($d) == 2) {
                            $data[$k] = "`" . $d[0] . "` " . $d[1];
                    }
            }
            $table = implode(',', $data);
        }
        else
            $table = '`' . $table . '`';

        return $table;
    }
}