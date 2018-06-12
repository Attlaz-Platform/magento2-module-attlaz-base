<?php

namespace Attlaz\Base\Controller\RealTime;

use Attlaz\Base\Helper\Data;
use Attlaz\Base\Helper\RealTimeInfo\RealTimeInfoHelper;
use Attlaz\Base\Model\RealTimeInfoRequest;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use  \Magento\Catalog\Model\ResourceModel\Product\Collection;
use \Attlaz\Base\Pricing\Render;

class ProductInfo extends Action
{
    /** @var PageFactory */
    protected $resultPageFactory;
    protected $realTimeInfoHelper;
    private $coreRegistry;
    private $logger;

    public function __construct(Context $context, PageFactory $resultPageFactory, RealTimeInfoHelper $realTimeInfoHelper, Registry $registry, LoggerInterface $logger)
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->realTimeInfoHelper = $realTimeInfoHelper;
        $this->coreRegistry = $registry;
        $this->logger = $logger;

    }

    public function execute()
    {
        //TODO: validate request
        try {

            $requests = $this->getRequestsFromRawRequests();
            $products = $this->getMagentoProductsByRealTimeInfoRequests($requests);
            $this->realTimeInfoHelper->updateProductCollectionWithExternalData($products);

            $priceRender = $this->getPriceRenderer();

            $resultData = [];
            /**
             * @var RealTimeInfoRequest $request
             */
            foreach ($requests as $request) {

                try {

                    $output = $this->renderRealTimeBlock($request, $products, $priceRender);

                    $resultData[$request->requestId] = [
                        'success' => true,
                        'result'  => $output,
                    ];
                } catch (\Exception $ex) {
                    $resultData[$request->requestId] = [
                        'success' => false,
                        'result'  => $ex->getMessage(),
                    ];
                    $this->logger->error('Unable to render real time block: ' . $ex->getMessage(), ['request' => $request->jsonSerialize()]);
                }

            }

            $result = [
                'success' => true,
                'data'    => $resultData,
                't'       => \gmdate('U'),
            ];
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage());
            $result = [
                'success' => false,
                'data'    => $ex->getMessage(),
                't'       => \gmdate('U'),
            ];
        }
        $this->getResponse()
             ->representJson(json_encode($result, JSON_PRETTY_PRINT));
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);

    }

    /**
     * @return RealTimeInfoRequest[]
     */
    private function getRequestsFromRawRequests(): array
    {
        $rawRequests = (array)$this->getRequest()
                                   ->getPost('requests');
        $requests = [];
        foreach ($rawRequests as $requestId => $rawRequest) {

            $request = RealTimeInfoRequest::fromJson($rawRequest);
            $request->requestId = $requestId;

            $requests[] = $request;

        }

        return $requests;
    }

    private function getProductIdsFromRequests(array $requests): array
    {
        $productIds = [];
        /** @var RealTimeInfoRequest $request */
        foreach ($requests as $request) {
            if (!in_array($request->product, $productIds)) {
                $productIds[] = $request->product;
            }
        }

        return $productIds;
    }

    private function getMagentoProductsByRealTimeInfoRequests(array $requests): Collection
    {

        $productIds = $this->getProductIdsFromRequests($requests);

        /** @var  \Magento\Catalog\Model\ResourceModel\Product\Collection $prodCollection */
        $prodCollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');

        $collection = $prodCollection->addAttributeToSelect(Data::EXTERNAL_ID_FIELD)
                                     ->addAttributeToSelect('*')
                                     ->addAttributeToFilter('entity_id', ['in' => $productIds])
                                     ->load();

        return $collection;
    }

    private function renderRealTimeBlock(RealTimeInfoRequest $request, Collection $products, $priceRender)
    {

        $productId = $request->product;
        $product = $products->getItemById($productId);

        switch ($request->type) {
            case RealTimeInfoRequest::TYPE_PRICE:

                $output = $this->renderPriceBlock($request, $product, $priceRender);

                break;
            case RealTimeInfoRequest::TYPE_STOCK:

                $output = $this->renderStockInfoBlock($request, $product);

                break;
            default:
                throw new \Exception('Unknown request type "' . $request->type . '"');

        }

        return $output;
    }

    private function renderStockInfoBlock(RealTimeInfoRequest $request, $product): string
    {
        $start_time = microtime(true);
        $block = $request->block;
        $template = $request->template;
        $data = $request->data;
        $data['product'] = $product;

        /** @var AbstractBlock $block */
        $block = $this->_objectManager->create($block, ['data' => $data]);
        $block->setData(Data::BLOCK_DATA_FLAG_CONTAINS_REAL_TIME_DATA, true);

        if ($block instanceof Template) {
            $block->setTemplate($template);
        }

        $output = $block->toHtml();

        $end_time = microtime(true);

        $this->bench['Render stock block'] = $end_time - $start_time;

        return $output;
    }

    private function renderPriceBlock(RealTimeInfoRequest $request, $product, $priceRender): string
    {
        $start_time = microtime(true);
        $priceCode = $request->data['priceCode'];
        $arguments = $request->data['arguments'];

        //TODO: temp: don't use registry
        if ($arguments['zone'] === \Magento\Framework\Pricing\Render::ZONE_ITEM_VIEW) {
            $this->coreRegistry->register('current_product', $product);
        }
        $output = $priceRender->render($priceCode, $product, $arguments);

        //TODO: temp: don't use registry
        if ($arguments['zone'] === \Magento\Framework\Pricing\Render::ZONE_ITEM_VIEW) {
            $this->coreRegistry->unregister('current_product');
        }
        $end_time = microtime(true);

        $this->bench['Render price block'] = $end_time - $start_time;

        return $output;
    }

    private function getPriceRenderer(): Render
    {

        $resultPage = $this->resultPageFactory->create();

        //TODO: do we need to validate the block?
        /** @var \Attlaz\Base\Pricing\Render $priceRender */
        $priceRender = $resultPage->getLayout()
                                  ->getBlock('product.price.render.default');
        $priceRender->setData(Data::BLOCK_DATA_FLAG_CONTAINS_REAL_TIME_DATA, true);

        return $priceRender;
    }

}
