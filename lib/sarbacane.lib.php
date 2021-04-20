<?php
/* Copyright (C) 2021 ATM Consulting <support@atm-consulting.fr>
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
 *	\file		lib/sarbacane.lib.php
 *	\ingroup	sarbacane
 *	\brief		This file is an example module library
 *				Put some comments here
 */

/**
 * @return array
 */
function sarbacaneAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load('sarbacane@sarbacane');

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/sarbacane/admin/sarbacane_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/sarbacane/admin/sarbacane_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFields");
    $head[$h][2] = 'extrafields';
    $h++;
    $head[$h][0] = dol_buildpath("/sarbacane/admin/sarbacane_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@sarbacane:/sarbacane/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@sarbacane:/sarbacane/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'sarbacane');

    return $head;
}

/**
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	sarbacane	$object		Object company shown
 * @return 	array				Array of tabs
 */
function sarbacane_prepare_head(sarbacane $object)
{
    global $langs, $conf;
    $h = 0;
    $head = array();
    $head[$h][0] = dol_buildpath('/sarbacane/card.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("sarbacaneCard");
    $head[$h][2] = 'card';
    $h++;
	
	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@sarbacane:/sarbacane/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@sarbacane:/sarbacane/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'sarbacane');
	
	return $head;
}

/**
 * @param Form      $form       Form object
 * @param sarbacane  $object     sarbacane object
 * @param string    $action     Triggered action
 * @return string
 */
function getFormConfirmsarbacane($form, $object, $action)
{
    global $langs, $user;

    $formconfirm = '';

    if ($action === 'valid' && !empty($user->rights->sarbacane->write))
    {
        $body = $langs->trans('ConfirmValidatesarbacaneBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmValidatesarbacaneTitle'), $body, 'confirm_validate', '', 0, 1);
    }
    elseif ($action === 'accept' && !empty($user->rights->sarbacane->write))
    {
        $body = $langs->trans('ConfirmAcceptsarbacaneBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmAcceptsarbacaneTitle'), $body, 'confirm_accept', '', 0, 1);
    }
    elseif ($action === 'refuse' && !empty($user->rights->sarbacane->write))
    {
        $body = $langs->trans('ConfirmRefusesarbacaneBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmRefusesarbacaneTitle'), $body, 'confirm_refuse', '', 0, 1);
    }
    elseif ($action === 'reopen' && !empty($user->rights->sarbacane->write))
    {
        $body = $langs->trans('ConfirmReopensarbacaneBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmReopensarbacaneTitle'), $body, 'confirm_refuse', '', 0, 1);
    }
    elseif ($action === 'delete' && !empty($user->rights->sarbacane->write))
    {
        $body = $langs->trans('ConfirmDeletesarbacaneBody');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmDeletesarbacaneTitle'), $body, 'confirm_delete', '', 0, 1);
    }
    elseif ($action === 'clone' && !empty($user->rights->sarbacane->write))
    {
        $body = $langs->trans('ConfirmClonesarbacaneBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmClonesarbacaneTitle'), $body, 'confirm_clone', '', 0, 1);
    }
    elseif ($action === 'cancel' && !empty($user->rights->sarbacane->write))
    {
        $body = $langs->trans('ConfirmCancelsarbacaneBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCancelsarbacaneTitle'), $body, 'confirm_cancel', '', 0, 1);
    }

    return $formconfirm;
}
