<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\PersonalizationClient\Exporter;

use EzSystems\EzRecommendationClient\Exporter\Exporter;
use EzSystems\EzRecommendationClient\Exporter\ExporterInterface;
use EzSystems\EzRecommendationClient\File\FileManagerInterface;
use EzSystems\EzRecommendationClient\Service\ExportNotificationServiceInterface;
use EzSystems\EzRecommendationClient\Value\Export\EventList;
use Ibexa\Contracts\PersonalizationClient\Criteria\CriteriaInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemGroupListInterface;
use Ibexa\Contracts\PersonalizationClient\Value\ItemListInterface;
use Ibexa\PersonalizationClient\Config\ItemType\IncludedItemTypeResolverInterface;
use Ibexa\PersonalizationClient\Criteria\Criteria;
use Ibexa\PersonalizationClient\Generator\File\ExportFileGeneratorInterface;
use Ibexa\PersonalizationClient\Service\Export\ExportServiceInterface;
use Ibexa\PersonalizationClient\Service\Storage\DataSourceServiceInterface;
use Ibexa\PersonalizationClient\Value\Export\Credentials;
use Ibexa\PersonalizationClient\Value\Export\Event;
use Ibexa\PersonalizationClient\Value\Export\Parameters;
use Ibexa\Tests\PersonalizationClient\Creator\DataSourceTestItemCreator;
use Ibexa\Tests\PersonalizationClient\Storage\AbstractDataSourceTestCase;
use Psr\Http\Message\ResponseInterface;

final class ExporterTest extends AbstractDataSourceTestCase
{
    private const EXPORT_FILE_PATH_DIR = 'test/';
    private const EXPORT_FILE_URL = 'https://localhost/api/ezp/v2/ez_recommendation/v1/exportDownload/';

    private ExporterInterface $exporter;

    /** @var \Ibexa\PersonalizationClient\Service\Storage\DataSourceServiceInterface|\PHPUnit\Framework\MockObject\MockObject */
    private DataSourceServiceInterface $dataSourceService;

    /** @var \Ibexa\PersonalizationClient\Service\Export\ExportServiceInterface|\PHPUnit\Framework\MockObject\MockObject */
    private ExportServiceInterface $exportService;

    /** @var \Ibexa\PersonalizationClient\Config\ItemType\IncludedItemTypeResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private IncludedItemTypeResolverInterface $itemTypeResolver;

    /** @var \Ibexa\PersonalizationClient\Generator\File\ExportFileGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private ExportFileGeneratorInterface $exportFileGenerator;

    /** @var \EzSystems\EzRecommendationClient\File\FileManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private FileManagerInterface $fileManager;

    /** @var \EzSystems\EzRecommendationClient\Service\ExportNotificationServiceInterface|\PHPUnit\Framework\MockObject\MockObject */
    private ExportNotificationServiceInterface $notificationService;

    protected function setUp(): void
    {
        $this->dataSourceService = $this->createMock(DataSourceServiceInterface::class);
        $this->exportService = $this->createMock(ExportServiceInterface::class);
        $this->itemTypeResolver = $this->createMock(IncludedItemTypeResolverInterface::class);
        $this->exportFileGenerator = $this->createMock(ExportFileGeneratorInterface::class);
        $this->fileManager = $this->createMock(FileManagerInterface::class);
        $this->notificationService = $this->createMock(ExportNotificationServiceInterface::class);

        $this->exporter = new Exporter(
            $this->dataSourceService,
            $this->exportService,
            $this->itemTypeResolver,
            $this->exportFileGenerator,
            $this->fileManager,
            $this->notificationService
        );
    }

    public function testReturnTrueIfHasExportItems(): void
    {
        $this->configureDataSourceServiceToReturnItemList(
            $this->createCriteria(),
            $this->getItemList()
        );
        $this->configureIncludedItemTypeResolverToReturnIncludedItemTypes(
            $this->getItemTypeIdentifierList(),
            false,
            'foo'
        );

        self::assertTrue($this->exporter->hasExportItems($this->createExportParameters()));
    }

    public function testReturnFalseIfNoItemsToExport(): void
    {
        $this->configureDataSourceServiceToReturnItemList(
            $this->createCriteria(),
            $this->itemCreator->createTestItemList()
        );
        $this->configureIncludedItemTypeResolverToReturnIncludedItemTypes(
            $this->getItemTypeIdentifierList(),
            false,
            'foo'
        );

        self::assertFalse($this->exporter->hasExportItems($this->createExportParameters()));
    }

