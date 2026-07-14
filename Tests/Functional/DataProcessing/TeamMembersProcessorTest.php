<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Tests\Functional\DataProcessing;

use PHPUnit\Framework\Attributes\Test;
use StarterTeam\StarterNessa\DataProcessing\TeamMembersProcessor;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\RecordInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class TeamMembersProcessorTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['starterteam/starter-nessa'];

    protected array $coreExtensionsToLoad = ['frontend'];

    #[Test]
    public function resolvesReferencedMembersInFieldOrderWithAssetsAndSocialLinks(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/TeamMembersToRender.csv');

        $teamMembers = $this->runProcessor(10);

        // Referenced order is "2,1,4,3"; member 4 is deleted and 3 is hidden, so
        // only 2 and 1 remain, in exactly that order.
        self::assertCount(2, $teamMembers);
        $first = $teamMembers[0];
        $second = $teamMembers[1];
        self::assertInstanceOf(RecordInterface::class, $first);
        self::assertInstanceOf(RecordInterface::class, $second);
        self::assertSame(2, $first->getUid());
        self::assertSame(1, $second->getUid());

        // Member 2 (Erika) owns two social links, resolved as sub-records in
        // sorting order.
        $socialLinks = $first->get('nessa_social_element');
        self::assertInstanceOf(LazyRecordCollection::class, $socialLinks);
        self::assertCount(2, $socialLinks);
        $socialRecords = array_values(iterator_to_array($socialLinks));
        self::assertInstanceOf(RecordInterface::class, $socialRecords[0]);
        self::assertInstanceOf(RecordInterface::class, $socialRecords[1]);
        self::assertSame('bi-linkedin', $socialRecords[0]->get('icon'));
        self::assertSame('bi-instagram', $socialRecords[1]->get('icon'));

        // Member 1 (Max) owns the FAL image, member 2 has none.
        $assets = $second->get('assets');
        self::assertInstanceOf(LazyFileReferenceCollection::class, $assets);
        self::assertCount(1, $assets);
        $assetItems = array_values(iterator_to_array($assets));
        self::assertInstanceOf(FileReference::class, $assetItems[0]);

        $emptyAssets = $first->get('assets');
        self::assertInstanceOf(LazyFileReferenceCollection::class, $emptyAssets);
        self::assertCount(0, $emptyAssets);
    }

    #[Test]
    public function returnsEmptyListWhenNoMembersAreReferenced(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/TeamMembersToRender.csv');
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');
        $connection->update('tt_content', ['nessa_team_member_element' => ''], ['uid' => 10]);

        self::assertSame([], $this->runProcessor(10));
    }

    /**
     * @return array<int, mixed>
     */
    private function runProcessor(int $contentElementUid): array
    {
        $row = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content')
            ->select(['*'], 'tt_content', ['uid' => $contentElementUid])
            ->fetchAssociative();
        self::assertIsArray($row);

        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObjectRenderer->setRequest(new ServerRequest('https://example.com/'));
        $contentObjectRenderer->start($row, 'tt_content');

        $result = GeneralUtility::makeInstance(TeamMembersProcessor::class)->process(
            $contentObjectRenderer,
            [],
            ['fieldName' => 'nessa_team_member_element', 'as' => 'teamMembers'],
            ['data' => $row],
        );

        self::assertArrayHasKey('teamMembers', $result);
        self::assertIsArray($result['teamMembers']);

        return array_values($result['teamMembers']);
    }
}
