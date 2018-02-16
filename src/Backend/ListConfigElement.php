<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\Backend;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\Input;
use Contao\System;
use HeimrichHannot\Request\Request;

class ListConfigElement extends Backend
{
    const TYPE_IMAGE = 'image';

    const TYPES = [
        self::TYPE_IMAGE,
    ];

    const PLACEHOLDER_IMAGE_MODE_NONE = 'none';
    const PLACEHOLDER_IMAGE_MODE_GENDERED = 'gendered';
    const PLACEHOLDER_IMAGE_MODE_SIMPLE = 'simple';

    const PLACEHOLDER_IMAGE_MODES = [
        self::PLACEHOLDER_IMAGE_MODE_GENDERED,
        self::PLACEHOLDER_IMAGE_MODE_SIMPLE,
    ];

    public function listChildren($arrRow)
    {
        return '<div class="tl_content_left">'.($arrRow['title'] ?: $arrRow['id']).' <span style="color:#b3b3b3; padding-left:3px">['
               .\Date::parse(\Contao\Config::get('datimFormat'), trim($arrRow['dateAdded'])).']</span></div>';
    }

    public function checkPermission()
    {
        $user = BackendUser::getInstance();
        $database = Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!is_array($user->listbundles) || empty($user->listbundles)) {
            $root = [0];
        } else {
            $root = $user->listbundles;
        }

        $id = strlen(Request::getGet('id')) ? Request::getGet('id') : CURRENT_ID;

        // Check current action
        switch (Request::getGet('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(Request::getGet('pid')) || !in_array(Request::getGet('pid'), $root, true)) {
                    throw new AccessDeniedException(
                        'Not enough permissions to create list_config_element items in list_config_element archive ID '.Request::getGet('pid').'.'
                    );
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(Request::getGet('pid'), $root, true)) {
                    throw new AccessDeniedException(
                        'Not enough permissions to '.Request::getGet('act').' list_config_element item ID '.$id
                        .' to list_config_element archive ID '.Request::getGet('pid').'.'
                    );
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

                if (!in_array($objArchive->pid, $root, true)) {
                    throw new AccessDeniedException(
                        'Not enough permissions to '.Request::getGet('act').' list_config_element item ID '.$id
                        .' of list_config_element archive ID '.$objArchive->pid.'.'
                    );
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root, true)) {
                    throw new AccessDeniedException(
                        'Not enough permissions to access list_config_element archive ID '.$id.'.'
                    );
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
                if (strlen(Request::getGet('act'))) {
                    throw new AccessDeniedException('Invalid command "'.Request::getGet('act').'".');
                } elseif (!in_array($id, $root, true)) {
                    throw new AccessDeniedException(
                        'Not enough permissions to access list_config_element archive ID '.$id.'.'
                    );
                }
                break;
        }
    }
}
