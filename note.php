<?php

require_once ("config.php");
require_once ("library.php");
if ($_SESSION["user"]["auth"] == false) { header ("location: index.php"); exit(); } else {

if (isset ($_GET["school"]))
{
	$school = mysqlQuery ("SELECT * FROM {$db["schools"]}
		WHERE
			`user_id` = '{$_SESSION["user"]["id"]}' AND
			`id` = '{$_GET["school"]}'", __FILE__, __LINE__);
	
	if (mysql_num_rows ($school) == 0)
	{
		unset ($_SESSION["school"]);
		setSessionMessage ("There was an error accessing a school (ID={$_GET["school"]}).", "error");
		abort(__FILE__, __LINE__);
	}
	else
	{
		$_SESSION["school"] = mysql_fetch_assoc ($school);		
	}
}

if (!isset ($_SESSION["school"]["id"]))
{
	setSessionMessage ("No school specified", "error");
	abort(__FILE__, __LINE__);
}

if (isset ($_GET["position"]))
{
	$position = mysqlQuery ("SELECT * FROM {$db["positions"]}
		WHERE
			`user_id` = '{$_SESSION["user"]["id"]}' AND
			`id` = '{$_GET["position"]}'", __FILE__, __LINE__);
	
	if (mysql_num_rows ($position) == 0)
	{
		unset ($_SESSION["position"]);
		setSessionMessage ("There was an error accessing a position (ID={$_GET["position"]}).", "error");
		abort(__FILE__, __LINE__);
	}
	else
	{
		$_SESSION["position"] = mysql_fetch_assoc ($position);		
	}
}

if (isset ($_POST["button"]))
{
	if (strlen ($_POST["next"]))
	{
		$_POST["next"] = "'{$_POST["next"]}'";
	}
	else
	{
		$_POST["next"] = "NULL";
	}
	
	if (strlen ($_POST["action"]))
	{
		$_POST["action"] = "'{$_POST["action"]}'";
	}
	else
	{
		$_POST["action"] = "NULL";
	}
	
	if (strlen ($_POST["note"]) && strlen ($_POST["date"]))
	{
		mysqlQuery ("INSERT INTO {$db["notes"]} (`user_id`, `school_id`, " . (isset ($_SESSION["position"]) ? "`position_id`, " : "") . "`date`, `note`, `next`, `action`)
			VALUES (
				'{$_SESSION["user"]["id"]}',
				'{$_SESSION["school"]["id"]}'," .
				(isset ($_SESSION["position"]) ? "'{$_SESSION["position"]["id"]}', " : "") .
				"'{$_POST["date"]}',
				'{$_POST["note"]}',
				{$_POST["next"]},
				{$_POST["action"]})", __FILE__, __LINE__);
			
		$note = mysqlQuery ("SELECT * FROM {$db["notes"]}
			WHERE
				`user_id` = '{$_SESSION["user"]["id"]}' AND
				`id` = '" . mysql_insert_id() . "'");
		if (mysql_num_rows ($note) == 0)
		{
			unset ($_SESSION["note"]);
			setSessionMessage ("There was an error accessing a note (ID=" . mysql_insert_id() . ").", "error");
			abort(__FILE__, __LINE__);
		}
		else
		{
			setSessionMessage ("New note on {$_SESSION["school"]["name"]} has been saved.");
			$_SESSION["note"] = mysql_fetch_assoc ($note);		
		}
	}
	
	if ($_POST["contact"] > 0)
	{
		mysqlQuery ("INSERT INTO {$db["notes-contacts"]} (
				`user_id`,
				`note_id`,
				`contact_id`
				)
			VALUES (
				'{$_SESSION["user"]["id"]}',
				'{$_SESSION["note"]["id"]}',
				'{$_POST["contact"]}'
			)",
			__FILE__, __LINE__);
		setSessionMessage ("A contact has been attached to the new note on {$_SESSION["school"]["name"]}.");
	}
	
	if ($_POST["button"] == "Save")
	{
		unset ($_SESSION["note"]);
		header ("location: school.php?id={$_SESSION["school"]["id"]}");
		exit();
	}
	else if ($_POST["button"] == "Add Another Contact")
	{
		$status = currentStatus ($_SESSION["school"]["id"]);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>New Note for <?= $_SESSION["school"]["name"] ?></title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="screen.css" />
</head>
<body>

<div id="content">
	
<h1><a href="school.php?id=<?= $_SESSION["school"]["id"] ?>">&laquo;</a> <?= $_SESSION["school"]["name"] ?></h1>

<?php displaySessionMessages(); ?>

<h3>On <?= $status["pretty date"]?>, with <?= contactList ($status["id"]) ?></h3>

<form method="post" action="<?= $_SERVER["PHP_SELF"] ?>">
<?php
	
$school_contacts = mysqlQuery ("SELECT *
	FROM {$db["contacts"]}
	WHERE
		`user_id` = '{$_SESSION["user"]["id"]}' AND
		`school_id` = '{$_SESSION["school"]["id"]}'
	ORDER BY
		`name` ASC", __FILE__, __LINE__);
$other_contacts = mysqlQuery ("SELECT *
	FROM {$db["contacts"]}
	WHERE
		`user_id` = '{$_SESSION["user"]["id"]}' AND
		`school_id` <> '{$_SESSION["school"]["id"]}'
	ORDER BY
		`name` ASC", __FILE__, __LINE__);
		
	echo "\t<label for=\"contact\">Contact</label><select id=\"contact\" name=\"contact\">\n";
	echo "\t\t<option disabled>At {$_SESSION["school"]["name"]}:</option>";
	while ($contact = mysql_fetch_assoc ($school_contacts))
	{
		echo "\t\t<option value=\"{$contact["id"]}\">&nbsp;&nbsp;&nbsp;&nbsp;{$contact["name"]}</option>\n";
	}
	echo "\t\t<option disabled>Others:</option>\n";
	while ($contact = mysql_fetch_assoc ($other_contacts))
	{
		echo "\t\t<option value=\"{$contact["id"]}\">&nbsp;&nbsp;&nbsp;&nbsp;{$contact["name"]}</option>\n";
	}
	echo "\t</select>\n";
?>
	<p><input name="button" type="submit" value="Add Another Contact" /> <input name="button" type="submit" value="Save" /></p>
</form>

<p><?= format ($status["note"]) ?></p>

<h3>Next Action <?= statusNext ($status) ?></h3>

<p><?= statusAction ($status) ?></p>

<?php	}
}
else /* no button was pressed */
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>New Note for <?= $_SESSION["school"]["name"] ?></title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="screen.css" />
</head>
<body>

<div id="content">
	
<h1><a href="school.php?id=<?= $_SESSION["school"]["id"] ?>">&laquo;</a> <?= $_SESSION["school"]["name"] ?></h1>

<?php displaySessionMessages(); ?>

<form method="post" action="<?= $_SERVER["PHP_SELF"] ?>">
	<p><label for="date">Date</label><input id="date" name="date" type="text" value="<?= date ("Y-m-d") ?>" /></p>
	<p><label for="note">Note</label><textarea id="note" name="note" cols="80" rows="20"></textarea></p>
<?php
	
$school_contacts = mysqlQuery ("SELECT *
	FROM {$db["contacts"]}
	WHERE
		`user_id` = '{$_SESSION["user"]["id"]}' AND
		`school_id` = '{$_SESSION["school"]["id"]}'
	ORDER BY
		`name` ASC", __FILE__, __LINE__);
$other_contacts = mysqlQuery ("SELECT *
	FROM {$db["contacts"]}
	WHERE
		`user_id` = '{$_SESSION["user"]["id"]}' AND
		`school_id` <> '{$_SESSION["school"]["id"]}'
	ORDER BY
		`name` ASC", __FILE__, __LINE__);
		
	echo "\t<label for=\"contact\">Contact</label><select id=\"contact\" name=\"contact\">\n";
	echo "\t\t<option value=\"\">None</option>\n";
	echo "\t\t<option disabled>At {$_SESSION["school"]["name"]}:</option>";
	while ($contact = mysql_fetch_assoc ($school_contacts))
	{
		echo "\t\t<option value=\"{$contact["id"]}\">&nbsp;&nbsp;&nbsp;&nbsp;{$contact["name"]}</option>\n";
	}
	echo "\t\t<option disabled>Others:</option>\n";
	while ($contact = mysql_fetch_assoc ($other_contacts))
	{
		echo "\t\t<option value=\"{$contact["id"]}\">&nbsp;&nbsp;&nbsp;&nbsp;{$contact["name"]}</option>\n";
	}
	echo "\t</select>\n";
?>
	<p><label for="next">Next</label><input id="next" name="next" type="text" /></p>
	<p><label for="action">Action</label><textarea id="action" name="action" cols="80" rows="5"></textarea></p>
	<p><input name="button" type="submit" value="Add Another Contact" /> <input name="button" type="submit" value="Save" /></p>
</form>
	
</div>
	
</body>
</head>
<?php

} /* button pressed */
} /* auth check */

?>