    public function testGetExportEvents(): void
    {
        $this->configureFileManagerToCreateChunkDir();
        $this->configureExportServiceToReturnCredentials();
        $this->configureExportServiceToReturnAuthenticationMethod();
        $this->configureIncludedItemTypeResolverToReturnIncludedItemTypes(
            $this->getItemTypeIdentifierList(),
            true,
            'foo'
        );
        $this->configureDataSourceServiceToReturnGroupedItems($this->createCriteria(), $this->getGroupedItems());

        self::assertEquals(
            $this->createExportEvents(),
            $this->exporter->getExportEvents($this->createExportParameters())
        );
    }

    public function testExport(): void
    {
        $this->configureFileManagerToCreateChunkDir();
        $this->configureExportServiceToReturnCredentials();
        $this->configureExportServiceToReturnAuthenticationMethod();
        $this->configureIncludedItemTypeResolverToReturnIncludedItemTypes(
            $this->getItemTypeIdentifierList(),
            true,
            'foo'
        );
        $this->configureDataSourceServiceToReturnGroupedItems($this->createCriteria(), $this->getGroupedItems());

        $parameters = $this->createExportParameters();
        $events = $this->createExportEvents();

        $this->notificationService
            ->expects(self::once())
            ->method('sendNotification')
            ->with($parameters, $events)
            ->willReturn($this->createMock(ResponseInterface::class));

        $this->exporter->export($parameters);
    }

    private function configureDataSourceServiceToReturnItemList(
        CriteriaInterface $criteria,
        ItemListInterface $itemList
    ): void {
        $this->dataSourceService
            ->expects(self::once())
            ->method('getItems')
            ->with($criteria)
            ->willReturn($itemList);
    }

    private function configureDataSourceServiceToReturnGroupedItems(
        CriteriaInterface $criteria,
        ItemGroupListInterface $groupedItems
    ): void {
        $this->dataSourceService
            ->expects(self::once())
            ->method('getGroupedItems')
            ->with($criteria, 'item_type_and_language')
            ->willReturn($groupedItems);
    }

    /**
     * @param array<string> $includedItemTypes
     */
    private function configureIncludedItemTypeResolverToReturnIncludedItemTypes(
        array $includedItemTypes,
        bool $uselogger,
        ?string $siteAccess = null
    ): void {
        $this->itemTypeResolver
            ->expects(self::once())
            ->method('resolve')
            ->with($includedItemTypes, $uselogger, $siteAccess)
            ->willReturn($includedItemTypes);
    }

    private function configureFileManagerToCreateChunkDir(): void
    {
        $this->fileManager
            ->expects(self::once())
            ->method('createChunkDir')
            ->willReturn(self::EXPORT_FILE_PATH_DIR);
    }

    private function configureFileManagerToSecureDir(string $login, string $password): void
    {
        $this->fileManager
            ->expects(self::once())
            ->method('secureDir')
            ->with(self::EXPORT_FILE_PATH_DIR, $login, $password);
    }

    private function configureExportServiceToReturnCredentials(): void
    {
        $this->exportService
            ->expects(self::once())
            ->method('getCredentials')
            ->with('foo')
            ->willReturn($this->getCredentials());
    }

    private function configureExportServiceToReturnAuthenticationMethod(): void
    {
        $this->exportService
            ->expects(self::once())
            ->method('getAuthenticationMethod')
            ->willReturn('basic');
    }

    private function createCriteria(): CriteriaInterface
    {
        return new Criteria(
            $this->getItemTypeIdentifierList(),
            $this->getLanguageList(),
            500
        );
    }

    private function createExportParameters(): Parameters
    {
        return Parameters::fromArray(
            [
                'customer_id' => '12345',
                'license_key' => '12345-12345-12345-12345',
                'item_type_identifier_list' => implode(',', $this->getItemTypeIdentifierList()),
                'languages' => implode(',', $this->getLanguageList()),
                'siteaccess' => 'foo',
                'web_hook' => 'https://reco.engine.com',
                'host' => 'https://localhost',
                'page_size' => '500',
            ]
        );
    }

