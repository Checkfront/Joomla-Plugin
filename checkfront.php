<?php
/**
* @package		Joomla.Plugin
* @subpackage   Content.Checkfront
* @version 	    3.0
* @copyright	Copyright (C) 2008-2014 Checkfront Inc. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @link 		https://github.com/Checkfront/Joomla-Plugin
* @link 		http://www.checkfront.com/joomla
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Embed a Checkfront booking window into a Joomla page.
 *
 * @param object|string to be sourced 
 * @param array Additional parameters. See {@see plgCheckfront()}.
 * @param int Optional page number. Unused. Defaults to zero.
 * @return boolean True on success.
 */


class plgContentCheckfront extends JPlugin {

	private $path = 'plugins/content/checkfront/';
	
	/**
	 * @param   string  The context of the content being passed to the plugin.
	 * @param   object  The article object.  Note $article->text is also available
	 * @param   object  The article params
	 * @param   int     The 'page' number
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0) {

		JPlugin::loadLanguage('plg_content_checkfront');

		// Don't run this plugin when the content is being indexed
		if($context != 'com_content.article' && isset($article->readmore_link)) {
			$link = '<a href="' . urlencode($article->readmore_link) . '">' . JText::_('PLG_CONTENT_CHECKFRONT_CONTINUE_BOOKING') . '</a>';
			$article->text = preg_replace('/[\[|{]checkfront(.*?)[\]|}]/iU', $link, $article->text);
			return true;
		}

		$article->text = preg_replace_callback('/[\[|{]checkfront(.*?)[\]|}]/iU', array($this, 'renderWidget'), $article->text);
	}
	
	
	protected function renderWidget($shortcode) {
		$url = $this->params->get('CF_url');
		if(!preg_match('~^http://|https://~', $url)) $url = 'https://' . $url;		
		$host = parse_url($url, PHP_URL_HOST);

		if (!$host) {
			return '<p style="padding:1em; border: solid 1px firebrick; font-weight: bold;">Checkfront not configured!</p>';
		}

		$root_dir = JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'checkfront'. DIRECTORY_SEPARATOR;
		include_once($root_dir . 'CheckfrontWidget.php');
		
		$Checkfront = new CheckfrontWidget(
			array(
				'host' => $host,
				'pipe_url'=> JURI::base() . $this->path . 'pipe.html',
				'provider' => 'joomla',
				'load_msg'=> JText::_('PLG_CONTENT_CHECKFRONT_SEARCHING_AVAILABILITY'),
				'continue_msg' => JText::_('PLG_CONTENT_CHECKFRONT_CONTINUE_BOOKING'),
			)
		);
		
		$config = array(
			'category_id' => '0',
			'item_id' => '0',
			'tid' => '',
			'discount' => '',
			'options' => '',
			'style' => '',
			'width' => '',
			'theme' => '',
			'category_id' => '',
			'item_id' => '',
			'lang_id' => '',
			'widget_id' => uniqid()
		);

		$this->parseShortcode($shortcode[1], $config);

		$doc = JFactory::getDocument();
		$doc->addScript('//' . $host . '/lib/interface--' . $Checkfront->interface_version . '.js');

		return $Checkfront->render($config);
	}

	
	protected function parseShortcode($shortcode, &$cnf) {
		preg_match_all('/(\w+)\s*=\s*(?|"([^"]*)"|\'([^\']*)\'|([^ ]+))/', $shortcode, $args, PREG_SET_ORDER);
		
		foreach($args as $val) {
			$cnf[$val[1]] = $val[2];
		}		
	}
}
