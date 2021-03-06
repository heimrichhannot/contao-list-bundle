<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Backend;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\System;

class ListConfigElement extends Backend
{
    const TYPE_IMAGE = 'image';

    const TYPES = [
        self::TYPE_IMAGE,
    ];

    const PLACEHOLDER_IMAGE_MODE_NONE = 'none';
    const PLACEHOLDER_IMAGE_MODE_GENDERED = 'gendered';
    const PLACEHOLDER_IMAGE_MODE_SIMPLE = 'simple';
    const PLACEHOLDER_IMAGE_MODE_RANDOM = 'random';
    const PLACEHOLDER_IMAGE_MODE_FIELD = 'field';

    const PLACEHOLDER_IMAGE_MODES = [
        self::PLACEHOLDER_IMAGE_MODE_GENDERED,
        self::PLACEHOLDER_IMAGE_MODE_SIMPLE,
        self::PLACEHOLDER_IMAGE_MODE_RANDOM,
        self::PLACEHOLDER_IMAGE_MODE_FIELD,
    ];

    public function checkPermission()
    {
        $user = BackendUser::getInstance();
        $database = Database::getInstance();

        $request = System::getContainer()->get('huh.request');

        if ($user->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!\is_array($user->listbundles) || empty($user->listbundles)) {
            $root = [0];
        } else {
            $root = $user->listbundles;
        }

        $id = \strlen($request->getGet('id')) ? $request->getGet('id') : CURRENT_ID;

        // Check current action
        switch ($request->getGet('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!\strlen($request->getGet('pid')) || !\in_array($request->getGet('pid'), $root)) {
                    throw new AccessDeniedException('Not enough permissions to create list_config_element items in list_config_element archive ID '.$request->getGet('pid').'.');
                }

                break;

            case 'cut':
            case 'copy':
                if (!\in_array($request->getGet('pid'), $root)) {
                    throw new AccessDeniedException('Not enough permissions to '.$request->getGet('act').' list_config_element item ID '.$id.' to list_config_element archive ID '.$request->getGet('pid').'.');
                }
            // no break STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $database->prepare('SELECT pid FROM tl_list_config_element WHERE id=?')->limit(1)->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new AccessDeniedException('Invalid list_config_element item ID '.$id.'.');
                }

                if (!\in_array($objArchive->pid, $root)) {
                    throw new AccessDeniedException('Not enough permissions to '.$request->getGet('act').' list_config_element item ID '.$id.' of list_config_element archive ID '.$objArchive->pid.'.');
                }

                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!\in_array($id, $root)) {
                    throw new AccessDeniedException('Not enough permissions to access list_config_element archive ID '.$id.'.');
                }

                $objArchive = $database->prepare('SELECT id FROM tl_list_config_element WHERE pid=?')->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new AccessDeniedException('Invalid list_config_element archive ID '.$id.'.');
                }

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
                $session = System::getContainer()->get('session');

                $session = $session->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $session->replace($session);

                break;

            default:
                if (\strlen($request->getGet('act'))) {
                    throw new AccessDeniedException('Invalid command "'.$request->getGet('act').'".');
                } elseif (!\in_array($id, $root)) {
                    throw new AccessDeniedException('Not enough permissions to access list_config_element archive ID '.$id.'.');
                }

                break;
        }
    }
}
