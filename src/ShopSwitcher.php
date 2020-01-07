<?php

namespace OxidProfessionalServices\ShopSwitcher;

use IteratorAggregate;

class ShopSwitcher implements IteratorAggregate
{
 
    private $shopList;
   
    public function __construct()
    {
        /** @var \oxShopList $oxShopList */
        $oxShopList = oxNew(\OxidEsales\Eshop\Application\Model\ShopList::class);
        $shopList = $oxShopList->getAll();
    }
   
    public function getIterator()
    {
        return (function () {
            while (list($key, $val) = each($this->shopList)) {
                $shopId = $val->oxshops__oxid->rawValue;
                $this->switchToShopId($shopId);
                yield $key => $shopId;
            }
        })();
    }
   
   /**
     * Completely switch shop
     *
     * @param string $shopId The shop id
     *
     * @return void
     */
    public function switchToShopId($shopId)
    {
        $_GET['shp'] = $shopId;
        $_GET['actshop'] = $shopId;
        
        $keepThese = [\OxidEsales\Eshop\Core\ConfigFile::class];
        $registryKeys = \OxidEsales\Eshop\Core\Registry::getKeys();
        foreach ($registryKeys as $key) {
            if (in_array($key, $keepThese)) {
                continue;
            }
            \OxidEsales\Eshop\Core\Registry::set($key, null);
        }

        $utilsObject = new \OxidEsales\Eshop\Core\UtilsObject();
        $utilsObject->resetInstanceCache();
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\UtilsObject::class, $utilsObject);

        \OxidEsales\Eshop\Core\Module\ModuleVariablesLocator::resetModuleVariables();
        \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('shp', $shopId);

        //ensure we get rid of all instances of config, even the one in Core\Base
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Config::class, null);
        \OxidEsales\Eshop\Core\Registry::getConfig()->setConfig(null);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Config::class, null);

        $moduleVariablesCache = new \OxidEsales\Eshop\Core\FileCache();
        $shopIdCalculator = new \OxidEsales\Eshop\Core\ShopIdCalculator($moduleVariablesCache);

        if (
            ($shopId != $shopIdCalculator->getShopId())
            || ($shopId != \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId())
        ) {
            throw new \Exception(
                'Failed to switch to subshop id ' . $shopId . " Calculate ID: "
                . $shopIdCalculator->getShopId() . " Config ShopId: "
                . \OxidEsales\Eshop\Core\Registry::getConfig()->getShopId()
            );
        }
    }
}
