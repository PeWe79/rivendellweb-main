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
$username = $_COOKIE['twofactusr'];
$code = $_POST['code'];

if (!isset($_COOKIE['twofactusr'])) {
    $echodata = ['error' => 'true', 'errorcode' => '1'];
    echo json_encode($echodata);
    exit();
} else {
    if ($json_sett['usrsett'][$username]["twofactor"]["enable"] == 1) {
        if (isset($json_sett['usrsett'][$username]["twofactor"]["expire"])) {
            $datetime = $json_sett['usrsett'][$username]["twofactor"]["expire"];
            $now = date('Y-m-d H:i:s');
            if (strtotime($datetime) < strtotime($now)) {
                $echodata = ['error' => 'true', 'errorcode' => '1'];
                echo json_encode($echodata);
                exit();
            } else {
                $rd_password = $functions->loadPass($username);
                $remember = $json_sett['usrsett'][$username]["twofactor"]["remember"];
                $rightcode = $json_sett['usrsett'][$username]["twofactor"]["code"];
                $isused = $json_sett['usrsett'][$username]["twofactor"]["used"];
                if ($code == $rightcode && $isused == 0) {
                    if ($user->isValidUsername($username)) {
                        if ($user->logintwo($username, $rd_password, $remember)) {
                            $json_sett['usrsett'][$username]["twofactor"]["used"] = 1;
                            $jsonsettings = json_encode($json_sett, JSON_UNESCAPED_SLASHES);
                            if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/settings.json', $jsonsettings)) {
                                $echodata = ['error' => 'true', 'errorcode' => '1'];
                                echo json_encode($echodata);
                            } else {
                                $echodata = ['error' => 'false', 'errorcode' => '0'];
                                echo json_encode($echodata);
                            }
                        } else {
                            $echodata = ['error' => 'true', 'errorcode' => '1'];
                            echo json_encode($echodata);
                        }
                    } else {
                        $echodata = ['error' => 'true', 'errorcode' => '1'];
                        echo json_encode($echodata);
                    }
                } else {
                    $echodata = ['error' => 'true', 'errorcode' => '1'];
                    echo json_encode($echodata);
                }

            }
        }

    } else {
        $echodata = ['error' => 'true', 'errorcode' => '1'];
        echo json_encode($echodata);
    }
}

