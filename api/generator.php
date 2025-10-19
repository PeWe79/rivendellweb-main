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

$services = $dbfunc->getServices();
$timeformat = 'i:s';
$lastschedcode = "";
$addingmusic = 0;
$count = 0;
$daystogen = 0;


if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/generatelog.json')) {
    if ($loggen_data['sys']['GENERATING'] == 0) {
        //auto generate logs here
        if ($loggen_data['sys']['AUTOGEN'] == 1) {
            $daystogen = $loggen_data['sys']['DAYSGEN'];
            $startDate = date("Y-m-d");
            $nextday = 1;
            $futureDate = $loggen->getDateInFuture($startDate, $nextday);
            foreach ($loggen_data['sys']['GENSERVICE'] as $lines) {
                if ($lines['LASTLOG'] == $futureDate) {
                    //Generate new logs
                    $futureDateStart = $loggen->getDateInFuture($futureDate, $nextday);
                    $futureDateEnd = $loggen->getDateInFuture($futureDate, $daystogen);
                    $startDate = $futureDateStart;
                    $endDate = $futureDateEnd;
                    $logdates = $loggen->getDatesBetween($startDate, $endDate);
                    
                    foreach ($logdates as $mdates) {
                        $datenext = date('Y-m-d', strtotime($mdates . ' +1 day'));
                        $splitdate = explode('-', $mdates);
                        $splitdatenext = explode('-', $datenext);
                        $month = $splitdate[1];
                        $day = $splitdate[2];
                        $year = $splitdate[0];
                        $monthnext = $splitdatenext[1];
                        $daynext = $splitdatenext[2];
                        $yearnext = $splitdatenext[0];
                        $service = $loggen_data['sys']['GENSERVICE'][$lines['SERVNAME']]['SERVNAME'];
                
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
                        $loggen_data['logs'][$tempname]['USERNAME'] = "LogGenerator";
                        $loggen_data['logs'][$tempname]['GENERATELOG'] = 1;
                        $loggen_data['sys']['GENSERVICE'][$lines['SERVNAME']]['LASTLOG'] = $endDate;
                
                        $jsonData = json_encode($loggen_data, JSON_PRETTY_PRINT);
                        if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/generatelog.json', $jsonData)) {
                            $loggen->addToLog("Could not add log to generate", $tempname);
                        }
                
                    }
                
                }
            }

        }

        foreach ($loggen_data['logs'] as $lines) {
            if (date("Y-m-d") == $lines['PURGELOG']) {
                unset($loggen_data['logs'][$lines['LOGNAME']]);
                unset($loggenlog_data[$lines['LOGNAME']]);
                $jsonData = json_encode($loggen_data, JSON_PRETTY_PRINT);
                if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/generatelog.json', $jsonData)) {
                    $loggen->addToLog("Could not purge log template.", $lines['LOGNAME']);
                }
                $jsonData = json_encode($loggenlog_data, JSON_PRETTY_PRINT);
                if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/loggen_log.json', $jsonData)) {
                    $loggen->addToLog("Could not purge log template.", $lines['LOGNAME']);
                }
            }
            if ($lines['GENERATELOG'] == 1) {
                //Start make the log here
                $loggen_data['sys']['GENERATING'] = 1;
                $jsonData = json_encode($loggen_data, JSON_PRETTY_PRINT);
                if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/generatelog.json', $jsonData)) {
                    $loggen->addToLog("Could not update log template.", $lines['LOGNAME']);
                }
                if ($lines['AUTOREFRESH'] == 1) {
                    $refreshlog = "Y";
                } else {
                    $refreshlog = "N";
                }
                if ($loggen->checkLogExist($lines['LOGNAME'], $lines['SERVICE'])) {
                    if (!$loggen->deletelog($lines['LOGNAME'], $lines['USERNAME'], APIURL)) {
                        $loggen->addToLog("Could not delete old log.", $lines['LOGNAME']);
                    }
                }
                if (!$loggen->addNewLogg($lines['LOGNAME'], $lines['SERVICE'], $lines['DESCRIPTION'], $lines['PURGELOG'], $refreshlog)) {
                    $loggen->addToLog("Could not create log.", $lines['LOGNAME']);
                } else {
                    $loggen_data['logs'][$lines['LOGNAME']]['GENERATELOG'] = 2;
                    $jsonData = json_encode($loggen_data, JSON_PRETTY_PRINT);
                    if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/generatelog.json', $jsonData)) {
                        $loggen->addToLog("Could not update log template.", $lines['LOGNAME']);
                    }
                    $chain_log = $lines['LOGCHAIN'];
                    $nextlogname = $lines['NEXTLOGNAME'];
                    $firststarttime = 0;

                    //Log has been generated move one.
                    //0 = Sunday 1= Monday 2 = Thuesday 3 = Wednesday 4 = Thursday 5 = Friday 6 = Saturday
                    $timestamp = strtotime($lines['DATE']);
                    $daynumber = date('w', $timestamp);
                    if ($daynumber == 1) {
                        $hr1 = 0;
                        $hr2 = 23;
                        $loggen->addToLog("Log generation for monday.", $lines['LOGNAME']);
                    } else if ($daynumber == 2) {
                        $hr1 = 24;
                        $hr2 = 47;
                        $loggen->addToLog("Log generation for tuesday.", $lines['LOGNAME']);
                    } else if ($daynumber == 3) {
                        $hr1 = 48;
                        $hr2 = 71;
                        $loggen->addToLog("Log generation for wednesday.", $lines['LOGNAME']);
                    } else if ($daynumber == 4) {
                        $hr1 = 72;
                        $hr2 = 95;
                        $loggen->addToLog("Log generation for thursday.", $lines['LOGNAME']);
                    } else if ($daynumber == 5) {
                        $hr1 = 96;
                        $hr2 = 119;
                        $loggen->addToLog("Log generation for friday.", $lines['LOGNAME']);
                    } else if ($daynumber == 6) {
                        $hr1 = 120;
                        $hr2 = 143;
                        $loggen->addToLog("Log generation for saturday.", $lines['LOGNAME']);
                    } else if ($daynumber == 0) {
                        $hr1 = 144;
                        $hr2 = 167;
                        $loggen->addToLog("Log generation for sunday.", $lines['LOGNAME']);
                    } else {
                        $loggen->addToLog("No day selection possible", $lines['LOGNAME']);
                    }
                    for ($hr = $hr1; $hr <= $hr2; ) {
                        //We go thru each hour 
                        $clockname = $loggen->getClockNameFromServiceClock($lines['SERVICE'], $hr);
                        $clocklines = $loggen->getClockLines($clockname);
                        $totalclocklines = count($clocklines);
                        $clockstart = 1;
                        foreach ($clocklines as $clline) {
                            //Go thru each clock lines                        
                            $cartstoremove = array();
                            $evname = $clline['EVENT_NAME'];
                            $start_time = $clline['START_TIME'];
                            $length = $clline['LENGTH'];
                            $event = $loggen->getEventData($evname);
                            $eventlines = $loggen->getEventLineData($evname);
                            $importtype = $event['IMPORT_SOURCE'];
                            $timetype = $event['TIME_TYPE'];
                            //Round up to closest hour start time
                            if ($hr != $hr1 && $clockstart == 1) {
                                $clockroundupnow = 1;
                            } else {
                                $clockroundupnow = 0;
                            }

                            if (empty($eventlines)) {
                                $prepost = 0;
                                $loggen->addToLog("No event lines", $lines['LOGNAME']);
                            } else {
                                $prepost = 1;
                                $loggen->addToLog("Event lines", $lines['LOGNAME']);
                            }

                            if ($prepost == 1) {
                                foreach ($eventlines as $evvline) {
                                    if ($evvline['TYPE'] == 0) {
                                        //the event is pre import lets add it
                                        $currentcount = $loggen->getCurrentCount($lines['LOGNAME']);
                                        $source = 3;
                                        $extstarttime = gmdate($timeformat, $loggen->getCurrentStartTime($lines['LOGNAME'], $clockroundupnow) / 1000);
                                        $gracetime = "-1";
                                        $comment = "";
                                        if ($firststarttime == 0 && $hr1 == $hr) {
                                            //First time
                                            $startingtime = 0;
                                            $firststarttime = 1;
                                        } else {
                                            $startingtime = $loggen->getCurrentStartTime($lines['LOGNAME'], $clockroundupnow);
                                        }
                                        if ($evvline['EVENT_TYPE'] == 0) {
                                            //Cart
                                            if (!$loggen->addPreImport($lines['LOGNAME'], $currentcount, $evvline['EVENT_TYPE'], $source, $startingtime, $event['GRACE_TIME'], $evvline['CART_NUMBER'], $event['TIME_TYPE'], $evvline['TRANS_TYPE'], $comment, $length)) {
                                                $loggen->addToLog("Could not add Pre-Import Carts to log", $lines['LOGNAME']);
                                            }

                                        } else if ($evvline['EVENT_TYPE'] == 1) {
                                            //Note
                                            $cartno = 0;
                                            if (!$loggen->addPreImport($lines['LOGNAME'], $currentcount, $evvline['EVENT_TYPE'], $source, $startingtime, $event['GRACE_TIME'], $cartno, $event['TIME_TYPE'], $evvline['TRANS_TYPE'], $evvline['MARKER_COMMENT'], $length)) {
                                                $loggen->addToLog("Could not add Pre-Import Note Marker to log", $lines['LOGNAME']);
                                            }

                                        } else if ($evvline['EVENT_TYPE'] == 6) {
                                            //Note
                                            $cartno = 0;
                                            if (!$loggen->addPreImport($lines['LOGNAME'], $currentcount, $evvline['EVENT_TYPE'], $source, $startingtime, $event['GRACE_TIME'], $cartno, $event['TIME_TYPE'], $evvline['TRANS_TYPE'], $evvline['MARKER_COMMENT'], $length)) {
                                                $loggen->addToLog("Could not add Pre-Import Voice Track to log", $lines['LOGNAME']);
                                            }

                                        }
                                    }

                                }
                            }

                            if ($importtype == 3) {
                                $picmusicnow = 1;
                                //Add Music
                                //Pick a random cart
                                while ($picmusicnow == 1) {
                                    $groupcarts = $loggen->schedulerCart($lines['SERVICE'], $event['SCHED_GROUP'], $event['HAVE_CODE'], $event['HAVE_CODE2'], $event['ARTIST_SEP'], $event['TITLE_SEP'], $clockname, $lines['LOGNAME']);
                                    $totalsongsleft = count($groupcarts);
                                    $randommusic = array_rand($groupcarts, 1);
                                    if ($totalsongsleft > 0) {
                                        $currentcount = $loggen->getCurrentCount($lines['LOGNAME']);
                                        if ($firststarttime == 0 && $hr1 == $hr) {
                                            //First time
                                            $startingtime = 0;
                                            $firststarttime = 1;
                                        } else {
                                            $startingtime = $loggen->getCurrentStartTime($lines['LOGNAME'], $clockroundupnow);
                                        }
                                        $extstarttime = gmdate($timeformat, $loggen->getCurrentStartTime($lines['LOGNAME'], $clockroundupnow) / 1000);
                                        if (!$loggen->addLogLine($currentcount, '0', $lines['LOGNAME'], '2', $startingtime, $event['GRACE_TIME'], $randommusic, $event['TIME_TYPE'], $event['FIRST_TRANS_TYPE'], $extstarttime, $length)) {
                                            $loggen->addToLog("Could not add Music log", $lines['LOGNAME']);
                                        } else {
                                            //Added to log add to stack lines
                                            $datenow = date("Y-m-d H:i:s");

                                            $titlesmall = strtolower($loggen->getCartTitle($randommusic));
                                            $artistsmall = strtolower($loggen->getCartArtist($randommusic));
                                            $titlesmall = str_replace(' ', '', $titlesmall);
                                            $artistsmall = str_replace(' ', '', $artistsmall);

                                            $stackidtouse = $loggen->getNextStackId($lines['SERVICE']);
                                            if (!$loggen->addtoStackLine($lines['SERVICE'], $datenow, $stackidtouse, $randommusic, $artistsmall, $titlesmall)) {
                                                $loggen->addToLog("Could not add scheduled log line to stack line.", $lines['LOGNAME']);
                                            } else {
                                                //Add next part to stack line
                                                if (!$loggen->addtoStackSched($stackidtouse, $loggen->getCartSchedCode($randommusic))) {
                                                    $loggen->addToLog("Could not add scheduled code to stack line.", $lines['LOGNAME']);
                                                }

                                            }

                                            $picmusicnow = 0;

                                        }
                                    }

                                }

                            }

                            if ($prepost == 1) {
                                //Add Post import
                                foreach ($eventlines as $evvline) {
                                    if ($evvline['TYPE'] == 1) {
                                        //the event is pre import lets add it
                                        $currentcount = $loggen->getCurrentCount($lines['LOGNAME']);
                                        $source = 3;
                                        $extstarttime = gmdate($timeformat, $loggen->getCurrentStartTime($lines['LOGNAME'], $clockroundupnow) / 1000);
                                        $gracetime = "-1";
                                        $comment = "";
                                        if ($firststarttime == 0 && $hr1 == $hr) {
                                            //First time
                                            $startingtime = 0;
                                            $firststarttime = 1;
                                        } else {
                                            $startingtime = $loggen->getCurrentStartTime($lines['LOGNAME'], $length);
                                        }
                                        if ($evvline['EVENT_TYPE'] == 0) {
                                            //Cart
                                            if (!$loggen->addPreImport($lines['LOGNAME'], $currentcount, $evvline['EVENT_TYPE'], $source, $startingtime, $event['GRACE_TIME'], $evvline['CART_NUMBER'], $event['TIME_TYPE'], $evvline['TRANS_TYPE'], $comment, $length)) {
                                                $loggen->addToLog("Could not add Post-Import Carts to log", $lines['LOGNAME']);
                                            }

                                        } else if ($evvline['EVENT_TYPE'] == 1) {
                                            //Note
                                            $cartno = 0;
                                            if (!$loggen->addPreImport($lines['LOGNAME'], $currentcount, $evvline['EVENT_TYPE'], $source, $startingtime, $event['GRACE_TIME'], $cartno, $event['TIME_TYPE'], $evvline['TRANS_TYPE'], $evvline['MARKER_COMMENT'], $length)) {
                                                $loggen->addToLog("Could not add Post-Import Note Marker to log", $lines['LOGNAME']);
                                            }

                                        } else if ($evvline['EVENT_TYPE'] == 6) {
                                            //Note
                                            $cartno = 0;
                                            if (!$loggen->addPreImport($lines['LOGNAME'], $currentcount, $evvline['EVENT_TYPE'], $source, $startingtime, $event['GRACE_TIME'], $cartno, $event['TIME_TYPE'], $evvline['TRANS_TYPE'], $evvline['MARKER_COMMENT'], $length)) {
                                                $loggen->addToLog("Could not add Post-Import Voice Track to log", $lines['LOGNAME']);
                                            }

                                        }
                                    }

                                }
                            }
                            $clockstart = $clockstart + 1;
                        }
                        $hr = $hr + 1;
                    }

                    if ($chain_log == "Y") {
                        //Add Chain Log
                        $loggen->addToLog("Add Log Chain", $lines['LOGNAME']);
                        $currentcount = $loggen->getCurrentCount($lines['LOGNAME']);
                        if (!$loggen->addChainLog($lines['LOGNAME'], $currentcount, $nextlogname)) {
                            $loggen->addToLog("Could not add log chain to log.", $lines['LOGNAME']);
                        }

                    }
                    $loggen_data['logs'][$lines['LOGNAME']]['GENERATELOG'] = 3;
                    $loggen_data['sys']['GENERATING'] = 0;
                    $jsonData = json_encode($loggen_data, JSON_PRETTY_PRINT);
                    if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/generatelog.json', $jsonData)) {
                        $loggen->addToLog("Could not update log template.", $lines['LOGNAME']);
                    }
                }
            }

        }

    }


}