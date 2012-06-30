<?php
ini_set('display_errors','On');
/**
* @package		Joomla.Plugin
* @subpackage   Content.Checkfront
* @version 	    2.0
* @copyright	Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
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
		if ($context != 'com_content.article') {
			if(isset($article->readmore_link)) {
				$link = '<a href="' . urlencode($article->readmore_link) . '">" . PLG_CONTENT_CHECKFRONT_CONTINUE_BOOKING . "</a>';
			} else {
				$link = '';
			}
			$article->text = preg_replace('/{checkfront(.*)}/iU', $link, $article->text);
			return true;
		}


		$u =& JFactory::getURI();

		$schema = $u->isSSL() ? 'https' : 'http';
		if(preg_match('/{checkfront(.*)}/iU', $article->text,$match)) {
			$plugin = & JPluginHelper::getPlugin('content', 'checkfront');

			$cnf = array(
				'style'=>'',
				'category_id'=>'',
				'item_id'=>'',
				'options'=>'',
			);

			$style = array();
			if($color = $this->params->def('color', 0)) {
				$style[]= "color:{$color}";
			}

			if($background= $this->params->def('background', 0)) {
				$style[]= "background-color:{$background}";
			}

			if($font = $this->params->def('font', 0)) {
				$style[]= "font-family:{$background}";
			}

			if(count($style)) {
				$cnf['style'] = implode(';',$style);
			}
			if($options= $this->params->def('options', 0)) {
				$cnf['options'] = implode(',',$options);
			}
		


			$this->shortcode($match[1],$cnf);


			// Load plugin params info
			//
			$url= $this->params->def('CF_url', 0);
			$interface = $this->params->def('interface', 'v1');
			$mode = $this->params->def('mode', 0);

			if(!$url) {
				$article->text = preg_replace('/{checkfront}/iU', '<p style="padding:1em; border: solid 1px firebrick; font-weight: bold;">Checkfront not configured</p>', $text );
				return true;
			}

			$root_dir = JPATH_SITE . DS . 'plugins' . DS . 'content' . DS . 'checkfront'. DS;


			$url = preg_replace('/[^\w\d\-\.]/','',$url);
			$doc =& JFactory::getDocument();
			include_once($root_dir. 'CheckfrontWidget.php');

			if($interface == 'v2') {
				$doc->addScript($schema . '://' . $url . '/lib/interface--4.js');
			} else {
				$doc->addScript($schema . '://' . $url . '/www/client.js?joomla');
			}

			$Checkfront = new CheckfrontWidget(
				array(
					'host'=>$url,
					'pipe_url'=> JURI::base() . $this->path . 'pipe.html',
					'interface' =>$interface,
					'provider' =>'joomla',
					'load_msg'=>JText::_('PLG_CONTENT_CHECKFRONT_SEARCHING_AVAILABILITY'),
					'continue_msg'=>JText::_('PLG_CONTENT_CHECKFRONT_CONTINUE_BOOKING'),
				)
			);
			$article->text = preg_replace('/{checkfront(.*)}/iU', $Checkfront->render($cnf), $article->text);
			return true;
		}
	}

	protected function shortcode($text,&$cnf) {
		$atts = array();
		$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
		$text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
		if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
			foreach ($match as $m) {
				if (!empty($m[1]))
					$atts[strtolower($m[1])] = stripcslashes($m[2]);
				elseif (!empty($m[3]))
					$atts[strtolower($m[3])] = stripcslashes($m[4]);
				elseif (!empty($m[5]))
					$atts[strtolower($m[5])] = stripcslashes($m[6]);
				elseif (isset($m[7]) and strlen($m[7]))
					$atts[] = stripcslashes($m[7]);
				elseif (isset($m[8]))
					$atts[] = stripcslashes($m[8]);
			}
		} else {
			$atts = ltrim($text);
		}
		$cnf = array_merge($cnf,$atts);
	}
}
