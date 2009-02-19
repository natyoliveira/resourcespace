<?php
include "../include/db.php";
include "../include/authenticate.php";
include "../include/general.php";
include "../include/collections_functions.php";

$ref=getvalescaped("ref","");

include "../include/header.php";
?>

<? $colname=sql_query("select name from collection where ref = '$ref'",""); $colname=$colname[0]['name'];?>

<div class="BasicsBox">
<h1><?php echo $lang["collectionlog"];?> - <a href="<?php echo $baseurl;?>/pages/collections.php?collection=<?php echo $ref?>" target="collections"><?php echo $colname;?></a></h1>
</div>

<div class="Listview">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<!--Title row-->	
<tr class="ListviewTitleStyle">
<td><?php echo $lang["date"]?></td>
<td><?php echo $lang["user"]?></td>
<td><?php echo $lang["action"]?></td>
<td><?php echo $lang["resourceid"]?></td>
<td><?php echo $lang["resourcetitle"]?></td>
</tr>

<?php
$log=get_collection_log($ref);
for ($n=0;$n<count($log);$n++)
	{
	?>
	<!--List Item-->
	<tr>
	<td><?php echo $log[$n]["date"]?></td>
	<td><?php echo $log[$n]["username"]?> (<?php echo $log[$n]["fullname"]?>)</td>
	<td><?php echo $lang["collectionlog-" . $log[$n]["type"]]?></td>
	<td><?php echo $log[$n]["resource"]?></td>
	<td><?php echo i18n_get_translated($log[$n]["title"])?></td>
	</tr>
	<?php
	}
?>
</table>
</div>
<?php
include "../include/footer.php";
?>
