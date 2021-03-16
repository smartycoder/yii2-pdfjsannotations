<?php

namespace pdfjsannotations;

/**
 * This is just an example.
 */
class PdfjsAnnotations extends \yii\base\Widget
{
     public $pdfFilePath;

    public function run()
    {
        return $this->render('index.php', [
            'pdfFilePath' => $this->pdfFilePath
        ]);
    }
}
