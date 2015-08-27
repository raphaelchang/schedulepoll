<?php
require_once("includes.php");

if (isset($_POST["name"]))
{
	if (($_POST["type"] == "open" && ($_POST["start"] == "" || $_POST["end"] == "")) || $_POST["name"] == "")
		echo "Missing fields";
	else if ($_POST["type"] == "open" && date_diff(date_create($_POST["start"]), date_create($_POST["end"]))->format("%r") == "-")
	{
		echo "End date must be after start date";
	}
	else
	{
		$hash = "";
		$account_id = ($account !== false ? $account : 0);
		$public = isset($_POST["public"]);
		$hidden = isset($_POST["hidden"]);
		if ($_POST["type"] == "open")
		{
			$hash = create_event($_POST["name"], $_POST["start"], $_POST["end"], $_POST["desc"], $_POST["type"], $account_id, $public, $hidden);
			header('Location: view?event=' . $hash);
		}
		else if ($_POST["type"] == "centered")
		{
			$hash = create_event($_POST["name"], date("Y-m-d H:i:s", 0), date("Y-m-d H:i:s", PHP_INT_MAX), $_POST["desc"], $_POST["type"], $account_id, $public, $hidden);
			$id = get_event($hash)["id"];
			$processor = new NaturalLanguageProcessor($_POST["proposal"], date("Y-m-d H:i:s", 0), date("Y-m-d H:i:s", PHP_INT_MAX));
			if ($account !== false)
				put_schedule("", $processor->Process(), $id, $account, $_POST["proposal"]);
			else
				put_schedule($_POST["username"], $processor->Process(), $id, $_POST["proposal"]);
			header('Location: view?event=' . $hash . "#share");
		}
	}
}
$title = "Create Event";
require_once("header.php");
?>

<h2>Create Event</h2>
<div class="tabs">
    <ul class="tab-links">
        <li class="active"><a href="#open">Open Scheduling</a></li>
        <li><a href="#centered">Centered Around My Schedule</a></li>
		<div style="clear:both"></div>
	<hr style="position: absolute; margin: -1px; width: 100%; z-index: -1">
    </ul>
    <div class="tab-content">
        <div id="open-div" class="tab active">
			This type of poll allows everyone to enter their own schedules, and SchedulePoll will find the time interval with most people available.<br>
			<?php if ($account === false) echo "<span style=\"color: #999\">You are not logged in. You will not be able to edit information for this event after it is created.</span>" ?>
			<form action="" method="POST">
			<strong>Event Name</strong><br>
			<input type="text" name="name" required>
			<br>
			<strong>Event Description (Optional)</strong><br>
			<textarea cols="50" rows="5" name="desc"></textarea>
			<br>
			<strong>Possible Date Range</strong><br>
			From <input type="date" name="start"> to <input type="date" name="end">
			<br>
			<input type="checkbox" name="public" id="invite1" checked<?php if ($account === false) echo ' disabled' ?>><label for="invite1">Anyone with link can fill out poll<?php if ($account === false) echo '<span style="color: #999"> (login to change this option)</span>' ?></label><br>
			<input type="checkbox" name="hidden" id="hidden1"<?php if ($account === false) echo ' disabled' ?>><label for="hidden1">Hide responses from others<?php if ($account === false) echo '<span style="color: #999"> (login to change this option)</span>' ?></label><br>
			<input type="hidden" name="type" value="open">
			<input type="submit" value="Create" style="margin-top: 10px"><br>
			</form>
		</div>
        <div id="centered-div" class="tab">
			This type of poll allows you to enter time proposals according to your schedule, and attendees can select among your proposed times.<br>
			<?php if ($account === false) echo "<span style=\"color: #999\">You are not logged in. You will not be able to edit information for this event after it is created.</span>" ?>
			<form action="" method="POST">
			<?php if ($account === false) { ?>
			<strong>Your Name:</strong><br>
			<input type="text" name="username" required>
			<br>
			<?php } ?>
			<strong>Event Name</strong><br>
			<input type="text" name="name" required>
			<br>
			<strong>Event Description (Optional)</strong><br>
			<textarea cols="50" rows="5" name="desc"></textarea>
			<br>
			<strong>Time Proposals</strong><br>
			Enter your time proposals for this event, separated by commas. For further explanation, visit the <a href="help">help</a> page.<br>
			Example: "7/4, 3-5 PM, 7/8, 5-8 PM, 8/30, 6-8 PM"<br>
			<input type="text" name="proposal" size="50">
			<br>
			<input type="checkbox" name="public" id="invite2" checked<?php if ($account === false) echo ' disabled' ?>><label for="invite2">Anyone with link can fill out poll<?php if ($account === false) echo '<span style="color: #999"> (login to change this option)</span>' ?></label><br>
			<input type="checkbox" name="hidden" id="hidden2"<?php if ($account === false) echo ' disabled' ?>><label for="hidden2">Hide responses from others<?php if ($account === false) echo '<span style="color: #999"> (login to change this option)</span>' ?></label><br>
			<input type="hidden" name="type" value="centered">
			<input type="submit" value="Create" style="margin-top: 10px"><br>
			</form>
		</div>
	</div>
</div>
<?php require_once("footer.php"); ?>