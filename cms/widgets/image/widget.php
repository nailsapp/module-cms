<?php

/**
 * This class is the "Image" CMS widget definition
 *
 * @package     Nails
 * @subpackage  module-cms
 * @category    Widget
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Cms\Cms\Widget;

use Nails\Cms\Widget\WidgetBase;

class Image extends WidgetBase
{
    /**
     * Construct and define the widget
     */
    public function __construct()
    {
        parent::__construct();

        $this->label       = 'Image';
        $this->icon        = 'fa-picture-o';
        $this->description = 'A single image.';
        $this->keywords    = 'image,images,photo,photos';
    }
}
