<?php
class LogPlugin {
  private static $path = '/import-logs/';
  private static $local = '/tmp/';
  public static function export($error, $user, $start_time, $type_name, $log, $key, $secret, $bucket){
    try{
      $log_name = 'log-import-'.time().'.log';
      $translate = [
        'error' => [
          'uniqueness' => 'Chave única violada', 
          'notfound' => 'Valor não encontrado'
        ]
      ];

      $handle = fopen(self::$local.$log_name, 'w+');
      
      fwrite($handle, 'Tipo: Importação de '.$type_name . PHP_EOL );
      fwrite($handle, 'Início: ' .$start_time . PHP_EOL );
      fwrite($handle, 'Fim: ' . date('d/m/Y - H:i:s') . PHP_EOL );
      fwrite($handle, 'Status: ' . ($error == 'error' ? 'Erro' : 'Sucesso') . PHP_EOL );
      fwrite($handle, 'Usuário: '. $user->first_name . ' '. $user->last_name. '('.$user->email.')' . PHP_EOL );
      fwrite($handle, 'LOG:' . PHP_EOL);
      
      if($error == 'error'){
        foreach($log as $l){
          $str  = '';
          $str  .= 'Linha ' . str_pad($l['line'], 7, '0', STR_PAD_LEFT) . ': ';
          $t    = explode('.',$l['message']);
          $err  = end($t);
          $str .= $translate['error'][$err] . PHP_EOL;
          fwrite($handle, $str);
        }  
      }else{
        fwrite($handle, 'quantidade de linhas importadas: '.$log->quantity);
      }
            
      fclose($handle);
      
      $file = new FilePlugin($key, $secret, $bucket);
      $f = $file->upload(self::$local.$log_name,self::$path.$log_name);
      if(!$f){
        throw new \RuntimeException('Não foi possível fazer upload do arquivo');
      }

      return $f['ObjectURL'];
      
    }catch(Exception $e){
      throw new $e;
    }
  }
}