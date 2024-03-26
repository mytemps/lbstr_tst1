# lbstr_tst1

## 1. Create DB and import .sql

Import to your db: [products.sql](products.sql)

## 2. Check DB connection settings

In [Classes/App.php](Classes/App.php)
```
private $MYSQL_Database = "";
private $MYSQL_Username = "";
private $MYSQL_Password = "";
 ```
   
## 2. Upload .csv files

Upload one or more .csv file to "[Files](Files)" directory

## 3. Test import

**Go to "/" url to test import, or use direct url "/run.php" with GET-variables:**
- start_file={filename} , to set filename from Files folder;
- truncate_table=1 , if you need to truncate table before import.
e.g. /run.php/?start_file=products_test.csv&truncate_table=1

**In the end you'll see results, e.g.**

```
Results
Array
(
    [status] => success
    [results] => Array
        (
            [microtime] => 65.811340808868
            [sql_success] => 33254
            [sql_errors] => 0
        )

    [errors] => Array
        (
        )

)
```
or
```
Array
(
    [status] => warning
    [results] => Array
        (
            [microtime] => 0.015331983566284
            [sql_success] => 14
            [sql_errors] => 0
        )

    [errors] => Array
        (
            [0] => Not enough data in CSV: ["6","",""]
            [1] => Not enough data in CSV: ["1985"," 23432","4"]
            [2] => Not int primary key: {"id":0,"title":"Product 7","price":"6911.57","param2":"","param1":"","not_in_db_param":""}
            [3] => Double primary IGNORED: {"id":1,"title":"p-1-2","price":"33.65","param2":"","param1":"","not_in_db_param":"test1"}
            [4] => Double primary IGNORED: {"id":1,"title":"p-1-3","price":"55.65","param2":"","param1":"","not_in_db_param":"test2"}
            [5] => Double primary IGNORED: {"id":1,"title":"p-1-4","price":"55.65","param2":"","param1":"","not_in_db_param":"test2"}
            [6] => Double primary IGNORED: {"id":8,"title":"Product 8","price":"8898.53","param2":"","param1":"","not_in_db_param":""}
        )

)
```

