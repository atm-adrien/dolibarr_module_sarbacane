<?php
/* Copyright (C) 2013 Florian Henry  <florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file /sarbacane/sarbacane.class.php
 * \ingroup sarbacane
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
// require_once 'MCAPI.class.php';
dol_include_once('/sarbacane/class/sarbacane.class.php');

/**
 * Put here description of your class
 */
class DolSarbacane extends CommonObject {
    var $db; // !< To store db handler
    var $error; // !< To return error code (or message)
    var $errors = array(); // !< To return several error codes (or messages)
    var $element = 'sarbacane'; // !< Id that identify managed objects
    var $table_element = 'sarbacane'; // !< Name of table without prefix where object is stored
    /** @var Sarbacane $sarbacane */
    var $sarbacane; // API Object
    var $email_lines = array();
    var $listdest_lines = array();
    var $blacklists_lines = array();
    var $listsegment_lines = array();
    var $listcampaign_lines = array();
    var $listlist_lines = array();
    var $email_activity = array();
    var $contactemail_activity = array();
    var $id;
    var $entity;
    var $fk_mailing;

    var $sarbacane_id;
    var $sarbacane_webid;
    var $sarbacane_listid;
    var $sarbacane_blacklistid;
    var $sarbacane_segmentid;
    var $sarbacane_sender_name;
    var $fk_user_author;
    var $datec = '';
    var $fk_user_mod;
    var $tms = '';
    var $currentmailing;
    var $lines = array();
    public static $list_contact_table = 'sarbacane_list_contact';
    public static $campaign_contact_table = 'sarbacane_campaign_contact';

    /**
     * Constructor
     *
     * @param DoliDb $db handler
     */
    function __construct($db) {
        $this->db = $db;
        return 1;
    }

    /**
     * Create object into database
     *
     * @param User $user      that creates
     * @param int  $notrigger triggers after, 1=disable triggers
     * @return int <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger = 0) {
        global $conf, $langs;
        $error = 0;

        // Clean parameters

        if(isset($this->entity)) $this->entity = trim($this->entity);
        if(isset($this->fk_mailing)) $this->fk_mailing = trim($this->fk_mailing);
        if(isset($this->sarbacane_id)) $this->sarbacane_id = trim($this->sarbacane_id);
        if(isset($this->sarbacane_webid)) $this->sarbacane_webid = trim($this->sarbacane_webid);
        if(isset($this->sarbacane_listid)) $this->sarbacane_listid = trim($this->sarbacane_listid);
        if(!empty($this->sarbacane_blacklistid)) {
        	$this->sarbacane_blacklistid = trim($this->sarbacane_blacklistid);
		}/* else {
			$this->sarbacane_blacklistid = 'DEFAULT_BLACKLIST';
		}*/
        if(isset($this->sarbacane_segmentid)) $this->sarbacane_segmentid = trim($this->sarbacane_segmentid);
        if(isset($this->sarbacane_sender_name)) $this->sarbacane_sender_name = trim($this->sarbacane_sender_name);

        // Check parameters
        // Put here code to add control on parameters values
        // Insert request
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."sarbacane(";

        $sql .= "entity,";
        $sql .= "fk_mailing,";
        $sql .= "sarbacane_id,";
        $sql .= "sarbacane_webid,";
        $sql .= "sarbacane_listid,";
        $sql .= "sarbacane_blacklistid,";
        $sql .= "sarbacane_segmentid,";
        $sql .= "sarbacane_sender_name,";
        $sql .= "fk_user_author,";
        $sql .= "datec,";
        $sql .= "fk_user_mod";
        $sql .= ") VALUES (";
        $sql .= " ".$conf->entity.",";
        $sql .= " ".(! isset($this->fk_mailing) ? 'NULL' : $this->fk_mailing).",";
        $sql .= " ".(! isset($this->sarbacane_id) ? 'NULL' : "'".$this->sarbacane_id."'").",";
        $sql .= " ".(! isset($this->sarbacane_webid) ? 'NULL' : "'".$this->sarbacane_webid."'").",";
        $sql .= " ".(! isset($this->sarbacane_listid) ? 'NULL' : "'".$this->db->escape($this->sarbacane_listid)."'").",";
        $sql .= " ".(! isset($this->sarbacane_blacklistid) ? 'NULL' : "'".$this->db->escape($this->sarbacane_blacklistid)."'").",";
        $sql .= " ".(! isset($this->sarbacane_segmentid) ? 'NULL' : "'".$this->db->escape($this->sarbacane_segmentid)."'").",";
        $sql .= " ".(! isset($this->sarbacane_sender_name) ? 'NULL' : "'".$this->db->escape($this->sarbacane_sender_name)."'").",";

        $sql .= " '".$user->id."',";
        $sql .= " '".$this->db->idate(dol_now())."',";
        $sql .= " '".$user->id."'";

        $sql .= ")";

        $this->db->begin();

        dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if(! $resql) {
            $error++;
            $this->errors[] = "Error ".$this->db->lasterror();
        }

