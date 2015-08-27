<?php
class Node
{
	private $parents = Array();
	private $children = Array();
	private $data;
	private $type;
	function __construct($data, $type)
	{
		$this->data = $data;
		$this->type = $type;
	}
	
	function addParent($parent)
	{
		array_push($this->parents, $parent);
	}
	
	function addChild($child)
	{
		array_push($this->children, $child);
	}
	
	function getParents()
	{
		return $this->parents;
	}
	
	function getChildren()
	{
		return $this->children;
	}
	
	function getData()
	{
		return $this->data;
	}
	
	function getType()
	{
		return $this->type;
	}
	
	function toString()
	{
		return $this->data;
	}
}

class NaturalLanguageProcessor
{
	private $text;
	private $startRange;
	private $endRange;
	private $keywords = Array("-", ",", "and", "before", "after", "from", "to", "between", "except");
	private $splitterkeywords = Array(",", "and", "or ", "at ", "on ");
	private $result = array();
	
	function __construct($text, $startRange, $endRange)
	{
		$this->text = $text;
		$this->startRange = date("m/d/Y", strtotime($startRange));
		$this->endRange = date("m/d/Y", strtotime($endRange));
	}
	
	function Process()
	{
		$text = strtolower($this->text);
		if ($text == "none")
			return $result;
		$text = str_replace("weekend", "saturday and sunday", $text);
		$text = str_replace("weekday", "monday,tuesday,wednesday,thursday,friday", $text);
		$text = str_replace("to", "-", $text);
		$text = preg_replace("/(?<=[^a-z])\s|\s(?=[^a-z])/", "", $text);
		$text = str_replace($this->splitterkeywords, " ", $text);
		$text = str_replace("any ", "any", $text);
		$keys = explode(" ", $text);
		$structure = Array();
		$c = 0;
		// Pick out keywords
		foreach($keys as $key)
		{
			if ($key == "")
			{
				array_splice($keys, $c, 1);
				continue;
			}
			if (preg_match("/([0-9]+)\/([0-9]+)/", $key) == 1 || preg_match("/([0-9]+)\/([0-9]+)\/([0-9]+)/", $key) == 1)
			{
				array_push($structure, "D");
			}
			else if (strpos($key, "monday") !== false
			|| strpos($key, "tuesday") !== false
			|| strpos($key, "wednesday") !== false
			|| strpos($key, "thursday") !== false
			|| strpos($key, "friday") !== false
			|| strpos($key, "saturday") !== false
			|| strpos($key, "sunday") !== false)
			{
				array_push($structure, "W");
			}
			else if (preg_match("/([0-9]+)am/", $key) == 1 || preg_match("/([0-9]+)pm/", $key) == 1)
			{
				array_push($structure, "T");
			}
			else
			{
				echo "Error: $key";
				return false;
			}
			$c++;
		}
		// TODO: Make after/before not inclusive
		// Convert "After x, before y" into "x-y"
		for ($i = 0; $i < count($keys); $i++)
		{
			if (strpos($keys[$i], "after") !== false && $i != count($keys) - 1)
			{
				if (strpos($keys[$i + 1], "before") !== false && $structure[$i] == $structure[$i + 1])
				{
					$append = str_replace("before", "-", $keys[$i + 1]);
					$keys[$i] = $keys[$i] . $append;
					$keys[$i] = str_replace("after", "", $keys[$i]);
					array_splice($keys, $i + 1, 1);
					array_splice($structure, $i + 1, 1);
				}
			}
		}
		for ($i = 0; $i < count($keys); $i++)
		{
			if (strpos($keys[$i], "after") !== false)
			{
				$keys[$i] = str_replace("after", "", $keys[$i]);
				if ($structure[$i] == "D")
					$keys[$i] .= "-" . $this->endRange;
				else if ($structure[$i] == "T")
					$keys[$i] .= "-11:59:59pm";
			}
			
			if (strpos($keys[$i], "before") !== false)
			{
				$keys[$i] = str_replace("before", "", $keys[$i]);
				if ($structure[$i] == "D")
					$keys[$i] = $this->startRange . "-" . $keys[$i];
				else if ($structure[$i] == "T")
					$keys[$i] = "12am" . "-" . $keys[$i];
			}
		}
		// Format into standardized ranges
		for ($i = 0; $i < count($keys); $i++)
		{
			if ($structure[$i] == "D")
			{
				if (strpos($keys[$i], "-") !== false) // Date range
				{
					$dates = explode("-", $keys[$i]);
					$start = date("m/d/Y", strtotime($dates[0]));
					$end = date("m/d/Y", strtotime($dates[1]));
					if ($start < $this->startRange)
					{
						$start = $this->startRange;
					}
					if ($end > $this->endRange)
					{
						$end = $this->endRange;
					}
					$keys[$i] = $start . "-" . $end;
				}
				else // Single date
				{
					$start = date("m/d/Y", strtotime($keys[$i]));
					$end = date("m/d/Y", strtotime($keys[$i]));
					$keys[$i] = $start . "-" . $end;
				}
			}
			else if ($structure[$i] == "W")
			{
				$day = substr($keys[$i], 0, strpos($keys[$i], "day") + 3);
				$keys[$i] = date("w", strtotime($day));
			}
			else if ($structure[$i] == "T")
			{
				if (strpos($keys[$i], "-") !== false) // Time range
				{
					$times = explode("-", $keys[$i]);
					if (strpos($times[0], ":") === false)
						$times[0] .= ":00";
					$start = strtotime($times[0]);
					$end = strtotime($times[1]);
					if (strpos($times[1], "pm") === false && strpos($times[1], "am") === false)
						return false;
					if (strpos($times[0], "pm") === false && strpos($times[0], "am") === false)
					{
						if ($end - $start > 3600 * 12)
							$start += 3600 * 12;
					}
					$start = date("H:i:s", $start);
					$end = date("H:i:s", $end);
					$keys[$i] = $start . "-" . $end;
				}
				else // Single time
				{
					if (strpos($keys[$i], "pm") === false && strpos($keys[$i], "am") === false)
						return false;
					$start = date("H:i:s", strtotime($keys[$i]));
					$end = date("H:i:s", strtotime($keys[$i]) + 3600);
					$keys[$i] = $start . "-" . $end;
				}
			}
		}
		//print_r(array_values($keys));
		//print_r(array_values($structure));
		//echo "<br>";
		$dates = array();
		$dateroots = array();
		$weekroots = array();
		$c = 0;
		$last = "";
		// Generate tree
		foreach($structure as $s)
		{
			switch($s)
			{
			case "D":
				if ($last == "T" || $last == "W")
				{
					$dateroots = array();
					$weekroots = array();
				}
				$d = new Node($keys[$c], "D");
				array_push($dates, $d);
				array_push($dateroots, $d);
				break;
			case "W":
				if ($last == "")
				{
					$d = new Node($this->startRange . "-" . $this->endRange, "D");
					array_push($dates, $d);
					array_push($dateroots, $d);
				}
				if ($last == "T")
				{
					$weekroots = array();
				}
				$w = new Node($keys[$c], $s);
				array_push($weekroots, $w);
				foreach($dateroots as $date)
				{
					$date->addChild($w);
					$w->addParent($date);
				}
				break;
			case "T":
				if ($last == "")
				{
					$d = new Node($this->startRange . "-" . $this->endRange, "D");
					array_push($dates, $d);
					array_push($dateroots, $d);
				}
				$t = new Node($keys[$c], $s);
				if (empty($weekroots))
				{
					foreach($dateroots as $date)
					{
						$date->addChild($t);
						$t->addParent($date);
					}
				}
				else
				{
					foreach($weekroots as $week)
					{
						$week->addChild($t);
						$t->addParent($week);
					}
				}
				break;
			}
			$last = $s;
			$c++;
		}
		// Generate ranges
		foreach($dates as $date)
		{
			//$this->recursivePrint($date);
			//echo "<br>";
			$this->recurse($date, "");
		}
		// Merge overlaps
		usort($this->result, 'cmp');
		for ($i = 1; $i < count($this->result); $i++)
		{
			if ($this->result[$i - 1][1] >= $this->result[$i][1])
			{
				array_splice($this->result, $i, 1);
			}
			else if ($this->result[$i - 1][1] >= $this->result[$i][0])
			{
				$this->result[$i - 1][1] = $this->result[$i][1];
				array_splice($this->result, $i, 1);
			}
		}
		foreach($this->result as $r)
		{
			//echo date("m/d/Y H:i:s", $r[0]) . "-" . date("m/d/Y H:i:s", $r[1]) . "<br>";
		}
		return $this->result;
	}
	
