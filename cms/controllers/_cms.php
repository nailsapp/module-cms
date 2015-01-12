<?php

/**
 * This class provides some common CMS controller functionality
 *
 * @package     Nails
 * @subpackage  module-cms
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

class NAILS_CMS_Controller extends NAILS_Controller
{
	protected $_cdn_root;

	// --------------------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();

		// --------------------------------------------------------------------------

		//	Check this module is enabled in settings
		if ( ! module_is_enabled( 'cms' ) ) :

			//	Cancel execution, module isn't enabled
			show_404();

		endif;

		// --------------------------------------------------------------------------

		//	Load language file
		$this->lang->load( 'cms' );
	}
}