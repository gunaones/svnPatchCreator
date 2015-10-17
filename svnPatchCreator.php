<?php
/*
 ___  ___    ___  _  _  _  _    ____   __   ____  ___  _   _     ___  ____  ____    __   ____  _____  ____    ___  ___ 
(___)(___)  / __)( \/ )( \( )  (  _ \ /__\ (_  _)/ __)( )_( )   / __)(  _ \( ___)  /__\ (_  _)(  _  )(  _ \  (___)(___)
 ___  ___   \__ \ \  /  )  (    )___//(__)\  )( ( (__  ) _ (   ( (__  )   / )__)  /(__)\  )(   )(_)(  )   /   ___  ___ 
(___)(___)  (___/  \/  (_)\_)  (__) (__)(__)(__) \___)(_) (_)   \___)(_)\_)(____)(__)(__)(__) (_____)(_)\_)  (___)(___)
*/

/** 
 * SVN Patch creator V 0.1
 * 20151017
 * How to : 
 * 1. Using argument
 * php svnPatchCreator.php --r="/var/www/html/" --s=1356 --e=1360
 * 
 * OR
 * 
 * 2. Using stdin
 * php svnPatchCreator.php
 * Enter existing svn working copy? /var/www/html/
 * Revision start? 1356
 * Revision end? 1360
 * Create Patch ready!...
 * 
 * @param $r : existing svn working copy 
 * @param $s : revision start
 * @param $e : revision end
 * @author Gunaones
 * @package Admin/Maintenance/Deploymen
 */
require_once('show_status.php');

class svnPatchCreator{
	function __construct($r=null, $s=null, $e=null, $z=true){
		$f = $s.'_'.$e.'.txt';
		$t = $s.'_'.$e;
		echo "\nCollect change logs!\n";
		$rs = exec('svn diff --summarize -r'.$s.':'.$e.' '.$r.' > '.$f);
		$l = exec('wc -l '.$f);
		$handle = fopen($f, "r");
		if ($handle) {
			$i = 0;
			echo "\nStart Copy!\n";
			while (($line = fgets($handle)) !== false) {
				$this->run($r, trim(explode('       ',$line)[1]), $t);
				show_status(++$i, $l);
				usleep(100000);
			}
			exec('mv '.$f.' '.$t);
			fclose($handle);
			if($z==true){
				exec('zip -r '.$t.'.zip "'.$t.'"');
			}
			echo "\nDone!\n";
		} else {
			echo 'error opening the file.';
		}
	}

	function run($r,$f,$t){
		if(is_file($f)){
			$nd = $t.'/'.str_replace($r,'',dirname($f));
			$nf = $t.'/'.str_replace($r,'',$f);
			exec('mkdir -p '.$nd);
			exec('cp '.$f.' '.$nf);
		}
	}
}

function arguments($argv) {
	$_ARG = array();
	foreach ($argv as $arg) {
		if (ereg('--([^=]+)=(.*)',$arg,$reg)) {
			$_ARG[$reg[1]] = $reg[2];
		} else if(ereg('-([a-zA-Z0-9])',$arg,$reg)) {
			$_ARG[$reg[1]] = 'true';
		}
	}
	return $_ARG;
}

function read ($length='255'){
	if (!isset ($GLOBALS['StdinPointer']))
	{
	$GLOBALS['StdinPointer'] = fopen ("php://stdin","r");
	}
	$line = fgets ($GLOBALS['StdinPointer'],$length);
	return trim ($line);
}

if (php_sapi_name() == "cli") {
	$arg = arguments($argv);
	if(count($arg)==3){
		$r = @$arg['r'];
		$s = @$arg['s'];
		$e = @$arg['e'];
	}else{
		r :
		echo "Enter \"existing svn working copy\" ? ";
		$r = read();
		if(empty($r))
			goto r;
		s :
		echo "\nRevision start? ";
		$s = read();
		if(empty($s))
			goto s;
		e :
		echo "\nRevision end? ";
		$e = read();
		if(empty($e))
			goto e;
	}

	if(empty($r) || empty($s) || empty($e)){
		echo "\nParameter required : --r=? --s=? --e=?\n";
		exit();
	}else{
		echo "\nCreate Patch ready!...";
	}
	new svnPatchCreator($r, $s, $e);
} else {
	// Not in cli-mode
}