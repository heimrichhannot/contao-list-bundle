<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ListBundle\Backend\ListConfigElement;
use HeimrichHannot\ListBundle\Item\ItemInterface;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;

class ImageConfigElementType implements ListConfigElementTypeInterface
{
    const TYPE = 'image';
    const RANDOM_IMAGE_PLACEHOLDERS_SESSION_KEY = 'huh.random-image-placeholders';
    const LIST_CONFIG_ELEMENT_TEMPLATE_CONTAINER_VARIABLE = 'images';

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function addToItemData(ItemInterface $item, ListConfigElementModel $listConfigElement)
    {
        $image = null;
        $validImageType = $this->isValidImageType($item, $listConfigElement);

        if ($listConfigElement->imageSelectorField && $item->getRawValue($listConfigElement->imageSelectorField)
            && $item->getRawValue($listConfigElement->imageField) && $validImageType) {
            $imageSelectorField = $listConfigElement->imageSelectorField;
            $image = $item->getRawValue($listConfigElement->imageField);
            $imageField = $listConfigElement->imageField;
        } elseif (!$listConfigElement->imageSelectorField && $listConfigElement->imageField && $item->getRawValue($listConfigElement->imageField) && $validImageType) {
            $imageSelectorField = '';
            $image = $item->getRawValue($listConfigElement->imageField);
            $imageField = $listConfigElement->imageField;
        } elseif ($listConfigElement->placeholderImageMode) {
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

                    $session = System::getContainer()->get('session');

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
                    if (empty($placeholderConfig = StringUtil::deserialize($listConfigElement->fieldDependentPlaceholderConfig,
                        true))) {
                        return;
                    }

                    foreach ($placeholderConfig as $config) {
                        if (!System::getContainer()->get('huh.utils.comparison')->compareValue($config['operator'],
                            $item->{$config['field']}, Controller::replaceInsertTags($config['value']))) {
                            continue;
                        }

                        $image = $config['placeholderImage'];
                    }
            }
        } else {
            return;
        }

        /** @var FilesModel $filesModel */
        $filesModel = $this->framework->getAdapter(FilesModel::class);

        // support for multifileupload
        $image = StringUtil::deserialize($image);

        if (\is_array($image)) {
            $image = array_values($image)[0];
        }

        if (null === ($imageFile = $filesModel->findByUuid($image))) {
            $uuid = StringUtil::deserialize($image, true)[0];
            $imageFile = $filesModel->findByUuid($uuid);
        }

        $projectDir = System::getContainer()->get('huh.utils.container')->getProjectDir();

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

            $templateData = [];
            $templateData[$templateContainer] = $item->getFormattedValue($templateContainer) ?: [];
            $templateData[$templateContainer][$templateVariable] = [];

            System::getContainer()->get('huh.utils.image')->addToTemplateData($imageField, $imageSelectorField,
                $templateData[$templateContainer][$templateVariable], $imageArray, null, null,
                null, $imageFile);

            $item->setFormattedValue($templateContainer, $templateData[$templateContainer]);
        }
    }

    public function getTemplateContainerVariable(ListConfigElementModel $listConfigElement): string
    {
        if (!$listConfigElement->overrideTemplateContainerVariable || !$listConfigElement->templateContainerVariable) {
            return self::LIST_CONFIG_ELEMENT_TEMPLATE_CONTAINER_VARIABLE;
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
     */
    public function addToListItemData(ListConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getListConfigElement());
    }

    /**
     * @throws \Exception
     */
    protected function isValidImageType(ItemInterface $item, ListConfigElementModel $listConfigElement): bool
    {
        if (!$listConfigElement->imageField || !$item->getRawValue($listConfigElement->imageField)) {
            return false;
        }

        $uuid = StringUtil::deserialize($item->getRawValue($listConfigElement->imageField), true)[0];

        if (null === ($file = System::getContainer()->get('huh.utils.file')->getFileFromUuid($uuid))) {
            return false;
        }

        return \in_array($file->getModel()->extension, explode(',', Config::get('validImageTypes')), true);
    }
}
