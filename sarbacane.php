<?php
/* <Sarbacane connector>
 * Copyright (C) 2013 Florian Henry florian.henry@open-concept.pro
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

/*error_reporting(E_ALL);
 ini_set('display_errors', true);
ini_set('html_errors', false);*/

/**
 *	\file		/sarbacane/sarbacane.php
 *	\ingroup	sarbacane
 */


require 'config.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/emailing.lib.php';
require_once DOL_DOCUMENT_ROOT.'/comm/mailing/class/mailing.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once __DIR__.'/class/dolsarbacane.class.php';
require_once __DIR__.'/class/html.formsarbacane.class.php';


// Load translation files required by the page
$langs->load("sarbacane@sarbacane");
$langs->load("mails");

// Get parameters
$id = GETPOST('id', 'int');
$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'none');
$createList = GETPOST('createList', 'none');
$nameList = GETPOST('nameList', 'none');

$error=0;

// Access control
if (! $user->rights->mailing->creer || (empty($conf->global->EXTERNAL_USERS_ARE_AUTHORIZED) && $user->societe_id > 0 )) {
	accessforbidden();
}

$object=new Mailing($db);
$result=$object->fetch($id);
if ($result<0) {
	setEventMessage($object->error,'errors');
}

$sarbacane= new DolSarbacane($db);
$result=$sarbacane->fetch_by_mailing($id);

if ($result<0) {
	setEventMessage($sarbacane->error,'errors');
}

$extrafields = new ExtraFields($db);

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
$hookmanager=new HookManager($db);
$hookmanager->initHooks(array('sarbacanecard'));



$error_sarbacane_control=0;

/*
 * ACTIONS
*
* Put here all code to do according to value of "action" parameter
*/

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if(!empty($createList) && !empty($nameList)){
    $res = $sarbacane->createList($nameList);
    // Auto choose the good list
    $newList = $res;
	if ($res < 0)
    {
        setEventMessage($langs->trans('SarbacaneReturnError', $sarbacane->error), 'errors');
    } else {
	    setEventMessage($langs->trans('SarbacaneListCreated'));
    }
}
// Action update description of emailing
if ($action == 'settitre' || $action == 'setemail_from') {

	if ($action == 'settitre')					$object->titre          = trim(GETPOST('titre','alpha'));
	else if ($action == 'setemail_from')		$object->email_from     = trim(GETPOST('email_from','alpha'));

	else if ($action == 'settitre' && empty($object->titre))		$mesg.=($mesg?'<br>':'').$langs->trans("ErrorFieldRequired",$langs->transnoentities("MailTitle"));
	else if ($action == 'setfrom' && empty($object->email_from))	$mesg.=($mesg?'<br>':'').$langs->trans("ErrorFieldRequired",$langs->transnoentities("MailFrom"));

	if (empty($mesg)) {
		if ($object->update($user) >= 0) {
			header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
			exit;
		} else {
			setEventMessage($object->error,'errors');
		}
	} else {
		setEventMessage($mesg,'errors');
	}

	$action="";
}

if ($action == 'createsarbacanecampaign') {

	$sarbacane->currentmailing=$object;

	$result=$sarbacane->createSarbacaneCampaign($user);
	if ($result<0) {

		setEventMessage($sarbacane->error,'errors');
	}
}

if ($action=='sendsarbacanecampaign') {
	//Send campaign
	$result=$sarbacane->sendSarbacaneCampaign();
	if ($result<0) {
		setEventMessage($sarbacane->error,'errors');
	} else {
		//Update mailing general status
		$object->statut=3;
		$sql="UPDATE ".MAIN_DB_PREFIX."mailing SET statut=".$object->statut." WHERE rowid=".$object->id;
		dol_syslog("sarbacane/sarbacane.php: update global status sql=".$sql, LOG_DEBUG);
		$resql2=$db->query($sql);
		if (! $resql2)	{
			setEventMessage($db->lasterror(),'errors');
		} else setEventMessage($langs->trans('Sent'));
	}
}

