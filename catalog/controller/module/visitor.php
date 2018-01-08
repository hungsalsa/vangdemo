<?php
/**
 * @version		$Id: visitor.php 1208 2010-07-04 09:03:01Z mic $
 * @package		FileZilla - Module 4 OpenCart
 * @copyright	(C) 2010 mic [ http://osworx.net ]. All Rights Reserved.
 * @author		mic - http://osworx.net
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

class ControllerModuleVisitor extends Controller
{
	private $_name;
	private $_type		= 'module';
	private $_version	= '1.0.3';

	/**
	 * main function
	 */
	protected function index() {
		$this->getName();
		$this->data['version']	= $this->_version;
		$this->id				= $this->_name;

		$this->getLanguage();
		$this->getParams();
		$this->getTemplate();
		$this->getFooter();
		$this->getData();

		$this->render();
	}

	/**
	 * gets the module name out of the class
	 */
	private function getName() {
        $this->_fName	= str_replace( 'ControllerModule', '', get_class( $this ) );
		$this->_name	= strtolower( $this->_fName );
    }

	/**
	 * get language vars
	 */
	private function getLanguage() {
		$this->language->load( $this->_type .'/'. $this->_name );

		// standard params
		$txtParams = array(
			'heading_title', 'text_today', 'text_week', 'text_month',
			'text_year', 'text_all', 'text_online'
		);

		foreach( $txtParams as $txtParam ) {
			$this->data[$txtParam]	= $this->language->get( $txtParam );
		}

		unset( $txtParams );
	}

	/**
	 * get params
	 */
	private function getParams() {
		// module specific
		$this->data['theme']	= $this->config->get( $this->_name . '_theme' );
		$this->data['imgPath']	= 'catalog/view/theme/default/image/counter/' . $this->data['theme'] .'/';
	}

	/**
	 * get template datas
	 */
	private function getTemplate() {
		$tmpl = '/template/' . $this->_type .'/'. $this->_name . '.tpl';

		if( file_exists( DIR_TEMPLATE . $this->config->get( 'config_template' ) . $tmpl ) ) {
			$this->template = $this->config->get( 'config_template' ) . $tmpl;
		}else{
			$this->template = 'default' . $tmpl;
		}
	}

	/**
	 * get data from a flat file
	 */
	private function getData() {
		$this->load->model( 'tool/file_data' );

		$this->data['data'] = $this->model_tool_file_data->getFileData( $this->_name );
	}

	/**
	 * constructs the footer
	 *
	 * Note: displaying this footer is mandatory, removing violates the license!
	 * If you do not want to display the footer, contact the author.
	 */
	private function getFooter() {
		$this->data['oxfooter']	= "\n" . '<!-- Module ' . $this->_fName .' v.'. $this->_version . ' by http://osworx.net -->' . "\n";
	}
}