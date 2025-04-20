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

        //For custom wildcards
        $shortn = date("D", strtotime($mdates)); //%a
        $shortnnext = date("D", strtotime($datenext)); //%a
        $fulldnames = date("l", strtotime($mdates)); //%A
        $fulldnamesnext = date("l", strtotime($datenext)); //%A
        $shortMnames = date("M", strtotime($mdates)); //%b
        $shortMnamesnext = date("M", strtotime($datenext)); //%b
        $longMnames = date("F", strtotime($mdates)); //%B
        $longMnamesnext = date("F", strtotime($datenext)); //%B
        $datform2 = date("m-d-y", strtotime($mdates)); //%D
        $dateform2next = date("m-d-y", strtotime($datenext)); //%D
        $dayofmonth = date("j", strtotime($mdates)); //%e
        $dayofmonthnext = date("j", strtotime($datenext)); //%e
        $fulldate = date("Y-m-d", strtotime($mdates)); //%F
        $fulldatenext = date("Y-m-d", strtotime($datenext)); //%F
        $hour24 = date("H"); //%H
        $hour24Space = date("G"); //%k
        $hour12 = date("g"); //%i
        $hour12zero = date("h"); //%h
        $dayofyeardate = date("z", strtotime($mdates)); //%j
        $dayofyeardatenext = date("z", strtotime($datenext)); //%j
        $monthdate = date("n", strtotime($mdates)); //%l
        $monthdatenext = date("n", strtotime($datenext)); //%l
        $minutezero = date("i"); //%M
        $ampm = date("A"); //%P
        $secondszero = date("s"); //%S
        $daytextdate = date("N", strtotime($mdates)); //%u
        $daytextdatenext = date("N", strtotime($datenext)); //%u
        $daytext0date = date("w", strtotime($mdates)); //%w
        $daytext0datenext = date("w", strtotime($datenext)); //%w
        $weeknumberdate = date("W", strtotime($mdates)); //%V
        $weeknumberdatenext = date("W", strtotime($datenext)); //%V
        $toyearnumb = date("y", strtotime($mdates)); //%y
        $toyearnumbnext = date("y", strtotime($datenext)); //%y

        $tempname = $loggen->getLogNameFromService($service);
        $tempname = str_replace('%m', $month, $tempname);
        $tempname = str_replace('%d', $day, $tempname);
        $tempname = str_replace('%Y', $year, $tempname);
        $tempname = str_replace('%y', $toyearnumb, $tempname);
        $tempname = str_replace('%G', $year, $tempname);
        $tempname = str_replace('%a', $shortn, $tempname);
        $tempname = str_replace('%A', $fulldnames, $tempname);
        $tempname = str_replace('%b', $shortMnames, $tempname);
        $tempname = str_replace('%h', $shortMnames, $tempname);
        $tempname = str_replace('%B', $longMnames, $tempname);
        $tempname = str_replace('%D', $datform2, $tempname);
        $tempname = str_replace('%e', " ".$dayofmonth, $tempname);
        $tempname = str_replace('%F', $fulldate, $tempname);
        $tempname = str_replace('%H', $hour24, $tempname);
        $tempname = str_replace('%k', " ".$hour24Space, $tempname);
        $tempname = str_replace('%i', " ".$hour12, $tempname);
        $tempname = str_replace('%J', $hour12, $tempname);
        $tempname = str_replace('%I', $hour12zero, $tempname);
        $tempname = str_replace('%j', $dayofyeardate, $tempname);
        $tempname = str_replace('%l', $monthdate, $tempname);
        $tempname = str_replace('%M', $minutezero, $tempname);
        $tempname = str_replace('%P', $ampm, $tempname);
        $tempname = str_replace('%S', $secondszero, $tempname);
        $tempname = str_replace('%u', $daytextdate, $tempname);
        $tempname = str_replace('%w', $daytext0date, $tempname);
        $tempname = str_replace('%V', $weeknumberdate, $tempname);
        $tempname = str_replace('%W', $weeknumberdate, $tempname);

        $tempnamenext = $loggen->getLogNameFromService($service);
        $tempnamenext = str_replace('%m', $monthnext, $tempnamenext);
        $tempnamenext = str_replace('%d', $daynext, $tempnamenext);
        $tempnamenext = str_replace('%Y', $yearnext, $tempnamenext);
        $tempnamenext = str_replace('%y', $toyearnumbnext, $tempnamenext);
        $tempnamenext = str_replace('%G', $yearnext, $tempnamenext);
        $tempnamenext = str_replace('%a', $shortnnext, $tempnamenext);
        $tempnamenext = str_replace('%A', $fulldnamesnext, $tempnamenext);
        $tempnamenext = str_replace('%b', $shortMnamesnext, $tempnamenext);
        $tempnamenext = str_replace('%h', $shortMnamesnext, $tempnamenext);
        $tempnamenext = str_replace('%B', $longMnamesnext, $tempnamenext);
        $tempnamenext = str_replace('%D', $dateform2next, $tempnamenext);
        $tempnamenext = str_replace('%e', " ".$dayofmonthnext, $tempnamenext);
        $tempnamenext = str_replace('%F', $fulldatenext, $tempnamenext);
        $tempnamenext = str_replace('%H', $hour24, $tempnamenext);
        $tempnamenext = str_replace('%k', " ".$hour24Space, $tempnamenext);
        $tempnamenext = str_replace('%i', " ".$hour12, $tempnamenext);
        $tempnamenext = str_replace('%J', $hour12, $tempnamenext);
        $tempnamenext = str_replace('%I', $hour12zero, $tempnamenext);
        $tempnamenext = str_replace('%j', $dayofyeardatenext, $tempnamenext);
        $tempnamenext = str_replace('%l', $monthdatenext, $tempnamenext);
        $tempnamenext = str_replace('%M', $minutezero, $tempnamenext);
        $tempnamenext = str_replace('%P', $ampm, $tempnamenext);
        $tempnamenext = str_replace('%S', $secondszero, $tempnamenext);
        $tempnamenext = str_replace('%u', $daytextdatenext, $tempnamenext);
        $tempnamenext = str_replace('%w', $daytext0datenext, $tempnamenext);
        $tempnamenext = str_replace('%V', $weeknumberdatenext, $tempnamenext);
        $tempnamenext = str_replace('%W', $weeknumberdatenext, $tempnamenext);

        $tempdesc = $loggen->getLogDescFromService($service);
        $tempdesc = str_replace('%m', $month, $tempdesc);
        $tempdesc = str_replace('%d', $day, $tempdesc);
        $tempdesc = str_replace('%Y', $year, $tempdesc);
        $tempdesc = str_replace('%y', $toyearnumb, $tempdesc);
        $tempdesc = str_replace('%G', $year, $tempdesc);
        $tempdesc = str_replace('%a', $shortn, $tempdesc);
        $tempdesc = str_replace('%A', $fulldnames, $tempdesc);
        $tempdesc = str_replace('%b', $shortMnames, $tempdesc);
        $tempdesc = str_replace('%h', $shortMnames, $tempdesc);
        $tempdesc = str_replace('%B', $longMnames, $tempdesc);
        $tempdesc = str_replace('%D', $datform2, $tempdesc);
        $tempdesc = str_replace('%e', " ".$dayofmonth, $tempdesc);
        $tempdesc = str_replace('%F', $fulldate, $tempdesc);
        $tempdesc = str_replace('%H', $hour24, $tempdesc);
        $tempdesc = str_replace('%k', " ".$hour24Space, $tempdesc);
        $tempdesc = str_replace('%i', " ".$hour12, $tempdesc);
        $tempdesc = str_replace('%J', $hour12, $tempdesc);
        $tempdesc = str_replace('%I', $hour12zero, $tempdesc);
        $tempdesc = str_replace('%j', $dayofyeardate, $tempdesc);
        $tempdesc = str_replace('%l', $monthdate, $tempdesc);
        $tempdesc = str_replace('%M', $minutezero, $tempdesc);
        $tempdesc = str_replace('%P', $ampm, $tempdesc);
        $tempdesc = str_replace('%S', $secondszero, $tempdesc);
        $tempdesc = str_replace('%u', $daytextdate, $tempdesc);
        $tempdesc = str_replace('%w', $daytext0date, $tempdesc);
        $tempdesc = str_replace('%V', $weeknumberdate, $tempdesc);
        $tempdesc = str_replace('%W', $weeknumberdate, $tempdesc);


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

    //For custom wildcards
    $shortn = date("D", strtotime($date)); //%a
    $shortnnext = date("D", strtotime($datenext)); //%a
    $fulldnames = date("l", strtotime($date)); //%A
    $fulldnamesnext = date("l", strtotime($datenext)); //%A
    $shortMnames = date("M", strtotime($date)); //%b
    $shortMnamesnext = date("M", strtotime($datenext)); //%b
    $longMnames = date("F", strtotime($date)); //%B
    $longMnamesnext = date("F", strtotime($datenext)); //%B
    $datform2 = date("m-d-y", strtotime($date)); //%D
    $dateform2next = date("m-d-y", strtotime($datenext)); //%D
    $dayofmonth = date("j", strtotime($date)); //%e
    $dayofmonthnext = date("j", strtotime($datenext)); //%e
    $fulldate = date("Y-m-d", strtotime($date)); //%F
    $fulldatenext = date("Y-m-d", strtotime($datenext)); //%F
    $hour24 = date("H"); //%H
    $hour24Space = date("G"); //%k
    $hour12 = date("g"); //%i
    $hour12zero = date("h"); //%h
    $dayofyeardate = date("z", strtotime($date)); //%j
    $dayofyeardatenext = date("z", strtotime($datenext)); //%j
    $monthdate = date("n", strtotime($date)); //%l
    $monthdatenext = date("n", strtotime($datenext)); //%l
    $minutezero = date("i"); //%M
    $ampm = date("A"); //%P
    $secondszero = date("s"); //%S
    $daytextdate = date("N", strtotime($date)); //%u
    $daytextdatenext = date("N", strtotime($datenext)); //%u
    $daytext0date = date("w", strtotime($date)); //%w
    $daytext0datenext = date("w", strtotime($datenext)); //%w
    $weeknumberdate = date("W", strtotime($date)); //%V
    $weeknumberdatenext = date("W", strtotime($datenext)); //%V
    $toyearnumb = date("y", strtotime($date)); //%y
    $toyearnumbnext = date("y", strtotime($datenext)); //%y

    $tempname = $loggen->getLogNameFromService($service);
    $tempname = str_replace('%m', $month, $tempname);
    $tempname = str_replace('%d', $day, $tempname);
    $tempname = str_replace('%Y', $year, $tempname);
    $tempname = str_replace('%y', $toyearnumb, $tempname);
    $tempname = str_replace('%G', $year, $tempname);
    $tempname = str_replace('%a', $shortn, $tempname);
    $tempname = str_replace('%A', $fulldnames, $tempname);
    $tempname = str_replace('%b', $shortMnames, $tempname);
    $tempname = str_replace('%h', $shortMnames, $tempname);
    $tempname = str_replace('%B', $longMnames, $tempname);
    $tempname = str_replace('%D', $datform2, $tempname);
    $tempname = str_replace('%e', " ".$dayofmonth, $tempname);
    $tempname = str_replace('%F', $fulldate, $tempname);
    $tempname = str_replace('%H', $hour24, $tempname);
    $tempname = str_replace('%k', " ".$hour24Space, $tempname);
    $tempname = str_replace('%i', " ".$hour12, $tempname);
    $tempname = str_replace('%J', $hour12, $tempname);
    $tempname = str_replace('%I', $hour12zero, $tempname);
    $tempname = str_replace('%j', $dayofyeardate, $tempname);
    $tempname = str_replace('%l', $monthdate, $tempname);
    $tempname = str_replace('%M', $minutezero, $tempname);
    $tempname = str_replace('%P', $ampm, $tempname);
    $tempname = str_replace('%S', $secondszero, $tempname);
    $tempname = str_replace('%u', $daytextdate, $tempname);
    $tempname = str_replace('%w', $daytext0date, $tempname);
    $tempname = str_replace('%V', $weeknumberdate, $tempname);
    $tempname = str_replace('%W', $weeknumberdate, $tempname);

    $tempnamenext = $loggen->getLogNameFromService($service);
    $tempnamenext = str_replace('%m', $monthnext, $tempnamenext);
    $tempnamenext = str_replace('%d', $daynext, $tempnamenext);
    $tempnamenext = str_replace('%Y', $yearnext, $tempnamenext);
    $tempnamenext = str_replace('%y', $toyearnumbnext, $tempnamenext);
    $tempnamenext = str_replace('%G', $yearnext, $tempnamenext);
    $tempnamenext = str_replace('%a', $shortnnext, $tempnamenext);
    $tempnamenext = str_replace('%A', $fulldnamesnext, $tempnamenext);
    $tempnamenext = str_replace('%b', $shortMnamesnext, $tempnamenext);
    $tempnamenext = str_replace('%h', $shortMnamesnext, $tempnamenext);
    $tempnamenext = str_replace('%B', $longMnamesnext, $tempnamenext);
    $tempnamenext = str_replace('%D', $dateform2next, $tempnamenext);
    $tempnamenext = str_replace('%e', " ".$dayofmonthnext, $tempnamenext);
    $tempnamenext = str_replace('%F', $fulldatenext, $tempnamenext);
    $tempnamenext = str_replace('%H', $hour24, $tempnamenext);
    $tempnamenext = str_replace('%k', " ".$hour24Space, $tempnamenext);
    $tempnamenext = str_replace('%i', " ".$hour12, $tempnamenext);
    $tempnamenext = str_replace('%J', $hour12, $tempnamenext);
    $tempnamenext = str_replace('%I', $hour12zero, $tempnamenext);
    $tempnamenext = str_replace('%j', $dayofyeardatenext, $tempnamenext);
    $tempnamenext = str_replace('%l', $monthdatenext, $tempnamenext);
    $tempnamenext = str_replace('%M', $minutezero, $tempnamenext);
    $tempnamenext = str_replace('%P', $ampm, $tempnamenext);
    $tempnamenext = str_replace('%S', $secondszero, $tempnamenext);
    $tempnamenext = str_replace('%u', $daytextdatenext, $tempnamenext);
    $tempnamenext = str_replace('%w', $daytext0datenext, $tempnamenext);
    $tempnamenext = str_replace('%V', $weeknumberdatenext, $tempnamenext);
    $tempnamenext = str_replace('%W', $weeknumberdatenext, $tempnamenext);

    $tempdesc = $loggen->getLogDescFromService($service);
    $tempdesc = str_replace('%m', $month, $tempdesc);
    $tempdesc = str_replace('%d', $day, $tempdesc);
    $tempdesc = str_replace('%Y', $year, $tempdesc);
    $tempdesc = str_replace('%y', $toyearnumb, $tempdesc);
    $tempdesc = str_replace('%G', $year, $tempdesc);
    $tempdesc = str_replace('%a', $shortn, $tempdesc);
    $tempdesc = str_replace('%A', $fulldnames, $tempdesc);
    $tempdesc = str_replace('%b', $shortMnames, $tempdesc);
    $tempdesc = str_replace('%h', $shortMnames, $tempdesc);
    $tempdesc = str_replace('%B', $longMnames, $tempdesc);
    $tempdesc = str_replace('%D', $datform2, $tempdesc);
    $tempdesc = str_replace('%e', $dayofmonth, $tempdesc);
    $tempdesc = str_replace('%F', $fulldate, $tempdesc);
    $tempdesc = str_replace('%H', $hour24, $tempdesc);
    $tempdesc = str_replace('%k', " ".$hour24Space, $tempdesc);
    $tempdesc = str_replace('%i', " ".$hour12, $tempdesc);
    $tempdesc = str_replace('%J', $hour12, $tempdesc);
    $tempdesc = str_replace('%I', $hour12zero, $tempdesc);
    $tempdesc = str_replace('%j', $dayofyeardate, $tempdesc);
    $tempdesc = str_replace('%l', $monthdate, $tempdesc);
    $tempdesc = str_replace('%M', $minutezero, $tempdesc);
    $tempdesc = str_replace('%P', $ampm, $tempdesc);
    $tempdesc = str_replace('%S', $secondszero, $tempdesc);
    $tempdesc = str_replace('%u', $daytextdate, $tempdesc);
    $tempdesc = str_replace('%w', $daytext0date, $tempdesc);
    $tempdesc = str_replace('%V', $weeknumberdate, $tempdesc);
    $tempdesc = str_replace('%W', $weeknumberdate, $tempdesc);

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

