<?php

namespace Printess\PrintessDesigner\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Session\SessionManager;
use Magento\Integration\Model\Oauth\TokenFactory;

class PrintessDesigner implements SectionSourceInterface
{

    /**
     * @var SessionManager
     */
    protected SessionManager $sessionManager;

    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @var TokenFactory
     */
    protected TokenFactory $tokenModelFactory;

    /**
     * @param Session $customerSession
     * @param SessionManager $sessionManager
     * @param TokenFactory $tokenModelFactory
     */
    public function __construct(
        Session $customerSession,
        SessionManager $sessionManager,
        TokenFactory $tokenModelFactory
    ) {
        $this->customerSession = $customerSession;
        $this->sessionManager = $sessionManager;
        $this->tokenModelFactory = $tokenModelFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData(): array
    {

        $customerId = 'anonymous';
        $customerToken = 'anonymous';

        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();

            $tokenFactory = $this->tokenModelFactory->create();
            $customerToken = $tokenFactory->createCustomerToken($customerId)->getToken();
        }

        return [
            'session_id' => $this->sessionManager->getSessionId(),
            'customer_id' =>  $customerId,
            'customer_token' => $customerToken
        ];
    }
}
