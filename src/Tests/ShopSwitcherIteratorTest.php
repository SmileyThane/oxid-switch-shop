<?php
declare(strict_types=1);
namespace OxidProfessionalServices\ShopSwitcher\Tests;

use PHPUnit\Framework\TestCase;

class ShopSwitcherIteratorTest extends TestCase
{
    public function testIterate() {
        $sut = new ShopSwitcher();
        $count = 0;
        foreach ($sut as $shopId) {
            $count++;
            $this->assertEquals(1, $shopId);
        }
        $this->assertEquals(1, $count);
    }
    
}
