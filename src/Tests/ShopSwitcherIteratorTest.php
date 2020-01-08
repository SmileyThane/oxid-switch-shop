<?php

declare(strict_types=1);

namespace OxidProfessionalServices\ShopSwitcher\Tests;

use PHPUnit\Framework\TestCase;
use OxidProfessionalServices\ShopSwitcher\ShopSwitcher;

class ShopSwitcherIteratorTest extends TestCase
{
    public function testIterate(): void
    {
        //bootstrap code that is somehow missing
        $cf = new \OxidEsales\Eshop\Core\ConfigFile('/var/www/oxideshop/source/config.inc.php');
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\ConfigFile::class, $cf);
        //end of bootstrap code
        
        
        $sut = new ShopSwitcher();
        $count = 0;
        foreach ($sut as $shopId) {
            $count++;
            $this->assertEquals(1, $shopId);
        }
        $this->assertEquals(1, $count);
    }
}
