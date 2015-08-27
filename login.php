<?php
require_once("includes.php");
$title = "Login/Register";
require_once("header.php");
?>
<?php
$error = "";
$register = false;
$success = false;
if (isset($_POST["email"]))
{
	if ($_POST["email"] == "")
		$error = "<span style=\"color:red\"Please enter your email.</span>";
	else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
		$error = "<span style=\"color:red\">Please enter a valid email.</span>";
	else if ($_POST["password"] == "")
		$error = "<span style=\"color:red\">Please enter a password.</span>";
	else if (isset($_POST["register"]))
	{
		$register = true;
		$fail = false;
		if ($_POST["email"] != $_POST["emailconfirm"])
		{
			$fail = true;
			$error = "<span style=\"color:red\">Emails do not match.</span><br>";
		}
		if ($_POST["password"] != $_POST["passwordconfirm"])
		{
			$fail = true;
			$error = "<span style=\"color:red\">Passwords do not match.</span><br>";
		}
		if ($_POST["firstname"] == "")
		{
			$fail = true;
			$error = "<span style=\"color:red\">Please enter your first name.</span><br>";
		}
		if ($_POST["lastname"] == "")
		{
			$fail = true;
			$error = "<span style=\"color:red\">Please enter your last name.</span><br>";
		}
		if (!$fail)
		{
			$activation = register($_POST["email"], $_POST["password"], $_POST["firstname"], $_POST["lastname"]);
			if ($activation != -1)
			{
				$msg = "Thank you for creating a SchedulePoll account. You may activate your account through this link: <a href=" . DOMAIN . SITE_ROOT . "/activate?key=$activation>" . DOMAIN . SITE_ROOT . "/activate?key=$activation</a>.";
				//mail($_POST["email"], "SchedulePoll - Activate Your Account", $msg, "From: info@schedulepoll.com");
				$success = true;
			}
			else
			{
				$error = "<span style=\"color:red\">This account already exists.</span><br>";
			}
		}
	}
	else
	{
		$res = login($_POST["email"], $_POST["password"]);
		if ($res == 1) // Account doesn't exist
		{
			$register = true;
		}
		else if ($res == 3 || $res == 0)
		{
			header("Location: cp");
		}
		else if ($res == 2)
		{
			$error = "<span style=\"color:red\">Password is incorrect.</span><br>";
		}
		else if ($res == 4)
		{
			$error = "<span style=\"color:red\">This account has not been activated. Please check your email for instructions to activate your account.</span><br>";
		}
	}
}
?>
<?php if (!$success) { ?>
<h2><?php echo $title ?></h2>
A SchedulePoll account allows you to edit and track events you've created or are involved in.<br>
<?php echo $error ?>
<form action="" method="POST">
<strong>Email</strong><br>
<input type="text" name="email" size="40" <?php if ($register) echo "value=" . $_POST["email"] ?> required><br>
<?php if ($register) { ?>
<strong>Confirm Email</strong><br>
<input type="text" name="emailconfirm" size="40" <?php if (isset($_POST["emailconfirm"])) echo "value=" . $_POST["emailconfirm"] ?> required><br>
<?php } ?>
<strong>Password</strong><br>
<input type="password" name="password" size="40" <?php if ($register) echo "value=" . $_POST["password"] ?> required><br>
<?php if ($register) { ?>
<strong>Confirm Password</strong><br>
<input type="password" name="passwordconfirm" size="40" <?php if (isset($_POST["passwordconfirm"])) echo "value=" . $_POST["passwordconfirm"] ?> required><br>
<strong>First Name</strong><br>
<input type="text" name="firstname" size="40" <?php if (isset($_POST["firstname"])) echo "value=" . $_POST["firstname"] ?> required><br>
<strong>Last Name</strong><br>
<input type="text" name="lastname" size="40" <?php if (isset($_POST["lastname"])) echo "value=" . $_POST["lastname"] ?> required><br>
<input type="hidden" name="register">
<?php } ?>
<input type="submit" value="<?php echo (!$register ? "Login/" : "") ?>Register" style="margin-top: 10px">
</form>
<?php } else echo "Thank you for registering. Please check your email for instructions to activate your account.<br>"; ?>
<?php require_once("footer.php") ?>