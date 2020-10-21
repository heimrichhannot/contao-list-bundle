<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Command;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\CoreBundle\Command\AbstractLockedCommand;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use Contao\ModuleModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigElementModel;
use HeimrichHannot\FilterBundle\Model\FilterConfigModel;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\ListBundle\Module\ModuleList;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeCommand extends AbstractLockedCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;
    /**
     * @var ContaoFramework
     */
    private $framework;
    /**
     * @var DcaUtil
     */
    private $dcaUtil;
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var string
     */
    private $name;

    public function __construct(
        ContaoFramework $contaoFramework,
        DcaUtil $dcaUtil,
        ModelUtil $modelUtil,
        $name = null
    ) {
        $this->framework = $contaoFramework;
        $this->dcaUtil = $dcaUtil;
        $this->modelUtil = $modelUtil;
        $this->name = $name;

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('huh-list:make')->setDescription('Creates list modules based on heimrichhannot/contao-list-bundle.');
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->framework->initialize();

        $themes = $this->modelUtil->findAllModelInstances('tl_theme', [
            'order' => 'tl_theme.name ASC',
        ]);

        if (null === $themes) {
            $this->io->error('You need at least 1 record in tl_theme before creating modules.');

            return 0;
        }

        $themeOptions = [];

        while ($themes->next()) {
            $themeOptions[$themes->id] = $themes->name;
        }

        $table = $this->io->ask('Which database entities would you like to display? Please type in the table name', 'tl_news');

        $this->dcaUtil->loadDc($table);

        if (!isset($GLOBALS['TL_DCA'][$table]) || !\is_array($GLOBALS['TL_DCA'][$table])) {
            $this->io->error('No DCA for "'.$table.'" could be found.');

            return 0;
        }

        $dca = $GLOBALS['TL_DCA'][$table];

        [$filterConfig, $filterTitle] = $this->createFilterConfig($table);

        $this->createPublishedFilterConfigElement($table, $filterConfig);
        $this->createArchiveFilterConfigElement($table, $filterConfig, $dca);

        $listConfig = $this->createListConfig($table, $filterTitle, $filterConfig);

        $this->createModule($filterTitle, $themeOptions, $listConfig);

        return 0;
    }

    protected function createFilterConfig(string $table)
    {
        $slugGenerator = new SlugGenerator();

        $filterTitle = $this->io->ask('Please type in the title of the filter configuration');

        $filterConfig = new FilterConfigModel();

        $filterConfig = $this->dcaUtil->setDefaultsFromDca('tl_filter_config', $filterConfig);

        $filterConfig->dateAdded = $filterConfig->tstamp = time();

        $filterConfig->mergeRow([
            'title' => $filterTitle,
            'name' => $slugGenerator->generate($filterTitle),
            'dataContainer' => $table,
            'template' => 'bootstrap_4_layout',
            'published' => true,
            'type' => 'filter',
        ]);

        $filterConfig->save();

        $this->io->success('Filter config created with ID '.$filterConfig->id.'.');

        return [$filterConfig, $filterTitle];
    }

    protected function createPublishedFilterConfigElement(string $table, Model $filterConfig)
    {
        $publishedField = $publishedStartField = $publishedStopField = null;
        $invertPublished = false;
        $addStartStop = false;

        if ($this->io->confirm('Would you like to hide unpublished entities?')) {
            switch ($table) {
                case 'tl_news':
                case 'tl_calendar_events':
                    $publishedField = 'published';
                    $publishedStartField = 'start';
                    $publishedStopField = 'stop';
                    $addStartStop = true;

                    break;

                case 'tl_member':
                    $publishedField = 'disable';
                    $publishedStartField = 'start';
                    $publishedStopField = 'stop';
                    $invertPublished = true;
                    $addStartStop = true;

                    break;
            }

            $publishedField = $this->io->ask('Please type in the published field\'s name', $publishedField);
            $invertPublished = $this->io->confirm('Should the published filter be inverted, i.e. is the "published" field defined inversely like "disabled"?', $invertPublished);

            if ($this->io->confirm('Does the entity have a start and stop field for publishing (e.g. tl_news.start and tl_news.stop)?', $addStartStop)) {
                $publishedStartField = $this->io->ask('Please type in the publish start field\'s name', $publishedStartField);
                $publishedStopField = $this->io->ask('Please type in the publish stop field\'s name', $publishedStopField);
            }
        }

        if ($publishedField) {
            $filterConfigElement = new FilterConfigElementModel();

            $filterConfigElement = $this->dcaUtil->setDefaultsFromDca('tl_filter_config_element', $filterConfigElement);

            $filterConfigElement->dateAdded = $filterConfigElement->tstamp = time();

            $filterConfigElement->mergeRow([
                'title' => 'Veröffentlicht',
                'pid' => $filterConfig->id,
                'sorting' => 32,
                'type' => 'visible',
                'field' => $publishedField,
                'published' => true,
            ]);

            if ($invertPublished) {
                $filterConfigElement->invertField = true;
            }

            if ($publishedStartField) {
                $filterConfigElement->addStartAndStop = true;
                $filterConfigElement->startField = $publishedStartField;
                $filterConfigElement->stopField = $publishedStopField;
            }

            $filterConfigElement->save();

            $this->io->success('Filter config element of type "visible" created with ID '.$filterConfigElement->id.'.');
        }
    }

    protected function createArchiveFilterConfigElement(string $table, Model $filterConfig, array $dca)
    {
        $parentTable = $dca['config']['ptable'] ?? null;

        if (!$this->io->confirm('Does the entity have one or more parent entities?', $parentTable ? true : false)) {
            return;
        }

        if (!$this->io->confirm('Would you like to filter the entities based on their parents?', true)) {
            return;
        }

        $parentTable = $this->io->ask('Please specify the parent table you\'d like to filter', $parentTable);
        $pidField = $this->io->ask('Please specify the parent id field in '.$table, isset($dca['fields']['pid']) ? 'pid' : null);

        $archives = $this->modelUtil->findAllModelInstances($parentTable);
        $archiveOptions = [];

        while ($archives->next()) {
            $archiveOptions[$archives->id] = $archives->name ?: ($archives->title ?: $archives->id);
        }

        $pid = $this->io->choice('Please type in the ID of the archive the entities need to be in', $archiveOptions);

        if (!is_numeric($pid)) {
            $pid = array_flip($archiveOptions)[$pid];
        }

        $filterConfigElement = new FilterConfigElementModel();

        $filterConfigElement = $this->dcaUtil->setDefaultsFromDca('tl_filter_config_element', $filterConfigElement);

        $filterConfigElement->dateAdded = $filterConfigElement->tstamp = time();

        $filterConfigElement->mergeRow([
            'title' => 'Archiv',
            'pid' => $filterConfig->id,
            'sorting' => 64,
            'type' => 'parent',
            'field' => $pidField,
            'isInitial' => true,
            'initialValueType' => 'scalar',
            'initialValue' => $pid,
            'operator' => 'equal',
            'published' => true,
        ]);

        $filterConfigElement->save();

        $this->io->success('Filter config element of type "parent" created with ID '.$filterConfigElement->id.'.');
    }

    protected function createListConfig(string $table, string $filterTitle, Model $filterConfig)
    {
        $listTitle = $this->io->ask('Please type in the title of the list configuration', $filterTitle);
        $parentListConfig = $this->io->ask('Do you want to create a child list config inheriting from a parent? If so, please type in the parent ID here', 0);

        switch ($table) {
            case 'tl_news':
                $sortingField = 'date';

                break;

            case 'tl_calendar_events':
                $sortingField = 'startDate';

                break;

            case 'tl_member':
                $sortingField = 'lastname';

                break;

            default:
                $sortingField = null;
        }

        $sortingField = $this->io->ask('Please specify the sorting field', $sortingField);
        $sortingDirection = $this->io->ask('Please specify the sorting direction (asc or desc)', 'asc');

        $useAlias = false;

        switch ($table) {
            case 'tl_news':
            case 'tl_calendar_events':
                $useAlias = true;

                break;
        }

        if ($useAlias = $this->io->confirm('Would you like to use the entity\'s alias field for url generation?', $useAlias)) {
            switch ($table) {
                case 'tl_news':
                case 'tl_calendar_events':
                    $aliasField = 'alias';

                    break;
            }

            $aliasField = $this->io->ask('Please type in the alias field\'s name', $aliasField);
        }

        $listConfig = new ListConfigModel();

        $listConfig = $this->dcaUtil->setDefaultsFromDca('tl_list_config', $listConfig);

        $listConfig->dateAdded = $listConfig->tstamp = time();

        $listConfig->mergeRow([
            'title' => $listTitle,
            'filter' => $filterConfig->id,
            'manager' => 'default',
            'item' => 'default',
            'limitFormattedFields' => true,
            'sortingMode' => 'field',
            'sortingField' => $sortingField,
            'sortingDirection' => $sortingDirection,
            'addDetails' => true,
            'listTemplate' => 'default',
            'itemTemplate' => 'default',
        ]);

        if ($parentListConfig) {
            $listConfig->parentListConfig = $parentListConfig;
        }

        if ($useAlias) {
            $listConfig->mergeRow([
                'useAlias' => true,
                'aliasField' => $aliasField,
            ]);
        }

        $listConfig->save();

        $this->io->success('List config created with ID '.$listConfig->id.'.');

        return $listConfig;
    }

    protected function createModule(string $filterTitle, array $themeOptions, Model $listConfig)
    {
        $moduleName = $this->io->ask('Please type in the title of the list module', $filterTitle);
        $modulePid = $this->io->choice('Please type in the ID of the theme which you\'d like to place the module under', $themeOptions);

        if (!is_numeric($modulePid)) {
            $modulePid = array_flip($themeOptions)[$modulePid];
        }

        $module = new ModuleModel();

        $module = $this->dcaUtil->setDefaultsFromDca('tl_module', $module);

        $module->tstamp = time();

        $module->mergeRow([
            'name' => $moduleName,
            'pid' => $modulePid,
            'type' => ModuleList::TYPE,
            'listConfig' => $listConfig->id,
        ]);

        $module->save();

        $this->io->success('Module created with ID '.$module->id.'.');
    }
}
