<?php

namespace HeimrichHannot\ListBundle\Util;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\File;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Exception;
use HeimrichHannot\UtilsBundle\Util\Utils;
use InvalidArgumentException;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Throwable;

class Polyfill
{
    const PHP_OPERATOR_EQUAL = 'equal';
    const PHP_OPERATOR_UNEQUAL = 'unequal';
    const PHP_OPERATOR_LIKE = 'like';
    const PHP_OPERATOR_UNLIKE = 'unlike';
    const PHP_OPERATOR_IN_ARRAY = 'inarray';
    const PHP_OPERATOR_NOT_IN_ARRAY = 'notinarray';
    const PHP_OPERATOR_LOWER = 'lower';
    const PHP_OPERATOR_LOWER_EQUAL = 'lowerequal';
    const PHP_OPERATOR_GREATER = 'greater';
    const PHP_OPERATOR_GREATER_EQUAL = 'greaterequal';
    const PHP_OPERATOR_IS_NULL = 'isnull';
    const PHP_OPERATOR_IS_NOT_NULL = 'isnotnull';

    /**
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Comparison/CompareUtil.php#L61}
     */
    public static function compareValue(string $operator, $value1, $value2 = null): bool
    {
        $value = match ($operator) {
            static::PHP_OPERATOR_IS_NULL => null === $value1,
            static::PHP_OPERATOR_IS_NOT_NULL => null !== $value1,
            default => null,
        };

        if ($value !== null) {
            return $value;
        }

        if (!$value1 || !$value2) {
            return false;
        }

        return match ($operator) {
            static::PHP_OPERATOR_EQUAL => $value1 == $value2,
            static::PHP_OPERATOR_UNEQUAL => $value1 != $value2,
            static::PHP_OPERATOR_LIKE => str_contains($value1, $value2),
            static::PHP_OPERATOR_UNLIKE => !str_contains($value1, $value2),
            static::PHP_OPERATOR_IN_ARRAY => in_array($value2, $value1),
            static::PHP_OPERATOR_NOT_IN_ARRAY => !in_array($value2, $value1),
            static::PHP_OPERATOR_LOWER => $value1 < $value2,
            static::PHP_OPERATOR_LOWER_EQUAL => $value1 <= $value2,
            static::PHP_OPERATOR_GREATER => $value1 > $value2,
            static::PHP_OPERATOR_GREATER_EQUAL => $value1 >= $value2,
            default => throw new InvalidArgumentException('Invalid operator'),
        };
    }

    /**
     * Adds an override selector to every field in $fields to the dca associated with $destinationTable.
     *
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Dca/DcaUtil.php#L517}
     */
    public static function addOverridableFields(array $fields, string $sourceTable, string $destinationTable, array $options = []): void
    {
        $framework = System::getContainer()->get('contao.framework');

        $framework->getAdapter(Controller::class)->loadDataContainer($sourceTable);
        System::loadLanguageFile($sourceTable);
        $sourceDca = $GLOBALS['TL_DCA'][$sourceTable];

        $framework->getAdapter(Controller::class)->loadDataContainer($destinationTable);
        System::loadLanguageFile($destinationTable);
        $destinationDca = &$GLOBALS['TL_DCA'][$destinationTable];

        foreach ($fields as $field) {
            // add override boolean field
            $overrideFieldname = 'override'.ucfirst($field);

            $destinationDca['fields'][$overrideFieldname] = [
                'label' => &$GLOBALS['TL_LANG'][$destinationTable][$overrideFieldname],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => ['tl_class' => 'w50', 'submitOnChange' => true, 'isOverrideSelector' => true],
                'sql' => "char(1) NOT NULL default ''",
            ];

            if (isset($options['checkboxDcaEvalOverride']) && is_array($options['checkboxDcaEvalOverride'])) {
                $destinationDca['fields'][$overrideFieldname]['eval'] = array_merge($destinationDca['fields'][$overrideFieldname]['eval'], $options['checkboxDcaEvalOverride']);
            }

            // important: nested selectors need to be in reversed order -> see DC_Table::getPalette()
            $destinationDca['palettes']['__selector__'] = array_merge([$overrideFieldname], isset($destinationDca['palettes']['__selector__']) && is_array($destinationDca['palettes']['__selector__']) ? $destinationDca['palettes']['__selector__'] : []);

            // copy field
            $destinationDca['fields'][$field] = $sourceDca['fields'][$field];

            // subpalette
            $destinationDca['subpalettes'][$overrideFieldname] = $field;

            if (!isset($options['skipLocalization']) || !$options['skipLocalization']) {
                $translator = System::getContainer()->get('translator');
                $GLOBALS['TL_LANG'][$destinationTable][$overrideFieldname] = [
                    $translator->trans('huh.utils.misc.override.label', [
                        '%fieldname%' => $GLOBALS['TL_DCA'][$sourceTable]['fields'][$field]['label'][0] ?? $field,
                    ]),
                    $translator->trans('huh.utils.misc.override.desc', [
                        '%fieldname%' => $GLOBALS['TL_DCA'][$sourceTable]['fields'][$field]['label'][0] ?? $field,
                    ]),
                ];
            }
        }
    }

