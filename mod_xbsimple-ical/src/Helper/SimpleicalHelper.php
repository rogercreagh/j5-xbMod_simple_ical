<?php
/*******
 * @package xbSimple-ical
 * @filesource mod_xbsimple-ical/src/Helper/SimpleicalHelper.php
 * @version 0.2.0.0 8th May 2026
 * @copyright Copyright (c) Roger Creagh-Osborne, 2026
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

/**
 * @version $Id: SimpleicalHelper.php 
 * @package simpleicalblock
 * @subpackage simpleicalblock Module
 * @copyright Copyright (C) 2022 -2026 A.H.C. Waasdorp, All rights reserved.
 * @license GNU General Public License version 3 or later
 * @author url: https://www.waasdorpsoekhan.nl
 * @author email contact@waasdorpsoekhan.nl
 * @developer A.H.C. Waasdorp
 * 
 */
namespace Crosborne\Module\Xbsimpleical\Site\Helper;
// no direct access
defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Date\Date as Jdate;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Filter\InputFilter;
use Joomla\Registry\Registry;
use Crosborne\Module\Xbsimpleical\Site\IcsParser;

/**
 * Helper for mod_simple-icalblock
 *
 * @since  1.0
 */
class SimpleicalHelper
{
    const SIB_ATTR = 'simple_ical_block_attrs';
    /**
     * tags allowed for summary
     *
     * @var array
     */
    static $allowed_tags_sum = [
        'a',
        'b',
        'div',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'i',
        'span',
        'strong',
        'summary',
        'u'
    ];
    /*
     * @var array allowed tags for text-output
     */
    static $allowed_tags = ['a','abbr', 'acronym', 'address','area','article', 'aside','audio',
        'b','big','blockquote', 'br','button', 'caption','cite','code','col', 'del',
        'details', 'div',
        'em',
        'fieldset',
        'figcaption',
        'figure',
        'footer',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hr',
        'i',
        'img',
        'li',
        'label',
        'legend',
        'ol',
        'p',
        'q',
        's',
        'section',
        'small',
        'span',
        'strike',
        'strong',
        'summary',
        'u',
        'ul'
    ];
    /*
     * @var array allowed attributes for html-text-output
     */
    static $allowed_attrs = [
        'aria-controls',
        'aria-current',
        'aria-describedby',
        'aria-details',
        'aria-expanded',
        'aria-hidden',
        'aria-label',
        'aria-labelledby',
        'aria-live',
        'class',
        'cite',
        'data-bs-animation',
        'data-bs-container',
        'data-bs-content',
        'data-bs-delay',
        'data-bs-dismiss',
        'data-bs-html',
        'data-bs-offset',
        'data-bs-offset-bottom',
        'data-bs-offset-top',
        'data-bs-placement',
        'data-bs-selector',
        'data-bs-spy',
        'data-bs-target',
        'data-bs-template',
        'data-bs-toggle',
        'data-bs-title',
        'data-bs-trigger',
        'data-bs-viewport',
        'data-toggle',
        'data-target',
        'data-sib-id',
        'data-sib-st',
        'datetime',
        'dir',
        'hidden',
        'href',
        'id',
        'lang',
        'role',
        'style',
        'title',
        'type',
        'xml:lang'
    ] ;
    /*
     * @var class InputFilter to initialize it only once.
     */
    static $input_fl = null;
    /**
     * default value for block_attributes (or instance)
     *
     * @var array
     */
    static $default_block_attributes = [
        'sibid' => '',
        'calendar_id' => '',
        'event_count' => 10,
        'event_period' => 92,
        'transient_time' => 60,
        'categories_filter_op' => '',
        'categories_filter' => '',
        'categories_display' => '',      
        'sib_layout' => 3,
        'dateformat_lg' => '',
        'dateformat_lgend' => '',
        'tag_sum' => 'a',
        'dateformat_tsum' => '',
        'dateformat_tsend' => '',
        'dateformat_tstart' => '',
        'dateformat_tend' => '',
        'excerptlength' => '',
        'suffix_lg_class' => '',
        'suffix_lgi_class' => ' py-0',
        'suffix_lgia_class' => '',
        'allowhtml' => true,
        'after_events' => '',
        'no_events' => '',
        'categories_filter_op' => '',
        'categories_filter' => '',
        'categories_display' => '',
        'add_sum_catflt' => false,
        'clear_cache_now' => false,
        'period_limits' => '1',
        'tzid_ui' => '',
        'className' => '',
        'anchorId' => '',
   		'title_collapse_toggle' => '',
		'add_collapse_code' => false,
        'before_title'  => '<h3 class="widget-title block-title">',
        'after_title'   => '</h3>'
    ];
    /**
     * copied from WP sanitize_html_class, and added space as allowed character to accomodate multiple classes in one string.
     * Strips the string down to A-Z, ,a-z,0-9,_,-. If this results in an empty string then it will return the alternative value supplied.
     *
     * @param string $class
     * @param string $fallback
     * @return string sanitized class or fallback.
     */
    static function sanitize_html_clss( $class, $fallback = '' ) {
        // Strip out any %-encoded octets.
        $sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', (string) $class );
        
        // Limit to A-Z, ' ', a-z, 0-9, '_', '-'.
        $sanitized = preg_replace( '/[^A-Z a-z0-9_-]/', '', $sanitized );
        
        if ( '' === $sanitized && $fallback ) {
            return  $fallback;
        }
        return $sanitized;
    }
    /**
     * Merge block attributes with defaults to be sure they exist is necesary.
     *
     * @param array $block_attributes the module params object presented as array ($params->toArray())
     * @return array attributes from parameters merged with default  attributes.
     */
    static function render_attributes($block_attributes) {
        $block_attributes =  array_merge(
            self::$default_block_attributes,
            $block_attributes
            );
        if (!in_array($block_attributes['tag_sum'], self::$allowed_tags_sum)) $block_attributes['tag_sum'] = 'a';
        $block_attributes['suffix_lg_class'] = self::sanitize_html_clss($block_attributes['suffix_lg_class']);
        $block_attributes['suffix_lgi_class'] = self::sanitize_html_clss($block_attributes['suffix_lgi_class']);
        $block_attributes['suffix_lgia_class'] = self::sanitize_html_clss($block_attributes['suffix_lgia_class']);
        
        return $block_attributes;
    }
    /**
     * copied from WP sanitize_html_class. (only for one class)
     * Strips the string down to A-Z,a-z,0-9,_,-. If this results in an empty string then it will return the alternative value supplied.
     *
     * @param string $class
     * @param string $fallback
     * @return string sanitized class or fallback.
     */
    static function sanitize_html_class( $class, $fallback = '' ) {
        // Strip out any %-encoded octets.
        $sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', (string) $class );
        
        // Limit to A-Z, a-z, 0-9, '_', '-'.
        $sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '', $sanitized );
        
