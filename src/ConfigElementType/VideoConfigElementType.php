<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\UtilsBundle\Image\ImageUtil;

class VideoConfigElementType implements ListConfigElementTypeInterface
{
    const TYPE = 'video';

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;
    /**
     * @var ImageUtil
     */
    private $imageUtil;

    public function __construct(ContaoFrameworkInterface $framework, ImageUtil $imageUtil)
    {
        $this->framework = $framework;
        $this->imageUtil = $imageUtil;
    }

    /**
     * @param $item
     */
    public function addToItemData($item, ListConfigElementModel $listConfigElement)
    {
        $video = null;

        if (!$listConfigElement->videoField || !$item->getRawValue($listConfigElement->videoField)) {
            return;
        }

        $video = $item->getRawValue($listConfigElement->videoField);

        /** @var FilesModel $filesModel */
        $filesModel = $this->framework->getAdapter(FilesModel::class);

        // support for multifileupload
        $video = StringUtil::deserialize($video);

        if (\is_array($video)) {
            $video = array_values($video)[0];
        }

        if (null === ($videoFile = $filesModel->findByUuid($video))) {
            $uuid = StringUtil::deserialize($video, true)[0];
            $videoFile = $filesModel->findByUuid($uuid);
        }

        if (null === $videoFile) {
            return;
        }

        $projectDir = System::getContainer()->get('huh.utils.container')->getProjectDir();

        if (!file_exists($projectDir.\DIRECTORY_SEPARATOR.$videoFile->path)) {
            return;
        }

        $videoData = $this->getVideoConfig($listConfigElement, $videoFile);

        if ($listConfigElement->videoSize && (null !== ($size = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_image_size', StringUtil::deserialize($listConfigElement->videoSize, true)[0])))) {
            $videoData['size'] = sprintf(' width="%s" height="%s" ', $size->width, $size->height);
        }

        $itemData = $item->getRaw();
        $itemData['size'] = $listConfigElement->videoSize;

        if ($listConfigElement->posterImageField && $item->getRawValue($listConfigElement->posterImageField)) {
            $this->imageUtil->addToTemplateData($listConfigElement->posterImageField, '', $videoData['posterImg'], $itemData);
        }

        $item->setFormattedValue($listConfigElement->templateVariable ?: 'video', $videoData);
    }

    public function getVideoConfig(ListConfigElementModel $listConfigElement, FilesModel $video): array
    {
        return [
            'autoplay' => $listConfigElement->autoplay,
            'loop' => $listConfigElement->loop,
            'controls' => $listConfigElement->controls,
            'muted' => $listConfigElement->muted,
            'files' => [
                $video,
            ],
            'posterImg' => [],
        ];
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
        return '{config_legend},videoField,videoSize,posterImageField,autoplay,loop,controls,muted;';
    }

    /**
     * Update the item data.
     */
    public function addToListItemData(ListConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getListConfigElement());
    }
}
