<?php

namespace Pim\Behat\Context\Domain\Collect;

use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Connector\Writer\File\SpoutWriterFactory;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use OpenSpout\Common\Entity\Row;
use Pim\Behat\Context\Domain\ImportExportContext;

class ImportProfilesContext extends ImportExportContext
{
    use SpinCapableTrait;

    /**
     * @param string       $extension
     * @param PyStringNode $string
     *
     * @Given /^the following ([^"]*) file to import:$/
     */
    public function theFollowingFileToImport($extension, PyStringNode $string)
    {
        $extension = strtolower($extension);

        $string = $this->replacePlaceholders($string);

        self::$placeholderValues['%file to import%'] = $filename =
            sprintf(
                '%s/pim-import/behat-import-%s.%s',
                self::$placeholderValues['%tmp%'],
                substr(md5(rand()), 0, 7),
                $extension
            );
        @rmdir(dirname($filename));
        @mkdir(dirname($filename), 0777, true);

        if (SpoutWriterFactory::XLSX === $extension) {
            $writer = SpoutWriterFactory::create(SpoutWriterFactory::XLSX);
            $writer->openToFile($filename);
            foreach (explode(PHP_EOL, $string) as $row) {
                $rowCells = explode(";", $row);
                foreach ($rowCells as &$cell) {
                    if (is_numeric($cell) && 0 === preg_match('|^\+[0-9]+$|', $cell)) {
                        $cell = false === strpos($cell, '.') ? (int) $cell : (float) $cell;
                    }
                }

                $writer->addRow(Row::fromValues($rowCells));
            }
            $writer->close();
        } else {
            file_put_contents($filename, (string) $string);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following CSV configuration to import:$/
     */
    public function theFollowingCSVToImport(TableNode $table)
    {
        $delimiter = ';';

        $data    = $table->getRowsHash();
        $columns = implode($delimiter, array_keys($data));

        $rows = [];
        foreach ($data as $values) {
            foreach ($values as $index => $value) {
                $value          = in_array($value, ['yes', 'no']) ? (int) $value === 'yes' : $value;
                $rows[$index][] = $value;
            }
        }
        $rows = array_map(
            function ($row) use ($delimiter) {
                return implode($delimiter, $row);
            },
            $rows
        );

        array_unshift($rows, $columns);

        $this->theFollowingFileToImport('csv', new PyStringNode($rows, 0));
    }

    /**
     * @param string $file
     *
     * @Given /^I upload and import (an invalid|the) file "([^"]*)"$/
     */
    public function iUploadAndImportTheFile($operator, $file)
    {
        $this->spin(function () {
            return $this->getCurrentPage()->find(
                'css',
                '.AknTitleContainer-meta .AknButton--greyLight.switcher-action'
            );
        }, 'Cannot switch the import method')->click();

        $this->getMainContext()->getSubcontext('job')
            ->attachFileToField($this->replacePlaceholders($file), 'Drag and drop a file or click here');

        $this->getCurrentPage()
            ->getSession()
            ->executeScript('$(\'.AknMediaField-fileUploaderInput\').trigger(\'change\');');

        $this->getCurrentPage()->pressButton('Upload and import now');
        $this->getMainContext()->wait();
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" import job) page$/
     */
    public function iAmOnTheImportJobPage(JobInstance $job)
    {
        $this->getNavigationContext()->iAmOnThePage('Import show', ['code' => $job->getCode()]);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" import job) edit page$/
     */
    public function iAmOnTheImportJobEditPage(JobInstance $job)
    {
        $this->getNavigationContext()->openPage('Import edit', ['code' => $job->getCode()]);
    }

    /**
     * @param string       $code
     * @param PyStringNode $behatData
     *
     * @internal param PyStringNode $data
     *
     * @Given /^the invalid data file of "([^"]*)" should contain:$/
     */
    public function theInvalidDataFileOfShouldContain($code, PyStringNode $behatData)
    {
        $jobInstance = $this->getMainContext()->getSubcontext('fixtures')->getJobInstance($code);
        $jobExecution = $jobInstance->getJobExecutions()->first();
        $fileType = $jobInstance->getRawParameters()['invalid_items_file_format'];

        $archivePath = $this->getMainContext()->getSubcontext('job')->getJobInstanceArchivePath($code);
        $archivePath = sprintf(
            '%simport/%s/%s/invalid_%s/invalid_items.%s',
            $archivePath,
            $jobInstance->getJobName(),
            $jobExecution->getId(),
            $fileType,
            $fileType
        );

        $config = [];

        if (SpoutWriterFactory::CSV === $fileType) {
            $config = $this->getCsvJobConfiguration($code);
        } elseif (SpoutWriterFactory::XLSX === $fileType) {
            $config = $this->getXlsxJobConfiguration($code);
        }

        $expectedLines = $this->getExpectedLines($behatData, $config);
        $actualLines = $this->getActualLinesFromArchive($archivePath, $fileType, $config);

        $this->compareFile($expectedLines, $actualLines, $archivePath);
    }
}
