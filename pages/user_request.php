<?php
include "../include/db.php";
include "../include/general.php";


$error=false;
$error_extra="";

$user_email=getval("email","");
hook("preuserrequest");

if (getval("save","")!="")
	{
	# Check the anti-spam code is correct
	if (getval("antispamcode","")!=md5(getval("antispam","")))
		{
		$error=$lang["requiredfields"];
		}
	
	# Check that the e-mail address doesn't already exist in the system
	elseif (user_email_exists(getval("email","")))
		{
		# E-mail already exists
		$error=$lang["accountemailalreadyexists"];$error_extra="<br/><a href=\"".$baseurl_short."pages/user_password.php?email=" . urlencode(getval("email","")) . "\">" . $lang["forgottenpassword"] . "</a>";
		}
	else
		{
		# E-mail is unique
		
		if ($user_account_auto_creation)
			{	
			# Automatically create a new user account
			$try=auto_create_user_account();
			}
		else
			{
			$try=email_user_request();
			}
			
		if ($try===true)
			{
			redirect($baseurl_short."pages/done.php?text=user_request");
			}
		else
			{
			$error=$lang["requiredfields"];
			}
		}
	}
include "../include/header.php";
?>

<h1><?php echo $lang["requestuserlogin"]?></h1>
<p><?php echo text("introtext")?></p>

<form method="post" action="<?php echo $baseurl_short?>pages/user_request.php">  

<?php if (!hook("replacemain")) { /* BEGIN hook Replacemain */ ?>

<div class="Question">
<label for="name"><?php echo $lang["yourname"]?> <sup>*</sup></label>
<input type=text name="name" id="name" class="stdwidth" value="<?php echo htmlspecialchars(getvalescaped("name",""))?>">
<div class="clearerleft"> </div>
</div>

<div class="Question">
<label for="email"><?php echo $lang["youremailaddress"]?> <sup>*</sup></label>
<input type=text name="email" id="email" class="stdwidth" value="<?php echo htmlspecialchars(getvalescaped("email",""))?>">
<div class="clearerleft"> </div>
</div>

<?php } /* END hook Replacemain */ ?>

