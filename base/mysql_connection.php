<?php

require_once("define.php");

class MySQLConnection {

    const ERROR_MODE_PASSIVE = 1;
    const ERROR_MODE_ACTIVE = 2;    // Errors only
    const ERROR_MODE_STRICT = 3;    // Errors and warnings

	public $host, $database, $mysql_connection;
    public $error_mode;

	function __construct ($host, $database, $username, $password) {
		$this->host = $host;
		$this->database = $database;
		$this->mysql_connection = mysql_connect($host, $username, $password);
		$this->query("USE {$database}");

        $this->error_mode = self::ERROR_MODE_PASSIVE; // self::ERROR_MODE_ACTIVE;	//
		if ($this->get_error() != "" && strpos($_SERVER['PHP_SELF'], "database_init") !== false) {
			echo $this->get_error();
		}
	}

	/* params: (id) or (where, limit) */
/*
	function __call ($function_name, $params) {
		if (count($params) == 1)
			return $this->get($function_name, "WHERE 
	}
*/

	function average ($table_name, $field_name, $where="") {
		return $this->get_field($table_name, "AVG($field_name)", $where);
	}

	function close () {
		mysql_close($this->mysql_connection);
	}

	function commit_transaction () {
		$this->query("COMMIT");
	}

	function commit () {
		$this->commit_transaction();
	}

    function error_check () {
        if ($this->error_mode == self::ERROR_MODE_ACTIVE) {
            $error = mysql_error();
			if ($error) {
				throw new Exception($error);
			}
        } else if ($this->error_mode == self::ERROR_MODE_STRICT) {
            // Check for warnings
            /* if (mysqli_warning_count()) {
                $warning = mysqli_warning
                throw new Exception($warning);
            } */
        }
    }

	function get_affected_rows () {
		return mysql_affected_rows($this->mysql_connection);
	}

	function get_field ($table, $field, $where_order="") {
		$query = "SELECT {$field} FROM {$table} {$where_order} LIMIT 1";
		// echo $query; exit;
		$results = $this->query($query);
		if ($results) {
			$result = mysql_fetch_array($results, MYSQL_NUM);
			return $result[0];
		}
		else return "";
	}

	function get_found_rows () {
		$count_query = "SELECT FOUND_ROWS()";

		$count_results = $this->sql($count_query);
		if ($count_results->has_next()) {
			$row = $count_results->next();
			$num_results = $row['FOUND_ROWS()'];
		}
		else $num_results = 0;
		return $num_results;
	}

	/* $table - table name or full query.*/
	function get_label ($table, $label, $where="", $fields="") {
		if (!$fields) {
			$matches = array();
			preg_match_all("/[a-zA-Z0-9_]+/", $label, $matches);
			$fields = implode(", ", $matches[0]);
		}
		if ($where)
			$entity = $this->get($table, $where, $fields);
		else $entity = $this->get_row($table);

		return $this->get_row_label($entity, $label);
	}

	function get ($table, $where, $fields="*", $order_by="") {
		if ($order_by != "")
			$order_by = "ORDER BY {$order_by}";
		$query = "SELECT {$fields} FROM {$table} {$where} {$order_by} LIMIT 1";
// echo $query;
		return $this->get_row($query);
	}

	function get_random ($table, $where="", $fields="*") {
		return $this->get($table, $where, $fields, "RAND()");
	}

	function get_row ($query) {
		$results = $this->query($query);
		$result = array();
		if ($results) {
			$result = mysql_fetch_array($results, MYSQL_ASSOC);
		}
		return $result;
	}

	function count ($table, $field, $where) {
		$query = "SELECT COUNT($field) FROM {$table}";
		if ($where)
			$query .= " $where";

		$result = $this->query($query);
		if ($result) {
			$result = mysql_fetch_array($result, MYSQL_NUM);
			return $result[0];
		}
		else return 0;
	}

	function max ($table, $field, $where="") {
		return $this->get_field($table, "MAX({$field})", $where);
	}

	function min ($table, $field, $where="") {
		return $this->get_field($table, "MIN({$field})", $where);
	}

	function sum ($table, $field, $where="") {
		return $this->get_field($table, "SUM({$field})", $where);
	}

