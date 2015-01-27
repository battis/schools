<?php

require_once ("config.php");
require_once ("library.php");
if ($_SESSION["user"]["auth"] == false) { header ("location: index.php"); exit(); } else {

$schools = mysqlQuery (
	"SELECT *
		FROM {$db["schools"]}
		WHERE
			`user_id` = '{$_SESSION["user"]["id"]}'
		GROUP BY
			`id`
		ORDER BY
			`name` ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Schools</title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="screen.css" />
</head>
<body>

<div id="content">
	
<h1>All Schools</h1>

<?php displaySessionMessages(); ?>

<h2>Active</h2>

<table>
	<tr>
		<th>School</th>
		<th>Position</th>
		<th>Last Contact</th>
		<th>Next</th>
		<th>Action</th>
	</tr>	
<?php

/* TODO combine open and closed openings into a single code base */
startStripes();
$closed = null;
$no_contact = null;
$keep_in_loop = null;
while ($school = mysql_fetch_assoc ($schools))
{
	$status = currentStatus ($school["id"]);
	if (strtolower ($status["action"]) == "closed")
	{
		$closed[] = $school;
	}
	else if (strtolower ($status["action"]) == "no contact")
	{
		$no_contact[] = $school;
	}
	else if (strtolower ($status["action"]) == "keep in loop")
	{
		$keep_in_loop[] = $school;
	}
	else
	{
		echo "\t<tr" . stripe() . ">\n";
		echo "\t\t<td><a href=\"school.php?id={$school["id"]}\">{$school["name"]}</a><br />\n" .
			"\t\t\t&nbsp;&nbsp;&nbsp;&nbsp;{$school["gender"]} {$school["lower_grade"]}-{$school["upper_grade"]}\n\t\t\t" .
			(strlen ($school["url"]) ? "<a href=\"{$school["url"]}\" target=\"_blank\"><img class=\"icon\" src=\"graphics/external_url.gif\" /></a>\n\t\t\t" : "") .
			(strlen ($school["street1"] . $school["street2"] . $school["city"] . $school["state"] . $school["zip"]) ?
				"<a href=\"http://maps.google.com/?q={$school["street1"]}, {$school["street2"]}, {$school["city"]}, {$school["state"]} {$school["zip"]} ({$school["name"]})\" target=\"_blank\"><img class=\"icon\" src=\"graphics/map_url.gif\" /></a>" :
				"") . 
			"</td>\n";
		$positions = mysqlQuery ("SELECT *
			FROM {$db["positions"]}
			WHERE
				`user_id` = '{$_SESSION["user"]["id"]}' AND
				`school_id` = '{$school["id"]}'
			ORDER BY
			`id` DESC", __FILE__, __LINE__);
		echo "\t\t<td>";
		while ($pos = mysql_fetch_assoc ($positions))
		{
			echo "{$pos["title"]}<br />\n\t\t\t";
		}
		echo "</td>\n";
		echo "\t\t<td>" . statusDate ($status) . "</td>\n";
		echo "\t\t<td>" . statusNext ($status) . "</td>\n";
		echo "\t\t<td>" . statusAction ($status) . "</td>\n";
		echo "\t</tr>\n";
	}
}

?>
</table>

<?php if (is_array ($keep_in_loop)) { ?>
<h2>Keep in Loop</h2>

<table width="100%">
	<tr>
		<th>School</th>
		<th>Position</th>
		<th>Last Contact</th>
		<th>Next</th>
		<th>Action</th>
	</tr>	
<?php

startStripes();
foreach ($keep_in_loop as $school)
{
	$status = currentStatus ($school["id"]);
	echo "\t<tr" . stripe() . ">\n";
	echo "\t\t<td><a href=\"school.php?id={$school["id"]}\">{$school["name"]}</a><br />\n" .
		"\t\t\t&nbsp;&nbsp;&nbsp;&nbsp;{$school["gender"]} {$school["lower_grade"]}-{$school["upper_grade"]}\n\t\t\t" .
		(strlen ($school["url"]) ? "<a href=\"{$school["url"]}\" target=\"_blank\"><img class=\"icon\" src=\"graphics/external_url.gif\" /></a>\n\t\t\t" : "") .
		(strlen ($school["street1"] . $school["street2"] . $school["city"] . $school["state"] . $school["zip"]) ?
			"<a href=\"http://maps.google.com/?q={$school["street1"]}, {$school["street2"]}, {$school["city"]}, {$school["state"]} {$school["zip"]} ({$school["name"]})\" target=\"_blank\"><img class=\"icon\" src=\"graphics/map_url.gif\" /></a>" :
			"") . 
		"</td>\n";
	$positions = mysqlQuery ("SELECT *
		FROM {$db["positions"]}
		WHERE
			`user_id` = '{$_SESSION["user"]["id"]}' AND
			`school_id` = '{$school["id"]}'
		ORDER BY
			`id` DESC", __FILE__, __LINE__);
	echo "\t\t<td>";
	while ($pos = mysql_fetch_assoc ($positions))
	{
		echo "{$pos["title"]}<br />\n\t\t\t";
	}
	echo "</td>\n";
	echo "\t\t<td>" . statusDate ($status) . "</td>\n";
	echo "\t\t<td>" . statusNext ($status) . "</td>\n";
	echo "\t\t<td>" . statusAction ($status) . "</td>\n";
	echo "\t</tr>\n";
}

?>
</table>
<?php } /* is_array ($keep_in_loop) */ ?>

<?php if (is_array ($no_contact)) { ?>
<h2>No Contact</h2>

<table width="100%">
	<tr>
		<th>School</th>
		<th>Position</th>
		<th>Last Contact</th>
		<th>Next</th>
		<th>Action</th>
	</tr>	
<?php

startStripes();
foreach ($no_contact as $school)
{
	$status = currentStatus ($school["id"]);
	echo "\t<tr" . stripe() . ">\n";
	echo "\t\t<td><a href=\"school.php?id={$school["id"]}\">{$school["name"]}</a><br />\n" .
		"\t\t\t&nbsp;&nbsp;&nbsp;&nbsp;{$school["gender"]} {$school["lower_grade"]}-{$school["upper_grade"]}\n\t\t\t" .
		(strlen ($school["url"]) ? "<a href=\"{$school["url"]}\" target=\"_blank\"><img class=\"icon\" src=\"graphics/external_url.gif\" /></a>\n\t\t\t" : "") .
		(strlen ($school["street1"] . $school["street2"] . $school["city"] . $school["state"] . $school["zip"]) ?
			"<a href=\"http://maps.google.com/?q={$school["street1"]}, {$school["street2"]}, {$school["city"]}, {$school["state"]} {$school["zip"]} ({$school["name"]})\" target=\"_blank\"><img class=\"icon\" src=\"graphics/map_url.gif\" /></a>" :
			"") . 
		"</td>\n";
	$positions = mysqlQuery ("SELECT *
		FROM {$db["positions"]}
		WHERE
			`user_id` = '{$_SESSION["user"]["id"]}' AND
			`school_id` = '{$school["id"]}'
		ORDER BY
			`id` DESC", __FILE__, __LINE__);
	echo "\t\t<td>";
	while ($pos = mysql_fetch_assoc ($positions))
	{
		echo "{$pos["title"]}<br />\n\t\t\t";
	}
	echo "</td>\n";
	echo "\t\t<td>" . statusDate ($status) . "</td>\n";
	echo "\t\t<td>" . statusNext ($status) . "</td>\n";
	echo "\t\t<td>" . statusAction ($status) . "</td>\n";
	echo "\t</tr>\n";
}

?>
</table>
<?php } /* is_array ($no_contact) */ ?>


<?php if (is_array ($closed)) { ?>
<h2>Closed</h2>

<table width="100%">
	<tr>
		<th>School</th>
		<th>Position</th>
		<th>Last Contact</th>
		<th>Next</th>
		<th>Action</th>
	</tr>	
<?php

startStripes();
foreach ($closed as $school)
{
	$status = currentStatus ($school["id"]);
	echo "\t<tr" . stripe() . ">\n";
	echo "\t\t<td><a href=\"school.php?id={$school["id"]}\">{$school["name"]}</a><br />\n" .
		"\t\t\t&nbsp;&nbsp;&nbsp;&nbsp;{$school["gender"]} {$school["lower_grade"]}-{$school["upper_grade"]}\n\t\t\t" .
		(strlen ($school["url"]) ? "<a href=\"{$school["url"]}\" target=\"_blank\"><img class=\"icon\" src=\"graphics/external_url.gif\" /></a>\n\t\t\t" : "") .
		(strlen ($school["street1"] . $school["street2"] . $school["city"] . $school["state"] . $school["zip"]) ?
			"<a href=\"http://maps.google.com/?q={$school["street1"]}, {$school["street2"]}, {$school["city"]}, {$school["state"]} {$school["zip"]} ({$school["name"]})\" target=\"_blank\"><img class=\"icon\" src=\"graphics/map_url.gif\" /></a>" :
			"") . 
		"</td>\n";
	$positions = mysqlQuery ("SELECT *
		FROM {$db["positions"]}
		WHERE
			`user_id` = '{$_SESSION["user"]["id"]}' AND
			`school_id` = '{$school["id"]}'
		ORDER BY
			`id` DESC", __FILE__, __LINE__);
	echo "\t\t<td>";
	while ($pos = mysql_fetch_assoc ($positions))
	{
		echo "{$pos["title"]}<br />\n\t\t\t";
	}
	echo "</td>\n";
	echo "\t\t<td>" . statusDate ($status) . "</td>\n";
	echo "\t\t<td>" . statusNext ($status) . "</td>\n";
	echo "\t\t<td>" . statusAction ($status) . "</td>\n";
	echo "\t</tr>\n";
}

?>
</table>
<?php } /* is_array ($closed) */ ?>

</div>

</body>
</html>
<?php

} /* auth check */

?>