<?php # Add custom fields 
if (isset($custom_registration_fields))
	{
	$custom=explode(",",$custom_registration_fields);
	$required=explode(",",$custom_registration_required);
	
	for ($n=0;$n<count($custom);$n++)
		{
		$type=1;
		
		# Support different question types for the custom fields.
		if (isset($custom_registration_types[$custom[$n]])) {$type=$custom_registration_types[$custom[$n]];}
		
		if ($type==4)
			{
			# HTML type - just output the HTML.
			$html = $custom_registration_html[$custom[$n]];
			if (is_string($html))
				echo $html;
			else if (isset($html[$language]))
				echo $html[$language];
			else if (isset($html[$defaultlanguage]))
				echo $html[$defaultlanguage];
			}
		else
			{
			?>
			<div class="Question">
			<label for="custom<?php echo $n?>"><?php echo htmlspecialchars(i18n_get_translated($custom[$n]))?>
			<?php if (in_array($custom[$n],$required)) { ?><sup>*</sup><?php } ?>
			</label>
			
			<?php if ($type==1) {  # Normal text box
			?>
			<input type=text name="custom<?php echo $n?>" id="custom<?php echo $n?>" class="stdwidth" value="<?php echo htmlspecialchars(getvalescaped("custom" . $n,""))?>">
			<?php } ?>

			<?php if ($type==2) { # Large text box 
			?>
			<textarea name="custom<?php echo $n?>" id="custom<?php echo $n?>" class="stdwidth" rows="5"><?php echo htmlspecialchars(getvalescaped("custom" . $n,""))?></textarea>
			<?php } ?>

			<?php if ($type==3) { # Drop down box
			?>
			<select name="custom<?php echo $n?>" id="custom<?php echo $n?>" class="stdwidth">
			<?php foreach ($custom_registration_options[$custom[$n]] as $option)
				{
				?>
				<option><?php echo htmlspecialchars(i18n_get_translated($option));?></option>
				<?php
				}
			?>
			</select>
			<?php } ?>
			
			<?php if ($type==5) { # checkbox
				?>
				<div class="stdwidth">			
					<table>
						<tbody>
						<?php								
						$i=0;
						foreach ($custom_registration_options[$custom[$n]] as $option)		# display each checkbox
							{
							$i++;
							$option_exploded = explode (":",$option);
							if (count($option_exploded) == 2)		# there are two fields, the first indicates if checked by default, the second is the name
								{
								$option_checked = ($option_exploded[0] == "1");
								$option_label = htmlspecialchars(i18n_get_translated(trim($option_exploded[1])));
								}
							else		# there are not two fields so treat the whole string as the name and set to unchecked
								{
								$option_checked = false;
								$option_label = htmlspecialchars(i18n_get_translated(trim($option)));
								}
							$option_field_name = "custom" . $n . "_" . $i;		# same format as all custom fields, but with a _<n> indicating sub field number
							?>
							<tr>
								<td>
									<input name="<?php echo $option_field_name; ?>" id="<?php echo $option_field_name; ?>" type="checkbox" <?php if ($option_checked) { ?> checked="checked"<?php } ?> value="<?php echo $option_label; ?>"></input>
								</td>
								<td>
									<label for="<?php echo $option_field_name; ?>" class="InnerLabel"><?php echo $option_label;?></label>												
								</td>
							</tr>
							<?php					
							}			
						?>				
						</tbody>
					</table>
				</div>			
			<?php } ?>
			
			<div class="clearerleft"> </div>
			</div>
			<?php
			}
		}
	}
?>

<?php if (!hook("replacegroupselect")) { /* BEGIN hook Replacegroupselect */ ?>
<?php if ($registration_group_select) {
# Allow users to select their own group
$groups=get_registration_selectable_usergroups();
?>
<div class="Question">
<label for="usergroup"><?php echo $lang["group"]?></label>
<select name="usergroup" id="usergroup" class="stdwidth">
<?php for ($n=0;$n<count($groups);$n++)
	{
	?>
	<option value="<?php echo $groups[$n]["ref"] ?>"><?php echo htmlspecialchars($groups[$n]["name"]) ?></option>
	<?php
	}
?>
</select>
<div class="clearerleft"> </div>
</div>	
<?php } ?>
<?php } /* END hook Replacegroupselect */ ?>

<?php if (!hook("replaceuserrequestcomment")){ ?>
<div class="Question">
<label for="userrequestcomment"><?php echo $lang["userrequestcomment"]?></label>
<textarea name="userrequestcomment" id="userrequestcomment" class="stdwidth"><?php echo htmlspecialchars(getvalescaped("userrequestcomment",""))?></textarea>
<div class="clearerleft"> </div>
</div>	
<?php } /* END hook replaceuserrequestcomment */ ?>

<?php hook("userrequestadditional");?>

<br />

<?php
$code=rand(1000,9999);
?>
<input type="hidden" name="antispamcode" value="<?php echo md5($code)?>">
<div class="Question">
<label for="antispam"><?php echo $lang["enterantispamcode"] . " " . $code ?></label>
<input type=text name="antispam" id="antispam" class="stdwidth" value="">
<div class="clearerleft"> </div>
</div>


<div class="QuestionSubmit">
<?php if ($error) { ?><div class="FormError">!! <?php echo $error ?> !!<?php echo $error_extra?></div><br /><?php } ?>
<label for="buttons"> </label>			
<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["requestuserlogin"]?>&nbsp;&nbsp;" />
</div>
</form>

<p><sup>*</sup> <?php echo $lang["requiredfield"] ?></p>	

<?php
include "../include/footer.php";
?>

