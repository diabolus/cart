<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\Cart\Business\StorageProvider;

use Generated\Shared\Transfer\CartTransfer;
use Generated\Shared\Transfer\ChangeTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Spryker\Zed\Cart\Business\StorageProvider\InMemoryProvider;

/**
 * @group Spryker
 * @group Zed
 * @group Cart
 * @group Business
 * @group InMemoryProvider
 */
class InMemoryProviderTest extends \PHPUnit_Framework_TestCase
{

    const COUPON_CODE_1 = 'coupon code 1';
    const COUPON_CODE_2 = 'coupon code 2';

    /**
     * @var \Spryker\Zed\Cart\Business\StorageProvider\StorageProviderInterface
     */
    private $provider;

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->provider = new InMemoryProvider();
    }

    //@todo test with more then 1 item

    /**
     * @return void
     */
    public function testAddExistingItem()
    {
        $cart = $this->createCartWithItem($itemId = '123', $existingQuantity = 1);

        $newItem = $this->createItem($itemId, $newQuantity = 3);
        $change = new ChangeTransfer();
        $change->addItem($newItem);

        $changedCart = $this->provider->addItems($cart, $change);
        $changedItems = $changedCart->getItems();
        $this->assertCount(2, $changedItems);

        /** @var \Generated\Shared\Transfer\ItemTransfer $changedItem */
        $changedItem = $changedItems[0];

        $this->assertEquals($itemId, $changedItem->getId());
        $this->assertEquals(
            1,
            $changedItem->getQuantity()
        );
    }

    /**
     * @return void
     */
    public function testAddNewItem()
    {
        $cart = $this->createCartWithItem($itemId = '123', $existingQuantity = 1);

        $newItem = $this->createItem($newId = '321', $newQuantity = 3);
        $change = new ChangeTransfer();
        $change->addItem($newItem);

        $changedCart = $this->provider->addItems($cart, $change);
        $changedItems = $changedCart->getItems();
        $this->assertCount(2, $changedItems);

        $skuIndex = [];
        /* @var ItemTransfer $cartItem */
        foreach ($changedItems as $key => $changedItem) {
            $skuIndex[$changedItem->getId()] = $key;
        }

        $this->assertArrayHasKey($itemId, $skuIndex);
        $this->assertArrayHasKey($newId, $skuIndex);

        /** @var \Generated\Shared\Transfer\ItemTransfer $addedItem */
        $addedItem = $changedItems[$skuIndex[$newId]];
        $this->assertEquals($newId, $addedItem->getId());
        $this->assertEquals($newQuantity, $addedItem->getQuantity());

        /** @var \Generated\Shared\Transfer\ItemTransfer $existingItem */
        $existingItem = $changedItems[$skuIndex[$itemId]];
        $this->assertEquals($itemId, $existingItem->getId());
        $this->assertEquals($existingQuantity, $existingItem->getQuantity());
    }

    /**
     * @return void
     */
    public function testRemoveExistingItem()
    {
        $cart = $this->createCartWithItem($itemId = '123', $existingQuantity = 1);
        $newItem = $this->createItem($itemId, $reduceQuantity = 1);
        $change = new ChangeTransfer();
        $change->addItem($newItem);

        $changedCart = $this->provider->removeItems($cart, $change);
        $this->assertCount(0, $changedCart->getItems());
    }

    /**
     * @return void
     */
    public function testRemoveNotExistingItem()
    {
        $cart = $this->createCartWithItem($itemId = '123', $existingQuantity = 1);
        $newItem = $this->createItem($deleteItemId = '321', $reduceQuantity = 1);
        $change = new ChangeTransfer();
        $change->addItem($newItem);

        $changedCart = $this->provider->removeItems($cart, $change);
        $changedItems = $changedCart->getItems();
        $this->assertCount(1, $changedItems);
        /** @var \Generated\Shared\Transfer\ItemTransfer $item */
        $item = $changedItems[0];
        $this->assertEquals($itemId, $item->getId());
        $this->assertEquals($existingQuantity, $item->getQuantity());
    }

    /**
     * @return void
     */
    public function testReduceWithMoreThanExists()
    {
        $cart = $this->createCartWithItem($itemId = '123', $existingQuantity = 1);
        $newItem = $this->createItem($itemId, $reduceQuantity = 3);
        $change = new ChangeTransfer();
        $change->addItem($newItem);

        $changedCart = $this->provider->removeItems($cart, $change);
        $this->assertCount(0, $changedCart->getItems());
    }

    /**
     * @expectedException \Spryker\Zed\Cart\Business\Exception\InvalidArgumentException
     * @expectedExceptionMessage Could not change cart item "123" with "-3" as value.
     *
     * @return void
     */
    public function testIncreaseWithNegativeValue()
    {
        $cart = $this->createCartWithItem($itemId = '123', $existingQuantity = 1);

        $newItem = $this->createItem($newId = '123', $newQuantity = -3);
        $change = new ChangeTransfer();
        $change->addItem($newItem);

        $this->provider->addItems($cart, $change);
    }

    /**
     * @expectedException \Spryker\Zed\Cart\Business\Exception\InvalidArgumentException
     * @expectedExceptionMessage Could not change cart item "123" with "0" as value.
     *
     * @return void
     */
    public function testIncreaseWithZeroValue()
    {
        $cart = $this->createCartWithItem($itemId = '123', $existingQuantity = 1);

        $newItem = $this->createItem($newId = '123', $newQuantity = 0);
        $change = new ChangeTransfer();
        $change->addItem($newItem);

        $this->provider->addItems($cart, $change);
    }

    /**
     * @expectedException \Spryker\Zed\Cart\Business\Exception\InvalidArgumentException
     * @expectedExceptionMessage Could not change cart item "123" with "-3" as value.
     *
     * @return void
     */
    public function testDecreaseWithNegativeValue()
    {
        $cart = $this->createCartWithItem($itemId = '123', $existingQuantity = 1);

        $newItem = $this->createItem($newId = '123', $newQuantity = -3);
        $change = new ChangeTransfer();
        $change->addItem($newItem);

        $this->provider->removeItems($cart, $change);
    }

    /**
     * @expectedException \Spryker\Zed\Cart\Business\Exception\InvalidArgumentException
     * @expectedExceptionMessage Could not change cart item "123" with "0" as value.
     *
     * @return void
     */
    public function testDecreaseWithZeroValue()
    {
        $cart = $this->createCartWithItem($itemId = '123', $existingQuantity = 1);

        $newItem = $this->createItem($newId = '123', $newQuantity = 0);
        $change = new ChangeTransfer();
        $change->addItem($newItem);

        $this->provider->removeItems($cart, $change);
    }

    /**
     * @return void
     */
    public function testAddCouponCodeMustCopyCouponCopeFromChangeTransferToCartTransfer()
    {
        $changeTransfer = new ChangeTransfer();
        $changeTransfer->setCouponCode(self::COUPON_CODE_1);

        $cartTransfer = new CartTransfer();

        $inMemoryProvider = new InMemoryProvider();
        $cartTransfer = $inMemoryProvider->addCouponCode($cartTransfer, $changeTransfer);

        $this->assertSame(self::COUPON_CODE_1, $cartTransfer->getCouponCodes()[0]);
    }

    /**
     * @return void
     */
    public function testRemoveCouponCodeMustRemoveCouponCodeFromCartTransfer()
    {
        $changeTransfer = new ChangeTransfer();
        $changeTransfer->setCouponCode(self::COUPON_CODE_1);

        $cartTransfer = new CartTransfer();
        $cartTransfer->addCouponCode(self::COUPON_CODE_1);
        $cartTransfer->addCouponCode(self::COUPON_CODE_2);

        $inMemoryProvider = new InMemoryProvider();
        $cartTransfer = $inMemoryProvider->removeCouponCode($cartTransfer, $changeTransfer);

        $this->assertSame(self::COUPON_CODE_2, $cartTransfer->getCouponCodes()[0]);
    }

    /**
     * @return void
     */
    public function testClearCouponCodeMustRemoveAllCouponCodesFromCartTransfer()
    {
        $cartTransfer = new CartTransfer();
        $cartTransfer->addCouponCode(self::COUPON_CODE_1);

        $inMemoryProvider = new InMemoryProvider();
        $cartTransfer = $inMemoryProvider->clearCouponCodes($cartTransfer);

        $this->assertSame([], $cartTransfer->getCouponCodes());
    }

    /**
     * @param string $itemId
     * @param int $itemQuantity
     *
     * @return \Generated\Shared\Transfer\CartTransfer
     */
    protected function createCartWithItem($itemId, $itemQuantity)
    {
        $cart = new CartTransfer();
        $existingItem = $this->createItem($itemId, $itemQuantity);
        $cart->addItem($existingItem);

        return $cart;
    }

    /**
     * @param string $itemId
     * @param int $itemQuantity
     *
     * @return \Generated\Shared\Transfer\ItemTransfer|\Generated\Shared\Transfer\ItemTransfer|\Spryker\Shared\Transfer\AbstractTransfer
     */
    protected function createItem($itemId, $itemQuantity)
    {
        $existingItem = new ItemTransfer();
        $existingItem->setId($itemId);
        $existingItem->setSku($itemId);
        $existingItem->setQuantity($itemQuantity);

        return $existingItem;
    }

}