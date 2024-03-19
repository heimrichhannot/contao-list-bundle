<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Database;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use Exception;
use HeimrichHannot\ListBundle\Backend\ListConfigElement;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\ListBundle\Util\Polyfill;
use HeimrichHannot\UtilsBundle\Util\Utils;

class ImageConfigElementType implements ListConfigElementTypeInterface
{
    const TYPE = 'image';
    const RANDOM_IMAGE_PLACEHOLDERS_SESSION_KEY = 'huh.random-image-placeholders';
    const LIST_CONFIG_ELEMENT_TEMPLATE_CONTAINER_VARIABLE = 'images';

    protected ContaoFramework $framework;
    protected InsertTagParser $insertTagParser;
    protected Utils $utils;

    public function __construct(
        ContaoFramework $framework,
        InsertTagParser $insertTagParser,
        Utils $utils
    ) {
        $this->framework = $framework;
        $this->insertTagParser = $insertTagParser;
        $this->utils = $utils;
    }

    /**
     * @throws Exception
     */
    public function addToItemData(ItemInterface $item, ListConfigElementModel $listConfigElement): void
    {
        $image = null;
        $validImageType = $this->isValidImageType($item, $listConfigElement);

        if ($listConfigElement->imageSelectorField && $item->getRawValue($listConfigElement->imageSelectorField)
            && $item->getRawValue($listConfigElement->imageField) && $validImageType)
        {
            $imageSelectorField = $listConfigElement->imageSelectorField;
            $image = $item->getRawValue($listConfigElement->imageField);
            $imageField = $listConfigElement->imageField;
        }
        elseif (!$listConfigElement->imageSelectorField && $listConfigElement->imageField && $item->getRawValue($listConfigElement->imageField) && $validImageType)
        {
            $imageSelectorField = '';
            $image = $item->getRawValue($listConfigElement->imageField);
            $imageField = $listConfigElement->imageField;
        }
        elseif ($listConfigElement->placeholderImageMode)
        {
            $imageSelectorField = $listConfigElement->imageSelectorField;
            $imageField = $listConfigElement->imageField;

            switch ($listConfigElement->placeholderImageMode) {
                case ListConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED:
                    $image = $this->getGenderedPlaceholderImage($item, $listConfigElement);

                    break;

                case ListConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE:
                    $image = $listConfigElement->placeholderImage;

                    break;

                case ListConfigElement::PLACEHOLDER_IMAGE_MODE_RANDOM:
                    $images = StringUtil::deserialize($listConfigElement->placeholderImages, true);

                    $session = System::getContainer()->get('request_stack')->getSession();

                    $randomImagePlaceholders = [];

                    if (!$session->has(static::RANDOM_IMAGE_PLACEHOLDERS_SESSION_KEY)) {
                        $session->set(static::RANDOM_IMAGE_PLACEHOLDERS_SESSION_KEY, $randomImagePlaceholders);
                    } else {
                        $randomImagePlaceholders = $session->get(static::RANDOM_IMAGE_PLACEHOLDERS_SESSION_KEY);
                    }

                    $dataContainer = System::getContainer()->get('huh.list.manager.list')->getFilterConfig()->getFilter()['dataContainer'];

                    $key = $dataContainer.'_'.$item->getRawValue('id');

                    if (isset($randomImagePlaceholders[$key])) {
                        $image = $randomImagePlaceholders[$key];
                    } elseif (null !== ($randomKey = array_rand($images))) {
                        $image = $randomImagePlaceholders[$key] = $images[$randomKey];
                    }

                    $session->set(static::RANDOM_IMAGE_PLACEHOLDERS_SESSION_KEY, $randomImagePlaceholders);

                    break;

                case ListConfigElement::PLACEHOLDER_IMAGE_MODE_FIELD:
                    $placeholderConfig = StringUtil::deserialize($listConfigElement->fieldDependentPlaceholderConfig, true);
                    if (empty($placeholderConfig)) {
                        return;
                    }

                    foreach ($placeholderConfig as $config)
                    {
                        if (!Polyfill::compareValue($config['operator'], $item->{$config['field']}, $this->insertTagParser->parse($config['value'])))
                        {
                            continue;
                        }

                        $image = $config['placeholderImage'];
                    }
            }
        }
        else
        {
            return;
        }

        /** @var FilesModel $filesModel */
        $filesModel = $this->framework->getAdapter(FilesModel::class);

        // support for multifileupload
        $image = StringUtil::deserialize($image);

        if (is_array($image)) {
            $image = array_values($image)[0];
        }

        if (null === ($imageFile = $filesModel->findByUuid($image))) {
            $uuid = StringUtil::deserialize($image, true)[0];
            $imageFile = $filesModel->findByUuid($uuid);
        }

        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        if (null !== $imageFile && file_exists($projectDir.'/'.$imageFile->path) && (getimagesize($projectDir.'/'.$imageFile->path) || 'svg' === strtolower($imageFile->extension))) {
            $imageArray = $item->getRaw();

            // Override the default image size
            if ('' != $listConfigElement->imgSize) {
                $size = StringUtil::deserialize($listConfigElement->imgSize);

                if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                    $imageArray['size'] = $listConfigElement->imgSize;
                }
            }

            $imageArray[$imageField] = $imageFile->path;

            $imageArray['fullsize'] = $listConfigElement->openImageInLightbox;

            $templateContainer = $this->getTemplateContainerVariable($listConfigElement);
            $templateVariable = $listConfigElement->templateVariable ?: $imageField;

            if (in_array($templateContainer, Database::getInstance()->getFieldNames($item->getDataContainer()))) {
                throw new Exception('Contao List Bundle: You specified that images of a list config element should be added to an array called "'.$templateContainer.'" in your list config element ID '.$listConfigElement->id.'. The associated DCA '.$item->getDataContainer().' contains a field of the same name which isn\'t supported. Please adjust the template container variable name in the list config element to be different from "'.$templateContainer.'".');
            }

            $templateData = [];
            $templateData[$templateContainer] = $item->getFormattedValue($templateContainer) ?: [];
            $templateData[$templateContainer][$templateVariable] = [];

            Polyfill::addImageToTemplateData(
                $imageField,
                $imageSelectorField,
                $templateData[$templateContainer][$templateVariable],
                $imageArray,
                null,
                null,
                null,
                $imageFile
            );

            $item->setFormattedValue($templateContainer, $templateData[$templateContainer]);
        }
    }

    public function getTemplateContainerVariable(ListConfigElementModel $listConfigElement): string
    {
        if (!$listConfigElement->overrideTemplateContainerVariable || !$listConfigElement->templateContainerVariable) {
            return static::LIST_CONFIG_ELEMENT_TEMPLATE_CONTAINER_VARIABLE;
        }

        return $listConfigElement->templateContainerVariable;
    }

    public function getGenderedPlaceholderImage(ItemInterface $item, ListConfigElementModel $listConfigElement): string
    {
        if ($item->getRawValue($listConfigElement->genderField) && 'female' == $item->getRawValue($listConfigElement->genderField)) {
            $image = $listConfigElement->placeholderImageFemale;
        } else {
            $image = $listConfigElement->placeholderImage;
        }

        return $image;
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
    public function getPalette(): string
    {
        return '{config_legend},overrideTemplateContainerVariable,imageSelectorField,imageField,imgSize,placeholderImageMode,openImageInLightbox;';
    }

    /**
     * Update the item data.
     *
     * @throws Exception
     */
    public function addToListItemData(ListConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getListConfigElement());
    }

    /**
     * @throws Exception
     */
    protected function isValidImageType(ItemInterface $item, ListConfigElementModel $listConfigElement): bool
    {
        if (!$listConfigElement->imageField || !$item->getRawValue($listConfigElement->imageField)) {
            return false;
        }

        $uuid = StringUtil::deserialize($item->getRawValue($listConfigElement->imageField), true)[0];
        $fileModel = FilesModel::findBy(['uuid=?'], [$uuid]);

        return in_array($fileModel->extension, explode(',', Config::get('validImageTypes')), true);
    }
}