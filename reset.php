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
$token = $_GET['token'];
if ($user->is_logged_in()) {
    header('Location: '.DIR.'/dash');
    exit();
}

if (USERESET == 0) {
    header('Location: '.DIR.'/login');
    exit();
}

if (!isset($token) && $token == "") {
    header('Location: '.DIR.'/login');
    exit();
}

if (!isset($reset_data[$token]['token']) && $reset_data[$token]['token'] == "") {
    header('Location: '.DIR.'/login');
    exit();
}
$checktime = strtotime($reset_data[$token]['added']);
if (time() - $checktime > 15 * 60) {
    unset($reset_data[$token]);
    $final_data = json_encode($reset_data, JSON_PRETTY_PRINT);
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/reset.json', $final_data);
    header('Location: '.DIR.'/login');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
    <meta name="apple-mobile-web-app-status-bar" content="#aa7700">
    <meta name="theme-color" content="black">
    <link rel="manifest" href="manifest.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:locale" content="<?php if (isset($_COOKIE['lang'])) { echo $_COOKIE['lang']; } else { echo DEFAULTLANG; } ?>" />
	<meta property="og:type" content="article" />
	<meta property="og:title" content="<?php echo SYSTIT; ?>" />
	<meta property="og:url" content="<?php echo DIR; ?>" />
	<meta property="og:site_name" content="<?php echo SYSTIT; ?>" />
	<link rel="canonical" href="<?php echo DIR; ?>" />
	<link rel="shortcut icon" href="<?php echo DIR; ?>/AppImages/favicon.ico" />
    <title>
        <?= $ml->tr('RESETYOURPASS'); ?>
    </title>
    <link rel="stylesheet" href="<?php echo DIR; ?>/assets/extensions/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="<?php echo DIR; ?>/assets/compiled/css/app.css">
    <link rel="stylesheet" href="<?php echo DIR; ?>/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="<?php echo DIR; ?>/assets/compiled/css/auth.css">
</head>

<body>
    <script src="<?php echo DIR; ?>/assets/static/js/initTheme.js"></script>
    <div id="auth">

        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <div class="auth-logo">
                        <a href="<?php echo DIR; ?>/login"><img src="<?php echo DIR; ?>/assets/static/images/rivlogo/rdairplay-128x128.png"
                                alt="Logo"></a>
                    </div>
                    <h1 class="auth-title">
                        <?= $ml->tr('RESETYOURPASS'); ?>
                    </h1>
                    <p class="auth-subtitle mb-5">
                        <?= $ml->tr('SETANEWPASS'); ?>
                    </p>

                    <form action="#" id="nepass_form">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" name="confirm_username" id="confirm_username"
                                class="form-control form-control-xl" placeholder="<?= $ml->tr('VERIFYUSERNAME'); ?>">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" id="password" name="password" class="form-control form-control-xl"
                                placeholder="<?= $ml->tr('PASSWORD'); ?>">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" name="confirm_password" id="confirm_password"
                                class="form-control form-control-xl" placeholder="<?= $ml->tr('CONFPASS'); ?>">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
                        <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">
                            <?= $ml->tr('SUBMIT'); ?>
                        </button>
                    </form>
                    <div class="text-center mt-5 text-lg fs-4">
                        <p class='text-gray-600'>
                            <?= $ml->tr('REMEMBERACCOUNT'); ?> <a href="index.php" class="font-bold">
                                <?= $ml->tr('LOGIN'); ?>
                            </a>.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right">

                </div>
            </div>
        </div>

    </div>
    <script src="<?php echo DIR; ?>/assets/extensions/jquery/jquery.min.js"></script>
    <script src="<?php echo DIR; ?>/assets/extensions/jqueryvalidation/jquery.validate.min.js"></script>
    <script src="<?php echo DIR; ?>/assets/extensions/jqueryvalidation/additional-methods.min.js"></script>
    <script src="<?php echo DIR; ?>/assets/extensions/sweetalert2/sweetalert2.min.js"></script>
    <script>
        var HOST_URL = "<?= DIR ?>";
        var IS_OFFLINE = <?php echo CLOSEDOWNED; ?>;
        var TRAN_NOTBEEMPTY = "<?= $ml->tr('NOTBEEMPTY'); ?>";
        var TRAN_PASSCHARMIN = "<?= $ml->tr('PASSCHARMIN'); ?>";
        var TRAN_PASSMUSTHAVESPECIALS = "<?= $ml->tr('PASSMUSTHAVESPECIAL'); ?>";
        var TRAN_PASSWORDSNEEDMATCH = "<?= $ml->tr('PASSWORDSNEEDMATCH'); ?>";
        var TRAN_PASSWORDHASCHANGED = "<?= $ml->tr('PASSWORDHASCHANGED'); ?>";
        var TRAN_OK = "<?= $ml->tr('OK'); ?>";
        var TRAN_ERRORSENDMAIL = "<?= $ml->tr('ERRORSENDMAIL'); ?>";
        var TRAN_BUG = "<?= $ml->tr('BUG'); ?>";
    </script>
    <script src="<?php echo DIR; ?>/assets/static/js/reset.js"></script>
    <script src="<?php echo DIR; ?>/assets/static/js/allpages_out.js"></script>
</body>

</html>