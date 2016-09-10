<?php
error_reporting(0);
function sec2human($time) {
  $seconds = $time%60;
	$mins = floor($time/60)%60;
	$hours = floor($time/60/60)%24;
	$days = floor($time/60/60/24);
	return $days > 0 ? $days . ' day'.($days > 1 ? 's' : '') : $hours.':'.$mins.':'.$seconds;
}

$array = array();
$fh = fopen('/proc/uptime', 'r');
$uptime = fgets($fh);
fclose($fh);
if(strlen($uptime) == 0 ) {
	$uptime = exec('cat /proc/uptime');
}
$uptime = explode('.', $uptime, 2);
$array['uptime'] = sec2human($uptime[0]);

$fh = fopen('/proc/meminfo', 'r');
  $mem = 0;
  while ($line = fgets($fh)) {
    $pieces = array();
    if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
      $memtotal = $pieces[1];
    }
    if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $pieces)) {
      $memfree = $pieces[1];
    }
    if (preg_match('/^Cached:\s+(\d+)\skB$/', $line, $pieces)) {
      $memcache = $pieces[1];
      break;
    }
  }
fclose($fh);

if (!isset($memtotal)) {
	$memtotal = exec ("free | awk '{if ($1 == \"Mem:\") print $2}'");
}
if (!isset($memfree)) {
	$memfree = exec ("free | awk '{if ($1 == \"Mem:\") print $4}'");
}
if (!isset($memcache)) {
	$memcache = exec ("free | awk '{if ($1 == \"Mem:\") print $7}'");
}

$memmath = $memcache + $memfree;
$memmath2 = $memmath / $memtotal * 100;
$memory = round($memmath2) . '%';

if ($memory >= "51%") { $memlevel = "success"; }
elseif ($memory <= "50%") { $memlevel = "warning"; }
elseif ($memory <= "35%") { $memlevel = "danger"; }

$array['memory'] = '<div class="progress progress-striped active">
<div class="bar bar-'.$memlevel.'" style="width: '.$memory.';">'.$memory.'</div>
</div>';

$hddtotal = disk_total_space("/");
if ($hddtotal === FALSE) { 
	$hddtotal = exec("df '/' | awk '{if ($1 != \"Filesystem\") print $2}'");
}
$hddfree = disk_free_space("/");
if ($hddfree === FALSE) { 
	$hddfree = exec("df '/' | awk '{if ($1 != \"Filesystem\") print $4}'");
}
$hddmath = $hddfree / $hddtotal * 100;
$hdd = round($hddmath) . '%';

if ($hdd >= "51%") { $hddlevel = "success"; }
elseif ($hdd <= "50%") { $hddlevel = "warning"; }
elseif ($hdd <= "35%") { $hddlevel = "danger"; }

$array['hdd'] = '<div class="progress progress-striped active">
<div class="bar bar-'.$hddlevel.'" style="width: '.$hdd.';">'.$hdd.'</div>
</div>';

$load = sys_getloadavg();
$array['load'] = $load[0];

$array['online'] = '<div class="progress">
<div class="bar bar-success" style="width: 100%;"><small>Up</small></div>
</div>';

echo json_encode($array);
