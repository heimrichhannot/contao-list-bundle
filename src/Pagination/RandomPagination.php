<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\ListBundle\Pagination;

use Contao\Template;
use HeimrichHannot\UtilsBundle\Url\UrlUtil;

class RandomPagination extends \Contao\Pagination
{
    const PARAM_RANDOM = 'random';

    protected $randomSeed = false;

    public function __construct(
        $randomSeed,
        $rows,
        $perPage,
        $numberOfLinks = 7,
        $parameter = 'page',
        Template $template = null,
        $forceParam = false
    ) {
        $this->randomSeed = $randomSeed;

        parent::__construct($rows, $perPage, $numberOfLinks, $parameter, $template, $forceParam);
    }

    protected function linkToPage($page)
    {
        $url = ampersand($this->strUrl);

        if ($page <= 1 && !$this->blnForceParam) {
            if ($this->randomSeed) {
                $url = UrlUtil::addQueryString(static::PARAM_RANDOM.'='.$this->randomSeed, $url);
            }

            return $url;
        }
        $url = UrlUtil::addQueryString($this->strParameter.'='.$page, $url);

        if ($this->randomSeed) {
            $url = UrlUtil::addQueryString(static::PARAM_RANDOM.'='.$this->randomSeed, $url);
        }

        return $url;
    }
}
