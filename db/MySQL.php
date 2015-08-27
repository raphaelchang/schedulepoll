<?php

class MySQL extends DBMS
{
    private $db_auth = null;
    
    private $conn = false;
    private $result = null;
    private $last_query = '';
    
    public $queried = null;
    public $queried_length = 0;
    
    public function __construct($host, $username, $password, $database)
    {
        $this->db_auth = array('host' => $host, 'username' => $username, 'password' => $password, 'database' => $database);
    }
    
    public function __destruct()
    {
        //mysql_close($this->conn);
    }
    
    public function Connect()
    {
        $this->conn = mysql_connect($this->db_auth['host'], $this->db_auth['username'], $this->db_auth['password']);
        
        if(!$this->conn)
        {
            $this->conn = false;
            
            if(defined('DEBUG'))
            { echo mysql_error(); }
                
            return false;
        }
        
        $res = mysql_select_db($this->db_auth['database'], $this->conn);
        
        if(!$res)
        {
            $this->conn = false;
            
            if(defined('DEBUG'))
            { echo mysql_error(); }
            
            return false;
        }
        
        return true;
    }
    
    public function Disconnect()
    {
        if(!$this->conn) return;
        
        mysql_close($this->conn);
        
        if(defined('DEBUG'))
        { echo 'disconnected'; }
    }
    
	public function SetupRows()
	{
	    $out = array();
	    $r = 0;
        
	    while($row = mysql_fetch_assoc($this->result))
	    {
	        $this_row = array();
	        $c = 0;
            
            $field_count = mysql_num_fields($this->result);
            
	        while ($c < $field_count)
            {      
	            $the_column = mysql_fetch_field($this->result, $c);  
                $name = $the_column->name;  
	            $this_row[$name] = $row[$name];            
	            $c++; // :)
	        }    
            
	        $out[$r] = $this_row;
	        $r++;
	    }    
        
        $this->queried_length = $r;
	    
        $this->queried = $out;
	}
    
    public function Query($query)
    {
        if(strcasecmp($query, $this->last_query) == 0)
            return;
            
        $this->last_query = $query;
            
        $this->result = mysql_query($query, $this->conn);
        
        if(!$this->result)
        {
            if(defined('DEBUG'))
            { echo mysql_error(); }
            
            return false;
        }
        
		if(is_bool($this->result) === false)
        	$this->SetupRows();
        
        return true;
    }
    
    public function Results()
    {
        return $this->queried;
    }
    
    public function Clear()
    {
        $this->result = null;
        $this->queried = null;
        $this->queried_length = 0;
        $this->last_query = '';
    }
}
?>