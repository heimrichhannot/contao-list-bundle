<?php

$GLOBALS['TL_DCA']['tl_list_config_element'] = [
    'config'   => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_list_config',
        'enableVersioning'  => true,
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback' => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'list'     => [
        'label'             => [
            'fields' => ['id'],
            'format' => '%s'
        ],
        'sorting'           => [
            'mode'                  => 1,
            'fields'                => ['title'],
            'headerFields'          => ['title'],
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => ['tl_list_config_element', 'listChildren']
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_list_config_element']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                                . '\'))return false;Backend.getScrollOffset()"'
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_list_config_element']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_list_config_element', 'toggleIcon']
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
        ]
    ],
    'palettes' => [
        '__selector__' => [],
        'default'      => '{general_legend},title;'
    ],
    'fields'   => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid'       => [
            'foreignKey' => 'tl_list_config.title',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager']
        ],
        'tstamp'    => [
            'label' => &$GLOBALS['TL_LANG']['tl_list_config_element']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded' => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_list_config_element']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
    ]
];


class tl_list_config_element extends \Contao\Backend
{

    public function listChildren($arrRow)
    {
        return '<div class="tl_content_left">' . ($arrRow['title'] ?: $arrRow['id']) . ' <span style="color:#b3b3b3; padding-left:3px">['
               . \Date::parse(\Contao\Config::get('datimFormat'), trim($arrRow['dateAdded'])) . ']</span></div>';
    }

    public function checkPermission()
    {
        $user     = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        if ($user->isAdmin)
        {
            return;
        }

        // Set the root IDs
        if (!is_array($user->listbundles) || empty($user->listbundles))
        {
            $root = [0];
        }
        else
        {
            $root = $user->listbundles;
        }

        $id = strlen(\Contao\Input::get('id')) ? \Contao\Input::get('id') : CURRENT_ID;

        // Check current action
        switch (\Contao\Input::get('act'))
        {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(\Contao\Input::get('pid')) || !in_array(\Contao\Input::get('pid'), $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to create list_config_element items in list_config_element archive ID ' . \Contao\Input::get('pid')
                        . '.'
                    );
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(\Contao\Input::get('pid'), $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to ' . \Contao\Input::get('act') . ' list_config_element item ID ' . $id
                        . ' to list_config_element archive ID ' . \Contao\Input::get('pid') . '.'
                    );
                }
            // NO BREAK STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $database->prepare("SELECT pid FROM tl_list_config_element WHERE id=?")->limit(1)->execute($id);

                if ($objArchive->numRows < 1)
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid list_config_element item ID ' . $id . '.');
                }

                if (!in_array($objArchive->pid, $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to ' . \Contao\Input::get('act') . ' list_config_element item ID ' . $id
                        . ' of list_config_element archive ID ' . $objArchive->pid . '.'
                    );
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to access list_config_element archive ID ' . $id . '.'
                    );
                }

                $objArchive = $database->prepare("SELECT id FROM tl_list_config_element WHERE pid=?")->execute($id);

                if ($objArchive->numRows < 1)
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid list_config_element archive ID ' . $id . '.');
                }

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
                $session = \System::getContainer()->get('session');

                $session                   = $session->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $session->replace($session);
                break;

            default:
                if (strlen(\Contao\Input::get('act')))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "' . \Contao\Input::get('act') . '".');
                }
                elseif (!in_array($id, $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to access list_config_element archive ID ' . $id . '.'
                    );
                }
                break;
        }
    }

}
