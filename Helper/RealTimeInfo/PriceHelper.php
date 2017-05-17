<?php
declare(strict_types=1);

namespace Attlaz\Base\Helper\RealTimeInfo;

use Magento\Framework\App\Helper\AbstractHelper;
use Attlaz\Model\Catalog\Product as AttlazProduct;
use Attlaz\Model\Catalog\ProductPrice;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Customer\Model\Group;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use \Magento\Tax\Model\Config as TaxConfig;

class PriceHelper extends AbstractHelper
{
    private $objectManager;
    private $taxConfig;

    public function __construct(Context $context, ObjectManagerInterface $objectManager, TaxConfig $taxConfig)
    {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->taxConfig = $taxConfig;
    }

    public function updateProductPriceWithAttlazData(MagentoProduct $magentoProduct, AttlazProduct $attlazProduct)
    {

        /** @var ProductPrice $basePrice */
        $basePrice = $attlazProduct->getPrice(1);
        if ($basePrice === null) {
            $this->_logger->warning('Unable to update price with Attlaz data: base price not available (Magento product id: ' . $magentoProduct->getId() . ')');

            return false;
        }

        //TODO: make this configuratable
        $forceNettoPriceWhenHigherThanNormal = true;

        if ($this->pricesIncludesTax()) {
            //If the netto price is higher than the normal price, force the netto price
            if ($basePrice->endIncl > $basePrice->baseIncl && $forceNettoPriceWhenHigherThanNormal) {
                $magentoProduct->setPrice($basePrice->endIncl);
                $magentoProduct->setSpecialPrice($basePrice->endIncl);
            } else {
                $magentoProduct->setPrice($basePrice->baseIncl);
                $magentoProduct->setSpecialPrice($basePrice->endIncl);

            }

        } else {
            //If the netto price is higher than the normal price, force the netto price
            if ($basePrice->endExcl > $basePrice->baseExcl && $forceNettoPriceWhenHigherThanNormal) {
                $magentoProduct->setPrice($basePrice->endExcl);
                $magentoProduct->setSpecialPrice($basePrice->endExcl);
            } else {
                $magentoProduct->setPrice($basePrice->baseExcl);
                $magentoProduct->setSpecialPrice($basePrice->endExcl);
            }

        }

        $startDate = date("Y-m-d", \strtotime('-2 hours'));
        $endDate = date("Y-m-d", \strtotime('+2 hours'));

        $magentoProduct->setSpecialFromDate($startDate);
        $magentoProduct->setSpecialFromDateIsFormated(true);

        $magentoProduct->setSpecialToDate($endDate);
        $magentoProduct->setSpecialToDateIsFormated(true);

        $this->setTierPrices($magentoProduct, $attlazProduct);

    }

    private function pricesIncludesTax(): bool
    {


        $priceDisplayType = $this->taxConfig->getPriceDisplayType();
        if ($priceDisplayType === TaxConfig::DISPLAY_TYPE_INCLUDING_TAX || $priceDisplayType === TaxConfig::DISPLAY_TYPE_BOTH) {
            return true;
        }

        return false;
    }

    private function setTierPrices(MagentoProduct $magentoProduct, AttlazProduct $attlazProduct)
    {
        $tierPrices = $attlazProduct->getPriceTiers();
        $personalTierPrices = [];
        if (count($tierPrices) > 1) {

            $i = 0;
            foreach ($tierPrices as $quantity) {
                $linkProductTierPrice = $attlazProduct->getPrice($quantity);

                $price = $linkProductTierPrice->endExcl;
                if ($this->pricesIncludesTax()) {
                    $price = $linkProductTierPrice->endIncl;
                }

                /** @var \Magento\Catalog\Model\Product\TierPrice $tierPrice */
                $tierPrice = $this->objectManager->create('Magento\Catalog\Model\Product\TierPrice');
                $tierPrice->setId($i);
                $tierPrice->setCustomerGroupId(Group::CUST_GROUP_ALL);
                $tierPrice->setValue($price);
                $tierPrice->setQty($quantity);

                $personalTierPrices[] = $tierPrice;

                $i++;
            }

        }

        if (count($personalTierPrices) > 0) {
            $magentoProduct->setTierPrices($personalTierPrices);
        }
    }
}
