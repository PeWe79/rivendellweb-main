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
function fileCreateWrite()
{
    $array_data = array();
    $extra = array(
        'sysname' => $_POST['sys_name'],
        'sysurl' => $_POST["urladd"],
        'deflang' => $_POST["def_lang"],
        'admin' => array(
            $_POST["admin_usr"] => array(
                'username' => $_POST["admin_usr"],
                'settings' => '1',
                'backups' => '1',
                'users' => '1',
                'message' => '1',
                'groups' => '1',
                'sched' => '1',
                'services' => '1',
                'hosts' => '1',
            )
        ),
        'timezone' => $_POST["time_zone"],
        'smtpserv' => $_POST["smtp_server"],
        'port' => $_POST["smtp_port"],
        'smtplogin' => $_POST["smtp_login"],
        'smtpenc' => $_POST["smtp_enc"],
        'smtpusr' => $_POST["smtp_usr"],
        'smtppass' => $_POST["smtp_pass"],
        'smtpfrom' => $_POST["smtp_from"],
        'newsmess' => '',
        'usereset' => $_POST["pass_reset"],
        'autotrim' => $_POST["autotrim"],
        'normalize' => $_POST["normalize"],
        'backups' => array(
            'autotype' => $_POST['back_type'],
            'olderthan' => $_POST['back_older'],
        ),
        'jsonID' => 'AxZQ9f3fEUkLz25131',

    );
    $array_data[] = $extra;
    $final_data = json_encode($extra, JSON_UNESCAPED_SLASHES);
    return $final_data;
}


$final_data = fileCreateWrite();
if (file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/settings.json', $final_data)) {
    $echodata = ['error' => 'false', 'errorcode' => '0'];
    echo json_encode($echodata);
} else {
    $echodata = ['error' => 'true', 'errorcode' => '1'];
    echo json_encode($echodata);
}