        if ( '' === $sanitized && $fallback ) {
            return  $fallback;
        }
        return $sanitized;
    }
    /**
     * call Rest / Ajax component.
     * Get block content wth sibid, (= active menu Itemid,)  and client timezone from request
     * use layout template with templatename wthout 'rest-' or 'ajax-' or default.
     * 
     * @param Input object $input $app-> 
     * 
     * @return JsonResponse object $data 
     * ["succes":   {true|false},
     *  "message":  {null|string}
     *  "messages": {null|array}
     *  "data":     ["content": {null| string: content of module},
     *               "params":  {null| array: used params ] 
     * ]
     * since 2.4.0
     * 
     */
    public static function getAjax()
    {   $app = Factory::getApplication();
        $input = $app->getInput();
        $ippars = $input->getArray();
        unset($ippars['option'],$ippars['module'],$ippars['method'],$ippars['view'],);
        if (empty($ippars['sibid'])) {
            $secho = '<p>' .  Text::_('MOD_SIMPLEICALBLOCK_EMPTYSIBID') .'</p>';
        } else {
            $module = ModuleHelper::getModuleById($ippars['sibid']);
            if (empty($module->params)) {
                $secho = '<p>' .  Text::_('MOD_SIMPLEICALBLOCK_NOPARAMS') .'</p>';
            } else {
                $secho = '';
                $attributes = self::render_attributes( array_merge( json_decode($module->params, true), $ippars));
                $params = new Registry($attributes); // for use in layout (overrides)
                $path = str_ireplace(['ajax-', 'rest-'] ,['',''], (string) $attributes['layout']);
                if ($attributes['layout'] == $path) $path = '_:default';
                $path = ModuleHelper::getLayoutPath($module->module, $path );
                $nohead = true;
                if (is_file( $path)) {
                    ob_start(); // catch echoed and cleaned layout output
                    require $path;
                    $secho .= ob_get_clean();
                }
                else{
                    self::display_block($attributes, $secho);
                    $secho = self::clean_output($secho);
                }
            }
        }
        $data = [
            'content' => $secho,
            'params' => $ippars
        ];
        return $data;
    }
    /**
     * Clean output
     *
     * since 2.6.0
     *
     * @param string $output utput to clean
     * 
     * @return string escaped HTML output to render for the block (frontend)
     */
    static function clean_output($output)
    {
        if (empty(self::$input_fl)) {
            self::$input_fl = new InputFilter(self::$allowed_tags, self::$allowed_attrs, InputFilter::ONLY_ALLOW_DEFINED_TAGS, InputFilter::ONLY_ALLOW_DEFINED_ATTRIBUTES);
        }
        return self::$input_fl->clean($output,'HTML');
    }
    
}
