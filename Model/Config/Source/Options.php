<?php
/**
 * @author MagePixel Team
 * @copyright Copyright © 2019 MagePixel. All rights reserved.
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