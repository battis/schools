<?php

require_once ("config.php");
require_once ("library.php");
if ($_SESSION["user"]["auth"] == false) { header ("location: index.php"); exit(); } else {

if (isset ($_GET["id"]))
{
	$school = mysqlQuery ("SELECT * FROM {$db["schools"]}
		WHERE
			`user_id` = '{$_SESSION["user"]["id"]}' AND
			`id` = '{$_GET["id"]}'", __FILE__, __LINE__);
	
	if (mysql_num_rows ($school) == 0)
	{
		unset ($_SESSION["school"]);
		setSessionMessage ("There was an error accessing a school (ID={$_GET["id"]}).", "error");
		abort(__FILE__, __LINE__);
	}
	else
	{
		$_SESSION["school"] = mysql_fetch_assoc ($school);		
	}
	
	$status = currentStatus ($_SESSION["school"]["id"]);
	
	$notes = mysqlQuery ("SELECT *,
			DATE_FORMAT(`date`, '{$config["pretty date format"]}') AS `pretty date`,
			DATE_FORMAT(`next`, '{$config["pretty date format"]}') AS `pretty next`
		FROM {$db["notes"]}
		WHERE
			`user_id` = '{$_SESSION["user"]["id"]}' AND
			`school_id` = '{$_SESSION["school"]["id"]}' AND
			`id` <> '{$status["id"]}'
		ORDER BY
			`date` DESC, `id` DESC", __FILE__, __LINE__);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title><?= $_SESSION["school"]["name"] ?> Notes</title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="screen.css" />
</head>
<body>

<div id="content">
	
<h1><a href="home.php">&laquo;</a> <?= $_SESSION["school"]["name"] ?></h1>

<?php displaySessionMessages(); ?>

<h2>Current Status (<a href="note.php?school=<?= $_SESSION["school"]["id"] ?>">Add a note</a>)</h2>

<h3>On <?= $status["pretty date"]?>, with <?= contactList ($status["id"]) ?></h3>

<p><?= format ($status["note"]) ?></p>

<h3>Next Action <?= statusNext ($status) ?></h3>

<p><?= statusAction ($status) ?></p>

<?php if (mysql_num_rows ($notes) > 0) { ?>
<h2>History</h2>

<table>
	<tr>
		<th>Date</th>
		<th>Contact</th>
		<th>Note</th>
		<th>Next</th>
		<th>Action</th>
	</tr>
<?php

startStripes();
while ($note = mysql_fetch_assoc ($notes))
{
	echo "\t<tr" . stripe() . ">\n";
	echo "\t\t<td>{$note["pretty date"]}</td>\n";
	echo "\t\t<td>" . contactList ($note["id"]) . "</td>\n";
	echo "\t\t<td>" . format ($note["note"]) . "</td>\n";
	echo "\t\t<td>{$note["pretty next"]}</td>\n";
	echo "\t\t<td>" . format ($note["action"]) . "</td>\n";
	echo "\t</tr>\n";
}

?>
</table>
<?php } /* mysql_num_rows ($notes) */ ?>
	
</div>
	
</body>
</head>
<?php

} /* auth check */

unset ($_SESSION["contact"]);
unset ($_SESSION["position"]);
unset ($_SESSION["note"]);

?>