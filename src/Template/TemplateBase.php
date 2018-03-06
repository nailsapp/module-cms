<?php

/**
 * This class is the basic CMS template definition
 *
 * @package     Nails
 * @subpackage  module-cms
 * @category    Template
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Cms\Template;

use Nails\Factory;

abstract class TemplateBase
{
    /**
     * Whether the template is enabled or not
     * @var bool
     */
    const DISABLED = false;

    // --------------------------------------------------------------------------

    /**
     * Whether the template is the default template
     * @var bool
     */
    protected static $isDefault;

    /**
     * The template's label
     * @var string
     */
    protected $label;

    /**
     * The template's description
     * @var string
     */
    protected $description;

    /**
     * The template's grouping
     * @var string
     */
    protected $grouping;

    /**
     * The available widget areas
     * @var array
     */
    protected $widget_areas;

    /**
     * Additional fields to make available
     * @var array
     */
    protected $additional_fields;

    /**
     * Any manual config items to pass in
     * @var string
     */
    protected $manual_config;

    /**
     * The template's icon
     * @var string
     */
    protected $icon;

    /**
     * The template's slug
     * @var string
     */
    protected $slug;

    /**
     * Assets to load when in the editor
     * @var array
     */
    protected $assets_editor;

    /**
     * Assets to load when rendering
     * @var array
     */
    protected $assets_render;

    /**
     * The template's path
     * @var string
     */
    protected $path;

    // --------------------------------------------------------------------------

    /**
     * Returns whether the template is disabled
     * @return bool
     */
    public static function isDisabled()
    {
        return !empty(static::DISABLED);
    }

    // --------------------------------------------------------------------------

    /**
     * Returns whether the template is a default template or not
     * @return bool
     */
    public static function isDefault()
    {
        return !empty(static::$isDefault);
    }

    // --------------------------------------------------------------------------

    /**
     * Constructs the template
     */
    public function __construct()
    {
        $this->label             = 'Template';
        $this->description       = '';
        $this->widget_areas      = [];
        $this->additional_fields = [];
        $this->manual_config     = '';
        $this->icon              = '';
        $this->slug              = '';
        $this->assets_editor     = [];
        $this->assets_render     = [];
        $this->path              = '';

        // --------------------------------------------------------------------------

        //  Detect the path
        $sCalledClass = get_called_class();
        $this->path   = $sCalledClass::detectPath();

        //  Icon
        $aExtensions = ['png', 'jpg', 'jpeg', 'gif'];

        foreach ($aExtensions as $sExtension) {

            $sIconPath = $this->path . 'icon.' . $sExtension;

            if (is_file($sIconPath)) {

                if (preg_match('#^' . preg_quote(NAILS_PATH, '#') . '#', $sIconPath)) {

                    //  Nails asset
                    $this->icon = preg_replace('#^' . preg_quote(NAILS_PATH, '#') . '#', NAILS_URL, $sIconPath);

                } elseif (preg_match('#^' . preg_quote(APPPATH, '#') . '#', $sIconPath)) {

                    if (isPageSecure()) {
                        $sPattern   = '#^' . preg_quote(APPPATH, '#') . '#';
                        $this->icon = preg_replace($sPattern, SECURE_BASE_URL . APPPATH . '', $sIconPath);
                    } else {
                        $sPattern   = '#^' . preg_quote(APPPATH, '#') . '#';
                        $this->icon = preg_replace($sPattern, BASE_URL . APPPATH . '', $sIconPath);
                    }
                }
                break;
            }
        }

        //  Slug - this should uniquely identify a type of template
        $this->slug = pathinfo($this->path);
        $this->slug = $this->slug['basename'];
    }

    // --------------------------------------------------------------------------

    /**
     * Detects the path of the called class
     * @return string
     */
    public static function detectPath()
    {
        $oReflect = new \ReflectionClass(get_called_class());
        return dirname($oReflect->getFileName()) . '/';
    }

    // --------------------------------------------------------------------------

    /**
     * Looks for a file in the widget hierarchy and returns it if found
     *
     * @param string $sFile The file name to look for
     *
     * @return null|string
     */
    public static function getFilePath($sFile)
    {
        //  Look for the file in the [potential] class hierarchy
        $aClasses = array_filter(
            array_merge(
                [get_called_class()],
                array_values(class_parents(get_called_class()))
            )
        );

        foreach ($aClasses as $sClass) {
            $sPath = $sClass::detectPath();
            if (is_file($sPath . $sFile)) {
                return $sPath . $sFile;
            }
        }

        return null;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the template's label
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the template's description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the template's grouping
     * @return string
     */
    public function getGrouping()
    {
        return $this->grouping;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the template's widget areas
     * @return array
     */
    public function getWidgetAreas()
    {
        return $this->widget_areas;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the template's additional fields
     * @return array
     */
    public function getAdditionalFields()
    {
        return $this->additional_fields;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the template's manual config
     * @return string
     */
    public function getManualConfig()
    {
        return $this->manual_config;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the template's icon
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the template's slug
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the template's path
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the template's assets
     *
     * @param string $sType The type of assets to return
     *
     * @return array
     */
    public function getAssets($sType)
    {
        if ($sType == 'EDITOR') {
            return $this->assets_editor;
        } elseif ($sType == 'RENDER') {
            return $this->assets_render;
        } else {
            return [];
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Renders the template with the provided data.
     *
     * @param  array $aTplData    The widgets to include in the template
     * @param  array $aTplOptions Additional data created by the template
     *
     * @return string
     */
    public function render($aTplData = [], $aTplOptions = [])
    {
        //  Process each widget area and render the HTML
        $aWidgetAreas  = $this->getWidgetAreas();
        $aRenderedData = [];
        $oWidgetModel  = Factory::model('Widget', 'nailsapp/module-cms');

        foreach ($aWidgetAreas as $sAreaSlug => $oWidgetArea) {

            $aWidgetData               = !empty($aTplData[$sAreaSlug]) ? $aTplData[$sAreaSlug] : [];
            $aRenderedData[$sAreaSlug] = '';

            foreach ($aWidgetData as $oWidgetData) {
                if (empty($oWidgetData->slug)) {
                    continue;
                }
                if (!property_exists($oWidgetData, 'data')) {
                    $oWidgetData->data = [];
                }
                $oWidget = $oWidgetModel->getBySlug($oWidgetData->slug, 'RENDER');
                if ($oWidget) {
                    $aRenderedData[$sAreaSlug] .= $oWidget->render((array) $oWidgetData->data);
                }
            }
        }

        return $this->loadView('view', $aTplOptions, $aRenderedData);
    }

    // --------------------------------------------------------------------------

    /**
     * Load a specific view
     *
     * @param string $sView       The view to load
     * @param array  $aTplOptions The selected template options
     * @param array  $aTplData    The data to render the view with
     *
     * @return mixed|string
     */
    protected function loadView($sView, $aTplOptions, $aTplData)
    {
        $sPath = static::getFilePath($sView . '.php');
        if (!empty($sPath)) {

            //  Add a reference to the CI super object, for view loading etc
            $oCi = get_instance();

            /**
             * Extract data into variables in the local scope so the view can use them.
             * Basically copying how CI does it's view loading/rendering
             */
            $NAILS_CONTROLLER_DATA =& getControllerData();
            if ($NAILS_CONTROLLER_DATA) {
                extract($NAILS_CONTROLLER_DATA);
            }

            if ($aTplOptions) {
                extract($aTplOptions);
            }

            if ($aTplData) {
                extract($aTplData);
            }

            ob_start();
            include $sPath;
            $sBuffer = ob_get_contents();
            @ob_end_clean();

            //  Look for blocks
            preg_match_all('/\[:([a-zA-Z0-9\-]+?):\]/', $sBuffer, $aMatches);

            if ($aMatches[0]) {

                //  Get all the blocks which were found
                $oBlockModel = Factory::model('Block', 'nailsapp/module-cms');
                $aBlocks     = $oBlockModel->getBySlugs($aMatches[1]);

                //  Swap them in
                if ($aBlocks) {
                    foreach ($aBlocks as $oBlock) {

                        //  Translate some block types
                        switch ($oBlock->type) {
                            case 'file':
                            case 'image':
                                $oBlock->value = cdnServe($oBlock->value);
                                break;
                        }

                        $sBuffer = str_replace('[:' . $oBlock->slug . ':]', $oBlock->value, $sBuffer);
                    }
                }

                //  Swap page variables
                $sPageTitle    = !empty($tplAdditionalFields['cmspage']) ? $tplAdditionalFields['cmspage']->title : '';
                $pageShortTags = [
                    'page-title' => $sPageTitle,
                ];

                foreach ($pageShortTags as $shortTag => $value) {
                    $sBuffer = str_replace('[:' . $shortTag . ':]', $value, $sBuffer);
                }
            }

            //  Return the HTML
            return $sBuffer;
        }

        return '';
    }

    // --------------------------------------------------------------------------

    /**
     * Format the template as a JSON object
     *
     * @param int $iJsonOptions The JSON options
     * @param int $iJsonDepth   The JSON depth
     *
     * @return string
     */
    public function toJson($iJsonOptions = 0, $iJsonDepth = 512)
    {
        return json_encode($this->toArray(), $iJsonOptions, $iJsonDepth);
    }

    // --------------------------------------------------------------------------

    /**
     * Format the template as an array
     * @return array
     */
    public function toArray()
    {
        return [
            'label'             => $this->getLabel(),
            'description'       => $this->getDescription(),
            'widget_areas'      => $this->getWidgetAreas(),
            'additional_fields' => $this->getAdditionalFields(),
            'manual_config'     => $this->getManualConfig(),
            'icon'              => $this->getIcon(),
            'slug'              => $this->getSlug(),
            'assets_editor'     => $this->getAssets('EDITOR'),
            'assets_render'     => $this->getAssets('RENDER'),
            'path'              => $this->getPath(),
        ];
    }
}