if ($action=='setsarbacane_sender_name') {
	$sarbacane->sarbacane_sender_name  = GETPOST('sarbacane_sender_name','alpha');
	if (empty($sarbacane->id)) {
		$sarbacane->fk_mailing=$id;
		$result=$sarbacane->create($user);
	}else {
		$result=$sarbacane->update($user);
	}
	if ($result<0) {
		setEventMessage($sarbacane->error,'errors');
	}else {
		$result=$sarbacane->fetch_by_mailing($id);
		if ($result<0) {
			setEventMessage($sarbacane->error,'errors');
		}
	}
}

if ($action=='associateconfirm') {

	$import=GETPOST('import','alpha');
	$export=GETPOST('export','alpha');
	$updateonly=GETPOST('updateonly','alpha');
	$updatesegment=GETPOST('updatesegment','alpha');
	$segmentid=GETPOST('segmentlist','alpha');
	if(empty($newList)) {
	    $listid=GETPOST('selectlist','alpha');
	} else {
	    $listid = $newList;
	}
	$newsegmentname=GETPOST('segmentname','alpha');
	$resetseg=GETPOST('resetseg','int');
	$sarbacane->sarbacane_listid=$listid;

	if (empty($sarbacane->id)) {
		$sarbacane->fk_mailing=$id;
		$result=$sarbacane->create($user);
		if ($result<0) {
			setEventMessage($sarbacane->error,'errors');
			$error++;
		}
	}

	if (empty($error)) {
		$result=$sarbacane->update($user);
		if ($result<0) {
			setEventMessage($sarbacane->error,'errors');
		}
	}

	$result=$object->fetch($id);
	if ($result<0) {
		setEventMessage($object->error,'errors');
	}
}

/**
 * Gestion des warnings
 */
$error_sarbacane_control = 0;
$email_in_dol_not_in_sarbacane=array();
//Listid must be define
$error_list_define=false;
if (empty($sarbacane->sarbacane_listid)) {
	$error_list_define=true;
	$error_sarbacane_control++;
}

$error_sendername=false;
if (empty($sarbacane->sarbacane_sender_name)) {
	$error_sendername=true;
	$error_sarbacane_control++;
}


$warning_destnotsync=false;
//Check dolibarr dest versus list segment define
if(! empty($conf->global->SARBACANE_API_KEY)) {
    if(! empty($sarbacane->id)) {

        if($object->statut == 0 || $object->statut == 1) {
            $email_seg_array = array();

            //retrive email for segment and Or List

            if(! empty($sarbacane->sarbacane_listid)) {
                $result = $sarbacane->getEmailList();

                if($result < 0) {

                    setEventMessage($sarbacane->error, 'errors');
                }
                else {
                    if(!empty($sarbacane->email_lines)) {
                        foreach($sarbacane->email_lines as $l) {
                            $email_seg_array[] = $l['email'];
                        }
                    }
                }
            }

            //Retreive mail from mailling destinaries
            $sarbacane->fk_mailing = $id;
            $result = $sarbacane->getEmailMailingDolibarr();
            if($result < 0) {
                setEventMessage($sarbacane->error, 'errors');
            }
            else {
                $email_dol_array = $sarbacane->email_lines;
            }

            //First compare count easy and quick
            if(count($email_dol_array) != count($email_seg_array)) {
                $warning_destnotsync = true;
                foreach($email_dol_array as $emailadress) {
                    if(array_search($emailadress, $email_seg_array) === false) {
                        $email_in_dol_not_in_sarbacane[] = $emailadress;
                    }
                }
            }
            else {
                foreach($email_seg_array as $emailadress) {
                    $email_sb_array[] = $emailadress;
                }

                //if count is same compare email by email
                foreach($email_dol_array as $emailadress) {
                    if(! in_array($emailadress, $email_sb_array)) {

                        $warning_destnotsync = true;
                        break;
                    }
                }
                if(! empty($email_sb_array)) {
                    foreach($email_sb_array as $emailadress) {
                        if(! in_array($emailadress, $email_dol_array)) {
                            $warning_destnotsync = true;
                            break;
                        }
                    }
                }
            }
        }
} else {
	$warning_destnotsync=true;
}

}



/*
 * VIEW
*
* Put here all code to build page
*/

// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label('mailing');

llxHeader('',$langs->trans("Mailing"));



$head = emailing_prepare_head($object);

dol_fiche_head($head, 'tabSarbacaneSending', $langs->trans("Sarbacane"), 0, 'email');

