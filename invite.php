<?php
require_once("includes.php");

if (!isset($_GET["event"]))
	header("Location: /" . SITE_ROOT);
$event = get_event($_GET["event"]);
if (isset($_POST["email"]) && $event["account"] === $account && $event["public"] == 0)
{
	if ($_POST["email"] == "")
		echo "Please enter an email.";
	else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
		echo "Please enter a valid email.";
	else
	{
		if ($_POST["email"] != get_account_info($account)["email"])
		{
			add_invite($event["id"], $_POST["email"]);
			//TODO: email
		}
	}
}
header("Location: view?event=" . $_GET["event"] . "#share");
?>