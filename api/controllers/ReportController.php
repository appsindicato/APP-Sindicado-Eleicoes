<?php
/**
* Controller responsável pelos relatórios
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class ReportController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = '';
    /**
    * Allowed methods 
    */
	public static $restricted = array(
        'get' => false,
        'put' => false,
        'post' => false,
        'delete' => false
    );
    /**
    * Allow Cache
    */
	protected $cacheable = false;
    /**
     * Allows search by and partial by fields
     */
    public static $_allow = array(
        'search' => array(),
        'partials' => array()
    );
    /**
    * @method Responsável pelo relatório de apuração por núcleo e geral
    */
    public function report($election_id = null){

        $election_id = 1;

        $request = $this->di->get('request')->getJsonRawBody();
        $city_id = $request->city_id ?? $request->city_id;
        $core_id =  $request->core_id ?? $request->core_id;

        $width = 280;
        $total_col = 0;

        $header = $footer = $pdf = $data_header = $data = [];
        
        $ttfl = 0;
        $de = [];
        $dr = [];
        $cf = [];
        $tdet = $det = 0;
        $tcft = $cft = 0;
        $tdrt = $drt = 0;
        $ttfl= 0;
        $pdf =[];
        /**
        * condições de consulta para o relatório
        * INÍCIO
        */

        $condition = ' election_id = ?2 and plaque_type_id = ?1 and valid = 1 ';
        $bind = [1 => 0, 2 => $election_id];

        $box_condition = ' finish_timestamp is not null ';
        $box_bind = [];

        if($city_id){
            $condition .= ' and core_id in (select core.id from core where core.city_id = ?4) ';
            $bind[4] = $city_id;

            $box_condition .= ' and city_id = ?2 ';
            $box_bind [2] = $city_id;
        }

        if($core_id){
            $condition .= ' and core_id = ?5';
            $bind[5] = $core_id;   

            $box_condition .= ' and core_id = ?3 ';
            $box_bind [3] = $core_id;
        }

        /**
        * condições de consulta para o relatório
        * FIM
        */

       //consulta as chapas 
        $bind[1] = 9;
        $e = Plaque::find([$condition, 'bind' => $bind, 'column' => ['number']], false);
        $e_count = Plaque::count([$condition, 'bind' => $bind]);
        $bind[1] = 7;
        $r = Plaque::find([$condition, 'bind' => $bind, 'column' => ['number']], false);
        $r_count = Plaque::count([$condition, 'bind' => $bind]);
        $bind[1] = 5;
        $c = Plaque::find([$condition, 'bind' => $bind, 'column' => ['number']], false);
        $c_count = Plaque::count([$condition, 'bind' => $bind]);

        //verifica e distribui o tamanho
        $total_col = $e_count + $r_count + $c_count + 5;
        $col_size = $width / $total_col;

        //cabecalho geral
        $e_size = $e_count == 0 ? $col_size : ($e_count + 1) * $col_size;
        $c_size = $c_count == 0 ? $col_size : ($c_count + 1) * $col_size;
        $r_size = $r_count == 0 ? $col_size : ($r_count + 1) * $col_size;
        $header = [
            0 => [
                $col_size, 
                $e_size,
                $c_size, 
                $r_size, 
                $col_size
            ],
            1 => [
                ' ', 
                utf8_decode('DIREÇÃO ESTADUAL'), 
                utf8_decode('CONSELHO FISCAL'), 
                utf8_decode('DIREÇÃO REGIONAL'), 
                utf8_decode('GERAL')
            ]
        ];
        // var_dump($total_col, $width, $col_size, $header); die();

        //cabecalho da tabela
        $data_header = [
            0 => array_fill(0, $total_col, $col_size),
            1 => ['URNA']
        ];

        //escreve as chapas
        foreach($e as $item){
            $data_header[1][] = $item->number;
        }
        $data_header[1][] = 'TOT';

        foreach($c as $item){
            $data_header[1][] = $item->number;
        }
        $data_header[1][] = 'TOT';

        foreach($r as $item){
            $data_header[1][] = $item->number;
        }
        $data_header[1][] = 'TOT';
        $data_header[1][] = 'TOTAL';       

        //consultando urnas
        $box = Box::find([$box_condition, 'bind' => $box_bind], false);

        foreach($box as $b){
            /*
            * t = total
            * de = diretoria estadual
            * cf = conselho fiscal
            * dr = diretoria regional
            * b = branco
            * n = nulo
            * tfl = total final linha
            * EX t + de + numero = total diretoria estadual 1 [nr da chapa]
            */
            
            $det = 0;
            $cft = 0;
            $drt = 0;
            $tfl = 0;
            $pdf[$b->id][] = $b->id;

            error_log(json_encode($pdf));

            //votos
            foreach($e as $k => $dd){
                $ve = Vote::count(['election_id = ?1 and plaque_id = ?2 and box_id = ?3', 'bind' => [1 => $election_id, 2=>$dd->id, 3=> $b->id]]); 
                // var_dump($ve, 'election_id = ?1 and plaque_id = ?2 and box_id = ?3', [1 => $election_id, 2=>$dd->id, 3=> $b->id]);
                //total do conjunto Dir Estad.
                $det += $ve;
                //escreve coluna
                $pdf[$b->id][] = $ve;
                //soma da ultima linha
                $de[$k] += $ve;
            }

           // die();
            // escreve total do cons. fisc.
            $pdf[$b->id][] = $det;
            //total da dir. Estad. (ultima linha)
            $tdet += $det;
            
            //votos
            foreach($c as $k => $cc){
                $vc = Vote::count(['election_id = ?1 and plaque_id = ?2 and box_id = ?3', 'bind' => [1 => $election_id, 2=>$cc->id, 3=> $b->id]]);
                //total do conjunto Cons. Fisc.
                $cft += $vc;
                //escreve coluna
                $pdf[$b->id][] = $vc;
                //soma ultima linha
                $cf[$k] += $vc;
            }
            // escreve total do cons. fisc.
            $pdf[$b->id][] = $cft;
            //total do cons. fiscal (ultima linha)
            $tcft += $cft;

            //votos
            foreach($r as $k => $rr){
                $vr = Vote::count(['election_id = ?1 and plaque_id = ?2 and box_id = ?3', 'bind' => [1 => $election_id, 2=>$rr->id, 3=> $b->id]]);
                // var_dump($rr->toArray(), $rr->number, $election_id, $b->id, $vr);die();
                //total do conjunto Dir. Reg.
                $drt += $vr;
                //escreve coluna
                $pdf[$b->id][] = $vr;
                //soma ultima linha
                $dr[$k] += $vr;
            }
            // escreve total do dir. Reg.
            $pdf[$b->id][] = $drt;
            //total da  dir. reg. (ultima linha)
            $tdrt += $drt;

            //total da linha
            $tfl  = $drt + $det + $cft;

            //escreve total da linha
            $pdf[$b->id][] = $tfl;

            //total da linha (ultima linha)
            $ttfl +=  $tfl;   


        }
        $report_name = '';
        $report_name .= ' RELATÓRIO NÚCLEO - '. Core::findFirst($core_id)->name;

        if($city_id){
            $report_name .= ' - CIDADE - '. City::findFirst($city_id)->name;
        }
        $report_name = utf8_decode($report_name);
        $title = [
             0 => $width,
             1 => $report_name
        ];

        // var_dump($title, $header);die();
        $data = [
            0 => array_fill(0, $total_col, $col_size),
            1 => $pdf
        ];

        $footer = [
            0 => array_fill(0, $total_col, $col_size),
            1 => ['TOTAL']   
        ];

        //escreve total das diretorias estaduais das linhas
        foreach($de as $tdefl){
            $footer[1][] = $tdefl;    
        }
        $footer[1][] = $tdet;

        //escreve total dos conselhos fiscais das linhas
        foreach($cf as $tcffl){
            $footer[1][] = $tcffl;    
        }
        $footer[1][] = $tcft;

        //escreve total das diretorias regionais das linhas
        foreach($dr as $tdrfl){
            $footer[1][] = $tdrfl;    
        }
        $footer[1][] = $tdrt;

        //escreve total final das linhas
        $footer[1][] = $ttfl;
                
        $this->response
            ->setStatusCode(200, 'Accepted')
            ->setContentType('application/pdf')
            ->setHeader("Content-Disposition","attachment")
            ->setContent(PdfPlugin::voteReport($title, $header, $data_header, $data, $footer)); 
    }
    /**
    * @method Responsável por gerar o relatório de representantes de municipio
    */
    public function adviser($city_id = null){
        $election_id = 1;

        $request = $this->di->get('request')->getJsonRawBody();
        $city_id = $request->city_id ?? $request->city_id;

        $width = 190;
        $total_col = 2;
        $col_size = $width / $total_col;

        if(!$city_id)
            return ResponseHandler::post($this, null, null, ['error' => 'controller.report.city_id.notfound']);

        $city = City::findFirst($city_id, false);
        $title = [ 
            0 => $width,
            1 => " REPRESENTANTES MUNICIPAIS" 
        ];

        $header = [
            0 => [150, 40],
            1 => ['REPRESENTANTE', 'VOTOS']
        ];
        $data_header = null;
        
        $footer = null;

        $box = Box::find([' city_id =?1', 'bind' => [1 => $city_id]], false);

        $data = [
            0 => [150, 40],
        ];

        
        foreach($box as $b){
            $votes = Vote::find(['box_id = ?2 and plaque_id in (select p.id from plaque p where p.plaque_type_id = 6 and p.city_id = ?1) ', 'bind' => [1 => $city_id, 2 => $b->id]], false);
            foreach($votes as $v){
                $votes_result[$v->plaque_id]++;
            }
        }

        foreach($votes_result as $plaque_id => $content){
            $data[1][$plaque_id] = [
                Plaque::findFirst($plaque_id, false)->name,
                $votes_result[$plaque_id]
                ];
        }

        $this->response
            ->setStatusCode(200, 'Accepted')
            ->setContentType('application/pdf')
            ->setHeader("Content-Disposition","attachment")
            ->setContent(PdfPlugin::voteReport($title, $header, $data_header, $data, $footer, 'P')); 
        
    }
}