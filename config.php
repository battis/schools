<?php

/* user-configurable */
$config["host"] = "localhost";
$config["user"] = "schools";
$config["password"] = "4ArutZKLXnveJW38";
$config["database"] = "schools";
$config["prefix"] = "schools_";
$config["pretty date format"] = "%W, %M %e, %Y";
$config["show debug messages"] = true;

/* automatically configured */
$prefix = (strlen ($config["prefix"]) ? "{$config["prefix"]}_" : "");
$db["contacts"] = "`{$prefix}contacts`";
$db["notes"] = "`{$prefix}notes`";
$db["notes-contacts"] = "`{$prefix}notes-contacts`";
$db["positions"] = "`{$prefix}positions`";
$db["schools"] = "`{$prefix}schools`";
$db["users"] = "`{$prefix}users`";

/* open connections */
session_start();
$_SESSION["link"] = mysql_pconnect ($config["host"], $config["user"], $config["password"]);
mysql_select_db ($config["database"], $_SESSION["link"]);
/* TODO catch MySQL errors */

?>