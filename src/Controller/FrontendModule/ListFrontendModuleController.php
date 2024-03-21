<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Controller\FrontendModule;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use HeimrichHannot\ListBundle\Asset\FrontendAsset;
use HeimrichHannot\ListBundle\Event\ListCompileEvent;
use HeimrichHannot\ListBundle\Exception\InterfaceNotImplementedException;
use HeimrichHannot\ListBundle\Exception\InvalidListConfigException;
use HeimrichHannot\ListBundle\ListConfiguration\ListConfiguration;
use HeimrichHannot\ListBundle\Lists\ListInterface;
use HeimrichHannot\ListBundle\Manager\ListManager;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use HeimrichHannot\ListBundle\Util\ListManagerUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @FrontendModule(ListFrontendModuleController::TYPE, category="application", template="mod_list")
 */
class ListFrontendModuleController extends AbstractFrontendModuleController
{
    const TYPE = 'huhlist';

    private ListConfigRegistry $listConfigRegistry;
    private ListManagerUtil    $listManagerUtil;
    private FrontendAsset      $frontendAsset;
    private EventDispatcherInterface    $eventDispatcher;

    public function __construct(ListConfigRegistry $listConfigRegistry, ListManagerUtil $listManagerUtil, FrontendAsset $frontendAsset, EventDispatcherInterface $eventDispatcher)
    {
        $this->listConfigRegistry = $listConfigRegistry;
        $this->listManagerUtil = $listManagerUtil;
        $this->frontendAsset = $frontendAsset;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $listConfiguration = $this->getListConfiguration($model, $request);

        // Hide list and show reader on detail pages if configured
        if ('1' === $model->list_renderReaderOnAutoItem && $model->list_readerModule && (Config::get('useAutoItem') && isset($_GET['auto_item']))) {
            return new Response(Controller::getFrontendModule($model->list_readerModule, $template->inColumn));
        }

        // retrieve list config
        if (!$listConfiguration) {
            $listConfigModel = $this->listConfigRegistry->getComputedListConfig((int) $model->listConfig);
        } else {
            $listConfigModel = $listConfiguration->getListConfigModel();
        }

        /** @var ListManager $listManager */
        $listManager = $this->listManagerUtil->getListManagerByName($listConfigModel->manager ?: 'default');

        if (!$listManager) {
            return $template->getResponse();
        }

        $listManager->setListConfig($listConfigModel);
        $listManager->setModuleData($model->row());

        $dataContainer = $listConfiguration
            ? $listConfiguration->getDataContainer()
            : $listManager->getFilterConfig()->getFilter()['dataContainer'];

        Controller::loadDataContainer('tl_list_config');
        Controller::loadDataContainer($dataContainer);
        Controller::loadLanguageFile($dataContainer);

        if (null !== ($listClass = $listManager->getListByName($listConfigModel->list ?: 'default'))) {
            $reflection = new \ReflectionClass($listClass);

            if (!$reflection->implementsInterface(ListInterface::class)) {
                throw new InterfaceNotImplementedException(ListInterface::class, $listClass);
            }

            if (!$reflection->implementsInterface(\JsonSerializable::class)) {
                throw new InterfaceNotImplementedException(\JsonSerializable::class, $listClass);
            }

            $list = new $listClass($listManager);
            $listManager->setList($list);

        } else {
            if ($this->container->has('parameter_bag')
                && $this->container->get('parameter_bag')->has('kernel.environment')
                && 'dev' === $this->container->get('parameter_bag')->get('kernel.environment')
            ) {
                throw new InvalidListConfigException('Could not create list class due invalid list type!');
            }

            return new Response();
        }

        $this->frontendAsset->addFrontendAssets();

        $list->handleShare($listConfiguration);

        // add class to every list template
        $template->class = trim($template->class.' huh-list '.$listConfiguration->getDataContainer());
        $template->noSearch = (bool) $listConfigModel->noSearch;

        $template->list = function (string $listTemplate = null, string $itemTemplate = null, array $data = []) use ($list, $listConfiguration) {
            return $list->parse($listTemplate, $itemTemplate, $data, $listConfiguration);
        };

        $this->eventDispatcher->dispatch(
            new ListCompileEvent($template, $this, $listConfigModel),
            ListCompileEvent::NAME
        );

        $response = $template->getResponse();

        if ((bool) $listConfigModel->doNotRenderEmpty
            && empty($list->getItems())) {
            return new Response();
        }

        return $response;
    }

    protected function getListConfiguration(ModuleModel $model, Request $request): ?ListConfiguration
    {
        return null;
    }
}
