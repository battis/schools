<?php

require_once ("config.php");
require_once ("library.php");

if (mysqlQuery ("CREATE TABLE IF NOT EXISTS {$db["schools"]} (
	`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'unique ID for this record',
	`user_id` BIGINT NOT NULL COMMENT 'user ID of creator of this record',
	`name` VARCHAR(255) NOT NULL COMMENT 'formal name of the school',
	`boarding` BOOL NULL COMMENT 'true if boarding (not exclusive of day)',
	`day` BOOL NULL COMMENT 'true if day (not exclusive of boarding)',
	`public` BOOL NULL COMMENT 'true if public or charter (not exclusive of boarding or day)',
	`gender` ENUM('Coed', 'Boys', 'Girls' ) NULL,
	`lower_grade` ENUM('PK', 'K', '4', '5', '6', '7', '8', '9' ) NULL,
	`upper_grade` ENUM('6', '7', '8', '9', '12', 'PG' ) NULL,
	`street1` VARCHAR(255) NULL COMMENT 'street address line 1',
	`street2` VARCHAR(255) NULL COMMENT 'street address line 2 (leave blank if none)',
	`city` VARCHAR(255) NULL,
	`state` VARCHAR(2) NULL COMMENT '2 letter USPS state abbreviation',
	`zip` VARCHAR(10) NULL COMMENT '00000 or 00000-0000',
	`url` VARCHAR(255) NULL COMMENT 'url includes http://',
	INDEX (
		`user`,
		`name`,
		`boarding`,
		`day`,
		`public`,
		`gender`,
		`lower_grade`,
		`upper_grade`
		),
	INDEX (
		`name`
		)
	) COMMENT = 'school detailed information'",
	__FILE__, __LINE__, false))
{
	setSessionMessage ("Schools table created. ({$config["database"]}:{$db["schools"]})");	
}
else
{
	setSessionMessage ("Schools table could not be created.", "error");
}
	
if (mysqlQuery ("CREATE TABLE IF NOT EXISTS {$db["contacts"]} (
	`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'unique ID for this record',
	`user_id` BIGINT NOT NULL COMMENT 'user ID of creator of this record',
	`school_id` BIGINT NULL COMMENT 'school ID of contact (leave blank if unknown)',
	`name` VARCHAR(255) NOT NULL COMMENT 'FirstName LastName',
	`title` VARCHAR(255) NULL COMMENT 'formal title (leave blank if unknown)',
	`email` VARCHAR(255) NULL COMMENT 'leave blank if unknown',
	`phone` VARCHAR(25) NULL COMMENT 'only digits (leave blank if unknown)',
	INDEX (
		`user`,
		`name`
		)
	) COMMENT = 'contacts (may or may not be associated with specific schools)'",
	__FILE__, __LINE__, false))
{
	setSessionMessage ("Contacts table created. ({$config["database"]}:{$db["contacts"]})");
}
else
{
	setSessionMessage ("Contacts table could not be created.", "error");
}
	
if (mysqlQuery ("CREATE TABLE IF NOT EXISTS {$db["positions"]} (
	`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'unique ID for this record',
	`user_id` BIGINT NOT NULL COMMENT 'user ID of creator of this record',
	`school_id` BIGINT NOT NULL COMMENT 'school ID for position',
	`contact_id` BIGINT NULL COMMENT 'contact ID for position (leave blank if unknown)',
	`title` VARCHAR(255) NOT NULL COMMENT 'formal title of position',
	INDEX (
		`user`,
		`title`
		)
	) COMMENT = 'specific positions available at schools'",
	__FILE__, __LINE__, false))
{
	setSessionMessage ("Positions table created. ({$config["database"]}:{$db["positions"]})");
}
else
{
	setSessionMessage ("Positions table could not be created.", "error");
}

if (mysqlQuery ("CREATE TABLE IF NOT EXISTS {$db["notes"]} (
	`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'unique ID for this record',
	`user_id` BIGINT NOT NULL COMMENT 'user ID of creator of this record',
	`school_id` BIGINT NOT NULL COMMENT 'school ID for this note',
	`position_id` BIGINT NULL COMMENT 'position ID for this note, position must be associated with school ID (leave blank if no specific position or only one position available)',
	`date` DATE NOT NULL COMMENT 'YYYY-MM-DD',
	`note` LONGTEXT NOT NULL,
	`next` DATE NULL COMMENT 'YYYY-MM-DD (leave blank if no specific date for next action)',
	`action` MEDIUMTEXT NULL COMMENT 'leave blank if no specific next action',
	INDEX (
		`user`
		),
	FULLTEXT (
		`note`,
		`action`
		)
	) COMMENT = 'individual notes on interactions with schools'",
	__FILE__, __LINE__, false))
{
	setSessionMessage ("Notes table created. ({$config["database"]}:{$db["notes"]})");
}
else
{
	setSessionMessage ("Notes table could not be created.", "error");
}
	
if (mysqlQuery ("CREATE TABLE IF NOT EXISTS {$db["notes-contacts"]} (
	`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'unique ID for this record',
	`user_id` BIGINT NOT NULL COMMENT 'user ID of creator of this record',
	`note_id` BIGINT NOT NULL COMMENT 'note ID',
	`contact_id` BIGINT NOT NULL COMMENT 'contact ID of person note refers to',
	INDEX (
		`user`,
		`note`,
		`contact`
		)
	) COMMENT = 'associations between notes and specific contacts (may be more than one contact per note)'",
	__FILE__, __LINE__, false))
{
	setSessionMessage ("Notes-contacts association table created. ({$config["database"]}:{$db["notes-contacts"]})");
}
else
{
	setSessionMessage ("Notes-contacts association table could not be created.", "error");
}

if (mysqlQuery ("CREATE TABLE IF NOT EXISTS {$db["users"]} (
	`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`name` VARCHAR(255) NOT NULL COMMENT 'unique user name (email address suggested)',
	`password` VARCHAR(255) NOT NULL COMMENT 'encrypted hash of password',
	`admin` BOOL NOT NULL DEFAULT '0' COMMENT 'true if user has administrative privileges',
	UNIQUE (
		`name`
		)
	) COMMENT = 'user information and privileges'",
	__FILE__, __LINE__, false))
{
	setSessionMessage ("Users table created. ({$config["database"]}:{$db["users"]})");
}
else
{
	setSessionMessage ("Users table could not be created.", "error");
}

/* generate an alphanumeric, 8-character, all-lower case random password */
$password = substr (strtolower (preg_replace ("|\W|", "", md5 (time()))), 0, 8);
if (mysqlQuery ("INSERT INTO {$db["users"]}
	(
		`name`,
		`password`,
		`admin`
		)
	VALUES (
		'admin',
		'" . md5($password) . "',
		'1'
		)",
	__FILE__, __LINE__, false))
{
	setSessionMessage ("Admin user created. (username 'admin', password '{$password}')");
}
else
{
	setSessionMessage ("Admin user could not be created.", "error");
}

if (mysqlQuery ("INSERT INTO {$db["users"]}
	(
		`name`,
		`password`,
		`admin`
		)
	VALUES (
		'seth@battis.net',
		'" . md5("8zc8ripM8soq") . "',
		'0'
		)",
	__FILE__, __LINE__, false))
{
	setSessionMessage ("seth@battis.net created.");
}
else
{
	setSessionMessage ("seth@battis.net could not be created.", "error");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Installation</title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="screen.css" />
</head>
<body>

<div id="content">
	
<h1>Installation</h1>

<?php displaySessionMessages(); ?>

<p><a href="index.php">Log in</a></p>

</div>

</body>
</html>