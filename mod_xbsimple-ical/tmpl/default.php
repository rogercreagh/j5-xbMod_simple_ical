<?php
/*******
 * @package xbSimple-ical
 * @filesource mod_xbsimple-ical/tmpl/default.php
 * @version 0.2.4.5 6th July 2026
 * @copyright Copyright (c) Roger Creagh-Osborne, 2026
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @desc based on SimpleIcalBlock by  A.H.C. Waasdorp (c) 2022-2026 https://www.waasdorpsoekhan.nl
 ******/

// no direct access
defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Component\Finder\Administrator\Indexer\Parser\Html;
use Joomla\CMS\Date\Date as Jdate;
use Crosborne\Module\Xbsimpleical\Site\IcsParser;
use Crosborne\Module\Xbsimpleical\Site\Helper\SimpleicalHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\HTML\Helpers\StringHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.popover', '.xbic-popover',
    ['title' => Text::_('XBIC_EVENT_DETS'),
    'trigger' => 'hover focus'
]);
    
    if (!empty($wa)) {
        $wa->useStyle('xbsimpleical.styles');
    }
    if (empty($output)) {  $output = ''; }
    
    if (empty($nohead) ) {
        $attributes = SimpleicalHelper::render_attributes( $params->toArray());
        $output .= '<div id="' . $attributes['anchorId']  .'" data-sib-id="' . $attributes['sibid'] . '" ' . ' class="xbsimple-ical ' . $attributes['title_collapse_toggle']. '" >';
    }
/**
 * Front-end display of module, block or widget.
 * @param array $attributes
 * @param string &$output (reference to $output), output to echo in calling function, to simplify escaping output by replacing multiple echoes by one
 *            Saved attribute/option values from database.
 * was static function display_block($attributes, &$output)
 */
    //timezone stuff
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
    
    //$layout = (isset($attributes['sib_layout'])) ? intval($attributes['sib_layout']) : 3;
    //sib_layout replaced by headtype 1=group 0=plain old sib_layout 1=group 2=summary first 3=oldstyle

    $dflg = (isset($attributes['dateformat_lg'])) ? $attributes['dateformat_lg'] : 'l jS \of F';
    $dflgend = (isset($attributes['dateformat_lgend'])) ? $attributes['dateformat_lgend'] : '';
    $dftsum = (isset($attributes['dateformat_tsum'])) ? $attributes['dateformat_tsum'] : 'G:i ';
    $dftsend = (isset($attributes['dateformat_tsend'])) ? $attributes['dateformat_tsend'] : '';
    $dftstart = (isset($attributes['dateformat_tstart'])) ? $attributes['dateformat_tstart'] : 'G:i';
    $dftend = (isset($attributes['dateformat_tend'])) ? $attributes['dateformat_tend'] : ' - G:i ';
    $excerptlength = (isset($attributes['excerptlength']) && ' ' < trim($attributes['excerptlength']) ) ? (int) $attributes['excerptlength'] : '' ;
    $sflgi = $attributes['suffix_lgi_class'];
    $sflgia = $attributes['suffix_lgia_class'];
    
    $headtype = (isset($attributes['headtype'])) ? intval($attributes['headtype']) : 0;
    if (isset($attributes['headdatefmt'])) {
        $headstartfmt = $attributes['headdatefmt']['startfmt'];
        $headendfmt = $attributes['headdatefmt']['endfmt'];
    }
    
    // create calendar_id(s) as comma separated string from $attributes['calendars']
    // expand the calendar details to use in events
    if (isset($attributes['calendars']) && (!empty($attributes['calendars']))) {
        $calids = '';
        $cals = [];
        foreach ($attributes['calendars'] as $cal) {
            $cal['calfmtname'] = '';
            if (!empty($cal['calurl'])) {               
                $calids .= $cal['calurl'].',';
                if (!empty($cal['calname'])) {
                    // put name in a span tag with the assigned classes
                    $calnamestr = SimpleicalHelper::sanitize_html_str($cal['calname'],'_ -:');
                    $calnamearr = explode(':',$calnamestr);
                    if (count($calnamearr) == 2){
                        $cal['calfmtname'] = '<span class="'.$calnamearr[1].'">'.$calnamearr[0].'</span>';
                    }
                }
                $cal['calbkgnd'] = (!empty($cal['calbkgnd'])) ?
                    SimpleicalHelper::sanitize_html_str($cal['calbkgnd'],'#(), .')
                    : '';
            }
            $cals[] = $cal;
        }
        $attributes['calendars'] = $cals;
        //         $attributes['cals'] = $cals;
        $attributes['calendar_id'] = trim($calids,',');
    }
    
//    if (! in_array($attributes['tag_sum'], SimpleicalHelper::$allowed_tags_sum))  $attributes['tag_sum'] = 'a';
    $ipd = IcsParser::getData($attributes);
    $data = $ipd['data'];
    //resort the data
    //      usort($data, fn($a, $b) => strcmp($a->summary, $b->summary));
