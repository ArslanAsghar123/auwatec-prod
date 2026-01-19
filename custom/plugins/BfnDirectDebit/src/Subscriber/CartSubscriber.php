<?php declare(strict_types=1);

namespace Bfn\DirectDebit\Subscriber;

use Bfn\DirectDebit\BfnDirectDebit;
use Shopware\Core\Checkout\Customer\Event\CustomerChangedPaymentMethodEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CartSubscriber implements EventSubscriberInterface
{
    /** @var EntityRepository $customerRepository*/
    private $customerRepository;
    /** @var EntityRepository $orderRepository*/
    private $orderRepository;
    /** @var SystemConfigService $configService */
    private $configService;

    /**
     * @param EntityRepository $customerRepository
     * @param EntityRepository $orderRepository
     * @param SystemConfigService $configService
     */
    public function __construct(
        EntityRepository $customerRepository,
        EntityRepository $orderRepository,
        SystemConfigService $configService
    ) {
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->configService = $configService;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            CartConvertedEvent::class => 'cartConvertedEvent',
            CustomerChangedPaymentMethodEvent::class => 'changedPaymentMethod',
            KernelEvents::REQUEST => 'onKernelRequestEvent'
        ];
    }

    public function onKernelRequestEvent(RequestEvent $event)
    {
        $route = $event->getRequest()->attributes->get('_route');

        if ($route === 'frontend.account.edit-order.update-order') {
            $request = $event->getRequest();
            if (!$event->isMainRequest()) {
                return;
            }

            $postData = $request->request->all();

            $salesChannelId = $event->getRequest()->attributes->get('sw-sales-channel-id');
            $config = $this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config', $salesChannelId);

            if (!empty($postData['paymentMethodId']) && $postData['paymentMethodId'] === $config['directDebitPaymentMethod']) {
                // Initialize custom fields array
                $customFieldsCustomer = [];
                $customFieldsOrder = [];

                // Check and add direct debit owner if name query is active
                if ($config['nameActive']) {
                    if (!empty($postData['editDirectDebitOwner'])) {
                        $customFieldsCustomer['bfn_customer_direct_debit_owner'] = $postData['editDirectDebitOwner'];
                        $customFieldsOrder['bfn_order_direct_debit_owner'] = $postData['editDirectDebitOwner'];
                    }
                }

                if (!empty($postData['editDirectDebitIban']) && preg_match('/X{10,}/', $postData['editDirectDebitIban']) != 1) {
                    $customFieldsCustomer['bfn_customer_direct_debit_iban'] = strtoupper(str_replace(' ', '', $postData['editDirectDebitIban']));
                    $customFieldsOrder['bfn_order_direct_debit_iban'] = strtoupper(str_replace(' ', '', $postData['editDirectDebitIban']));
                }

                // Check and add bic if it's present in the request and swift query is active
                if ($config['swiftActive']) {
                    if (!empty($postData['editDirectDebitBicSwift'])) {
                        $customFieldsCustomer['bfn_customer_direct_debit_bic_swift'] = strtoupper(str_replace(' ', '', $postData['editDirectDebitBicSwift']));
                        $customFieldsOrder['bfn_order_direct_debit_bic_swift'] = strtoupper(str_replace(' ', '', $postData['editDirectDebitBicSwift']));
                    }
                }

                // Check and add mandate if it's present in the request and mandate query is active
                if ($config['queryMandateCreation']) {
                    if ($postData['editDirectDebitMandate'] == true) {
                        $customFieldsCustomer['bfn_customer_direct_debit_mandate'] = true;
                        $customFieldsOrder['bfn_order_direct_debit_mandate'] = true;
                    } else {
                        $customFieldsCustomer['bfn_customer_direct_debit_mandate'] = false;
                        $customFieldsOrder['bfn_order_direct_debit_mandate'] = false;
                    }
                }

                    // Only proceed if at least one of the fields is present
                if ($config['saveDebitDataOnCustomer']) {
                    if (!empty($customFieldsCustomer)) {
                        // Store the data to the customer account
                        $this->customerRepository->upsert([
                            [
                                'id' => $postData['customerId'],
                                'customFields' => $customFieldsCustomer,
                            ],
                        ], \Shopware\Core\Framework\Context::createDefaultContext());
                    }
                }

                if (!empty($customFieldsOrder)) {
                    // Store the data to the customer account
                    $this->orderRepository->upsert([
                        [
                            'id' => $postData['orderId'],
                            'customFields' => $customFieldsOrder,
                        ],
                    ], \Shopware\Core\Framework\Context::createDefaultContext());
                }
            }

            if (!empty($postData['paymentMethodId']) && $config['changeCustomerDefaultPayment'] && !empty($postData['customerId'])) {
                $this->customerRepository->update([
                    [
                        'id' => $postData['customerId'],
                        'defaultPaymentMethodId' => $postData['paymentMethodId'],
                    ]
                ], \Shopware\Core\Framework\Context::createDefaultContext());
            }
        }
    }

    /**
     * @param CustomerChangedPaymentMethodEvent $event
     * @return void
     */
    public function changedPaymentMethod(CustomerChangedPaymentMethodEvent $event)
    {
        // Get payment method id
        $paymentMethodId = $event->getRequestDataBag()->get('paymentMethodId');

        // Only proceed if the new payment method is the defined payment method and the config is set accordingly
        if ($paymentMethodId === $this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config.directDebitPaymentMethod', $event->getSalesChannelContext()->getSalesChannelId())
        && $this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config.saveDebitDataOnCustomer', $event->getSalesChannelContext()->getSalesChannelId())) {
            // Check if name and bic (swift) queries are active
            $nameActive = $this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config.nameActive', $event->getSalesChannelContext()->getSalesChannelId());
            $swiftActive = $this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config.swiftActive', $event->getSalesChannelContext()->getSalesChannelId());
            $mandateActive = $this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config.queryMandateCreation', $event->getSalesChannelContext()->getSalesChannelId());

            // Initialize custom fields array
            $customFields = [];

            // Check and add direct debit owner if name query is active
            if ($nameActive) {
                $directDebitOwner = $event->getRequestDataBag()->get('direct_debit_owner', false);
                if ($directDebitOwner !== false) {
                    $customFields['bfn_customer_direct_debit_owner'] = $directDebitOwner;
                }
            }

            // Check and add iban if it's present in the request and not false
            $directDebitIban = $event->getRequestDataBag()->get('direct_debit_iban', false);
            if ($directDebitIban !== false && preg_match('/X{10,}/', $directDebitIban) != 1) {
                $customFields['bfn_customer_direct_debit_iban'] = strtoupper(str_replace(' ', '', $directDebitIban));
            }

            // Check and add bic if it's present in the request and swift query is active
            if ($swiftActive) {
                $directDebitBic = $event->getRequestDataBag()->get('direct_debit_bic_swift', false);
                if ($directDebitBic !== false) {
                    $customFields['bfn_customer_direct_debit_bic_swift'] = strtoupper(str_replace(' ', '', $directDebitBic));
                }
            }

            // Check and add mandate if it's present in the request and mandate query is active
            if ($mandateActive) {
                $directDebitMandate = $event->getRequestDataBag()->get('direct_debit_mandate', false);
                if ($directDebitMandate == 'on') {
                    $customFields['bfn_customer_direct_debit_mandate'] = true;
                } else {
                    $customFields['bfn_customer_direct_debit_mandate'] = false;
                }
            }

            // Only proceed if at least one of the fields is present
            if (!empty($customFields)) {
                // Store the data to the customer account
                $this->customerRepository->upsert([
                    [
                        'id' => $event->getCustomerId(),
                        'customFields' => $customFields,
                    ],
                ], $event->getContext());
            }
        }
    }

    /**
     * @param CartConvertedEvent $event
     * @return void
     */
    public function cartConvertedEvent(CartConvertedEvent $event)
    {
        $salesChannelContext = $event->getSalesChannelContext();
        $paymentMethodId = $salesChannelContext->getPaymentMethod()->getId();

        if ($paymentMethodId === $this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config.directDebitPaymentMethod', $event->getSalesChannelContext()->getSalesChannelId())) {
            // Check if name and bic (swift) queries are active
            $nameActive = $this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config.nameActive', $event->getSalesChannelContext()->getSalesChannelId());
            $swiftActive = $this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config.swiftActive', $event->getSalesChannelContext()->getSalesChannelId());
            $mandateActive = $this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config.queryMandateCreation', $event->getSalesChannelContext()->getSalesChannelId());

            // Initialize custom fields array
            $customFields = [];

            // Get the cart
            $cart = $event->getConvertedCart();

            // Check and add direct debit owner if name query is active
            if ($nameActive) {
                $directDebitOwner = (isset($_POST['direct_debit_owner'])?$_POST['direct_debit_owner']:false);
                if ($directDebitOwner !== false) {
                    $cart['customFields']['bfn_order_direct_debit_owner'] = $directDebitOwner;
                    $customFields['bfn_customer_direct_debit_owner'] = $directDebitOwner;
                }
            }

            // Check and add iban if it's present in the request and not false
            $directDebitIban = (isset($_POST['direct_debit_iban'])?$_POST['direct_debit_iban']:false);
            if ($directDebitIban !== false) {
                if (preg_match('/X{10,}/', $directDebitIban) != 1) {
                    $cart['customFields']['bfn_order_direct_debit_iban'] = strtoupper(str_replace(' ', '', $directDebitIban));
                    $customFields['bfn_customer_direct_debit_iban'] = strtoupper(str_replace(' ', '', $directDebitIban));
                } else {
                    $customerId = $event->getSalesChannelContext()->getCustomerId();
                    $customer = $this->customerRepository->search(
                        (new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria([$customerId]))
                            ->addAssociation('customFields'),
                        $event->getContext()
                    )->first();

                    if ($customer && isset($customer->getCustomFields()['bfn_customer_direct_debit_iban'])) {
                        $savedIban = $customer->getCustomFields()['bfn_customer_direct_debit_iban'];

                        // Check whether the last 4 characters match
                        if (substr($savedIban, -4) === substr($directDebitIban, -4)) {
                            // Verwende die gespeicherte IBAN anstelle der aus der Anfrage
                            $cart['customFields']['bfn_order_direct_debit_iban'] = $savedIban;
                            $customFields['bfn_customer_direct_debit_iban'] = $savedIban;
                        } else {
                            // If there is no match, use the new IBAN
                            $cart['customFields']['bfn_order_direct_debit_iban'] = strtoupper(str_replace(' ', '', $directDebitIban));
                            $customFields['bfn_customer_direct_debit_iban'] = strtoupper(str_replace(' ', '', $directDebitIban));
                        }
                    }
                }
            }

            // Check and add bic if it's present in the request and swift query is active
            if ($swiftActive) {
                $directDebitBic = (isset($_POST['direct_debit_bic_swift'])?$_POST['direct_debit_bic_swift']:false);
                if ($directDebitBic !== false) {
                    $cart['customFields']['bfn_order_direct_debit_bic_swift'] = strtoupper(str_replace(' ', '', $directDebitBic));
                    $customFields['bfn_customer_direct_debit_bic_swift'] = strtoupper(str_replace(' ', '', $directDebitBic));
                }
            }

            // Check and add mandate if it's present in the request and mandate query is active
            if ($mandateActive) {
                $directDebitMandate = (isset($_POST['direct_debit_mandate'])?$_POST['direct_debit_mandate']:false);
                if ($directDebitMandate == 'on') {
                    $directDebitMandate = true;
                } else {
                    $directDebitMandate = false;
                }
                $cart['customFields']['bfn_order_direct_debit_mandate'] = $directDebitMandate;
                $customFields['bfn_customer_direct_debit_mandate'] = $directDebitMandate;
            }

            // Store direct debit data to order
            $event->setConvertedCart($cart);

            // Store direct debit data to customer account if config is set accordingly
            if ($this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config.saveDebitDataOnCustomer', $event->getSalesChannelContext()->getSalesChannelId())) {
                $this->customerRepository->upsert([
                    [
                        'id' => $event->getSalesChannelContext()->getCustomerId(),
                        'customFields' => $customFields,
                    ],
                ], $event->getContext());
            }
        }

        $changeCustomerDefaultPayment = $this->configService->get(BfnDirectDebit::PLUGIN_NAME . '.config.changeCustomerDefaultPayment', $event->getSalesChannelContext()->getSalesChannelId());

        if (!empty($paymentMethodId) && $changeCustomerDefaultPayment && !empty($event->getSalesChannelContext()->getCustomerId())) {
            $this->customerRepository->update([
                [
                    'id' => $event->getSalesChannelContext()->getCustomerId(),
                    'defaultPaymentMethodId' => $paymentMethodId,
                ]
            ], \Shopware\Core\Framework\Context::createDefaultContext());
        }
    }
}
