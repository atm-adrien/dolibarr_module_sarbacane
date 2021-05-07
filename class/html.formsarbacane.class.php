<?php
/* <Sarbacane connector>
 * Copyright (C) 2021 Quentin Vial-Gouteyron quentin.vial-gouteyron@atm-consulting.fr
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
 *	\file       /sarbacane/class/html.formsarbacane.class.php
 *  \ingroup    sarbacane
 *	\brief      HTML coponent for Sarbacane
 */
require_once 'dolsarbacane.class.php';

/**
 *	Class to offer components to list and upload files
 */
class FormSarbacane
{
	var $db;
	var $error;

	var $num;



	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;
		return 1;
	}

	/**
	 *	Select list with current Sarbacane List
	 *
	 *  @param		string		$htmlname     	HTML name input
	 *  @param		int			$showempty      display empty options
	 *  @param		string		$selected      	Id of preselected option
	 *  @param		int			$option_only 	output only options
	 *  @param		array		$event			Event options. (disabled if $option_only=true)
	 *  @param array $filters a hash of filters to apply to this query - all are optional:
			 string list_id optional - return a single list using a known list_id. Accepts multiples separated by commas when not using exact matching
			 string list_name optional - only lists that match this name
			 string from_name optional - only lists that have a default from name matching this
			 string from_email optional - only lists that have a default from email matching this
			 string from_subject optional - only lists that have a default from email matching this
			 string created_before optional - only show lists that were created before this date/time (in GMT) - format is YYYY-MM-DD HH:mm:ss (24hr)
			 string created_after optional - only show lists that were created since this date/time (in GMT) - format is YYYY-MM-DD HH:mm:ss (24hr)
			 boolean exact optional - flag for whether to filter on exact values when filtering, or search within content for filter values - defaults to true
	 * @param int $start optional - control paging of lists, start results at this list #, defaults to 1st page of data  (page 0)
	 * @param int $limit optional - control paging of lists, number of lists to return with each call, defaults to 25 (max=100)
	 * @param string $sort_field optional - "created" (the created date, default) or "web" (the display order in the web app). Invalid values will fall back on "created" - case insensitive.
	 * @param string $sort_dir optional - "DESC" for descending (default), "ASC" for Ascending.  Invalid values will fall back on "created" - case insensitive. Note: to get the exact display order as the web app you'd use "web" and "ASC"
	 *
	 *  @return string 		HTML input
	 */
	function select_sarbacanelist($htmlname='selectlist',$showempty=0,$selected='',$option_only=0, $event=array(), $filters = array(),$start=0, $limit=100, $sort_field='created', $sort_dir='DESC') {

		$error=0;
        $out = '';

        $sarbacane = new DolSarbacane($this->db);
        $result = $sarbacane->getListDestinaries();
        if($result < 0) {
            $this->error = $sarbacane->errors;
            dol_syslog(get_class($this)."::select_sarbacanelist Error : ".$this->error, LOG_ERR);
            return -1;
        }

        $out .= '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'">';

        if(! empty($showempty)) {
            if(empty($selected)) {
                $out .= '<option value="" selected="selected">&nbsp;</option>';
            }
            else {
                $out .= '<option value="">&nbsp;</option>';
            }
        }

        if(is_array($sarbacane->listdest_lines) && count($sarbacane->listdest_lines) > 0) {
            foreach($sarbacane->listdest_lines as $line) {
                if($selected == $line['id']) {
                    $out .= '<option value="'.$line['id'].'" selected="selected">';
                }
                else {
                    $out .= '<option value="'.$line['id'].'">';
                }
                $out .= $line['name'];
                $out .= '</option>';
            }
        }

        $out .= '</select>';

		return $out;
	}

	public function select_sarbacaneBlacklist($htmlname='selectblacklist',$showempty=0,$selected='')
	{
		global $langs;
		$error=0;
		$out = '';
		$disabled = '';

		$sarbacane = new DolSarbacane($this->db);
		$result = $sarbacane->getBlackLists();

		if($result < 0) {
			$this->error = $sarbacane->errors;
			dol_syslog(get_class($this)."::select_sarbacanelist Error : ".$this->error, LOG_ERR);
			return -1;
		}

		if (is_array($sarbacane->blacklists_lines) && count($sarbacane->blacklists_lines) == 2)
		{
			$selected = 'DEFAULT_BLACKLIST';
			$disabled = 'disabled';
		}
		if (empty($selected)) $selected = 'DEFAULT_BLACKLIST';

		$out .= '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'" '.$disabled.'>';

		if(! empty($showempty)) {
			if(empty($selected)) {
				$out .= '<option value="" selected="selected">&nbsp;</option>';
			}
			else {
				$out .= '<option value="">&nbsp;</option>';
			}
		}

		$out .= '<option value="DEFAULT_BLACKLIST" selected="selected">'.$langs->trans('SarbacaneDefaultBlacklist').'</option>';

		if(is_array($sarbacane->blacklists_lines) && count($sarbacane->blacklists_lines) > 0) {
			foreach($sarbacane->blacklists_lines as $line) {
				if ($line['id'] == 'DEFAULT_BOUNCELIST' || $line['id'] == 'DEFAULT_BLACKLIST') continue;
				if($selected == $line['id']) {
					$out .= '<option value="'.$line['id'].'" selected="selected">';
				}
				else {
					$out .= '<option value="'.$line['id'].'">';
				}

				$name = $line['name'];

				$out .= $name;
				$out .= '</option>';
			}
		}

		$out .= '</select>';

		return $out;
	}

}
