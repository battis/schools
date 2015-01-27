<?php

function checkMark ($value)
{
	if ($value)
	{
		return "X";
	}
	return "&nbsp;";
}

$stripeCounter = 1;
function startStripes()
{
	global $stripeCounter;
	$stripeCounter = 1;
}

function stripe()
{
	global $stripeCounter;
	$stripeCounter++;
	if ($stripeCounter % 2 == 0)
	{
		return " class=\"stripe\"";
	}
	return "";
}

function displaySessionMessages()
{
	global $config;
	if (isset ($_SESSION["messages"]))
	{
		foreach ($_SESSION["messages"] as $m)
		{
			if (($m["class"] != "debug") ||
				(($m["class"] == "debug") && ($config["show debug messages"])))
			echo "<p class=\"{$m["class"]}\">{$m["message"]}</p>\n";
		}
	}
	unset ($_SESSION["messages"]);
}

function setSessionMessage ($message, $class = null)
{
	$_SESSION["messages"][] = array ("message" => $message, "class" => ($class ? $class : "message"));
}

function abort($loc = __FUNCTION__, $line = null)
{
	setSessionMessage ("Script execution aborted at {$loc}" . ($line !== null ? ", line {$line}" : "") . ".", "error");
	if (isset ($_SESSION["note"]["id"]))
	{
		header ("location: note.php?id={$_SESSION["note"]["id"]}");
	}
	else if (isset ($_SESSION["position"]["id"]))
	{
		header ("location: position.php?id={$_SESSION["position"]["id"]}");
	}
	else if (isset ($_SESSION["contact"]["id"]))
	{
		header ("location: contact.php?id={$_SESSION["contact"]["id"]}");
	}
	else if (isset ($_SESSION["school"]["id"]))
	{
		header ("location: school.php?id={$_SESSION["school"]["id"]}");
	}
	else
	{
		header ("location: home.php");
	}
	exit();
}

function mysqlQuery ($query, $loc = __FUNCTION__, $line = null, $abort = true)
{
	$result = mysql_query ($query);
	if (!$result)
	{
		setSessionMessage ("{$loc}" . ($line !== null ? ", line {$line}" : "") . "<br />" . mysql_error() . "<br /><code>$query</code>", "debug");
		if ($abort)
		{
			abort(__FUNCTION__);
		}
	}
	return $result;
}

function currentStatus ($school)
{
	global $config, $db;
	$result = mysqlQuery ("SELECT *,
				DATE_FORMAT(`date`, '{$config["pretty date format"]}') AS `pretty date`,
				DATE_FORMAT(`next`, '{$config["pretty date format"]}') AS `pretty next`
			FROM {$db["notes"]}
			WHERE
				`user_id` = '{$_SESSION["user"]["id"]}' AND
				`school_id` = '$school'
			ORDER BY
				`date` DESC, `id` DESC
			LIMIT 1",
			__FILE__ . ":" . __FUNCTION__, __LINE__);
	return mysql_fetch_assoc ($result);
}

function contactList ($note)
{
	global $db;
	$contacts =  mysqlQuery ("SELECT *
		FROM {$db["notes-contacts"]} AS nc
		LEFT JOIN {$db["contacts"]} AS contacts
			ON nc.`contact_id` = contacts.`id`
		WHERE
			contacts.`user_id` = '{$_SESSION["user"]["id"]}' AND
			nc.`user_id` = '{$_SESSION["user"]["id"]}' AND
			nc.`note_id` = '$note'
		ORDER BY
			contacts.`name` ASC",
		__FILE__ . ":" . __FUNCTION__, __LINE__);
	$list = "";
	while ($contact = mysql_fetch_assoc ($contacts))
	{
		$list .= ", <a href=\"contact.php?id={$contact["id"]}\">{$contact["name"]}</a>";
	}
	$list = substr ($list, 2);
	return $list;
}

function statusDate ($status)
{
	return $status["pretty date"];
}

function statusNote ($status)
{
	return $status["note"];
}

function statusNext ($status)
{
	return $status["pretty next"];
}

function statusAction ($status)
{
	if (strlen ($status["action"]))
	{
		switch ($status["action"])
		{
			case "Schedule a meeting at NAIS":
			case "Schedule meeting at NAIS":
				return "<span class=\"alert\">{$status["action"]}</span>";
			case "Waiting":
			default:
				return format ($status["action"]);
		}
	}
	return "<span class=\"alert\">Follow&nbsp;up!</span>";
}

function format ($text)
{
	return preg_replace ("|\n+|", "<br />\n", $text);
}

?>