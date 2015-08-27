<?php

abstract class DBMS
{
	public abstract function __construct($host, $username, $password, $database);
	public abstract function Connect();
	public abstract function Disconnect();
	public abstract function Query($query);
    public abstract function Results();
	public abstract function Clear();
}
?>