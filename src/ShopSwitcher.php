<?php

declare(strict_types=1);

namespace OxidProfessionalServices\ShopSwitcher;

use IteratorAggregate;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\Eshop\Application\Model\ShopList;
use OxidEsales\Eshop\Application\Model\Shop;

/** @implements IteratorAggregate<string> */
class ShopSwitcher implements IteratorAggregate
{
    /**
     * @var array<string> $shopList
     **/
    private $shopList;
   
    public function __construct()
    {
        $shopListService = oxNew(ShopList::class);
        $shopListService->getAll();
        $this->shopList = [];
        foreach ($shopListService as $shop) {
            $this->shopList[] = $shop->getId();
        }
    }
   
    /**
     * @return \Traversable<string>
     **/
    public function getIterator()
    {
        $shopList = $this->shopList;
        foreach ($shopList as $shopId) {
            $this->switchToShopId($shopId);
            yield $shopId;
        }
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
        $registryKeys = Registry::getKeys();
        foreach ($registryKeys as $key) {
            if (in_array($key, $keepThese)) {
                continue;
            }
            Registry::set($key, null);
        }

        $utilsObject = new UtilsObject();
        $utilsObject->resetInstanceCache();
        Registry::set(UtilsObject::class, $utilsObject);

        \OxidEsales\Eshop\Core\Module\ModuleVariablesLocator::resetModuleVariables();
        Registry::getSession()->setVariable('shp', $shopId);

        //ensure we get rid of all instances of config, even the one in Core\Base
        Registry::set(Config::class, null);
        $config = Registry::getConfig();

        if (method_exists($config, 'setConfig')) {
            $config->setConfig(null);
        }
        
        Registry::set(Config::class, null);

        $moduleVariablesCache = new \OxidEsales\Eshop\Core\FileCache();
        $shopIdCalculator = new \OxidEsales\Eshop\Core\ShopIdCalculator($moduleVariablesCache);

        if (
            ($shopId != $shopIdCalculator->getShopId())
            || ($shopId != Registry::getConfig()->getShopId())
        ) {
            throw new \Exception(
                'Failed to switch to subshop id ' . $shopId . " Calculate ID: "
                . $shopIdCalculator->getShopId() . " Config ShopId: "
                . Registry::getConfig()->getShopId()
            );
        }
    }
}
