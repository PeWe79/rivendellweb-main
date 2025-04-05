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


if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/generatelog.json')) {
    if ($loggen_data['sys']['GENERATING'] == 0) {

        foreach ($loggen_data['logs'] as $lines) {
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