<?php

namespace HeimrichHannot\ListBundle\Util;

use Contao\DC_Table;
use Contao\System;
use HeimrichHannot\FilterBundle\Util\AbstractChoice;
use HeimrichHannot\UtilsBundle\Util\Utils;

class ModelInstanceChoicePolyfill extends AbstractChoice
{
    const TITLE_FIELDS = [
        'name',
        'title',
        'headline',
    ];

    /**
     * @return array
     */
    protected function collect(): array
    {
        $context = $this->getContext();
        $choices = [];

        $instances = System::getContainer()->get(Utils::class)->model()
            ->findModelInstancesBy(
                $context['dataContainer'],
                $context['columns'] ?? [],
                $context['values'] ?? null,
                is_array($context['options'] ?? null) ? $context['options'] : []
            );

        if (null === $instances) {
            return $choices;
        }

        while ($instances->next()) {
            $labelPattern = $context['labelPattern'] ?? null;

            if (!$labelPattern) {
                $labelPattern = 'ID %id%';

                switch ($context['dataContainer']) {
                    case 'tl_member':
                        $labelPattern = '%firstname% %lastname% (ID %id%)';

                        break;

                    default:
                        foreach (static::TITLE_FIELDS as $titleField) {
                            if (isset($GLOBALS['TL_DCA'][$context['dataContainer']]['fields'][$titleField])) {
                                $labelPattern = '%'.$titleField.'% (ID %id%)';

                                break;
                            }
                        }

                        break;
                }
            }

            $skipFormatting = $context['skipFormatting'] ?? false;

            if (!$skipFormatting) {
                $dca = &$GLOBALS['TL_DCA']['tl_submission'];
                # note: originally new \HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils(...);
                $dc = new DC_Table($context['dataContainer']);
                $dc->id = $instances->id;
                $dc->activeRecord = $instances->current();

                $label = preg_replace_callback(
                    '@%([^%]+)%@i',
                    function ($matches) use ($instances, $dca, $context, $dc) {
                        return System::getContainer()->get(Utils::class)->form()
                            ->prepareSpecialValueForOutput(
                                $matches[1],
                                $instances->{$matches[1]},
                                $dc
                            );
                    },
                    $labelPattern
                );
            } else {
                $label = preg_replace_callback(
                    '@%([^%]+)%@i',
                    function ($matches) use ($instances) {
                        return $instances->{$matches[1]};
                    },
                    $labelPattern
                );
            }

            $callbackLabel = Polyfill::getConfigByArrayOrCallbackOrFunction($context, 'label', [$label, $instances->row(), $context]);
            if (null !== $callbackLabel) {
                $label = $callbackLabel;
            }

            $choices[$instances->id] = $label;
        }

        if (!isset($context['skipSorting']) || !$context['skipSorting']) {
            natcasesort($choices);
        }

        return $choices;
    }
}