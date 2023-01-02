<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Controller;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\ModuleModel;
use HeimrichHannot\ListBundle\Controller\FrontendModule\ListFrontendModuleController;
use HeimrichHannot\ListBundle\Registry\ListConfigRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModalController extends AbstractController
{
    private ContaoFramework    $contaoFramework;
    private ListConfigRegistry $listConfigRegistry;

    public function __construct(ContaoFramework $contaoFramework, ListConfigRegistry $listConfigRegistry)
    {
        $this->contaoFramework = $contaoFramework;
        $this->listConfigRegistry = $listConfigRegistry;
    }

    /**
     * @Route("/_list/modal/{id}/{item}", name="huh_list_modal_reader")
     */
    public function renderReaderModelContent(Request $request, int $id, string $item): Response
    {
        $this->contaoFramework->initialize();

        $moduleModel = ModuleModel::findByPk($id);

        if (!$moduleModel || ListFrontendModuleController::TYPE !== $moduleModel->type) {
            return new Response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        $listConfigModel = $this->listConfigRegistry->getComputedListConfig((int) $moduleModel->listConfig);

        if (!$listConfigModel->openListItemsInModal) {
            return new Response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        $moduleModel->list_renderReaderOnAutoItem = '1';
        $moduleModel->list_readerModule = $listConfigModel->listModalReaderModule;

        $_GET['auto_item'] = $item;

        return new Response(Controller::getFrontendModule($moduleModel));
    }
}
