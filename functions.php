<?php
require_once('PasswordHash.php');
    
session_start();

function create_event($name, $start, $end, $description, $type, $account = 0, $public = true, $hidden = false)
{
	global $db;
	$db->Prepare("SELECT UUID()");
	$db->Execute();
	$uuid = $db->Fetch();
	$uuid = str_replace("-", "", implode($uuid, ""));
	$uuid = substr($uuid, 0, 16);
	$db->Prepare("INSERT INTO events (name,range_start,range_end,hash_code,description,type,account,public,hidden) VALUES ('$0','" . $start . " 00:00:00'" . ",'" . $end . " 23:59:00', '$1', '$2', '$3', '$4', '$5', '$6')");
	$db->Execute($name, $uuid, $description, $type, $account, ($public ? 1 : 0), ($hidden ? 1 : 0));
	return $uuid;
}

function get_event($hash)
{
	global $db;
	$db->Prepare("SELECT * FROM events WHERE hash_code='$0'");
	$db->Execute($hash);
	return $db->Fetch();
}

function edit_event($id, $name, $description)
{
	global $db;
	$db->Prepare("UPDATE events SET name='$0', description='$1' WHERE id='$2'");
	$db->Execute($name, $description, $id);
}

function put_schedule($name, $ranges, $event, $account = 0, $text = "", $hash = "")
{
	global $db;
	if ($hash == "")
	{
		$db->Prepare("SELECT * FROM users WHERE event_id='$0' AND account='$1'");
		$db->Execute($event, $account);
	}
	else
	{
		$db->Prepare("SELECT * FROM users WHERE id IN (SELECT completed FROM invites WHERE event_id='$0' AND hash='$1')");
		$db->Execute($event, $hash);
	}
	$id = "";
	if ($db->RowCount() == 0 || ($hash == "" && $account == 0))
	{
		$db->Prepare("INSERT INTO users (event_id, name, account, entry) VALUES ('$0', '$1', '$2', '$3')");
		$db->Execute($event, $name, $account, $text);
		$db->Prepare("SELECT LAST_INSERT_ID()");
		$db->Execute();
		$id = $db->Fetch();
		$id = implode($id, "");
	}
	else
	{
		$id = $db->Fetch()["id"];
		$db->Prepare("UPDATE users SET entry='$0' WHERE id='$1'");
		$db->Execute($text, $id);
		$db->Prepare("DELETE FROM schedules WHERE user_id='$0'");
		$db->Execute($id);
	}
	foreach($ranges as $r)
	{
		$start = date("Y-m-d H:i:s", $r[0]);
		$end = date("Y-m-d H:i:s", $r[1]);
		$db->Prepare("INSERT INTO schedules (user_id, event_id, range_start, range_end) VALUES ('$0', '$1', '$2', '$3')");
		$db->Execute($id, $event, $start, $end);
	}
	generate_event_intervals($event);
	return $id;
}

function get_users($event)
{
	global $db;
	$db->Prepare("SELECT * FROM users WHERE event_id='$0' ORDER BY name");
	$db->Execute($event);
	$ret = array();
	while($result = $db->Fetch())
	{
		array_push($ret, $result);
	}
	return $ret;
}

function get_schedule($user, $event)
{
	global $db;
	$db->Prepare("SELECT * FROM schedules WHERE event_id='$0' AND user_id='$1' ORDER BY range_start");
	$db->Execute($event, $user);
	$ret = array();
	while($result = $db->Fetch())
	{
		array_push($ret, $result);
	}
	return $ret;
}

function generate_event_intervals($event)
{
	global $db;
	$db->Prepare("DELETE FROM event_intervals WHERE event_id='$0'");
	$db->Execute($event);
	$db->Prepare("SELECT * FROM schedules WHERE event_id='$0'");
	$db->Execute($event);
	$intervals = array();
	while($result = $db->Fetch())
	{
		$start = strtotime($result["range_start"]);
		$end = strtotime($result["range_end"]);
		if (!isset($intervals[$start]))
			$intervals[$start] = array(array(), array());
		if (!isset($intervals[$end]))
			$intervals[$end] = array(array(), array());
		array_push($intervals[$start][0], $result["user_id"]);
		array_push($intervals[$end][1], $result["user_id"]);
	}
	$current = array();
	$first = true;
	$lastkey = "";
	ksort($intervals);
	$query = "INSERT INTO event_intervals (event_id, user_id, range_start, range_end) VALUES ";		
	foreach($intervals as $key => $value)
	{
		//echo date("m/d/Y H:i:s", $key) . ": ";
		//print_r(array_values($value[0]));
		//print_r(array_values($value[1]));
		//echo "<br>";
		if (!$first)
		{
			foreach($current as $k => $v)
			{
				if ($v == 1)
				{
					$query .= "('$event', '$k', '" . date("Y-m-d H:i:s", $lastkey) . "', '" . date("Y-m-d H:i:s", $key) . "'),";
				}
			}
		}
		foreach($value[0] as $user)
		{
			$current[$user] = 1;
		}
		foreach($value[1] as $user)
		{
			$current[$user] = 0;
		}
		$first = false;
		$lastkey = $key;
	}
	$query = substr($query, 0, strlen($query) - 1);
	$db->Prepare($query);
	$db->Execute();
}

