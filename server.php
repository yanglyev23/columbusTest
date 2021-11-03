<?php
class DB
{
    private $database;

    public function __construct()
    {
        $databaseConfig = require ('connection.php');
        $this->database = new PDO('mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'], $databaseConfig['login'], $databaseConfig['password']);
    }

    public function query($sql, $params = [])
    {
        $preparedSql = $this->database->prepare($sql);
        $preparedSql->execute($params);
    }
    public function search($table, $id, $params = [])
    {
        return $this->query("SELECT * FROM $table where Code = $id", $params);
    }
    public function read($table, $sql = '', $params = [])
    {
        return $this->query("SELECT * FROM $table" . $sql, $params);
    }
}

function readCSVFile($file='', $delimiter=',')
{
    if(!file_exists($file) || !is_readable($file))
        return FALSE;
    $header = NULL;
    $data = array();
    if (($handle = fopen($file, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 16384, $delimiter)) !== FALSE)
        {
            if(!$header)
                $header = $row;
            else
                $data[] = array_combine($header, $row);
        }
        fclose($handle);
    }
    
    return $data;
}

$tmp_name = $_FILES["inputfile"]["tmp_name"];
$data = readCSVFile($tmp_name);
$report = [];
$db = new DB;
$pattern = '/[^а-яА-Яa-zA-Z0-9.-]+/msiu';
foreach ($data as $key => $value)
{
    $report[$key]['Code'] = $data[$key]['Code'];
    $report[$key]['Name'] = $data[$key]['Name'];
    $report[$key]['Error'] = "";
    if (!preg_match($pattern, $data[$key]['Name'], $matches))
    {
        $params = [
            'id' => $data[$key]['Code'], 
            'name1' => iconv("CP1251", "UTF-8", $data[$key]['Name'])
        ];
        $put = $db->search('list',3000);
        if (empty($put) == 0){
            $db->query("INSERT INTO `list` ( Code, Name1 ) VALUES ( :id, :name1 )", $params);
        }
        else{
            $db->query("UPDATE `list` SET Name1=:name1 WHERE Code=:id", $params);
        }
    }
    else
    {
        $error = "Недопустимый символ ".$matches[0]." в поле Название";
        $report[$key]['Error'] = iconv("UTF-8", "CP1251", $error);
    }

}

$fp = fopen('files/file.csv', 'w');

foreach ($report as $fields) {
    fputcsv($fp, $fields);
}
fclose($fp);
header("Content-disposition: attachment; filename=file.csv");
header("Content-type: application/octet-stream");
header("Content-Description: File Transfer");
readfile("files/file.csv");


