<?php


namespace pdfjsannotations;

use borales\extensions\phoneInput\PhoneInputAsset;
use common\helpers\FileHelper;
use frontend\assets\AppAsset;
use Yii;
use yii\bootstrap4\BootstrapAsset;
use yii\jui\JuiAsset;
use yii\web\AssetBundle;

class PdfjsAnnotationsAssets extends AssetBundle
{

//    public $basePath = '@webroot';
//    public $baseUrl = '@web';
    public $sourcePath = '@vendor/pdfjsannotations/yii2-pdfjsannotations';

    public $css = [
        'views/site.css',

        'https://fonts.googleapis.com/icon?family=Material+Icons',
        'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css',

        'pdfjsAnnotations/pdfannotate.css',
        'pdfjsAnnotations/styles.css',
    ];

    public $js = [
//        'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js',

        'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.3.0/fabric.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.2.0/jspdf.umd.min.js',
        'https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js',
        'https://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js',

        'views/app.js',
        'pdfjsAnnotations/pdfannotate.js',
        'pdfjsAnnotations/arrow.fabric.js',
        'pdfjsAnnotations/script.js',
        'pdfjs-dist/build/pdf.js',
        'pdfjs-dist/build/pdf.worker.js',
        'pdfjs-dist/web/pdf_viewer.js',
    ];

    public $depends = [
        AppAsset::class,
        \borales\extensions\phoneInput\PhoneInputAsset::class,
        JuiAsset::class,
    ];

    public function init()
    {
        parent::init();
    }

}