function get_event_intervals($event)
{
	global $db;
	$ret = array();
	$db->Prepare("SELECT * FROM event_intervals WHERE event_id=$0 ORDER BY range_start");
	$db->Execute($event);
	while($result = $db->Fetch())
	{
		$key = strtotime($result["range_start"]);
		if (!isset($ret[$key]))
		{
			$ret[$key] = array($result["range_start"], $result["range_end"], array());
		}
		$ret[$key][2][$result["user_id"]] = 1;
	}
	return array_values($ret);
}

function event_intervals_cmp($a, $b)
{
	if (count($a[2]) == count($b[2]))
		return event_intervals_sub_cmp($a, $b);
	return (count($a[2]) > count($b[2])) ? -1 : 1;
}

function event_intervals_sub_cmp($a, $b)
{
	if (strtotime($a[0]) == strtotime($b[0]))
		return 0;
	return (strtotime($a[0]) < strtotime($b[0])) ? -1 : 1;
}

function add_comment($name, $content, $event_id)
{
	global $db;
	$db->Prepare("INSERT INTO comments (name, content, event_id) VALUES ('$0', '$1', '$2')");
	$db->Execute($name, $content, $event_id);
}

function get_comments($event_id)
{
	global $db;
	$db->Prepare("SELECT * FROM comments WHERE event_id='$0' ORDER BY timestamp DESC");
	$db->Execute($event_id);
	$ret = array();
	while($result = $db->Fetch())
	{
		array_push($ret, $result);
	}
	return $ret;
}

function get_event_creator($event_id) // For centered events
{
	global $db;
	$db->Prepare("SELECT * FROM schedules WHERE event_id='$0'");
	$db->Execute($event_id);
	$id = $db->Fetch()["user_id"];
	$db->Prepare("SELECT * FROM users WHERE id='$0'");
	$db->Execute($id);
	return $db->Fetch();
}

function get_schedules_for_event($event_id)
{
	global $db;
	$db->Prepare("SELECT * FROM schedules WHERE event_id='$0'");
	$db->Execute($event_id);
	$ret = array();
	while($result = $db->Fetch())
	{
		array_push($ret, $result);
	}
	return $ret;
}

function add_user($name, $event, $account = 0)
{
	global $db;
	$db->Prepare("INSERT INTO users (event_id, name, account) VALUES ('$0', '$1', '$2')");
	$db->Execute($event, $name, $account);
	$db->Prepare("SELECT LAST_INSERT_ID()");
	$db->Execute();
	$id = $db->Fetch();
	$id = implode($id, "");
	return $id;
}

function add_vote($name, $intervals, $event_id, $account = 0, $hash = "")
{
	global $db;
	$user = false;
	if ($hash == "")
		$user = get_user_from_account($event_id, $account);
	else
	{
		$db->Prepare("SELECT * FROM users WHERE id IN (SELECT completed FROM invites WHERE event_id='$0' AND hash='$1')");
		$db->Execute($event_id, $hash);
		if ($db->RowCount() > 0)
			$user = $db->Fetch();
	}
	if ($user !== false && ($hash != "" || $account != 0))
	{
		$query = "UPDATE votes SET value = CASE interval_id ";
		foreach($intervals as $i)
		{
			$query .= "WHEN '" . $i[0] . "' THEN '" . $i[1] . "' ";
		}
		$query .= "END WHERE user_id='$0'";
		$db->Prepare($query);
		$db->Execute($user["id"]);
		return $user["id"];
	}
	else
	{
		$id = add_user($name, $event_id, $account);
		$query = "INSERT INTO votes (interval_id,user_id,value) VALUES ";
		foreach($intervals as $i)
		{
			$query .= "(" . $i[0] . ",$id," . $i[1] . "),";
		}
		$query = substr($query, 0, strlen($query) - 1);
		$db->Prepare($query);
		$db->Execute();
		return $id;
	}
}

