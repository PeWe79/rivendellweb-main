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
class Touch
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

    public function maxColums($username, $panelno)
    {
        $maxIDresult = $this->_db->prepare('SELECT max(COLUMN_NO) as maxCOL FROM PANELS WHERE OWNER = :owner AND PANEL_NO = :panel');

        $maxIDresult->execute([
            'owner' => $username,
            'panel' => $panelno
        ]);
        $maxIDvalue = $maxIDresult->fetch();

        return $maxIDvalue['maxCOL'];
    }

    public function maxRows($username, $panelno)
    {
        $maxIDresult = $this->_db->prepare('SELECT max(ROW_NO) as maxROW FROM PANELS WHERE OWNER = :owner AND PANEL_NO = :panel');

        $maxIDresult->execute([
            'owner' => $username,
            'panel' => $panelno
        ]);
        $maxIDvalue = $maxIDresult->fetch();

        return $maxIDvalue['maxROW'];
    }

    public function countPanels($username, $panelno)
    {
        $stmt = $this->_db->prepare('SELECT * FROM PANELS WHERE OWNER = :owner AND PANEL_NO = :panel');

            $stmt->execute([
                'owner' => $username,
                'panel' => $panelno
            ]);
            $number_of_rows = $stmt->rowCount();

           return $number_of_rows;
    }

    public function isloggedInPC($username)
    {
        $stmt = $this->_db->prepare('SELECT * FROM STATIONS WHERE USER_NAME = :owner');

            $stmt->execute([
                'owner' => $username,
            ]);
            $number_of_rows = $stmt->rowCount();

           return $number_of_rows;
    }

    public function countPanelsRow($username, $panelno, $row)
    {
        $stmt = $this->_db->prepare('SELECT * FROM PANELS WHERE OWNER = :owner AND PANEL_NO = :panel AND ROW_NO = :row');

            $stmt->execute([
                'owner' => $username,
                'panel' => $panelno,
                'row' => $row
            ]);
            $number_of_rows = $stmt->rowCount();

           return $number_of_rows;
    }

    public function getPanelsButton($username, $panelno)
    {

        $panelbuttons = array();
        $sql = 'SELECT * FROM `PANELS` WHERE `OWNER` = :owner AND `PANEL_NO` = :panel  ORDER BY ROW_NO, COLUMN_NO ASC';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':owner', $username);
        $stmt->bindParam(':panel', $panelno);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {

            $panelbuttons[] = $row;
        }

        return $panelbuttons;

    }

    public function getPanelsButtonRowUnique($username, $panelno)
    {

        $panelbuttons = array();
        $sql = 'SELECT DISTINCT ROW_NO FROM `PANELS` WHERE `OWNER` = :owner AND `PANEL_NO` = :panel  ORDER BY ROW_NO ASC';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':owner', $username);
        $stmt->bindParam(':panel', $panelno);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {

            $panelbuttons[] = $row;
        }

        return $panelbuttons;

    }

    public function getPanelsButtonRow($username, $panelno, $rowso)
    {

        $panelbuttons = array();
        $sql = 'SELECT * FROM `PANELS` WHERE `OWNER` = :owner AND `PANEL_NO` = :panel AND `ROW_NO` = :rowsp  ORDER BY COLUMN_NO ASC';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':owner', $username);
        $stmt->bindParam(':panel', $panelno);
        $stmt->bindParam(':rowsp', $rowso);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while ($row = $stmt->fetch()) {

            $panelbuttons[] = $row;
        }

        return $panelbuttons;

    }


}