	function get_distinct ($table, $field, $where="", $limit="", $sort="") {
		$query = "SELECT DISTINCT $field FROM $table";
		if ($where != "")
			$query .= " $where";
		if ($limit != "")
			$query = " LIMIT $limit";
		if ($sort != "")
			$query .= " ORDER BY $sort";

		$sql_results = $this->sql($query);
		$results = array();
		while ($sql_results->has_next()) {
			$row = $sql_results->next();
			$results[$row[$field]] = $row[$field];
		}
		return $results;
	}

	function get_array ($table, $field, $where="") {
		$query = "SELECT {$field} FROM {$table}";
		if ($where != "")
			$query .= " {$where}";

		$sql_results = $this->sql($query);

		$results = array();
		while ($sql_results->has_next()) {
			$row = $sql_results->next();
			$results[] = $row[$field];
		}
		return $results;
	}

	function get_row_label ($row, $label, $suffix="") {
		if (!is_array($label)) {
			$delimiters = "/([^a-zA-Z0-9_]+)/";
			if (preg_match($delimiters, $label)) {
				$label_parts = preg_split($delimiters, $label, -1, PREG_SPLIT_DELIM_CAPTURE);
				return $this->get_row_label($row, $label_parts);
			}
			else {
				return $row["{$label}{$suffix}"];
			}
		}
		$value = "";
		foreach ($label AS $field_delim) {
// echo "$field_delim => {$row[$field_delim]}<br/>";
			if (isset($row[$field_delim]) && preg_match("/[a-zA-Z0-9_]+/", $field_delim)) {
				$value .= $row["{$field_delim}{$suffix}"];
			}
			else $value .= $field_delim;
		}
		return $value;
	}

	function get_associative ($table, $key, $value, $where="", $limit="", $sort="", $empty_slot="") {
		$delimiters = "/([^a-zA-Z0-9_]+)/";	// /([ ,:])/";		// "/[ ,\/-:]/";
		if (preg_match("{$delimiters}", $value)) {
			$values = preg_split("{$delimiters}", $value, -1, PREG_SPLIT_DELIM_CAPTURE);
			$value = "";
			$i = 0;
			foreach ($values AS $field_delim) {
				if (preg_match("/[a-zA-Z_]+/", $field_delim)) {
					if ($value != "")
						$value .= ", ";
					$value .= $field_delim;
				}
			}
		}
		$query = "SELECT $key, $value FROM $table";
		if ($where != "")
			$query .= " $where";
		if ($sort != "")
			$query .= " ORDER BY $sort";
		if ($limit != "")
			$query .= " LIMIT $limit";
		//echo "$query<br/>";
		$sql_results = $this->sql($query);

		$results = array();

		if ($empty_slot != "")
			$results[""] = $empty_slot;

		while ($sql_results->has_next()) {
			$row = $sql_results->next();
			if (count($values) > 0) {
				$value = $this->get_row_label($row, $values);
				$results[$row[$key]] = $value;
			}
			else $results[$row[$key]] = $row[$value];
		}
		return $results;
	}

	function get_error () {
		return mysql_error($this->mysql_connection);
	}

	function get_insert_ID () {
		$query = "SELECT LAST_INSERT_ID()";
		$result = mysql_query($query, $this->mysql_connection);
		if ($result) {
			$result = mysql_fetch_array($result, MYSQL_NUM);
			return $result[0];
		}
		else return 0;
		// return mysql_insert_id($this->mysql_connection);
	}

    function prepare ($raw_query) {
        return new PreparedStatement($this, $raw_query);
    }

	function query ($query) {
        // SQLQuery object.
        if (is_object($query)) {
            $query = $query->render();
        }
		$result = mysql_query($query, $this->mysql_connection);
		$this->error_check();
		return $result;
	}

	// The same as query(), but returns affected rows.
	function update ($query) {
		$this->query($query);
		return $this->get_affected_rows();
	}

	function lock_rows ($table_name, $where, $fields="*") {
		$query = "SELECT {$fields} FROM {$table_name} {$where} FOR UPDATE";
		return $this->sql($query);
	}

	function lock_rows_advanced ($query) {
		$query .= " FOR UPDATE";
		return $this->sql($query);
	}

	function insert ($query) {
		$this->query($query);
		return $this->get_insert_ID();
	}

