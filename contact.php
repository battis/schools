<?php

require_once ("config.php");
require_once ("library.php");
if ($_SESSION["user"]["auth"] == false) { header ("location: index.php"); exit(); } else {

if (isset ($_GET["id"]))
{
	$contact = mysqlQuery ("SELECT * FROM {$db["contacts"]}
		WHERE
			`user_id` = '{$_SESSION["user"]["id"]}' AND
			`id` = '{$_GET["id"]}'
		LIMIT 1",
		__FILE__, __LINE__);
		

	if (mysql_num_rows ($contact) == 0)
	{
		unset ($_SESSION["contact"]);
		setSessionMessage ("There was an error accessing a contact (ID = {$_GET["id"]}).", "error");
		abort(__FILE__, __LINE__);
	}
	else
	{
		$_SESSION["contact"] = mysql_fetch_assoc ($contact);
	}
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title><?= "{$_SESSION["contact"]["name"]} ({$_SESSION["school"]["name"]})" ?></title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="screen.css" />
</head>
<body>

<h1><?= $_SESSION["contact"]["name"] ?></h1>

<?php displaySessionMessages(); ?>

<h2><?= "{$_SESSION["contact"]["title"]}, {$_SESSION["school"]["name"]}" ?></h2>
		
</body>
</html>
<?php

} /* auth check */

?>