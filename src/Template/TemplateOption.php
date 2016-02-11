<?php

/**
 * Represents a template option
 *
 * @package     Nails
 * @subpackage  module-cms
 * @category    Template
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Cms\Template;

class TemplateOption
{
    protected $type        = '';
    protected $key         = '';
    protected $label       = '';
    protected $subLabel    = '';
    protected $info        = '';
    protected $default     = '';
    protected $class       = '';
    protected $placeholder = '';
    protected $tip         = '';
    protected $options     = array();

    // --------------------------------------------------------------------------

    public function setType($sType)
    {
        $this->type = $sType;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function getType()
    {
        return $this->type;
    }

    // --------------------------------------------------------------------------

    public function setKey($sKey)
    {
        $this->key = $sKey;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function getKey()
    {
        return $this->key;
    }

    // --------------------------------------------------------------------------

    public function setLabel($sLabel)
    {
        $this->label = $sLabel;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function getLabel()
    {
        return $this->label;
    }

    // --------------------------------------------------------------------------

    public function setSubLabel($sSubLabel)
    {
        $this->subLabel = $sSubLabel;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function getSubLabel()
    {
        return $this->subLabel;
    }

    // --------------------------------------------------------------------------

    public function setInfo($sInfo)
    {
        $this->info = $sInfo;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function getInfo()
    {
        return $this->info;
    }

    // --------------------------------------------------------------------------

    public function setDefault($sDefault)
    {
        $this->default = $sDefault;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function getDefault()
    {
        return $this->default;
    }

    // --------------------------------------------------------------------------

    public function setClass($sClass)
    {
        $this->class = $sClass;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function getClass()
    {
        return $this->class;
    }

    // --------------------------------------------------------------------------

    public function setPlaceholder($sPlaceholder)
    {
        $this->placeholder = $sPlaceholder;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    // --------------------------------------------------------------------------

    public function setTip($sTip)
    {
        $this->tip = $sTip;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function getTip()
    {
        return $this->tip;
    }

    // --------------------------------------------------------------------------

    public function setOptions($sOptions)
    {
        $this->options = $sOptions;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function getOptions()
    {
        return $this->options;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the class properties as an array
     * @return array
     */
    public function toArray()
    {
        return array(
            'type'        => $this->type,
            'key'         => $this->key,
            'label'       => $this->label,
            'subLabel'    => $this->subLabel,
            'info'        => $this->info,
            'default'     => $this->default,
            'class'       => $this->class,
            'placeholder' => $this->placeholder,
            'tip'         => $this->tip,
            'options'     => $this->options
        );
    }
}
