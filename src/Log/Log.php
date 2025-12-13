<?php
/**
 * @version $Id: Log.php 
 * @package simpleicalblock
 * @subpackage simpleicalblock Module
 * @copyright Copyright (C) 2025 -2026 A.H.C. Waasdorp, All rights reserved.
 * @license GNU General Public License version 3 or later
 * @author url: https://www.waasdorpsoekhan.nl
 * @author email contact@waasdorpsoekhan.nl
 * @developer A.H.C. Waasdorp
 * Log to standard Joomla Log for mod_simpleicalblock
 * @since  3.0
 * 3.0.0 remove messages to front-end, replaced by Log
 */
namespace WaasdorpSoekhan\Module\Simpleicalblock\Site\Log;
// no direct access
defined('_JEXEC') or die ('Restricted access');
use Joomla\CMS\Log\Log as JLog;

class Log
{
/**
 * Describes log levels.
 */
    const EMERGENCY = JLog::EMERGENCY;
    const ALERT     = JLog::ALERT;
    const CRITICAL  = JLog::CRITICAL;
    const ERROR     = JLog::ERROR;
    const WARNING   = JLog::WARNING;
    const NOTICE    = JLog::NOTICE;
    const INFO      = JLog::INFO;
    const DEBUG     = JLog::DEBUG;
/**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     */
    static function log($level, string|\Stringable $message, array $context = [])
    {
        if (!is_string($message)) $message = print_r($message, true);
        if (empty($context['category'])) $context['category'] = 'simple-ical-block';
        JLog::add($message, $level, $context['category']);
        
    }
}

