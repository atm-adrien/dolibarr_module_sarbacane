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

// Access control
//if (! $user->rights->mailing->creer || (empty($conf->global->EXTERNAL_USERS_ARE_AUTHORIZED) && $user->societe_id > 0 )) {
//	accessforbidden();
//}

$object = new Contact($db);
$result=$object->fetch($id);
if ($result<0) {
	setEventMessage($object->error,'errors');
}

$Dolsarbacane= new DolSarbacane($db);
$sarbacane = new Sarbacane('https://sarbacaneapis.com/v1', $conf->global->SARBACANE_API_KEY, $conf->global->SARBACANE_ACCOUNT_KEY);

/*
 * ACTIONS
*
* Put here all code to do according to value of "action" parameter
*/


/*
 * VIEW
*
* Put here all code to build page
*/

// fetch optionals attributes and labels

llxHeader('',$langs->trans("Sarbacane"));

$head = contact_prepare_head($object);

dol_fiche_head($head, 'tabSarbacaneSending', $langs->trans("Sarbacane"), 0, 'email');

// récupère toutes les campagnes sarbacane présentes dans Dolibarr
$result = $Dolsarbacane->fetch_all();

/*
 * TODO pour chaque campagne
 * GET https://sarbacaneapis.com/v1/reports/{campaignId}/recipients
 * vérifier que le contact est présent et récupérer ses stats
 *
 * pb le temps de lecture est inaccessible via l'API
 * le status actif si ouvert au moins une fois ?
 *
 */

echo "<pre>";

print_r($Dolsarbacane->lines);
