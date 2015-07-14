<?php
/*--------------------------------------------------------+
| SourcePunish WebApp                                     |
| Copyright (C) 2015 https://sourcepunish.net             |
+---------------------------------------------------------+
| This program is free software and is released under     |
| the terms of the GNU Affero General Public License      |
| version 3 as published by the Free Software Foundation. |
| You can redistribute it and/or modify it under the      |
| terms of this license, which is included with this      |
| software as agpl-3.0.txt or viewable at                 |
| http://www.gnu.org/licenses/agpl-3.0.html               |
+--------------------------------------------------------*/

if(!defined('SP_LOADED')) die('Access Denied!');

class SQL {
	private $Connection = null;

	public function __construct($Host, $Username, $Password, $Database) {
        $this->Connection = @mysqli_connect($Host, $Username, $Password, $Database);
		if(mysqli_connect_errno()) 
            throw new SQLError('sql.connect', mysqli_connect_error(), mysqli_connect_errno());
        mysqli_set_charset($this->Connection, 'UTF-8');
	}
	public function __destruct() {
		$this->Close();
	}
	public function Close() {
		if($this->Connection !== null) {
            mysqli_close($this->Connection);
            $this->Connection = null;
            return true;
        } else
            return false;
	}
	public function Error() {
        throw new SQLError('sql.query', mysqli_error($this->Connection), mysqli_errno($this->Connection));
	}
	public function Free($Free_Query) {
		return mysqli_free_result($Free_Query);	
	}
	public function Escape($Escape_String) {
		if(get_magic_quotes_gpc())
			$Escape_String = stripslashes($Escape_String);
		return mysqli_real_escape_string($this->Connection, $Escape_String);
	}
	public function Query($Query_String) {
		$SQL_Query = @mysqli_query($this->Connection, $Query_String) or $this->Error();
		return $SQL_Query;
	}
	public function Query_InsertID($Query_String) {
		$this->Query($Query_String);
		return mysqli_insert_id($this->Connection);
	}
	public function InsertID() {
		return mysqli_insert_id($this->Connection);
	}
	public function Query_Rows($Query_String) {
		$Q = $this->Query($Query_String);
        $Rows = mysqli_num_rows($Q);
        $this->Free($Q);
		return $Rows;
	}
	public function Rows($Query) {
		return mysqli_num_rows($Query);
	}
	public function Query_FetchArray($Query_String) {
		$Q = $this->Query($Query_String);
		$Array = mysqli_fetch_assoc($Q);
        $this->Free($Q);
		return $Array;
	}
	public function FetchArray($Query) {
		return mysqli_fetch_assoc($Query);
	}
}
?>