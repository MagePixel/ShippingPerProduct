<?php
/**
 * @author MagePixel Team
 * @copyright Copyright (c) 2020 MagePixel (http://www.magepixel.com/)
 * @package MagePixel_ShippingPerProduct
 */

namespace MagePixel\ShippingPerProduct\Model\Config\Source;


class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label' => __('Fixed on Price'), 'value'=>'0'],
            ['label' => __('Percentage of Price'), 'value'=>'1']
        ];
        return $this->_options;
    }

}