<?php

namespace Nails\Cms\Cms\Monitor\Widget;

use Nails\Cms\Constants;
use Nails\Cms\Interfaces;
use Nails\Cms\Traits;
use Nails\Factory;

/**
 * Class Page
 *
 * @package Nails\Cms\Cms\Monitor\Widget
 */
class Page implements Interfaces\Monitor\Widget
{
    use Traits\Monitor\Widget;

    // --------------------------------------------------------------------------

    public function getLabel(): string
    {
        return 'CMS: Pages';
    }

    // --------------------------------------------------------------------------

    protected function getTableName(): string
    {
        return Factory::model('Page', Constants::MODULE_SLUG)->getTableName();
    }

    // --------------------------------------------------------------------------

    private function getDataColumns(): array
    {
        return ['published_template_data', 'draft_template_data'];
    }

    // --------------------------------------------------------------------------

    private function getQueryColumns(): array
    {
        return ['id', 'published_title', 'published_slug', 'draft_title', 'draft_slug'];
    }

    // --------------------------------------------------------------------------

    protected function getJsonPath(): string
    {
        return '$.*[*].slug';
    }

    // --------------------------------------------------------------------------

    protected function compileUsage(\stdClass $oRow): \Nails\Cms\Factory\Monitor\Detail\Usage
    {
        /** @var \Nails\Cms\Factory\Monitor\Detail\Usage $oUsage */
        $oUsage = Factory::factory(
            'MonitorDetailUsage',
            Constants::MODULE_SLUG,
            $oRow->published_title ?: $oRow->draft_title,
            siteUrl($oRow->published_slug ?: $oRow->draft_slug),
            userHasPermission('admin:cms:pages:edit')
                ? siteUrl('admin/cms/pages/edit/' . $oRow->id)
                : null
        );

        return $oUsage;
    }
}