if ( !empty($conf->global->SARBACANE_API_KEY)) {

	$form = new Form($db);
	$formsarbacane = new FormSarbacane($db);



	print '<table class="border tableforfield" width="100%">';

	if ((float) DOL_VERSION <= 3.6)	$linkback = '<a href="'.DOL_URL_ROOT.'/comm/mailing/liste.php">'.$langs->trans("BackToList").'</a>';
	else $linkback = '<a href="'.DOL_URL_ROOT.'/comm/mailing/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

	print '<tr class="impair"><td width="20%">'.$langs->trans("Ref").'</td>';
	print '<td colspan="3">';
	print $form->showrefnav($object,'id', $linkback);
	print '</td></tr>';

	// Description
	print '<tr class="pair"><td>'.$form->editfieldkey("MailTitle",'titre',$object->titre,$object,$user->rights->mailing->creer && $object->statut < 3,'string').'</td><td colspan="3">';
	print $form->editfieldval("MailTitle",'titre',$object->titre,$object,$user->rights->mailing->creer && $object->statut < 3,'string');
	print '</td></tr>';

	// From
	print '<tr class="impair"><td>'.$form->editfieldkey("MailFrom",'email_from',$object->email_from,$object,$user->rights->mailing->creer && $object->statut < 3,'string').'</td><td colspan="3">';
	print $form->editfieldval("MailFrom",'email_from',$object->email_from,$object,$user->rights->mailing->creer && $object->statut < 3,'string');
	print '</td></tr>';

	// Status
	print '<tr class="pair"><td>'.$langs->trans("Status").'</td><td colspan="3">'.$object->getLibStatut(4).'</td></tr>';

	// Nb of distinct emails
	print '<tr class="impair"><td>';
	print $langs->trans("TotalNbOfDistinctRecipients");
	print '</td><td colspan="3">';
	$nbemail = ($object->nbemail?$object->nbemail:img_warning('').' <font class="warning">'.$langs->trans("SarbacaneSelectSegmentOrList").'</font>');
	if ($object->statut != 3 && !empty($conf->global->MAILING_LIMIT_SENDBYWEB) && is_numeric($nbemail) && $conf->global->MAILING_LIMIT_SENDBYWEB < $nbemail)
	{
		if ($conf->global->MAILING_LIMIT_SENDBYWEB > 0)	{
			$text=$langs->trans('LimitSendingEmailing',$conf->global->MAILING_LIMIT_SENDBYWEB);
			print $form->textwithpicto($nbemail,$text,1,'warning');
		} else {
			$text=$langs->trans('NotEnoughPermissions');
			print $form->textwithpicto($nbemail,$text,1,'warning');
		}
	} else {
		print $nbemail;
	}
	print '</td></tr>';

	//Glue to avoid problem with edit in place option
	if (! empty($conf->global->MAIN_USE_JQUERY_JEDITABLE)) {
		$objecttoedit=$sarbacane;
		if (empty($sarbacane->id)) {
			$sarbacane->fk_mailing=$object->id;
			$result=$sarbacane->create($user);
		}
	}else {
		$objecttoedit=$object;
	}

	// Sarbacane Sender Name
	print '<tr class="pair"><td>';
	print $form->editfieldkey("SarbacaneSenderName",'sarbacane_sender_name',$sarbacane->sarbacane_sender_name,$objecttoedit,$user->rights->mailing->creer && $object->statut < 3 && empty($sarbacane->sarbacane_id),'string');
	print '</td><td colspan="3">';
	print $form->editfieldval("SarbacaneSenderName",'sarbacane_sender_name',$sarbacane->sarbacane_sender_name,$objecttoedit,$user->rights->mailing->creer && $object->statut < 3 && empty($sarbacane->sarbacane_id),'string');
	print '</td></tr>';


	if (!empty($sarbacane->sarbacane_id)) {

		//Status campaign sarbacane
		print '<tr class="impair"><td>';
		print $langs->trans("SarbacaneStatus");
		print '</td><td colspan="3">';
		if (!empty($sarbacane->sarbacane_id)) {
			print $sarbacane->getSarbacaneCampaignStatus();
		}
		print '</td></tr>';

		// Sarbacane Campaign
		print '<tr class="pair"><td>';
		print $langs->trans("SarbacaneCampaign");
		print '</td><td colspan="3">';
		print '<a target="_blanck" href="https://app.sarbacane.com/#!/p/campaignslist/camp/'.$sarbacane->sarbacane_id.'/preview">'.$langs->trans('SarbacaneCampaign').'</a>';
		print '</td></tr>';

		//List campaign sarbacane
		print '<tr class="impair"><td>';
		print $langs->trans("SarbacaneDestList");
		print '</td><td colspan="3">';
		if (!empty($sarbacane->sarbacane_listid)) {
			$result=$sarbacane->getListDestinaries();
			if ($result<0) {
				setEventMessage($sarbacane->error,'errors');
			}
			if (is_array($sarbacane->listdest_lines) && count($sarbacane->listdest_lines)>0) {

                foreach($sarbacane->listdest_lines as $list) {
                    if($sarbacane->sarbacane_listid == $list['id']) print '<a target="_blanck" href="https://app.sarbacane.com/#!/p/contacts/list/'.$list['id'].'">'.$list['name'].'</a>';
                }


			}
		}
		print '</td></tr>';
	/*	print '<tr><td width="15%">';
		print $langs->trans("SarbacaneSegment");
		print '</td><td colspan="3">';
		if (!empty($sarbacane->sarbacane_segmentid) && !empty($sarbacane->sarbacane_listid)) {
			$result=$sarbacane->getListSegmentDestinaries($sarbacane->sarbacane_listid);
			if ($result<0) {
				setEventMessage($sarbacane->error,'errors');
			}
			if (is_array($sarbacane->listsegment_lines) && count($sarbacane->listsegment_lines)>0) {
				foreach($sarbacane->listsegment_lines as $line) {
					if ($sarbacane->sarbacane_segmentid== $line['id']) {
						print $line['name'];
					}
				}
			}
		}
		print '</td></tr>';*/
	}


	// Other attributes
	$parameters=array();
	$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);
	if (empty($reshook) && ! empty($extrafields->attribute_label)) {
		foreach($extrafields->attribute_label as $key=>$label) {
			$value=(isset($_POST["options_".$key])?$_POST["options_".$key]:$object->array_options["options_".$key]);
			print '<tr class="impair"><td';
			if (! empty($extrafields->attribute_required[$key])) print ' class="fieldrequired"';
			print '>'.$label.'</td><td colspan="3">';
			print $extrafields->showInputField($key,$value);
			print "</td></tr>\n";
		}
	}

	print '</table>';

	if (empty($sarbacane->sarbacane_id) || $object->statut==0) {
		print '<form name="formmailing" method="post" action="'.$_SERVER["PHP_SELF"].'?id='.$id.'">';
		print '<input type="hidden" value="associateconfirm" name="action">';
		print '<input type="hidden" value="'.$_SESSION['newtoken'].'" name="token">';

		print '<br/><br/>';
		print '<table class="border tableforfield" width="100%" style="border:1px solid #ccc;padding:5px;">';

		print '<tr class="pair"><td colspan="3"><h3>Sarbacane</h3></td></tr>';
		print '<tr class="impair"><td width="30%">';
		print $langs->trans('SarbacaneCreateList');
		print '</td><td>';
		print '<input type="text" name="nameList" />';
		print '<input type="submit" class="button" name="createList" value="'.$langs->trans('Add').'"/>';
		print '</td><td>';
		print '</td></tr>';

		print '</table>';

		print '<br/><br/>';
		print '<table class="border tableforfield" width="100%" style="border:1px solid #ccc;padding:5px;">';

		print '<tr class="pair"><td colspan="3"><h3>Sarbacane</h3></td></tr>';
		print '<tr class="pair"><td class="fieldrequired" width="30%">';
		print $langs->trans('SarbacaneUpdateExistingList');
		print '</td><td>';
		$events=array();
		//if ($conf->use_javascript_ajax) {
		//$events[]=array('method' => 'getSegment', 'url' => dol_buildpath('/sarbacane/ajax/sarbacane.php',1), 'htmlname' => 'segmentlist','params' => array('blocksegement' => 'style'));
		//}
		print $formsarbacane->select_sarbacanelist('selectlist',1,$sarbacane->sarbacane_listid,0,$events);
		print '&nbsp;&nbsp;<input type="submit" class="button" name="save" value="'.$langs->trans('Save').'" />';
		print '</td><td>';
		print '</td></tr>';

		print '<tr class="impair"><td colspan="3" style="text-align:center">';
		print img_picto($langs->trans('Sarbacane_SyncLoading'), 'sync_loading.gif@sarbacane', 'id="sarbacane_loading" style="display:none;margin:20px auto 0"');
		print '</td></tr>';

		print '<tr class="pair">';
		print '<td style="text-align:right"><input id="bt_send_import" type="button" class="button" onclick="sarbacaneCallImport()" value="'.$langs->trans('SarbacaneImportForm').'" />';
		print $form->textwithpicto('',$langs->trans('SarbacaneImportFormHelp'));
		print '</td><td></td><td>';
		print '<input id="bt_send_export" type="button" class="button" onclick="sarbacaneCallExport()" value="'.$langs->trans('SarbacaneExportTo').'" />';
		print $form->textwithpicto('',$langs->trans('SarbacaneExportToHelp'));
		print '</td></tr>';
		print '</table>';

		print '<form>';
	}

	print "</div>";


	if(!strpos($object->email_from,'@')){
		dol_htmloutput_mesg($langs->trans("SarbacaneSenderMustBeAnEmail"),'','error',1);
	}
	if ($warning_destnotsync) {
		dol_htmloutput_mesg($langs->trans("SarbacaneEmailNotSync"),'','warning',1);
		if (count($email_in_dol_not_in_sarbacane)>0) {
			dol_htmloutput_mesg($langs->trans("SarbacaneEmailNotSyncInDolNotSarbacane").'<br>'.implode('<br>',$email_in_dol_not_in_sarbacane),'','warning',1);
		}
	}
    if($error_sendername) {
        dol_htmloutput_mesg($langs->trans("SarbacaneSenderNameMandatory"), '', 'error', 1);
    }
	if ($object->statut == 0) {
		if ((float) DOL_VERSION < 3.7) dol_htmloutput_mesg($langs->trans("SarbacaneNotValidated").' : <a href="'.dol_buildpath('/comm/mailing/fiche.php',1).'?id='.$object->id.'">'.$langs->trans('Mailing').'</a>','','warning',1);
		else  dol_htmloutput_mesg($langs->trans("SarbacaneNotValidated").' : <a href="'.dol_buildpath('/comm/mailing/card.php',1).'?id='.$object->id.'">'.$langs->trans('Mailing').'</a>','','warning',1);
	}

	print "\n\n<div class=\"tabsAction\">\n";
	if (($object->statut == 0) && $user->rights->mailing->creer) {
		if ((float) DOL_VERSION < 3.7) print '<a class="butAction" href="'.dol_buildpath('/comm/mailing/fiche.php',1).'?action=edit&amp;id='.$object->id.'">'.$langs->trans("EditMailing").'</a>';
		else print '<a class="butAction" href="'.dol_buildpath('/comm/mailing/card.php',1).'?action=edit&amp;id='.$object->id.'">'.$langs->trans("EditMailing").'</a>';
	}

	if (($object->statut == 1 || $object->statut == 2) && $object->nbemail > 0 && $user->rights->mailing->valider && !$error_sarbacane_control) {
		if ((! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! $user->rights->mailing->mailing_advance->send)) {
			print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->transnoentitiesnoconv("NotEnoughPermissions")).'">'.$langs->trans("SarbacaneCreateCampaign").'</a>';
		} else {
			if (empty($sarbacane->sarbacane_id)) {
				print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=createsarbacanecampaign&amp;id='.$object->id.'">'.$langs->trans("SarbacaneCreateCampaign").'</a>';
			}
		}
	}else {
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->transnoentitiesnoconv("SarbacaneCannotSendControlNotOK")).'">'.$langs->trans("SarbacaneCreateCampaign").'</a>';
	}
	if (!empty($sarbacane->sarbacane_id) && !$error_sarbacane_control) {
		if (($object->statut == 1 || $object->statut == 2) && $object->nbemail > 0 && $user->rights->mailing->valider) {
			if ((! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! $user->rights->mailing->mailing_advance->send)) {
				print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->transnoentitiesnoconv("NotEnoughPermissions")).'">'.$langs->trans("SendMailing").'</a>';
			} else {
				print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=sendsarbacanecampaign&amp;id='.$object->id.'">'.$langs->trans("SarbacaneSendMailing").'</a>';
			}
		}
	}

	if (!empty($sarbacane->sarbacane_id) && !$error_sarbacane_control) {
		if (($object->statut == 3 ) && $object->nbemail > 0 && $user->rights->mailing->valider) {
			if ((! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! $user->rights->mailing->mailing_advance->send)) {
				print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->transnoentitiesnoconv("NotEnoughPermissions")).'">'.$langs->trans("SendMailing").'</a>';
			} else {
				print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=updatesarbacanecampaignstatus&amp;id='.$object->id.'">'.$langs->trans("SarbacaneUpdateStatus").'</a>';
			}
		}
		//TODO: manage with jquery to avoid timeout browser
		//print '<input type="button" name="updatesarbacanecampaignstatus" id="updatesarbacanecampaignstatus" value="' . $langs->trans ( 'SarbacaneUpdateStatus' ) . '" class="butAction"/>';
	}

	print '<br><br></div>';


	// Print mail content
	print_fiche_titre($langs->trans("EMail"),'','');
	print '<table class="border" width="100%">';

	// Subject
	print '<tr><td width="15%">'.$langs->trans("MailTopic").'</td><td colspan="3">'.$object->sujet.'</td></tr>';

	// Message
	print '<tr><td valign="top">'.$langs->trans("MailMessage").'</td>';
	print '<td colspan="3" bgcolor="'.($object->bgcolor?(preg_match('/^#/',$object->bgcolor)?'':'#').$object->bgcolor:'white').'">';
	print dol_htmlentitiesbr($object->body);
	print '</td>';
	print '</tr>';

	print '</table>';
	print "<br>";
}else {
	dol_htmloutput_mesg($langs->trans("InvalidAPIKey"),'','error',1);
}

