<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ListBundle\EventListener\Contao;

use Contao\Database;

class SqlGetFromDcaListener
{
    public function __invoke($sqlDcaData)
    {
        $this->migrateParentListConfigToPid($sqlDcaData);

        return $sqlDcaData;
    }

    protected function migrateParentListConfigToPid(array &$sqlDcaData)
    {
        $db = Database::getInstance();

        // migration already took place
        if (!$db->tableExists('tl_list_config', null, true)) {
            return;
        }

        if ($db->fieldExists('pid', 'tl_list_config', true)) {
            return;
        }

        $db->execute('ALTER TABLE tl_list_config ADD pid INT UNSIGNED DEFAULT 0 NOT NULL');
        $db->execute('ALTER TABLE tl_list_config ADD sorting INT UNSIGNED DEFAULT 0 NOT NULL');

        // remove fields from sql data in order to avoid duplicate column errors
        unset($sqlDcaData['tl_list_config']['TABLE_FIELDS']['sorting'], $sqlDcaData['tl_list_config']['TABLE_FIELDS']['pid']);

        // migrate parentListConfig to pid
        $db->execute('UPDATE tl_list_config SET pid=parentListConfig');

        // create sorting based on alphabetical order
        $listConfigs = $db->execute('SELECT * FROM tl_list_config ORDER BY title ASC');

        $sorting = 128;

        if ($listConfigs->numRows > 0) {
            while ($listConfigs->next()) {
                $db->prepare('UPDATE tl_list_config SET sorting=? WHERE id=?')->execute(
                    $sorting,
                    $listConfigs->id
                );

                $sorting += 64;
            }
        }
    }
}
