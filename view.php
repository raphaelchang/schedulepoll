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
$intervals = get_event_intervals($event["id"]);
$ranked = $intervals;
usort($ranked, "event_intervals_cmp");
$users = get_users($event["id"]);
$comments = get_comments($event["id"]);
$max = count($ranked) > 0 ? count($ranked[0][2]) : 0;
$me = false;
if ($account !== false)
	$me = get_user_from_account($event["id"], $account);
if ($event["public"] == 0 && $account === false)
{
	$me = get_invite_user($event["id"], $_GET["key"]);
}
$title = $event["name"];
require_once("header.php");
?>
<h2><?php echo $event["name"] ?></h2>
<?php
if ($event["type"] == "open")
	require_once("viewopen.php");
else
	require_once("viewcentered.php");
?>

<?php require_once("footer.php") ?>