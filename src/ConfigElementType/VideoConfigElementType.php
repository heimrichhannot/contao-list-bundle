<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\ConfigElementType;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ListBundle\Model\ListConfigElementModel;
use HeimrichHannot\ListBundle\Util\Polyfill;
use HeimrichHannot\UtilsBundle\Util\Utils;

class VideoConfigElementType implements ListConfigElementTypeInterface
{
    const TYPE = 'video';

    protected ContaoFramework $framework;
    protected Utils $utils;

    public function __construct(
        ContaoFramework $framework,
        Utils           $utils,
    ) {
        $this->framework = $framework;
        $this->utils = $utils;
    }

    /**
     * @param $item
     */
    public function addToItemData($item, ListConfigElementModel $listConfigElement): void
    {
        if (!$listConfigElement->videoField || !$item->getRawValue($listConfigElement->videoField)) {
            return;
        }

        $video = $item->getRawValue($listConfigElement->videoField);

        /** @var FilesModel $filesModel */
        $filesModel = $this->framework->getAdapter(FilesModel::class);

        // support for multifileupload
        $video = StringUtil::deserialize($video);

        if (is_array($video)) {
            $video = array_values($video)[0];
        }

        if (null === ($videoFile = $filesModel->findByUuid($video))) {
            $uuid = StringUtil::deserialize($video, true)[0];
            $videoFile = $filesModel->findByUuid($uuid);
        }

        if (null === $videoFile) {
            return;
        }

        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        if (!file_exists($projectDir . DIRECTORY_SEPARATOR . $videoFile->path)) {
            return;
        }

        $videoData = $this->getVideoConfig($listConfigElement, $videoFile);

        $imageSizePk = StringUtil::deserialize($listConfigElement->videoSize, true)[0];
        $size = $this->utils->model()->findModelInstanceByPk('tl_image_size', $imageSizePk);

        if ($listConfigElement->videoSize && null !== $size) {
            $videoData['size'] = sprintf(' width="%s" height="%s" ', $size->width, $size->height);
        }

        $itemData = $item->getRaw();
        $itemData['size'] = $listConfigElement->videoSize;

        if ($listConfigElement->posterImageField && $item->getRawValue($listConfigElement->posterImageField)) {
            Polyfill::addImageToTemplateData($listConfigElement->posterImageField, '', $videoData['posterImg'], $itemData);
        }

        $item->setFormattedValue($listConfigElement->templateVariable ?: 'video', $videoData);
    }

    public function getVideoConfig(ListConfigElementModel $listConfigElement, FilesModel $video): array
    {
        return [
            'autoplay' => $listConfigElement->addAutoplay,
            'loop' => $listConfigElement->addLoop,
            'controls' => $listConfigElement->addControls,
            'muted' => $listConfigElement->addMuted,
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
        return '{config_legend},videoField,videoSize,posterImageField,addAutoplay,addLoop,addControls,addMuted;';
    }

    /**
     * Update the item data.
     */
    public function addToListItemData(ListConfigElementData $configElementData): void
    {
        $this->addToItemData($configElementData->getItem(), $configElementData->getListConfigElement());
    }
}
