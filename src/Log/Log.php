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
 * Describes PSR-3 log levels.
 */
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';
    /**
     * Mapping array to map a PSR-3 level to an ascending integer Joomla priority.
     *
     * @var    array
     * @since  3.0.0
     */
    protected $priorityMap = [
        self::EMERGENCY => JLog::EMERGENCY,
        self::ALERT     => JLog::ALERT,
        self::CRITICAL  => JLog::CRITICAL,
        self::ERROR     => JLog::ERROR,
        self::WARNING   => JLog::WARNING,
        self::NOTICE    => JLog::NOTICE,
        self::INFO      => JLog::INFO,
        self::DEBUG     => JLog::DEBUG,
        'ALL'=> JLog::ALL,        
    ];
    
/**
     * Logs with an arbitrary level.
     *
     * @param string $level
     * @param mixed[] $context
     *
     */
    public function log($level, string|\Stringable $message, array $context = [])
    {
        if (!is_string($message)) $message = print_r($message, true);
        if (empty(self::$priorityMap[$level])) $level = self::NOTICE;
        if (empty($context['category'])) $context['category'] = 'simple-ical-block';
        JLog::add($message, self::$priorityMap[$level], $context['category']);
        
    }
}
