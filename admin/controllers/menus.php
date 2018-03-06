<?php

/**
 * This class provides CMS Menu management functionality
 *
 * @package     Nails
 * @subpackage  module-cms
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Cms;

use Nails\Factory;
use Nails\Admin\Helper;
use Nails\Cms\Controller\BaseAdmin;

class Menus extends BaseAdmin
{
    protected $oMenuModel;

    // --------------------------------------------------------------------------

    /**
     * Announces this controller's navGroups
     * @return \Nails\Admin\Nav
     */
    public static function announce()
    {
        if (userHasPermission('admin:cms:menus:manage')) {

            $oNavGroup = Factory::factory('Nav', 'nailsapp/module-admin');
            $oNavGroup->setLabel('CMS');
            $oNavGroup->setIcon('fa-file-text');
            $oNavGroup->addAction('Manage Menus');
            return $oNavGroup;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     * @return array
     */
    public static function permissions()
    {
        $permissions = parent::permissions();

        $permissions['manage']  = 'Can manage menus';
        $permissions['create']  = 'Can create a new menu';
        $permissions['edit']    = 'Can edit an existing menu';
        $permissions['delete']  = 'Can delete an existing menu';
        $permissions['restore'] = 'Can restore a deleted menu';

        return $permissions;
    }

    // --------------------------------------------------------------------------

    /**
     * Construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        $this->oMenuModel = Factory::model('Menu', 'nailsapp/module-cms');
    }

    // --------------------------------------------------------------------------

    /**
     * Browse CMS Menus
     * @return void
     */
    public function index()
    {
        if (!userHasPermission('admin:cms:menus:manage')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Manage Menus';

        // --------------------------------------------------------------------------

        $sTableAlias = $this->oMenuModel->getTableAlias();

        //  Get pagination and search/sort variables
        $page      = $this->input->get('page') ? $this->input->get('page') : 0;
        $perPage   = $this->input->get('perPage') ? $this->input->get('perPage') : 50;
        $sortOn    = $this->input->get('sortOn') ? $this->input->get('sortOn') : $sTableAlias . '.label';
        $sortOrder = $this->input->get('sortOrder') ? $this->input->get('sortOrder') : 'asc';
        $keywords  = $this->input->get('keywords') ? $this->input->get('keywords') : '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $sortColumns = [
            $sTableAlias . '.label'    => 'Label',
            $sTableAlias . '.modified' => 'Modified',
        ];

        // --------------------------------------------------------------------------

        //  Define the $data variable for the queries
        $data = [
            'sort'     => [
                [$sortOn, $sortOrder],
            ],
            'keywords' => $keywords,
        ];

        //  Get the items for the page
        $totalRows           = $this->oMenuModel->countAll($data);
        $this->data['menus'] = $this->oMenuModel->getAll($page, $perPage, $data);

        //  Set Search and Pagination objects for the view
        $this->data['search']     = Helper::searchObject(true, $sortColumns, $sortOn, $sortOrder, $perPage, $keywords);
        $this->data['pagination'] = Helper::paginationObject($page, $perPage, $totalRows);

        //  Add a header button
        if (userHasPermission('admin:cms:menus:create')) {

            Helper::addHeaderButton('admin/cms/menus/create', 'Create Menu');
        }

        // --------------------------------------------------------------------------

        Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new CMS Menu
     * @return void
     */
    public function create()
    {
        if (!userHasPermission('admin:cms:menus:create')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            //  Validate form
            $oFormValidation = Factory::service('FormValidation');
            $oFormValidation->set_rules('label', '', 'xss_clean|trim|required');
            $oFormValidation->set_rules('description', '', 'trim');
            $oFormValidation->set_message('required', lang('fv_required'));

            if ($oFormValidation->run()) {

                //  Prepare the create data
                $aItemData                = [];
                $aItemData['label']       = $this->input->post('label');
                $aItemData['description'] = strip_tags($this->input->post('description'));
                $aItemData['items']       = [];

                //  Prepare the menu items
                $aMenuItems = $this->input->post('menuItem');
                $iNumItems  = isset($aMenuItems['id']) ? count($aMenuItems['id']) : 0;

                for ($i = 0; $i < $iNumItems; $i++) {

                    $aItemData['items'][$i]              = [];
                    $aItemData['items'][$i]['id']        = isset($aMenuItems['id'][$i]) ? $aMenuItems['id'][$i] : null;
                    $aItemData['items'][$i]['parent_id'] = isset($aMenuItems['parent_id'][$i]) ? $aMenuItems['parent_id'][$i] : null;
                    $aItemData['items'][$i]['label']     = isset($aMenuItems['label'][$i]) ? $aMenuItems['label'][$i] : null;
                    $aItemData['items'][$i]['url']       = isset($aMenuItems['url'][$i]) ? $aMenuItems['url'][$i] : null;
                    $aItemData['items'][$i]['page_id']   = isset($aMenuItems['page_id'][$i]) ? $aMenuItems['page_id'][$i] : null;
                }

                if ($this->oMenuModel->create($aItemData)) {

                    $sStatus  = 'success';
                    $sMessage = 'Menu created successfully.';

                    $this->session->set_flashdata($sStatus, $sMessage);
                    redirect('admin/cms/menus');

                } else {

                    $this->data['error'] = 'Failed to create menu. ';
                    $this->data['error'] .= $this->oMenuModel->lastError();
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }

        } else {

            $aItems = [];
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Create Menu';

        // --------------------------------------------------------------------------

        //  Prepare the menu items
        if ($this->input->post()) {

            $aMenuItems     = [];
            $aPostMenuItems = $this->input->post('menuItem');
            $iNumItems      = !empty($aPostMenuItems['id']) ? count($aPostMenuItems['id']) : 0;

            for ($i = 0; $i < $iNumItems; $i++) {

                $aMenuItems[$i]              = [];
                $aMenuItems[$i]['id']        = isset($aPostMenuItems['id'][$i]) ? $aPostMenuItems['id'][$i] : null;
                $aMenuItems[$i]['parent_id'] = isset($aPostMenuItems['parent_id'][$i]) ? $aPostMenuItems['parent_id'][$i] : null;
                $aMenuItems[$i]['label']     = isset($aPostMenuItems['label'][$i]) ? $aPostMenuItems['label'][$i] : null;
                $aMenuItems[$i]['url']       = isset($aPostMenuItems['url'][$i]) ? $aPostMenuItems['url'][$i] : null;
                $aMenuItems[$i]['page_id']   = isset($aPostMenuItems['page_id'][$i]) ? $aPostMenuItems['page_id'][$i] : null;
            }

        } else {

            $aMenuItems = $aItems;
        }

        // --------------------------------------------------------------------------

        //  Get the CMS Pages
        $oPageModel          = Factory::model('Page', 'nailsapp/module-cms');
        $aPages              = $oPageModel->getAllNestedFlat();
        $this->data['pages'] = ['' => 'Select a CMS Page'] + $aPages;

        // --------------------------------------------------------------------------

        //  Assets
        $oAsset = Factory::service('Asset');
        $oAsset->load('nestedSortable/jquery.ui.nestedSortable.js', 'NAILS-BOWER');
        $oAsset->library('MUSTACHE');
        $oAsset->load('admin.menus.edit.min.js', 'nailsapp/module-cms');
        $oAsset->inline('var menuEdit = new NAILS_Admin_CMS_Menus_Create_Edit(' . json_encode($aMenuItems) . ');', 'JS');

        // --------------------------------------------------------------------------

        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Edit a CMS Menu
     * @return void
     */
    public function edit()
    {
        if (!userHasPermission('admin:cms:menus:edit')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $menu               = $this->oMenuModel->getById($this->uri->segment(5));
        $this->data['menu'] = $menu;

        if (!$menu) {

            $this->session->set_flashdata('error', 'Invalid menu ID.');
            redirect('admin/cms/menus');
        }

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            //  Validate form
            $oFormValidation = Factory::service('FormValidation');
            $oFormValidation->set_rules('label', '', 'xss_clean|trim|required');
            $oFormValidation->set_rules('description', '', 'trim');
            $oFormValidation->set_message('required', lang('fv_required'));

            if ($oFormValidation->run()) {

                //  Prepare the create data
                $aItemData                = [];
                $aItemData['label']       = $this->input->post('label');
                $aItemData['description'] = strip_tags($this->input->post('description'));
                $aItemData['items']       = [];

                //  Prepare the menu items
                $aMenuItems = $this->input->post('menuItem');
                $iNumItems  = isset($aMenuItems['id']) ? count($aMenuItems['id']) : 0;

                for ($i = 0; $i < $iNumItems; $i++) {

                    $aItemData['items'][$i]              = [];
                    $aItemData['items'][$i]['id']        = isset($aMenuItems['id'][$i]) ? $aMenuItems['id'][$i] : null;
                    $aItemData['items'][$i]['parent_id'] = isset($aMenuItems['parent_id'][$i]) ? $aMenuItems['parent_id'][$i] : null;
                    $aItemData['items'][$i]['label']     = isset($aMenuItems['label'][$i]) ? $aMenuItems['label'][$i] : null;
                    $aItemData['items'][$i]['url']       = isset($aMenuItems['url'][$i]) ? $aMenuItems['url'][$i] : null;
                    $aItemData['items'][$i]['page_id']   = isset($aMenuItems['page_id'][$i]) ? $aMenuItems['page_id'][$i] : null;
                }

                if ($this->oMenuModel->update($menu->id, $aItemData)) {

                    $sStatus  = 'success';
                    $sMessage = 'Menu updated successfully.';

                    $this->session->set_flashdata($sStatus, $sMessage);
                    redirect('admin/cms/menus');

                } else {

                    $this->data['error'] = 'Failed to update menu. ';
                    $this->data['error'] .= $this->oMenuModel->lastError();
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }

        } else {

            $aItems = $menu->items;
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Edit Menu &rsaquo; ' . $menu->label;

        // --------------------------------------------------------------------------

        if ($this->input->post()) {

            $aMenuItems     = [];
            $aPostMenuItems = $this->input->post('menuItem');
            $iNumItems      = !empty($aPostMenuItems['id']) ? count($aPostMenuItems['id']) : 0;

            for ($i = 0; $i < $iNumItems; $i++) {

                $aMenuItems[$i]              = [];
                $aMenuItems[$i]['id']        = isset($aPostMenuItems['id'][$i]) ? $aPostMenuItems['id'][$i] : null;
                $aMenuItems[$i]['parent_id'] = isset($aPostMenuItems['parent_id'][$i]) ? $aPostMenuItems['parent_id'][$i] : null;
                $aMenuItems[$i]['label']     = isset($aPostMenuItems['label'][$i]) ? $aPostMenuItems['label'][$i] : null;
                $aMenuItems[$i]['url']       = isset($aPostMenuItems['url'][$i]) ? $aPostMenuItems['url'][$i] : null;
                $aMenuItems[$i]['page_id']   = isset($aPostMenuItems['page_id'][$i]) ? $aPostMenuItems['page_id'][$i] : null;
            }

        } else {

            $aMenuItems = $aItems;
        }

        // --------------------------------------------------------------------------

        //  Get the CMS Pages
        $oPageModel          = Factory::model('Page', 'nailsapp/module-cms');
        $aPages              = $oPageModel->getAllNestedFlat();
        $this->data['pages'] = ['' => 'Select a CMS Page'] + $aPages;

        // --------------------------------------------------------------------------

        //  Assets
        $oAsset = Factory::service('Asset');
        $oAsset->load('nestedSortable/jquery.ui.nestedSortable.js', 'NAILS-BOWER');
        $oAsset->library('MUSTACHE');
        $oAsset->load('admin.menus.edit.min.js', 'nailsapp/module-cms');
        $oAsset->inline('var menuEdit = new NAILS_Admin_CMS_Menus_Create_Edit(' . json_encode($aMenuItems) . ');', 'JS');

        // --------------------------------------------------------------------------

        Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Delete a CMS Menu
     * @return void
     */
    public function delete()
    {
        if (!userHasPermission('admin:cms:menus:delete')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $menu = $this->oMenuModel->getById($this->uri->segment(5));

        if (!$menu) {

            $this->session->set_flashdata('error', 'Invalid menu ID.');
            redirect('admin/cms/menus');
        }

        // --------------------------------------------------------------------------

        if ($this->oMenuModel->delete($menu->id)) {

            $sStatus = 'success';
            $msg     = 'Menu was deleted successfully.';

        } else {

            $sStatus = 'error';
            $msg     = 'Failed to delete menu. ';
            $msg     .= $this->oMenuModel->lastError();
        }

        $this->session->set_flashdata($sStatus, $msg);
        redirect('admin/cms/menus');
    }
}
