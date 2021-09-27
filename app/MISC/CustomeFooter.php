<?php


namespace App\MISC;
use PDF;

class CustomeFooter
{
    public function footer()
    {
        PDF::setFooterCallback(function($pdf) {
            $pdf->SetY(-15);
            $pdf->SetFont('helvetica', 'I', 8);
            $footer_stamp=session('user_id').'/'.time().'/'.session('company_name');
            $pdf->Cell(0,10,$footer_stamp,'',0,'L');
            $pdf->Cell(0,10,'Page '.$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        });
    }
}
