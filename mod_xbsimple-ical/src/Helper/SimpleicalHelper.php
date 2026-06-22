<?php
/*******
 * @package xbSimple-ical
 * @filesource mod_xbsimple-ical/src/Helper/SimpleicalHelper.php
 * @version 0.2.4.0 21st June 2026
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

use DateTime;
use Joomla\CMS\Date\Date as Jdate;
use NumberFormatter;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Filter\InputFilter;
use Joomla\Registry\Registry;
use Crosborne\Module\Xbsimpleical\Site\IcsParser;
use Joomla\CMS\HTML\Helpers\Links;

/**
 * Helper for mod_simple-icalblock
 *
 * @since  1.0
 */
class SimpleicalHelper
{
    const SIB_ATTR = 'xbsimple-ical_attrs';
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
        'target',
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
     * @desc copied from WP sanitize_html_str, and added space as allowed character to accomodate multiple classes in one string.
     * Strips the string down to A-Z, ,a-z,0-9,_,-. If this results in an empty string then it will return the alternative value supplied.
     * added $allow param to specify extra chars to be allowed (eg punctuation)
     * @param string $class
     * @param string $fallback
     * @param string $allow
     * @return string sanitized class or fallback.
     */
    /**
    static function sanitize_html_str( $class, $fallback = '', $allow = '' ) {
        // Strip out any %-encoded octets.
        $sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', (string) $class );
        
        // Limit to A-Z, ' ', a-z, 0-9, '_', '-'.
        $sanitized = preg_replace( '/[^A-Z a-z0-9_-'.$allow.']/', '', $sanitized );
        
        if ( '' === $sanitized && $fallback ) {
            return  $fallback;
        }
        return $sanitized;
    }
    */
    
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
        $block_attributes['suffix_lg_class'] = self::sanitize_html_str($block_attributes['suffix_lg_class'],'_ -');
        $block_attributes['suffix_lgi_class'] = self::sanitize_html_str($block_attributes['suffix_lgi_class'],'_ -');
        $block_attributes['suffix_lgia_class'] = self::sanitize_html_str($block_attributes['suffix_lgia_class'],'_ -');
        
