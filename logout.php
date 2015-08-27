<?php
require_once("includes.php");
if ($account === false)
	header("Location: /" . SITE_ROOT);
logout();
header("Location: /" . SITE_ROOT);
?>