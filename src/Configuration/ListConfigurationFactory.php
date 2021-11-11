<?php

namespace HeimrichHannot\ListBundle\Configuration;

use Contao\Controller;
use HeimrichHannot\ListBundle\Model\ListConfigModel;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use http\Exception\InvalidArgumentException;

class ListConfigurationFactory
{
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var DcaUtil
     */
    private $dcaUtil;

    public function __construct(ModelUtil $modelUtil, DcaUtil $dcaUtil)
    {
        $this->modelUtil = $modelUtil;
        $this->dcaUtil = $dcaUtil;
    }

    public function createConfiguration($idOrAlias, ListConfigurationFactoryOptions $options = null): ListConfiguration
    {
        if (is_int($idOrAlias)) {
            $configuration = $this->createConfigurationFromModel($idOrAlias);
        } elseif (is_string($idOrAlias)) {
            $configuration = $this->createConfigurationFromConfig($idOrAlias);
        } else {
            throw new InvalidArgumentException('Parameter $idOrAlias must be of type int or string.');
        }

        if ($options->getParentTable() && $options->getParentId()) {
            $configuration->setParent($this->modelUtil->findModelInstanceByPk($options->getParentTable(), $options->getParentId()));
        }

        return $configuration;
    }

    private function createConfigurationFromModel(int $id): ListConfiguration
    {
        $listConfigModel = $this->getListConfigModel($id);
        if (!$listConfigModel) {
            throw new \Exception("List configuration not found.");
        }

        $configuration = new ListConfiguration();
        $configuration->setSource($listConfigModel);
        $configuration->setFilter((int)$listConfigModel->filter);
        $configuration->setShowInitialResults((bool)$listConfigModel->showInitialResults);

        return $configuration;
    }

    private function createConfigurationFromConfig(string $alias): ListConfiguration
    {
        $configuration = new ListConfiguration();
        return $configuration;
    }

    public function getListConfigModel(int $listConfigId): ?ListConfigModel
    {
        $listConfig = ListConfigModel::findByPk($listConfigId);

        if (!$listConfig) {
            return null;
        }

        $listConfig->rootId = $listConfig->id;

        if (!$listConfig->pid) {
            return $listConfig;
        }

        $computedListConfig = new ListConfigModel();

        $parentListConfigs = $this->modelUtil->findParentsRecursively(
            'pid',
            'tl_list_config',
            $listConfig
        );

        $rootListConfig = $this->modelUtil->findRootParentRecursively(
            'pid',
            'tl_list_config',
            $listConfig
        );

        Controller::loadDataContainer('tl_list_config');

        foreach ($GLOBALS['TL_DCA']['tl_list_config']['fields'] as $field => $data) {
            if ($data['eval']['notOverridable']) {
                $computedListConfig->{$field} = $rootListConfig->{$field};
            } else {
                $computedListConfig->{$field} = $this->dcaUtil->getOverridableProperty(
                    $field,
                    array_merge($parentListConfigs, [$listConfig])
                );
            }
        }

        $computedListConfig->id = $listConfigId;
        $computedListConfig->rootId = $rootListConfig->id;

        return $computedListConfig;
    }


}