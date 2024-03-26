<?php
class App
{
    private $MYSQL_Server = "localhost";
    private $MYSQL_Database = "t777_db";
    private $MYSQL_Username = "t777_db_usr";
    private $MYSQL_Password = "xHk64dkK4HgHNQUl";
    private $MYSQL_ProductsTableName = "products";

    private $dbh;
    private $dbh_products_primary;
    private $dbh_products_columns;

    public $errors;
    public $truncate_table = false;

    /**
     * @throws Exception
     */
    public function __construct() {
        $this->errors = [];

        try {
            $this->dbh = new PDO("mysql:host=$this->MYSQL_Server;dbname=$this->MYSQL_Database;charset=utf8", "$this->MYSQL_Username", "$this->MYSQL_Password");
        } catch (PDOException $e) {
            throw new Exception($e);
        }

        try {
            $STH = $this->dbh->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$this->MYSQL_ProductsTableName."' ORDER BY ORDINAL_POSITION");
            $STH->execute();
            $this->dbh_products_columns = $STH->fetchAll(PDO::FETCH_COLUMN);
            sort($this->dbh_products_columns);
        } catch (PDOException $e) {
            throw new Exception($e);
        }

        try {
            $STH = $this->dbh->prepare("SHOW KEYS FROM ".$this->MYSQL_ProductsTableName." WHERE Key_name = 'PRIMARY';");
            $STH->execute();
            $this->dbh_products_primary = $STH->fetch(PDO::FETCH_ASSOC)['Column_name'];
        } catch (PDOException $e) {
            throw new Exception($e);
        }

    }

    private function bulkInsertOrUpdate($csv_data): array
    {

        $results = [
            'microtime'=>microtime(true),
            'sql_success'=>0,
            'sql_errors'=>0
        ];

        // making templates
        $update_query = [];
        foreach (array_keys($csv_data[0]) as $k){
            $update_query[] = $k.' = :'.$k;
        }

        if($this->truncate_table) {
            $this->dbh->prepare("TRUNCATE TABLE ".$this->MYSQL_ProductsTableName)->execute();
        }

        $query = "INSERT INTO ".$this->MYSQL_ProductsTableName."
            (".implode(', ',array_keys($csv_data[0])).")
            VALUES  (:".implode(', :',array_keys($csv_data[0])).")
            ON DUPLICATE KEY UPDATE ".implode(', ',$update_query).";";

        $STH = $this->dbh->prepare($query);

        // running sql
        foreach ($csv_data as $row) {

            foreach ($row as $k => $v) {
                $row[':' . $k] = $v;
                unset($row[$k]);
            }

            if (!$STH->execute($row)) {
                $this->errors[] = 'SQL_ERROR: '.json_encode(['data'=>$row,'Statement'=>$STH]);
                $results['sql_errors']++;
            }else{
                $results['sql_success']++;
            }

        }

        $results['microtime'] = microtime(true)-$results['microtime'];

        return $results;

    }

    private function makePrimaryInt($array)
    {
        foreach($array as $k=>$v){
            if(!$v[$this->dbh_products_primary] || !is_numeric($v[$this->dbh_products_primary])){
                $this->errors[] = 'Not int primary key: '.json_encode($v);
                unset($array[$k]);
            }
        }
    }

    private function removePrimaryDoubles(&$array)
    {

        // sorting main array by inside key "id"
        usort($array, function ($item1, $item2) {
            return $item1['id'] <=> $item2['id'];
        });

        $last_primary_val = $array[0][$this->dbh_products_primary]-1;
        foreach($array as $k=>$v){
            if($v[$this->dbh_products_primary] <= $last_primary_val){
                $this->errors[] = 'Double primary IGNORED: '.json_encode($v);
                unset($array[$k]);
            }
            $last_primary_val = $v[$this->dbh_products_primary];
        }

    }

    private function prepareDataForDB(&$array)
    {
        // left only db columns exists in data
        $to_db_columns = array_diff(
            array_keys($array[0]),
            array_diff(
                array_keys($array[0]),
                $this->dbh_products_columns
            ));
        foreach($array as $i=>$row){
            foreach ($row as $k=>$v){
                if(!in_array($k,$to_db_columns)){
                    unset($array[$i][$k]);
                }else{
                    if(!$v){$array[$i][$k]=null;}
                }
            }
        }
    }

    /**
     * Метод импорта в базу данных из csv-файла. Возвращает массив с результатами.
     * @return array
     * @throws Exception
     */
    public function importFromCsv($filename)
    {

        try {
            $csv = new CSV($filename);
        } catch (Exception $e) {
            die($e->getMessage());
        }

        // set DB primary_key to CSV reader
        $csv->primary_key = $this->dbh_products_primary;

        list($csv_columns, $csv_data, $csv_errors) = $csv->readCsvToArray();
        $this->errors = array_merge($this->errors,$csv_errors);

        // check if primary in data
        if (!in_array($this->dbh_products_primary,$csv_columns)) {
            throw new Exception("В CSV нет primary столбца ".$this->dbh_products_primary.".");
        }

        // prepare data
        $this->makePrimaryInt($csv_data);

        // remove duplicates by primary key
        $this->removePrimaryDoubles($csv_data);

        // remove duplicates by primary key
        $this->prepareDataForDB($csv_data);

        $import_results = $this->bulkInsertOrUpdate($csv_data);

        return [
            'status'=>($this->errors?'warning':'success'),
            'results'=> $import_results,
            'errors'=>$this->errors];
    }


}

