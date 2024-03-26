# lbstr_tst1

## 1. Create DB and import .sql
   
## 2. Upload .csv file to /Files directory

## 3. Test import

**Go to "/" url to test import, or use direct url "/run.php" with GET-variables:**
- start_file={filename} , to set filename from Files folder;
- truncate_table=1 , if you need to truncate table before import.
e.g. /run.php/?start_file=products_test.csv&truncate_table=1

