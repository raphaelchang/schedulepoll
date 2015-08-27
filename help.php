<?php
require_once("includes.php");
$title = "Help";
require_once("header.php");
?>
<h2><?php echo $title ?></h2>
<h3>Types of Polls</h3>
SchedulePoll offers two types of polls. Open Scheduling allows everyone to enter their own schedules, and finds the time interval with most people available.
Centered Around My Schedule allows you to enter time proposals according to your schedule, and attendees can select among your proposed times.
<h3>Poll Access Options</h3>
There are two options for how polls can be shared. If a poll is open to anyone with the link, the poll can be shared by simply copying the URL of the event.
Anyone with that link will be able to fill out the poll. Otherwise, the poll will be viewable by invite only.
The creator of the event must invite others by email in order for them to be able to access the poll. One invite is restricted to one entry in the poll.
<h3>Using the Natural Language Processor</h3>
SchedulePoll uses a natural language processor to convert time-related text into a series of time ranges.
It uses keywords to determine the structure of the text as a tree, and then uses the tree to narrow down the entered ranges.
There are three levels in the structure for time-related text: date ranges, days of the week, and time ranges.
Each one of these entities should follow the other from least specific to most specific, separated by commas.
For example: 7/16-8/16, Mondays and Fridays, 5:30-8:30 PM.
If multiple ranges are required within a level, they can be separated by commas or "and".
Lower level ranges are applied to all higher level ranges in the current hierarchy.
In the example "Before 7/15, Mondays and Fridays, 5:30-8:30 PM, 7/16-8/16, after 8/19, Tuesdays, before 8 PM",
The ranges "Mondays and Fridays, 5:30-8:30 PM" are applied to "Before 8/15", and the range "Tuesdays, before 8 PM" is applied to both "7/16-8/16" and "after 8/19".
Ranges can be specified using "before x", "after x", "x to y", or "x-y".
<?php require_once("footer.php") ?>