?>
<script type="text/javascript">
	sarbacaneTimer = null;
	TsarbacanePid = [];
	<?php
	if (!empty($_SESSION['SARBACANE_PID_ACTIVE'][$object->id])) {
		foreach ($_SESSION['SARBACANE_PID_ACTIVE'][$object->id] as $lid => $TPid) {
			foreach ($TPid as $pid) {
			?>
				TsarbacanePid.push(<?php echo $pid; ?>);
			<?php
			}
		}
	}
	?>


	triggerIntervalChecker = function() {
		$('#bt_send_export').prop('disabled',true);
		$('#bt_send_import').prop('disabled',true);
		$('#sarbacane_loading').css('display', 'block');

		sarbacaneTimer = setInterval(function() {
			var listid = $('#selectlist').val();
			var fk_mailing = <?php echo $object->id; ?>;
			$.ajax({
				url: '<?php echo dol_buildpath('/sarbacane/script/interface.php', 1); ?>'
				,type: 'GET'
				,dataType: 'json'
				,data: {
					json: 1
					,get: 'pidIsRunning'
					,TSarbacanePid: TsarbacanePid
					,listid: listid
					,fk_mailing: fk_mailing
				}
			}).done(function(reload) {
				if (reload) {
					window.location.href = '<?php echo dol_buildpath('/sarbacane/sarbacane.php', 1).'?id='.$object->id; ?>';
				}
			});
		}, 5000);
	};

	if (TsarbacanePid.length > 0) {
		triggerIntervalChecker();
	}

	sarbacaneCallExport = function() {
		sarbacaneCallAjax('export', '');
	};

	sarbacaneCallImport = function() {
		sarbacaneCallAjax('import', '');
	};

	sarbacaneCallAjax = function(set, get) {
		var listid = $('#selectlist').val();
		var fk_mailing = <?php echo $object->id; ?>;
		$.ajax({
			url: '<?php echo dol_buildpath('/sarbacane/script/interface.php', 1); ?>'
			,type: 'GET'
			,dataType: 'json'
			,data: {
				json: 1
				,get: get
				,set: set
				,listid: listid
				,fk_mailing: fk_mailing
				,TsarbacanePid: TsarbacanePid
			}
		}).done(function(pid) {
			if (pid > 0) {
				TsarbacanePid.push(pid);
				if (sarbacaneTimer === null) triggerIntervalChecker();
			}
		});
	};

</script>
<?php
// End of page
dol_fiche_end();
llxFooter();
$db->close();