        return $block_attributes;
    }
    /**
     * @name sanitize_html_str()
     * @desc copied from WP sanitize_html_str. (only for one class)
     * Strips the string down to A-Z,a-z,0-9 plus the $allow param. 
     * If this results in an empty string then it will return the $fallback param.
     *
     * @param string $str2clean
     * @param string $allow - default to hyphen and underscore
     * @param string $fallback - default to empty string
     * @return string sanitized string or fallback.
     */
    static function sanitize_html_str( $str2clean, $allow = '_-', $fallback = ''  ) {
        // Strip out any %-encoded octets.
        $sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', (string) $str2clean );        
        // Limit to A-Z, a-z, 0-9, '_', '-'.
        $sanitized = preg_replace( '/[^A-Za-z0-9'.$allow.']/', '', $sanitized );        
        if ( $sanitized === '' ) {
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
    
    /**
     * @name makeUrlstoLinks()
     * @desc parses a string replacing any text urls with html links to the url,
     *  options to show just hostname as link text and to open link in new window/tab.
     *  Will detect space followed by https:// or http:// or www. as the start of a url and a space as the end.
     *  Prepends https:// to urls that start with www. and no scheme.
     *  Strips www. from link text if displaying hostname only.
     *  Will ignore any existing html <a tags leaving them intact
     * @param string $string  - the text to have urls converted to Links
     * @param bool $hostonly - true = only the hostname will be displayed, false = full url as link text
     * @param bool $newtab - true = open links in new tab,false = current window
     * @return string $string with links inserted as appropriate
     */
    static function makeUrlstoLinks(string $string, $hostonly = true, $newtab = true) {
        $reg_pattern = '`<a\b[^>]*>.*?<\/a>(*SKIP)(*FAIL)|\b(?:https?://|www)[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))`';
        preg_match_all($reg_pattern, $string, $match);
        $targ = ($newtab) ? 'target="_blank"' : '';
        foreach ($match[0] as $value) {
            $url = $value;
            // if url starts with www then prefix https
            if (preg_match('/^www\./',$url)) $url = 'https://'.$url;
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                if ($hostonly) {
                    $linktext = parse_url($url,PHP_URL_HOST);
                    // if host starts with www. strip it from link text
                    $linktext = preg_replace('/^www\./', '', $linktext);
                } else {
                    // show the original value for link text
                    $linktext = $value;
                }
                $string = str_replace($value,'<a href="'.$url.'" '.$targ.'>'.$linktext.'</a>',$string);
            } // if url doesn't pass validation then leave it untouched
        }
        return $string;
    }
    
    /**
     * @name parseKeyValueStr()
     * @desc returns an array of ['key1'=>'val1',...] from a string of 'key1:val1,$key:val2...'
     * the separator characters between pairs and between key and value can be specified in the params
     * @param string $valstr - string containing key:value pairs
     * @param string $sep1 - defaults to comma ','
     * @param string $sep2 - defaults to colon ':'
     * @return array[]
     */
    static function parseKeyValueStr(string $valstr, $sep1 = ",", $sep2 = ":") {
        $valarr = [];
        foreach (explode($sep1, $valstr) as $pair) {
            list($key, $value) = explode($sep2, $pair);
            $valarr[trim($key)] = trim($value);
        }        
        return $valarr;
    }
    
    /**
     * @name int2orderstr()
     * @desc converts integer to string with ordinal suffix
     * if php intl is loaded will use current language suffixes
     * @param int $number
     * @return string
     */
    static function int2ordstr($number){
        if ($number<0) $number=$number * -1;
        if (extension_loaded('intl')) {
            $lang = Factory::getApplication()->get('tag');
            $formatter = new NumberFormatter($lang, NumberFormatter::ORDINAL);
            return $formatter->format($number);
        } else {
            $last = substr($number, -1);
            $lastTwo = substr($number, -2);
            
            // Handle 11th, 12th, 13th exceptions
            if ($lastTwo >= 11 && $lastTwo <= 13) {
                return $number . 'th';
            }
            
            switch ($last) {
                case '1': return $number . 'st';
                case '2': return $number . 'nd';
                case '3': return $number . 'rd';
                default:  return $number . 'th';
            }
        }
        return (string) $number;
    }
    
    /**
     * @name iso8601toStr()
     * @desc converts ISO8601 format date to text string per given format string
     * @param string $iso
     * @param string $fmt
     * @return string
     */
    static function iso8601toStr(string $iso, $fmt = 'D jS M \'y') {
        $date = DateTime::createFromFormat('Ymd\THis\Z', strtoupper($iso));       
        if ($date) {
            return $date->format($fmt);
        } else {
            return "Invalid date format";
        }        
    }
    
    /**
     * @name rrule2text()
     * @desc given recurrence elements for VEVENT will returns human readable text version
     * @param string $rrule
     * @return string
     */
    static function rrule2text(string $rrule) {
        // make rule lower case to avoid errors with case
        $rrule = strtolower($rrule);
        $rulearr = self::parseKeyValueStr($rrule,";","=");
        
        $text = "";
        // FREQ is required
        if (!isset($rulearr['freq'])) return '';
        $cntstr = (isset($rulearr['count'])) ? ' '.$rulearr['count'].' times ' : '';
        $tillstr = (isset($rulearr['until'])) ? ' until '.self::iso8601toStr($rulearr['until']) : '';
        $intstr = ''; 
        $text = Text::_('Repeats').' '.$rulearr['freq'].$cntstr.$tillstr;
        switch ($rulearr['freq']) {
            case 'daily':
                $intstr = 'days';
                break;
            case 'weekly':
                $intstr = 'weeks';
                // $interval = 'weekly ';
             //   $text .= $interval;
                $daysarr = explode(',',$rulearr['byday']);
                $daystr = '';
                $langprefix = (count($daysarr) > 1) ? 'XBIC_S' : 'XBIC_';
                foreach ($daysarr as $day) {
                    $daystr .= Text::_($langprefix.strtoupper($day)).', ';
                }
                $daystr = trim($daystr,', '); //get rid of trailing comma & space
                // strings are reversed to find the first instead of last comma
                if (count($daysarr) > 1) $daystr = strrev(preg_replace(strrev("/,/"),';pma& ',strrev($daystr),1));
                $text .= ' on '.$daystr;               
                break;
            case 'monthly':
                $intstr = 'months';
                if (isset($rulearr['byday'])) {
                    $byday = $rulearr['byday'];
                    // get the day of week
                    $day = 'XBIC_'.strtoupper(ltrim($byday,'- 0..9'));
                    $num = (int) $rulearr['byday'];
                    $ord = self::int2ordstr($num);
                    if (($num > 0) && ($num < 6)) { //never more than 5 mondays (eg) in a month
                        //we've got a numbered weekday of the month
                        $ord = self::int2ordstr($num);
                        // every Nth of WEEKDAY the month
                        $text .= ' on '.$ord.' '.Text::_($day);
                    } elseif ($num === -1) {
                        $text .= ' on the last '.Text::_($day);
                    } elseif (($num < -1) && ($num > -6)) {
                        $text .= ' on the '.$ord.' to last '.Text::_($day);
                    } else {
                        // this is impossible 
                        $text = '';
                    }                   
                } elseif (isset($rulearr['bymonthday'])) {
                    $ord = self::int2ordstr($rulearr['bymonthday']);
                    $text .= ' on the '.$ord;
                   
                }
                break;
            default:
                $text = '';
            break;
        }   
        if (isset($rulearr['interval']) && ($rulearr['interval'] > 1)) {
            $text .= ' every '.$rulearr['interval'].' '.$intstr;
        }       
        return $text;
    }
    
}
