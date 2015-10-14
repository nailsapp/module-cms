<?php

/**
 * This class provides CMS Slider management functionality
 *
 * @package     Nails
 * @subpackage  module-cms
 * @category    AdminController
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Cms;

use Nails\Cms\Controller\BaseAdmin;

class Slider extends BaseAdmin
{
    /**
     * Announces this controller's navGroups
     * @return stdClass
     */
    public static function announce()
    {
        if (userHasPermission('admin:cms:slider:manage')) {

            $navGroup = new \Nails\Admin\Nav('CMS', 'fa-file-text');
            $navGroup->addAction('Manage Sliders');
            return $navGroup;
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

        $permissions['manage']  = 'Can manage sliders';
        $permissions['create']  = 'Can create a new slider';
        $permissions['edit']    = 'Can edit an existing slider';
        $permissions['delete']  = 'Can delete an existing slider';
        $permissions['restore'] = 'Can restore a deleted slider';

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

        //  Load common items
        $this->load->model('cms/cms_slider_model');
    }

    // --------------------------------------------------------------------------

    /**
     * Browse CMS Sliders
     * @return void
     */
    public function index()
    {
        if (!userHasPermission('admin:cms:slider:manage')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Set method info
        $this->data['page']->title = 'Manage Sliders';

        // --------------------------------------------------------------------------

        //  Get pagination and search/sort variables
        $page      = $this->input->get('page')      ? $this->input->get('page')      : 0;
        $perPage   = $this->input->get('perPage')   ? $this->input->get('perPage')   : 50;
        $sortOn    = $this->input->get('sortOn')    ? $this->input->get('sortOn')    : 's.label';
        $sortOrder = $this->input->get('sortOrder') ? $this->input->get('sortOrder') : 'asc';
        $keywords  = $this->input->get('keywords')  ? $this->input->get('keywords')  : '';

        // --------------------------------------------------------------------------

        //  Define the sortable columns
        $sortColumns = array(
            's.label'    => 'Label',
            's.modified' => 'Modified Date'
        );

        // --------------------------------------------------------------------------

        //  Define the $data variable for the queries
        $data = array(
            'sort' => array(
                array($sortOn, $sortOrder)
            ),
            'keywords' => $keywords
        );

        //  Get the items for the page
        $totalRows             = $this->cms_slider_model->count_all($data);
        $this->data['sliders'] = $this->cms_slider_model->get_all($page, $perPage, $data);

        //  Set Search and Pagination objects for the view
        $this->data['search']     = \Nails\Admin\Helper::searchObject(true, $sortColumns, $sortOn, $sortOrder, $perPage, $keywords);
        $this->data['pagination'] = \Nails\Admin\Helper::paginationObject($page, $perPage, $totalRows);

        //  Add a header button
        if (userHasPermission('admin:cms:slider:create')) {

             \Nails\Admin\Helper::addHeaderButton('admin/cms/slider/create', 'Add New Slider');
        }

        // --------------------------------------------------------------------------

        \Nails\Admin\Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new CMS Slider
     * @return void
     */
    public function create()
    {
        if (!userHasPermission('admin:cms:slider:create')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        if ($this->input->post())
        {
            //  Rebuild sliders
            $slideIds  = $this->input->post('slideId') ? $this->input->post('slideId') : array();
            $objectIds = $this->input->post('objectId') ? $this->input->post('objectId') : array();
            $captions  = $this->input->post('caption') ? $this->input->post('caption') : array();
            $urls      = $this->input->post('url') ? $this->input->post('url') : array();

            $slides = array();
            for ($i=0; $i < count($slideIds); $i++) {

                $slides[$i]            = new \stdClass();
                $slides[$i]->id        = !empty($slideIds[$i]) ? $slideIds[$i] : null;
                $slides[$i]->object_id = !empty($objectIds[$i]) ? $objectIds[$i] : null;
                $slides[$i]->caption   = !empty($captions[$i]) ? $captions[$i] : null;
                $slides[$i]->url       = !empty($urls[$i]) ? $urls[$i] : null;
            }

            // --------------------------------------------------------------------------

            //  Validate form
            $this->load->library('form_validation');
            $this->form_validation->set_rules('label', '', 'xss_clean|trim|required');
            $this->form_validation->set_rules('description', '', 'trim');
            $this->form_validation->set_message('required', lang('fv_required'));

            if ($this->form_validation->run()) {

                //  Prepare the create data
                $aSliderData                = array();
                $aSliderData['label']       = $this->input->post('label');
                $aSliderData['description'] = strip_tags($this->input->post('description'));
                $aSliderData['slides']      = $slides;

                if ($this->cms_slider_model->create($aSliderData)) {

                    $status  = 'success';
                    $message = 'Slider created successfully.';

                    $this->session->set_flashdata($status, $message);
                    redirect('admin/cms/slider');

                } else {

                    $this->data['error']  = 'Failed to create slider. ';
                    $this->data['error'] .= $this->cms_slider_model->last_error();
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }

        } else {

            $slides = array();
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Create Slider';

        // --------------------------------------------------------------------------

        //  Prep the slides for the view
        foreach ($slides as $slide) {

            $slide->imgSourceUrl = !empty($slide->object_id) ? cdnServe($slide->object_id) : null;
            $slide->imgThumbUrl = !empty($slide->object_id) ? cdnScale($slide->object_id, 130, 130) : null;
        }

        // --------------------------------------------------------------------------

        //  Define the manager URL
        $cdnManagerUrl = cdnManageUrl('cms-slider', array('sliderEdit','setImgCallback'), null, isPageSecure());

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->load('jquery-ui/jquery-ui.min.js', 'NAILS-BOWER');
        $this->asset->load('mustache.js/mustache.js', 'NAILS-BOWER');
        $this->asset->load('nails.admin.cms.sliders.createEdit.min.js', true);
        $this->asset->inline('var sliderEdit = new NAILS_Admin_CMS_Sliders_Create_Edit();', 'JS');
        $this->asset->inline('sliderEdit.setScheme("serve", "' . $this->cdn->url_serve_scheme() . '");', 'JS');
        $this->asset->inline('sliderEdit.setScheme("thumb", "' . $this->cdn->url_thumb_scheme() . '");', 'JS');
        $this->asset->inline('sliderEdit.setManagerUrl("' . $cdnManagerUrl . '");', 'JS');
        $this->asset->inline('sliderEdit.addSlides(' . json_encode($slides) . ');', 'JS');

        // --------------------------------------------------------------------------

        \Nails\Admin\Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Edit a CMS Slider
     * @return void
     */
    public function edit()
    {
        if (!userHasPermission('admin:cms:slider:edit')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        $slider = $this->cms_slider_model->get_by_id($this->uri->segment(5));
        $this->data['slider'] = $slider;

        if (!$slider) {

            $this->session->set_flashdata('error', 'Invalid slider ID.');
            redirect('admin/cms/slider');
        }

        // --------------------------------------------------------------------------

        if ($this->input->post())
        {
            //  Rebuild sliders
            $slideIds  = $this->input->post('slideId') ? $this->input->post('slideId') : array();
            $objectIds = $this->input->post('objectId') ? $this->input->post('objectId') : array();
            $captions  = $this->input->post('caption') ? $this->input->post('caption') : array();
            $urls      = $this->input->post('url') ? $this->input->post('url') : array();

            $slides = array();
            for ($i=0; $i < count($slideIds); $i++) {

                $slides[$i]            = new \stdClass();
                $slides[$i]->id        = !empty($slideIds[$i]) ? $slideIds[$i] : null;
                $slides[$i]->object_id = !empty($objectIds[$i]) ? $objectIds[$i] : null;
                $slides[$i]->caption   = !empty($captions[$i]) ? $captions[$i] : null;
                $slides[$i]->url       = !empty($urls[$i]) ? $urls[$i] : null;
            }

            // --------------------------------------------------------------------------

            //  Validate form
            $this->load->library('form_validation');
            $this->form_validation->set_rules('label', '', 'xss_clean|trim|required');
            $this->form_validation->set_rules('description', '', 'trim');
            $this->form_validation->set_message('required', lang('fv_required'));

            if ($this->form_validation->run()) {

                //  Prepare the create data
                $aSliderData                = array();
                $aSliderData['label']       = $this->input->post('label');
                $aSliderData['description'] = strip_tags($this->input->post('description'));
                $aSliderData['slides']      = $slides;

                if ($this->cms_slider_model->update($slider->id, $aSliderData)) {

                    $status  = 'success';
                    $message = 'Slider updated successfully.';

                    $this->session->set_flashdata($status, $message);
                    redirect('admin/cms/slider');

                } else {

                    $this->data['error']  = 'Failed to update slider. ';
                    $this->data['error'] .= $this->cms_slider_model->last_error();
                }

            } else {

                $this->data['error'] = lang('fv_there_were_errors');
            }

        } else {

            $slides = $slider->slides;
        }

        // --------------------------------------------------------------------------

        $this->data['page']->title = 'Edit Slider &rsaquo; ' . $slider->label;

        // --------------------------------------------------------------------------

        //  Prep the slides for the view
        foreach ($slides as $slide) {

            $slide->imgSourceUrl = !empty($slide->object_id) ? cdnServe($slide->object_id) : null;
            $slide->imgThumbUrl = !empty($slide->object_id) ? cdnScale($slide->object_id, 130, 130) : null;
        }

        // --------------------------------------------------------------------------

        //  Define the manager URL
        $cdnManagerUrl = cdnManageUrl('cms-slider', array('sliderEdit','setImgCallback'), null, isPageSecure());

        // --------------------------------------------------------------------------

        //  Assets
        $this->asset->load('jquery-ui/jquery-ui.min.js', 'NAILS-BOWER');
        $this->asset->load('mustache.js/mustache.js', 'NAILS-BOWER');
        $this->asset->load('nails.admin.cms.sliders.createEdit.min.js', true);
        $this->asset->inline('var sliderEdit = new NAILS_Admin_CMS_Sliders_Create_Edit();', 'JS');
        $this->asset->inline('sliderEdit.setScheme("serve", "' . $this->cdn->url_serve_scheme() . '");', 'JS');
        $this->asset->inline('sliderEdit.setScheme("thumb", "' . $this->cdn->url_thumb_scheme() . '");', 'JS');
        $this->asset->inline('sliderEdit.setScheme("scale", "' . $this->cdn->url_scale_scheme() . '");', 'JS');
        $this->asset->inline('sliderEdit.setManagerUrl("' . $cdnManagerUrl . '");', 'JS');
        $this->asset->inline('sliderEdit.addSlides(' . json_encode($slides) . ');', 'JS');

        // --------------------------------------------------------------------------

        \Nails\Admin\Helper::loadView('edit');
    }

    // --------------------------------------------------------------------------

    /**
     * Delete a CMS Slider
     * @return void
     */
    public function delete()
    {
        if (!userHasPermission('admin:cms:slider:delete')) {

            unauthorised();
        }

        // --------------------------------------------------------------------------

        //  Fetch and check post
        $sliderId = $this->uri->segment(5);
        $slider   = $this->cms_slider_model->get_by_id($sliderId);

        if (!$slider) {

            $this->session->set_flashdata('error', 'I could\'t find a slider by that ID.');
            redirect('admin/cms/slider');
        }

        // --------------------------------------------------------------------------

        if ($this->cms_slider_model->delete($slider->id)) {

            $status  = 'success';
            $message = 'Slider was deleted successfully.';

        } else {

            $status   = 'error';
            $message  = 'I failed to delete that slider. ';
            $message .= $this->cms_slider_model->last_error();
        }

        $this->session->set_flashdata($status, $message);
        redirect('admin/cms/slider');
    }
}
