<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\Module;
use Contao\StringUtil;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementData;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementResult;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementTypeInterface;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment as TwigEnvironment;

class SubmissionFormConfigElementType implements ConfigElementTypeInterface
{
    const TYPE = 'submission_form';

    protected Utils $utils;
    protected RequestStack $requestStack;
    protected TwigEnvironment $twig;
    public static $recipientEmail;
    private static $count = 0;

    public function __construct(
        Utils           $utils,
        RequestStack    $requestStack,
        TwigEnvironment $twig,
    )
    {
        $this->utils = $utils;
        $this->requestStack = $requestStack;
        $this->twig = $twig;
    }

    /**
     * Return the list config element type alias.
     */
    public static function getType(): string
    {
        return static::TYPE;
    }

    /**
     * Return the list config element type palette.
     */
    public function getPalette(string $prependPalette, string $appendPalette): string
    {
        return $prependPalette.'{config_legend},submissionFormExplanation,submissionReader,submissionDefaultValues,emailField,submissionFormTemplate;'.$appendPalette;
    }

    /**
     * Update the item data.
     */
    public function applyConfiguration(ConfigElementData $configElementData): ConfigElementResult
    {
        $itemData = $configElementData->getItemData();
        $configuration = $configElementData->getConfiguration();

        // add email value to notification center tokens
        static::$recipientEmail = $itemData[$configuration->emailField];
        $GLOBALS['TL_HOOKS']['formhybridBeforeCreateNotifications']['contao-list-bundle.addEmailToTokens'] = [static::class, 'addEmailToTokens'];

        // generate form
        $type = $configuration->type;
        $ids = $configuration->id.$configuration->pid;
        $identifier = $type.'_'.$ids.$itemData['id'];

        $submissionReader = $this->generateSubmissionReader(
            (int) $configuration->submissionReader,
            $itemData,
            StringUtil::deserialize($configuration->submissionDefaultValues, true)
        );

        $templateData = [
            'identifier' => $identifier,
            'item' => $this,
            'form' => $submissionReader,
        ];

        $content = $this->twig->render($configuration->submissionFormTemplate, $templateData);
        return new ConfigElementResult(ConfigElementResult::TYPE_FORMATTED_VALUE, $content);
    }

    public function addEmailToTokens(&$submissionData, $submission): bool
    {
        $submissionData['form_value_submission_form_email'] = static::$recipientEmail;
        return true;
    }

    public function generateSubmissionReader(int $submissionReader, array $item = [], array $defaultValues = []): string
    {
        $moduleModel = $this->utils->model()->findModelInstanceByPk('tl_module', $submissionReader);
        if (null === $moduleModel) {
            return '';
        }

        $class = Module::findClass($moduleModel->type);
        if (!class_exists($class)) {
            return '';
        }

        $request = $this->requestStack->getCurrentRequest();

        // the form might be more than one time on the page
        $moduleModel->formHybridUseCustomFormIdSuffix = true;
        $moduleModel->formHybridCustomFormIdSuffix = ++static::$count;
        $existingDefaultValues = StringUtil::deserialize($moduleModel->formHybridDefaultValues, true);

        if (!empty($defaultValues)) {
            $moduleModel->formHybridAddDefaultValues = true;

            $newValues = [];

            foreach ($defaultValues as $value) {
                $newValues[] = [
                    'field' => $value['submissionField'],
                    'value' => $item[$value['entityField']],
                    'label' => $value['submissionField']
                ];
            }

            $moduleModel->formHybridDefaultValues = array_merge($existingDefaultValues, $newValues);
        }

        $shouldRender = 'POST' !== $request->getMethod()
            || 'POST' === $request->getMethod()
            && str_starts_with($request->get('FORM_SUBMIT'), 'tl_submission_'.$moduleModel->id)
            && str_ends_with($request->get('FORM_SUBMIT'), '_'.$moduleModel->formHybridCustomFormIdSuffix);

        if ($shouldRender) {
            /** @var Module $module */
            $module = new $class($moduleModel);

            return $module->generate();
        }

        return '';
    }
}
