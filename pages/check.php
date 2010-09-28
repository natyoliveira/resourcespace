<?php
include "../include/db.php";
include "../include/general.php";
include "../include/authenticate.php"; if (!checkperm("a")) {exit("Access denied.");}
include "../include/header.php";

# A simple script to check the ResourceSpace hosting environment supports our needs.

function ResolveKB($value)
	{
	$value=trim(strtoupper($value));
	if (substr($value,-1,1)=="K")
		{
		return substr($value,0,strlen($value)-1);
		}
	if (substr($value,-1,1)=="M")
		{
		return substr($value,0,strlen($value)-1) * 1024;
		}
	if (substr($value,-1,1)=="G")
		{
		return substr($value,0,strlen($value)-1) * 1024 * 1024;
		}
	return $value;
	}

?>

<div class="BasicsBox"> 
  <h1><?php echo $lang["installationcheck"]?></h1>
  
<table class="InfoTable">
<?php
# Check PHP version
$phpversion=phpversion();
if ($phpversion<'4.4') {$result="FAIL: should be 4.4 or greater";} else {$result="OK";}
?><tr><td>PHP version</td><td><?php echo $phpversion?></td><td><b><?php echo $result?></b></td></tr><?php

# Check MySQL version
$mysqlversion=mysql_get_server_info();
if ($mysqlversion<'5') {$result="FAIL: should be 5 or greater";} else {$result="OK";}
?><tr><td>MySQL version</td><td><?php echo $mysqlversion?></td><td><b><?php echo $result?></b></td></tr><?php

# Check GD installed
if (function_exists("gd_info"))
	{
	$gdinfo=gd_info();
	if (is_array($gdinfo))
		{
		$version=$gdinfo["GD Version"];
		$result="OK";
		}
	else
		{
		$version="Not installed.";
		$result="FAIL";
		}
	}
else
	{
	$version="Not installed.";
	$result="FAIL";
	}
?><tr><td>GD version</td><td><?php echo $version?></td><td><b><?php echo $result?></b></td></tr><?php

# Check ini values for memory_limit, post_max_size, upload_max_filesize
$memory_limit=ini_get("memory_limit");
if (ResolveKB($memory_limit)<(200*1024)) {$result="WARNING: should be 200M or greater";} else {$result="OK";}
?><tr><td>PHP.INI value for 'memory_limit'</td><td><?php echo $memory_limit?></td><td><b><?php echo $result?></b></td></tr><?php

$post_max_size=ini_get("post_max_size");
if (ResolveKB($post_max_size)<(100*1024)) {$result="WARNING: should be 100M or greater";} else {$result="OK";}
?><tr><td>PHP.INI value for 'post_max_size'</td><td><?php echo $post_max_size?></td><td><b><?php echo $result?></b></td></tr><?php

$upload_max_filesize=ini_get("upload_max_filesize");
if (ResolveKB($upload_max_filesize)<(100*1024)) {$result="WARNING: should be 100M or greater";} else {$result="OK";}
?><tr><td>PHP.INI value for 'upload_max_filesize'</td><td><?php echo $upload_max_filesize?></td><td><b><?php echo $result?></b></td></tr><?php


# Check write access to filestore
$success=is_writable($storagedir);
if ($success===false) {$result="FAIL: $storagedir not writable";} else {$result="OK";}
?><tr><td colspan="2">Write access to 'filestore' directory</td><td><b><?php echo $result?></b></td></tr>


<?php
# Check filestore folder browseability
# $output=@file_get_contents($baseurl . "/filestore");
$output="";
if (strpos($output,"Index of")===false)
	{
	$result="OK";
	}
else
	{
	$result="FAIL: filestore folder appears to be browseable; remove 'Indexes' from Apache 'Options' list.";
	}
?><tr><td colspan="2">Blocked browsing of 'filestore' directory</td><td><b><?php echo $result?></b></td></tr>

<?php
$imagemagick_version="";
function CheckImagemagick()
	{
 	global $imagemagick_path;
 	
 	# Check for path
 	$path=$imagemagick_path . "/convert";
	if (!file_exists($path)) {$path=$imagemagick_path . "/convert.exe";}
	if (!file_exists($path)) {return false;}
	
	# Check execution and return version
	$version=@shell_exec(escapeshellcmd($path) . " -version");
	if (strpos($version,"ImageMagick")===false && strpos($version,"GraphicsMagick")===false)
		{
		return "Execution failed; unexpected output when executing convert command. Output was '$version'.<br>If on Windows and using IIS 6, access must be granted for command line execution. Refer to installation instructions in the wiki.";
		}	
		
	# Set version
	$s=explode("\n",$version);
	global $imagemagick_version;$imagemagick_version=$s[0];
	
	return true;
	}

