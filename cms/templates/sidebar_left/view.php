<?php

	echo $this->load->view( 'structure/header', getControllerData() );

	// --------------------------------------------------------------------------

	echo '<h1>Sidebar content</h1>';
	echo $sidebar;

	echo '<h1>Mainbody content</h1>';
	echo $mainbody;

	// --------------------------------------------------------------------------

	echo $this->load->view( 'structure/footer', getControllerData() );