//     usort($data, function($a, $b) {
//         if (!isset($a->summary) && !isset($b->summary)) return 0;
//         if (!isset($a->summary)) return 1;
//         if (!isset($b->summary)) return -1;
//         return strcasecmp($a->summary, $b->summary);
//     });
        
    if (!empty($data) && is_array($data)) {
        
        //get attributes used here 
        $show_cats = (isset($attributes['showcats'])) ? (int) $attributes['showcats'] : 0;
        $show_repicon = (isset($attributes['repicon'])) ? (int) $attributes['repicon'] : 0;
        
 //       $showhead = (isset($attributes['headtype'])) ? (int) $attributes['headtype'] : 1;
        
        $show_title = (isset($attributes['showtitle'])) ?  $attributes['showtitle'] : false;
        if ($show_title) {
            $titlecomps = (isset($attributes['titlecomps'])) ?  $attributes['titlecomps'] : [];
            $titleclass = (isset($attributes['titleclass'])) ? $attributes['titleclass'] : '';
//         } else {
//             $titlecomps = [];
        }
        
        $show_info = (isset($attributes['showinfo'])) ?  $attributes['showinfo'] : false;
        if ($show_info) {
            $infocomps = (isset($attributes['infocomps'])) ?  $attributes['infocomps'] : [];
            $infoclass = (isset($attributes['infoclass'])) ? $attributes['infoclass'] : '';
            //         } else {
//             $infocomps = [];
        }

        $show_details = (isset($attributes['showdetails'])) ?  $attributes['showdetails'] : false;
        if ($show_details) {
            $detailscomps = (isset($attributes['detailscomps'])) ?  $attributes['detailscomps'] : false;
            $detailsclass = (isset($attributes['detailsclass'])) ? $attributes['detailsclass'] : '';
//         } else {
//             $detailscomps = [];
        }
//       Factory::getApplication()->enqueueMessage('<pre>'.print_r($detailscomps,true).'</pre>');
        
        $catclassarr = []; 
        if ($show_cats) {
            //get category classes
            $catclassstr = (isset($attributes['catclasses'])) ? $attributes['catclasses'] : '';
            //remove unwanted chars including spaces- only alphanumeric, comma, colon, underscore and hyphen allowed
//            $catclassstr = preg_replace('/[^a-zA-Z0-9,:_\-]/', '', $catclassstr);
            $catclassstr = SimpleicalHelper::sanitize_html_str($catclassstr,'_:, -');
            // split string at commas then split each element into key:value at colon
            if ($catclassstr != '') {           
                foreach (explode(',', $catclassstr) as $pair) {
                    list($key, $value) = explode(':', trim($pair));
                    $catclassarr[strtolower(trim($key))] = trim($value);
                }
            }
        }
//        $showrepicon = (isset($attributes['showrepicon'])) ? (int) $attributes['showrepicon'] : 0;
        
        
//        $curdate = '';
        $curhead = '';
        $odd = false;
        $output .= '<ul class="list-group' . ' simple-ical-widget" > '; //$attributes['suffix_lg_class'] .
        
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
            if ($headtype < 2) {
                $headdate = $e_dtstart->format($headstartfmt, true, true);            
                if (!$sameday) $headdate .= ' '.$e_dtend->format($headendfmt,true, true);
            }
            

            //get the calendar name with class and background style into the event details
            $cal = $attributes['calendars'][($e->cal_ord)-1];
            $e->calfmtname = $cal['calfmtname'];
            $e->calstyle = ($cal['calbkgnd'] != '') ? ' style="background-color:'.$cal['calbkgnd'].';" ' : '';
            
            
            $is_repeat = isset($e->rrule);
            $e->repstr = ($is_repeat) ? SimpleicalHelper::rrule2text($e->rrule) : '';
            
            //set component classes in $e so they can be used by SimpleicalHelper::renderSectionComps
            
            //build category list for event
            $cat_list = '';
            if (!empty($e->categories)) {
                if ($show_cats) {
                    foreach ($e->categories as $cat) {
                        $cat_list .= '<span class="';
                        if (key_exists(strtolower($cat), $catclassarr)) {
                            $cat_list .= $catclassarr[strtolower($cat)];
                        } else {
                            $cat_list .= 'xbic-label xbic-ltgrey';
                        }
                        $cat_list .= '">'.$cat.'</span> ';
                    }
                }
            }
            $e->catlist = $cat_list;
            //get component classes into $e for rendering
            $e->sumclass = (isset($attributes['sumclass'])) ? $attributes['sumclass'] : '';
            $e->locclass = (isset($attributes['locclass'])) ? $attributes['locclass'] : '';
            $e->descclass = (isset($attributes['descclass'])) ? $attributes['descclass'] : '';
            //get the formated dates to use in sections
            $e->titlestartdate = '';
            $e->tileenddate = '';
            $e->titlestarttime = '';
            $e->titleendtime = '';
            $e->infostartdate = '';
            $e->infoenddate = '';
            $e->infostarttime = '';
            $e->infoendtime = '';
            $e->detailsstartdate = '';
            $e->detailsenddate = '';
            $e->detailsstarttime = '';
            $e->detailsendtime = '';
            
            $evdetails = '';
            if ($show_title) {
                $evdetails .= '<div class="'.$titleclass.'">';
                if ($is_repeat && $show_repicon) $evdetails .= '<span class="fas fa-repeat" title="'.$e->repstr.'"></span>&nbsp;';
                $evdetails .= SimpleicalHelper::renderSectionComps($titlecomps, $e);
                $evdetails .= '</div>';
            }
            if ($show_info) {
                $evdetails .= '<div class="'.$infoclass.'">';
                $evdetails .= SimpleicalHelper::renderSectionComps($infocomps, $e);
                $evdetails .= '</div>';
            }
            if ($show_details) {
                $detstext = SimpleicalHelper::renderSectionComps($detailscomps, $e);
                if ($detstext != '') {
                    switch ($show_details) {
                        case 1: //popup  
                            $pop = '<div class="'.$detailsclass.'">';
                            $pop .= '<button type="button" class="xbic-label xbic-ltgrey xbic-popover"';
                            $pop .= ' data-bs-toggle="popover"  data-bs-html="true"';
                            $pop .= ' data-bs-content="'.str_replace('"','&quot;',$detstext).'" >';
                            $pop .= $attributes['detailsprompt'];
                            $pop .= '</button></div>';
                            $evdetails .= $pop;
                            break;
                        case 2: //dropdown
                            $evdetails .= '<div class="'.$detailsclass.'">';
                            $evdetails .= '<details><summary><span class="xbic-dropdets">';
                            $evdetails .= $attributes['detailsprompt'].'</span></summary>';
                            $evdetails .= $detstext;
                            $evdetails .= '</details>';
                            $evdetails .= '</div>';
                            break;
                        case 3: //full
                            $evdetails .= '<div class="'.$detailsclass.' xbic-fulldets">';
                            $evdetails .= '<i>'.Text::_('XBIC_EVENT_DETS').'</i><br />';
                            $evdetails .= $detstext;
                            $evdetails .= '</div>';
                            break;
                    } //end switch
                } //end if detstext
            } //end show_details
            
            //Start building the output
            
            switch ($headtype) {
                case 1: //group by date
                    if (($curhead != '') && ($curhead != $evdate)) $output .= '</li>';
                    if ($curhead != $evdate) {
                        $odd = !$odd;
                        $output .= '<li class="list-group-item' . $sflgi . ' head '.$oddeven.'">';
                        $output .= '<span class="ical-date">' . ucfirst($headdate) . '</span>';
                    }
                    break;
                case 2: //group by summary
                    if (($curhead != '') && ($curhead != $e->summary)) $output .= '</li>';
                    if ($curhead != $e->summary) {
                        $odd = !$odd;
                        $output .= '<li class="list-group-item' . $sflgi . ' head '.$oddeven.'">';
                        $output .= $e->summary;
                        //poss need to truncate summary
                    }
                    break;
                case 3: //group by location
                    if (($curhead != '') && ($curhead != $e->location)) $output .= '</li>';
                    if ($curhead != $e->location) {
                        $odd = !$odd;
                        $output .= '<li class="list-group-item' . $sflgi . ' head '.$oddeven.'">';
                        $output .= '<span class="ical-date">' . $e->location . '</span>';
                        //poss need to truncate location
                    }
                    break;
                default: //no grouping, just use date for every event
                    if (($curhead != '') && ($curhead != $evdate)) $output .= '</li>';
                    $odd = !$odd;
                    $output .= '<li class="list-group-item' . $sflgi . ' head '.$oddeven.'">';
                    $output .= '<span class="ical-date">' . ucfirst($headdate) . '</span>';                    
                break;
            }
            
            $output .= '<ul class="list-group' . $attributes['suffix_lg_class'] .'">';
            $output .= '<li  class="list-group-item' . $sflgi . '" '. $e->calstyle .'>';
            $output .= $evdetails;
            $output .= '</li>';
            $output .= '</ul>';
            switch ($headtype) {
                case 1:
                    $curhead = $evdate;
                    break;
                case 2:
                    $curhead = $e->summary;
                    break;
                case 3:
                    $curhead = $e->location;
                    break;
                    
                default:
                    $curhead = $evdate;
                    $output .= '</li>';
                    break;
            }
        } //  endforeach
        $output .= '</ul>';
        $output .= $attributes['after_events'];
    } else {
        $output .= $attributes['no_events'];
    } //endif empty data
        
    /* end display_block */
    if (empty($nohead)) {
        $output .= '</div>';
    }
    
    echo $output; //SimpleicalHelper::clean_output($output);
    $output = '';
    
