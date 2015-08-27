<?php
require_once("includes.php");
$title = "Dashboard";
if ($account === false)
	header("Location: login");
require_once("header.php");
$user = get_account_info($account);
echo "<h2>" . $user["first_name"] . "'s Dashboard</h2>";
?>
Welcome, <?php echo $user["first_name"] ?>!
<div class="tabs">
    <ul class="tab-links">
		<?php
		$invites = get_invites_on_account($account, true);
		?>
        <li class="active"><a href="#events">My Events</a></li>
        <li><a href="#involved">Events I'm Involved In</a></li>
        <li><a href="#invites">Event Invites<?php echo " (" . count($invites) . ")" ?></a></li>
		<div style="clear:both"></div>
	<hr style="position: absolute; margin: -1px; width: 100%; z-index: -1">
    </ul>
    <div class="tab-content">
        <div id="events-div" class="tab active">
		<?php
		$events = get_events_by_account($account);
		if (count($events) > 0)
		{
		?>
		<table border="1">
		<tr>
		<th>Event</th>
		<th>Event Description</th>
		<th>Event Type</th>
		<th>Responses</th>
		</tr>
		<?php
			foreach($events as $e)
			{
				echo "<tr>";
				echo "<td><a href=\"view?event=" . $e["hash_code"] . "\">" . $e["name"] . "</a></td>";
				echo "<td>" . $e["description"] . "</td>";
				echo "<td>" . ($e["type"] == "open" ? "Open Scheduling" : "Centered Around Schedule") . "</td>";
				$num = get_number_responses($e["id"]);
				echo "<td>" . ($e["type"] == "open" ? $num : ($num - 1)) . "</td>";
				echo "</tr>";
			}
		?>
		</table>
		<?php
		}
		else
			echo "No events!";
		?>
		</div>
        <div id="involved-div" class="tab">
		<?php
		$events = get_involved_events($account);
		if (count($events) > 0)
		{
		?>
		<table border="1">
		<tr>
		<th>Event</th>
		<th>Event Description</th>
		<th>Event Type</th>
		<th>Responses</th>
		</tr>
		<?php
			foreach($events as $e)
			{
				echo "<tr>";
				echo "<td><a href=\"view?event=" . $e["hash_code"] . "\">" . $e["name"] . "</a></td>";
				echo "<td>" . $e["description"] . "</td>";
				echo "<td>" . ($e["type"] == "open" ? "Open Scheduling" : "Centered Around Schedule") . "</td>";
				$num = get_number_responses($e["id"]);
				echo "<td>" . ($e["type"] == "open" ? $num : ($num - 1)) . "</td>";
				echo "</tr>";
			}
		?>
		</table>
		<?php
		}
		else
			echo "No events!";
		?>
		</div>
        <div id="invites-div" class="tab">
		<?php
		if (count($invites) > 0)
		{
		?>
		<table border="1">
		<tr>
		<th>Event</th>
		<th>Event Description</th>
		<th>Event Type</th>
		<th>Invited By</th>
		</tr>
		<?php
			foreach($invites as $e)
			{
				$owner = get_account_info($e["account"]);
				echo "<tr>";
				echo "<td><a href=\"view?event=" . $e["hash_code"] . "\">" . $e["name"] . "</a></td>";
				echo "<td>" . $e["description"] . "</td>";
				echo "<td>" . ($e["type"] == "open" ? "Open Scheduling" : "Centered Around Schedule") . "</td>";
				$num = get_number_responses($e["id"]);
				echo "<td>" . $owner["first_name"] . " " . $owner["last_name"] . "</td>";
				echo "</tr>";
			}
		?>
		</table>
		<?php
		}
		else
			echo "No invites!";
		?>
		</div>
	</div>
</div>
<?php require_once("footer.php") ?>