<?php
/*********************************************************************************************************
 *                                        RIVENDELL WEB BROADCAST                                        *
 *    A WEB SYSTEM TO USE WITH RIVENDELL RADIO AUTOMATION: HTTPS://GITHUB.COM/ELVISHARTISAN/RIVENDELL    *
 *              THIS SYSTEM IS NOT CREATED BY THE DEVELOPER OF RIVENDELL RADIO AUTOMATION.               *
 * IT'S CREATED AS AN HELP TOOL ONLINE BY ANDREAS OLSSON AFTER HE FIXED BUGS IN AN OLD SCRIPT CREATED BY *
 *             BRIAN P. MCGLYNN : HTTPS://GITHUB.COM/BPM1992/RIVENDELL/TREE/RDWEB/WEB/RDPHP              *
 *        USE THIS SYSTEM AT YOUR OWN RISK. IT DO DIRECT MODIFICATION ON THE RIVENDELL DATABASE.         *
 *                 YOU CAN NOT HOLD US RESPONISBLE IF SOMETHING HAPPENDS TO YOUR SYSTEM.                 *
 *                   THE DESIGN IS DEVELOP BY SAUGI: HTTPS://GITHUB.COM/ZURAMAI/MAZER                    *
 *                                              MIT LICENSE                                              *
 *                                   COPYRIGHT (C) 2024 ANDREAS OLSSON                                   *
 *             PERMISSION IS HEREBY GRANTED, FREE OF CHARGE, TO ANY PERSON OBTAINING A COPY              *
 *             OF THIS SOFTWARE AND ASSOCIATED DOCUMENTATION FILES (THE "SOFTWARE"), TO DEAL             *
 *             IN THE SOFTWARE WITHOUT RESTRICTION, INCLUDING WITHOUT LIMITATION THE RIGHTS              *
 *               TO USE, COPY, MODIFY, MERGE, PUBLISH, DISTRIBUTE, SUBLICENSE, AND/OR SELL               *
 *                 COPIES OF THE SOFTWARE, AND TO PERMIT PERSONS TO WHOM THE SOFTWARE IS                 *
 *                       FURNISHED TO DO SO, SUBJECT TO THE FOLLOWING CONDITIONS:                        *
 *            THE ABOVE COPYRIGHT NOTICE AND THIS PERMISSION NOTICE SHALL BE INCLUDED IN ALL             *
 *                            COPIES OR SUBSTANTIAL PORTIONS OF THE SOFTWARE.                            *
 *              THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR               *
 *               IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,                *
 *              FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE              *
 *                AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER                 *
 *             LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,             *
 *             OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE             *
 *                                               SOFTWARE.                                               *
 *********************************************************************************************************/
require $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
$username = $_POST['username'];
$password = $_POST['password'];
$remember = $_POST['remember'];

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/mail/src/SMTP.php';

if (!$remember == 1) {
    $remember = 0;
}

if ($json_sett['usrsett'][$username]["twofactor"]["enable"] == 1) {
    if ($user->checkPassword($password, $username)) {
        $logincode = $user->randomGenerator(5);
        $time = strtotime('+5 minutes');
        $expire = date('Y-m-d H:i:s', $time);
        $email = $user->getUserEmail($username);
        $fullname = $user->getUserFullName($email);
        setcookie('twofactusr', $username, time() + 300, '/');
        $json_sett["usrsett"][$username]["twofactor"]["code"] = $logincode;
        $json_sett["usrsett"][$username]["twofactor"]["expire"] = $expire;
        $json_sett["usrsett"][$username]["twofactor"]["remember"] = $remember;
        $json_sett["usrsett"][$username]["twofactor"]["used"] = 0;
        $jsonsettings = json_encode($json_sett, JSON_UNESCAPED_SLASHES);
        if (file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/settings.json', $jsonsettings)) {
            $message = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/includes/mailtemp/twofactor-code.html');
            $message = str_replace('%imglogo%', DIR . '/assets/static/images/rivlogo/rdairplay-128x128.png', $message);
            $message = str_replace('%logincode%', $ml->tr('YOURLOGINCODE'), $message);
            $message = str_replace('%hello%', $ml->tr('HELLONAME {{' . $fullname . '}}'), $message);
            $message = str_replace('%loginwithcode%', $ml->tr('YOURLOGINCODEINFO'), $message);
            $message = str_replace('%newcode%', $ml->tr('YOURLOGINCODEIS {{' . $logincode . '}}'), $message);
            $message = str_replace('%enterthecode%', $ml->tr('USETHECODETOLOGIN'), $message);
            $message = str_replace('%footernote%', $ml->tr('SENTFROM {{' . APPNAME . '}}'), $message);
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTPSERV;
            $mail->Port = SMTPPORT;
            if ($json_sett["smtplogin"] == 1) {
                $mail->SMTPAuth = true;
                $mail->Username = SMTPUSER;
                $mail->Password = SMTPPASS;
                if ($json_sett["smtpenc"] == 1) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
            }
            $mail->setFrom(SMTPFROM, APPNAME);
            $mail->addAddress($email, $fullname);
            $mail->Subject = $ml->tr('YOURLOGINCODE');
            $mail->CharSet = 'utf-8';
            $mail->IsHTML(true);
            $mail->msgHTML($message);
            if (!$mail->send()) {
                $echodata = ['error' => 'true', 'errorcode' => '1', 'twofactor' => '1'];
                echo json_encode($echodata);
            } else {
                $echodata = ['error' => 'false', 'errorcode' => '0', 'twofactor' => '1'];
                echo json_encode($echodata);
            }

        } else {
            $echodata = ['error' => 'true', 'errorcode' => '1', 'twofactor' => '1'];
            echo json_encode($echodata);
        }

    } else {
        $echodata = ['error' => 'true', 'errorcode' => '1', 'twofactor' => '1'];
        echo json_encode($echodata);
    }


} else {
    if ($user->isValidUsername($username)) {
        if ($user->login($username, $password, $remember)) {
            $echodata = ['error' => 'false', 'errorcode' => '0', 'twofactor' => '0'];
            echo json_encode($echodata);
        } else {
            $echodata = ['error' => 'true', 'errorcode' => '1', 'twofactor' => '0'];
            echo json_encode($echodata);
        }
    } else {
        $echodata = ['error' => 'true', 'errorcode' => '1', 'twofactor' => '0'];
        echo json_encode($echodata);
    }
}
