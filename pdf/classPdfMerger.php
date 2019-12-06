<?php
if (!defined('FPDF_FONTPATH')) {
	define('FPDF_FONTPATH','font/');
}
require_once __DIR__.'/fpdf/fpdf.php';
require_once __DIR__.'/fpdi/fpdi.php';

class PdfMerger extends fpdi {

    /**
     * @deprecated
     * @var string[]
     */
    var $files = [];

    /**
     * @param string[] $files
     * @param bool $unlinkTheSourceFilesAfterMerge
     * @return \PdfMerger
     */
    public static function fromFilesIntoSinglePdf(array $files, $unlinkTheSourceFilesAfterMerge = true)
    {
        $instance = new self();
        $instance->files = $files;
        $instance->merge();
        if (!$unlinkTheSourceFilesAfterMerge) {
            return $instance;
        }

        foreach ($instance->files as $file) {
            @unlink($file);
        }
        $instance->files = [];
        return $instance;
    }


    /**
     * PdfMerger constructor.
     * @deprecated The public constructor is deprecated, please use the fromFilesIntoSinglePdf factory method
     * @param string $orientation
     * @param string $unit
     * @param string $format
     */
    function __construct($orientation='P',$unit='mm',$format='A4') {
        parent::__construct($orientation,$unit,$format);
    }

    /**
     * @deprecated will be (re)moved  / privatised
     * @param $files
     */
    function setFiles($files) {
        $this->files = $files;
    }

    /**
     * @deprecated will be (re)moved  / privatised
     */
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
