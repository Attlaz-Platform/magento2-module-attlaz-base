<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Attlaz\Base\Setup;

use Attlaz\Base\Helper\Data;

use Magento\Catalog\Model\Category;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Psr\Log\LoggerInterface;
use \Magento\Eav\Model\Config;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    protected $attributeSetFactory;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    private $logger;

    public function __construct(IndexerRegistry $indexerRegistry, Config $eavConfig, AttributeSetFactory $attributeSetFactory, EavSetupFactory $eavSetupFactory, LoggerInterface $logger)
    {


        $this->attributeSetFactory = $attributeSetFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;

        $this->logger = $logger;
    }

    private function currentVersionIsLowerThan(ModuleContextInterface $context, string $version): bool
    {
        return version_compare($context->getVersion(), $version, '<');
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($this->currentVersionIsLowerThan($context, '1.0.1')) {
            $this->logger->info('Upgrade Attlaz Module to 1.0.1');
            $this->upgradeVersionOneZeroOne($setup);
        }

        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
        $this->eavConfig->clear();
        $setup->endSetup();
    }

    private function upgradeVersionOneZeroOne(ModuleDataSetupInterface $setup)
    {

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        //Add Category entity "external_id" attribute
        $eavSetup->addAttribute(Category::ENTITY, Data::EXTERNAL_ID_FIELD, [
            'type'       => 'varchar',
            'label'      => 'External id',
            'input'      => 'text',
            'required'   => false,
            'sort_order' => 25,
            'global'     => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'      => 'General Information',
        ]);
        //Add Product entity "external_id" attribute
        $eavSetup->addAttribute(Product::ENTITY, Data::EXTERNAL_ID_FIELD, [
            'type'                    => 'varchar',
            'label'                   => 'External id',
            'input'                   => 'text',
            'required'                => false,
            'sort_order'              => 25,
            'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
            'used_in_product_listing' => true,
            'group'                   => 'General',
            'is_used_in_grid'         => true,
            'is_visible_in_grid'      => false,
            'is_filterable_in_grid'   => true,
        ]);

    }

}
