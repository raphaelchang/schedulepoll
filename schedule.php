<?php
require_once("includes.php");

if (!isset($_GET["event"]))
	header("Location: /" . SITE_ROOT);
$event = get_event($_GET["event"]);
if ($event == null)
	header("Location: /" . SITE_ROOT);
if ($event["public"] == 0 && $event["account"] != $account
&& !(isset($_GET["key"]) && validate_invite($event["id"], $_GET["key"])) && !($account !== false && validate_invite_account($event["id"], $account)))
	header("Location: /" . SITE_ROOT);
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if ($account === false && (isset($_POST["name"]) && $_POST["name"] == ""))
		echo "Please enter your name";
	else if ($_POST["text"] == "")
		echo "Please enter your availability";
	else
	{
		$processor = new NaturalLanguageProcessor($_POST["text"], $event["range_start"], $event["range_end"]);
		$p = $processor->Process();
		if ($p !== false)
		{
			$userid = 0;
			if ($account !== false)
				$userid = put_schedule("", $p, $event["id"], $account, $_POST["text"]);
			else
			{
				if ($event["public"] == 0)
					$userid = put_schedule((isset($_POST["name"]) ? $_POST["name"] : ""), $p, $event["id"], 0, $_POST["text"], $_GET["key"]);
				else
					$userid = put_schedule((isset($_POST["name"]) ? $_POST["name"] : ""), $p, $event["id"], 0, $_POST["text"]);
			}
			if ($event["public"] == 0)
			{
				if ($account !== false)
				{
					update_invite_account($event["id"], $account, $userid);
				}
				else
				{
					update_invite($event["id"], $_GET["key"], $userid);
				}
			}
		}
	}
}
if ($event["public"] == 0 && isset($_GET["key"]))
	header("Location: view?event=" . $_GET["event"] . "&key=" . $_GET["key"] . "#attendees");
else
	header("Location: view?event=" . $_GET["event"] . "#attendees");
?>