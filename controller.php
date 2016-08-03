<?php
namespace Concrete\Package\ArtesianProductSlider;

use Package;
use BlockType;

defined('C5_EXECUTE') or die('Access Denied');

class Controller extends Package {

    protected $pkgHandle = 'artesian_product_slider';
    protected $appVersionRequired = '5.7.2';
    protected $pkgVersion = '0.5.1';

    public function getPackageName(){
        return t('Artesian Product Slider');
    }

    public function getPackageDescription(){
        return t('Product slider/carousel block for Vivid Store');
    }

    public function install(){
        $pkg = parent::install();
        BlockType::installBlockTypeFromPackage('artesian_product_slider', $pkg);
    }

    public function upgrade(){
        $pkg = $this->getByID($this->getPackageID());
        parent::upgrade();
    }

    public function on_start()
    {
        $al = \Concrete\Core\Asset\AssetList::getInstance();
        $al->register('javascript', 'flexslider', 'js/jquery.flexslider.js', array('version' => '2.6.1', 'minify' => false, 'combine' => false), $this
        );
        $al->register('css', 'flexslider', 'css/flexslider.css', array('version' => '2.6.1', 'minify' => true, 'combine' => true), $this
        );
    }
}