    private function getItemList(): ItemListInterface
    {
        return $this->itemCreator->createTestItemList(
            $this->itemCreator->createTestItem(
                1,
                '1',
                DataSourceTestItemCreator::ARTICLE_TYPE_ID,
                DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                DataSourceTestItemCreator::ARTICLE_TYPE_NAME,
                DataSourceTestItemCreator::LANGUAGE_EN
            ),
            $this->itemCreator->createTestItem(
                1,
                '2',
                DataSourceTestItemCreator::PRODUCT_TYPE_ID,
                DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER,
                DataSourceTestItemCreator::PRODUCT_TYPE_NAME,
                DataSourceTestItemCreator::LANGUAGE_EN
            ),
            $this->itemCreator->createTestItem(
                1,
                '3',
                DataSourceTestItemCreator::BLOG_TYPE_ID,
                DataSourceTestItemCreator::BLOG_TYPE_IDENTIFIER,
                DataSourceTestItemCreator::BLOG_TYPE_NAME,
                DataSourceTestItemCreator::LANGUAGE_EN
            ),
        );
    }

    private function getGroupedItems(): ItemGroupListInterface
    {
        return $this->itemCreator->createTestItemGroupList(
            $this->itemCreator->createTestItemGroup(
                'article_en',
                $this->itemCreator->createTestItemList(
                    $this->itemCreator->createTestItem(
                        1,
                        '1',
                        DataSourceTestItemCreator::ARTICLE_TYPE_ID,
                        DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
                        DataSourceTestItemCreator::ARTICLE_TYPE_NAME,
                        DataSourceTestItemCreator::LANGUAGE_EN
                    )
                )
            ),
            $this->itemCreator->createTestItemGroup(
                'product_en',
                $this->itemCreator->createTestItemList(
                    $this->itemCreator->createTestItem(
                        1,
                        '2',
                        DataSourceTestItemCreator::PRODUCT_TYPE_ID,
                        DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER,
                        DataSourceTestItemCreator::PRODUCT_TYPE_NAME,
                        DataSourceTestItemCreator::LANGUAGE_EN
                    )
                )
            ),
            $this->itemCreator->createTestItemGroup(
                'blog_en',
                $this->itemCreator->createTestItemList(
                    $this->itemCreator->createTestItem(
                        1,
                        '3',
                        DataSourceTestItemCreator::BLOG_TYPE_ID,
                        DataSourceTestItemCreator::BLOG_TYPE_IDENTIFIER,
                        DataSourceTestItemCreator::BLOG_TYPE_NAME,
                        DataSourceTestItemCreator::LANGUAGE_EN
                    )
                )
            ),
        );
    }

    /**
     * @return array<string>
     */
    private function getItemTypeIdentifierList(): array
    {
        return [
            DataSourceTestItemCreator::ARTICLE_TYPE_IDENTIFIER,
            DataSourceTestItemCreator::PRODUCT_TYPE_IDENTIFIER,
            DataSourceTestItemCreator::BLOG_TYPE_IDENTIFIER,
        ];
    }

    /**
     * @return array<string>
     */
    private function getLanguageList(): array
    {
        return [
            DataSourceTestItemCreator::LANGUAGE_EN,
            DataSourceTestItemCreator::LANGUAGE_DE,
        ];
    }

    /**
     * @return iterable<\Ibexa\PersonalizationClient\Value\Export\Event>
     */
    private function createExportEvents(): iterable
    {
        $credentials = $this->getCredentials();
        $generatedEvents = [
            new Event(
                DataSourceTestItemCreator::ARTICLE_TYPE_ID,
                DataSourceTestItemCreator::ARTICLE_TYPE_NAME,
                DataSourceTestItemCreator::LANGUAGE_EN,
                [
                    self::EXPORT_FILE_URL . self::EXPORT_FILE_PATH_DIR . 'article_en_1',
                ],
                $credentials
            ),
            new Event(
                DataSourceTestItemCreator::PRODUCT_TYPE_ID,
                DataSourceTestItemCreator::PRODUCT_TYPE_NAME,
                DataSourceTestItemCreator::LANGUAGE_EN,
                [
                    self::EXPORT_FILE_URL . self::EXPORT_FILE_PATH_DIR . 'product_en_1',
                ],
                $credentials
            ),
            new Event(
                DataSourceTestItemCreator::BLOG_TYPE_ID,
                DataSourceTestItemCreator::BLOG_TYPE_NAME,
                DataSourceTestItemCreator::LANGUAGE_EN,
                [
                    self::EXPORT_FILE_URL . self::EXPORT_FILE_PATH_DIR . 'blog_en_1',
                ],
                $credentials
            ),
        ];

        return new EventList($generatedEvents);
    }

    private function getCredentials(): Credentials
    {
        return new Credentials('foo', '123456789');
    }
}