    /**
     * Set initial $varData from dca.
     *
     * @param string $strTable Dca table name
     * @param mixed  $varData  Object or array
     *
     * @return mixed Object or array with the default values
     *
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Dca/DcaUtil.php#L302}
     */
    public static function setDefaultsFromDca(string $strTable, mixed $varData = null, bool $includeSql = false): mixed
    {
        $framework = System::getContainer()->get('contao.framework');
        $framework->getAdapter(Controller::class)->loadDataContainer($strTable);

        if (empty($GLOBALS['TL_DCA'][$strTable])) {
            return $varData;
        }

        $dbFields = [];

        foreach (Database::getInstance()->listFields($strTable) as $data) {
            if (!isset($data['default'])) {
                continue;
            }

            $dbFields[$data['name']] = $data['default'];
        }

        // Get all default values for the new entry
        foreach ($GLOBALS['TL_DCA'][$strTable]['fields'] as $k => $v) {
            $addDefaultValue = false;
            $defaultValue = null;

            // check sql definition
            if ($includeSql && isset($dbFields[$k])) {
                $addDefaultValue = true;
                $defaultValue = $dbFields[$k];
            }

            // check dca default value
            if (array_key_exists('default', $v)) {
                $addDefaultValue = true;
                $defaultValue = is_array($v['default']) ? serialize($v['default']) : $v['default'];
            }

            if (!$addDefaultValue) {
                continue;
            }

            // Encrypt the default value (see #3740)
            if ($GLOBALS['TL_DCA'][$strTable]['fields'][$k]['eval']['encrypt'] ?? false) {
                $defaultValue = static::encrypt($defaultValue);
            }

            if (is_object($varData)) {
                $varData->{$k} = $defaultValue;
            } else {
                if (null === $varData) {
                    $varData = [];
                }
                if (is_array($varData)) {
                    $varData[$k] = $defaultValue;
                }
            }
        }

        return $varData;
    }

    /**
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Security/EncryptionUtil.php#L24}
     */
    public static function encrypt(string $plain, string $key = '', string $cipher = 'aes-256-ctr', $options = 0): array|false
    {
        $key = '' !== $key ? $key : System::getContainer()->getParameter('secret');

        if (in_array($cipher, openssl_get_cipher_methods())) {
            $ivLength = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivLength);

            return [openssl_encrypt($plain, $cipher, $key, $options, $iv), base64_encode($iv)];
        }

