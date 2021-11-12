<?php
// принимаем переданный из формы файл
$tmp_name = $_FILES["inputfile"]["tmp_name"];
// подключение к базе данных
$databaseConfig = require ('connection.php');
$db = new PDO('mysql:host=' . $databaseConfig['host'] . ';dbname=' . $databaseConfig['dbname'], $databaseConfig['login'], $databaseConfig['password']);
// открываем переданный файл и файл, в котором будем формировать отчёт
$data = fopen($tmp_name, 'r');
$report = fopen('files/file.csv', 'w');
// объявление вспомогательных переменных (массива данных для отчёта и итератора)
$array = [];
$key = 1;
// паттерн для регулярных выражений
$pattern = '/[^а-яА-Яa-zA-Z0-9.-]+/msiu';
// копируем шапку из исходного файла в отчёт
fputcsv($fp, fgetcsv($data));
// цикл, считываем строку из исходного файла, производим проверку, записываем в бд, записываем в файл отчёта
while($row = fgetcsv($data)) {
    // заполняем массив для отчёта данными из исходного файла
    $array[$key]['Code'] = $row[0];
    $array[$key]['Name'] = $row[1];
    $array[$key]['Error'] = "";
    // производим проверку на запрещённые символы
    if (!preg_match($pattern, $row[1], $matches))
    {
        /*$params = [
            'id' => $row[0], 
            'name1' => iconv("CP1251", "UTF-8", $row[1])
        ];
        $put = $db->search('list',3000);
        if (empty($put) == 0){
            $db->query("INSERT INTO `list` ( Code, Name1 ) VALUES ( :id, :name1 )", $params);
        }
        else{
            $db->query("UPDATE `list` SET Name1=:name1 WHERE Code=:id", $params);
        }*/
    }
    else
    {
        // запись ошибки в отчёт
        $error = "Недопустимый символ ".$matches[0]." в поле Название";
        $array[$key]['Error'] = iconv("UTF-8", "CP1251", $error);
    }
    // запись данных для отчёта в файл
    fputcsv($fp, $array[$key]);
    $key++;
}
// закрываем файлы
fclose($fp);
fclose($data);
//автоматическое скачивание файла отчёта
header("Content-disposition: attachment; filename=file.csv");
header("Content-type: application/octet-stream");
header("Content-Description: File Transfer");
readfile("files/file.csv");


