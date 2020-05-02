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
use Magento\Ui\Component\Form;

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

        $shippingCostPath = $this->arrayManager->findPath($shippingCostField, $meta, null, 'children');
        $shippingCostTypeFieldPath = $this->arrayManager->findPath($shippingCostTypeField, $meta, null, 'children');
        $addafter = $this->arrayManager->get($shippingCostPath . '/arguments/data/config/addafter', $meta);
        if ($shippingCostPath && $shippingCostTypeFieldPath) 
        {
            //require('uiRegistry').get('product_form.product_form.product-details.container_shipping_cost.shipping_cost_type').value();
            //!${$.provider}:data.product.shipping_cost_type:value
            //echo $addafter;
            /*if($addafter){
                exit('if');
            } else {
                exit('else');
            }*/
            $meta = $this->arrayManager->merge(
                $shippingCostPath . static::META_CONFIG_PATH,
                $meta,
                [
                    'dataScope' => $shippingCostField,
                    'validation' => [
                        'validate-zero-or-greater' => true
                    ],
                    'additionalClasses' => 'admin__field-small',
                    'addafter' => $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol(),
                    'imports' => $addafter ? [
                        'addafter' => '${$.provider}:' . self::DATA_SCOPE_PRODUCT
                            . '.shipping_cost_type:value'
                    ] : [] 
                ]
            );

            $containerPath = $this->arrayManager->findPath(
                static::CONTAINER_PREFIX . $shippingCostField,
                $meta,
                null,
                'children'
            );
            $meta = $this->arrayManager->merge($containerPath . static::META_CONFIG_PATH, $meta, [
                'component' => 'Magento_Ui/js/form/components/group',
            ]);

            $shippingCostTypePath = $this->arrayManager->slicePath($shippingCostPath, 0, -1) . '/'
                . $shippingCostTypeField;
            $meta = $this->arrayManager->set(
                $shippingCostTypePath,
                $meta,
                $this->arrayManager->get($shippingCostTypeFieldPath, $meta)
            );
            $meta = $this->arrayManager->remove($shippingCostTypeFieldPath, $meta);
        }
        return $meta;
    }
}
