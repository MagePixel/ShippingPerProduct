<?php
/**
 * @author MagePixel Team
 * @copyright Copyright (c) 2020 MagePixel (http://www.magepixel.com/)
 * @package MagePixel_ShippingPerProduct
 */

namespace MagePixel\ShippingPerProduct\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ShippingPerProduct extends AbstractCarrier implements CarrierInterface
{
    /**
     * Constant defining shipping code for method
     */
    const SHIPPING_CODE = 'shippingperproduct';

    /**
     * Constant Min subtotal
     */
    const MIN_SUBTOTAL = 0;

    /**
     * Constant Max subtotal
     */
    const MAX_SUBTOTAL = 999999;

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = self::SHIPPING_CODE;

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var cart
     */
    protected $_cart;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_productRepository = $productRepository;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Generates list of allowed carrier`s shipping methods
     * Displays on cart price rules page
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

    /**
     * Collect and get rates for storefront
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param RateRequest $request
     * @api
     */
    public function collectRates(RateRequest $request)
    {
        /**
         * Make sure that Shipping method is enabled
         */
        if (!$this->isActive()) {
            return false;
        }

        $min_subtotal = self::MIN_SUBTOTAL;
        $max_subtotal = self::MAX_SUBTOTAL;
        if((int)$this->getConfigData('min_subtotal') >= 0)
            $min_subtotal = (int)$this->getConfigData('min_subtotal');
        if((int)$this->getConfigData('max_subtotal'))
            $max_subtotal = (int)$this->getConfigData('max_subtotal');
        $order_subtotal = (int)$request->getPackageValueWithDiscount();
        
        if($this->getCarrierCode() === self::SHIPPING_CODE && $order_subtotal >= $min_subtotal && $order_subtotal < $max_subtotal)
        {
            /** @var \Magento\Shipping\Model\Rate\Result $result */
            $result = $this->_rateResultFactory->create();
            
            $finalShippingPrice =  $this->getFinalShippingPrice($request);

            $method = $this->_rateMethodFactory->create();

            /**
             * Set carrier's method data
             */
            $method->setCarrier($this->getCarrierCode());
            $method->setCarrierTitle($this->getConfigData('title'));

            /**
             * Displayed as shipping method under Carrier
             */
            $method->setMethod($this->getCarrierCode());
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice($finalShippingPrice);
            $method->setCost($finalShippingPrice);

            $result->append($method);

            return $result;
        } else {
            return false;
        }
    }

    /**
     * @param RateRequest $request
     * @return int
     */
    private function getFinalShippingPrice(RateRequest $request)
    {
        $finalShippingPrice = 0;
        $useDefaultShippingPrice = $this->getConfigData('use_default_price');
        $shippingPrice = $this->getConfigData('price');
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                
                $shippingCost = $item->getProduct()->getShippingCost();
                $shippingCostType = $item->getProduct()->getShippingCostType();

                if($item->getProduct()->getTypeId() == 'configurable'){
                    $simpleProduct = $this->_productRepository->get($item->getProduct()->getSku());
                    if($simpleProduct->getShippingCost()) {
                        $shippingCost = $simpleProduct->getShippingCost();
                        $shippingCostType = $simpleProduct->getShippingCostType();
                    }
                }

                if($this->getConfigData('multiply_qty')) {
                    if($shippingCost && $shippingCostType) {
                        $finalShippingPrice += ($item->getProduct()->getFinalPrice() * $shippingCost/100) * $item->getQty();
                    } else {
                        $finalShippingPrice += $shippingCost * $item->getQty();
                    }
                } else {
                    if($shippingCost && $shippingCostType) {
                        $finalShippingPrice += ($item->getProduct()->getFinalPrice() * $shippingCost/100);
                    } else {
                        $finalShippingPrice += $shippingCost;
                    }
                }
            }
            if($useDefaultShippingPrice) {
                $finalShippingPrice += $shippingPrice;
            }
            if($this->getConfigData('handling_charge')){
                $finalShippingPrice += $this->getConfigData('handling_charge');
            }
        }
        return $finalShippingPrice;
    }

}