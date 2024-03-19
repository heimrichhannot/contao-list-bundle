<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Choice;

use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\ListBundle\Util\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\Finder\Finder;

class ListChoices
{
    public static function getItemOptions(DataContainer $dc): array
    {
        $config = System::getContainer()->getParameter('huh.list');

        if (empty($config['list']['items'])) {
            return [];
        }

        $choices = [];

        foreach ($config['list']['items'] as $manager) {
            $choices[$manager['name']] = $manager['class'];
        }

        asort($choices);

        return $choices;
    }

    public static function getListOptions(DataContainer $dc): array
    {
        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['lists'])) {
            return [];
        }

        $choices = [];

        foreach ($config['list']['lists'] as $manager) {
            $choices[$manager['name']] = $manager['class'];
        }

        asort($choices);

        return $choices;
    }

    public static function getManagerOptions(DataContainer $dc): array
    {
        $config = System::getContainer()->getParameter('huh.list');

        if (!isset($config['list']['managers'])) {
            return [];
        }

        $choices = [];

        foreach ($config['list']['managers'] as $manager) {
            $choices[$manager['name']] = $manager['id'];
        }

        asort($choices);

        return $choices;
    }

    public static function getParentListConfigOptions(DataContainer $dc): array
    {
        $id = $dc->id ?? null;

        if (!$id) {
            return [];
        }

        $listConfigs = System::getContainer()
            ->get('huh.list.list-config-registry')
            ->findBy(['tl_list_config.id != ?'], [$id]);

        if (null === $listConfigs) {
            return [];
        }

        $choices = array_combine(
            $listConfigs->fetchEach('id'),
            $listConfigs->fetchEach('title')
        );

        asort($choices);

        return $choices;
    }

    public static function getMessageOptions(DataContainer $dc, string|array $prefixes): array
    {
        $translator = System::getContainer()->get('translator');

        $catalog = $translator->getCatalogue();
        $all = $catalog->all();
        $messages = $all['messages'];

        if (!is_array($messages)) {
            return [];
        }

        if (!is_array($prefixes)) {
            $prefixes = [$prefixes];
        }

        $choices = [];

        foreach ($messages as $key => $value) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($key, $prefix)) {
                    $choices[$key] = $value . '[' . $key . ']';
                }
            }
        }

        return $choices;
    }

    const TITLE_FIELDS = [
        'name',
        'title',
        'headline',
    ];

    public static function getModelInstanceOptions(
        DataContainer $dc,
        string        $table,
        array         $columns = [],
        mixed         $values = null,
        ?string       $labelPattern = null,
        bool          $skipSorting = false,
        bool          $skipFormatting = false
    ): array {
        $instances = System::getContainer()->get(Utils::class)->model()
            ->findModelInstancesBy($table, $columns, $values);

        if (null === $instances) {
            return [];
        }

        if (!$labelPattern) {
            if ($table == 'tl_member')
            {
                $labelPattern = '%firstname% %lastname% (ID %id%)';
            }
            else
            {
                $labelPattern = 'ID %id%';
                foreach (static::TITLE_FIELDS as $titleField)
                {
                    if (isset($GLOBALS['TL_DCA'][$table]['fields'][$titleField]))
                    {
                        $labelPattern = '%' . $titleField . '% (ID %id%)';
                        break;
                    }
                }
            }
        }

        $choices = [];

        while ($instances->next()) {

            if (!$skipFormatting)
            {
                $dc = new DC_Table_Utils($table);
                $dc->id = $instances->id;
                $dc->activeRecord = $instances->current();

                $label = preg_replace_callback(
                    '@%([^%]+)%@i',
                    function ($matches) use ($instances, $dc) {
                        return System::getContainer()->get(Utils::class)->formatter()
                            ->formatDcaFieldValue(
                                $dc,
                                $matches[1],
                                $instances->{$matches[1]}
                            );
                    },
                    $labelPattern
                );
            }
            else
            {
                $label = preg_replace_callback(
                    '@%([^%]+)%@i',
                    function ($matches) use ($instances) {
                        return $instances->{$matches[1]};
                    },
                    $labelPattern
                );
            }

            // if (null !== ($callbackLabel = System::getContainer()->get('huh.utils.dca')
            //         ->getConfigByArrayOrCallbackOrFunction($context, 'label', [$label, $instances->row(), $context])))
            // {
            //     $label = $callbackLabel;
            // }

            $choices[$instances->id] = $label;
        }

        if (!$skipSorting) {
            natcasesort($choices);
        }

        return $choices;
    }

    public static function getTwigTemplateOptions(string|array $prefixes)
    {

        if (!is_array($prefixes)) {
            $prefixes = [$prefixes];
        }
        $prefixes = array_filter($prefixes);

        $kernel = System::getContainer()->get('kernel');
        $bundles = $kernel->getBundles();
        $pattern = !empty($prefixes) ? ('/(^'.implode('|^', $prefixes).').*twig/') : '*.twig';

        $choices = [];

        foreach ($bundles as $key => $value) {
            $path = $kernel->locateResource("@$key");
            $finder = new Finder();
            $finder->in($path);
            $finder->files()->name($pattern);
            $twigKey = preg_replace('/Bundle$/', '', $key);

            foreach ($finder as $val) {
                $explodedUrl = explode('Resources'.\DIRECTORY_SEPARATOR.'views'.\DIRECTORY_SEPARATOR, $val->getRelativePathname());
                $string = end($explodedUrl);
                $choices[$val->getBasename('.html.twig')] = "@$twigKey/$string";
            }
        }

        if (!System::getContainer()->has('huh.utils.container')) {
            return $choices;
        }

        foreach ($prefixes as $prefix) {
            # todo
            $choices = array_merge($choices, System::getContainer()->get('huh.utils.template')->getTemplateGroup($prefix, 'html.twig'));
        }

        return $choices;
    }
}
