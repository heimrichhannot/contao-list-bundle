<?php

namespace HeimrichHannot\ListBundle\Util;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\Model;
use Contao\System;
use HeimrichHannot\UtilsBundle\Util\Utils;

class DCUtil
{
    /**
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Util/Container/ContainerUtil.php#L73}
     */
    public static function isTableUsingDcMultilingual(string $table): bool
    {
        if (empty($GLOBALS['TL_DCA'][$table])) {
            /** @var Adapter<Controller> $ctrl */
            $ctrl = System::getContainer()->get('contao.framework')->getAdapter(Controller::class);
            $ctrl->loadDataContainer($table);
        }

        $kernelBundles = System::getContainer()->getParameter('kernel.bundles');

        $dcMultilingualBundleName = 'Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle';

        return isset($GLOBALS['TL_DCA'][$table]['config']['dataContainer'])
            && 'Multilingual' === $GLOBALS['TL_DCA'][$table]['config']['dataContainer']
            && (in_array($dcMultilingualBundleName, array_values($kernelBundles))
                || in_array($dcMultilingualBundleName, array_keys($kernelBundles)));
    }

    /**
     * Retrieves a property of given contao model instances by *ascending* priority, i.e. the last instance of $instances
     * will have the highest priority.
     *
     * CAUTION: This function assumes that you have used addOverridableFields() in this class!! That means, that a value in a
     * model instance is only used if it's either the first instance in $arrInstances or "overrideFieldname" is set to true
     * in the instance.
     *
     * @param string $property  The property name to retrieve
     * @param array  $instances An array of instances in ascending priority. Instances can be passed in the following form:
     *                          ['tl_some_table', $instanceId] or $objInstance
     *
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Dca/DcaUtil.php#L579}
     */
    public static function getOverridableProperty(string $property, array $instances): mixed
    {
        $result = null;
        $preparedInstances = [];

        $modelUtil = System::getContainer()->get(Utils::class)->model();

        // prepare instances
        foreach ($instances as $instance) {
            if (is_array($instance)) {
                if (null !== ($objInstance = $modelUtil->findModelInstanceByPk($instance[0], $instance[1]))) {
                    $preparedInstances[] = $objInstance;
                }
            } elseif ($instance instanceof Model || \is_object($instance)) {
                $preparedInstances[] = $instance;
            }
        }

        foreach ($preparedInstances as $i => $preparedInstance) {
            if (0 == $i || $preparedInstance->{'override'.ucfirst($property)}) {
                $result = $preparedInstance->{$property};
            }
        }

        return $result;
    }
}