        return false;
    }

    /**
     * Add an image to a template.
     *
     * Advanced version of Controller::addImageToTemplate
     * with custom imageField and imageSelectorField and array instead of FrontendTemplate.
     *
     * @param string          $imageField         the image field name (typical singleSRC)
     * @param string          $imageSelectorField the image selector field indicated if an image is added (typical addImage)
     * @param array           $templateData       An array to add the generated data to
     * @param array           $item               The source data containing the imageField and imageSelectorField
     * @param int|null        $maxWidth           An optional maximum width of the image
     * @param string|null     $lightboxId         An optional lightbox ID
     * @param string|null     $lightboxName       An optional lightbox name
     * @param FilesModel|null $model              an optional file model used to read meta data
     *
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Image/ImageUtil.php#L57}
     */
    public static function addImageToTemplateData(
        string $imageField,
        string $imageSelectorField,
        array &$templateData,
        array $item,
        int $maxWidth = null,
        string $lightboxId = null,
        string $lightboxName = null,
        FilesModel $model = null
    ): void {
        $utils = System::getContainer()->get(Utils::class);
        $projDir = System::getContainer()->getParameter('kernel.project_dir');

        try {
            if (Validator::isUuid($item[$imageField])) {
                $fileModel = FilesModel::findBy(['uuid=?'], [$item[$imageField]]);
                $file = new File($fileModel->path);
            } else {
                $file = new File($item[$imageField]);
            }
            $imgSize = $file->imageSize;
        } catch (Exception) {
            return;
        }

        if (null === $model) {
            $model = $file->getModel();
        }

        $size = StringUtil::deserialize($item['size'] ?? '');

        if (is_numeric($size)) {
            $size = [0, 0, (int) $size];
        } elseif (!is_array($size)) {
            $size = [];
        }

        $size += [0, 0, 'crop'];

        if (null === $maxWidth) {
            $maxWidth = ($utils->container()->isBackend()) ? 320 : Config::get('maxImageWidth');
        }

        // $marginArray = ($utils->container()->isBackend()) ? '' : StringUtil::deserialize($item['imagemargin'] ?? '');

        // Store the original dimensions
        $templateData['width'] = $imgSize[0] ?? 0;
        $templateData['height'] = $imgSize[1] ?? 0;

        // Adjust the image size
        if ($maxWidth > 0) {
            // Subtract the margins before deciding whether to resize (see #6018)
            //if (\is_array($marginArray) && 'px' == $marginArray['unit']) {
            //    $margin = (int) $marginArray['left'] + (int) $marginArray['right'];
            //
            //    // Reset the margin if it exceeds the maximum width (see #7245)
            //    if ($maxWidth - $margin < 1) {
            //        $marginArray['left'] = '';
            //        $marginArray['right'] = '';
            //    } else {
            //        $maxWidth -= $margin;
            //    }
            //}

            if ($size[0] > $maxWidth || (!$size[0] && !$size[1] && (!$imgSize[0] || $imgSize[0] > $maxWidth))) {
                // See #2268 (thanks to Thyon)
                $ratio = ($size[0] && $size[1]) ? $size[1] / $size[0] : (($imgSize[0] && $imgSize[1]) ? $imgSize[1] / $imgSize[0] : 0);

                $size[0] = $maxWidth;
                $size[1] = floor($maxWidth * $ratio);
            }
        }

        // Disable responsive images in the back end (see #7875)
        if ($utils->container()->isBackend()) {
            unset($size[2]);
        }

        $imageFile = $file;

        try {
            $src = System::getContainer()->get('contao.image.image_factory')->create($projDir.'/'.$file->path, $size)->getUrl($projDir);
            $picture = System::getContainer()->get('contao.image.picture_factory')->create($projDir.'/'.$file->path, $size);

            $picture = [
                'img' => $picture->getImg($projDir, TL_FILES_URL),
                'sources' => $picture->getSources($projDir, TL_FILES_URL),
                'ratio' => '1.0',
                'copyright' => $file->getModel()->copyright,
            ];

            if ($src !== $file->path) {
                $imageFile = new File(rawurldecode($src));
            }
        } catch (Exception $e) {
            /** @var Logger $logger */
            $logger = System::getContainer()->get('logger');
            $logger->log(
                LogLevel::ERROR,
                'Image "'.$file->path.'" could not be processed: '.$e->getMessage(),
                ['contao' => new ContaoContext(__METHOD__, 'ERROR')]
            );

            $src = '';
            $picture = ['img' => ['src' => '', 'srcset' => ''], 'sources' => []];
        }

        // Image dimensions
        $imgSize = $imageFile->imageSize;
        if ($imgSize && $imageFile->exists()) {
            $templateData['arrSize'] = $imgSize;
            $templateData['imgSize'] = ' width="'.$imgSize[0].'" height="'.$imgSize[1].'"';

            $picture['size'] = $imgSize;
            $picture['width'] = $imgSize[0];
            $picture['height'] = $imgSize[1];
            $picture['ratio'] = $imgSize[1] > 0 ? ($imgSize[0] / $imgSize[1]) : '1.0';
        }

        $meta = [];

        // Load the meta data
        if ($model instanceof FilesModel) {
            if ($utils->container()->isFrontend()) {
                global $objPage;

                $meta = Frontend::getMetaData($model->meta, $objPage->language);

                if (empty($meta) && null !== $objPage->rootFallbackLanguage) {
                    $meta = Frontend::getMetaData($model->meta, $objPage->rootFallbackLanguage);
                }
            } else {
                $meta = Frontend::getMetaData($model->meta, $GLOBALS['TL_LANGUAGE']);
            }

            System::getContainer()->get('contao.framework')->getAdapter(Controller::class)->loadDataContainer('tl_files');

            // Add any missing fields
            foreach (array_keys($GLOBALS['TL_DCA']['tl_files']['fields']['meta']['eval']['metaFields']) as $k) {
                if (!isset($meta[$k])) {
                    $meta[$k] = '';
                }
            }

            $meta['imageTitle'] = $meta['title'];
            $meta['imageUrl'] = $meta['link'];
            unset($meta['title'], $meta['link']);

            // Add the meta data to the item
            # fixme: looks like this works the opposite way it should at first glance
            if (!($item['overwriteMeta'] ?? false)) {
                foreach ($meta as $k => $v) {
                    $item[$k] = match ($k) {
                        'alt', 'imageTitle' => StringUtil::specialchars($v),
                        default => $v,
                    };
                }
            }
        }

        $picture['alt'] = StringUtil::specialchars($item['alt']);

        $fullsize = (bool) ($item['fullsize'] ?? false);

        // Move the title to the link tag so it is shown in the lightbox
        if ($fullsize && ($item['imageTitle'] ?? false) && !($item['linkTitle'] ?? false)) {
            $item['linkTitle'] = $item['imageTitle'];
            unset($item['imageTitle']);
        }

        if (isset($item['imageTitle'])) {
            $picture['title'] = StringUtil::specialchars($item['imageTitle']);
        }

        // empty the attributes in order to avoid passing the link attributes to the img element
        $picture['attributes'] = '';

        $templateData['picture'] = $picture;

        // Provide an ID for single lightbox images in HTML5 (see #3742)
        if (null === $lightboxId && $fullsize) {
            $lightboxId = substr(md5($lightboxName.'_'.$item['id']), 0, 6);
        }

        // Float image
        if ($item['floating'] ?? false) {
            $templateData['floatClass'] = ' float_'.$item['floating'];
        }

        // Do not override the "href" key (see #6468)
        $hrefKey = (isset($templateData['href']) && '' != $templateData['href']) ? 'imageHref' : 'href';

        // Image link
        if ($item['imageUrl'] && $utils->container()->isFrontend()) {
            $templateData[$hrefKey] = $item['imageUrl'];
            $templateData['attributes'] = '';

            if ($fullsize) {
                // Open images in the lightbox
                if (preg_match('/\.(jpe?g|gif|png)$/', $item['imageUrl'])) {
                    // Do not add the TL_FILES_URL to external URLs (see #4923)
                    if (0 !== strncmp($item['imageUrl'], 'http://', 7) && 0 !== strncmp($item['imageUrl'], 'https://', 8)) {
                        $templateData[$hrefKey] = TL_FILES_URL.System::urlEncode($item['imageUrl']);
                    }

                    $templateData['attributes'] = ' data-lightbox="'.$lightboxId.'"';
                } else {
                    $templateData['attributes'] = ' target="_blank"';
                }
            }
        } // Fullsize view
        elseif ($fullsize && $utils->container()->isFrontend()) {
            $templateData[$hrefKey] = TL_FILES_URL.System::urlEncode($file->path);
            $templateData['attributes'] = ' data-lightbox="'.$lightboxId.'"';
        }

        // Add the meta data to the template
        foreach (array_keys($meta) as $k) {
            $templateData[$k] = $item[$k] ?? null;
        }

        // Do not urlEncode() here because getImage() already does (see #3817)
        $templateData['src'] = TL_FILES_URL.$src;
        $templateData[$imageField] = $file->path;
        $templateData['linkTitle'] = $item['linkTitle'] ?? ($item['title'] ?? null);
        $templateData['fullsize'] = $fullsize;
        $templateData['addBefore'] = ('below' != ($item['floating'] ?? ''));
        $templateData['margin'] = ['top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'unit' => ''];
        //$templateData['margin'] = Controller::generateMargin($marginArray);
        $templateData[$imageSelectorField] = true;

        // HOOK: modify image template data
        if (isset($GLOBALS['TL_HOOKS']['addImageToTemplateData']) && is_array($GLOBALS['TL_HOOKS']['addImageToTemplateData'])) {
            foreach ($GLOBALS['TL_HOOKS']['addImageToTemplateData'] as $callback) {
                $templateData = System::importStatic($callback[0])->{$callback[1]}($templateData, $imageField, $imageSelectorField, $item, $maxWidth, $lightboxId, $lightboxName, $model);
            }
        }
    }
}