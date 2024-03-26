<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

// some front

$files_in_folder = glob('Files/*.csv');
if($files_in_folder){
    ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th><b>File</b></th>
                <th>Actions</th>
            </tr>
            <?php
            foreach ($files_in_folder as $file_path){
                ?>
                <tr>
                    <td><a href="<?=$file_path?>" target="_blank"><?=$file_path?></a></td>
                    <td>
                        <a href="run.php?start_file=<?=$file_path?>" target="_blank">Import</a><br/>
                        <a href="run.php?start_file=<?=$file_path?>&truncate_table=1" target="_blank">Truncate and Import</a><br/>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
<?php
}
