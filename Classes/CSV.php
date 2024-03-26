<?php
class CSV {

    private $_csv_file = null;
    private $_separator = ',';
    private $_errors;

    public $primary_key = null;

    /**
     * @param string $csv_file - путь до csv-файла
     * @param string $separator - разделитель
     * @throws Exception
     */
    public function __construct(string $csv_file, string $separator='') {
        if($separator){
            $this->_separator = $separator;
        }
        if (file_exists($csv_file)) {
            $this->_csv_file = $csv_file;
        }else{
            throw new Exception("Файл ".$csv_file." не найден");
        }
    }

    /**
     * Метод для чтения из csv-файла. Возвращает массив с данными из csv
     * @return array;
     */
    public function readCsvToArray(): array
    {

        $this->_errors = [];

        $handle = fopen($this->_csv_file, "r");

        $data = [];
        $columns = [];
        $row_num = 0;
        while (($row = fgetcsv($handle, 0, $this->_separator)) !== FALSE) {

            if(!($row[0])){
                $row_num++;
                continue;
            }

            if(!$columns){
                if (count(array_unique($row))!=count($row)) {
                    throw new Exception("В CSV обнаружены дублирующиеся столбцы.");
                }
                $columns = $row;
            }else {
                if(count($row)!=count($columns)){
                    $this->_errors[] = 'Not enough data in CSV: '.json_encode($row);
                    continue;
                }
                $row = array_combine($columns,$row);
                if($this->primary_key){
                    $row[$this->primary_key] = intval($row[$this->primary_key]);
                }
                $data[] = $row;
            }
            $row_num++;
        }
        fclose($handle);

        return [$columns, $data, $this->_errors];
    }

}