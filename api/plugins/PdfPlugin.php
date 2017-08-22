<?php
require __DIR__ . '/fpdf/fpdf.php';

class PdfPlugin
{
	/**
     * transform a table to pdf
     * @param $data array - the first line is the headers and other lines is the body
     * @return pdf 
     */
	public static function generate($data = null, $w = null, $type = 'P', $font = ['font' => 'Arial', 'size' => 14]){
		// Column headings
		$pdf = new FPDF($type);

		$header = array_slice($data, 0, 1)[0];
		unset($data[0]);

		$pdf->SetFont($font['font'],'',$font['size']);
		$pdf->AddPage();

		$pdf->SetFillColor(255,0,0);
	    $pdf->SetTextColor(255);
	    $pdf->SetDrawColor(128,0,0);
	    $pdf->SetLineWidth(.3);
	    // Header

	    if(!$w){
	    	$t = count($header);
	    	$w = [];
	    	for($i=0;$i<$t;$i++){
	    		$w[] = 190/$t;
	    	}
	    }
	    //header
	    for($i=0;$i<count($header);$i++){
	        $pdf->Cell($w[$i],7,$header[$i],1,0,'C',true);
	    }
	    $pdf->Ln();

	    // Color and font restoration
	    $pdf->SetFillColor(224,235,255);
	    $pdf->SetTextColor(0);
	    $pdf->SetFont('');
	    // Data
	    $fill = false;
	    foreach($data as $row)
	    {
	    	$i = 0;
	    	foreach($row as $r){
	    		$pdf->Cell($w[$i],6,$r,'',0,'C',$fill);	
	    		$i++;
	    	}
	        $pdf->Ln();
	        $fill = !$fill;
	    }
	    $pdf->Ln();
		return $pdf->Output('S');
	}

	/**
     * transform a table to pdf
     * @param $data array - the first line is the headers and other lines is the body
     * @return pdf
     */
	public static function voteReport($title = null, $header = null, $data_header = null, $data = null, $footer = null, $type = 'L'){
		// Column headings
		$pdf = new FPDF($type);

		$pdf->SetFont('Arial','',8);
		$pdf->AddPage();

	    // Header
	    if($title){
	    	$pdf->SetFillColor(255,255,255);
		    $pdf->SetTextColor(0);
		    $pdf->SetDrawColor(0,0,0);
		    $pdf->SetLineWidth(.3);
	        $pdf->Cell($title[0],7,$title[1],1,0,'C',true);
			$pdf->Ln();
	    }

	    if($header){
	    	$pdf->SetFillColor(230,230,230);
		    $pdf->SetTextColor(0);
		    $pdf->SetDrawColor(0,0,0);
		    $pdf->SetLineWidth(.3);
			for($i=0;$i<count($header[0]);$i++){
		        $pdf->Cell($header[0][$i],7,$header[1][$i],1,0,'C',true);
		    }
			$pdf->Ln();
		}

	    if($data_header){
		    for($i=0;$i<count($data_header[0]);$i++){
		    	$pdf->SetFillColor(150,150,150);
			    $pdf->SetTextColor(0);
			    $pdf->SetDrawColor(0,0,0);
			    $pdf->SetLineWidth(.3);
		        $pdf->Cell($data_header[0][$i],7,$data_header[1][$i],1,0,'C',true);
		    }
		    $pdf->Ln();
		}

		if($data){
		    // Color and font restoration
		    $pdf->SetFillColor(224,235,255);
		    $pdf->SetTextColor(0);
		    $pdf->SetDrawColor(25,25,25);
		    $pdf->SetLineWidth(.3);
		    $pdf->SetFont('');
		    // Data
		    $fill = false;
		    foreach($data[1] as $line){
		    	foreach($line as $i => $d){
		    		$pdf->Cell($data[0][$i],7,$d,1,0,'C',$fill);
		    	}
		    	$fill = !$fill;
		        $pdf->Ln();
		    }
		}

	    if($footer){
	    	$pdf->SetFillColor(230,230,230);
		    $pdf->SetTextColor(0);
		    $pdf->SetDrawColor(0,0,0);
		    $pdf->SetLineWidth(.3);
	    	for($i=0;$i<count($footer[0]);$i++){
	        	$pdf->Cell($footer[0][$i],7,$footer[1][$i],1,0,'C',true);
	    	}
		    $pdf->Ln();
	    }

		return $pdf->Output('S');
	}
}


