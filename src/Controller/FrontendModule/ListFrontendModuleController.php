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
        // Hide list and show reader on detail pages if configured
        if ('1' === $model->list_renderReaderOnAutoItem && $model->list_readerModule && (Config::get('useAutoItem') && isset($_GET['auto_item']))) {
            return new Response(Controller::getFrontendModule($model->list_readerModule, $template->inColumn));
        }

        // retrieve list config
        $listConfig = $this->listConfigRegistry->getComputedListConfig((int) $model->listConfig);

        /** @var ListManager $listManager */
        $listManager = $this->listManagerUtil->getListManagerByName($listConfig->manager ?: 'default');

        if (!$listManager) {
            return $template->getResponse();
        }

        $listManager->setListConfig($listConfig);
        $listManager->setModuleData($model->row());
        $filterConfig = $listManager->getFilterConfig();

        Controller::loadDataContainer('tl_list_config');
        Controller::loadDataContainer($filterConfig->getFilter()['dataContainer']);
        Controller::loadLanguageFile($filterConfig->getFilter()['dataContainer']);

        if (null !== ($listClass = $listManager->getListByName($listConfig->list ?: 'default'))) {
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

        $list->handleShare();

        // add class to every list template
        $template->class = trim($template->class.' huh-list '.$list->getDataContainer());
        $template->noSearch = (bool) $listConfig->noSearch;

        $template->list = function (string $listTemplate = null, string $itemTemplate = null, array $data = []) use ($list) {
            return $list->parse($listTemplate, $itemTemplate, $data);
        };

        $this->eventDispatcher->dispatch(
            new ListCompileEvent($template, $this, $listConfig),
            ListCompileEvent::NAME
        );

        $response = $template->getResponse();

        if ((bool) $listConfig->doNotRenderEmpty
            && empty($list->getItems())) {
            return new Response();
        }

        return $response;
    }
}
