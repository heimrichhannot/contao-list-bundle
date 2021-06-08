<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\Module;
use Contao\StringUtil;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementData;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementResult;
use HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementTypeInterface;
use HeimrichHannot\TwigSupportBundle\Renderer\TwigTemplateRenderer;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class SubmissionFormConfigElementType implements ConfigElementTypeInterface
{
    const TYPE = 'submission_form';

    public static $recipientEmail;

    /**
     * @var TwigTemplateRenderer
     */
    protected $twigTemplateRenderer;
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    private static $count = 0;

    public function __construct(ModelUtil $modelUtil, TwigTemplateRenderer $twigTemplateRenderer)
    {
        $this->modelUtil = $modelUtil;
        $this->twigTemplateRenderer = $twigTemplateRenderer;
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
        static::$recipientEmail = $configuration->emailField;
        $GLOBALS['TL_HOOKS']['formhybridBeforeCreateNotifications']['contao-list-bundle.addEmailToTokens'] = [static::class, 'addEmailToTokens'];

        // generate form
        $type = $configuration->type;
        $ids = $configuration->id.$configuration->pid;
        $identifier = $type.'_'.$ids.$itemData['id'];
        $templateData = [
            'identifier' => $identifier,
            'item' => $this,
            'form' => $this->generateSubmissionReader((int) $configuration->submissionReader, $itemData, StringUtil::deserialize($configuration->submissionDefaultValues, true)),
        ];

        return new ConfigElementResult(ConfigElementResult::TYPE_FORMATTED_VALUE, $this->twigTemplateRenderer->render($configuration->submissionFormTemplate, $templateData));
    }

    public function addEmailToTokens(&$submissionData, $submission)
    {
        $submissionData['form_value_submission_form_email'] = static::$recipientEmail;

        return true;
    }

    public function generateSubmissionReader(int $submissionReader, array $item = [], array $defaultValues = [])
    {
        if (null === ($moduleModel = $this->modelUtil->findModelInstanceByPk('tl_module', $submissionReader))) {
            return '';
        }

        $class = Module::findClass($moduleModel->type);

        if (!class_exists($class)) {
            return '';
        }

        // the form might be more than one time on the page
        $moduleModel->formHybridUseCustomFormIdSuffix = true;
        $moduleModel->formHybridCustomFormIdSuffix = ++static::$count;
        $existingDefaultValues = StringUtil::deserialize($moduleModel->formHybridDefaultValues, true);

        if (!empty($defaultValues)) {
            $moduleModel->formHybridAddDefaultValues = true;

            $newValues = [];

            foreach ($defaultValues as $value) {
                $newValues[] = ['field' => $value['submissionField'], 'value' => $item[$value['entityField']], 'label' => $value['submissionField']];
            }

            $moduleModel->formHybridDefaultValues = array_merge($existingDefaultValues, $newValues);
        }

        /** @var Module $module */
        $module = new $class($moduleModel);

        return $module->generate();
    }
}
