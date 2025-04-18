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

$json_sett["closedown"] = 1;

$jsonsettings = json_encode($json_sett, JSON_UNESCAPED_SLASHES);

if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/settings.json', $jsonsettings)) {
    $errormain = 1;
} else {
    $errormain = 0;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $ml->tr('UPDATETITLE {{' . SYSTIT . '}}'); ?></title>
    <link rel="shortcut icon" href="<?php echo DIR ?>/AppImages/favicon.ico" />
    <link rel="stylesheet" href="<?php echo DIR ?>/assets/compiled/css/app.css">
    <link rel="stylesheet" href="<?php echo DIR ?>/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="<?php echo DIR ?>/assets/extensions/choices.js/public/assets/styles/choices.css">
    <link rel="stylesheet" href="<?php echo DIR ?>/assets/extensions/sweetalert2/sweetalert2.min.css">
    <link href="css/site.css" rel="stylesheet" />
</head>

<body>
    <script src="<?php echo DIR ?>/assets/static/js/initTheme.js"></script>
    <nav class="navbar navbar-light">
        <div class="container d-block">
            <a class="navbar-brand ms-4" href="javascript:;">
                <img src="<?php echo DIR ?>/assets/static/images/rivlogo/rdairplay-128x128.png">
            </a>
        </div>
    </nav>
    <div class="container">
        <div class="card mt-5">
            <div class="card-header">
                <h4 class="card-title"><?= $ml->tr('UPDATETITLE {{' . SYSTIT . '}}'); ?></h4>
            </div>
            <div class="card-body">
                <P><?= $ml->tr('UPDATEWELCOME'); ?></P>
                <div class="alert alert-light-danger color-danger"><?= $ml->tr('UPDATE_MAINTANCEMODEACTIVE'); ?></div>
                <div id="relNewsInfoShow" style="display: none;">
                    <h2><?= $ml->tr('UPDATE_RELEASENOTES'); ?></h2>
                    <div id="relNotesText"></div>
                </div>
                <div id="info">
                    <div> <span class="waiting"></span></div>
                </div>
                <P class="col-sm-12 d-flex justify-content-end"><a href="javascript:;" onclick="turnOffMain()" class="btn btn-info"><?= $ml->tr('DONE'); ?></a></P>

            </div>
        </div>

    </div>
    <script src="<?php echo DIR ?>/assets/extensions/jquery/jquery.min.js"></script>
    <script src="<?php echo DIR ?>/assets/extensions/choices.js/public/assets/scripts/choices.js"></script>
    <script src="<?php echo DIR ?>/assets/extensions/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?php echo DIR ?>/assets/extensions/jqueryvalidation/jquery.validate.min.js"></script>
    <script src="<?php echo DIR ?>/assets/extensions/jqueryvalidation/additional-methods.min.js"></script>
    <script src="<?php echo DIR ?>/assets/compiled/js/app.js"></script>
    <SCRIPT>
        var HOST_URL = "<?= DIR ?>";
        var TRAN_UPDATE_CHECKFORSCRIPTS = "<?= $ml->tr('UPDATE_CHECKFORSCRIPTS'); ?>";
        var TRAN_UPDATE_CHECKVERFILEEXIST = "<?= $ml->tr('UPDATE_CHECKVERFILEEXIST'); ?>";
        var TRAN_UPDATE_VERSIONUPTODATE = "<?= $ml->tr('UPDATE_VERSIONUPTODATE'); ?>";
        var TRAN_UPDATE_VERSIONOUTOFDATE = "<?= $ml->tr('UPDATE_VERSIONOUTOFDATE'); ?>";
        var TRAN_UPDATE_UPDATENOW = "<?= $ml->tr('UPDATE_UPDATENOW'); ?>";
        var TRAN_UPDATE_CHECKWRITABLEFILES = "<?= $ml->tr('UPDATE_CHECKWRITABLEFILES'); ?>";
        var TRAN_UPDATE_FILESAREWRITABLE = "<?= $ml->tr('UPDATE_FILESAREWRITABLE'); ?>";
        var TRAN_UPDATE_FILESNOTWRITABLEFAILED = "<?= $ml->tr('UPDATE_FILESNOTWRITABLEFAILED'); ?>";
        var TRAN_UPDATE_THEUPDATEFAILED = "<?= $ml->tr('UPDATE_THEUPDATEFAILED'); ?>";
        var TRAN_UPDATE_THEUPDATEHASFINISHED = "<?= $ml->tr('UPDATE_THEUPDATEHASFINISHED'); ?>";
        var TRAN_UPDATE_DOWNLOADINSTALLFILES = "<?= $ml->tr('UPDATE_DOWNLOADINSTALLFILES'); ?>";
        var TRAN_UPDATE_FILESINSTALLED = "<?= $ml->tr('UPDATE_FILESINSTALLED'); ?>";
        var TRAN_UPDATE_UPDATEFILENOTFOUND = "<?= $ml->tr('UPDATE_UPDATEFILENOTFOUND'); ?>";
        var TRAN_UPDATE_UPDATEFILEEXISTS = "<?= $ml->tr('UPDATE_UPDATEFILEEXISTS'); ?>";
        var TRAN_UPDATE_UPDATINGVERSIONFILE = "<?= $ml->tr('UPDATE_UPDATINGVERSIONFILE'); ?>";
        var TRAN_UPDATE_VERSIONFILEUPDATED = "<?= $ml->tr('UPDATE_VERSIONFILEUPDATED'); ?>";
        var TRAN_UPDATE_SCRIPTEXIST = "<?= $ml->tr('UPDATE_SCRIPTEXIST'); ?>";
        var TRAN_UPDATE_NOSCRIPTTORUN = "<?= $ml->tr('UPDATE_NOSCRIPTTORUN'); ?>";
        var TRAN_UPDATE_EXECUTINSCRIPTS = "<?= $ml->tr('UPDATE_EXECUTINSCRIPTS'); ?>";
        var TRAN_UPDATE_SCRIPTFINISHED = "<?= $ml->tr('UPDATE_SCRIPTFINISHED'); ?>";
        var TRAN_UPDATE_STEPNOTFOUND = "<?= $ml->tr('UPDATE_STEPNOTFOUND'); ?>";
        var TRAN_UPDATE_CHECKINGREMOTEFILES = "<?= $ml->tr('UPDATE_CHECKINGREMOTEFILES'); ?>";
        var TRAN_UPDATE_REMOTEFILESEXIST = "<?= $ml->tr('UPDATE_REMOTEFILESEXIST'); ?>";
        var TRAN_UPDATE_ONEORMOREREMOTENOTFOUND = "<?= $ml->tr('UPDATE_ONEORMOREREMOTENOTFOUND'); ?>";
        var TRAN_UPDATE_BACKINGUPFILES = "<?= $ml->tr('UPDATE_BACKINGUPFILES'); ?>";
        var TRAN_UPDATE_BACKUPCREATED = "<?= $ml->tr('UPDATE_BACKUPCREATED'); ?>";
        var TRAN_UPDATE_BACKUPFAILED = "<?= $ml->tr('UPDATE_BACKUPFAILED'); ?>";
        var TRAN_UPDATE_CHECKINGFORBACKUPS = "<?= $ml->tr('UPDATE_CHECKINGFORBACKUPS'); ?>";
        var TRAN_UPDATE_BACKUPSWEREFOUND = "<?= $ml->tr('UPDATE_BACKUPSWEREFOUND'); ?>";
        var TRAN_UPDATE_BACKUPSNOTFOUND = "<?= $ml->tr('UPDATE_BACKUPSNOTFOUND'); ?>";
        var TRAN_UPDATE_WHATWOULDYOUDO = "<?= $ml->tr('UPDATE_WHATWOULDYOUDO'); ?>";
        var TRAN_UPDATE_CHECKFORUPDATES = "<?= $ml->tr('UPDATE_CHECKFORUPDATES'); ?>";
        var TRAN_UPDATE_RESTOREABACKUP = "<?= $ml->tr('UPDATE_RESTOREABACKUP'); ?>";
        var TRAN_UPDATE_FETCHBACKUPVERSIONS = "<?= $ml->tr('UPDATE_FETCHBACKUPVERSIONS'); ?>";
        var TRAN_UPDATE_WHICHVERSIONRESTORE = "<?= $ml->tr('UPDATE_WHICHVERSIONRESTORE'); ?>";
        var TRAN_UPDATE_RESTORING = "<?= $ml->tr('UPDATE_RESTORING'); ?>";
        var TRAN_UPDATE_RESTORED = "<?= $ml->tr('UPDATE_RESTORED'); ?>";
        var TRAN_UPDATE_ADDINGUNDOSCRIPT = "<?= $ml->tr('UPDATE_ADDINGUNDOSCRIPT'); ?>";
        var TRAN_UPDATE_UNDOISADDED = "<?= $ml->tr('UPDATE_UNDOISADDED'); ?>";
        var TRAN_UPDATE_FAILEDUNDOSCRIPTADD = "<?= $ml->tr('UPDATE_FAILEDUNDOSCRIPTADD'); ?>";
        var TRAN_UPDATE_CHECKTOSEUPDATERUP = "<?= $ml->tr('UPDATE_CHECKTOSEUPDATERUP'); ?>";
        var TRAN_UPDATE_CREATEAUXILIARYCONTR = "<?= $ml->tr('UPDATE_CREATEAUXILIARYCONTR'); ?>";
        var TRAN_UPDATE_AUXILIARYCREATED = "<?= $ml->tr('UPDATE_AUXILIARYCREATED'); ?>";
        var TRAN_UPDATE_DELETEAUXILIARY = "<?= $ml->tr('UPDATE_DELETEAUXILIARY'); ?>";
        var TRAN_UPDATE_AUXICONTDELETED = "<?= $ml->tr('UPDATE_AUXICONTDELETED'); ?>";
        var TRAN_UPDATE_COULDNOTDELAUXI = "<?= $ml->tr('UPDATE_COULDNOTDELAUXI'); ?>";
        var TRAN_UPDATE_FINDINGNEWERBACKUPS = "<?= $ml->tr('UPDATE_FINDINGNEWERBACKUPS'); ?>";
        var TRAN_UPDATE_TRYTOFINDFINISHBUTTON = "<?= $ml->tr('UPDATE_TRYTOFINDFINISHBUTTON'); ?>";
        var TRAN_UPDATE_FINISHED = "<?= $ml->tr('UPDATE_FINISHED'); ?>";
        var TRAN_UPDATE_NOFINISHBUTTONFOUND = "<?= $ml->tr('UPDATE_NOFINISHBUTTONFOUND'); ?>";
    </SCRIPT>
    <script src="<?php echo DIR ?>/update/js/site.js?time=<?php echo time(); ?>"></script>

</body>

</html>