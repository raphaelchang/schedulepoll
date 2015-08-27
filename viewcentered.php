<?php
$creator = get_event_creator($event["id"]);
$schedules = get_schedules_for_event($event["id"]);
$users = get_users($event["id"]);
$creatorname = $creator["name"];
if ($creator["account"] != 0)
{
	$c = get_account_info($creator["account"]);
	$creatorname = $c["first_name"] . " " . $c["last_name"];
}
for($i = 0; $i < count($users); $i++)
{
	if ($users[$i]["id"] == $creator["id"])
		array_splice($users, $i, 1);
}
?>
<?php echo "<span style=\"color: #999\">Scheduled by " . $creatorname . "</span><br>"; ?>
<?php if ($event["description"] != "") echo $event["description"] . "<br>" ?>
<div class="tabs">
    <ul class="tab-links">
	<?php if ($event["account"] !== $account) { ?>
        <li class="active"><a href="#select"><?php echo ($me === false ? 'Select' : 'Edit') ?> Availability</a></li>
		<li><?php } else echo "<li class=\"active\">" ?>
		<?php if ($event["hidden"] == 0 || $event["account"] === $account) { ?><a href="#results">Poll Results</a></li>
        <li><?php } ?><a href="#discussion">Discussion (<?php echo count($comments) ?>)</a></li>
        <?php if ($event["public"] == 1 || $event["account"] === $account) { ?>
		<li><a href="#share">Share</a></li>
		<?php } ?>
	<?php if ($event["account"] === $account) { ?>
        <li><a href="#edit">Edit Event</a></li>
	<?php } ?>
		<div style="clear:both"></div>
	<hr style="position: absolute; margin: -1px; width: 100%; z-index: -1">
    </ul>
    <div class="tab-content">
	<?php if ($event["account"] !== $account) { ?>
        <div id="select-div" class="tab active">
			<form action="vote?event=<?php echo $_GET["event"] ?><?php if (isset($_GET["key"])) echo "&key=" . $_GET["key"] ?>" method="POST">
			<?php if ($account === false) { ?>
			<strong>Name</strong><br>
			<input type="text" name="name" required <?php echo ($me !== false ? 'value="' . $me["name"] . '" disabled' : '') ?>><br>
			<?php } ?>
			<?php echo ($me === false ? 'Select' : 'Edit') ?> your availability for the event <strong><?php echo $event["name"] ?></strong>.<br>
			<table border="1">
			<tr>
			<th>Time</th>
			<th style="background-color: SpringGreen; width: 20%">Preferred</th>
			<th style="background-color: LightGreen; width: 20%">Available</th>
			<th style="background-color: Salmon; width: 20%">Unavailable</th>
			</tr>
			<?php
			$votes = array();
			if ($me !== false)
			{
				$votes = get_votes($me["id"]);
			}
			foreach($schedules as $s)
			{
				echo "<tr>";
				echo "<td>" . date("n/j/Y g:i A", strtotime($s["range_start"])) . "-" . (date("n/j/Y", strtotime($s["range_start"])) == date("n/j/Y", strtotime($s["range_end"])) ? date("g:i A", strtotime($s["range_end"])) : date("n/j/Y g:i A", strtotime($s["range_end"]))) . "</td>";
				for ($i = 2; $i >= 0; $i--)
				{
					echo "<td><input type=\"radio\" name=\"" . $s["id"] . "\" value=\"" . $i . "\" required " . ($me !== false && $votes[$s["id"]] == $i ? "checked" : "") . "></td>";
				}
				echo "</tr>";
			}
			?>
			</table>
			<input type="submit" value="Submit">
			</form>
		</div>
		<?php } ?>
		<?php if ($event["hidden"] == 0 || $event["account"] === $account) { ?>
		<div id="results-div" class="tab<?php if ($event["account"] === $account) echo " active" ?>">
		<?php if (count($users) > 0) { ?>
			<table border="1">
			<tr>
			<th></th>
			<?php
			for($i = 0; $i < count($schedules); $i++)
			{
				$schedules[$i]["count"] = 0;
				echo "<td>" . date("n/j/Y g:i A", strtotime($schedules[$i]["range_start"])) . "-" . (date("n/j/Y", strtotime($schedules[$i]["range_start"])) == date("n/j/Y", strtotime($schedules[$i]["range_end"])) ? date("g:i A", strtotime($schedules[$i]["range_end"])) : date("n/j/Y g:i A", strtotime($schedules[$i]["range_end"]))) . "</td>";
			}
			?>
			</tr>
			<?php
			foreach($users as $u)
			{
				$votes = get_votes($u["id"]);
				echo "<tr>";
				if ($u["account"] != 0)
				{
					$a = get_account_info($u["account"]);
					echo "<td>" . $a["first_name"] . " " . $a["last_name"] . "</td>";
				}
				else
				{
					echo "<td>" . $u["name"] . "</td>";
				}
				for($i = 0; $i < count($schedules); $i++)
				{
					if ($votes[$schedules[$i]["id"]] == "2")
					{
						$schedules[$i]["count"]++;
						echo "<td style=\"background-color: SpringGreen\">Preferred</td>";
					}
					else if ($votes[$schedules[$i]["id"]] == "1")
					{
						$schedules[$i]["count"]++;
						echo "<td style=\"background-color: LightGreen\">Available</td>";
					}
					else if ($votes[$schedules[$i]["id"]] == "0")
					{
						echo "<td style=\"background-color: Salmon\">Unavailable</td>";
					}
				}
				echo "</tr>";
			}
			?>
			<tr>
			<th>Total</th>
			<?php
			for($i = 0; $i < count($schedules); $i++)
			{
				echo "<td>" . $schedules[$i]["count"] . "</td>";
			}
			?>
			</tr>
			</table>
		<?php } else echo "No responses yet!"; ?>
		</div>
		<?php } ?>
		<div id="discussion-div" class="tab">
			<form action="comment?event=<?php echo $_GET["event"] ?>" method="POST">
			<?php if ($account === false) { ?>
			<strong>Name</strong><br>
			<input type="text" name="name" required><br>
			<?php } ?>
			<strong>Comment</strong><br>
			<textarea name="text" cols="100" rows="5" required style="margin-bottom: 5px"></textarea><br>
			<input type="submit" value="Post">
			</form>
			<?php
			foreach($comments as $c)
			{
				echo "<hr>";
				echo "<div id=\"comment\">";
				echo "<span>" . $c["name"] . "</span> on " . date("n/j/Y g:i A", strtotime($c["timestamp"])) . "<br>";
				echo $c["content"];
				echo "</div>";
			}
			?>
			<br>
		</div>
        <?php if ($event["public"] == 1 || $event["account"] === $account) { ?>
		<div id="share-div" class="tab">
		<?php if ($event["public"] == 1) { ?>
		<script type="text/javascript">
			function selectText(containerid) {
				if (document.selection) {
					var range = document.body.createTextRange();
					range.moveToElementText(document.getElementById(containerid));
					range.select();
				} else if (window.getSelection) {
					var range = document.createRange();
					range.selectNode(document.getElementById(containerid));
					window.getSelection().addRange(range);
				}
			}
		</script>
			Use this link to invite others to enter their availability:<br>
			<div id="selectable" onclick="selectText('selectable')"><?php echo DOMAIN . SITE_ROOT . "/view?event=" . $_GET["event"] ?></div>
		<?php } else { ?>
			Enter an email to invite to this poll:<br>
			<form action="invite?event=<?php echo $_GET["event"] ?>" method="POST">
			<input type="text" name="email" size="50" required> <input type="submit" value="Invite"><br>
			</form>
		<?php
		$invites = get_invites($event["id"], true);
		if (count($invites) > 0)
		{
			echo "<strong>Invites Pending</strong><br>";
			foreach($invites as $i)
			{
				echo $i["email"] . "<br>";
			}
		}
		?>
		<?php } ?>
		</div>
		<?php } ?>
		<div id="edit-div" class="tab">
			<form action="edit?event=<?php echo $_GET["event"] ?>" method="POST">
			<strong>Event Name</strong><br>
			<input type="text" name="name" value="<?php echo $event["name"] ?>" required>
			<br>
			<strong>Event Description (Optional)</strong><br>
			<textarea cols="50" rows="5" name="desc"><?php echo $event["description"] ?></textarea>
			<br>
			<input type="submit" value="Edit"><br>
			</form>
		</div>
	</div>
</div>