<?php
/*******
 * @package xbSimple-ical
 * @filesource mod_xbsimple-ical/mod_xbsimpleical_script.php
 * @version 0.2.4.0 22nd June 2026
 * @copyright Copyright (c) Roger Creagh-Osborne, 2026
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 ******/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Changelog\Changelog;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Version;

return new class () implements InstallerScriptInterface {

    protected string $minPhp = '8.3';
    protected string $minJoomla = '5.3'; //this is the lowest acceptable version
    protected string $maxJoomla = '6.0'; //this is the lowest unacceptable version
    protected string $extension = 'mod_xbsimple-ical'; //used in path to find module code
    protected string $extslug = 'xbsimpleical'; //the alias used on CrOsborne.uk for docs etc
    protected string $oldver = 'v1.2.3.4';
    protected string $olddate = '32nd January 1952';
    protected string $ver = 'v5.6.7.8';
    protected string $extdate = '32nd January 2052';
    
    
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        
        if (($type != 'uninstall') && (version_compare(PHP_VERSION, $this->minPhp, '<'))) {
            Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_PHP'), $this->minPhp), 'error');
            return false;
        }

        if (version_compare(JVERSION, $this->minJoomla, '<')) {
            Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_JOOMLA'), $this->minJoomla), 'error');
            return false;
        }
        if (version_compare(JVERSION, $this->maxJoomla, '>=')) {
            Factory::getApplication()->enqueueMessage('This xbSimple-iCal version is not ready for J'. $this->maxJoomla, 'error');
            return false;
        }
        
        if ($type=='update') {
            $oldmanifest = simplexml_load_file(Path::clean(JPATH_SITE . '/modules/'.$this->extension.'/'.$this->extension.'.xml'));
            $this->oldver = $oldmanifest->version;
            $this->olddate = $oldmanifest->creationDate;
        }
        return true;
    }

    public function install(InstallerAdapter $adapter): bool
    {
//        echo "mod_hello install<br>";
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {

 //       echo "Updating from ".$this->oldver." ".$this->olddate."<br>";
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
//        echo "mod_hello uninstall<br>";
        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
//        $app = Factory::getApplication();
//        $wa = $app->getDocument()->getWebAssetManager();
//        $wa->useScript('joomla.dialog');
        $manifest = $adapter->getManifest();
        $ver = $manifest->version;
        $this->extdate = $manifest->creationDate;
        $changelogurl = $manifest->changelogurl;
        $ext_mess = '<div style="position: relative; margin: 0 auto; padding: 1rem; border:solid 1px #444; border-radius: 6px; max-width: 900px; background-color: #d7d7d7;">';
        
        if ($type == 'update') {
            $ext_mess .= '<p><b>'.$manifest->name.'</b> module has been updated from '.$this->oldver.' of '.$this->olddate;
            $ext_mess .= ' to v<b>'.$ver.'</b> dated '.$manifest->creationDate.'</p>';
            $ext_mess .= '<p>Check options for existing instances of '.$manifest->name.' on <a href="index.php?option=com_modules&view=modules&client_id=0">Site Modules</a> page.</p>';
        }
        
        if (($type=='install') || ($type=='discover_install')) {
            $ext_mess .= '<h3>'.$manifest->name.' module installed</h3>';
            $ext_mess .= '<p>version '.$ver.' dated '.$manifest->creationDate.'</p>';
            $ext_mess .= '<p>Enable module and set options on <a href="index.php?option=com_modules&view=select&client_id=0">Site Modules</a> page.</p>';
        }
        
        if ($type != 'uninstall') {
            
            $changelog = new Changelog();
            
            $changelog->setVersion($ver); $changelog->loadFromXml((string)$changelogurl);
            if  ($changelog->get('element')!=null) {
                //order of entries array defines order on screen - this order is not Joonla standard, but better!
                $entries = [
                    'addition' => [],
                    'security' => [],
                    'fix'      => [],
                    'change'   => [],
                    'remove'   => [],
                    'language' => [],
                    'note'     => [],
                ];
                
                foreach (array_keys($entries) as $name) {
                    $field = $changelog->get($name);
                    if ($field) {
                        $entries[$name] = $changelog->get($name)->data;
                    }
                }
                
                $layout = new FileLayout('joomla.installer.changelog');
                $clog = '<style>.changelog { margin 0;}</style><div style="border:1px solid black; margin:10px 20px;">';
                $clog .= $layout->render($entries);
                $clog .= '</div>';
                $ext_mess .= '<details style="background-color: #f7f7f7;"><summary>Changelog v'.$ver.'</summary>'.$clog.'</details>';
//                $ext_mess .= '<p><button type="button" class="btn btn-info btn-sm" data-joomla-dialog="{&quot;popupType&quot;:&quot;inline&quot;,&quot;textHeader&quot;:&quot;Changelog - xbSimple-iCal - 0.1.0.0&quot;,&quot;popupContent&quot;:&quot;'.htmlentities($clog).'&quot;,&quot;width&quot;:&quot;800px&quot;,&quot;height&quot;:&quot;fit-content&quot;}">Changelog v'.$ver.'</button></p>';

                //strip newlines and replace " with \" or ' in clog
                $cleanclog = trim(preg_replace('/\s+/', ' ', $clog));
                $cleanclog = str_replace('"', "'", $cleanclog);
                $ext_mess .= self::updatelang('XBIC_CLOG', $cleanclog);
            } else {
                $ext_mess .= '<p style="color:red;"><i>no changelog found for '.$ver.'</i></p>';
            }
            $ext_mess .= '<p>For help and information see documentation tab in module settings and and <a href="https://github.com/rogercreagh/j5-xbMod_simple_ical/" target="_blank">README.md file on GitHub</a> ';
            //update language string
            $ext_mess .= '<br />'.self::updatelang('XBIC_DESC','<b>'.$manifest->name.'</b> v'.$manifest->version.' '. $manifest->creationDate);
            $ext_mess .= '<br />'.self::updatelang('XBIC_DESC','<b>'.$manifest->name.'</b> v'.$manifest->version.' '. $manifest->creationDate,'sys.ini');
            $verdatedesc = "<div style='color: #3f48cc; background: #eef; padding-right:10px;'><div style='float:left;'><img src='../media/mod_xbsimple-ical/icons/simpleicalicon128x128.svg' style='width:3em; margin-right: 0.5em;'/></div><p>".$manifest->name.' v'.$manifest->version.' '.$manifest->creationDate."<br />Module displays a list of forthcoming events from a VCALENDAR source</div><div class='clearfix'></div>";
            $ext_mess .= '<br />'.self::updatelang('XBSIMPLEICAL_DESC',$verdatedesc );
        }
        
        if ($type == 'uninstall') {
            $ext_mess .= '<p>To reinstall <b>xbSimple-iCal</b> download the latest release from <a href="https://github.com/rogercreagh/j5-xbMod_simple_ical/releases" target="_blank">GitHub</a>';
        }
        $ext_mess .= '</div>';
        echo $ext_mess;
        return true;
    }
    
    private function updatelang(string $key, string $value, $ext="ini") {
        $langfile = JPATH_ROOT.'/modules/mod_xbsimple-ical/language/en-GB/mod_xbsimple-ical.'.$ext;
        $targetKey = $key.'="';
        $newLine = $targetKey.$value.'"';
        
        // Read file into array, preserving line endings
        if (!file_exists($langfile)) return $langfile.' does not exist';
        $lines = file($langfile, FILE_IGNORE_NEW_LINES);
        if (!$lines) return 'Could not open '.$langfile;
        $msg = '';
        $fnd = false;
        foreach ($lines as &$line) {
            // Check if line starts with the target string
            if (strpos($line, $targetKey) === 0) {
                $line = $newLine;
                $fnd = true;
                break; // Stop after first match if only one replacement is needed
            }
        }
        unset($line); // Break reference
        if ($fnd) {
            // Write modified lines back to file
            file_put_contents($langfile, implode("\n", $lines) . "\n");
            $msg = 'language_file.'.$ext.' updated<br />';
        } else {
            $msg = 'failed updating language_file.'.$ext.'<br />';
        }
        return $msg;        
    }

    /**
    private function desc2lang($name='nn',$ver='xx',$date='yy') {
        $langfile = JPATH_ROOT.'/modules/mod_xbsimple-ical/language/en-GB/mod_xbsimple-ical.sys.ini';
        $targetDesc = 'XBIC_DESC="';
        $newDesc = 'XBIC_DESC="'.$name.' v'.$ver.' '.$date.'"';
        
        // Read file into array, preserving line endings
        if (!file_exists($langfile)) return $langfile.' does not exist';
        $lines = file($langfile, FILE_IGNORE_NEW_LINES);
        if (!$lines) return 'Could not open '.$langfile;
        $fnd = false;
        foreach ($lines as &$line) {
            // Check if line starts with the target string
            if (strpos($line, $targetDesc) === 0) {
                $line = $newDesc;
                $fnd = true;
                break; // Stop after first match if only one replacement is needed
            }
        }
        unset($line); // Break reference
        $msg = '';
        if ($fnd) {
            // Write modified lines back to file
            file_put_contents($langfile, implode("\n", $lines) . "\n");
            $msg = 'en-GB.mod_xbsimple-ical.sys.ini updated<br />';
        } else {
            $msg = 'failed updating en-GB.mod_xbsimple-ical.sys.ini<br />';
        }
      
        $langfile = JPATH_ROOT.'/modules/mod_xbsimple-ical/language/en-GB/en-GB.mod_xbsimple-ical.ini';
        
        // Read file into array, preserving line endings
        if (!file_exists($langfile)) return $msg.$langfile.' does not exist';
        $lines = file($langfile, FILE_IGNORE_NEW_LINES);
        if (!$lines) return $msg.'Could not open '.$langfile;
        $fnd = false;
        foreach ($lines as &$line) {
            // Check if line starts with the target string
            if (strpos($line, $targetDesc) === 0) {
                $line = $newDesc;
                $fnd = true;
                break; // Stop after first match if only one replacement is needed
            }
        }
        unset($line); // Break reference
        
        if ($fnd) {
            // Write modified lines back to file
            file_put_contents($langfile, implode("\n", $lines) . "\n");
            return $msg.'en-GB.mod_xbsimple-ical.ini updated';
        } else {
            return $msg.'failed updating en-GB.mod_xbsimple-ical.ini';
        }
        
    }
    **/
    
};