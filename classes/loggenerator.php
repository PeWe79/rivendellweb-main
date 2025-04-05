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
class LogGenerator
{
    private $_db;
    private $_ignoreCase;

    public function __construct($db)
    {
        $this->_db = $db;
        $this->_ignoreCase = false;
    }

    public function setIgnoreCase($sensitive)
    {
        $this->_ignoreCase = $sensitive;
    }

    public function getIgnoreCase()
    {
        return $this->_ignoreCase;
    }

    public function loadPass($username)
    {
        $stmt = $this->_db->prepare('SELECT * FROM USERS WHERE LOGIN_NAME = :name');

        $stmt->execute(['name' => $username]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['PASSWORD'];
    }

    public function addNewLogg($logname, $service, $desc, $purge, $autorefresh)
    {
        //Inserts log into database
        $shelflife = 0;
        $shelforigin = 0;
        $desctemp = "";
        $datenow = date("Y-m-d H:i:s");
        $ltype = 0;
        $orgusr = 'RDLogManager';
        $nextid = 0;

        $sql = 'INSERT INTO `LOGS` (`NAME`, `TYPE`, `DESCRIPTION`, `ORIGIN_USER`, `ORIGIN_DATETIME`, `MODIFIED_DATETIME`, `LINK_DATETIME`, `SERVICE`, `PURGE_DATE`, `AUTO_REFRESH`, `NEXT_ID`)
                VALUES (:lname, :ltype, :ldesc, :lorgusr, :ldatetime, :lmoddate, :llinkdate, :lservice, :lpurge, :refresh, :nextid)';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':lname', $logname);
        $stmt->bindParam(':ltype', $ltype);
        $stmt->bindParam(':ldesc', $desc);
        $stmt->bindParam(':lorgusr', $orgusr);
        $stmt->bindParam(':ldatetime', $datenow);
        $stmt->bindParam(':lmoddate', $datenow);
        $stmt->bindParam(':llinkdate', $datenow);
        $stmt->bindParam(':lservice', $service);
        $stmt->bindParam(':lpurge', $purge);
        $stmt->bindParam(':refresh', $autorefresh);
        $stmt->bindParam(':nextid', $nextid);

        if ($stmt->execute() === FALSE || $stmt->rowCount() != 1) {
            return false;
        } else {
            return true;
        }
    }

    public function UpdateNextID($logname)
    {

        $stmt1 = $this->_db->prepare('SELECT NEXT_ID FROM LOGS WHERE NAME = :name');

        $stmt1->execute(['name' => $logname]);

        $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

        $next_id = $row1['NEXT_ID'] + 1;

        $sql = 'UPDATE `LOGS` SET `NEXT_ID` = :nextid WHERE `NAME` = :name';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':nextid', $next_id);
        $stmt->bindParam(':name', $logname);

        if ($stmt->execute() === FALSE) {
            return false;
        } else {
            return true;
        }

    }

    public function addLogLine($lineid, $type, $logname, $source, $starttime, $gracetime, $cart, $timetype, $trans, $exstart, $evleng)
    {
        $sql = 'INSERT INTO `LOG_LINES` (`LOG_NAME`, `LINE_ID`, `COUNT`, `TYPE`, `SOURCE`, `START_TIME`, `GRACE_TIME`, `CART_NUMBER`, `TIME_TYPE`, `TRANS_TYPE`, `EXT_START_TIME`, `EVENT_LENGTH`)
                VALUES (:logname, :lineid, :count, :types, :source, :stime, :gtime, :cnumber, :ttype, :transtype, :extime, :evlength)';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':logname', $logname);
        $stmt->bindParam(':lineid', $lineid);
        $stmt->bindParam(':count', $lineid);
        $stmt->bindParam(':types', $type);
        $stmt->bindParam(':source', $source);
        $stmt->bindParam(':stime', $starttime);
        $stmt->bindParam(':gtime', $gracetime);
        $stmt->bindParam(':cnumber', $cart);
        $stmt->bindParam(':ttype', $timetype);
        $stmt->bindParam(':transtype', $trans);
        $stmt->bindParam(':extime', $exstart);
        $stmt->bindParam(':evlength', $evleng);

        if ($stmt->execute() === FALSE || $stmt->rowCount() != 1) {
            return false;
        } else {
            if (!$this->UpdateNextID($logname)) {
                return false;
            } else {
                return true;
            }

        }
    }

