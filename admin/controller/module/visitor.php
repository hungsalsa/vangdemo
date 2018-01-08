<?php

/**
 * @version		$Id: visitor.php 1208 2010-07-04 09:03:01Z mic $
 * @package		FileZilla - Module 4 OpenCart - Admin Controller
 * @copyright	(C) 2010 mic [ http://osworx.net ]. All Rights Reserved.
 * @author		mic - http://osworx.net
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
class ControllerModuleVisitor extends Controller
{

    private $error = array();
    private $_name = '';
    private $_url;
    private $_type = 'module';
    private $_version = '1.0.3';

    /**
     * main function
     */
    public function index()
    {
        $this->getBasics();
        $this->getLanguage();
        $this->load->model('setting/setting');

        if (( $this->request->server['REQUEST_METHOD'] == 'POST' ) && ( $this->validate() ))
        {
            $this->model_setting_setting->editSetting($this->_name, $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->_url . 'extension/' . $this->_type);
        }

        $this->getLinks();
        $this->getParams();
        $this->getTemplate();
        $this->getBreadcrumbs();
        $this->getErrors();
        $this->getDocument();
        $this->getData();
		$this->data['modules'] = array();
		
		if (isset($this->request->post['visitor_module'])) {
			$this->data['modules'] = $this->request->post['visitor_module'];
		} elseif ($this->config->get('visitor_module')) { 
			$this->data['modules'] = $this->config->get('visitor_module');
		}			
				
		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();
        $this->response->setOutput($this->render(true), $this->config->get('config_compression'));
    }

    /**
     * several basic data used globally
     */
    protected function getBasics()
    {
        $this->getName();
        $this->getFooter();

        $this->data['extName'] = $this->_name;
        $this->data['version'] = $this->_version;
        $this->data['token'] = (!empty($this->session->data['token']) ? $this->session->data['token'] : '' );

        $this->_url = HTTPS_SERVER . 'index.php?token=' . $this->data['token'] . '&amp;route=';
        $this->_path = DIR_DOWNLOAD;
        $this->_images = HTTPS_SERVER . 'view/image/osworx/';
    }

    /**
     * gets the module name out of the class
     */
    private function getName()
    {
        $this->_fName = str_replace('Controller', '', get_class($this));
        $this->_fName = str_replace(ucfirst($this->_type), '', $this->_fName);
        $this->_name = strtolower($this->_fName);
    }

    /**
     * get data
     */
    private function getData()
    {
        $this->data['module'] = $this->_name;
        $this->data['version'] = $this->_version;
        $this->data['themes'] = array(
            array('val' => 'Shopping', 'text' => $this->data['entry_th_shopping']),
            array('val' => 'Users', 'text' => $this->data['entry_th_users'])
        );
    }

    /**
     * - adds javascript and css into document header
     * - defines document title
     */
    private function getDocument()
    {
        $this->document->setTitle = $this->language->get('heading_title');
    }

    /**
     * build href links
     */
    private function getLinks()
    {
        $this->data['action'] = $this->_url . $this->_type . '/' . $this->_name;
        $this->data['cancel'] = $this->_url . 'extension/' . $this->_type;
    }

    /**
     * build breadcrumbs
     * @param array	additional breadcrumbs (optional) as text
     */
    private function getBreadcrumbs($add = null)
    {
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'href' => $this->_url . 'common/home',
            'text' => $this->language->get('text_home'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'href' => $this->_url . 'extension/' . $this->_type,
            'text' => $this->language->get('text_module'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'href' => $this->_url . $this->_type . '/' . $this->_name,
            'text' => $this->language->get('heading_title'),
            'separator' => ' :: '
        );

        if ($add)
        {
            foreach ($add as $ad)
            {
                $this->document->breadcrumbs[] =
                        array(
                            'href' => 'javascript:void(0);',
                            'text' => $ad,
                            'separator' => ' :: '
                );
            }
        }
    }

    /**
     * get locale and active languages
     */
    private function getLocaleLangs()
    {
        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
    }

    /**
     * get language vars
     * @param array		$arr	optional vars to get
     * @param string	$lng	optional language file to load
     */
    private function getLanguage($arr = null, $lng = '')
    {
        if (!$lng)
        {
            $langs = $this->load->language($this->_type . '/' . $this->_name);
        }
        else
        {
            $langs = $this->load->language($lng);
        }

        $this->getLocaleLangs();

        $this->data = array_merge($this->data, $langs);
    }

    /**
     * get params
     */
    private function getParams()
    {
        $params = array(
            // standard
            'position', 'status', 'sort_order',
            // specific
            'theme', 'expire'
        );

        foreach ($params as $param)
        {
            $this->getParam('_' . $param);
        }

        unset($params);
    }

    /**
     * get a single value either from request or config
     * @param string	$param	parameter name to fetch
     * @param bool		$ret	return the value (optional) or define the data array (standard))
     */
    private function getParam($param, $ret = false)
    {
        $name = $this->_name . $param;

        if ($this->getRequest($name))
        {
            if ($ret)
            {
                return $this->getRequest($name);
            }
            $this->data[$name] = $this->getRequest($name);
        }
        else
        {
            if ($ret)
            {
                return $this->config->get($name);
            }
            $this->data[$name] = $this->config->get($name);
        }
    }

    /**
     * get a value from a request
     * @param string	$value		value to fetch
     * @param mixed		$default	default value if empty or not set [optional]
     * @param string	$from		Force to where the var should come from (POST, GET, FILES, COOKIE, SERVER, METHOD) [optional]
     * @return mixed
     */
    private function getRequest($value, $default = null, $from = '')
    {
        $from = strtolower($from);
        $ret = $default;

        if ($from && isset($this->request->{$from}[$value]))
        {
            $ret = $this->request->{$from}[$value];
        }
        else
        {
            if (isset($this->request->post[$value]))
            {
                $ret = $this->request->post[$value];
            }
            elseif (isset($this->request->get[$value]))
            {
                $ret = $this->request->get[$value];
            }
        }

        return $ret;
    }

    /**
     * define template params
     * @param string	$suffix	optional suffix (e.g. voucher_cpanel.tpl where _cpanel IS the suffix)
     * @param string	$folder	optional folder (within type/folder ) if template is NOT in the main folder
     */
    private function getTemplate($suffix = '', $folder = '')
    {
        $this->template = $this->_type . '/' . ( $folder ? $folder . '/' : '' ) . $this->_name . $suffix . '.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );
    }

    /**
     * get error messages
     */
    private function getErrors()
    {
        if (isset($this->error['warning']))
        {
            $this->data['error_warning'] = $this->error['warning'];
        }
        else
        {
            $this->data['error_warning'] = '';
        }

        if (isset($this->error['expire']))
        {
            $this->data['error_expire'] = $this->error['expire'];
        }
        else
        {
            $this->data['error_expire'] = '';
        }
    }

    /**
     * constructs the footer
     *
     * Note: displaying this footer is mandatory, removing violates the license!
     * If you do not want to display the footer, contact the author.
     */
    private function getFooter()
    {
        $this->data['oxfooter'] = '<div style="text-align:center; color:#666666; margin-top:5px">'
                . $this->_fName
                . ' Module v.' . $this->_version
                . ' &copy; '
                . date('Y')
                . ' by <a href="http://osworx.net" target="_blank">OSWorX</a>'
                . '</div>'
        ;
    }

    /**
     * validates user permission and checks specific module vars
     */
    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'module/' . $this->_name))
        {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post[$this->_name . '_expire'])
        {
            $this->error['expire'] = $this->language->get('error_expire');
        }

        if (!$this->error)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}