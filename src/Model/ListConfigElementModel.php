<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Model;

use Contao\Model;

/**
 * Class ListConfigElementModel.
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $dateAdded
 * @property string $title
 * @property string $type
 * @property string $templateVariable
 * @property string $imageSelectorField
 * @property string $imgSize
 * @property string $placeholderImage
 * @property string $placeholderImageFemale
 * @property string $genderField
 */
class ListConfigElementModel extends Model
{
    protected static $strTable = 'tl_list_config_element';
}