    public function addtoStackLine($service, $schedat, $schedid, $cart, $artist, $title)
    {
        $sql = 'INSERT INTO `STACK_LINES` (`SERVICE_NAME`, `SCHEDULED_AT`, `SCHED_STACK_ID`, `CART`, `ARTIST`, `TITLE`)
                VALUES (:servicen, :schedat, :schedstack, :cart, :artist, :title)';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':servicen', $service);
        $stmt->bindParam(':schedat', $schedat);
        $stmt->bindParam(':schedstack', $schedid);
        $stmt->bindParam(':cart', $cart);
        $stmt->bindParam(':artist', $artist);
        $stmt->bindParam(':title', $title);

        if ($stmt->execute() === FALSE || $stmt->rowCount() != 1) {
            return false;
        } else {
            return true;
        }
    }

    public function addtoStackSched($stackline, $schedcode)
    {
        $sql = 'INSERT INTO `STACK_SCHED_CODES` (`STACK_LINES_ID`, `SCHED_CODE`)
                VALUES (:stackline, :schedcode)';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':stackline', $stackline);
        $stmt->bindParam(':schedcode', $schedcode);

        if ($stmt->execute() === FALSE || $stmt->rowCount() != 1) {
            return false;
        } else {
            return true;
        }
    }

    public function addChainLog($logname, $lineid, $label)
    {
        $types = '5';
        $sources = '3';
        $starttime = null;
        $cart = '0';
        $trans = '1';
        $timetype = '0';
        $evlength = '-1';
        $sql = 'INSERT INTO `LOG_LINES` (`LOG_NAME`, `LINE_ID`, `COUNT`, `TYPE`, `SOURCE`, `START_TIME`, `CART_NUMBER`, `TRANS_TYPE`, `LABEL`, `EVENT_LENGTH`, `TIME_TYPE`)
                VALUES (:logname, :lineid, :count, :types, :source, :starttime, :cart, :trans, :label, :evlengt, :ttype)';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':logname', $logname);
        $stmt->bindParam(':lineid', $lineid);
        $stmt->bindParam(':count', $lineid);
        $stmt->bindParam(':types', $types);
        $stmt->bindParam(':source', $sources);
        $stmt->bindParam(':starttime', $starttime);
        $stmt->bindParam(':cart', $cart);
        $stmt->bindParam(':trans', $trans);
        $stmt->bindParam(':label', $label);
        $stmt->bindParam(':evlengt', $evlength);
        $stmt->bindParam(':ttype', $timetype);

        if ($stmt->execute() === FALSE || $stmt->rowCount() != 1) {
            return false;
        } else {
            if (!$this->UpdateNextID($logname)) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function addPreImport($logname, $count, $type, $source, $starttime, $gracetime, $cartnumber, $timetype, $transtype, $comment, $eventlength)
    {
        $sql = 'INSERT INTO `LOG_LINES` (`LOG_NAME`, `LINE_ID`, `COUNT`, `TYPE`, `SOURCE`, `START_TIME`, `GRACE_TIME`, `CART_NUMBER`, `TIME_TYPE`, `TRANS_TYPE`, `COMMENT`, `EVENT_LENGTH`)
                VALUES (:logname, :lineid, :count, :types, :source, :stime, :gtime, :cnumber, :ttype, :transtype, :comment, :evlength)';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':logname', $logname);
        $stmt->bindParam(':lineid', $count);
        $stmt->bindParam(':count', $count);
        $stmt->bindParam(':types', $type);
        $stmt->bindParam(':source', $source);
        $stmt->bindParam(':stime', $starttime);
        $stmt->bindParam(':gtime', $gracetime);
        $stmt->bindParam(':cnumber', $cartnumber);
        $stmt->bindParam(':ttype', $timetype);
        $stmt->bindParam(':transtype', $transtype);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':evlength', $eventlength);

        if ($stmt->execute() === FALSE || $stmt->rowCount() != 1) {
            return false;
        } else {
            if (!$this->UpdateNextID($logname)) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function getCartLength($cart)
    {
        $stmt = $this->_db->prepare('SELECT FORCED_LENGTH FROM CART WHERE NUMBER = :number');

        $stmt->execute(['number' => $cart]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['FORCED_LENGTH'];
    }

    public function getCartArtist($cart)
    {
        $stmt = $this->_db->prepare('SELECT ARTIST FROM CART WHERE NUMBER = :number');

        $stmt->execute(['number' => $cart]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['ARTIST'];
    }

    public function getCartSchedCode($cart)
    {
        $stmt = $this->_db->prepare('SELECT SCHED_CODE FROM CART_SCHED_CODES WHERE CART_NUMBER = :number');

        $stmt->execute(['number' => $cart]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['SCHED_CODE'];
    }

    public function getCartTitle($cart)
    {
        $stmt = $this->_db->prepare('SELECT TITLE FROM CART WHERE NUMBER = :number');

        $stmt->execute(['number' => $cart]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['TITLE'];
    }


    public function getCurrentCount($logname)
    {
        $stmt = $this->_db->prepare('SELECT NEXT_ID FROM LOGS WHERE NAME = :name');

        $stmt->execute(['name' => $logname]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['NEXT_ID'];
    }

    public function getCurrentStartTime($logname, $roundupnow)
    {
        if ($this->getCurrentCount($logname) == 1) {
            //Second line needs different calculation
            $lineid = 0;
            $stmt = $this->_db->prepare('SELECT START_TIME, EVENT_LENGTH FROM LOG_LINES WHERE LOG_NAME = :name AND LINE_ID = :lineid');

            $stmt->execute(['name' => $logname,
            'lineid' => $lineid]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row['EVENT_LENGTH'];
        } else {
            $lineid = $this->getCurrentCount($logname) - 1;
            $stmt = $this->_db->prepare('SELECT START_TIME, EVENT_LENGTH FROM LOG_LINES WHERE LOG_NAME = :name AND LINE_ID = :lineid');

            $stmt->execute(['name' => $logname,
            'lineid' => $lineid]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($roundupnow == 1) {
                $milliseconds = $row['START_TIME'] + $row['EVENT_LENGTH'];
                $hours = ceil($milliseconds / 3600000);
                $rounded_time = $hours * 3600000;
                return $rounded_time;
            } else {
                return $row['START_TIME'] + $row['EVENT_LENGTH'];
            }

            
        }


    }

    public function getCurrentEventLength($logname)
    {
        $stmt = $this->_db->prepare('SELECT EVENT_LENGTH FROM LOG_LINES WHERE LOG_NAME = :name ORDER BY COUNT DESC');

        $stmt->execute(['name' => $logname]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['EVENT_LENGTH'];
    }


    public function getCurrentLink($logname)
    {
        $stmt = $this->_db->prepare('SELECT LINK_ID FROM LOG_LINES WHERE LOG_NAME = :name AND LINK_ID >=0 ORDER BY LINK_ID DESC');

        $stmt->execute(['name' => $logname]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['COUNT'] + 1;
    }

    public function getNextStackId($service)
    {
        $stmt = $this->_db->prepare('SELECT MAX(SCHED_STACK_ID) AS maxID FROM STACK_LINES WHERE SERVICE_NAME = :name');

        $stmt->execute(['name' => $service]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row['maxID'] > 0) {
            return $row['maxID'] + 1;
        } else {
            return 0;
        }

    }

    public function removeElementWithValue($array, $value, $item)
    {
        $temp = array(); //Create temp array variable.
        foreach ($array as $item) { //access array elements.
            if ($item[$item] != $value) { //Skip the value, Which is equal.
                array_push($temp, $item);  //Push the array element into $temp var.
            }
        }
        return $temp; // Return the $temp array variable.
    }

    public function removeElementWithNotValue($array, $value, $item)
    {
        $temp = array(); //Create temp array variable.
        foreach ($array as $item) { //access array elements.
            if ($item[$item] == $value) { //Skip the value, Which is equal.
                array_push($temp, $item);  //Push the array element into $temp var.
            }
        }
        return $temp; // Return the $temp array variable.
    }

    public function loadCartsToArray($group)
    {
        $carts = array();
        $sql = 'SELECT ca.*, sh.* FROM CART ca LEFT JOIN CART_SCHED_CODES sh ON sh.CART_NUMBER = ca.NUMBER WHERE ca.GROUP_NAME = :name ORDER BY ca.NUMBER ASC';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':name', $group);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {

            $carts[$row['NUMBER']] = $row;
        }

        return $carts;
    }

    public function schedulerCart($service, $group, $have, $have2, $artsep, $titsep, $clockname, $log)
    {

        if ($artsep >= -1 && $artsep <= 50000) {
            $artseparator = $artsep;
        } else {
            $artseparator = 15;
        }

        if ($titsep >= -1 && $titsep <= 50000) {
            $titlesep = $titsep;
        } else {
            $titlesep = 100;
        }


        $clockrules = $this->ClockRules($clockname);

        $carts = array();
        $sql = 'SELECT ca.*, sh.* FROM CART ca LEFT JOIN CART_SCHED_CODES sh ON sh.CART_NUMBER = ca.NUMBER WHERE ca.GROUP_NAME = :name ORDER BY RAND() LIMIT 10';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':name', $group);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            //Check if have code
            $okadd = 0;
            $notadd = 0;
            if ($have != "") {
                if ($have == $row['SCHED_CODE']) {
                    $okadd = $okadd + 1;
                } else {
                    $notadd = $notadd + 1;
                }
            }
            if ($have2 != "") {
                if ($have2 == $row['SCHED_CODE']) {
                    $okadd = $okadd + 1;
                } else {
                    $notadd = $notadd + 1;
                }
            }

            $laststackid = $this->getNextStackId($service) - 1;
            $titsepval = $laststackid - $titlesep;
            $artsepval = $laststackid - $artseparator;
            $titlesmall = strtolower($row['TITLE']);
            $artistsmall = strtolower($row['ARTIST']);
            $titlesmall = str_replace(' ', '', $titlesmall);
            $artistsmall = str_replace(' ', '', $artistsmall);
            $titleseparation = $this->Separation($titsepval, $service);
            $artistseparation = $this->Separation($artsepval, $service);

            foreach ($titleseparation as $titsepo) {
                //Loop thru and find titles
                if ($titlesmall == $titsepo['TITLE']) {
                    $notadd = $notadd + 1;
                    $this->addToLog("Broken rule title separation for cart: ".$row['NUMBER'], $log);
                }
            }
            foreach ($artistseparation as $artsepo) {
                //Loop thru and find titles
                if ($artistsmall == $artsepo['ARTIST']) {
                    $notadd = $notadd + 1;
                    $this->addToLog("Broken rule artist separation for cart: ".$row['NUMBER'], $log);
                }
            }
            //Check Clock Rules
            $laststackid = $this->getNextStackId($service) - 1;
            if ($clockrules[$row['SCHED_CODE']]['MAX_ROW'] > 0 && $clockrules[$row['SCHED_CODE']]['MAX_ROW'] != null) {
                $maxrow = $laststackid - $clockrules[$row['SCHED_CODE']]['MAX_ROW'];
                $maxrowcarts = $this->getMinWait($maxrow, $service, $row['SCHED_CODE']);
                if ($this->checkSequence($maxrowcarts)) {
                    //Return true, we can not add 
                    $notadd = $notadd + 1;
                    $this->addToLog("Broken rule to many in row for cart: ".$row['NUMBER'], $log);
                } else {
                    $okadd = $okadd + 1;
                }

            } else {
                $okadd = $okadd + 1;
            }

            if ($clockrules[$row['SCHED_CODE']]['MIN_WAIT'] > 0 && $clockrules[$row['SCHED_CODE']]['MIN_WAIT'] != null) {
                $minwait = $laststackid - $clockrules[$row['SCHED_CODE']]['MIN_WAIT'];
                $minrowcarts = $this->getMinWait($minwait, $service, $row['SCHED_CODE']);
                if ($this->checkSequence($minrowcarts)) {
                    //Return true, we can not add
                    $notadd = $notadd + 1;
                    $this->addToLog("Broken rule min wait for cart: ".$row['NUMBER'], $log);
                } else {
                    $okadd = $okadd + 1;
                }
            } else {
                $okadd = $okadd + 1;
            }

            if ($clockrules[$row['SCHED_CODE']]['NOT_AFTER'] != "" || $clockrules[$row['SCHED_CODE']]['NOT_AFTER'] != null) {
                if (!empty($this->getNotAfter($laststackid, $service, $clockrules[$row['SCHED_CODE']]['NOT_AFTER']))) {
                    //Not after is before, we can not add this remove from cart array
                    $notadd = $notadd + 1;
                    $this->addToLog("Broken rule not after for cart: ".$row['NUMBER'], $log);
                } else {
                    $okadd = $okadd + 1;
                }
            } else {
                $okadd = $okadd + 1;
            }

            if ($clockrules[$row['SCHED_CODE']]['OR_AFTER'] != "" || $clockrules[$row['SCHED_CODE']]['OR_AFTER'] != null) {
                if (!empty($this->getNotAfter($laststackid, $service, $clockrules[$row['SCHED_CODE']]['OR_AFTER']))) {
                    //Or after is before, we can not add this remove from cart array
                    $notadd = $notadd + 1;
                    $this->addToLog("Broken rule or after for cart: ".$row['NUMBER'], $log);
                } else {
                    $okadd = $okadd + 1;
                }
            } else {
                $okadd = $okadd + 1;
            }

            if ($clockrules[$row['SCHED_CODE']]['OR_AFTER_II'] != "" || $clockrules[$row['SCHED_CODE']]['OR_AFTER_II'] != null) {
                if (!empty($this->getNotAfter($laststackid, $service, $clockrules[$row['SCHED_CODE']]['OR_AFTER_II']))) {
                    //Or after is before, we can not add this remove from cart array
                    $notadd = $notadd + 1;
                    $this->addToLog("Broken rule or after two for cart: ".$row['NUMBER'], $log);
                } else {
                    $okadd = $okadd + 1;
                }
            } else {
                $okadd = $okadd + 1;
            }




            if ($okadd > $notadd) {
                $carts[$row['NUMBER']] = $row;
            }


        }

        return $carts;

    }

    public function loadCartToArrayRandom($group)
    {
        $carts = array();
        $sql = 'SELECT ca.*, sh.* FROM CART ca LEFT JOIN CART_SCHED_CODES sh ON sh.CART_NUMBER = ca.NUMBER WHERE ca.GROUP_NAME = :name ORDER BY RAND() LIMIT 1';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':name', $group);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {

            $carts[$row['NUMBER']] = $row;
        }

        return $carts;
    }

    public function checkSequence($array)
    {
        $isSequential = true;
        for ($i = 0; $i < count($array) - 1; $i++) {
            if ($array[$i] + 1 != $array[$i + 1]) {
                $isSequential = false;
                break;
            }
        }
        return $isSequential;
    }

    public function getNotAfter($stackid, $service, $schedcode)
    {
        $separation = array();
        $sql = 'SELECT st.*, stc.* FROM STACK_LINES st LEFT JOIN STACK_SCHED_CODES stc ON stc.STACK_LINES_ID = st.ID WHERE st.SERVICE_NAME = :name AND st.SCHED_STACK_ID = :stack AND stc.SCHED_CODE = :sched';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':name', $service);
        $stmt->bindParam(':stack', $stackid);
        $stmt->bindParam(':sched', $schedcode);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $separation[$row['SCHED_STACK_ID']] = $row;
        }
        return $separation;
    }


    public function getMinWait($total, $service, $schedcode)
    {
        $separation = array();
        $sql = 'SELECT st.*, stc.* FROM STACK_LINES st LEFT JOIN STACK_SCHED_CODES stc ON stc.STACK_LINES_ID = st.ID WHERE st.SERVICE_NAME = :name AND st.SCHED_STACK_ID > :stack AND stc.SCHED_CODE = :sched';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':name', $service);
        $stmt->bindParam(':stack', $total);
        $stmt->bindParam(':sched', $schedcode);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $separation[$row['SCHED_STACK_ID']] = $row;
        }
        return $separation;
    }

    public function getSchedCodesAll()
    {
        $separation = array();
        $sql = 'SELECT * FROM SCHED_CODES';
        $stmt = $this->_db->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $separation[$row['CODE']] = $row;
        }
        return $separation;
    }

    public function Separation($total, $service)
    {
        $separation = array();
        $sql = 'SELECT * FROM STACK_LINES WHERE SERVICE_NAME = :name AND SCHED_STACK_ID >= :stack';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':name', $service);
        $stmt->bindParam(':stack', $total);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $separation[$row['SCHED_STACK_ID']] = $row;
        }
        return $separation;
    }

    public function ClockRules($clockname)
    {
        $clock = array();
        $sql = 'SELECT * FROM RULE_LINES WHERE CLOCK_NAME = :name';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':name', $clockname);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $clock[$row['CODE']] = $row;
        }
        return $clock;
    }

    public function getLogNameFromService($service)
    {
        $stmt = $this->_db->prepare('SELECT * FROM SERVICES WHERE NAME = :name');

        $stmt->execute(['name' => $service]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['NAME_TEMPLATE'];
    }

    public function getLogDescFromService($service)
    {
        $stmt = $this->_db->prepare('SELECT * FROM SERVICES WHERE NAME = :name');

        $stmt->execute(['name' => $service]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['DESCRIPTION_TEMPLATE'];
    }

    public function getLogPurgeDate($service)
    {
        $stmt = $this->_db->prepare('SELECT * FROM SERVICES WHERE NAME = :name');

        $stmt->execute(['name' => $service]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['DEFAULT_LOG_SHELFLIFE'];
    }

    public function getLogShelflife($service)
    {
        $stmt = $this->_db->prepare('SELECT * FROM SERVICES WHERE NAME = :name');

        $stmt->execute(['name' => $service]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['LOG_SHELFLIFE_ORIGIN'];
    }

    public function getLogAutoRefresh($service)
    {
        $stmt = $this->_db->prepare('SELECT * FROM SERVICES WHERE NAME = :name');

        $stmt->execute(['name' => $service]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['AUTO_REFRESH'];
    }

    public function checkLogExist($logname, $service)
    {
        $sql = 'SELECT * FROM `LOGS` WHERE `NAME` = :logname AND `SERVICE` = :servicename';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':logname', $logname);
        $stmt->bindParam(':servicename', $service);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            return true;

        } else {
            return false;
        }
    }

    public function deletelog($logname, $username, $api)
    {
        $rd_password = $this->loadPass($username);

        $ch = curl_init();
        $headers = array('Content-Type:multipart/form-data');
        $parameters = array(
            'COMMAND' => '30',
            'LOGIN_NAME' => $username,
            'PASSWORD' => $rd_password,
            'LOG_NAME' => $logname,
        );

        $options = array(
            CURLOPT_URL => $api,
            CURLOPT_HEADER => false,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $parameters,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        if (preg_match('/ResponseCode>200</', $result, $matches)) {
            return true;
        } else {
            return false;
        }


    }

    public function addLogg($logname, $service, $username, $api)
    {
        $rd_username = $username;
        $rd_password = $this->loadPass($rd_username);
        $rd_web_api = $api;

        $ch = curl_init();
        $headers = array('Content-Type:multipart/form-data');
        $parameters = array(
            'COMMAND' => '29',
            'LOGIN_NAME' => $rd_username,
            'PASSWORD' => $rd_password,
            'LOG_NAME' => $logname,
            'SERVICE_NAME' => $service,
        );

        $options = array(
            CURLOPT_URL => $rd_web_api,
            CURLOPT_HEADER => false,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $parameters,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        if (preg_match('/ResponseCode>200</', $result, $matches)) {
            return true;
        } else {
            return false;
        }
    }

    public function getServiceData($service)
    {

        $stmt = $this->_db->prepare('SELECT * FROM SERVICES WHERE NAME = :id');
        $stmt->execute([':id' => $service]);
        $array = $stmt->fetch(PDO::FETCH_ASSOC);

        return $array;

    }

    public function getLogChain($service)
    {

        $stmt = $this->_db->prepare('SELECT * FROM SERVICES WHERE NAME = :id');
        $stmt->execute([':id' => $service]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['CHAIN_LOG'];

    }

    public function getClockNameFromServiceClock($service, $hour)
    {
        $stmt = $this->_db->prepare('SELECT * FROM SERVICE_CLOCKS WHERE SERVICE_NAME = :name AND HOUR = :hour');

        $stmt->execute([
            'name' => $service,
            'hour' => $hour
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['CLOCK_NAME'];
    }

    public function getClockLines($clockname)
    {

        $clock = array();
        $sql = 'SELECT * FROM CLOCK_LINES WHERE CLOCK_NAME = :name';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':name', $clockname);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $clock[$row['ID']] = $row;
        }
        return $clock;

    }

    public function getEventData($eventname)
    {

        $stmt = $this->_db->prepare('SELECT * FROM EVENTS WHERE NAME = :id');
        $stmt->execute([':id' => $eventname]);
        $array = $stmt->fetch(PDO::FETCH_ASSOC);

        return $array;

    }

    public function getEventLineData($eventname)
    {

        $eventlines = array();
        $sql = 'SELECT * FROM EVENT_LINES WHERE EVENT_NAME = :id ORDER BY COUNT ASC';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':id', $eventname);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $eventlines[$row['ID']] = $row;
        }
        return $eventlines;

    }

    public function getRandomMusic($group)
    {

        $stmt = $this->_db->prepare('SELECT * FROM CART WHERE GROUP_NAME = :id ORDER BY rand() LIMIT 1');
        $stmt->execute([':id' => $group]);
        $array = $stmt->fetch(PDO::FETCH_ASSOC);

        return $array;

    }

    public function getCutData($cart)
    {

        $stmt = $this->_db->prepare('SELECT * FROM CUTS WHERE CART_NUMBER = :id LIMIT 1');
        $stmt->execute([':id' => $cart]);
        $array = $stmt->fetch(PDO::FETCH_ASSOC);

        return $array;

    }

    public function getSchedCodes($cart)
    {

        $stmt = $this->_db->prepare('SELECT SCHED_CODE FROM CART_SCHED_CODES WHERE CART_NUMBER = :id');
        $stmt->execute([':id' => $cart]);
        $array = $stmt->fetch(PDO::FETCH_ASSOC);

        return $array;

    }

    public function searchMultiDimensionalArray($array, $searchValue)
    {
        $results = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, $this->searchMultiDimensionalArray($value, $searchValue));
            } elseif ($value == $searchValue) {
                $results[] = array($key => $value);
            }
        }
        return $results;
    }

    public function searchMultiArray($val, $array, $match)
    {
        foreach ($array as $element) {
            if ($element[$match] == $val) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getRuleLines($clockname)
    {

        $stmt = $this->_db->prepare('SELECT CODE, MAX_ROW, MIN_WAIT, NOT_AFTER, OR_AFTER, OR_AFTER_II FROM RULE_LINES WHERE CLOCK_NAME = :id');
        $stmt->execute([':id' => $clockname]);
        $array = $stmt->fetch(PDO::FETCH_ASSOC);

        return $array;

    }

    public function addToLog($text, $logname)
    {
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/loggen_log.json')) {
            $loggenlog_data = array();
        } else {
            $filepath = $_SERVER['DOCUMENT_ROOT'] . '/data/loggen_log.json';
            $json_string = file_get_contents($filepath);
            $loggenlog_data = json_decode($json_string, true);
        }
        $datetime = date("Y-m-d H:i:s");

        $loggenlog_data[$logname][$datetime]['INFO'] = $text;
        $loggenlog_data[$logname][$datetime]['DATE'] = $datetime;

        $jsonData = json_encode($loggenlog_data, JSON_PRETTY_PRINT);
        if (!file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/data/loggen_log.json', $jsonData)) {
            return false;
        } else {
            return true;
        }

    }

}