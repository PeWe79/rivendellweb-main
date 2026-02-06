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
if (!$user->is_logged_in()) {
    header('Location: ' . DIR . '/touch/login');
    exit();
}
$username = $_COOKIE['username'];
$expire = time() + (30 * 24 * 60 * 60);
if ($touch->isloggedInPC($username) > 0) {
    $isloggedin = 1;
} else {
    $isloggedin = 0;
}

if ($isloggedin == 1) {
    if (!isset($_COOKIE['t_station'])) {
        $stmt = $db->prepare("SELECT * FROM STATIONS WHERE USER_NAME=? LIMIT 1");
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $pclog = $row['NAME'];
        setcookie('t_station', $row['NAME'], $expire, '/');
    } else {
        $pclog = $_COOKIE['t_station'];
    }
}

if (isset($_COOKIE['t_panel'])) {
    $panelno = $_COOKIE['t_panel'];
    $strtype = substr($panelno, 0, 1);
    if ($strtype == 'S') {
        $paneltype = 0;
        $panelno = substr($panelno, 1);
    } else {
        $paneltype = 1;
        $panelno = substr($panelno, 1);
    }
} else {
    $panelno = "S0";
    setcookie('t_panel', $panelno, $expire, '/');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $ml->tr('SNDPANEL'); ?></title>
    <meta name="apple-mobile-web-app-status-bar" content="#aa7700">
    <meta name="theme-color" content="black">
    <link rel="manifest" href="manifest.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:locale"
        content="<?php if (isset($_COOKIE['lang'])) {
            echo $_COOKIE['lang'];
        } else {
            echo DEFAULTLANG;
        } ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?php echo SYSTIT; ?>" />
    <meta property="og:url" content="<?php echo DIR; ?>" />
    <meta property="og:site_name" content="<?php echo SYSTIT; ?>" />
    <meta name="apple-mobile-web-app-title" content="RivWebPanel" />
    <link rel="canonical" href="<?php echo DIR; ?>" />
    <link rel="shortcut icon" href="<?php echo DIR; ?>/touch/assets/favicon.ico" />
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
        <?php if ($isloggedin == 0) { ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4 class="card-title"><?= $ml->tr('SNDPANEL'); ?></h4>

                </div>
                <div class="card-body">
                    <P><?= $ml->tr('SNDLOGINRIVENDELLHOST'); ?></P>
                </div>
            </div>
        <?php } else { ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4 class="card-title"><?= $ml->tr('SNDPANEL'); ?></h4>
                    <div data-kt-library-table-toolbar="base">
                        <?php if ($touch->isloggedInPC($username) > 1) { ?>
                            <select id="selStation" class="form-select">
                                <?php $sql2 = 'SELECT * FROM `STATIONS` WHERE `USER_NAME` = :owner';

                                $stmt2 = $db->prepare($sql2);
                                $stmt2->bindParam(':owner', $username);
                                $stmt2->setFetchMode(PDO::FETCH_ASSOC);
                                $stmt2->execute();
                                $result = $stmt2->fetchAll();
                                foreach ($result as $row1) { ?>
                                    <option value="<?php echo $row1['NAME']; ?>">
                                        <?php echo $row1['DESCRIPTION']; ?>
                                    </option>
                                <?php } ?>

                            </select>
                        <?php } ?>
                        <select id="selPanels" onchange="changePanel(this);" class="form-select">
                            <?php $sql = 'SELECT DISTINCT PANEL_NO FROM `PANELS` WHERE `OWNER` = :owner ORDER BY PANEL_NO ASC';

                            $stmt = $db->prepare($sql);
                            $stmt->bindParam(':owner', $username);
                            $stmt->setFetchMode(PDO::FETCH_ASSOC);
                            $stmt->execute();

                            $result = $stmt->fetchAll();
                            foreach ($result as $row1) { ?>
                                <option value="<?php echo "U" . $row1['PANEL_NO']; ?>" <?php if ($_COOKIE['t_panel'] == "U" . $row1['PANEL_NO']) {
                                         echo "SELECTED";
                                     } ?>>
                                    <?php echo "U:" . $row1['PANEL_NO'] + 1; ?>
                                </option>
                            <?php } ?>
                            <?php $sql = 'SELECT DISTINCT PANEL_NO FROM `PANELS` WHERE `OWNER` = :owner ORDER BY PANEL_NO ASC';

                            $stmt = $db->prepare($sql);
                            $stmt->bindParam(':owner', $pclog);
                            $stmt->setFetchMode(PDO::FETCH_ASSOC);
                            $stmt->execute();

                            $result = $stmt->fetchAll();
                            foreach ($result as $row1) { ?>
                                <option value="<?php echo "S" . $row1['PANEL_NO']; ?>" <?php if ($_COOKIE['t_panel'] == "S" . $row1['PANEL_NO']) {
                                         echo "SELECTED";
                                     } ?>>
                                    <?php echo "S:" . $row1['PANEL_NO'] + 1; ?>
                                </option>
                            <?php } ?>

                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <?php
                                    if ($paneltype == 0) {
                                        $owner = $pclog;
                                    } else {
                                        $owner = $username;
                                    }
                                    $pancolcont = 0;
                                    $totpanels = $touch->countPanels($owner, $panelno);
                                    $maxpanelcol = $touch->maxColums($owner, $panelno);
                                    $maxrowspan = $touch->maxRows($owner, $panelno);
                                    $pancol = $touch->getPanelsButton($owner, $panelno);
                                    foreach ($pancol as $colrow) { ?>

                                        <?php if ($pancolcont <= $maxpanelcol) { ?>
                                            <th>

                                            </th>
                                        <?php }
                                        $pancolcont++;
                                    } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $thepanels = $touch->getPanelsButtonRowUnique($owner, $panelno);
                                $panelcontplace = 1;
                                $panelcontplace1 = 1;
                                $panelcontrow = 1;
                                $lastrowcont = 0;
                                $runsNo = -1;
                                $sql = 'SELECT DISTINCT ROW_NO FROM `PANELS` WHERE `OWNER` = :owner AND `PANEL_NO` = :panel  ORDER BY ROW_NO ASC';
                                $stmt = $db->prepare($sql);
                                $stmt->bindParam(':owner', $owner);
                                $stmt->bindParam(':panel', $panelno);
                                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                $stmt->execute();

                                while ($row = $stmt->fetch()) {
                                    $thisrowpanels = $touch->getPanelsButtonRow($owner, $panelno, $row['ROW_NO']);
                                    $totalthisrow = $touch->countPanelsRow($owner, $panelno, $row['ROW_NO']);
                                    echo "<tr>";
                                    $sql2 = 'SELECT * FROM `PANELS` WHERE `OWNER` = :owner AND `PANEL_NO` = :panel AND `ROW_NO` = :rowsp  ORDER BY COLUMN_NO ASC';

                                    $stmt2 = $db->prepare($sql2);
                                    $stmt2->bindParam(':owner', $owner);
                                    $stmt2->bindParam(':panel', $panelno);
                                    $stmt2->bindParam(':rowsp', $row['ROW_NO']);
                                    $stmt2->setFetchMode(PDO::FETCH_ASSOC);
                                    $stmt2->execute();
                                    $result = $stmt2->fetchAll();
                                    foreach ($result as $row1) {
                                        if ($row1['TYPE'] == 0) {
                                            $panletter = "S" . $row1['PANEL_NO'];
                                        } else {
                                            $panletter = "U" . $row1['PANEL_NO'];
                                        }
                                        ?>
                                        <td><a href="javascript:;" style="background: <?php echo $row1['DEFAULT_COLOR']; ?>;"
                                                id="panelbutt_<?php echo $row1['ID']; ?>" class="btn btn-lg btn-light"
                                                onclick="runPanel('<?php echo $panletter; ?>', '<?php echo $pclog; ?>', <?php echo $row1['ROW_NO']; ?>, <?php echo $row1['COLUMN_NO']; ?>)"><span
                                                    class="fs-6 font-bold">
                                                    <?php echo $row1['LABEL']; ?>
                                                </span></a>

                                        </td>
                                    <?php }
                                    echo "</tr>";

                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><?php } ?>

    </div>
    <script src="<?php echo DIR ?>/assets/extensions/jquery/jquery.min.js"></script>
    <script src="<?php echo DIR ?>/assets/extensions/choices.js/public/assets/scripts/choices.js"></script>
    <script src="<?php echo DIR ?>/assets/extensions/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?php echo DIR ?>/assets/extensions/jqueryvalidation/jquery.validate.min.js"></script>
    <script src="<?php echo DIR ?>/assets/extensions/jqueryvalidation/additional-methods.min.js"></script>
    <script src="<?php echo DIR ?>/assets/compiled/js/app.js"></script>
    <SCRIPT>
        var HOST_URL = "<?= DIR ?>";
        var TRAN_SNDLOGINRIVENDELLHOST = "<?= $ml->tr('SNDLOGINRIVENDELLHOST'); ?>";
        var TRAN_OK = "<?= $ml->tr('OK'); ?>";
    </SCRIPT>
    <script src="<?php echo DIR ?>/assets/static/js/touch.js?5467"></script>

</body>

</html>