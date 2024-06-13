<?php
/**
 * Copyleft (c) Since 2024 Marco Salvatore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file docs/licenses/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 *
 * @author    Marco Salvatore <hi@marcosalvatore.dev>
 * @copyleft since 2024 Marco Salvatore
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License ("AFL") v. 3.0
 *
 */

namespace MCM\Console\Tests\Unit\Tools;

use MCM\Console\Tests\Unit\CSVFileIterator;
use MCM\Console\Tools\FindAndReplaceTool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class FindAndReplaceToolTest extends TestCase
{
    private $findAndReplaceTool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->findAndReplaceTool = new FindAndReplaceTool();
    }

    public function testGetCasedReplacePairs()
    {
        $casedReplacePairsDir = 'cased-replace-pairs/';

        foreach (iterator_to_array($this->csvProvider($casedReplacePairsDir . 'search-replace')) as $searchReplace) {
            $search = $searchReplace[0];
            $replace = $searchReplace[1];
            $formats = $searchReplace[2];

            $csvCasedReplacePairs = iterator_to_array($this->csvProvider($casedReplacePairsDir . $search . '-to-' . $replace));
            $expectedCasedReplacePairs = [];
            foreach ($csvCasedReplacePairs as $replacePair) {
                $expectedCasedReplacePairs[$replacePair[0]] = $replacePair[1];
            }

            $actualCasedReplacePairs = $this->getActualReplacePairs($search, $replace, $formats);

            $this->assertEquals($expectedCasedReplacePairs, $actualCasedReplacePairs);
        }
    }

    /**
     * @depends testGetCasedReplacePairs
     */
    public function testFindReplacePairsInFiles()
    {
        $filesReplacePairsDir = 'files-replace-pairs/';
        $modulesPaths = (new Finder())->in('tests/Resources/csv/' . $filesReplacePairsDir);

        foreach ($modulesPaths as $modulePath) {
            $module = $modulePath->getRelativePathname();
            if (!file_exists(_PS_MODULE_DIR_ . $module)) {
                continue;
            }

            $csvFilesReplacePairs = iterator_to_array($this->csvProvider($filesReplacePairsDir . $module . '/found'));
            $expectedFilesReplacePairs = [];
            foreach ($csvFilesReplacePairs as $replacePair) {
                $expectedFilesReplacePairs[$replacePair[0]] = $replacePair[1];
            }

            $actualFilesReplacePairs = [];
            $moduleFiles = $this->findAndReplaceTool
                    ->getFilesSortedByDepth(_PS_MODULE_DIR_ . $module)
                    ->exclude(['vendor', 'node_modules']);
            foreach (iterator_to_array($this->csvProvider($filesReplacePairsDir . $module . '/search-replace')) as $searchReplace) {
                $search = $searchReplace[0];
                $replace = $searchReplace[1];
                $formats = $searchReplace[2];

                $actualReplacePairs = $this->getActualReplacePairs($search, $replace, $formats);

                $actualFilesReplacePairs += $this->findAndReplaceTool->findReplacePairsInFiles(
                    $moduleFiles,
                    $actualReplacePairs
                );
            }

            $this->assertEquals($expectedFilesReplacePairs, $actualFilesReplacePairs);
        }
    }

    public function getActualReplacePairs($search, $replace, $formats)
    {
        if ($formats === 'usual') {
            $caseFormats = $this->findAndReplaceTool->getUsualCasesFormats();
        } else {
            $caseFormats = [];
        }

        return $this->findAndReplaceTool->getCasedReplacePairs($search, $replace, $caseFormats);
    }

    public function csvProvider($relativePath): CSVFileIterator
    {
        return new CSVFileIterator('tests/Resources/csv/' . $relativePath . '.csv');
    }
}
