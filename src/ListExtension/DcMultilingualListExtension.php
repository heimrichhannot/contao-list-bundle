<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ListExtension;

use Contao\Controller;
use Contao\Database;
use Contao\Date;
use Contao\DcaExtractor;
use Doctrine\DBAL\Query\QueryBuilder;
use HeimrichHannot\DcMultilingualUtilsBundle\ContaoDcMultilingualUtilsBundle;
use HeimrichHannot\ListBundle\ListConfiguration\ListConfiguration;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Terminal42\DcMultilingualBundle\QueryBuilder\MultilingualQueryBuilder;
use Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle;

/**
 * @internal Spezification not final, no bc promise!
 */
class DcMultilingualListExtension implements ListExtensionInterface
{
    /**
     * @var Utils
     */
    private $utils;

    public function __construct(Utils $utils)
    {
        $this->utils = $utils;
    }

    public static function getAlias(): string
    {
        return 'dc_multilingual';
    }

    public static function getFields(): array
    {
        return [];
    }

    public function prepareQueryBuilderBeforeCount(QueryBuilder $queryBuilder, ListConfiguration $listConfiguration): void
    {
        if (!isset($GLOBALS['TL_DCA'][$listConfiguration->getDataContainer()])) {
            Controller::loadDataContainer($listConfiguration->getDataContainer());
        }

        $dbFields = Database::getInstance()->getFieldNames($listConfiguration->getDataContainer());
        $dca = $GLOBALS['TL_DCA'][$listConfiguration->getDataContainer()];
        $fallbackLanguage = ($GLOBALS['TL_LANGUAGE'] === $dca['config']['fallbackLang']);

        $regularFields = array_intersect(
            $dbFields,
            array_keys(DcaExtractor::getInstance($listConfiguration->getDataContainer())->getFields())
        );

        $translatableFields = [];

        foreach ($GLOBALS['TL_DCA'][$listConfiguration->getDataContainer()]['fields'] as $field => $data) {
            if (!isset($data['eval']['translatableFor']) || !\in_array($field, $dbFields, true)) {
                continue;
            }
            $translatableFields[] = $field;
        }

        $dcQueryBuilder = new MultilingualQueryBuilder(
            new QueryBuilder($queryBuilder->getConnection()),
            $listConfiguration->getDataContainer(),
            $dca['config']['langPid'],
            $dca['config']['langColumnName'],
            $regularFields,
            $translatableFields
        );
        $dcQueryBuilder->buildQueryBuilderForFind($GLOBALS['TL_LANGUAGE']);
        $dcQueryBuilder = $dcQueryBuilder->getQueryBuilder();

        foreach ($dcQueryBuilder->getQueryParts() as $key => $part) {
            $append = false;

            if (!\in_array($key, ['select', 'from', 'where', 'join'])) {
                continue;
            }

            if ('where' === $key) {
                if ('AND' === $part->getType()) {
                    $queryBuilder->andWhere($part);

                    continue;
                }
                $append = true;
            }

            // only show translated records
            if ('join' === $key) {
                if (!$fallbackLanguage) {
                    $part[$listConfiguration->getDataContainer()][0]['joinType'] = 'right outer';
                }
                $queryBuilder->add($key, [
                    $listConfiguration->getDataContainer() => $part[$listConfiguration->getDataContainer()][0],
                ], true);

                continue;
            }
            $queryBuilder->add($key, $part, $append);
        }

        $queryBuilder->resetQueryPart('groupBy');

        if (false && !$fallbackLanguage
            && class_exists(ContaoDcMultilingualUtilsBundle::class)
            && !$this->utils->container()->isPreviewMode()
            && isset($dca['config']['langPublished'])
            && isset($dca['fields'][$dca['config']['langPublished']])
            && \is_array($dca['fields'][$dca['config']['langPublished']])
        ) {
            $and = $queryBuilder->expr()->andX();

            if (isset($dca['config']['langStart']) && isset($dca['fields'][$dca['config']['langStart']]) && \is_array($dca['fields'][$dca['config']['langStart']]) &&
                isset($dca['config']['langStop']) && isset($dca['fields'][$dca['config']['langStop']]) && \is_array($dca['fields'][$dca['config']['langStop']])) {
                $time = Date::floorToMinute();

                $orStart = $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('translation.'.$dca['config']['langStart'], '""'),
                    $queryBuilder->expr()->lte('translation.'.$dca['config']['langStart'], ':'.$dca['config']['langStart'].'_time')
                );

                $and->add($orStart);
                $queryBuilder->setParameter($dca['config']['langStart'].'_time', $time);

                $orStop = $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('translation.'.$dca['config']['langStop'], '""'),
                    $queryBuilder->expr()->gt('translation.'.$dca['config']['langStop'], ':'.$dca['config']['langStop'].'_time')
                );

                $and->add($orStop);
                $queryBuilder->setParameter($dca['config']['langStop'].'_time', $time + 60);
            }

            $and->add($queryBuilder->expr()->eq('translation.'.$dca['config']['langPublished'], 1));

            $queryBuilder->andWhere($and);
        }
    }

    public function prepareListTemplate(array &$templateData, ListConfiguration $listConfiguration): void
    {
    }

    public function prepareListItemTemplate(array &$templateData, ListConfiguration $listConfiguration): void
    {
    }

    public function prepareQueryBuilderBeforeItemRetrival(QueryBuilder $queryBuilder, ListConfiguration $listConfiguration, int $totalCount): void
    {
        // TODO: Implement prepareQueryBuilderBeforeItemRetrival() method.
    }

    private function isDcMultilingualActive(ListConfiguration $listConfiguration)
    {
        if (!class_exists(Terminal42DcMultilingualBundle::class)) {
            return false;
        }

        return 'Multilingual' === ($GLOBALS['TL_DCA'][$listConfiguration->getDataContainer()]['config']['dataContainer'] ?? '');
    }
}
