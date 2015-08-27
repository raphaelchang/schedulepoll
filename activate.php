<?php
require_once("includes.php");
$title = "Activate Account";
if (!isset($_GET["key"]))
	header("Location: /" . SITE_ROOT);
$id = activate_account($_GET["key"]);
if ($id === false)
	header("Location: /" . SITE_ROOT);
require_once("header.php");
?>
Thank you for making a SchedulePoll account. Your account has been activated. You may now <a href="login">login</a>.<br>
<?php require_once("footer.php") ?>