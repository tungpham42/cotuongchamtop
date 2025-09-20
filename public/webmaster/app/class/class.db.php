<?php
class DB
{
    private static $ins;

    private $mysqli;

    private $tbl_prefix = "";

    private function __construct()
    {
        $conf = ConfigFactory::load("app");
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->mysqli = new mysqli($conf['db_host'], $conf['db_username'], $conf['db_passwd'], $conf['db_name'], $conf['db_port'], $conf['db_default_socket']);
        $this->mysqli->set_charset($conf['db_charset']);
        $this->tbl_prefix = $conf['db_table_prefix'];
    }

    private function __clone() {}

    public static function ins()
    {
        if(empty(self::$ins)) {
            self::$ins = new self;
        }
        return self::$ins;
    }

    public function get_result($sql, array $params = array())
    {
        return $this->execute($sql, $params)->get_result();
    }

    public function last_insert_id()
    {
        return $this->mysqli->insert_id;
    }

    public function affected_rows()
    {
        return $this->mysqli->affected_rows;
    }

    public function execute($sql, array $params = array()) {
        $stmt = $this->prepare_statement($sql, $params);
        if(!$stmt->execute()) {
            trigger_error("Unable to execute SQL statement: ${sql}", E_USER_ERROR);
        }
        return $stmt;
    }

    private function prepare_statement($sql, array $params = array())
    {
        $types = "";
        $vars = [];

        $statements = array();
        foreach (array_keys($params) as $bind) {
            $pos = stripos($sql, $bind);
            if(false === $pos) {
                trigger_error("Unable to find bind [{$bind}] in SQL ${sql}", E_USER_ERROR);
            }
            $statements[$pos] = $bind;
        }
        ksort($statements);

        foreach ($statements as $statement) {
            $value = $params[$statement];

            if(is_array($value)) {
                $value = array_values($value);
                $types .= $this->array_to_bind_type($value);
                $vars = array_merge($vars, $value);
                $sql = strtr($sql, [$statement => $this->array_to_statement($value)]);
            } else {
                $types .= $this->get_bind_type($value);
                $vars[] = $value;
                $sql = strtr($sql, [$statement=>"?"]);
            }
        }

        $stmt = $this->mysqli->prepare($sql);

        if(!$stmt) {
            trigger_error("Unable to prepare SQL statement: ${sql}", E_USER_ERROR);
        }
        if(!empty($types) AND !empty($vars)) {
            $stmt->bind_param($types, ...$vars);
        }

        //var_dump($vars);
        //var_dump($sql);
        //var_dump($types);
        //var_dump($params);

        return $stmt;
    }

    private function get_bind_type($param)
    {
        switch (gettype($param)) {
            case "string":
                return "s";
            case "integer":
                return "i";
            case "double":
                return "d";
            default:
                return "b";
        }
    }

    private function array_to_statement(array $array = array())
    {
        return implode(", ", array_fill(0, count($array), "?"));
    }

    private function array_to_bind_type(array $array = array())
    {
        $types = "";
        foreach ($array as $value) {
            $types .= $this->get_bind_type($value);
        }
        return $types;
    }

    private function escape_column($column)
    {
        return "`$column`";
    }

    private function escape_columns(& $columns)
    {
        foreach ($columns as & $column) {
            $column = $this->escape_column($column);
        }
    }

    public function queryAll($sql, array $params = array())
    {
        $data = array();
        $result = $this->get_result($sql, $params);
        if($result->num_rows === 0) {
            return $data;
        }
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    public function queryScalar($sql, array $params = array())
    {
        $result = $this->get_result($sql, $params);
        $row = $result->fetch_row();
        return is_array($row) ? (int) $row[0] : 0;
    }

    public function queryRow($sql, array $params = array())
    {
        $data = array();
        $result = $this->get_result($sql, $params);
        if($result->num_rows === 0) {
            return $data;
        }
        return $result->fetch_assoc();
    }

    public function insert($tbl, array $data)
    {
        $fields = array_keys($data);
        $this->escape_columns($fields);

        $sql = "INSERT INTO {$this->table($tbl)} (".implode(",", $fields).") VALUES (:values)";

        $stmt = $this->execute($sql, array(
            ":values"=>$data
        ));

        return $stmt->insert_id;
    }

    public function batch_insert($tbl, array $fields, array $batches)
    {
        if(empty($batches)) {
            return 0;
        }
        $this->escape_columns($fields);
        $inc = 0;
        $rows = [];
        $rows_str = "";
        foreach ($batches as $row) {
            $key = ":row$inc";
            $rows[$key] = & $row;
            $rows_str .= "($key),";
            $inc++;
        }

        $rows_str = substr($rows_str, 0, -1);
        $sql = "INSERT INTO {$this->escape_column($this->tbl_prefix($tbl))} (".implode(",", $fields).") VALUES $rows_str";

        $stmt = $this->execute($sql, $rows);

        //var_dump($sql);
        //var_dump($rows_str);
        //var_dump($tbl);
        //var_dump($fields);
        //var_dump($rows);
        //var_dump($batches);

        return $stmt->affected_rows;
    }

    public function begin_transaction()
    {
        $this->mysqli->begin_transaction();
    }

    public function commit()
    {
        $this->mysqli->commit();
    }

    public function rollback()
    {
        $this->mysqli->rollback();
    }

    public function update($tbl, array $data, $condition = "", array $params = array())
    {
        $i = 0;
        $fields = "";
        foreach ($data as $column_name => $value) {
            $bind_param = ":{$i}_bind_param";
            $fields .= "{$this->escape_column($column_name)}={$bind_param},";
            $params[$bind_param] = $value;
            $i++;
        }
        $fields = substr($fields, 0, -1);

        $sql = "UPDATE {$this->table($tbl)} SET {$fields}";
        if(!empty($condition)) {
            $sql .= " WHERE {$condition}";
        }
        $stmt = $this->execute($sql, $params);
        return $stmt->affected_rows;
    }

    public function delete($tbl, $condition = "", array $params = array())
    {
        $sql = "DELETE FROM {$this->table($tbl)}";
        if(!empty($condition)) {
            $sql .= " WHERE {$condition}";
        }
        $stmt = $this->execute($sql, $params);
        return $stmt->affected_rows;
    }

    public function tbl_prefix($tbl) {
        return "{$this->tbl_prefix}{$tbl}";
    }

    public function table($tbl) {
        return $this->escape_column($this->tbl_prefix($tbl));
    }
}