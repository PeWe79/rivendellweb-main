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
$error = 0;
$json_sett["usrsett"][$username]['rdcatch'] = $_POST['handlerdcatch'];
if ($_POST['adminrights'] == 1) {
    $json_sett["admin"][$username]['username'] = $username;
    $json_sett["admin"][$username]['settings'] = $_POST['systemdata'];
    $json_sett["admin"][$username]['backups'] = $_POST['managebackups'];
    $json_sett["admin"][$username]['users'] = $_POST['manageuser'];
    $json_sett["admin"][$username]['message'] = $_POST['messages'];
    $json_sett["admin"][$username]['hosts'] = $_POST['hosts'];
    $json_sett["admin"][$username]['groups'] = $_POST['modifygroups'];
    $json_sett["admin"][$username]['sched'] = $_POST['modifysched'];
    $json_sett["admin"][$username]['services'] = $_POST['modifyservices'];

    $jsonsettings = json_encode($json_sett, JSON_UNESCAPED_SLASHES);

    if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/settings.json', $jsonsettings)) {
        $error = 1;
    }
} else if (isset($json_sett["admin"][$username])) {
    unset($json_sett["admin"][$username]);
    $jsonsettings = json_encode($json_sett, JSON_UNESCAPED_SLASHES);
    if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/settings.json', $jsonsettings)) {
        $error = 1;
    }
}

if ($error == 1) {
    $echodata = ['error' => 'true', 'errorcode' => '2'];
    echo json_encode($echodata);
} else {

    if (!$user->updateUserDataAdmin($username, $_POST['fullname'], $_POST['email'], $_POST['phone'], $_POST['description'])) {
        $echodata = ['error' => 'true', 'errorcode' => '1'];
        echo json_encode($echodata);
    } else {
        $echodata = ['error' => 'false', 'errorcode' => '0'];
        echo json_encode($echodata);
    }
}