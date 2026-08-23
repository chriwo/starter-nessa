<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\DataProcessing;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

final readonly class AssetsOfCategoriesProcessor implements DataProcessorInterface
{
    private const string DEFAUL_TARGET_VARIABLE_NAME = 'portfolioAssets';

    private const string DEFAULT_SORTBY = 'name';

    public function __construct(
        private ConnectionPool $connectionPool,
        private ResourceFactory $resourceFactory,
    ) {
    }

    #[\Override]
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $targetVariableName = StringUtility::cast(
            $cObj->stdWrapValue('as', $processorConfiguration, self::DEFAUL_TARGET_VARIABLE_NAME),
        );
        if (!is_string($targetVariableName)) {
            $targetVariableName = self::DEFAUL_TARGET_VARIABLE_NAME;
        }

        $sortBy = StringUtility::cast(
            $cObj->stdWrapValue('sorting', $processorConfiguration, self::DEFAULT_SORTBY)
        );
        if (!is_string($sortBy)) {
            $sortBy = self::DEFAULT_SORTBY;
        }

        $categoriesIdentifiers = $this->getCategoriesIdsFromProcessedData($processedData);
        if ($categoriesIdentifiers === []) {
            return $processedData;
        }

        $fileRecords = $this->getRecords($categoriesIdentifiers, $sortBy);

        $categoriesWithData = [];
        foreach ($fileRecords as $record) {
            $processedData[$targetVariableName][] = [
                'file' => $this->resourceFactory->getFileObject($record['uid']),
                'category' => [
                    'uid' => $record['categoryUid'],
                    'title' => $record['title'],
                ],
            ];
            $categoriesWithData[] = $record['categoryUid'];
        }

        $result = array_unique($categoriesWithData);
        foreach ($processedData['portfolioCategories'] as $index => $resultItem) {
            $processedData['portfolioCategories'][$index]['assetsAvailable'] = false;
            if (in_array($resultItem['data']['uid'], $result)) {
                $processedData['portfolioCategories'][$index]['assetsAvailable'] = true;
            }
        }

        return $processedData;
    }

    /**
     * @return array<int, array{'uid': int, 'title': string, 'categoryUid': int}>
     * @throws Exception
     */
    private function getRecords(array $category, string $sorting = 'name'): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file');

        return $queryBuilder
            ->select('sys_file.uid', 'sys_category.title', 'sys_category.uid as categoryUid')
            ->from('sys_file')
            ->join(
                'sys_file',
                'sys_file_metadata',
                'sys_file_metadata',
                $queryBuilder->expr()->eq(
                    'sys_file_metadata.file',
                    $queryBuilder->quoteIdentifier('sys_file.uid')
                )
            )
            ->join(
                'sys_file_metadata',
                'sys_category_record_mm',
                'sys_category_record_mm',
                $queryBuilder->expr()->eq(
                    'sys_category_record_mm.uid_foreign',
                    $queryBuilder->quoteIdentifier('sys_file_metadata.uid')
                )
            )
            ->join(
                'sys_category_record_mm',
                'sys_category',
                'sys_category',
                $queryBuilder->expr()->eq(
                    'sys_category.uid',
                    $queryBuilder->quoteIdentifier('sys_category_record_mm.uid_local')
                )
            )
            ->where(
                $queryBuilder->expr()->in('sys_category_record_mm.uid_local', $category),
                $queryBuilder->expr()->eq(
                    'sys_category_record_mm.tablenames',
                    $queryBuilder->quote('sys_file_metadata')
                )
            )
            ->orderBy('sys_file.' . $sorting)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    private function getCategoriesIdsFromProcessedData(array $processedData): array
    {
        $identifiers = [];
        $processedPortfolioCategories = ArrayUtility::getValueByPath($processedData, 'portfolioCategories');

        if (is_array($processedPortfolioCategories) && count($processedPortfolioCategories) > 0) {
            foreach ($processedPortfolioCategories as $category) {
                $identifiers[] = MathUtility::forceIntegerInRange(
                    ArrayUtility::getValueByPath($category, 'data/uid'),
                    0,
                    PHP_INT_MAX
                );
            }
        }

        return $identifiers;
    }
}
