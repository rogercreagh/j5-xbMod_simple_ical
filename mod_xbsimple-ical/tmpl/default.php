<?php
/*******
 * @package xbSimple-ical
 * @filesource mod_xbsimple-ical/tmpl/default.php
 * @version 0.1.1.0 1st May 2026
 * @copyright Copyright (c) Roger Creagh-Osborne, 2026
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

/**
 * @version $Id: default.php
 * @package simpleicalblock
 * @subpackage simpleicalblock Module
 * @copyright Copyright (C) 2022 -2026 simpleicalblock, All rights reserved.
 * @license GNU General Public License version 3 or later
 * @author url: https://www.waasdorpsoekhan.nl
 * @author email contact@waasdorpsoekhan.nl
 * @developer A.H.C. Waasdorp
 *
 *
 */
// no direct access
defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Date\Date as Jdate;
use Joomla\CMS\Factory;
use Crosborne\Module\Xbsimpleical\Site\IcsParser;
use Crosborne\Module\Xbsimpleical\Site\Helper\SimpleicalHelper;

if (!empty($wa)) {
    $wa->addInlineStyle('.simple_ical_block p[hidden]{display:none !important;}', ['name' => 'simple-ical-block-inline-style']);
    $wa->useStyle('xbsimpleical.styles');
}
if (empty($secho)) {  $secho = ''; }

