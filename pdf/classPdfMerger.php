<?php

if (!defined('FPDF_FONTPATH')) {
    define('FPDF_FONTPATH', 'font/');
}
require_once __DIR__ . '/fpdf/fpdf.php';
require_once __DIR__ . '/fpdi/fpdi.php';

final class PdfMerger extends FPDI
{

    /**
     * @param string[] $files
     * @param bool $unlinkTheSourceFilesAfterMerge
     * @return \PdfMerger
     */
    public static function fromFilesIntoSinglePdf(array $files, $unlinkTheSourceFilesAfterMerge = true)
    {
        $instance = new self();
        foreach ($files as $file) {
            $pagecount = $instance->setSourceFile($file);
            for ($i = 1; $i <= $pagecount; $i++) {
                $tplidx = $instance->ImportPage($i);
                $instance->AddPage();
                $instance->useTemplate($tplidx);
            }
        }
        if ($unlinkTheSourceFilesAfterMerge) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        return $instance;
    }

}
