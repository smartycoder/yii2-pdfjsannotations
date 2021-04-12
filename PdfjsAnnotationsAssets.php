<?php


namespace smartysoft;

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
    public $sourcePath = '@vendor/smartysoft/yii2-pdfjsannotations';

    public $css = [
        'views/site.css',

        'https://fonts.googleapis.com/icon?family=Material+Icons',
        'assets/materialize.min.css',

        'assets/pdfjsAnnotations/pdfannotate.css',
        'assets/pdfjsAnnotations/styles.css',
    ];

    public $js = [
//        'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js',

        'assets/popper.min.js',
        'assets/fabric.min.js',
        'assets/jspdf.umd.min.js',
        'assets/run_prettify.js',
        'assets/prettify.min.js',
        'assets/materialize.min.js',

        'assets/pdfjsAnnotations/pdfannotate.js',
        'assets/pdfjsAnnotations/arrow.fabric.js',
        'assets/pdfjsAnnotations/script.js',
        'assets/pdfjs-dist/build/pdf.js',
        'assets/pdfjs-dist/build/pdf.worker.js',
        'assets/pdfjs-dist/web/pdf_viewer.js',
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