	function recurse($node, $prev)
	{ 
		// Check for and ignore negative ranges
		if ($node->getType() == "D" || $node->getType() == "T")
		{
			$ranges = explode("-", $node->getData());
			if ($node->getType() == "D")
			{
				if (strtotime($ranges[0]) > strtotime($ranges[1]))
				{
					return;
				}
			}
			else if ($node->getType() == "T")
			{
				if (strtotime($ranges[0]) >= strtotime($ranges[1]))
				{
					return;
				}
			}
		}
		$days = array();
		if ($node->getType() == "W")
		{
			$ranges = explode("-", $prev);
			$days = $this->getDateForSpecificDayBetweenDates($ranges[0], $ranges[1], $node->getData());
		}
		$children = $node->getChildren();
		if (empty($children))
		{
			if ($node->getType() == "D")
			{
				$dates = explode("-", $node->getData());
				array_push($this->result, array(strtotime($dates[0] . " 00:00:00"), strtotime($dates[1] . " 23:59:59") + 1));
			}
			else if ($node->getType() == "W")
			{
				foreach($days as $d)
				{
					array_push($this->result, array(strtotime($d . " 00:00:00"), strtotime($d . " 23:59:59") + 1));
				}
			}
			else if ($node->getType() == "T")
			{
				$ranges = explode("-", $prev);
				$days = $this->getDaysBetweenDates($ranges[0], $ranges[1]);
				$times = explode("-", $node->getData());
				foreach($days as $d)
				{
					if ($times[1] == "23:59:59")
						array_push($this->result, array(strtotime($d . " " . $times[0]), strtotime($d . " " . $times[1]) + 1));
					else
						array_push($this->result, array(strtotime($d . " " . $times[0]), strtotime($d . " " . $times[1])));
				}
			}
		}
		else
			foreach($node->getChildren() as $n)
			{
				if ($node->getType() == "D")
					$this->recurse($n, $node->getData());
				else if ($node->getType() == "W")
				{
					foreach($days as $d)
					{
						$this->recurse($n, $d . "-" . $d);
					}
				}
			}
	}
	
