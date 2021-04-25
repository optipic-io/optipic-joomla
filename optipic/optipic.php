<?php
/**
 *
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die;

class PlgSystemOptipic extends JPlugin
{
    /**
     * Load the language file on instantiation. Note this is only available in Joomla 3.1 and higher.
     * If you want to support 3.0 series you must override the constructor
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Plugin method with the same name as the event will be called automatically.
     */

    function onAfterRender()
    {
        //global $app;
        $app = JFactory::getApplication();

        if ($app->getName() == 'site'){
            $settings = $this->getSettings();
            if($settings['autoreplace_active'] && $settings['site_id']){
                require_once 'ImgUrlConverter.php';
                optipic\cdn\ImgUrlConverter::loadConfig($settings);
                $content = $app->getBody();
                $content = optipic\cdn\ImgUrlConverter::convertHtml($content);
                $app->setBody($content);
            }
        }
        elseif($app->isAdmin()) {
            $bodyHtml = $app->getBody();
            
            $isOptipicSettingsPage = stripos($bodyHtml, 'optipic-plugin-page-detecor-mark')!==false;
            
            if($isOptipicSettingsPage) {
                
                $settings = $this->getSettings();
                
                if($sid = $settings['site_id']) { 
                    $uri = JUri::getInstance(); 
                    $host = $uri->getHost();
 
                    $js = '<script src="https://optipic.io/api/cp/stat?domain='.$host.'&sid='.$sid.'&cms=joomla&stype=cdn&append_to=%23general%3Afirst&version=1.14.0"></script>';
                    
                    $bodyHtml = str_replace ("</body>", $js." </body>", $bodyHtml);
                    $app->setBody($bodyHtml);
                }
                
                
            }
        }

        return true;
    }

    function getSettings(){
        $autoreplaceActive = $this->params->get('autoreplace_active', '');
        $siteId = $this->params->get('site_id', '');
        $domains = $this->params->get('domains', '')!=''? explode("\n", $this->params->get('domains', '')):array();
        $exclusionsUrl = $this->params->get('exclusions_url', '')!=''?explode("\n", $this->params->get('exclusions_url', '')):array();
        $whitelistImgUrls = $this->params->get('whitelist_img_urls', '')!=''?explode("\n", $this->params->get('whitelist_img_urls', '')):array();
        $srcsetAttrs = $this->params->get('srcset_attrs', '')!=''?explode("\n", $this->params->get('srcset_attrs', '')):array();

        return array(
            'autoreplace_active' => $autoreplaceActive,
            'site_id' => $siteId,     // your SITE ID from CDN OptiPic controll panel
            'domains' => $domains, // list of domains should replace to cdn.optipic.io
            'exclusions_url' => $exclusionsUrl, // list of URL exclusions - where is URL should not converted
            'whitelist_img_urls' => $whitelistImgUrls, // whitelist of images URL - what should to be converted (parts or full urls start from '/')
            'srcset_attrs' => $srcsetAttrs, // tag's srcset attributes // @see https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images
        );
    }
}
?>