<?php
class ImportPlugin {
  protected static $delimiter = ',';
  private static $path = 'import-files/';

  public static function import($filename, $key, $secret, $bucket){
    try{
      //aws import file
      $s3 = new FilePlugin($key, $secret, $bucket);
      $get = $s3->get(self::$path.$filename);
      //file content
      $f_aws = (string) $get['Body'];
      
      if(!strlen($f_aws)){
        return (object)['quantity' => 0, 'content' => 'controller.import.aws.empty'];
      }

      $path = '/tmp/'.time().'_'.$filename;
      
      $handle = fopen($path, 'w+');

      if(!(fwrite($handle, trim($f_aws)) && fclose($handle))){
        return (object)['quantity' => 0, 'content' => 'controller.import.aws.issue'];
      }

      if(!is_readable($path)){
        return (object)['quantity' => 0, 'content' => 'controller.import.notredable'];
      }

      $r = [];
      $i=0;
      $file = new SplFileObject($path);

      if(!$file->valid()){
        return (object)['quantity' => 0, 'content' => 'controller.import.notredable'];
      }

      $first_line =$file->fgets();
      $headers = explode(self::$delimiter, $first_line);
      $headers_quantity = count($headers);
      foreach($headers as $k=>$v){
        $headers[$k] = trim($v);
      }
      
      while($file->valid()){
        if($i > 0){
          $line = $file->fgets();
          $content = explode(self::$delimiter, $line);
          
          if(count($content) !== $headers_quantity && strlen($line)){ //arquivo inconsistente
            return (object)['quantity' => 0, 'content' => ['controller.import.columns.different', $i]];
          }

          $obj = new stdClass();
          foreach($headers as $ph => $h){
            //error_log($ph . '=>' . $h . '  => '. json_encode($content));
            $obj->$h = trim($content[$ph]);
          }
          $r[] = $obj;
        }
        $i++;
      }
      return (object)['quantity' => $i - 1, 'content' => $r ];
    }catch(Exception $e){
      return ['quantity' => 0, 'content'=>['controller.import.error']];
    }
  }
}