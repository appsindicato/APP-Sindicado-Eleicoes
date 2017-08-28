<?php

function sqlite_escape_string( $string ){
    return SQLite3::escapeString($string);
}

require('plugins/FilePlugin.php');

echo "Conectando no servidor mysql...\n";
$readConnection = new PDO('1','2','3');

echo "Conectando no sqlite...\n";
$writeConnection = new PDO('sqlite:baseUrna.sqlite');


$prepareInsert = '';

echo "Criando tabela eleitor...\n";
$writeConnection->exec("CREATE TABLE client_eleitor
      (
        id INTEGER PRIMARY KEY,
        nome TEXT,
        id_nucleo INTEGER,
        nome_nucleo TEXT,
        id_cidade INTEGER,
        nome_cidade TEXT,
        rg TEXT,
        flag_transito INTEGER,
        flag_situacao INTEGER,
        valid INTEGER,
        finish_code TEXT
      )");


echo "Fetch tabela eleitor...\n";
$stmt = $readConnection->query("SELECT `v`.`id` AS `id`,`v`.`name` AS `nome`,`v`.`core_id` AS `id_nucleo`, `c`.`name` AS `nome_nucleo`, `v`.`city_id` AS `id_cidade`, `cc`.`name` AS `nome_cidade`, `v`.`document` AS `rg`, `v`.`transit` AS `flag_transito`, `v`.`status` AS `flag_situacao`, 12 AS `id_finalizacao`, `v`.`valid` AS `valid`, `v`.`finish_code` AS `finish_code` FROM ((`voter` `v` JOIN `city` `cc` ON ((`cc`.`id` = `v`.`city_id`))) JOIN `core` `c` ON ((`c`.`id` = `v`.`core_id`)));");
if ($stmt){
      echo "Preparando insert eleitor...\n";
      $prepareInsert='BEGIN TRANSACTION;';
      foreach ($stmt as $row){
            $row['nome'] = sqlite_escape_string($row['nome']);
            $row['nome_nucleo'] = sqlite_escape_string($row['nome_nucleo']);
            $row['nome_cidade'] = sqlite_escape_string($row['nome_cidade']);
            $prepareInsert .= "INSERT INTO client_eleitor ('id','nome','id_nucleo','nome_nucleo','id_cidade','nome_cidade','rg','flag_transito','flag_situacao','valid','finish_code') VALUES ($row[id],'$row[nome]','$row[id_nucleo]','$row[nome_nucleo]','$row[id_cidade]','$row[nome_cidade]','$row[rg]','$row[flag_transito]','$row[flag_situacao]','$row[valid]','$row[finish_code]');";
      }
      $prepareInsert .='COMMIT;';
}

echo "Executando insert eleitor...\n";
$writeConnection->exec($prepareInsert);

$prepareInsert = '';

echo "Criando tabela chapa...\n";
$writeConnection->exec("CREATE TABLE client_chapa
      (
        id INTEGER PRIMARY KEY,
        nome TEXT,
        id_tipo INTEGER,
        nome_tipo TEXT,
        id_nucleo INTEGER,
        nome_nucleo TEXT,
        id_cidade INTEGER,
        nome_cidade TEXT,
        numero_chapa INTEGER
      )");

echo "Fetch tabela chapa...\n";
$stmt = $readConnection->query('SELECT 
       `p`.`id` AS `id`,
       `p`.`name` AS `nome`,
       `p`.`plaque_type_id` AS `id_tipo`,
       `pt`.`name` AS `nome_tipo`,
       `p`.`core_id` AS `id_nucleo`,
       `c`.`name` AS `nome_nucleo`,
       `p`.`city_id` AS `id_cidade`,
       `cc`.`name` AS `nome_cidade`,
       `p`.`number` AS `numero_chapa`
   FROM
       (((`plaque` `p`
       JOIN `plaque_type` `pt` ON ((`pt`.`id` = `p`.`plaque_type_id`)))
       LEFT JOIN `core` `c` ON ((`p`.`core_id` = `c`.`id`)))
       LEFT JOIN `city` `cc` ON ((`cc`.`id` = `p`.`city_id`)))');
if ($stmt){
      echo "Preparando insert chapa...\n";
      $prepareInsert='BEGIN TRANSACTION;';
      foreach ($stmt as $row){

            $row['nome'] = sqlite_escape_string($row['nome']);
            $row['nome_tipo'] = sqlite_escape_string($row['nome_tipo']);
            $row['nome_nucleo'] = sqlite_escape_string($row['nome_nucleo']);
            $row['nome_cidade'] = sqlite_escape_string($row['nome_cidade']);
            $row['numero_chapa'] = sqlite_escape_string($row['numero_chapa']);
            $prepareInsert .= "INSERT INTO client_chapa ('id','nome','id_tipo','nome_tipo','id_nucleo','nome_nucleo','id_cidade','nome_cidade','numero_chapa') VALUES ($row[id],'$row[nome]','$row[id_tipo]','$row[nome_tipo]','$row[id_nucleo]','$row[nome_nucleo]','$row[id_cidade]','$row[nome_cidade]','$row[numero_chapa]');";
      }
      $prepareInsert.='COMMIT;';
}
echo "Executando insert chapa...\n";
$writeConnection->exec($prepareInsert);



$prepareInsert = '';

echo "Criando tabela urna...\n";
$writeConnection->exec("CREATE TABLE client_urna 
      (  id INTEGER PRIMARY KEY,
        senha_mesario_abertura TEXT,
        senha_diretoria_abertura TEXT,
        senha_mesario_operacao TEXT,
        nome_nucleo TEXT,
        id_nucleo INTEGER,
        id_cidade INTEGER,
        nome_cidade TEXT
      )");

echo "Fetch tabela urna...\n";
$stmt = $readConnection->query("SELECT 
       `b`.`id` AS `id`,
       SHA1(password_open_1) AS `senha_mesario_abertura`,
       SHA1(password_open_2) AS `senha_diretoria_abertura`,
       SHA1(password_operation) AS `senha_mesario_operacao`,
       `c`.`name` AS `nome_nucleo`,
       `c`.`id` AS `id_nucleo`,
       `cc`.`id` AS `id_cidade`,
       `cc`.`name` AS `nome_cidade`
   FROM
       ((`box` `b`
       JOIN `core` `c` ON ((`b`.`core_id` = `c`.`id`)))
       JOIN `city` `cc` ON ((`b`.`city_id` = `cc`.`id`)))");

if ($stmt){
      echo "Preparando insert urna...\n";
      $prepareInsert='BEGIN TRANSACTION;';
      foreach ($stmt as $row){

            $row['nome_nucleo'] = sqlite_escape_string($row['nome_nucleo']);
            $row['nome_cidade'] = sqlite_escape_string($row['nome_cidade']);

            $prepareInsert .= "INSERT INTO client_urna ('id','senha_mesario_abertura','senha_diretoria_abertura','senha_mesario_operacao','nome_nucleo','id_nucleo','id_cidade','nome_cidade') VALUES ($row[id],'$row[senha_mesario_abertura]','$row[senha_diretoria_abertura]','$row[senha_mesario_operacao]','$row[nome_nucleo]','$row[id_nucleo]','$row[id_cidade]','$row[nome_cidade]');";
      }
      $prepareInsert.='COMMIT;';
}
echo "Executando insert urna...\n";
$writeConnection->exec($prepareInsert);

$file = new FilePlugin('','','');
echo "Executando upload...\n";
if (!$file->upload('baseUrna.sqlite','databases/baseUrna.sqlite'))
      throw new Exception("Erro ao fazer upload da base de dados");
echo "Excluindo arquivo local...\n";
unlink('baseUrna.sqlite');

echo "FIM\n";
?>
