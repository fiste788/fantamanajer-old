<?php
/**
* Dump MySQL database
*
* Here is an inline example:
* <code>
* $connection = @mysql_connect($dbhost,$dbuser,$dbpsw);
* $dumper = new MySQLDump($dbname,'filename.sql',FALSE,FALSE);
* $dumper->doDump();
* </code>
*
* Special thanks to:
* - Andrea Ingaglio <andrea@coders4fun.com> helping in development of all class code
* - Dylan Pugh for precious advices halfing the size of the output file and for helping in debug
*
* @name    MySQLDump
* @author  Daniele Viganò - CreativeFactory.it <daniele.vigano@creativefactory.it>
* @version 2.20 - 02/11/2007
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

class MySQLDump {
	/**
	* @access private
	*/
	var $database = NULL;

	/**
	* @access private
	*/
	var $compress = FALSE;

	/**
	* @access private
	*/
	var $hexValue = FALSE;

  /**
	* The output filename
	* @access private
	*/
	var $filename = NULL;

	/**
	* The pointer of the output file
	* @access private
	*/
	var $file = NULL;

	/**
	* @access private
	*/
	var $isWritten = FALSE;

	/**
	* Class constructor
	* @param string $db The database name
	* @param string $filepath The file where the dump will be written
	* @param boolean $compress It defines if the output file is compress (gzip) or not
	* @param boolean $hexValue It defines if the outup values are base-16 or not
	*/
	function MYSQLDump($db = NULL, $filepath = 'dump.sql', $compress = FALSE, $hexValue = FALSE){
		$this->compress = $compress;
		if ( !$this->setOutputFile($filepath) )
			return FALSE;
		return $this->setDatabase($db);
	}

	/**
	* Sets the database to work on
	* @param string $db The database name
	*/
	function setDatabase($db){
		$this->database = $db;
		if ( !@mysql_select_db($this->database) )
			return FALSE;
		return TRUE;
  }

	/**
	* Returns the database where the class is working on
	* @return string
	*/
  function getDatabase(){
		return $this->database;
	}

	/**
	* Sets the output file type (It can be made only if the file hasn't been already written)
	* @param boolean $compress If it's TRUE, the output file will be compressed
	*/
	function setCompress($compress){
		if ( $this->isWritten )
			return FALSE;
		$this->compress = $compress;
		$this->openFile($this->filename);
		return TRUE;
  }

	/**
	* Returns if the output file is or not compressed
	* @return boolean
	*/
  function getCompress(){
		return $this->compress;
	}

	/**
	* Sets the output file
	* @param string $filepath The file where the dump will be written
	*/
	function setOutputFile($filepath){
		if ( $this->isWritten )
			return FALSE;
		$this->filename = $filepath;
		$this->file = $this->openFile($this->filename);
		return $this->file;
  }

  /**
	* Returns the output filename
	* @return string
	*/
  function getOutputFile(){
		return $this->filename;
	}

	/**
	* Writes to file the $table's structure
	* @param string $table The table name
	*/
  function getTableStructure($table){
		if ( !$this->setDatabase($this->database) )
			return FALSE;
		// Structure Header
		$structure = "-- \n";
		$structure .= "-- Table structure for table `{$table}` \n";
		$structure .= "-- \n\n";
		// Dump Structure
		$structure .= 'DROP TABLE IF EXISTS `'.$table.'`;'."\n";
		$structure .= "CREATE TABLE `".$table."` (\n";
		$records = @mysql_query('SHOW FULL COLUMNS FROM `'.$table.'`');
		if ( @mysql_num_rows($records) == 0 )
			return FALSE;
		while ( $record = mysql_fetch_assoc($records) ) {
			$structure .= '`'.$record['Field'].'` '.$record['Type'];
			if ( $record['Collation'] != NULL)
				$structure .= ' COLLATE '.$record['Collation'];
			if ( !empty($record['Default']) )
				if( $record['Default'] == 'CURRENT_TIMESTAMP')
					$structure .= ' DEFAULT '.$record['Default'];
				else
					$structure .= ' DEFAULT \''.$record['Default'].'\'';
			if ( @strcmp($record['Null'],'YES') != 0 )
				$structure .= ' NOT NULL';
			if ( !empty($record['Extra']) )
				$structure .= ' '.$record['Extra'];
			if ( !empty($record['Comment']) )
				$structure .= ' COMMENT \''.$record['Comment'].'\'';
			$structure .= ",\n";
		}
		$structure = @ereg_replace(",\n$", NULL, $structure);

		// Save all Column Indexes
		$structure .= $this->getSqlKeysTable($table);
		$structure .= "\n)";

		//Save table engine
		$records = @mysql_query("SHOW TABLE STATUS LIKE '".$table."'");
		if ( $record = @mysql_fetch_assoc($records) ) {
			if ( !empty($record['Engine']) )
				$structure .= ' ENGINE='.$record['Engine'];
			if ( !empty($record['Auto_increment']) )
				$structure .= ' AUTO_INCREMENT='.$record['Auto_increment'];
			if ( !empty($record['Collation']) )
				$structure .= ' COLLATE='.$record['Collation'];
		}

		$structure .= ";\n\n-- --------------------------------------------------------\n\n";
		$this->saveToFile($this->file,$structure);
	}

	/**
	* Writes to file the $table's data
	* @param string $table The table name
	* @param boolean $hexValue It defines if the output is base 16 or not
	*/
	function getTableData($table,$hexValue = TRUE) {
		if ( !$this->setDatabase($this->database) )
			return FALSE;
		// Header
		$data = "-- \n";
		$data .= "-- Dumping data for table `$table` \n";
		$data .= "-- \n\n";

		$records = mysql_query('SHOW FIELDS FROM `'.$table.'`');
		$num_fields = @mysql_num_rows($records);
		if ( $num_fields == 0 )
			return FALSE;
		// Field names
		$selectStatement = "SELECT ";
		$insertStatement = "INSERT INTO `$table` (";
		$hexField = array();
		for ($x = 0; $x < $num_fields; $x++) {
			$record = @mysql_fetch_assoc($records);
			if ( ($hexValue) && ($this->isTextValue($record['Type'])) ) {
				$selectStatement .= 'HEX(`'.$record['Field'].'`)';
				$hexField [$x] = TRUE;
			}
			else
				$selectStatement .= '`'.$record['Field'].'`';
			$insertStatement .= '`'.$record['Field'].'`';
			$insertStatement .= ", ";
			$selectStatement .= ", ";
		}
		$insertStatement = @substr($insertStatement,0,-2).') VALUES';
		$selectStatement = @substr($selectStatement,0,-2).' FROM `'.$table.'`';

		$records = @mysql_query($selectStatement);
		$num_rows = @mysql_num_rows($records);
		$num_fields = @mysql_num_fields($records);
		// Dump data
		if ( $num_rows > 0 ) {
			$data .= $insertStatement;
			for ($i = 0; $i < $num_rows; $i++) {
				$record = @mysql_fetch_assoc($records);
				$data .= ' (';
				for ($j = 0; $j < $num_fields; $j++) {
					$field_name = @mysql_field_name($records, $j);
					if ( isset($hexField[$j]) && (@strlen($record[$field_name]) > 0) )
						$data .= "0x".$record[$field_name];
					elseif ( $record[$field_name] == NULL )
						$data .= "NULL";
					else
						$data .= "'".@str_replace('\"','"',@mysql_escape_string($record[$field_name]))."'";
					$data .= ',';
				}
				$data = @substr($data,0,-1).")";
				$data .= ( $i < ($num_rows-1) ) ? ',' : ';';
				$data .= "\n";
				//if data in greather than 1MB save
				if (strlen($data) > 1048576) {
					$this->saveToFile($this->file,$data);
					$data = '';
				}
			}
			$data .= "\n-- --------------------------------------------------------\n\n";
			$this->saveToFile($this->file,$data);
		}
	}

  /**
	* Writes to file all the selected database tables structure
	* @return boolean
	*/
	function getDatabaseStructure(){
		$structure = '';
		$records = @mysql_query('SHOW TABLE STATUS');
		if ( @mysql_num_rows($records) == 0 )
			return FALSE;
		while ( $record = @mysql_fetch_array($records) ) {
			if($record['Comment'] != "VIEW")
				$structure .= $this->getTableStructure($record[0]);
		}
		return TRUE;
  }
  
	function getViewStructure(){
		$records = @mysql_query('SHOW TABLE STATUS');
		while ( $record = mysql_fetch_array($records) ) {
			if($record['Comment'] != "")
			{
				$data = "-- \n";
				$data .= "-- Dumping data for view `$record[0]` \n";
				$data .= "-- \n\n";
				$exe = mysql_query('SHOW CREATE VIEW ' . $record[0]);
				while ( $row = mysql_fetch_array($exe) ) 
					$data .= str_replace("CREATE","CREATE OR REPLACE",$row['Create View']);
				$data .= ";\n\n-- --------------------------------------------------------\n\n";
				$this->saveToFile($this->file,$data);
			}
		}
		return TRUE;
  }
		

	/**
	* Writes to file all the selected database tables data
	* @param boolean $hexValue It defines if the output is base-16 or not
	*/
	function getDatabaseData($hexValue = TRUE){
		$records = @mysql_query('SHOW TABLE STATUS');
		if ( @mysql_num_rows($records) == 0 )
			return FALSE;
		while ( $record = @mysql_fetch_array($records) ) {
			if($record['Comment'] != "VIEW")
				$this->getTableData($record[0],$hexValue);
		}
  }

	/**
	* Writes to file the selected database dump
	*/
	function doDump() {
		$this->saveToFile($this->file,"SET FOREIGN_KEY_CHECKS = 0;\n\n-- --------------------------------------------------------\n\n");
		$this->getDatabaseStructure();
		$this->getDatabaseData($this->hexValue);
		$this->getViewStructure();
		$this->saveToFile($this->file,"SET FOREIGN_KEY_CHECKS = 1;\n\n");
		$this->closeFile($this->file);
		return TRUE;
	}
	
	/**
	* @deprecated Look at the doDump() method
	*/
	function writeDump($filename) {
		if ( !$this->setOutputFile($filename) )
			return FALSE;
		$this->doDump();
    $this->closeFile($this->file);
    return TRUE;
	}

	/**
	* @access private
	*/
	function getSqlKeysTable ($table) {
		$primary = "";
		unset($unique);
		unset($index);
		unset($fulltext);
		$results = mysql_query("SHOW KEYS FROM `{$table}`");
		if ( @mysql_num_rows($results) == 0 )
			return FALSE;
		while($row = mysql_fetch_object($results)) {
			if (($row->Key_name == 'PRIMARY') AND ($row->Index_type == 'BTREE')) {
				if ( $primary == "" )
					$primary = "  PRIMARY KEY  (`{$row->Column_name}`";
				else
					$primary .= ", `{$row->Column_name}`";
			}
			if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '0') AND ($row->Index_type == 'BTREE')) {
				if ( (!is_array($unique)) OR ($unique[$row->Key_name]=="") )
					$unique[$row->Key_name] = "  UNIQUE KEY `{$row->Key_name}` (`{$row->Column_name}`";
				else
					$unique[$row->Key_name] .= ", `{$row->Column_name}`";
			}
			else
				$unique = "";
			if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '1') AND ($row->Index_type == 'BTREE')) {
				if (isset($index) && (!is_array($index)) OR (!isset($index[$row->Key_name])) OR (isset($index[$row->Key_name])AND $index[$row->Key_name]=="") )
					$index[$row->Key_name] = "  KEY `{$row->Key_name}` (`{$row->Column_name}`";
				else
					$index[$row->Key_name] .= ", `{$row->Column_name}`";
			}
			else
				$index = "";
			if (($row->Key_name != 'PRIMARY') AND ($row->Non_unique == '1') AND ($row->Index_type == 'FULLTEXT')) {
				if ( (!is_array($fulltext)) OR ($fulltext[$row->Key_name]=="") )
					$fulltext[$row->Key_name] = "  FULLTEXT `{$row->Key_name}` (`{$row->Column_name}`";
				else
					$fulltext[$row->Key_name] .= ", `{$row->Column_name}`";
			}
			else
				$fulltext = "";
		}
		$sqlKeyStatement = '';
		// generate primary, unique, key and fulltext
		if ( $primary != "" ) {
			$sqlKeyStatement .= ",\n";
			$primary .= ")";
			$sqlKeyStatement .= $primary;
		}
		if (is_array($unique)) {
			foreach ($unique as $keyName => $keyDef) {
				$sqlKeyStatement .= ",\n";
				$keyDef .= ")";
				$sqlKeyStatement .= $keyDef;

			}
		}
		if (is_array($index)) {
			foreach ($index as $keyName => $keyDef) {
				$sqlKeyStatement .= ",\n";
				$keyDef .= ")";
				$sqlKeyStatement .= $keyDef;
			}
		}
		if (is_array($fulltext)) {
			foreach ($fulltext as $keyName => $keyDef) {
				$sqlKeyStatement .= ",\n";
				$keyDef .= ")";
				$sqlKeyStatement .= $keyDef;
			}
		}
		return $sqlKeyStatement;
	}

  /**
	* @access private
	*/
	function isTextValue($field_type) {
		switch ($field_type) {
			case "tinytext":
			case "text":
			case "mediumtext":
			case "longtext":
			case "binary":
			case "varbinary":
			case "tinyblob":
			case "blob":
			case "mediumblob":
			case "longblob":
				return True;
				break;
			default:
				return False;
		}
	}
	
	/**
	* @access private
	*/
	function openFile($filename) {
		$file = FALSE;
		if ( $this->compress )
			$file = @gzopen($filename, "w9");
		else
			$file = @fopen($filename, "w");
		return $file;
	}

  /**
	* @access private
	*/
	function saveToFile($file, $data) {
		if ( $this->compress )
			@gzwrite($file, $data);
		else
			@fwrite($file, $data);
		$this->isWritten = TRUE;
	}

  /**
	* @access private
	*/
	function closeFile($file) {
		if ( $this->compress )
			@gzclose($file);
		else
			@fclose($file);
	}
}
?>