if (empty($nohead) ) {
    $attributes = SimpleicalHelper::render_attributes( $params->toArray());
    $secho .= '<div id="' . $attributes['anchorId']  .'" data-sib-id="' . $attributes['sibid'] . '" ' . ' class="simple_ical_block ' . $attributes['title_collapse_toggle']. '" >';
}
/**
 * Front-end display of module, block or widget.
 *
 * @see
 *
 * @param array $attributes
 * @param string &$secho (reference to $secho), output to echo in calling function, to simplify escaping output by replacing multiple echoes by one
 *            Saved attribute/option values from database.
 * was static function display_block($attributes, &$secho)
 */
 // start
{
    try {
        $attributes['tz_ui'] = new \DateTimeZone($attributes['tzid_ui']);
    } catch (\Exception $exc) {}
    if (empty($attributes['tz_ui']))
        try {
            $attributes['tzid_ui'] = str_replace('Etc/GMT ','Etc/GMT+',$attributes['tzid_ui']);
            $attributes['tz_ui'] = new \DateTimeZone($attributes['tzid_ui']);
    } catch (\Exception $exc) {}
    if (empty($attributes['tz_ui']))
        try {
            $attributes['tzid_ui'] = Factory::getApplication()->get('offset');
            $attributes['tz_ui'] = new \DateTimeZone($attributes['tzid_ui']);
    } catch (\Exception $exc) {}
    if (empty($attributes['tz_ui'])) {
        $attributes['tzid_ui'] = 'UTC';
        $attributes['tz_ui'] = new \DateTimeZone('UTC');
    }
    $layout = (isset($attributes['sib_layout'])) ? intval($attributes['sib_layout']) : 3;
    $dflg = (isset($attributes['dateformat_lg'])) ? $attributes['dateformat_lg'] : 'l jS \of F';
    $dflgend = (isset($attributes['dateformat_lgend'])) ? $attributes['dateformat_lgend'] : '';
    $dftsum = (isset($attributes['dateformat_tsum'])) ? $attributes['dateformat_tsum'] : 'G:i ';
    $dftsend = (isset($attributes['dateformat_tsend'])) ? $attributes['dateformat_tsend'] : '';
    $dftstart = (isset($attributes['dateformat_tstart'])) ? $attributes['dateformat_tstart'] : 'G:i';
    $dftend = (isset($attributes['dateformat_tend'])) ? $attributes['dateformat_tend'] : ' - G:i ';
    $excerptlength = (isset($attributes['excerptlength']) && ' ' < trim($attributes['excerptlength']) ) ? (int) $attributes['excerptlength'] : '' ;
    $sflgi = $attributes['suffix_lgi_class'];
    $sflgia = $attributes['suffix_lgia_class'];
    if (empty($attributes['categories_display'])) {
        $cat_disp = false;
    } else {
        $cat_disp = true;
        $cat_sep = '</small>'.$attributes['categories_display'].'<small>';
    }
    if (! in_array($attributes['tag_sum'], SimpleicalHelper::$allowed_tags_sum))  $attributes['tag_sum'] = 'a';
    $ipd = IcsParser::getData($attributes);
    $data = $ipd['data'];
    if (!empty($data) && is_array($data)) {
        $secho .= '<ul class="list-group' . $attributes['suffix_lg_class'] . ' simple-ical-widget" > ';
        $curdate = '';
        $odd = false;
        foreach($data as $e) {
            $oddeven =  ($odd) ? 'odd' : 'even';
            $idlist = explode("@", $e->uid );
            $itemid = 'b' . $attributes['sibid'] . '_' . $idlist[0];
            $e_dtstart = new Jdate ($e->start);
            $e_dtstart->setTimezone($attributes['tz_ui']);
            $e_dtend = new Jdate ($e->end);
            $e_dtend->setTimezone($attributes['tz_ui']);
            $e_dtend_1 = new Jdate ($e->end -1);
            $e_dtend_1->setTimezone($attributes['tz_ui']);
            $evdate = $e_dtstart->format($dflg, true, true);
            $sameday = ($e_dtstart->format('yz', true, true) === $e_dtend->format('yz', true, true));
            $ev_class =  ((!empty($e->cal_class)) ? ' ' . SimpleicalHelper::sanitize_html_clss($e->cal_class): '');
            $cat_list = '';
            if (!empty($e->categories)) {
                $ev_class = $ev_class . ' ' . implode( ' ',
                    array_map( "Crosborne\Module\Xbsimpleical\Site\Helper\SimpleicalHelper::sanitize_html_class"
                        , $e->categories ));
                if ($cat_disp) {
                    $cat_list = '<div class="categories"><small>'
                        . implode($cat_sep,str_replace("\n", '<br>', $e->categories ))
                        . '</small></div>';
                }
            }
            if (! $sameday) {
                $evdate = str_replace(array("</div><div>", "</h4><h4>", "</h5><h5>", "</h6><h6>" ), '', $evdate . $e_dtend_1->format($dflgend, true, true));
            }
            $evdtsum = (($e->startisdate === false) ? $e_dtstart->format($dftsum, true, true) . $e_dtend->format($dftsend, true, true) : '');
            if ($layout < 2 && $curdate != $evdate) {
                if  ($curdate != '') {
                    $secho .= '</ul></li>';
                }
                $secho .= '<li class="list-group-item' . $sflgi . ' head '.$oddeven.'">' . '<span class="ical-date">' . ucfirst($evdate) . '</span><ul class="list-group' . $attributes['suffix_lg_class'] . '">';
                $odd = !$odd;
            }
            $secho .= '<li class="list-group-item' . $sflgi . $ev_class . '">';
            if ($layout == 3 && $curdate != $evdate) {
                $secho .= '<span class="ical-date">' . ucfirst($evdate) . '</span>' . (('a' == $attributes['tag_sum']) ? '<br>' : '');
                $odd = !$odd;
            }

            if ('summary' == $attributes['tag_sum']) {
                $secho .= '<details class="ical_details' . $sflgia . '" id="'. $itemid. '">';
            }
            
            
            $secho .=  '<' . $attributes['tag_sum'] . ' class="ical_summary' . $sflgia . (('a' == $attributes['tag_sum']) ? '" data-toggle="collapse" data-bs-toggle="collapse" href="#' . $itemid . '" aria-expanded="false" aria-controls="' . $itemid . '">' : '">');
            if ($layout != 2)	{
                $secho .= $evdtsum;
            }
            if(!empty($e->summary)) {
                $secho .= str_replace("\n", '<br>', $e->summary);
            }
            $secho .= '</' . $attributes['tag_sum'] . '>' .$cat_list;
            if ($layout == 2)	{
                $secho .= '<span>'. $evdate . $evdtsum . '</span>';
            }
            
            $secho .=  '<div style="margin-left:20px;">';
            if ($e->startisdate === false && $sameday)	{
                $secho .= '<span class="time">' . ($e_dtstart->format($dftstart, true, true)).
                '</span><span class="time">' . ($e_dtend->format($dftend, true, true)). '</span> @ ' ;
            } else {
                $secho .= '';
            }
            if(!empty($e->location)) {
                $secho .= '<span class="location">'. str_replace("\n", '<br>', $e->location). '</span>';
            }
            $secho .=  '</div>';
            
            if ('summary' != $attributes['tag_sum']) {
                $secho .= '<div class="ical_details' . $sflgia . (('a' == $attributes['tag_sum']) ? ' collapse' : '') . '" id="'. $itemid. '">';
            }
            
            if(!empty($e->description) && trim($e->description) > '' && $excerptlength !== 0) {
                $secho .= '<details><summary class="xbsum">more details</summary>';
                if ($excerptlength !== '' && strlen($e->description) > $excerptlength) {$e->description = substr($e->description, 0, $excerptlength + 1);
                if (rtrim($e->description) !== $e->description) {$e->description = substr($e->description, 0, $excerptlength);}
                else {if (strrpos($e->description, ' ', max(0,$excerptlength - 10))!== false OR strrpos($e->description, "\n", max(0,$excerptlength - 10))!== false )
                {$e->description = substr($e->description, 0, max(strrpos($e->description, "\n", max(0,$excerptlength - 10)),strrpos($e->description, ' ', max(0,$excerptlength - 10))));
                } else
                {$e->description = substr($e->description, 0, $excerptlength);}
                }
                }
                $e->description = str_replace("\n", '<br>', $e->description);
                $secho .= '<span class="dsc">'. $e->description. ((strrpos($e->description, '<br>') === (strlen($e->description) - 4)) ? '' : '<br>'). '</span>';
                $secho .= '</details>';
            }
            if ('summary' == $attributes['tag_sum']) {
                $secho .= '</details></li>';
            } else {
                $secho .= '</div></li>';
            }
            $curdate =  $evdate;
        }
        if ($layout < 2 ) {
            $secho .= '</ul></li>';
        }
        $secho .= '</ul>';
        $secho .= $attributes['after_events'];
    } else {
        $secho .= $attributes['no_events'];
    }
    $secho .= '<br class="clear v300" />';
}
/* end display_block */
if (empty($nohead)) {
    $secho .= '</div>';
}

echo SimpleicalHelper::clean_output($secho);
$secho = '';


