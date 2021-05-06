<?php
/* <Sarbacane connector>
 * Copyright (C) 2021 Quentin Vial-Gouteyron quentin.vial-gouteyron@atm-consulting.fr
 * Copyright (C) 2021 Grégory Blémand gregory.blemand@atm-consulting.fr
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 *	\file		/sarbacane/contact_tab.php
 *	\ingroup	sarbacane
 */


require 'config.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/contact.lib.php';
require_once DOL_DOCUMENT_ROOT.'/comm/mailing/class/mailing.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once __DIR__.'/class/dolsarbacane.class.php';
require_once __DIR__.'/class/sarbacane.class.php';
require_once __DIR__.'/class/html.formsarbacane.class.php';


// Load translation files required by the page
$langs->load("sarbacane@sarbacane");
$langs->load("mails");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'none');

$search_campaign = GETPOST('search_campaign', 'none');
$search_status = GETPOST('search_status', 'none');
$search_nb_open = GETPOST('search_nb_open', 'int');
$search_nb_click = GETPOST('search_nb_click', 'int');

$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$limit = GETPOST('limit', 'int') ?GETPOST('limit', 'int') : $conf->liste_limit;
$optioncss = GETPOST('optioncss', 'alpha');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page == -1 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha') || (empty($toselect) && $massaction === '0')) { $page = 0; }     // If $page is not defined, or '' or -1 or if we click on clear filters or if we select empty mass action
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortorder) $sortorder = "DESC";
if (!$sortfield) $sortfield = "s.datec";

$object = new Contact($db);
$hookmanager->initHooks(array('sarbacaneCampaignContactlist', 'sarbacaneContactTab'));
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Access control
//if (! $user->rights->mailing->creer || (empty($conf->global->EXTERNAL_USERS_ARE_AUTHORIZED) && $user->societe_id > 0 )) {
//	accessforbidden();
//}

$result=$object->fetch($id);
if ($result<0) {
	setEventMessage($object->error,'errors');
}

if ($object->socid > 0)
{
	$objsoc = new Societe($db);
	$objsoc->fetch($object->socid);
}

$Dolsarbacane= new DolSarbacane($db);
$sarbacane = new Sarbacane('https://sarbacaneapis.com/v1', $conf->global->SARBACANE_API_KEY, $conf->global->SARBACANE_ACCOUNT_KEY);

/*
 * ACTIONS
*
* Put here all code to do according to value of "action" parameter
*/
$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// Purge search criteria
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) // All tests are required to be compatible with all browsers
	{
		/*foreach($object->fields as $key => $val)
		{
			$search[$key]='';
		}*/
		$search_campaign = '';
		$search_status = '';
		$search_nb_open = '';
		$search_nb_click = '';
	}
}

/*
 * VIEW
*
* Put here all code to build page
*/

//$TCampaignIds = array();

llxHeader('',$langs->trans("Sarbacane"));

$head = contact_prepare_head($object);

dol_fiche_head($head, 'tabSarbacaneSending', $langs->trans("Sarbacane"), 0, 'email');

$linkback = '<a href="'.DOL_URL_ROOT.'/contact/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

$morehtmlref = '<div class="refidno">';
if (empty($conf->global->SOCIETE_DISABLE_CONTACTS))
{
	$objsoc->fetch($object->socid);
	// Thirdparty
	$morehtmlref .= $langs->trans('ThirdParty').' : ';
	if ($objsoc->id > 0) $morehtmlref .= $objsoc->getNomUrl(1, 'contact');
	else $morehtmlref .= $langs->trans("ContactNotLinkedToCompany");
}
$morehtmlref .= '</div>';

dol_banner_tab($object, 'id', $linkback, 1, 'rowid', 'ref', $morehtmlref);

dol_fiche_end();

// récupérer les campagnes dans lesquelles le contact est présent (DolSarbacane::$campaign_contact_table)
$sql = "SELECT";
$sql.= " m.titre, s.fk_mailing, scc.sarbacane_campaignid, scc.statut, scc.nb_open, scc.nb_click";
$sql.= " FROM ".MAIN_DB_PREFIX.DolSarbacane::$campaign_contact_table." as scc";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."sarbacane as s ON s.sarbacane_id = scc.sarbacane_campaignid";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."mailing m ON m.rowid = s.fk_mailing";
$sql.= " WHERE scc.fk_contact = ".$id;
// Todo ajouter les filtres

if (isset($search_campaign) && !empty($search_campaign)) $sql.= " AND m.titre LIKE '%".$db->escape($search_campaign)."%'";
if ($search_status !== '' && $search_status > -1) $sql.= " AND scc.statut = ".$search_status;
if ($search_nb_open !== '' && $search_nb_open > -1) $sql.= " AND scc.nb_open = ".$search_nb_open;
if ($search_nb_click !== '' && $search_nb_click > -1) $sql.= " AND scc.nb_click = ".$search_nb_click;

$sql .= $db->order($sortfield, $sortorder);
//var_dump($sql); exit;
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$resql = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($resql);
	if (($page * $limit) > $nbtotalofrecords)	// if total resultset is smaller then paging size (filtering), goto and load page 0
	{
		$page = 0;
		$offset = 0;
	}
}

