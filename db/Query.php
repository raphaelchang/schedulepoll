<?php

# Database Driver #
class Query
{
    private $dbms = '';
    private $dbobj = null;
    
    private $query = '';
    
    private $read_position = 0;
    
    public function __construct($dbms, $host, $username, $password, $database)
    {
        $this->dbms = $dbms;
        
        switch($dbms)
        {
            case 'mysql':
                $this->dbobj = new MySQL($host, $username, $password, $database);
            break;
            default:
                die('FATAL: Invalid database management system; supplied with: ' . $dbms);
            break;
        }
    }
    
    public function DatabaseType()
    {
        return $this->dbms;
    }
    
    public function Connect()
    {
        $this->dbobj->Connect();
    }
    
    public function Disconnect()
    {
        $this->dbobj->Disconnect();
    }
    
    public function Prepare($query)
    {
        $this->Clear();
        $this->query = $query;
    }
	
    public function Execute()
    {
        if(strlen($this->query) <= 0)
            return 'No query supplied';
        
		$variables = func_get_args();
        $newquery = $this->query;
		$trans = array();
		
        for($i = 0; $i < count($variables); $i++)
        {
			$trans['$'.$i] = mysql_real_escape_string($variables[$i]);
        }
		
		$newquery = strtr($newquery, $trans);
		
        $this->dbobj->Query($newquery);
    }
    
    public function RowCount()
    {
        return $this->dbobj->queried_length;
    }
    
    public function Fetch()
    {
        if($this->read_position < $this->dbobj->queried_length)
        {
            return $this->dbobj->queried[$this->read_position++];
        }
        else
        {
            $this->Rewind();
            return false;
        }
            
        return false;
    }
    
    public function Rewind()
    {
        $this->read_position = 0;
    }
    
    public function Clear()
    {
        $this->dbobj->Clear();
        
        $this->read_position = 0;
    }
}
?>