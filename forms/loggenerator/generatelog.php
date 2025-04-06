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
$todaydate = date('Y-m-d');
$date = $_POST['date'];
if (strpos($date, ',') !== false) {
    $multdates = explode(',', $date);
    foreach ($multdates as $mdates) {
        $datenext = date('Y-m-d', strtotime($mdates . ' +1 day'));
        $splitdate = explode('-', $mdates);
        $splitdatenext = explode('-', $datenext);
        $month = $splitdate[1];
        $day = $splitdate[2];
        $year = $splitdate[0];
        $monthnext = $splitdatenext[1];
        $daynext = $splitdatenext[2];
        $yearnext = $splitdatenext[0];
        $service = $_POST['logservice'];

        $tempname = $loggen->getLogNameFromService($service);
        $tempname = str_replace('%m', $month, $tempname);
        $tempname = str_replace('%d', $day, $tempname);
        $tempname = str_replace('%Y', $year, $tempname);

        $tempnamenext = $loggen->getLogNameFromService($service);
        $tempnamenext = str_replace('%m', $monthnext, $tempnamenext);
        $tempnamenext = str_replace('%d', $daynext, $tempnamenext);
        $tempnamenext = str_replace('%Y', $yearnext, $tempnamenext);

        $tempdesc = $loggen->getLogDescFromService($service);
        $tempdesc = str_replace('%m', $month, $tempdesc);
        $tempdesc = str_replace('%d', $day, $tempdesc);
        $tempdesc = str_replace('%Y', $year, $tempdesc);

        $chainlog = $loggen->getLogChain($service);
        if ($loggen->getLogShelflife($service) == 0) {
            $logpurge = date('Y-m-d', strtotime($mdates . ' +' . $loggen->getLogPurgeDate($service) . ' days'));
        } else {
            $logpurge = date('Y-m-d', strtotime($todaydate . ' +' . $loggen->getLogPurgeDate($service) . ' days'));
        }

        if ($loggen->getLogAutoRefresh($service) == 'Y') {
            $logrefresh = 1;
        } else {
            $logrefresh = 0;
        }
        $loggen_data['logs'][$tempname]['LOGNAME'] = $tempname;
        $loggen_data['logs'][$tempname]['SERVICE'] = $service;
        $loggen_data['logs'][$tempname]['DESCRIPTION'] = $tempdesc;
        $loggen_data['logs'][$tempname]['DATE'] = $mdates;
        $loggen_data['logs'][$tempname]['NEXTLOGNAME'] = $tempnamenext;
        $loggen_data['logs'][$tempname]['LOGCHAIN'] = $chainlog;
        $loggen_data['logs'][$tempname]['PURGELOG'] = $logpurge;
        $loggen_data['logs'][$tempname]['AUTOREFRESH'] = $logrefresh;
        $loggen_data['logs'][$tempname]['USERNAME'] = $_COOKIE["username"];
        $loggen_data['logs'][$tempname]['GENERATELOG'] = 1;

        $jsonData = json_encode($loggen_data, JSON_PRETTY_PRINT);
        if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/generatelog.json', $jsonData)) {
            $echodata = ['error' => 'true', 'errorcode' => '1'];
            echo json_encode($echodata);
        }

    }
    $echodata = ['error' => 'false', 'errorcode' => '0'];
    echo json_encode($echodata);

} else {

    $datenext = date('Y-m-d', strtotime($date . ' +1 day'));
    $splitdate = explode('-', $date);
    $splitdatenext = explode('-', $datenext);
    $month = $splitdate[1];
    $day = $splitdate[2];
    $year = $splitdate[0];
    $monthnext = $splitdatenext[1];
    $daynext = $splitdatenext[2];
    $yearnext = $splitdatenext[0];
    $service = $_POST['logservice'];

    $tempname = $loggen->getLogNameFromService($service);
    $tempname = str_replace('%m', $month, $tempname);
    $tempname = str_replace('%d', $day, $tempname);
    $tempname = str_replace('%Y', $year, $tempname);

    $tempnamenext = $loggen->getLogNameFromService($service);
    $tempnamenext = str_replace('%m', $monthnext, $tempnamenext);
    $tempnamenext = str_replace('%d', $daynext, $tempnamenext);
    $tempnamenext = str_replace('%Y', $yearnext, $tempnamenext);

    $tempdesc = $loggen->getLogDescFromService($service);
    $tempdesc = str_replace('%m', $month, $tempdesc);
    $tempdesc = str_replace('%d', $day, $tempdesc);
    $tempdesc = str_replace('%Y', $year, $tempdesc);

    $chainlog = $loggen->getLogChain($service);
    if ($loggen->getLogShelflife($service) == 0) {
        $logpurge = date('Y-m-d', strtotime($date . ' +' . $loggen->getLogPurgeDate($service) . ' days'));
    } else {
        $logpurge = date('Y-m-d', strtotime($todaydate . ' +' . $loggen->getLogPurgeDate($service) . ' days'));
    }

    if ($loggen->getLogAutoRefresh($service) == 'Y') {
        $logrefresh = 1;
    } else {
        $logrefresh = 0;
    }
    $loggen_data['logs'][$tempname]['LOGNAME'] = $tempname;
    $loggen_data['logs'][$tempname]['SERVICE'] = $service;
    $loggen_data['logs'][$tempname]['DESCRIPTION'] = $tempdesc;
    $loggen_data['logs'][$tempname]['DATE'] = $date;
    $loggen_data['logs'][$tempname]['NEXTLOGNAME'] = $tempnamenext;
    $loggen_data['logs'][$tempname]['LOGCHAIN'] = $chainlog;
    $loggen_data['logs'][$tempname]['PURGELOG'] = $logpurge;
    $loggen_data['logs'][$tempname]['AUTOREFRESH'] = $logrefresh;
    $loggen_data['logs'][$tempname]['USERNAME'] = $_COOKIE["username"];
    $loggen_data['logs'][$tempname]['GENERATELOG'] = 1;

    $jsonData = json_encode($loggen_data, JSON_PRETTY_PRINT);
    if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/generatelog.json', $jsonData)) {
        $echodata = ['error' => 'true', 'errorcode' => '1'];
        echo json_encode($echodata);
    } else {
        $echodata = ['error' => 'false', 'errorcode' => '0'];
        echo json_encode($echodata);
    }

}

