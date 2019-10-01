<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\Module;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Twig\Environment;

class SubmissionFormConfigElementType implements ListConfigElementTypeInterface
{
    const TYPE = 'submission_form';

    public static $recipientEmail;
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    private static $count = 0;

    public function __construct(ModelUtil $modelUtil, Environment $twig)
    {
        $this->modelUtil = $modelUtil;
        $this->twig = $twig;
    }

    public function addToItemData(ItemInterface $item, ListConfigElementModel $configElement)
    {
        // add email value to notification center tokens
        static::$recipientEmail = $item->getRawValue($configElement->emailField);
        $GLOBALS['TL_HOOKS']['formhybridBeforeCreateNotifications']['contao-list-bundle.addEmailToTokens'] = [static::class, 'addEmailToTokens'];

        // generate form
        $moduleId = $item->getModule()->id;

        $table = $item->getManager()->getFilterConfig()->getFilter()['dataContainer'];

        $identifier = $table.'_'.$moduleId.$item->getRawValue('id');

        $item->setFormattedValue($configElement->templateVariable ?: 'submissionForm', $this->twig->render('@HeimrichHannotContaoList/config_element/submission_form_modal_bootstrap4.html.twig', [
            'identifier' => $identifier,
            'item' => $this,
            'form' => $this->generateSubmissionReader((int) $configElement->submissionReader),
        ]));
    }

    public function addEmailToTokens(&$submissionData, $submission)
    {
        $submissionData['form_value_submission_form_email'] = static::$recipientEmail;

        return true;
    }

    public function generateSubmissionReader(int $submissionReader)
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

        /** @var Module $module */
        $module = new $class($moduleModel);

        return $module->generate();
    }

    /**
     * Return the list config element type alias.
     *
     * @return string
     */
    public static function getType(): string
    {
        return static::TYPE;
    }

    /**
     * Return the list config element type palette.
     *
     * @return string
     */
    public function getPalette(): string
    {
        return '{config_legend},submissionFormExplanation,submissionReader,emailField;';
    }

    /**
     * Update the item data.
     *
     * @param ListConfigElementData $configElementData
     */
    public function addToListItemData(ListConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getListConfigElement());
    }
}