	function recursivePrint($node)
	{
		echo $node->toString() . " ";
		$children = $node->getChildren();
		if (!empty($children))
			foreach($node->getChildren() as $n)
			{
				$this->recursivePrint($n);
			}
	}
	
	function getDateForSpecificDayBetweenDates($startDate, $endDate, $weekdayNumber)
	{
		$startDate = strtotime($startDate);
		$endDate = strtotime($endDate);

		$dateArr = array();

		do
		{
			if(date("w", $startDate) != $weekdayNumber)
			{
				$startDate += (24 * 3600); // add 1 day
			}
		} while(date("w", $startDate) != $weekdayNumber);


		while($startDate <= $endDate)
		{
			$dateArr[] = date('m/d/Y', $startDate);
			$startDate += (7 * 24 * 3600); // add 7 days
		}

		return($dateArr);
	}
	
	function getDaysBetweenDates($startDate, $endDate)
	{
		$startDate = strtotime($startDate);
		$endDate = strtotime($endDate);
		$return = array();
		while ($startDate <= $endDate)
		{
			array_push($return, date("m/d/Y", $startDate));
			$startDate += 3600 * 24;
		}
		return $return;
	}
}
	
function cmp($a, $b)
{
	if ($a[0] == $b[0])
		return 0;
	return ($a[0] < $b[0]) ? -1 : 1;
}
?>