	function rollback_transaction () {
		$this->query("ROLLBACK");
	}

	function read_lock ($table_name, $local="") {
		if (is_array($table_name)) {
			$tables = implode(" READ, ", $table_name);
			$this->query("LOCK TABLES {$tables} READ {$local}");
		}
		else $this->query("LOCK TABLE {$table_name} READ {$local}");
	}

	function rollback () {
		$this->rollback_transaction();
	}

	// Get the number of rows affected by an update query.
	function row_count () {
		$result = $this->sql("SELECT ROW_COUNT()");
		$result = $result->next();
		return $result['ROW_COUNT()'];
	}

    function set_error_mode ($new_error_mode) {
        $this->error_mode = $new_error_mode;
    }

	function sql ($query) {
		$results = $this->query($query);
		$sql_result = new SQLResult($results);
		return $sql_result;
	}

	function start_transaction () {
		$this->query("START_TRANSACTION");
	}

	function start () {
		$this->start_transaction();
	}

	function unlock () {
		$this->query("UNLOCK TABLES");
	}

	function write_lock ($table_name, $low_priority="") {
		if (is_array($table_name)) {
			$tables = implode(" WRITE, ", $table_name);
			$this->query("LOCK TABLES {$tables} WRITE");
		}
		else $this->query("LOCK TABLES {$table_name} {$low_priority} WRITE");
	}

}

/* class SQLQuery {

	protected $fields;
	protected $table;
	protected $joins;
	protected $where;
	protected $group_by;
	protected $order_by;
	protected $limit, $offset;

	function __construct ($fields, $table, $joins, $where) {
		$this->fields = $fields;
		$this->table = $table;
		$this->joins = $joins;
		$this->where = $where;
	}

	function add_condition ($where) {
		$this->where .= " " . $where;
	}

	function add_fields ($fields) {
		if ($this->fields && substr($this->fields, -1) != ",") {
			$this->fields .= ", ";
		}
		$this->fields .= $fields;
	}

	function order_by ($order_by) {
		$this->order_by = $order_by;
	}

	function set_fields ($fields) {
		$this->fields = $fields;
	}

	function where ($where) {
		$this->where = $where;
	}

	function compile () {
		$query = "SELECT {$this->fields} FROM {$this->table} {$this->joins} {$this->where}";

		if ($this->group_by) {
			$query .= " GROUP BY " . $this->group_by;
		}

		if ($this->limit) {
			$query .= " LIMIT " . $this->limit;
		}

		return $query;
	}
} */

class SQLResult implements Iterator {

	public $results;
	public $num_rows, $current_row;
	public $next_row;	// Look-ahead

	function __construct ($results) {
		$this->results = $results;
		if ($results)
			$this->num_rows = mysql_num_rows($results);
		else $this->num_rows = 0;
		$this->current_row = 0;
	}

    /* Iterator functionality */
    function current () {
        return $this->peek();
    }

    function key () {
        return $this->current_row;
    }

    function rewind () {
        $this->reset();
    }

    function valid () {
        return $this->has_next();
    }

	function flatten ($fields="") {
		if (strpos($fields, ",") !== FALSE) {
			$fields = preg_split("/[\s]*[,][\s]*/", $fields);
		}

		$results = array();
		while ($this->has_next()) {
			$row = $this->next();

			if ($fields == "") {
				if (count($row) == 1) {
					$results[] = current($row);
				}
				else $results[] = $row;
			}
			else if (is_array($fields)) {
				$row_array = array();
				foreach ($fields AS $field)
					$row_array[$field] = $row[$field];

				$results[] = $row_array;
			}
			else {
				$results[] = $row[$fields];
			}
		}
		return $results;
	}

	function has_next () {
		return $this->has_more_results();
	}

	function has_more_results () {
		return ($this->current_row < $this->num_rows);
	}

	function next ($array_type=MYSQL_ASSOC) {
		if (!$this->has_more_results())
			return array();

		if ($this->next_row != "") {	// Use lookahead, if available
			$row = $this->next_row;
			$this->next_row = "";
		}
		else $row = mysql_fetch_array($this->results, $array_type);

		$this->current_row++;
		return $row;
	}

