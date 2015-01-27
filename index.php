<?php
require_once ("config.php");
require_once ("library.php");
if (($_SESSION["user"]["auth"]))
{
	header ("location: home.php");
	exit();
}

if (isset ($_POST["name"]))
{
	$user = mysqlQuery ("SELECT * FROM {$db["users"]}
		WHERE
			`name` = '{$_POST["name"]}' AND
			`password` = '" . md5 ($_POST["password"]) . "'",
		__FILE__, __LINE__, false);
		
	if (mysql_num_rows ($user))
	{
		$user = mysql_fetch_assoc ($user);
		unset ($user["password"]);
		$_SESSION["user"] = $user;
		$_SESSION["user"]["auth"] = true;
		header ("location: home.php");
		exit();
	}
	else
	{
		setSessionMessage ("Please enter a valid username and password.", "error");
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Authentication</title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="screen.css" />
</head>
<body>

<h1>Login</h1>

<?php displaySessionMessages(); ?>
	
<form method="post" action="<?= $_SERVER["PHP_SELF"] ?>">
	<p><label for="name">email</label><input id="name" name="name" type="text" /></p>
	<p><label for="password">password</label><input id="password" name="password" type="password" /></p>
	<p><input type="submit" value="login" /></p>
</form>

</body>
</html>