function get_votes($user_id)
{
	global $db;
	$db->Prepare("SELECT * FROM votes WHERE user_id='$0'");
	$db->Execute($user_id);
	$ret = array();
	while($result = $db->Fetch())
	{
		$ret[$result["interval_id"]] = $result["value"];
	}
	return $ret;
}

function login($email, $password)
{
	global $db;
	
	if(isset($_COOKIE['session']))
        return 3;
		
	$db->Prepare('SELECT * FROM `accounts` WHERE email=\'$0\'');
	$db->Execute($email);
	
    if($db->RowCount() <= 0)
        return 1;
    
    $row = $db->Fetch();
    $hasher = new PasswordHash(8, false);
    
    if(!$hasher->CheckPassword($password, $row['password']))
        return 2;
        
	if($row['activated'] == 0)
        return 4;
		
    $secure = $hasher->HashPassword($email . ':' . uniqid() . ':' . $password);
    
    $db->Prepare('SELECT id FROM `sessions` WHERE user_id=\'$0\'');
    $db->Execute($row['id']);
	
    if($db->RowCount() <= 0)
    {
        $db->Prepare('INSERT INTO `sessions` (ip, session, user_id) VALUES (\'$0\', \'$1\', \'$2\')');
		$db->Execute($_SERVER['REMOTE_ADDR'], $secure, $row['id']);
    }
    else
    {
        $sess_row = $db->Fetch();
        $db->Prepare('UPDATE `sessions` SET ip=\'$0\', session=\'$1\', user_id=\'$2\' WHERE id=$3');
		$db->Execute($_SERVER['REMOTE_ADDR'], $secure, $row['id'], $sess_row['id']);
    }
	
	setcookie('session', $secure);
	
	return 0;
}

function load_session()
{
	global $db;
	
	global $account;
	$account = false;
    if(isset($_COOKIE['session']))
    {
        $db->Prepare('SELECT * FROM `sessions` WHERE session=\'$0\' LIMIT 1');
		$db->Execute($_COOKIE['session']);
		if($db->RowCount() <= 0)
        {
            setcookie('session', "", time() - 1);
        }
        else
        {
			$row = $db->Fetch();
            $account = $row['user_id'];
        }
    }
}

function logout()
{
	global $db;
	
    if(isset($_COOKIE['session']))
    {
		$db->Prepare('DELETE FROM `sessions` WHERE session=\'$0\' LIMIT 1');
		$db->Execute($_COOKIE['session']);
	} else
		return -1;

    setcookie('session', "", time() - 1);
    
    return 0;
}

function register($email, $password, $first, $last)
{
	global $db;
    $db->Prepare('SELECT id FROM `accounts` WHERE email=\'$0\'');
	$db->Execute($email);
    if($db->RowCount() > 0)
        return -1;
	$hasher = new PasswordHash(8, false);
    $password = $hasher->HashPassword($password);
	$firstname = ucfirst($first);
	$lastname = ucfirst($last);
	$db->Prepare("INSERT INTO accounts (email, password, first_name, last_name, activated) VALUES ('$0', '$1', '$2', '$3', '$4')");
	$db->Execute(trim($email), $password, trim($firstname), trim($lastname), 0);
	$db->Prepare("SELECT LAST_INSERT_ID()");
	$db->Execute();
	$id = $db->Fetch();
	$id = implode($id, "");
	$db->Prepare("SELECT UUID()");
	$db->Execute();
	$uuid = $db->Fetch();
	$uuid = str_replace("-", "", implode($uuid, ""));
	$uuid = substr($uuid, 0, 16);
	$db->Prepare("INSERT INTO activation_keys (`key`, user_id) VALUES ('$0', '$1')");
	$db->Execute($uuid, $id);
	return $uuid;
}

function activate_account($key)
{
	global $db;
	$db->Prepare("SELECT user_id FROM activation_keys WHERE `key`='$0'");
	$db->Execute($key);
	if($db->RowCount() == 1)
	{
        $id = $db->Fetch();
		$db->Prepare("UPDATE accounts SET activated='1' WHERE id='$0'");
		$db->Execute($id["user_id"]);
		$db->Prepare("DELETE FROM activation_keys WHERE `key`='$0'");
		$db->Execute($key);
		return $id;
	}
	return false;
}

function get_account_info($id)
{
	global $db;
	$db->Prepare("SELECT * FROM accounts WHERE id='$0'");
	$db->Execute($id);
	return $db->Fetch();
}

