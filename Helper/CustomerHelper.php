<?php

namespace Attlaz\Base\Helper;

use Attlaz\Base\Model\Config\Source\CustomerType;
use \Magento\Framework\App\Helper\Context;
use \Magento\Customer\Model\Session;

class CustomerHelper extends Data
{

    private $customerSession;

    public function __construct(Context $context, Session $customerSession)
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;

    }

    public function getSessionInfo(): array
    {
        return [
            'id'         => $this->customerSession->getCustomerId(),
            'logged_in'  => $this->customerSession->isLoggedIn(),
            'session_id' => $this->customerSession->getSessionId(),
            'exists'     => $this->customerSession->isSessionExists(),
        ];
    }

    public function shouldDisplayPrices(): bool
    {
        $value = $this->getConfigShowPricesForCustomer();
        $result = false;
        switch ($value) {
            case CustomerType::TYPE_ALL:
                $result = true;
                break;
            case CustomerType::TYPE_AUTHENTICATED:
                $result = $this->customerSession->isLoggedIn();
                break;
            case CustomerType::TYPE_LINKED:
                $result = $this->hasCurrentCustomerExternalId();
                break;
        }

        return $result;
    }

    public function shouldDisplayStockInfo(): bool
    {

        $value = $this->getConfigShowStockForCustomer();
        $result = false;
        switch ($value) {
            case CustomerType::TYPE_ALL:
                $result = true;
                break;
            case CustomerType::TYPE_AUTHENTICATED:
                $result = $this->customerSession->isLoggedIn();
                break;
            case CustomerType::TYPE_LINKED:
                $result = $this->hasCurrentCustomerExternalId();
                break;
        }

        return $result;
    }

    public function shouldDisplayStockBeforeRealTimeUpdate(): bool
    {
        //TODO: remove debug
        return true;

        //TODO: make this configurable
        return false;
    }

    public function shouldDisplayPricesBeforeRealTimeUpdate(): bool
    {
        //TODO: remove debug
        return true;

        //TODO: make this configurable
        return false;
    }

    public function hasCurrentCustomerExternalId(): bool
    {
        //TODO: there can be a customer external id for a not logged in customer
        //TODO: remove debug
        return true;
        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();

            return $this->hasExternalId($customer);
        }

//TODO check if there is a default external id
        return false;
    }

    public function getCurrentCustomerExternalId(): string
    {
        //TODO: remove debug
        return 'debug';

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();

            return $this->getExternalId($customer);
        }

        //TODO: return default customer external id
        return '';
    }

}
