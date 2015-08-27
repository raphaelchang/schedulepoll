<?php
require_once("includes.php");

if (!isset($_GET["event"]))
	header("Location: /" . SITE_ROOT);
$event = get_event($_GET["event"]);
if ($event == null)
	header("Location: /" . SITE_ROOT);
if (isset($_POST["name"]) && $event["account"] === $account)
{
	if ($_POST["name"] == "")
		echo "Please enter an event name.";
	else
	{
		edit_event($event["id"], $_POST["name"], $_POST["desc"]);
	}
}
header("Location: view?event=" . $_GET["event"] . "#edit");
?>