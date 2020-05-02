<?php
/**
 * @author MagePixel Team
 * @copyright Copyright (c) 2020 MagePixel (http://www.magepixel.com/)
 * @package MagePixel_ShippingPerProduct
 */

namespace MagePixel\ShippingPerProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;

class ShippingType extends AbstractModifier
{

    protected $storeManager;

    protected $arrayManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        ArrayManager $arrayManager
    ) {
        $this->storeManager = $storeManager;
        $this->arrayManager = $arrayManager;
    }

    public function modifyMeta(array $meta)
    {
        $meta = $this->customizeShippingTypeField($meta);

        return $meta;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    protected function customizeShippingTypeField(array $meta)
    {
        $shippingCostField = 'shipping_cost';
        $shippingCostTypeField = 'shipping_cost_type';

        $shippingCostFieldPath = $this->arrayManager->findPath($shippingCostField, $meta, null, 'children');
        $shippingCostTypeFieldPath = $this->arrayManager->findPath($shippingCostTypeField, $meta, null, 'children');

        if ($shippingCostFieldPath && $shippingCostTypeFieldPath) {
            $fromContainerPath = $this->arrayManager->slicePath($shippingCostFieldPath, 0, -2);
            $toContainerPath = $this->arrayManager->slicePath($shippingCostTypeFieldPath, 0, -2);

            $meta = $this->arrayManager->merge(
                $shippingCostFieldPath . self::META_CONFIG_PATH,
                $meta,
                [
                    'label' => __('Shipping Charge'),
                    'additionalClasses' => 'admin__field-small',
                    'addafter' => $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol(),
                ]
            );
            $meta = $this->arrayManager->merge(
                $shippingCostTypeFieldPath . self::META_CONFIG_PATH,
                $meta,
                [
                    'label' => __('Shipping Charge Type'),
                    'scopeLabel' => null,
                    'additionalClasses' => 'admin__field-small',
                ]
            );
            $meta = $this->arrayManager->merge(
                $fromContainerPath . self::META_CONFIG_PATH,
                $meta,
                [
                    'label' => __('Shipping Charge'),
                    'additionalClasses' => 'admin__control-grouped-shipping',
                    'breakLine' => false,
                    'component' => 'Magento_Ui/js/form/components/group',
                ]
            );
            $meta = $this->arrayManager->set(
                $fromContainerPath . '/children/' . $shippingCostTypeField,
                $meta,
                $this->arrayManager->get($shippingCostTypeFieldPath, $meta)
            );

            $meta = $this->arrayManager->remove($toContainerPath, $meta);
        }

        return $meta;
    }
}
