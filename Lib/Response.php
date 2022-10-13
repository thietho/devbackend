<?php

namespace Lib;

class Response
{
    public $result;

    public function redirect($url)
    {
        header('Location: ' . $url);
    }

    public function jsonOutput($data)
    {
        if (is_string($data)) {
            $this->jsonResult($data);
        } else {
            header('Content-Type: application/json');
            $this->result = json_encode($data,JSON_UNESCAPED_UNICODE);
            echo $this->result;
        }

    }

    private function jsonResult($data)
    {
        //header('Content-Type: application/json');
        echo $data;
    }

    public function htmlOutput($content)
    {
        $html = <<<EOD
<!DOCTYPE html>
<html>
<head>
<title>Page Title</title>
<meta charset="UTF-8">
</head>
<body>
$content
</body>
</html>
EOD;
        echo $html;
    }

    public function pdfOutput($content)
    {
// create new PDF document
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
        $pdf->setCreator(PDF_CREATOR);
        $pdf->setAuthor('FinViet');
        $pdf->setTitle('PDF Viewer');
        $pdf->setSubject('PDF Viewer');
        $pdf->setKeywords('PDF Viewer');

// set default header data
        //$pdf->setHeaderData(RESOUREC.'assets/images/logofv.png', 0, 'HỢP ĐỒNG ƯỚNG LƯƠNG', 'by FINVIET', array(0,64,255), array(0,64,128));
        //$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
        $pdf->setHeaderFont(Array('dejavusans', '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
        //$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
        //$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->setFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
        $pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


// ---------------------------------------------------------

// set default font subsetting mode
        //$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
        $pdf->setFont('dejavusans', '', 14, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
        $pdf->AddPage();

// set text shadow effect
        //$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

// Set some content to print
        $html = $content;

// Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, false, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
        ob_end_clean();
        $pdf->Output('Contract.pdf', 'I');
    }
}
