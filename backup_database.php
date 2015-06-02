<?php

require_once("base/mysql_connection.php");

backup_tables('localhost','username','password','blog');


/* backup the db OR just a table */
function backup_tables ($host, $user, $pass, $name, $tables='*') {

	$link = mysql_connect($host,$user,$pass);
	mysql_select_db($name,$link);
	
	//get all of the tables
	if($tables == '*') {
		$tables = array();
		$result = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($result)) {
			$tables[] = $row[0];
		}
	}
	else {
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}
	
	//cycle through
	foreach ($tables as $table) {
		$result = mysql_query('SELECT * FROM '.$table);
		$num_fields = mysql_num_fields($result);
		
		$database_sql .= 'DROP TABLE '.$table.';';
		$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
		$database_sql .= "\n\n".$row2[1].";\n\n";
		
		for ($i = 0; $i < $num_fields; $i++) {
			while($row = mysql_fetch_row($result)) {
				$database_sql .= 'INSERT INTO ' . $table . ' VALUES(';

				for($j=0; $j<$num_fields; $j++)  {
					$row[$j] = addslashes($row[$j]);
					$row[$j] = ereg_replace("\n","\\n",$row[$j]);

					if (isset($row[$j])) {
						$database_sql .= '"' . $row[$j] . '"';
					}
					else $database_sql .= '""';
					
					if ($j < ($num_fields-1))
						$database_sql .= ',';
				}
				$database_sql .= ");\n";
			}
		}
		$database_sql .="\n\n\n";
	}
	
	//save file
	$handle = fopen('db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
	fwrite($handle, $database_sql);
	fclose($handle);
}

?>