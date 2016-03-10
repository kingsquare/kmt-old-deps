<?php
if (!defined('FPDF_FONTPATH')) {
	define('FPDF_FONTPATH','font/');
}
require_once __DIR__.'/fpdf/fpdf.php';
require_once __DIR__.'/fpdi/fpdi.php';

class PdfMerger extends fpdi {

    var $files = array();

    function PdfMerger($orientation='P',$unit='mm',$format='A4') {
        parent::fpdi($orientation,$unit,$format);
    }

    function setFiles($files) {
        $this->files = $files;
    }

    function merge() {
        foreach ($this->files AS $file) {
            $pagecount = $this->setSourceFile($file);
            for ($i = 1; $i <= $pagecount; $i++) {
                 $tplidx = $this->ImportPage($i);
                 $this->AddPage();
                 $this->useTemplate($tplidx);
            }
        }
    }

}

