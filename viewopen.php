<?php if ($event["description"] != "") echo $event["description"] . "<br>" ?>
<div class="tabs">
    <ul class="tab-links">
        <li class="active"><a href="#enter"><?php echo ($me === false ? 'Enter' : 'Edit') ?> Availability</a></li>
        <?php if ($event["hidden"] == 0 || $event["account"] === $account) { ?>
		<li><a href="#attendees">Potential Attendees (<?php echo count($users) ?>)</a></li>
        <li><a href="#results">Poll Results</a></li>
		<?php } ?>
        <li><a href="#discussion">Discussion (<?php echo count($comments) ?>)</a></li>
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
        <div id="enter-div" class="tab active">
			Available range: <?php echo date("m/d/Y", strtotime($event["range_start"])) . "-" . date("m/d/Y", strtotime($event["range_end"]))?><br>
			<form action="schedule?event=<?php echo $_GET["event"] ?><?php if (isset($_GET["key"])) echo "&key=" . $_GET["key"] ?>" method="POST">
			<?php if ($account === false) { ?>
			<strong>Name</strong><br>
			<input type="text" name="name" required <?php echo ($me !== false ? 'value="' . $me["name"] . '" disabled' : '') ?>><br>
			<?php } ?>
			<?php echo ($me === false ? 'Enter' : 'Edit') ?> your availability for the event <strong><?php echo $event["name"] ?></strong> (available range: <?php echo date("m/d/Y", strtotime($event["range_start"])) . "-" . date("m/d/Y", strtotime($event["range_end"]))?>). 
			For further explanation, visit the <a href="help">help</a> page.<br>
			Examples: "7/13-8/14", "Mondays and Fridays, 5:30-8:30 PM", "Before 8/17, after 9/19", "Any time", "7/13-8/14 on Mondays and Fridays, 5:30-8:30 PM, and after 9/16, before 8 PM"<br>
			<input type="text" name="text" required size="50" <?php echo ($me !== false ? 'value="' . $me["entry"] . '"' : '') ?>>
			<input type="submit" value="Submit">
			</form>
		</div>
		<?php if ($event["hidden"] == 0 || $event["account"] === $account) { ?>
		<div id="attendees-div" class="tab">
			<script type="text/javascript">
			google.setOnLoadCallback(drawChart);
			function drawChart() {

				var container = document.getElementById('timeline');
				var chart = new google.visualization.Timeline(container);
				var dataTable = new google.visualization.DataTable();
				dataTable.addColumn({ type: 'string', id: 'Name' });
				dataTable.addColumn({ type: 'string', id: 'dummy bar label' });
				dataTable.addColumn({ type: 'string', role: 'tooltip' });
				dataTable.addColumn({ type: 'date', id: 'Start' });
				dataTable.addColumn({ type: 'date', id: 'End' });
				dataTable.addRows([
				<?php
				$unavailable = array();
				$numRows = 0;
				$blankexists = false;
				foreach($users as $u)
				{
					$name = "";
					if ($u["account"] != 0)
					{
						$a = get_account_info($u["account"]);
						$name = $a["first_name"] . " " . $a["last_name"];
					}
					else
					{
						$name = $u["name"];
					}
					$sched = get_schedule($u["id"], $event["id"]);
					foreach($sched as $s)
					{
						$exists = true;
						echo "['" . $name . "', null, ' " . date("n/j/Y g:i A", strtotime($s["range_start"])) . "-" . date("n/j/Y g:i A", strtotime($s["range_end"])) . " ', new Date(" . (strtotime($s["range_start"]) * 1000 + 1000 * 3600 * 7) . "), new Date(" . (strtotime($s["range_end"]) * 1000 + 1000 * 3600 * 7) . ")],";
					}
					if (count($sched) == 0)
					{
						$blankexists = true;
						echo "['" . $name . "', null, 'None', new Date(" . (strtotime($event["range_start"]) * 1000 + 1000 * 3600 * 7) . "), new Date(" . (strtotime($event["range_start"]) * 1000 + 1000 * 3600 * 7) . ")],";
					}
				}
				?>]);
				
				var chartHeight = dataTable.getNumberOfRows() * 15 + 40;

				var options = {
				timeline: { colorByRowLabel: true,  height: chartHeight }
				};

				chart.draw(dataTable, options);
				<?php if ($blankexists) { ?>
				(function(){                                            //anonymous self calling function to prevent variable name conficts
					var el = container.getElementsByTagName("rect");      //get all the descendant rect element inside the container      
					var width=100000000;                                //set a large initial value to width
					var elToRem=[];                                     //element would be added to this array for removal
					for(var i=0;i<el.length;i++){                           //looping over all the rect element of container
						var cwidth=parseInt(el[i].getAttribute("width"));//getting the width of ith element
						if(cwidth<width){                               //if current element width is less than previous width then this is min. width and ith element should be removed
							elToRem=[el[i]];
							width=cwidth;                               //setting the width with min width
						}
						else if(cwidth==width){                         //if current element width is equal to previous width then more that one element would be removed
							elToRem.push(el[i]);        
						}
					}
					for(var i=0;i<elToRem.length;i++) // now iterate JUST the elements to remove
						elToRem[i].setAttribute("fill","none"); //make invisible all the rect element which has minimum width
				})();
				<?php } ?>
			}

			</script>

			<?php if (count($users) > 0)
					echo '<div id="timeline" style="height: ' . (count($users) + 1) * 45 . '"></div>';
				else
					echo 'No attendees yet!';
				if (count($unavailable) > 0)
				{
					echo 'Unavailable:<br>';
					foreach($unavailable as $u)
					{
						echo $u["name"] . '<br>';
					}
				}
				?>
		</div>
		<div id="results-div" class="tab">
			<div id="chart_container">
					<div id="y_axis"></div>
					<div id="chart"></div>
			<div id="legend" style="float:right"></div>
			</div>
			<script>
			var graph = new Rickshaw.Graph( {
					element: document.querySelector("#chart"),
					width: 970,
					height: 300,
					renderer: 'area',
					stroke: true,
					interpolation: 'linear',
					series: [ {
							data: [	
								<?php
								$values = array();
								$last = array();
								$last[1] = $event["range_start"];
								foreach($intervals as $i)
								{
									if(strtotime($i[0]) != strtotime($last[1]))
									{
										echo "{x:" . strtotime($last[1]) . ", y:0}";
										echo ",";
										echo "{x:" . strtotime($i[0]) . ", y:0}";
										echo ",";
									}
									$value = count($i[2]);
									echo "{x:" . strtotime($i[0]) . ", y:" . $value . "}";
									echo ",";
									echo "{x:" . strtotime($i[1]) . ", y:" . $value . "}";
									echo ",";
									$last = $i;
								}
								if (strtotime($last[1]) != strtotime($event["range_end"]))
								{
									echo "{x:" . strtotime($last[1]) . ", y:0}";
									echo ",";
									echo "{x:" . strtotime($event["range_end"]) . ", y:0}";
								}
								?>
							], 
							color: '#9cc1e0',
							name: "Total"
					}]
			} );

			var hoverDetail = new Rickshaw.Graph.HoverDetail( {
				graph: graph,
				yFormatter: function(y) {
						return y.toFixed(0);
					},
				xFormatter: function(x) {
						return new Date( x * 1000 + 1000 * 3600 * 7).toLocaleString();
					}} );

			var x_axis = new Rickshaw.Graph.Axis.Time( { graph: graph,
				timeFixture: new Rickshaw.Fixtures.Time.Local() } );

			var y_axis = new Rickshaw.Graph.Axis.Y( {
					graph: graph,
					orientation: 'left',
					tickFormat: Rickshaw.Fixtures.Number.formatKMBT,
					element: document.getElementById('y_axis'),
					ticks: <?php echo $max ?>
			} );

			graph.render();

			</script>
			<br>
			<table border="1">
			<tr>
			<th>Time</th>
			<th>Number Available</th>
			<th>Available</th>
			<th>Unavailable</th>
			</tr>
			<?php
			foreach($ranked as $entry)
			{
				echo "<tr>";
				echo "<td>" . date("n/j/Y g:i A", strtotime($entry[0])) . " to " . date("n/j/Y g:i A", strtotime($entry[1])) . "</td>"; 
				echo "<td>" . count($entry[2]) . "</td>";
				echo "<td>";
				foreach($users as $u)
				{
					$name = "";
					if ($u["account"] != 0)
					{
						$a = get_account_info($u["account"]);
						$name = $a["first_name"] . " " . $a["last_name"];
					}
					else
					{
						$name = $u["name"];
					}
					if (isset($entry[2][$u["id"]]))
						echo $name . "<br>";
				}
				echo "</td>";
				echo "<td>";
				foreach($users as $u)
				{
					$name = "";
					if ($u["account"] != 0)
					{
						$a = get_account_info($u["account"]);
						$name = $a["first_name"] . " " . $a["last_name"];
					}
					else
					{
						$name = $u["name"];
					}
					if (!isset($entry[2][$u["id"]]))
						echo $name . "<br>";
				}
				echo "</td>";
				echo "</tr>";
			}
			?>
			</table>
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