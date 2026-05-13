<?php
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
        //echo "mod_hello preflight<br>";
        
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
        // echo "mod_hello postflight<br>"; Factory::getApplication()->getDocument()->
        $app = Factory::getApplication();
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->useScript('joomla.dialog');
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
        
        if ($type!='uninstall') {
            
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
            } else {
                $ext_mess .= '<p style="color:red;"><i>no changelog found for '.$ver.'</i></p>';
            }
            $ext_mess .= '<p>For help and information see documentation tab in module settings and and <a href="https://github.com/rogercreagh/j5-xbMod_simple_ical/" target="_blank">README.md file on GitHub</a> ';
        }
        
        if ($type == 'uninstall') {
            $ext_mess .= '<p>To reinstall <b>xbSimple-iCal</b> download the latest release from <a href="https://github.com/rogercreagh/j5-xbMod_simple_ical/releases" target="_blank">GitHub</a>';
        }
        $ext_mess .= '</div>';
        echo $ext_mess;
        return true;
    }
    
};