        if(! $error) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."sarbacane");

            if(! $notrigger) {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action calls a trigger.

                // // Call triggers
                // include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
                // $interface=new Interfaces($this->db);
                // $result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
                // if ($result < 0) { $error++; $this->errors=$interface->errors; }
                // // End call triggers
            }
        }

        // Commit or rollback
        if($error) {
            foreach($this->errors as $errmsg) {
                dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
                $this->error .= ($this->error ? ', '.$errmsg : $errmsg);
            }
            $this->db->rollback();
            return -1 * $error;
        }
        else {
            $this->db->commit();
            return $this->id;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @param int $id object
     * @return int <0 if KO, >0 if OK
     */
    function fetch($id) {
        global $langs;
        $sql = "SELECT";
        $sql .= " t.rowid,";

        $sql .= " t.entity,";
        $sql .= " t.fk_mailing,";
        $sql .= " t.sarbacane_id,";
        $sql .= " t.sarbacane_webid,";
        $sql .= " t.sarbacane_listid,";
		$sql .= " t.sarbacane_blacklistid,";
		$sql .= " t.sarbacane_segmentid,";
        $sql .= " t.sarbacane_sender_name,";
        $sql .= " t.fk_user_author,";
        $sql .= " t.datec,";
        $sql .= " t.fk_user_mod,";
        $sql .= " t.tms";

        $sql .= " FROM ".MAIN_DB_PREFIX."sarbacane as t";
        $sql .= " WHERE t.rowid = ".$id;

        dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if($resql) {
            if($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->rowid;

                $this->entity = $obj->entity;
                $this->fk_mailing = $obj->fk_mailing;
                $this->sarbacane_id = $obj->sarbacane_id;
                $this->sarbacane_webid = $obj->sarbacane_webid;
                $this->sarbacane_listid = $obj->sarbacane_listid;
                $this->sarbacane_blacklistid = $obj->sarbacane_blacklistid;
                $this->sarbacane_segmentid = $obj->sarbacane_segmentid;
                $this->sarbacane_sender_name = $obj->sarbacane_sender_name;
                $this->fk_user_author = $obj->fk_user_author;
                $this->datec = $this->db->jdate($obj->datec);
                $this->fk_user_mod = $obj->fk_user_mod;
                $this->tms = $this->db->jdate($obj->tms);
            }
            $this->db->free($resql);

            return 1;
        }
        else {
            $this->error = "Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @return int <0 if KO, >0 if OK
     */
    function fetch_all($month_filter = 0) {
        global $langs;
        $sql = "SELECT";
        $sql .= " t.rowid,";

        $sql .= " t.entity,";
        $sql .= " t.fk_mailing,";
        $sql .= " t.sarbacane_id,";
        $sql .= " t.sarbacane_webid,";
        $sql .= " t.sarbacane_listid,";
        $sql .= " t.sarbacane_blacklistid,";
        $sql .= " t.sarbacane_segmentid,";
        $sql .= " t.sarbacane_sender_name,";
        $sql .= " t.fk_user_author,";
        $sql .= " t.datec,";
        $sql .= " t.fk_user_mod,";
        $sql .= " t.tms";

        $sql .= " FROM ".MAIN_DB_PREFIX."sarbacane as t";
        if(! empty($month_filter)) {
            require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
            $sql .= " WHERE (t.datec BETWEEN '".$this->db->escape($this->db->idate(dol_time_plus_duree(dol_now(), $month_filter * -1, 'm')))."' AND NOW())";
            $sql .= " ORDER BY t.datec DESC";
        }

        dol_syslog(get_class($this)."::fetch_all sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if($resql) {
            if($this->db->num_rows($resql)) {

                $this->lines = array();

                while($obj = $this->db->fetch_object($resql)) {

                    $line = new DolSarbacaneLine();

                    $line->id = $obj->rowid;

                    $line->entity = $obj->entity;
                    $line->fk_mailing = $obj->fk_mailing;
                    $line->sarbacane_id = $obj->sarbacane_id;
                    $line->sarbacane_webid = $obj->sarbacane_webid;
                    $line->sarbacane_listid = $obj->sarbacane_listid;
                    $line->sarbacane_blacklistid = $obj->sarbacane_blacklistid;
                    $line->sarbacane_segmentid = $obj->sarbacane_segmentid;
                    $line->sarbacane_sender_name = $obj->sarbacane_sender_name;
                    $line->fk_user_author = $obj->fk_user_author;
                    $line->datec = $this->db->jdate($obj->datec);
                    $line->fk_user_mod = $obj->fk_user_mod;
                    $line->tms = $this->db->jdate($obj->tms);

                    $this->lines[] = $line;
                }
            }
            $this->db->free($resql);

            return 1;
        }
        else {
            $this->error = "Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch_all ".$this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @param int $id of mailing
     * @return int <0 if KO, >0 if OK
     */
    function fetch_by_mailing($id) {
        global $langs;
        $sql = "SELECT";
        $sql .= " t.rowid,";

        $sql .= " t.entity,";
        $sql .= " t.fk_mailing,";
        $sql .= " t.sarbacane_id,";
        $sql .= " t.sarbacane_webid,";
        $sql .= " t.sarbacane_listid,";
        $sql .= " t.sarbacane_blacklistid,";
        $sql .= " t.sarbacane_segmentid,";
        $sql .= " t.sarbacane_sender_name,";
        $sql .= " t.fk_user_author,";
        $sql .= " t.datec,";
        $sql .= " t.fk_user_mod,";
        $sql .= " t.tms";

        $sql .= " FROM ".MAIN_DB_PREFIX."sarbacane as t";
        $sql .= " WHERE t.fk_mailing = ".$id;

        dol_syslog(get_class($this)."::fetch_by_mailing sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if($resql) {
            if($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->rowid;

                $this->entity = $obj->entity;
                $this->fk_mailing = $obj->fk_mailing;
                $this->sarbacane_id = $obj->sarbacane_id;
                $this->sarbacane_webid = $obj->sarbacane_webid;
                $this->sarbacane_listid = $obj->sarbacane_listid;
                $this->sarbacane_blacklistid = $obj->sarbacane_blacklistid;
                $this->sarbacane_segmentid = $obj->sarbacane_segmentid;
                $this->sarbacane_sender_name = $obj->sarbacane_sender_name;
                $this->fk_user_author = $obj->fk_user_author;
                $this->datec = $this->db->jdate($obj->datec);
                $this->fk_user_mod = $obj->fk_user_mod;
                $this->tms = $this->db->jdate($obj->tms);

				$this->db->free($resql);

				return 1;
            } else {
            	return 0;
			}

        }
        else {
            $this->error = "Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch_by_mailing ".$this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @param int $id of mailing
     * @return int <0 if KO, >0 if OK
     */
    function fetch_by_sarbacaneid($id) {
        global $langs;
        $sql = "SELECT";
        $sql .= " t.rowid,";

        $sql .= " t.entity,";
        $sql .= " t.fk_mailing,";
        $sql .= " t.sarbacane_id,";
        $sql .= " t.sarbacane_webid,";
        $sql .= " t.sarbacane_listid,";
        $sql .= " t.sarbacane_blacklistid,";
        $sql .= " t.sarbacane_segmentid,";
        $sql .= " t.sarbacane_sender_name,";
        $sql .= " t.fk_user_author,";
        $sql .= " t.datec,";
        $sql .= " t.fk_user_mod,";
        $sql .= " t.tms";

        $sql .= " FROM ".MAIN_DB_PREFIX."sarbacane as t";
        $sql .= " WHERE t.sarbacane_id = '".$this->db->escape($id)."'";

        dol_syslog(get_class($this)."::fetch_by_sarbacaneid sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if($resql) {
            if($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->rowid;

                $this->entity = $obj->entity;
                $this->fk_mailing = $obj->fk_mailing;
                $this->sarbacane_id = $obj->sarbacane_id;
                $this->sarbacane_webid = $obj->sarbacane_webid;
                $this->sarbacane_listid = $obj->sarbacane_listid;
                $this->sarbacane_blacklistid = $obj->sarbacane_blacklistid;
                $this->sarbacane_segmentid = $obj->sarbacane_segmentid;
                $this->sarbacane_sender_name = $obj->sarbacane_sender_name;
                $this->fk_user_author = $obj->fk_user_author;
                $this->datec = $this->db->jdate($obj->datec);
                $this->fk_user_mod = $obj->fk_user_mod;
                $this->tms = $this->db->jdate($obj->tms);
            }
            $this->db->free($resql);

            return 1;
        }
        else {
            $this->error = "Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch_by_sarbacaneid ".$this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     * Update object into database
     *
     * @param User $user      that modifies
     * @param int  $notrigger triggers after, 1=disable triggers
     * @return int <0 if KO, >0 if OK
     */
    function update($user = 0, $notrigger = 0) {
        global $conf, $langs;
        $error = 0;

        // Clean parameters

        if(isset($this->entity)) $this->entity = trim($this->entity);
        if(isset($this->fk_mailing)) $this->fk_mailing = trim($this->fk_mailing);
        if(isset($this->sarbacane_id)) $this->sarbacane_id = trim($this->sarbacane_id);
        if(isset($this->sarbacane_webid)) $this->sarbacane_webid = trim($this->sarbacane_webid);
        if(isset($this->sarbacane_listid)) $this->sarbacane_listid = trim($this->sarbacane_listid);
		if(!empty($this->sarbacane_blacklistid)) {
			$this->sarbacane_blacklistid = trim($this->sarbacane_blacklistid);
		}/* else {
			$this->sarbacane_blacklistid = 'DEFAULT_BLACKLIST';
		}*/
        if(isset($this->sarbacane_segmentid)) $this->sarbacane_segmentid = trim($this->sarbacane_segmentid);
        if(isset($this->sarbacane_sender_name)) $this->sarbacane_sender_name = trim($this->sarbacane_sender_name);

        // Check parameters
        // Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."sarbacane SET";

        $sql .= " entity=".$conf->entity.",";
        $sql .= " fk_mailing=".(isset($this->fk_mailing) ? $this->fk_mailing : "null").",";
        $sql .= " sarbacane_id=".(isset($this->sarbacane_id) ? "'".$this->sarbacane_id."'" : "null").",";
        $sql .= " sarbacane_webid=".(isset($this->sarbacane_webid) ? "'".$this->sarbacane_webid."'" : "null").",";
        $sql .= " sarbacane_listid=".(isset($this->sarbacane_listid) ? "'".$this->db->escape($this->sarbacane_listid)."'" : "null").",";
        $sql .= " sarbacane_blacklistid=".(!empty($this->sarbacane_blacklistid) ? "'".$this->db->escape($this->sarbacane_blacklistid)."'" : "NULL").",";
        $sql .= " sarbacane_segmentid=".(isset($this->sarbacane_segmentid) ? "'".$this->db->escape($this->sarbacane_segmentid)."'" : "null").",";
        $sql .= " sarbacane_sender_name=".(isset($this->sarbacane_sender_name) ? "'".$this->db->escape($this->sarbacane_sender_name)."'" : "null").",";

        $sql .= " fk_user_mod=".$user->id;

        $sql .= " WHERE rowid=".$this->id;

        $this->db->begin();

        dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if(! $resql) {
            $error++;
            $this->errors[] = "Error ".$this->db->lasterror();
        }

        if(! $error) {
            if(! $notrigger) {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action calls a trigger.

                // // Call triggers
                // include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
                // $interface=new Interfaces($this->db);
                // $result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
                // if ($result < 0) { $error++; $this->errors=$interface->errors; }
                // // End call triggers
            }
        }

        // Commit or rollback
        if($error) {
            foreach($this->errors as $errmsg) {
                dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
                $this->error .= ($this->error ? ', '.$errmsg : $errmsg);
            }
            $this->db->rollback();
            return -1 * $error;
        }
        else {
            $this->db->commit();
            return 1;
        }
    }

    /**
     * Delete object in database
     *
     * @param User $user      that deletes
     * @param int  $notrigger triggers after, 1=disable triggers
     * @return int <0 if KO, >0 if OK
     */
    public function delete($user, $notrigger = 0) {
        global $conf, $langs;
        $error = 0;

        $this->db->begin();

        if(! $error) {
            if(! $notrigger) {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action calls a trigger.

                // // Call triggers
                // include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
                // $interface=new Interfaces($this->db);
                // $result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
                // if ($result < 0) { $error++; $this->errors=$interface->errors; }
                // // End call triggers
            }
        }

        if(! $error) {
            $sql = "DELETE FROM ".MAIN_DB_PREFIX."sarbacane";
            $sql .= " WHERE rowid=".$this->id;

            dol_syslog(get_class($this)."::delete sql=".$sql);
            $resql = $this->db->query($sql);
            if(! $resql) {
                $error++;
                $this->errors[] = "Error ".$this->db->lasterror();
            }
        }

        //on supprime aussi la ligne liée à la campagne sarbacane dans la table sarbacane_campaign_contact
        if(! $error && !empty($this->sarbacane_id)) {
            $sql = "DELETE FROM ".MAIN_DB_PREFIX."sarbacane_campaign_contact";
            $sql .= " WHERE sarbacane_campaignid='".$this->sarbacane_id."'";

            dol_syslog(get_class($this)."::delete sql=".$sql);
            $resql = $this->db->query($sql);
            if(! $resql) {
                $error++;
                $this->errors[] = "Error ".$this->db->lasterror();
            }
        }

        // Commit or rollback
        if($error) {
            foreach($this->errors as $errmsg) {
                dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
                $this->error .= ($this->error ? ', '.$errmsg : $errmsg);
            }
            $this->db->rollback();
            return -1 * $error;
        }
        else {
            $this->db->commit();
            return 1;
        }
    }

    /**
     * @return int
     * @throws Exception
     */
    private function getInstanceSarbacane() {
        global $conf, $langs;

        if(! is_object($this->sarbacane)) {
            if(empty($conf->global->SARBACANE_API_KEY)) {
                $langs->load("sarbacane@sarbacane");
                $this->error = $langs->trans("SarbacaneAPIKeyNotSet");
                dol_syslog(get_class($this)."::getInstanceSarbacane ".$this->error, LOG_ERR);
                return -1;
            }

            $sarbacane = new Sarbacane('https://sarbacaneapis.com/v1', $conf->global->SARBACANE_API_KEY, $conf->global->SARBACANE_ACCOUNT_KEY);
            $this->sarbacane = $sarbacane;
        }

        return 1;
    }

    /**
     * Retreive Sarbacane Contact Lists
     *
     * @param array $filters
     * @return int <0 if KO, >0 if OK
     */
    function getListDestinaries($filters = array()) {
        $error = 0;

        $result = $this->getInstanceSarbacane();
        if($result > 0) {
            try {
                $response = $this->sarbacane->get('lists', $filters);
            }
            catch(Exception $e) {
                $this->error = $e->getMessage();
                $error++;
            }
            if(! empty($error)) {
                dol_syslog(get_class($this)."::getListDestinaries ".$this->error, LOG_ERR);
                return -1;
            }
            else $this->listdest_lines = $response;

            return 1;
        }
        else {
            dol_syslog(get_class($this)."::getListDestinaries ".$this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     * Retreive Sarbacane BlackLists
     *
     * @param array $filters
     * @return int <0 if KO, >0 if OK
     */
    public function getBlackLists($filters = array()) {
        $error = 0;

        $result = $this->getInstanceSarbacane();
        if($result > 0) {
            try {
                $response = $this->sarbacane->get('blacklists', $filters);
            }
            catch(Exception $e) {
                $this->error = $e->getMessage();
                $error++;
            }
            if(! empty($error)) {
                dol_syslog(get_class($this)."::getBlackLists ".$this->error, LOG_ERR);
                return -1;
            }
            else $this->blacklists_lines = $response;

            return 1;
        }
        else {
            dol_syslog(get_class($this)."::getBlackLists ".$this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     * Retraive email from sarbacane List
     *
     * @return int <0 if KO, >0 if OK
     */
    function getEmailList() {
        global $conf;
        $this->getInstanceSarbacane();
        $error = 0;

        $this->email_lines = array();
        try {
            $this->email_lines = $this->sarbacane->get('lists/'.$this->sarbacane_listid.'/contacts', array());
        }
        catch(Exception $e) {
            $this->error = $e->getMessage();
            dol_syslog(get_class($this)."::createSarbacaneCampaign ".$this->error, LOG_ERR);
            return -1;
        }
        if(empty($this->email_lines['message'])) {
            $emailsegment = 1;
        }
        else {
            $emailsegment = -1;
        }

        return $emailsegment;

    }

    /**
     * Retreive stats for sarbacane campaign
     *
	 * @param string $campaignId sarbacane campaign ID
     * @return int <0 if KO, >0 if OK
     */
    public function getCampaignRecipientStat($campaignId) {

        $this->getInstanceSarbacane();
		$this->CampaignRecipientStats = array();
        $error = 0;

			$offset = 0;

			while (1)
			{
				try {

					$campaignRecipientStats = $this->sarbacane->get('reports/' . $campaignId . '/recipients?offset=' . $offset, array());
					$this->CampaignRecipientStats = array_merge($this->CampaignRecipientStats, $campaignRecipientStats);
					if (count($campaignRecipientStats) < 1000) {
						break;
					} else {
						$offset += 1000;
						continue;
					}
				}
				catch(Exception $e) {
					$this->errors[] = $e->getMessage($campaignId);
					$error++;
					break;
				}


			}

		if (empty($error)) return 1;
        else return -1;
    }

	/**
	 * Retreive stats for sarbacane campaign
	 *
	 * @param string $campaignId sarbacane campaign ID
	 * @return int <0 if KO, >0 if OK
	 */
	function getCampaignStat($campaignId) {

		$this->getInstanceSarbacane();
		$this->CampaignStats = array();
		$error = 0;

		try {
			$this->CampaignStats = array_merge($this->CampaignStats, $this->sarbacane->get('reports/'.$campaignId , array()));
		}
		catch(Exception $e) {
			$this->errors[] = $e->getMessage($campaignId);
			$error++;
		}

		if (empty($error)) return 1;
		else return -1;
	}

	/**
	 * update stats for sarbacane campaigns recipients
	 *
	 * @param array $TCampaignId array of sarbacane campaign IDs
	 * @return int <0 if KO, >0 if OK
	 */
    public function updateCampaignRecipientStats($TCampaignId = array()) {

    	global $user;

    	$error = 0;

    	if (empty($TCampaignId))
		{
			$sql = "SELECT sarbacane_id FROM ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " WHERE date_format(datec, '%Y-%m-%d') > '".date('Y-m-d', strtotime('-3 month', dol_now()))."'";
			$resql = $this->db->query($sql);
			if ($resql)
			{
				while ($obj = $this->db->fetch_object($resql)) $TCampaignId[] = $obj->sarbacane_id;

				$this->db->free($resql);
			}
		}

		if (!empty($TCampaignId))
		{

			$nosendcomplet = false;	//on compte le nombre de destinataire pour qui l'envoi du mailing a échoué, si il y en a au moins 1, le statut de la campagne passe en "envoyée partiellement"

			foreach ($TCampaignId as $sarbacaneCampaignId)
			{
				try {

					//on récupère le mailing associé à la campagne sarbacane
					$sql = "SELECT fk_mailing, sarbacane_blacklistid FROM ".MAIN_DB_PREFIX.$this->table_element." WHERE sarbacane_id = '".$sarbacaneCampaignId."'";
					$resql = $this->db->query($sql);

					if($resql){
						$obj = $this->db->fetch_object($resql);
						$sarbacaneCampaign_fkmailing = $obj->fk_mailing;
						$this->sarbacane_blacklistid = $obj->sarbacane_blacklistid;
					}

					$res = $this->getCampaignRecipientStat($sarbacaneCampaignId);
					$res2 = $this->getCampaignStat($sarbacaneCampaignId);

					if ($res > 0  && !empty($this->CampaignRecipientStats))
					{
						foreach ($this->CampaignRecipientStats as $campaignStat)
						{
							$campaignContact = new DolSarbacaneTargetLine($this->db);
							$ret = $campaignContact->fetchBySarbacaneContactCampaignId($campaignStat['recipient']['fields']['CONTACT_ID']);
							if ($ret > 0)
							{
								$TNPAIContacts = $this->getNPAIContactEmail();
								$campaignContact->nb_open = $campaignStat['opens'];
								$campaignContact->nb_click = $campaignStat['clicks'];
								$campaignContact->unsubscribe = $campaignStat['unsubscribe'];
								//si l'adresse mail est dans la liste des adresses NPAI, on annulé le success renvoyé par sarbacane (hack de la mort qui tue) et on note l'adresse en npai de la target de la campagne
								if(!empty($TNPAIContacts)){
									foreach ($TNPAIContacts as $bounce){
										if($bounce == $campaignStat['recipient']['email']){
											$campaignContact->npai = $campaignStat['recipient']['email'];
											$campaignStat['success'] = false;
											break;
										}
									}
								}
								if ($campaignStat['unsubscribe'] == true)
								{
									$campaignContact->unsubscribed_email = $campaignStat['recipient']['email'];
									$campaignContact->used_blacklist = $this->sarbacane_blacklistid;
									if (empty($campaignContact->used_blacklist)){
										$campaignContact->used_blacklist = 'DEFAULT_BLACKLIST';
									}
								}
								$campaignContact->statut = ($campaignContact->nb_open > 0 && empty($campaignContact->unsubscribe)) ? 1 : 0;

								$ret = $campaignContact->update($user);
								if ($ret > 0)
								{
									if (!empty($campaignContact->npai))
									{
										//si npai alors màj statut du destinataire au statut "error"
										$sql = "UPDATE " . MAIN_DB_PREFIX . "mailing_cibles SET statut = '-1' WHERE email ='" . $campaignStat['recipient']['email'] . "' AND fk_mailing =" . ((int)$sarbacaneCampaign_fkmailing);
										$this->db->query($sql);

										if ($campaignContact->npai == $campaignContact->contact->email)
										{
											$campaignContact->contact->array_options['options_sarb_npai'] = true;
											$campaignContact->contact->insertExtraFields();
										}
									}
								}
							}

							//si success alors màj du statut "envoyé" sinon on note que la campagne n'a pas été envoyé complètement
							if ($campaignStat['success'] == true) {
								$sql = "UPDATE " . MAIN_DB_PREFIX . "mailing_cibles SET statut = 1 WHERE email ='" . $campaignStat['recipient']['email'] . "' AND fk_mailing =" . ((int)$sarbacaneCampaign_fkmailing);
								$this->db->query($sql);

								if (!$resql) {
									$this->errors = $this->db->lastqueryerror();
									$error++;
								}
							} else {
								$nosendcomplet = true;
							}
						}
					}

					if($res2 > 0 && !empty($this->CampaignStats)){

						//on vérifie que le nombre de destinataires du mailing est bien égal au nombre de destinataires de la campagne Sarbacane
						//si ce n'est pas le cas, cela veut dire qu'une ou plusieurs adresses mails ont été exclues de l'envoie sur sarbacane (npai identifiée) et on ne peut donc pas considérer la campagne comme envoyée complètement
						$sql = "SELECT COUNT(rowid) as nbDest FROM " . MAIN_DB_PREFIX . "mailing_cibles WHERE fk_mailing =" . ((int)$sarbacaneCampaign_fkmailing);
						$resql = $this->db->query($sql);

						if($resql) {
							$obj = $this->db->fetch_object($resql);

							if ($obj->nbDest > count($this->CampaignRecipientStats)) {
								$nosendcomplet = true;
							}
						} else {
							$this->errors = $this->db->lastqueryerror();
							$error++;
						}

						//màj du statut de la campagne : si $nosendcomplet est à true, c'est envoyé partiellement
						foreach ($this->CampaignStats as $campaignStat) {

							$sql = "UPDATE " . MAIN_DB_PREFIX . "mailing SET date_envoi = '" . dol_print_date($campaignStat['date'], '%Y-%m-%d %H:%M:%S') . "', statut ='" . (($nosendcomplet == false) ? '3' : '2') . "'WHERE rowid=" . ((int)$sarbacaneCampaign_fkmailing);
							$resql = $this->db->query($sql);

							if (!$resql) {
								$this->errors = $this->db->lastqueryerror();
								$error++;
							}
						}
					}

				}
				catch(Exception $e) {
					$this->errors[] = $e->getMessage($sarbacaneCampaignId);
					$error++;
				}
			}
		}

    	if (empty($error)) return 1;
        else return -1;
	}

	/**
	 * Method to be called by dolibarrCron
	 *
	 * @return int 0 everything OK, 1 = error
	 */
	public function CRONupdateCampaignRecipientStats()
	{
		$a = microtime(true);

		$this->output = '';
		try {
			$ret = $this->updateCampaignRecipientStats();
		}
		catch(Exception $e) {
			//dol_syslog();
			$this->output .= 'CRON exécution has stopped because of an error';
			return 1;	// Error
		}

		$b = microtime(true);
		$this->output .= 'Execution time: '.($b-$a);

		if ($ret > 0) return 0;
		else return 1;
	}

    /**
     * Check if sender mail is already a validated sender
     *
     * @param string $mail_sender use to send mails
     * @return int <0 if KO, >0 if OK
     */
    function checkMailSender($mail_sender = '') {
        if(empty($mail_sender) && ! isValidEmail($mail_sender)) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * Add email to list
     *
     * @param int   $listid      to add
     * @param array $array_email add
     * @return int <0 if KO, >0 if OK
     */
    function addEmailToList($listid = 0, $array_email = array()) {
        global $conf, $db;

        require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
        require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
        require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

        $extrafields_societe = new ExtraFields($db);
        $extrafields_societe->fetch_name_optionals_label('societe');
        $extrafields_contact = new ExtraFields($db);
        $extrafields_contact->fetch_name_optionals_label('socpeople');

        $error = 0;

        if(empty($listid)) {
            $this->error = 'listid is mandatory';
            dol_syslog(get_class($this)."::addEmailToList ".$this->error, LOG_ERR);
            return -1;
        }
        if(count($array_email) == 0) {
            $this->error = '$array_email is empty';
            dol_syslog(get_class($this)."::addEmailToList ".$this->error, LOG_ERR);
            return -1;
        }

        $result = $this->getInstanceSarbacane();
        if($result < 0) {
            dol_syslog(get_class($this)."::getListDestinaries ".$this->error, LOG_ERR);
            return -1;
        }

        $email_added = array();
        $email_to_add = array();

        dol_syslog(get_class($this).'::addEmailToList count($$array_email)='.count($array_email), LOG_DEBUG);

        $TExtSociete = explode(',', $conf->global->SARBACANE_EXTRAFIELDS_SOCIETE_ALLOWED);
        $TExtContact = explode(',', $conf->global->SARBACANE_EXTRAFIELDS_CONTACT_ALLOWED);

        foreach($array_email as $email) {

            // email is formated like email&type&id where type=contact for contact or thirdparty and id is the id of contact or thridparty
            $tmp_array = explode('&', $email);
            $merge_vars = new stdClass();

            if(! empty($tmp_array[0]) && isValidEmail($tmp_array[0]) && ! in_array($tmp_array[0], $email_added)) {

            	if ($tmp_array[1] == 'contact' || strtolower($tmp_array[1]) == strtolower('DistributionList')) {
            		$contactstatic = new Contact($this->db);
            		$result = $contactstatic->fetch($tmp_array[3]);

            		if ($result < 0) {
            			$this->error = $contactstatic->error;
            			dol_syslog(get_class($this) . "::getListDestinaries " . $this->error, LOG_ERR);
            			return -1;
            		}
            		if (!empty($contactstatic->id)) {
            			$merge_vars->FNAME = $contactstatic->firstname;
            			$merge_vars->LNAME = $contactstatic->lastname;
            			$merge_vars->CIVILITY = $contactstatic->civility;
            			$merge_vars->EMAIL = $tmp_array[0];
            		}
            	}
            	if ($tmp_array[1] == 'thirdparty') {
            		$socstatic = new Societe($this->db);
            		$result = $socstatic->fetch($tmp_array[2]);
            		if ($result < 0) {
            			$this->error = $socstatic->error;
            			dol_syslog(get_class($this) . "::getListDestinaries " . $this->error, LOG_ERR);
            			return -1;
            		}
            		if (!empty($socstatic->id)) {
            			$merge_vars->FNAME = $socstatic->name;
            			$merge_vars->EMAIL = $tmp_array[0];
            		}
            	}

            	if($tmp_array[1] == 'file'){
            		if(!empty($tmp_array[5])) $merge_vars->FNAME = $tmp_array[5];
					if(!empty($tmp_array[4])) $merge_vars->LNAME = $tmp_array[4];
					if(!empty($tmp_array[0])) $merge_vars->EMAIL = $tmp_array[0];
				}

                dol_syslog(get_class($this)."::addEmailToList listid=".$listid." merge_vars=".var_export($merge_vars, true).' $tmp_array[0]='.$tmp_array[0], LOG_DEBUG);

                // Add only on time the email
                $email_added[] = $tmp_array[0];

                $email_to_add[] = array(
                    'email_address' => $tmp_array[0],
                    'status' => 'subscribed',
                    'email_type' => 'html',
                    'tmp_array' => $tmp_array,
                    'merge_vars' => $merge_vars
                );
            }
        }


        dol_syslog(get_class($this).'::addEmailToList var_export($email_to_add)='.var_export($email_to_add, true), LOG_DEBUG);
        dol_syslog(get_class($this).'::addEmailToList count($email_to_add)='.count($email_to_add), LOG_DEBUG);
        $batch_email_to_add_error = array();

        $add_count = 0;

        foreach($email_to_add as $email) {
            // Call

            if(! empty($email)) {

				try {
					$data = array(
                        "email" => $email['email_address'],
                        "phone" => ""
                    );
                    if($email['tmp_array'][1] == 'contact' || strtolower($email['tmp_array'][1]) == strtolower('DistributionList') || $email['tmp_array'][1] == 'file') {
                        $found = 0;

                        $civ_id = 'CIVILITY_ID';
                        $name_id = 'LASTNAME_ID';
                        $firstname_id = 'FIRSTNAME_ID';

                        $TSarbacaneFields = $this->sarbacane->get('lists/'.$listid.'/fields', array());
                        if(! empty($TSarbacaneFields)) {
                            foreach($TSarbacaneFields['fields'] as $field) {
                                if($field['caption'] == 'Civilité') $civ_id = $field['id'];
                                if($field['caption'] == 'Nom') $name_id = $field['id'];
                                if($field['caption'] == 'Prénom') $firstname_id = $field['id'];
                            }
                        }

                        $data[$civ_id] = $email['merge_vars']->CIVILITY;
                        $data[$name_id] = $email['merge_vars']->LNAME;
                        $data[$firstname_id] = $email['merge_vars']->FNAME;

                        $TSarbacaneIds = $this->getSarbacaneContactIdByListId($email['tmp_array'][3], $listid);
                        $TSarbacaneContacts = $this->sarbacane->get('lists/'.$listid.'/contacts', array());
                        foreach($TSarbacaneContacts as $contact) {
                            if(in_array($contact['id'], $TSarbacaneIds)) $found = 1;
                        }
                        if(empty($TSarbacaneIds) || ! $found) {
                            $response = $this->sarbacane->post('lists/'.$listid.'/contacts', $data);
                            if(! empty($response[0])) {
                                $ret = $this->upsertListContact($listid, $response[0], $email['tmp_array'][2]);
                                if($ret < 0) $error++;
                            }
                        }
                        else {
                            foreach($TSarbacaneIds as $fk_sarbacane) $response = $this->sarbacane->put('lists/'.$listid.'/contacts/'.$fk_sarbacane, $data);
                        }
                    }
                    else $response = $this->sarbacane->post('lists/'.$listid.'/contacts/upsert', $data);
                }
                catch(Exception $e) {
                    $this->errors[] = $e->getMessage();
                    $error++;
                }
            }
        }

        if($error) {
            foreach($this->errors as $errmsg) {
                dol_syslog(get_class($this)."::addEmailToList Error".$errmsg, LOG_ERR);
                $this->error .= ($this->error ? ', '.$errmsg : $errmsg);
            }
            return -1;
        }
        else {
            return count($email_added);
        }
    }

    /**
     * @param $fk_contact
     * @param $listid
     * @return array
     */
    public function getSarbacaneContactIdByListId($fk_contact, $listid) {
        $TIds = array();
        $sql = 'SELECT sarbacane_contactlistid FROM '.MAIN_DB_PREFIX.$this::$list_contact_table.' WHERE fk_contact='.$fk_contact.' AND sarbacane_listid="'.$listid.'"';

        $resql = $this->db->query($sql);
        if(! empty($resql) && $this->db->num_rows($resql) > 0) {
            while($obj = $this->db->fetch_object($resql)) {
                $TIds[] = $obj->sarbacane_contactlistid;
            }
        }

        return $TIds;
    }

    /**
     * @param $listid
     * @param $sarbacane_contactid
     * @param $contactid
     * @return float|int
     * @throws Exception
     */
    public function upsertListContact($listid, $sarbacane_contactid, $contactid) {
        global $user;
        $error = 0;

        $sql = 'DELETE FROM '.MAIN_DB_PREFIX.$this::$list_contact_table.' WHERE fk_contact='.$contactid.' AND sarbacane_listid="'.$listid.'";';
        $resql = $this->db->query($sql);

        $sql = ' INSERT INTO '.MAIN_DB_PREFIX.$this::$list_contact_table.' (fk_contact, sarbacane_listid, sarbacane_contactlistid, fk_user_author, datec, fk_user_mod)
                VALUES ('.$contactid.', "'.$listid.'","'.$sarbacane_contactid.'",'.$user->id.',NOW(),'.$user->id.');';
        $this->db->begin();

        dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if(! $resql) {
            $error++;
            $this->errors[] = "Error ".$this->db->lasterror();
        }

        if(! $error) {
            $id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this::$list_contact_table);

            // Commit or rollback
            if($error) {
                foreach($this->errors as $errmsg) {
                    dol_syslog(get_class($this)."::upsertListContact ".$errmsg, LOG_ERR);
                    $this->error .= ($this->error ? ', '.$errmsg : $errmsg);
                }
                $this->db->rollback();
                return -1 * $error;
            }
            else {
                $this->db->commit();
                return $id;
            }
        }
        return -2;
    }

    /**
     * Change mail adresses to lower case
     *
     * @param array $TMail
     */
    static function toLowerCase(&$TMail) {
        if(! empty($TMail)) {
            foreach($TMail as &$email) $email = strtolower($email);
        }
    }

    /**
     * @param $user
     * @return int
     * @throws Exception
     */
    function createSarbacaneCampaign($user) {
        $result = $this->getInstanceSarbacane();
        if($result < 0) {
            dol_syslog(get_class($this)."::createSarbacaneCampaign ".$this->error, LOG_ERR);
            return -1;
        }
        $data = array(
            "aliasFrom" => $this->sarbacane_sender_name,
            "aliasReplyTo" => $this->sarbacane_sender_name,
            "name" => $this->currentmailing->titre,
            "listid" => array($this->sarbacane_listid),
            "subject" => $this->currentmailing->sujet,
            "emailFrom" => $this->currentmailing->email_from,
            "emailReplyTo" => $this->currentmailing->email_from
        );

        if(empty($this->sarbacane_id)) {
            try {

                $response = $this->sarbacane->post('campaigns/email', $data);
                if(empty($response['id'])) {

                    $this->error = $response['message'];
                    return -1;
                }
                $this->sarbacane_id = $response['id'];
                $this->sarbacane_webid = $this->sarbacane_id;
                //Import de la liste dans la campagne
                $this->sarbacane->post('/campaigns/'.$this->sarbacane_id.'/list', array("listId" => $this->sarbacane_listid));

                // selection de la liste noire associée
				if (!empty($this->sarbacane_blacklistid) && $this->sarbacane_blacklistid !== "DEFAULT_BLACKLIST")
				{
					$this->sarbacane->post('/campaigns/'.$this->sarbacane_id.'/blacklists', array("blacklistIds" => array($this->sarbacane_blacklistid)));
				}

                //Ajout du contenu html
                $response = $this->sarbacane->get('/campaigns/'.$this->sarbacane_id, array());
                $sendId = $response['campaign']['sends'][0];
                $this->sarbacane->post('/campaigns/'.$this->sarbacane_id.'/send/'.$sendId.'/content', array("html" => $this->currentmailing->body.'<br><a href="{{unsubscribe}}">D&eacute;sinscription</a>'));

                //Liaison campagne contact id & contact dolibarr
                $TRecipient = $this->sarbacane->get('/campaigns/'.$this->sarbacane_id.'/recipients', array());
                $this->getEmailMailingDolibarr('toadd');
                if(!empty($TRecipient)) {
                    foreach($TRecipient as $recipient) {
                        $fk_contact = $this->getContactDolibarrIdByMail($recipient['email']);
                        if(!empty($fk_contact))
						{
							$this->upsertCampaignContact($recipient['id'], $fk_contact);
							// fix list_contact pas mis à jour à la création
							$this->upsertListContact($this->sarbacane_listid, $recipient['id'], $fk_contact);
						}
                    }
                }
            }
            catch(Exception $e) {
                $this->error = $e->getMessage();
                dol_syslog(get_class($this)."::createSarbacaneCampaign ".$this->error, LOG_ERR);
                return -1;
            }

            $result = $this->update($user);
            if($result < 0) {
                return -1;
            }
        }

        return 1;
    }

    /**
     * @param $email
     * @return int|mixed|string
     */
    public function getContactDolibarrIdByMail ($email) {
        if(!empty($this->email_lines)){
        	foreach($this->email_lines as $email_line) {
                $tmp_array = explode('&', $email_line);
                if($tmp_array[0] == $email && $tmp_array[1] == 'contact') return $tmp_array[2];
                if($tmp_array[0] == $email && strtolower($tmp_array[1]) == strtolower('DistributionList')) return $tmp_array[3];
            }
        }

        return 0;
    }

    /**
     * @param $sarbacane_contactid
     * @param $contactid
     * @return float|int
     * @throws Exception
     */
    public function upsertCampaignContact($sarbacane_contactid, $contactid) {
        global $user;
        $error = 0;

        $sql = 'DELETE FROM '.MAIN_DB_PREFIX.$this::$campaign_contact_table.' WHERE fk_contact='.$contactid.' AND sarbacane_campaignid="'.$this->sarbacane_id.'";';
        $resql = $this->db->query($sql);

        $sql = ' INSERT INTO '.MAIN_DB_PREFIX.$this::$campaign_contact_table.' (fk_contact, sarbacane_campaignid, sarbacane_contactcampaignid, fk_user_author, datec, fk_user_mod)
                VALUES ('.$contactid.', "'.$this->sarbacane_id.'","'.$sarbacane_contactid.'",'.$user->id.',NOW(),'.$user->id.');';

        $this->db->begin();

        dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);

        if(! $resql) {
            $error++;
            $this->errors[] = "Error ".$this->db->lasterror();
        }

        if(! $error) {
            $id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this::$campaign_contact_table);

            // Commit or rollback
            if($error) {
                foreach($this->errors as $errmsg) {
                    dol_syslog(get_class($this)."::upsertCampaignContact ".$errmsg, LOG_ERR);
                    $this->error .= ($this->error ? ', '.$errmsg : $errmsg);
                }
                $this->db->rollback();
                return -1 * $error;
            }
            else {
                $this->db->commit();
                return $id;
            }
        }
        return -2;
    }

    /**
     * @param $namelist
     * @return int|mixed
     * @throws Exception
     */
    function createList($namelist) {
        if(empty($this->sarbacane)) {
            $result = $this->getInstanceSarbacane();
            if($result < 0) {
                dol_syslog(get_class($this)."::getListDestinaries ".$this->error, LOG_ERR);
                return -1;
            }
        }
        try {
            $response = $this->sarbacane->post('lists', array('name' => $namelist));
            if(empty($response['id'])) {
                $this->error = $response['message'];
                $this->errors[] = $this->error;
                return -1;
            }
            $listid = $response['id'];

            $response = $this->sarbacane->post('lists/'.$listid.'/fields', array('kind' => 'RADIO', 'caption' => 'Civilité'));
            $response = $this->sarbacane->post('lists/'.$listid.'/fields', array('kind' => 'STRING', 'caption' => 'Prénom'));
            $response = $this->sarbacane->post('lists/'.$listid.'/fields', array('kind' => 'STRING', 'caption' => 'Nom'));
        }
        catch(Exception $e) {
            $this->error = $e->getMessage();
            dol_syslog(get_class($this)."::createSarbacaneCampaign ".$this->error, LOG_ERR);
            return -1;
        }


        return $listid;
    }

    /**
     * get Sarbacane campaign status
     *
     * @param string $status Status to convert
     * @param int    $mode   1 with picto, 0 only text
     * @return String status
     */
    static function getLibStatus($status, $mode = 1) {
        global $langs;

        $langs->load("sarbacane@sarbacane");

        if($mode == 0) {
            return $langs->trans('Sarbacane'.$status);
        }
        if($mode == 1) {

            if($status == 'save' || $status == 'Draft') {
                return img_picto($langs->trans($status), 'stcomm0').' '.$langs->trans($status);
            }
            if($status == 'paused') {
                return img_picto($langs->trans('Sarbacane'.$status), 'stcomm1_grayed').' '.$langs->trans('Sarbacane'.$status);
            }
            if($status == 'schedule') {
                return img_picto($langs->trans('Sarbacane'.$status), 'stcomm0_grayed').' '.$langs->trans('Sarbacane'.$status);
            }
            if($status == 'sent' || $status == 'Sent') {
                return img_picto($langs->trans($status), 'stcomm3').' '.$langs->trans($status);
            }
            if($status == 'Sent and Archived') {
                return img_picto($langs->trans($status), 'stcomm3').' '.$langs->trans($status);
            }
            if($status == 'sending') {
                return img_picto($langs->trans('Sarbacane'.$status), 'stcomm2').' '.$langs->trans('Sarbacane'.$status);
            }
            if($status == 'Scheduled') {
                return img_picto($langs->trans('Sarbacane'.$status), 'stcomm2').' '.$langs->trans('Sarbacane'.$status);
            }
        }
    }

    /**
     * get Sarbacane campaign status
     *
     * @param int $mode 1 with picto, 0 only text
     * @return string status
     */
    function getSarbacaneCampaignStatus($mode = 1) {
//        $result = $this->getInstanceSarbacane();
//        if($result < 0) {
//            dol_syslog(get_class($this)."::getSarbacaneCampaignStatus ".$this->error, LOG_ERR);
//            return -1;
//        }
//
//        $opts['campaign_id'] = $this->sarbacane_id;
//        // Call
//        try {
//            $response = $this->sarbacane->get_campaign_v2(array("id" => $this->sarbacane_id));
//            $this->sarbacane_webid = $response;
//        }
//        catch(Exception $e) {
//            $this->error = $e->getMessage();
//            dol_syslog(get_class($this)."::getListCampaign ".$this->error, LOG_ERR);
//            return -1;
//        }
//        if($mode == 1) {
//            return DolSarbacane::getLibStatus($response['data'][0]['status']);
//        }
//        else if($mode == 0) {
//            return $response['data'][0]['status'];
//        }
    }

    /**
     * Send Sarbacane campaign
     *
     * @return int <0 if KO, >0 if OK
     */
    function sendSarbacaneCampaign() {
        $result = $this->getInstanceSarbacane();
        if($result < 0) {
            dol_syslog(get_class($this)."::sendSarbacaneCampaign ".$this->error, LOG_ERR);
            return -1;
        }

        try {
            $this->sarbacane->post('/campaigns/'.$this->sarbacane_id.'/send', array());
        }
        catch(Exception $e) {
            $this->error = $e->getMessage();
            dol_syslog(get_class($this)."::sendSarbacaneCampaign ".$this->error, LOG_ERR);
            return -1;
        }

        return 1;
    }

    /**
     * Get dolibarr destinaries email
     *
     * @param string $returntype populate email_lines with only email, 'toadd' for 'email&type&id'
     * @return int <0 if KO, >0 if OK
     */
    function getEmailMailingDolibarr($returntype = 'simple') {
        global $conf;
        $this->email_lines = array();

        $sql = "SELECT mc.lastname, mc.firstname, mc.email,mc.source_type,mc.source_id,mc.fk_contact";
        $sql .= " FROM ".MAIN_DB_PREFIX."mailing_cibles as mc";
        $sql .= " WHERE mc.fk_mailing=".$this->fk_mailing;

        dol_syslog(get_class($this)."::getEmailMailingDolibarr sql=".$sql);
        $result = $this->db->query($sql);
        if($result) {
            if($this->db->num_rows($result)) {
                while($obj = $this->db->fetch_object($result)) {
                    if($returntype == 'simple') {
                        $this->email_lines[] = strtolower($obj->email);
                    }
                    else if($returntype == 'toadd') {
                        $this->email_lines[] = strtolower($obj->email).'&'.$obj->source_type.'&'.$obj->source_id.'&'.$obj->fk_contact.'&'.$obj->lastname.'&'.$obj->firstname;
                    }
                }
            }
        }
        else {
            $this->error = "Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::getEmailMailingDolibarr	 ".$this->error, LOG_ERR);
            return -1;
        }

        return 1;
    }

    /**
     * Import into dolibarr email
     *
     * @return int <0 if KO, >0 if OK
     */
    function importSegmentDestToDolibarr($segment_id) {
        global $conf;

        $error = 0;
        $insertcible = 0;

        $this->db->begin();

        $sql = 'DELETE FROM '.MAIN_DB_PREFIX.'mailing_cibles WHERE fk_mailing='.$this->fk_mailing;
        dol_syslog(get_class($this)."::importSegmentDestToDolibarr sql=".$sql, LOG_DEBUG);
        $result = $this->db->query($sql);
        if(! $result) {
            $this->error = "Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::importSegmentDestToDolibarr ".$this->error, LOG_ERR);
            return -1;
        }

        $this->sarbacane_segmentid = $segment_id;
        $this->getInstanceSarbacane();
        $result = $this->getEmailList();

        if($result > 0) {
            // Try to find for each email if it is already into dolibarr as thirdparty or contact
            foreach($this->email_lines as $email) {
                $sql = 'SELECT rowid,nom from '.MAIN_DB_PREFIX.'societe WHERE email=\''.$email['email'].'\'';
                dol_syslog(get_class($this)."::importSegmentDestToDolibarr sql=".$sql, LOG_DEBUG);
                $result = $this->db->query($sql);
                if($result) {
                    if($this->db->num_rows($result)) {
                        $obj = $this->db->fetch_object($result);

                        $url = '<a href="'.DOL_URL_ROOT.'/societe/soc.php?socid='.$obj->rowid.'"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/object_company.png" border="0" alt="" title=""></a>';

                        $sqlinsert = 'INSERT INTO '.MAIN_DB_PREFIX.'mailing_cibles (fk_mailing,fk_contact,lastname,email,statut,source_url,source_id,source_type)';
                        $sqlinsert .= 'VALUES ('.$this->fk_mailing.',0,\''.$this->db->escape($obj->nom).'\',\''.$email['email'].'\',0,\''.$url.'\','.$obj->rowid.',\'thirdparty\')';
                    }
                    $this->db->free($result);
                }
                $sql = 'SELECT rowid,lastname,firstname from '.MAIN_DB_PREFIX.'socpeople WHERE email=\''.$email['email'].'\'';
                dol_syslog(get_class($this)."::importSegmentDestToDolibarr sql=".$sql, LOG_DEBUG);
                $result = $this->db->query($sql);
                if($result) {
                    if($this->db->num_rows($result)) {
                        $obj = $this->db->fetch_object($result);

                        $url = '<a href="'.DOL_URL_ROOT.'/contact/card.php?id='.$obj->rowid.'"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/object_contact.png" border="0" alt="" title=""></a>';

                        $sqlinsert = 'INSERT INTO '.MAIN_DB_PREFIX.'mailing_cibles (fk_mailing,fk_contact,lastname,firstname,email,statut,source_url,source_id,source_type)';
                        $sqlinsert .= 'VALUES ('.$this->fk_mailing.','.$obj->rowid.',\''.$this->db->escape($obj->lastname).'\',\''.$this->db->escape($obj->firstname).'\',\''.$email['email'].'\',0,\''.$url.'\','.$obj->rowid.',\'contact\')';
                    }
                    $this->db->free($result);
                }

                // If not found, no matter into email wihtout thirdparty/contact link
                if(empty($sqlinsert)) {
                    $sqlinsert = 'INSERT INTO '.MAIN_DB_PREFIX.'mailing_cibles (fk_mailing,fk_contact,lastname,firstname,email,statut,source_url,source_id,source_type)';
                    $sqlinsert .= 'VALUES ('.$this->fk_mailing.',0,\'\',\'\',\''.$email['email'].'\',0,\'\',NULL,\'file\')';
                }

                if(! empty($sqlinsert)) {
                    dol_syslog(get_class($this)."::importSegmentDestToDolibarr sqlinsert=".$sqlinsert, LOG_DEBUG);
                    $result = $this->db->query($sqlinsert);
                    $insertcible++;
                    if(! $result) {
                        $this->errors[] = "Error ".$this->db->lasterror();
                        $error++;
                    }
                }

                if(! empty($insertcible)) {
                    $sql = 'UPDATE '.MAIN_DB_PREFIX.'mailing SET nbemail='.$insertcible.' WHERE rowid='.$this->fk_mailing;
                    dol_syslog(get_class($this)."::importSegmentDestToDolibarr sql=".$sql, LOG_DEBUG);
                    $result = $this->db->query($sql);
                    if(! $result) {
                        $this->errors[] = "Error ".$this->db->lasterror();
                        $error++;
                    }
                }

                $sqlinsert = '';
            }

            // Commit or rollback
            if($error) {
                foreach($this->errors as $errmsg) {
                    dol_syslog(get_class($this)."::importSegmentDestToDolibarr ".$errmsg, LOG_ERR);
                    $this->error .= ($this->error ? ', '.$errmsg : $errmsg);
                }
                $this->db->rollback();
                return -1 * $error;
            }
            else {
                $this->db->commit();
                return 1;
            }
        }
        else {
            return -1;
        }
    }

    /**
     * Export to list and segments sarbacane only segment from dolibarr email
     *
     * @param int    $segmentid      segment id
     * @param string $newsegmentname segment name
     * @param int    $resetseg       segment
     * @return int <0 if KO, >0 if OK
     */
    function exportDesttoSarbacane() {
        global $conf;

        $result = $this->getEmailMailingDolibarr('toadd');
        if($result < 0) {
            return -1;
        }

        if(!empty($conf->global->SARBACANE_EXPORT_EMPTYLIST)) {
			$this->getInstanceSarbacane();
            try {
                $this->sarbacane->post('lists/'.$this->sarbacane_listid.'/empty', '');
            }
            catch(Exception $e) {
                $this->error = $e->getMessage();
                dol_syslog(get_class($this)."::exportDesttoSarbacane ".$this->error, LOG_ERR);
                return -1;
            }
        }


        if(count($this->email_lines)) {

            $result_add_to_list = $this->addEmailToList($this->sarbacane_listid, $this->email_lines);
        }

        if($result_add_to_list < 0) {
            return -2;
        }
        else {
            return 1;
        }
    }

    /**
     * Export to sarbacane only segment from dolibarr email
     *
     * @param int    $segmentid      segment id
     * @param string $newsegmentname segment name
     * @param int    $resetseg       segment
     * @return int <0 if KO, >0 if OK
     */
    /*function exportSegmentOnlyDesttoSarbacane($segmentid, $newsegmentname, $resetseg = 0) {
        global $conf;

        $result = $this->getEmailMailingDolibarr('toadd');
        if ($result < 0) {
            return - 1;
        }
        if (count($this->email_lines)) {

            $this->sarbacane_segmentid = $segmentid;

            $result = $this->updateList($this->sarbacane_listid,  $this->email_lines, $resetseg);
            if ($result < 0) {
                return - 1;
            }
        }

        if ($result < 0) {
            return - 2;
        } else {
            return 1;
        }
    }*/

    /**
     * Renvoi le libelle d'un statut donne
     *
     * @param int $statut statut
     * @param int $mode   long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
     * @return string Label
     */
    static public function libStatutDest($statut, $mode = 0) {
        global $langs;
        $langs->load('sarbacane@sarbacane');

        if($mode == 0) {
            if($statut == -1) return $langs->trans("MailingStatusError");
            if($statut == 0) return $langs->trans("MailingStatusNotSent");
            if($statut == 1) return $langs->trans("MailingStatusSent");
            if($statut == 2) return $langs->trans("SarbacaneStatusOpen");
            if($statut == 3) return $langs->trans("SarbacaneUnsucscribe");
            if($statut == 4) return $langs->trans("SarbacaneClick");
            if($statut == 5) return $langs->trans("SarbacaneHardBounce");
            if($statut == 6) return $langs->trans("SarbacaneSoftBounce");
        }
        if($mode == 1) {
            if($statut == -1) return $langs->trans("MailingStatusError");
            if($statut == 0) return $langs->trans("MailingStatusNotSent");
            if($statut == 1) return $langs->trans("MailingStatusSent");
            if($statut == 2) return $langs->trans("SarbacaneOpen");
            if($statut == 3) return $langs->trans("SarbacaneUnsucscribe");
            if($statut == 4) return $langs->trans("SarbacaneClick");
            if($statut == 5) return $langs->trans("SarbacaneHardBounce");
            if($statut == 6) return $langs->trans("SarbacaneSoftBounce");
        }
        if($mode == 2) {
            if($statut == -1) return $langs->trans("MailingStatusError").' '.img_error();
            if($statut == 0) return $langs->trans("MailingStatusNotSent");
            if($statut == 1) return $langs->trans("MailingStatusSent").' '.img_picto($langs->trans("MailingStatusSent"), 'statut4');
            if($statut == 2) return $langs->trans("SarbacaneOpen").' '.img_picto($langs->trans("MailingStatusRead"), 'statut6');
            if($statut == 3) return $langs->trans("SarbacaneUnsucscribe").' '.img_picto($langs->trans("SarbacaneUnsucscribe"), 'statut8');
            if($statut == 4) return $langs->trans("SarbacaneClick").' '.img_picto($langs->trans("SarbacaneClick"), 'statut6');
            if($statut == 5) return $langs->trans("SarbacaneHardBounce").' '.img_error();
            if($statut == 6) return $langs->trans("SarbacaneSoftBounce").' '.img_error();
        }
        if($mode == 3) {
            if($statut == -1) return $langs->trans("MailingStatusError").' '.img_error();
            if($statut == 0) return $langs->trans("MailingStatusNotSent");
            if($statut == 1) return $langs->trans("MailingStatusSent").' '.img_picto($langs->trans("MailingStatusSent"), 'statut4');
            if($statut == 2) return $langs->trans("SarbacaneOpen").' '.img_picto($langs->trans("MailingStatusRead"), 'statut6');
            if($statut == 3) return $langs->trans("SarbacaneUnsucscribe").' '.img_picto($langs->trans("SarbacaneUnsucscribe"), 'statut8');
            if($statut == 4) return $langs->trans("SarbacaneClick").' '.img_picto($langs->trans("SarbacaneClick"), 'statut6');
            if($statut == 5) return $langs->trans("SarbacaneHardBounce").' '.img_error();
            if($statut == 6) return $langs->trans("SarbacaneSoftBounce").' '.img_error();
        }
        if($mode == 4) {
            if($statut == -1) return $langs->trans("MailingStatusError").' '.img_error();
            if($statut == 0) return $langs->trans("MailingStatusNotSent");
            if($statut == 1) return $langs->trans("MailingStatusSent").' '.img_picto($langs->trans("MailingStatusSent"), 'statut4');
            if($statut == 2) return $langs->trans("SarbacaneOpen").' '.img_picto($langs->trans("MailingStatusRead"), 'statut6');
            if($statut == 3) return $langs->trans("SarbacaneUnsucscribe").' '.img_picto($langs->trans("SarbacaneUnsucscribe"), 'statut8');
            if($statut == 4) return $langs->trans("SarbacaneClick").' '.img_picto($langs->trans("SarbacaneClick"), 'statut6');
            if($statut == 5) return $langs->trans("SarbacaneHardBounce").' '.img_error();
            if($statut == 6) return $langs->trans("SarbacaneSoftBounce").' '.img_error();
        }
        if($mode == 5) {
            if($statut == -1) return $langs->trans("MailingStatusError").' '.img_error();
            if($statut == 0) return $langs->trans("MailingStatusNotSent");
            if($statut == 1) return $langs->trans("MailingStatusSent").' '.img_picto($langs->trans("MailingStatusSent"), 'statut4');
            if($statut == 2) return $langs->trans("SarbacaneOpen").' '.img_picto($langs->trans("MailingStatusRead"), 'statut6');
            if($statut == 3) return $langs->trans("SarbacaneUnsucscribe").' '.img_picto($langs->trans("SarbacaneUnsucscribe"), 'statut8');
            if($statut == 4) return $langs->trans("SarbacaneClick").' '.img_picto($langs->trans("SarbacaneClick"), 'statut6');
            if($statut == 5) return $langs->trans("SarbacaneHardBounce").' '.img_error();
            if($statut == 6) return $langs->trans("SarbacaneSoftBounce").' '.img_error();
        }
    }

    /**
     * Return URL Link
     *
     * @return string with URL
     */
    function getNomUrl() {
        require_once DOL_DOCUMENT_ROOT.'/comm/mailing/class/mailing.class.php';
        $object = new Mailing($this->db);
        $result = $object->fetch($this->fk_mailing);

        $result = '<a href="'.dol_buildpath('/sarbacane/sarbacane.php', 1).'?id='.$object->id.'">';
        $result .= '<span class="fas fa-at paddingright classfortooltip" style=""></span>';
        $result .= $object->titre;
        $result .= '</a>';

        return $result;
    }

    /**
     * @return string
     */
    function getExternalNomUrl() {
        require_once DOL_DOCUMENT_ROOT.'/comm/mailing/class/mailing.class.php';
        $object = new Mailing($this->db);
        $result = $object->fetch($this->fk_mailing);

        $result = '<a href="https://app.sarbacane.com/#!/p/campaignslist/camp/'.$this->sarbacane_id.'/preview">';
        $result .= img_picto('', 'object_sarbacane@sarbacane', 'style="position:relative;top:2px;"');
        $result .= '&nbsp;'.$object->titre;
        $result .= '</a>';

        return $result;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function updateSarbacaneCampaignStatus() {
        global $conf;

        $result = $this->getInstanceSarbacane();
        if($result < 0) {
            dol_syslog(get_class($this)."::sendSarbacaneCampaign ".$this->error, LOG_ERR);
            return -1;
        }

        try {
            $ret = $this->sarbacane->get('/campaigns/'.$this->sarbacane_id.'/recipients', array());
        }
        catch(Exception $e) {
            $this->error = $e->getMessage();
            dol_syslog(get_class($this)."::sendSarbacaneCampaign ".$this->error, LOG_ERR);
            return -1;
        }
//        var_dump($ret);
        try {
            $ret = $this->sarbacane->get('/lists/'.$this->sarbacane_listid.'/contacts', array());
        }
        catch(Exception $e) {
            $this->error = $e->getMessage();
            dol_syslog(get_class($this)."::sendSarbacaneCampaign ".$this->error, LOG_ERR);
            return -1;
        }
//        var_dump($ret);
//        exit;
    }


	/**
	 * Return blacklisted contact of blacklisted list of $this
	 *
	 * @return array|int  if OK , -1 if KO
	 */
    public function getBlacklistedContact(){

    	$this->getInstanceSarbacane();
		$error = 0;
		$TBlacklistedContact = array();

		$offset = 0;

		if(!empty($this->sarbacane_blacklistid)) {

			while (1) {
				try {
					$response = $this->sarbacane->get('/blacklists/' . $this->sarbacane_blacklistid . '/unsubscribers?offset='.$offset.'&limit=1000', array());
					$TBlacklistedContact = array_merge($TBlacklistedContact, $response);

					if (count($response) < 1000) {
						break;
					} else {
						$offset += 1000;
						continue;
					}
				} catch (Exception $e) {
					$this->errors[] = $e->getMessage($this->sarbacane_blacklistid);
					$error++;
					break;
				}


			}
		}


		if (empty($error)) return $TBlacklistedContact;
		else return -1;


	}

	/**
	 * Return bounces contact email of bounce list of sarbacane
	 *
	 * @param string $list 'sarbacane' for list of sarbacane, 'dolibarr' for list of contacts with extrafield sarb_npai to 1, 'all' for both
	 *
	 * @return array|int if OK , -1 if KO
	 */
	public function getNPAIContactEmail($list = 'sarbacane'){

		$this->getInstanceSarbacane();
		$error = 0;
		$TBounces = array();
		$TBouncesDol = array();
		$TBouncesSarb = array();

		$offset = 0;

		if($list == 'sarbacane' || $list == 'all') {

			while (1) {
				try {
					$response = $this->sarbacane->get('/blacklists/DEFAULT_BOUNCELIST/bounces?offset=' . $offset . '&limit=1000', array());

					foreach ($response as $npai_contact) {
						$TBouncesSarb[] = $npai_contact['email'];
					}

					if (count($response) < 1000) {
						break;
					} else {
						$offset += 1000;
						continue;
					}
				} catch (Exception $e) {
					$this->errors[] = $e->getMessage($this->sarbacane_blacklistid);
					$error++;
					break;
				}

			}

			$TBounces = $TBouncesSarb;

		}

		if($list = 'dolibarr' || $list == 'all'){

			$sql = "SELECT email FROM ".MAIN_DB_PREFIX."socpeople s";
			$sql .= " JOIN ".MAIN_DB_PREFIX."socpeople_extrafields se ON se.fk_object = s.rowid";
			$sql .= " WHERE sarb_npai = 1";

			$resql = $this->db->query($sql);

			if($resql){
				while($obj = $this->db->fetch_object($resql)){
					if(!in_array($obj->email, $TBouncesSarb)) $TBouncesDol[] = $obj->email;
				}
			} else {
				$error ++;
			}

			$TBounces = $TBouncesDol;

		}

		if($list == 'all'){
			$TBounces = array_merge($TBouncesDol, $TBouncesSarb);
		}

		if (empty($error)) return $TBounces;
		else return -1;
	}
}

class DolSarbacaneeMailLine {
    var $id;
    var $email;
    var $thirdparty;
    var $contactfullname;
    var $type;

    /**
     * Constructor
     */
    function __construct() {
        return 0;
    }
}

/**
 * Class DolSarbacaneTargetLine is a contact in a sarbacane campaign
 */
class DolSarbacaneTargetLine extends DolSarbacane {
	public $id;
	public $fk_contact;
	public $sarbacane_campaignid;
	public $sarbacane_contactcampaignid;
	public $fk_user_author;
	public $datec = '';
	public $fk_user_mod;
	public $tms = '';
	public $statut = 0;
	public $nb_click = 0;
	public $nb_open = 0;
	public $npai = '';
	public $unsubscribe = 0;
	public $unsubscribed_email = '';
	public $used_blacklist = 'DEFAULT_BLACKLIST';

	public $table_element = 'sarbacane_campaign_contact';

    /**
     * Constructor
     */
    function __construct($db) {
        parent::__construct($db);
        return 0;
    }

	/**
	 * Create object into database
	 *
	 * @param User $user      that creates
	 * @param int  $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create($user, $notrigger = 0) {
		global $conf, $langs;

		$error = 0;

		if(isset($this->fk_contact)) $this->fk_contact = trim($this->fk_contact);
		if(isset($this->sarbacane_campaignid)) $this->sarbacane_campaignid = trim($this->sarbacane_campaignid);
		if(isset($this->sarbacane_contactcampaignid)) $this->sarbacane_id = trim($this->sarbacane_contactcampaignid);

		// Insert request
		$sql = "INSERT ".MAIN_DB_PREFIX.$this->table_element."(";
		$sql.= " fk_contact,";
		$sql.= " sarbacane_campaignid,";
		$sql.= " sarbacane_contactcampaignid,";
		$sql.= " fk_user_author,";
		$sql.= " datec,";
		$sql.= " fk_user_mod";
		$sql.= ") VALUES (";
		$sql.= " '".$this->fk_contact."',";
		$sql.= " '".$this->sarbacane_campaignid."',";
		$sql.= " '".$this->sarbacane_contactcampaignid."',";
		$sql.= " '".$user->id."',";
		$sql.= " '".$this->db->idate(dol_now())."',";
		$sql.= " '".$user->id."'";
		$sql.= ")";

		$this->db->begin();

		dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if(! $resql) {
			$error++;
			$this->errors[] = "Error ".$this->db->lasterror();
		}

		if(! $error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);

			if(! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.

				 // Call triggers
				 include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				 $interface=new Interfaces($this->db);
				 $result=$interface->run_triggers('SARBACANE_CAMPAIGN_CONTACT_CREATE',$this,$user,$langs,$conf);
				 if ($result < 0) { $error++; $this->errors=$interface->errors; }
				 // End call triggers
			}
		}

		// Commit or rollback
		if($error) {
			foreach($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		}
		else {
			$this->db->commit();
			return $this->id;
		}
	}

	/**
	 * load object from database
	 *
	 * @param string $sarbacane_contactcampaignid id coté sarbacane du contact dans la campagne
	 *
	 * @return int <0 KO, 0 not found, >0 OK
	 * @throws Exception
	 */
	public function fetchBySarbacaneContactCampaignId($sarbacane_contactcampaignid)
	{
		$sql = "SELECT rowid, fk_contact, sarbacane_campaignid, sarbacane_contactcampaignid, fk_user_author, datec, fk_user_mod, tms, statut, nb_click, nb_open, npai, unsubscribe, unsubscribed_email, used_blacklist";
		$sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql.= " WHERE sarbacane_contactcampaignid = '".$sarbacane_contactcampaignid."'";
		$resql = $this->db->query($sql);

		if ($resql)
		{
			if($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;
				$this->fk_contact = $obj->fk_contact;
				$this->sarbacane_campaignid = $obj->sarbacane_campaignid;
				$this->sarbacane_contactcampaignid = $obj->sarbacane_contactcampaignid;
				$this->fk_user_author = $obj->fk_user_author;
				$this->datec = $this->db->jdate($obj->datec);
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->tms = $this->db->jdate($obj->tms);
				$this->statut = $obj->statut;
				$this->nb_click = $obj->nb_click;
				$this->nb_open = $obj->nb_open;
				$this->npai = $obj->npai;
				$this->unsubscribe = $obj->unsubscribe;
				$this->unsubscribed_email = $obj->unsubscribed_email;
				$this->used_blacklist = $obj->used_blacklist;

				$this->db->free($resql);
			}
			else return 0;

			return 1;
		}
		else {
			$this->error = "Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
			return -1;
		}
	}

	/**
	 * Update db for line
	 * @param int $user
	 * @param int $notrigger
	 * @return float|int
	 * @throws Exception
	 */
	public function update($user = 0, $notrigger = 0)
	{
		global $conf, $langs;

		$error = 0;

		if(isset($this->fk_contact)) $this->fk_contact = trim($this->fk_contact);
		if(isset($this->sarbacane_campaignid)) $this->sarbacane_campaignid = trim($this->sarbacane_campaignid);
		if(isset($this->sarbacane_contactcampaignid)) $this->sarbacane_contactcampaignid = trim($this->sarbacane_contactcampaignid);
		if(isset($this->npai)) $this->npai = trim($this->npai);
		if(empty($this->unsubscribe)) $this->unsubscribe = 0;
		if(isset($this->unsubscribed_email)) $this->unsubscribed_email = trim($this->unsubscribed_email);
		if(isset($this->used_blacklist)) $this->used_blacklist = trim($this->used_blacklist);
		$this->nb_click = intval($this->nb_click);
		$this->nb_open = intval($this->nb_open);

		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET";
		$sql.= " fk_contact=".$this->fk_contact;
		$sql.= ",sarbacane_campaignid='".$this->db->escape($this->sarbacane_campaignid)."'";
		$sql.= ",sarbacane_contactcampaignid='".$this->db->escape($this->sarbacane_contactcampaignid)."'";
		$sql.= ",fk_user_mod=".$user->id;
		$sql.= ",statut=".$this->statut;
		$sql.= ",nb_click=".$this->nb_click;
		$sql.= ",nb_open=".$this->nb_open;
		$sql.= ",npai='".$this->db->escape($this->npai)."'";
		$sql.= ",unsubscribe=".$this->unsubscribe;
		$sql.= ",unsubscribed_email='".$this->db->escape($this->unsubscribed_email)."'";
		$sql.= ",used_blacklist='".$this->db->escape($this->used_blacklist)."'";
		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if(! $resql) {
			$error++;
			$this->errors[] = "Error ".$this->db->lasterror();
		}

		if(! $error) {
			if(! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.

				 // Call triggers
				 include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				 $interface=new Interfaces($this->db);
				 $result=$interface->run_triggers('SARBACANE_CAMPAIGN_CONTACT_MODIFY',$this,$user,$langs,$conf);
				 if ($result < 0) { $error++; $this->errors=$interface->errors; }
				 // End call triggers
			}
		}

		// Commit or rollback
		if($error) {
			foreach($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		}
		else {
			$this->db->commit();
			return 1;
		}

	}

	/**
	 * Delete object in database
	 *
	 * @param User $user      that deletes
	 * @param int  $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;

		$this->db->begin();

		if(! $error) {
			if(! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.

				 // Call triggers
				 include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				 $interface=new Interfaces($this->db);
				 $result=$interface->run_triggers('SARBACANE_CAMPAIGN_CONTACT_DELETE',$this,$user,$langs,$conf);
				 if ($result < 0) { $error++; $this->errors=$interface->errors; }
				 // End call triggers
			}
		}

		if(! $error) {
			$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " WHERE rowid=".$this->id;

			dol_syslog(get_class($this)."::delete sql=".$sql);
			$resql = $this->db->query($sql);
			if(! $resql) {
				$error++;
				$this->errors[] = "Error ".$this->db->lasterror();
			}
		}

		// Commit or rollback
		if($error) {
			foreach($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		}
		else {
			$this->db->commit();
			return 1;
		}
	}

	public function getAverageStatus()
	{
		global $langs;
		$error = $nb_active = $nb_total = $average = 0;

		if (empty($this->fk_contact))
		{
			$this->error = 'No contact id defined';
			return -1;
		}

		$sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX.$this->table_element." WHERE fk_contact = ".$this->fk_contact;
		$resql = $this->db->query($sql);
		if(! $resql) {
			$error++;
			$this->error = "Error ".$this->db->lasterror();
			return -2;
		}
		else
		{
			$obj = $this->db->fetch_object($resql);
			$nb_total = intval($obj->nb);
		}

		if (empty($nb_total)) return $langs->trans('SarbInactiveContact');

		$resql = $this->db->query($sql . " AND statut = 1");
		if(! $resql) {
			$error++;
			$this->error = "Error ".$this->db->lasterror();
			return -2;
		}
		else
		{
			$obj = $this->db->fetch_object($resql);
			$nb_active = intval($obj->nb);

			if (empty($nb_active)) return $langs->trans('SarbInactiveContact');
		}
		$average = $nb_active / $nb_total;

		if ($average < 0.3) return $langs->trans('SarbNotSoActiveContact');
		else if ($average < 0.6) return $langs->trans('SarbMiddleActiveContact');
		else return $langs->trans('SarbActiveContact');
	}
}

class DolSarbacaneActivitesLine {
    public $campaign;
    public $campaignid;
    public $fk_mailing;
    public $activites = array();

    /**
     * Constructor
     */
    function __construct() {
        return 0;
    }
}

class DolSarbacaneLine {
    public $id;
    public $entity;
    public $fk_mailing;
    public $sarbacane_id;
    public $sarbacane_webid;
    public $sarbacane_listid;
    public $sarbacane_segmentid;
    public $sarbacane_sender_name;
    public $fk_user_author;
    public $datec = '';
    public $fk_user_mod;
    public $tms = '';

    /**
     * Constructor
     */
    function __construct() {
        return 0;
    }
}
