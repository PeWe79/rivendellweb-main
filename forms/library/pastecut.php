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
$username = $_COOKIE['username'];
$password = $functions->loadPass($username);
$fullname = $_COOKIE['fullname'];
$_RDWEB_API = $_COOKIE['rdWebAPI'];
$cutname = $_POST["cutname"];
$tocut = $_POST["tocut"];
$autotrimlevel = $_POST["autotrimlevel"];
$autotrimactive = $_POST["autotrimactive"];
$filepath = '/var/snd/' . $cutname . '.wav';
$cartno = substr($tocut, 0, strpos($tocut, "_"));
$cutid = substr($tocut, strpos($tocut, "_") + 1);
$startpoint = 0;
$targetPath = '/tmp/';
if ($autotrimactive == 1) {
    $autolevel = $autotrimlevel;
} else {
    $autolevel = 0;
}

if (file_exists($filepath)) {
    $ch = curl_init();
    $parameters = array(
        'COMMAND' => '2',
        'LOGIN_NAME' => $username,
        'PASSWORD' => $password,
        'CART_NUMBER' => $cartno,
        'CUT_NUMBER' => $cutid,
        'CHANNELS' => $copy_data['CUTS'][$_POST["cutname"]]['CHANNELS'],
        'NORMALIZATION_LEVEL' => 0,
        'USE_METADATA' => '0',
        'AUTOTRIM_LEVEL' => $autolevel,
    );
    $files = array('FILENAME' => $filepath);
    $postfields = $functions->curl_custom_postfields($ch, $parameters, $files);
    curl_setopt($ch, CURLOPT_URL, $_RDWEB_API);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    if (preg_match('/ResponseCode>200</', $result, $matches)) {

        if (!$functions->rd_edit_marker($cartno, $cutid, $copy_data['CUTS'][$_POST["cutname"]]['START_POINT'], $copy_data['CUTS'][$_POST["cutname"]]['END_POINT'], $copy_data['CUTS'][$_POST["cutname"]]['TALK_START_POINT'], $copy_data['CUTS'][$_POST["cutname"]]['TALK_END_POINT'], $copy_data['CUTS'][$_POST["cutname"]]['FADEUP_POINT'], $copy_data['CUTS'][$_POST["cutname"]]['FADEDOWN_POINT'], $copy_data['CUTS'][$_POST["cutname"]]['SEGUE_START_POINT'], $copy_data['CUTS'][$_POST["cutname"]]['SEGUE_END_POINT'], $copy_data['CUTS'][$_POST["cutname"]]['HOOK_START_POINT'], $copy_data['CUTS'][$_POST["cutname"]]['HOOK_END_POINT'])) {
            $echodata = ['error' => 'true', 'errorcode' => '1'];
            echo json_encode($echodata);
        } else {
            unset($copy_data['CUTS'][$_POST["cutname"]]);
            $jsonData = json_encode($copy_data, JSON_PRETTY_PRINT);
            if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/copy.json', $jsonData)) {
                $echodata = ['error' => 'true', 'errorcode' => '1'];
                echo json_encode($echodata);
            } else {
                $echodata = ['error' => 'false', 'errorcode' => '0'];
                echo json_encode($echodata);
            }
        }
    } else {
        $echodata = ['error' => 'true', 'errorcode' => '1'];
        echo json_encode($echodata);
    }
}