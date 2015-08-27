</div>
<hr>
<div id="footer">
&copy; 2015 Raphael Chang
<?php if ($account !== false) { ?>
<div style="float:right">Logged in as: <?php echo get_account_info($account)["email"] ?> | <a href="logout">Logout</a></div>
<?php } ?>
</div>
</div>
</body>