$sql .= $db->plimit($limit + 1, $offset);

$resql = $db->query($sql);
$num = 0;

if($resql) {
	$num = $db->num_rows($resql);

	$param = '&id='.$id;
	if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param .= '&contextpage='.urlencode($contextpage);
	if ($limit > 0 && $limit != $conf->liste_limit) $param .= '&limit='.urlencode($limit);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="list">';
	print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
	print '<input type="hidden" name="id" value="'.$id.'">';

	$title = $langs->trans('ListOfEMailings');
	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'object_email', 0, '', '', $limit, 0, 0, 1);

	print '<div class="div-table-responsive">';
	print '<table class="tagtable liste">'."\n";

	// header filtre
	print '<tr class="liste_titre_filter">';
	// nom de campagne
	print '<td class="liste_titre">';
	print '<input type="text" name="search_campaign" id="search_campaign" value="'.$search_campaign.'"/>';
	print '</td>';

	// Statut dans la campagne
	print '<td class="liste_titre">';
	print $form->selectArray('search_status', array($langs->trans('SarbInactiveContact'), $langs->trans('SarbActiveContact')), $search_status, 1);
	print '</td>';

	// Nombre d'ouvertures
	print '<td class="liste_titre"><input type="number" step="1" name="search_nb_open" id="search_nb_open" value="'.$search_nb_open.'"></td>';

	// Nombre de clics
	print '<td class="liste_titre"><input type="number" step="1" name="search_nb_click" id="search_nb_click" value="'.$search_nb_click.'"></td>';

	print '<td class="liste_titre maxwidthsearch">';
	$searchpicto = $form->showFilterAndCheckAddButtons(0);
	print $searchpicto;
	print '</td>';
	print "</tr>\n";

	// header titre/tri
	print '<tr class="liste_titre">';
	// nom de campagne
	print_liste_field_titre($langs->trans('SarbacaneCampaign'), $_SERVER["PHP_SELF"], "m.titre", $param, "", "", $sortfield, $sortorder);

	// Statut dans la campagne
	print_liste_field_titre($langs->trans('Status'), $_SERVER["PHP_SELF"], "scc.statut", $param, "", "", $sortfield, $sortorder);

	// Nombre d'ouvertures
	print_liste_field_titre($langs->trans('SarbNbOpen'), $_SERVER["PHP_SELF"], "scc.nb_open", $param, "", "", $sortfield, $sortorder);

	// Nombre de clics
	print_liste_field_titre($langs->trans('SarbNbClick'), $_SERVER["PHP_SELF"], "scc.nb_clic", $param, "", "", $sortfield, $sortorder);

	print '<td class="liste_titre maxwidthsearch"></td>';
	print "</tr>\n";
	if($num) {
		$total = array('open' => 0, 'click' => 0);
		while($obj = $db->fetch_object($resql)) {
			$StaticDolsarbacane = new DolSarbacane($db);
			$StaticDolsarbacane->fk_mailing = $obj->fk_mailing;

			print '<tr class="oddeven">';
			// nom de campagne
			print '<td>';
			print $StaticDolsarbacane->getNomUrl();
			print '</td>';

			// Statut dans la campagne
			print '<td>';
			print (empty($obj->statut)) ? $langs->trans('SarbInactiveContact') : $langs->trans('SarbActiveContact');
			print '</td>';

			// Nombre d'ouvertures
			print '<td>'.$obj->nb_open.'</td>';
			$total['open'] += intval($obj->nb_open);

			// Nombre de clics
			print '<td>'.$obj->nb_click.'</td>';
			$total['click'] += intval($obj->nb_click);

			print '<td>&nbsp;</td>';
			print "</tr>\n";



//			$TCampaignIds[] = $obj->sarbacane_campaignid;
		}
	}

	if (empty($num)) {
		$colspan = 5;
		print '<tr><td colspan="'.$colspan.'"><span class="opacitymedium">'.$langs->trans("NoRecordFound").'</td></tr>';
	}
	else
	{
		print '<tr class="oddeven">';
		// nom de campagne
		print '<td>'.$langs->trans('Total').'</td>';

		// Statut moyen
		$campaignContact = new DolSarbacaneTargetLine($db);
		$campaignContact->fk_contact = $id;
		$AverageStatus = $campaignContact->getAverageStatus();
		print '<td>'.$AverageStatus.'</td>';
		// Nombre d'ouvertures
		print '<td>'.$total['open'].'</td>';

		// Nombre de clics
		print '<td>'.$total['click'].'</td>';

		print '<td>&nbsp;</td>';
		print "</tr>\n";
	}

	print '</table>';
	print '</div>';
	print '</form>';

	// statut moyen
//	$campaignContact = new DolSarbacaneTargetLine($db);
//	$campaignContact->fk_contact = $id;
//	var_dump($campaignContact->getAverageStatus());

	$db->free($resql);
}
else
{
	dol_print_error($db);
}

/*

// test d'update des stats destinataires

echo "<pre>";

if (!empty($TCampaignIds))
{
	$results = $Dolsarbacane->updateCampaignRecipientStats($TCampaignIds);
	//print_r($Dolsarbacane->CampaignRecipientStats);
}*/

// End of page
llxFooter();
$db->close();
