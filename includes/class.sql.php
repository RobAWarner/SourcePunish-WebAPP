<?php
/*--------------------------------------------------------+
| SourcePunish WebApp                                     |
| Copyright (C) https://sourcepunish.net                  |
+---------------------------------------------------------+
| This program is free software and is released under     |
| the terms of the GNU Affero General Public License      |
| version 3 as published by the Free Software Foundation. |
| You can redistribute it and/or modify it under the      |
| terms of this license, which is included with this      |
| software as agpl-3.0.txt or viewable at                 |
| http://www.gnu.org/licenses/agpl-3.0.html               |
+--------------------------------------------------------*/
if(!defined('IN_SP')) die('Access Denied!');
class SQL {
	private $Connection = null;

	public function __construct($Host, $Username, $Password, $Database) {
		PrintDebug('Called SQL->__construct', 3);
        $this->Connection = mysqli_connect($Host, $Username, $Password, $Database);
		if(mysqli_connect_errno()) 
            die('MySQL Connection Error '.mysqli_connect_errno().': '.mysqli_connect_error());
	}
	public function __destruct() {
        PrintDebug('Called SQL->__destruct', 3);
		$this->Close();
	}
	public function Close() {
        PrintDebug('Called SQL->Close', 3);
		if($this->Connection != null) {
            mysqli_close($this->Connection);
            $this->Connection = null;
            return true;
        } else
            return false;
	}
	public function Error() {
        die('MySQL Error: '.mysqli_error($this->Connection));
	}
	public function Free($Free_Query) {
        PrintDebug('Called SQL->Free', 3);
		return mysqli_free_result($Free_Query);	
	}
	public function Escape($Escape_String) {
        PrintDebug('Called SQL->Escape', 3);
		if(get_magic_quotes_gpc())
			$Escape_String = stripslashes($Escape_String);
		return mysqli_real_escape_string($this->Connection, $Escape_String);
	}
	public function Query($Query_String) {
        PrintDebug('Called SQL->Query with \''.$Query_String.'\'', 3);
		$SQL_Query = @mysqli_query($this->Connection, $Query_String) or $this->Error();
		return $SQL_Query;
	}
	public function Query_InsertID($Query_String) {
        PrintDebug('Called SQL->Query_InsertID', 3);
		$this->Query($Query_String);
		return mysqli_insert_id($this->Connection);
	}
	public function InsertID() {
        PrintDebug('Called SQL->InsertID', 3);
		return mysqli_insert_id($this->Connection);
	}
	public function Query_Rows($Query_String) {
        PrintDebug('Called SQL->Query_Rows', 3);
		$Q = $this->Query($Query_String);
        $Rows = mysqli_num_rows($Q);
        $this->Free($Q);
		return $Rows;
	}
	public function Rows($Query) {
        PrintDebug('Called SQL->Rows', 3);
		return mysqli_num_rows($Query);
	}
	public function Query_FetchArray($Query_String) {
        PrintDebug('Called SQL->Query_FetchArray', 3);
		$Q = $this->Query($Query_String);
		$Array = mysqli_fetch_assoc($Q);
        $this->Free($Q);
		return $Array;
	}
	public function FetchArray($Query) {
        PrintDebug('Called SQL->FetchArray', 3);
		return mysqli_fetch_assoc($Query);
	}
}
?>