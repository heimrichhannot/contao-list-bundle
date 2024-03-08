<?php

namespace HeimrichHannot\ListBundle\Util;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\File;
use Contao\FilesModel;
use Contao\Frontend;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Error;
use Exception;
use HeimrichHannot\UtilsBundle\Util\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use InvalidArgumentException;
use JsonSerializable;
use Monolog\Logger;
use Psr\Log\LogLevel;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

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

    /**
     * This function transforms an entity's palette (that can also contain sub palettes and concatenated type selectors) to a flatten
     * palette where every field can be overridden.
     *
     * CAUTION: This function assumes that you have used addOverridableFields() for adding the fields that are overridable. The latter ones
     * are $overridableFields
     *
     * This function is useful if you want to adjust a palette for sub entities that can override properties of their ancestor(s).
     * Use $this->getOverridableProperty() for computing the correct value respecting the entity hierarchy.
     *
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Dca/DcaUtil.php#L614}
     */
    public static function flattenPaletteForSubEntities(string $table, array $overridableFields): void
    {
        $container = System::getContainer();
        $utils = $container->get(Utils::class);
        $framework = $container->get('contao.framework');

        $framework->getAdapter(Controller::class)->loadDataContainer($table);

        $pm = PaletteManipulator::create();

        $dca = &$GLOBALS['TL_DCA'][$table];
        $arrayUtil = $utils->array();

        // Contao 4.4 fix
        $replaceFields = [];

        // palette
        foreach ($overridableFields as $field) {
            if (true === ($dca['fields'][$field]['eval']['submitOnChange'] ?? false)) {
                unset($dca['fields'][$field]['eval']['submitOnChange']);

                if (\in_array($field, $dca['palettes']['__selector__'])) {
                    // flatten concatenated type selectors
                    foreach ($dca['subpalettes'] as $selector => $subPaletteFields) {
                        if (false !== strpos($selector, $field.'_')) {
                            if ($dca['subpalettes'][$selector]) {
                                $subPaletteFields = explode(',', $dca['subpalettes'][$selector]);

                                foreach (array_reverse($subPaletteFields) as $subPaletteField) {
                                    $pm->addField($subPaletteField, $field);
                                }
                            }

                            // remove nested field in order to avoid its normal "selector" behavior
                            $arrayUtil->removeValue($field, $dca['palettes']['__selector__']);
                            unset($dca['subpalettes'][$selector]);
                        }
                    }

                    // flatten sub palettes
                    if (isset($dca['subpalettes'][$field]) && $dca['subpalettes'][$field]) {
                        $subPaletteFields = explode(',', $dca['subpalettes'][$field]);

                        foreach (array_reverse($subPaletteFields) as $subPaletteField) {
                            $pm->addField($subPaletteField, $field);
                        }

                        // remove nested field in order to avoid its normal "selector" behavior
                        $arrayUtil->removeValue($field, $dca['palettes']['__selector__']);
                        unset($dca['subpalettes'][$field]);
                    }
                }
            }

            $replaceFields[] = $field;

            //            $pm->addField('override'.ucfirst($field), $field)->removeField($field);
        }

        $pm->applyToPalette('default', $table);

        foreach ($replaceFields as $replaceField) {
            $dca['palettes']['default'] = str_replace($replaceField, 'override'.ucfirst($replaceField), $dca['palettes']['default']);
        }
    }

    /**
     * Recursively finds the root parent.
     *
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Model/ModelUtil.php#L365}
     */
    public static function findRootParentRecursively(
        ModelUtil $modelUtil,
        string    $parentProperty,
        string    $table,
        ?Model    $instance,
        bool      $returnInstanceIfNoParent = true
    ): ?Model {
        if ($instance === null
            || !$instance->{$parentProperty}
            || null === $parentInstance = $modelUtil->findModelInstanceByPk($table, $instance->{$parentProperty}))
        {
            return $returnInstanceIfNoParent ? $instance : null;
        }

        return self::findRootParentRecursively($modelUtil, $parentProperty, $table, $parentInstance);
    }

    /**
     * Retrieves an array from a dca config (in most cases eval) in the following priorities:.
     *
     * 1. The value associated to $array[$property]
     * 2. The value retrieved by $array[$property . '_callback'] which is a callback array like ['Class', 'method'] or ['service.id', 'method']
     * 3. The value retrieved by $array[$property . '_callback'] which is a function closure array like ['Class', 'method']
     *
     * @return mixed|null The value retrieved in the way mentioned above or null
     *
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Dca/DcaUtil.php#L375}
     */
    public static function getConfigByArrayOrCallbackOrFunction(array $array, $property, array $arguments = [])
    {
        if (isset($array[$property])) {
            return $array[$property];
        }

        if (!isset($array[$property.'_callback'])) {
            return null;
        }

        if (is_array($array[$property.'_callback'])) {
            $callback = $array[$property.'_callback'];

            if (!isset($callback[0]) || !isset($callback[1])) {
                return null;
            }

            try {
                $instance = Controller::importStatic($callback[0]);
            } catch (Exception $e) {
                return null;
            }

            if (!method_exists($instance, $callback[1])) {
                return null;
            }

            try {
                return call_user_func_array([$instance, $callback[1]], $arguments);
            } catch (Error $e) {
                return null;
            }
        } elseif (is_callable($array[$property.'_callback'])) {
            try {
                return call_user_func_array($array[$property.'_callback'], $arguments);
            } catch (Error $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Serialize a class object to JSON by iterating over all public getters (get(), is(), ...).
     *
     * @throws ReflectionException if the class or method does not exist
     *
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Classes/ClassUtil.php#L172}
     */
    public static function jsonSerialize(object $object, array $data = [], array $options = []): array
    {
        $class = get_class($object);

        $rc = new ReflectionClass($object);

        // get values of properties
        if (isset($options['includeProperties']) && $options['includeProperties']) {
            foreach ($rc->getProperties() as $reflectionProperty) {
                $propertyName = $reflectionProperty->getName();

                $property = $rc->getProperty($propertyName);

                if (isset($options['ignorePropertyVisibility']) && $options['ignorePropertyVisibility']) {
                    $property->setAccessible(true);
                }

                $data[$propertyName] = $property->getValue($object);

                if (\is_object($data[$propertyName])) {
                    if (!($data[$propertyName] instanceof JsonSerializable)) {
                        unset($data[$propertyName]);

                        continue;
                    }

                    $data[$propertyName] = static::jsonSerialize($data[$propertyName]);
                }
            }
        }

        if (isset($options['ignoreMethods']) && $options['ignoreMethods']) {
            return $data;
        }

        // get values of methods
        if (isset($options['ignoreMethodVisibility']) && $options['ignoreMethodVisibility']) {
            $methods = $rc->getMethods();
        } else {
            $methods = $rc->getMethods(ReflectionMethod::IS_PUBLIC);
        }

        // add all public getter Methods
        foreach ($methods as $method) {

            $start = self::getMethodNameStartIndex($method, $rc);
            if ($start === null) continue;

            // skip methods with parameters
            $rm = new ReflectionMethod($class, $method->name);

            if ($rm->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            if (isset($options['skippedMethods']) && \is_array($options['skippedMethods']) && \in_array($method->name, $options['skippedMethods'])) {
                continue;
            }

            $property = lcfirst(substr($method->name, $start));

            if (!$method->isPublic()) {
                $method->setAccessible(true);
                $data[$property] = $method->invoke($object);
            } else {
                $data[$property] = $object->{$method->name}();
            }

            if (\is_object($data[$property])) {
                if (!($data[$property] instanceof JsonSerializable)) {
                    unset($data[$property]);

                    continue;
                }
                $data[$property] = static::jsonSerialize($data[$property]);
            }
        }

        return $data;
    }

    /**
     * @param ReflectionMethod $method
     * @param ReflectionClass $rc
     * @return int|null
     *
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Classes/ClassUtil.php#L128}
     */
    private static function getMethodNameStartIndex(ReflectionMethod $method, ReflectionClass $rc): ?int
    {
        $len = 3;
        $prefix = substr($method->name, 0, $len);

        /** vvv Prefixes with length 3. vvv */

        // get{MethodName}()
        if ('get' === $prefix)
            return $len;

        // has{MethodName}()
        if ('has' === $prefix) {
            $name = ucfirst(substr($method->name, 3, strlen($method->name)));
            if ($rc->hasMethod("is$name") || $rc->hasMethod("get$name"))
                return 0;
            return $len;
        }

        /** vvv Prefixes with length 2. vvv */

        $len = 2;
        $prefix = substr($method->name, 0, $len);

        // is{MethodName}()
        if ('is' === $prefix) {
            $name = ucfirst(substr($method->name, 2, strlen($method->name)));
            if ($rc->hasMethod("has$name") || $rc->hasMethod("get$name"))
                return 0;
            return  $len;
        }

        return null;
    }
}