$ffmpeg_version="";
function CheckFfmpeg()
{
 	global $ffmpeg_path;
 	
 	# Check for path
 	$path=$ffmpeg_path . "/ffmpeg";
	if (!file_exists($path)) {$path=$ffmpeg_path . "/ffmpeg.exe";}
	if (!file_exists($path)) {return false;}
	
	# Check execution and return version
	$version=@shell_exec(escapeshellcmd($path));
	if (strpos(strtolower($version),"ffmpeg")===false)
		{
		return "Execution failed; unexpected output when executing ffmpeg command. Output was '$version'.<br>If on Windows and using IIS 6, access must be granted for command line execution. Refer to installation instructions in the wiki.";
		}	
		
	# Set version
	$s=explode("\n",$version);
	global $ffmpeg_version;$ffmpeg_version=$s[0];
	
	return true;
}
function CheckGhostscript()
{
 	global $ghostscript_path;
	if (file_exists($ghostscript_path . "/gs")) return true;
	if (file_exists($ghostscript_path . "/gs.exe")) return true;	
	return false;
}
function CheckExiftool()
{
 	global $exiftool_path;
	if (file_exists($exiftool_path . "/exiftool")) return true;
	if (file_exists($exiftool_path . "/exiftool.exe")) return true;	
	return false;
}

# Check ImageMagick path
if (isset($imagemagick_path))
	{	 
	$result=CheckImagemagick();
	if ($result===true)
		{
		$result="OK";
		}
	else
		{
		$result="FAIL: " . $result;
		}
	}
else
	{
	$result="(not installed)";
	}
?><tr><td <?php if ($imagemagick_version=="") { ?>colspan="2"<?php } ?>>ImageMagick</td>
<?php if ($imagemagick_version!="") { ?><td><?php echo $imagemagick_version ?></td><?php } ?>
<td><b><?php echo $result?></b></td></tr><?php


# Check FFmpeg path
if (isset($ffmpeg_path))
	{
	if (CheckFfmpeg())
		{
		$result="OK";
		}
	else
		{
		$result="FAIL: '$ffmpeg_path/ffmpeg' not found";
		}
	}
else
	{
	$result="(not installed)";
	}
?><tr><td <?php if ($ffmpeg_version=="") { ?>colspan="2"<?php } ?>>FFmpeg</td>
<?php if ($ffmpeg_version!="") { ?><td><?php echo $ffmpeg_version ?></td><?php } ?>
<td><b><?php echo $result?></b></td></tr><?php


# Check Ghostscript path
if (isset($ghostscript_path))
	{
	if (CheckGhostscript())
		{
		$result="OK";
		}
	else
		{
		$result="FAIL: '$ghostscript_path/gs' not found";
		}
	}
else
	{
	$result="(not installed)";
	}
?><tr><td colspan="2">Ghostscript</td><td><b><?php echo $result?></b></td></tr><?php


# Check Exif function
if (function_exists('exif_read_data')) 
	{
	$result="OK";
	}
else
	{
	$version="Not installed.";
	$result="FAIL";
	}
?><tr><td colspan="2">EXIF extension installed</td><td><b><?php echo $result?></b></td></tr><?php


# Check Exiftool path
if (isset($exiftool_path))
	{
	if (CheckExiftool())
		{
		$result="OK";
		}
	else
		{
		$result="FAIL: '$exiftool_path/exiftool' not found";
		}
	}
else
	{
	$result="(not installed)";
	}
?><tr><td colspan="2">Exiftool</td><td><b><?php echo $result?></b></td></tr>


<tr>
<td>Last scheduled task execution (days)</td>
<td><?php $last_cron=sql_value("select datediff(now(),value) value from sysvars where name='last_cron'","Never");echo $last_cron ?></td>
<td><?php if ($last_cron>2 || $last_cron=="Never") { ?><b>WARNING</b><br/>Relevance matching will not be effective and periodic e-mail reports will not be sent. Ensure <a href="../batch/cron.php">batch/cron.php</a> is executed at least once daily via a cron job or similar.<?php } else {?><b>OK</b><?php } ?></td>

</tr>


</table>
</div>

<?php
include "../include/footer.php";
?>