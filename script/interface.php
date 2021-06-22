<?php

$sapi_type = php_sapi_name();
// from ajax call (apache2handler) from php command line (cli)
if (substr($sapi_type, 0, 3) == 'cli')
{
	@set_time_limit(0);

	define('INC_FROM_CRON_SCRIPT', 1);
	chdir(dirname(__FILE__));

	// get params
	foreach($argv as $key => $val)
	{
		if (preg_match('/async_action=([^\s]+)$/',$val,$reg)) $async_action=$reg[1];
		if (preg_match('/listid=([^\s]+)$/',$val,$reg)) $listid=$reg[1];
		if (preg_match('/fk_mailing=([^\s]+)$/',$val,$reg)) $fk_mailing=$reg[1];
		if (preg_match('/fk_user=([^\s]+)$/',$val,$reg)) $fk_user=$reg[1];
	}
}

require '../config.php';
require_once DOL_DOCUMENT_ROOT.'/comm/mailing/class/mailing.class.php';
dol_include_once('/sarbacane/class/dolsarbacane.class.php');

if (!empty($fk_user) && $user->id != $fk_user)
{
	$user->fetch($fk_user);
}


$get=GETPOST('get', 'none');
//$async_action=GETPOST('async_action', 'none');
$set=GETPOST('set', 'none');

if (empty($listid)) $listid = GETPOST('listid', 'none');
if (empty($fk_mailing)) $fk_mailing = (int)GETPOST('fk_mailing', 'int');

if (empty($listid)) return _out('listid param missing');
if (empty($fk_mailing)) return _out('fk_mailing param missing');

$sarbacane= new DolSarbacane($db);
$sarbacane->fetch_by_mailing($fk_mailing);

$sarbacane->sarbacane_listid=$listid;
$sarbacane->fk_mailing=$fk_mailing;

// Cas possible lors d'un import
if (empty($sarbacane->id))
{
	$result=$sarbacane->create($user);
	if ($result<0) {
		return _out($sarbacane->error);
	}
}
else
{
	$result=$sarbacane->update($user);
	if ($result<0) {
		return _out($sarbacane->error);
	}
}

switch ($get) {
	case 'pidIsRunning':
		$TSarbacanePid = GETPOST('TSarbacanePid', 'none');

		if (!empty($TSarbacanePid))
		{
			foreach ($TSarbacanePid as $pid)
			{
				// Si j'ai au moin 1 pid en cours je renvoi l'info qu'il ne faut pas reload la page
				if (file_exists('/proc/'.$pid))
				{
					_out(false);
					exit;
				}

				unset($_SESSION['SARBACANE_PID_ACTIVE'][$fk_mailing][$listid][$pid]);
			}
		}

		_out(true);
		exit;

		break;
}

switch ($set) {
	case 'export':
		$script = dol_buildpath('/sarbacane/script/interface.php', 0);
		$params = 'async_action=export listid='.$listid.' fk_mailing='.$fk_mailing.' fk_user='.$user->id;

		$pid = exec('php '.$script.' '.$params.' > /dev/null 2>&1 & echo $!;');
		$_SESSION['SARBACANE_PID_ACTIVE'][$fk_mailing][$listid][$pid] = $pid;

		_out($pid);
		exit;

		break;
	case 'import':
		$script = dol_buildpath('/sarbacane/script/interface.php', 0);
		$params = 'async_action=import listid='.$listid.' fk_mailing='.$fk_mailing.' fk_user='.$user->id;

		$pid = exec('php '.$script.' '.$params.' > /dev/null 2>&1 & echo $!;');
		$_SESSION['SARBACANE_PID_ACTIVE'][$fk_mailing][$listid][$pid] = $pid;

		_out($pid);
		exit;

		break;
}

switch ($async_action) {
	case 'export':

		$result=$sarbacane->exportDesttoSarbacane($listid);
		exit;
		break;
	case 'import':
		$result=$sarbacane->importSegmentDestToDolibarr($listid);
		exit;
		break;
}

function _out($data, $type='', $callback='') {

	if(empty($type)) {
		if(isset($_REQUEST['gz'])) $type = 'gz';
		else if(isset($_REQUEST['gz2']))$type = 'gz2';
		elseif(isset($_REQUEST['json'])) $type='json';
		elseif(isset($_REQUEST['jsonp'])) $type='jsonp';
	}



	if($type==='gz') {
		$s = serialize($data);
		print gzdeflate($s,9);
	}
	elseif($type==='gz2') {
		$s = serialize($data);
		print gzencode($s,9);
	}
	elseif($type==='json') {
		print json_encode($data);
	}
	elseif($type==='jsonp') {
		if(empty($callback) && isset($_GET['callback'])) $callback = $_GET['callback'];
		print $callback.'('.json_encode($data).');' ;
	}
	else{
		$s = serialize($data);
		print $s;
	}

}