	function peek ($array_type=MYSQL_ASSOC) {
		if ($this->next_row != "") {
			return $this->next_row;
		}
		else if (!$this->results) {
			return array();
		}

		$this->next_row = mysql_fetch_array($this->results, $array_type);
		return $this->next_row;
	}

	function reset ($position=0) {
		$this->current_row = $position;
        if ($this->results && $this->num_rows > 0) {
            mysql_data_seek($this->results, $position);
        }
	}

}

class PreparedStatement {

    protected $mysql;
    protected $raw_query;
    protected $bound_values;
    protected $rendered_query;

    function __construct (MySQLConnection $mysql, $raw_query) {
        $this->mysql = $mysql;
        $this->raw_query = $raw_query;
        $this->bound_values = array();
    }

    function bind ($value, $tag="%s") {
        return $this->bind_string($value, $tag);
    }

	// array("tag" => $value)
	function bind_all (array $values) {
		foreach ($values AS $tag => $value) {
			$this->bind($value, $tag);
		}

		return $this;
	}

    protected function bind_value ($value, $tag) {
        // Only allow multiple binding for %d, %s, etc. Overwrite named tags.
        if ($tag[0] == "%") {
            if (isset($this->bound_values[$tag])) {
                if (!is_array($this->bound_values[$tag])) {
                    $this->bound_values[$tag] = array($this->bound_values[$tag]);
                }
                $this->bound_values[$tag][] = $value;
            }
            else {
                $this->bound_values[$tag] = $value;
            }
        }
        else {
            // Require named bound values to be wrapped in '{' and '}'
            $tag = "{" . $tag . "}";
            $this->bound_values[$tag] = $value;
        }

        return $this;
    }

	function bind_IDs (array $IDs, $tag="%s") {
		$ID_string = implode(",", $IDs);
		return $this->bind_value($ID_string, $tag);
	}

	function bind_int ($value, $tag="%d") {
        return $this->bind_value(intval($value), $tag);
    }

    function bind_float ($value, $tag="%f") {
        return $this->bind_value(floatval($value), $tag);
    }

    function bind_string ($value, $tag="%s") {
        return $this->bind_value(mysql_real_escape_string($value), $tag);
    }

    function output () {
        echo $this->render();
        return $this;
    }

    function execute () {
        $query = $this->render();
		preg_match("/^[^a-z]*([a-z]+)\b/i", $query, $command);
		$command = $command[1];
        if (!strncasecmp($command, "SELECT", 6)) {
            return $this->mysql->sql($query);
        } else if (!strncasecmp($command, "INSERT", 6)) {
            return $this->mysql->insert($query);
        } else if (!strncasecmp($command, "UPDATE", 6)) {
            return $this->mysql->update($query);
        } else {
            return $this->mysql->query($query);
        }
    }

	function fetch_row () {
		$query = $this->render();
		return $this->mysql->get_row($query);
	}

	function limit ($limit, $page=1) {
		$limit = intval($limit);
		if ($limit) {
			$offset = ($page-1) * $limit;
			$limit_clause = " LIMIT {$offset}, {$limit}";
			$this->raw_query .= $limit_clause;
		}
		return $this;
	}

    // Apply bound values.
    function render () {
        if ($this->rendered_query) {
            return $this->rendered_query;
        }

        $rendered = $this->raw_query;
        // Several values bound to the same tag, e.g., %d, %f, etc.
        foreach ($this->bound_values AS $tag => $value) {
            if (is_array($value)) {
                // Escape '%'
                if (strpos($tag, "%") !== false) {
                    $tag = str_replace("%", "\\%", $tag);
                }

                $regex = "/{$tag}/i";
                foreach ($value AS $a_value) {  // 1: Only replace one %d, %s, etc.
                    $rendered = preg_replace($regex, $a_value, $rendered, 1);
                }
            }
            else {
                $rendered = str_replace($tag, $value, $rendered);
            }
        }
        $this->rendered_query = $rendered;

        return $this->rendered_query;
    }

    function __toString () {
        return $this->render();
    }

}

/* Global values defined in /base/define.php */
$mysql = new MySQLConnection($DATABASE_HOST, $DATABASE_NAME, $DATABASE_USER, $DATABASE_PASSWORD);
$mysql_connection = $mysql;	// Retain legacy name for backwards compatibility.

?>