function get_entry($event_id, $account_id)
{
	global $db;
	$db->Prepare("SELECT entry FROM users WHERE event_id='$0' AND account='$1'");
	$db->Execute($event_id, $account_id);
	return $db->Fetch()["entry"];
}

function get_user_from_account($event_id, $account_id)
{
	global $db;
	$db->Prepare("SELECT * FROM users WHERE event_id='$0' AND account='$1'");
	$db->Execute($event_id, $account_id);
	if ($db->RowCount() > 0)
		return $db->Fetch();
	return false;
}

function get_events_by_account($account)
{
	global $db;
	$db->Prepare("SELECT * FROM events WHERE account='$0' ORDER BY id DESC");
	$db->Execute($account);
	$ret = array();
	while($result = $db->Fetch())
	{
		array_push($ret, $result);
	}
	return $ret;
}

function get_number_responses($event)
{
	global $db;
	$db->Prepare("SELECT * FROM users WHERE event_id='$0'");
	$db->Execute($event);
	return $db->RowCount();
}

function get_involved_events($account)
{
	global $db;
	$db->Prepare("SELECT * FROM events WHERE id IN (SELECT event_id FROM users WHERE account='$0' ORDER BY id DESC) AND account != '$1'");
	$db->Execute($account, $account);
	$ret = array();
	while($result = $db->Fetch())
	{
		array_push($ret, $result);
	}
	return $ret;
}

function add_invite($event, $email)
{
	global $db;
	$db->Prepare("SELECT * FROM invites WHERE email='$0' AND event_id='$1'");
	$db->Execute($email, $event);
	if ($db->RowCount() > 0)
		return;
	$db->Prepare("SELECT id FROM accounts WHERE email='$0'");
	$db->Execute($email);
	if ($db->RowCount() == 1)
	{
		$id = $db->Fetch()["id"];
		$db->Prepare("INSERT INTO invites (event_id, account_id, email) VALUES ('$0', '$1', '$2')");
		$db->Execute($event, $id, $email);
	}
	else
	{
		$db->Prepare("SELECT UUID()");
		$db->Execute();
		$uuid = $db->Fetch();
		$uuid = str_replace("-", "", implode($uuid, ""));
		$uuid = substr($uuid, 0, 16);
		$db->Prepare("INSERT INTO invites (event_id, hash, email) VALUES ('$0', '$1', '$2')");
		$db->Execute($event, $uuid, $email);
	}
}

function validate_invite($event, $hash)
{
	global $db;
	$db->Prepare("SELECT * FROM invites WHERE event_id='$0' AND hash='$1'");
	$db->Execute($event, $hash);
	return $db->RowCount() > 0;
}

function validate_invite_account($event, $account)
{
	global $db;
	$db->Prepare("SELECT * FROM invites WHERE event_id='$0' AND account_id='$1'");
	$db->Execute($event, $account);
	return $db->RowCount() > 0;
}

function get_invite_user($event, $hash)
{
	global $db;
	$db->Prepare("SELECT * FROM users WHERE id IN (SELECT completed FROM invites WHERE event_id='$0' AND hash='$1')");
	$db->Execute($event, $hash);
	if ($db->RowCount() > 0)
		return $db->Fetch();
	return false;
}

function update_invite($event, $hash, $user)
{
	global $db;
	$db->Prepare("UPDATE invites SET completed='$0' WHERE event_id='$1' AND hash='$2'");
	$db->Execute($user, $event, $hash);
}

function update_invite_account($event, $account, $user)
{
	global $db;
	$db->Prepare("UPDATE invites SET completed='$0' WHERE event_id='$1' AND account_id='$2'");
	$db->Execute($user, $event, $account);
}

function get_invites($event, $pending = false)
{
	global $db;
	if ($pending)
		$db->Prepare("SELECT * FROM invites WHERE event_id='$0' AND completed=0");
	else
		$db->Prepare("SELECT * FROM invites WHERE event_id='$0'");
	$db->Execute($event);
	$ret = array();
	while($result = $db->Fetch())
	{
		array_push($ret, $result);
	}
	return $ret;
}

function get_invites_on_account($account, $pending = false)
{
	global $db;
	if ($pending)
		$db->Prepare("SELECT * FROM events WHERE id IN (SELECT event_id FROM invites WHERE account_id='$0' AND completed=0)");
	else
		$db->Prepare("SELECT * FROM events WHERE id IN (SELECT event_id FROM invites WHERE account_id='$0')");
	$db->Execute($account);
	$ret = array();
	while($result = $db->Fetch())
	{
		array_push($ret, $result);
	}
	return $ret;
}
?>