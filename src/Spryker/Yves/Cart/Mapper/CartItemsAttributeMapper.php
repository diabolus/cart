<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Cart\Mapper;

class CartItemsAttributeMapper
{

    /**
     * @var \Spryker\Client\Product\ProductClientInterface
     */
    protected $productOptionsClient;

    /**
     * @var \Spryker\Client\Availability\AvailabilityClientInterface
     */
    protected $productAvailabilityClient;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param \Spryker\Client\Product\ProductClientInterface $productClient
     * @param \Spryker\Client\Availability\AvailabilityClientInterface $productAvailabilityClient
     */
    public function __construct($productClient, $productAvailabilityClient)
    {
        $this->productClient = $productClient;
        $this->productAvailabilityClient = $productAvailabilityClient;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer[] $items
     *
     * @return array
     */
    public function buildMap($items)
    {
        $attributes = [];
        foreach ($items as $item) {
            $attributes[$item->getSku()] = $this->getAttributesAndAvailability($item);
        }

        return $attributes;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $item
     *
     * @return array
     */
    protected function getAttributesAndAvailability($item)
    {
        return [
            'attributes' => $this->getAttributesBySku($item),
            'availability' => $this->getAvailability($item),
        ];
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $item
     *
     * @return array
     */
    protected function getSelectedAttributes($item)
    {
        $selectedAttributes = [];

        $attributes = $this->getAttributesMapByProductAbstract($item);

        foreach ($attributes['attributeVariants'] as $variantName => $variant) {
            foreach ($variant as $productId => $options) {
                foreach ((array)$options as $option) {
                    if ($option === $item->getId()) {
                        $this->extractKeyValue($selectedAttributes, $variantName);
                    }
                }
            }
        }

        return $selectedAttributes;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $item
     *
     * @return array
     */
    protected function getAttributesBySku($item)
    {
        $attributes = $this->getAttributesMapByProductAbstract($item);

        $selectedAttributes = $this->getSelectedAttributes($item);

        return $this->markAsSelected($attributes['superAttributes'], $selectedAttributes);
    }

    /**
     * @param array $attributes
     * @param array $selectedAttributes
     *
     * @return array
     */
    protected function markAsSelected($attributes, $selectedAttributes)
    {
        $result = [];

        foreach ($attributes as $name => $attributeList) {
            foreach ($attributeList as $attribute) {
                if ($selectedAttributes[$name] === $attribute) {
                    $result[$name][$attribute] = true;
                    continue;
                }

                $result[$name][$attribute] = false;
            }
        }

        return $result;
    }

    /**
     * @param array $selectedAttributes
     * @param string $strVal
     * @param string $delimiter
     *
     * @return void
     */
    protected function extractKeyValue(array &$selectedAttributes, $strVal, $delimiter = ':')
    {
        list($k, $v) = explode($delimiter, $strVal);
        $selectedAttributes[$k] = $v;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $item
     *
     * @return array
     */
    protected function getAvailability($item)
    {
        $mapped = [];

        $availability = $this->productAvailabilityClient->getProductAvailabilityByIdProductAbstract($item->getIdProductAbstract())->toArray();

        foreach ($availability['concrete_product_available_items'] as $sku => $itemAvailable) {
            if ($sku === $item->getSku()) {
                $mapped['concreteProductAvailableItems'] = $itemAvailable;
                break;
            }
        }

        foreach ($availability['concrete_products_availability'] as $sku => $itemsAvailable) {
            if ($sku === $item->getSku()) {
                $mapped['concreteProductsAvailability'] = $itemsAvailable;
                break;
            }
        }

        return $mapped;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $item
     *
     * @return array
     */
    protected function getAttributesMapByProductAbstract($item)
    {
        if (array_key_exists($item->getSku(), $this->attributes) === false) {
            $this->attributes[$item->getSku()] = $this->productClient->getAttributeMapByIdProductAbstractForCurrentLocale($item->getIdProductAbstract());
        }
        return $this->attributes[$item->getSku()];
    }

}