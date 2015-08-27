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
	else
	{
		$intervals = array();
		$ids = get_schedules_for_event($event["id"]);
		foreach($ids as $id)
		{
			array_push($intervals, array($id["id"], $_POST[$id["id"] . ""]));
		}
		$userid = 0;
		if ($account !== false)
			$userid = add_vote("", $intervals, $event["id"], $account);
		else
		{
			if ($event["public"] == 0)
				$userid = add_vote((isset($_POST["name"]) ? $_POST["name"] : ""), $intervals, $event["id"], 0, $_GET["key"]);
			else
				$userid = add_vote((isset($_POST["name"]) ? $_POST["name"] : ""), $intervals, $event["id"]);
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
if ($event["public"] == 0 && isset($_GET["key"]))
	header("Location: view?event=" . $_GET["event"] . "&key=" . $_GET["key"] . "#results");
else
	header("Location: view?event=" . $_GET["event"] . "#results");
?>