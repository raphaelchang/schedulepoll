<?php
require_once("includes.php");

if (!isset($_GET["event"]))
	header("Location: /" . SITE_ROOT);
$event = get_event($_GET["event"]);
if (isset($_POST["text"]))
{
	if ($account === false)
		add_comment($_POST["name"], $_POST["text"], $event["id"]);
	else
	{
		$a = get_account_info($account);
		add_comment($a["first_name"] . " " . $a["last_name"], $_POST["text"], $event["id"]);
	}
}
header("Location: view?event=" . $_GET["event"] . "#discussion");
?>