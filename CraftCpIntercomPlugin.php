<?php
/**
 * Craft CP Intercom plugin for Craft CMS
 *
 * Enable Intercom in the Control Panel
 *
 *
 * @author    Knut Melvær
 * @copyright Copyright (c) 2016 Knut Melvær
 * @link      https://github.com/kmelve
 * @package   CraftCpIntercom
 * @since     1.0.1
 */

namespace Craft;

class CraftCpIntercomPlugin extends BasePlugin
{
    /**
     * @return mixed
     */

    public function init()
    {
      /* Only run for logged in users */
      if (craft()->userSession->isLoggedIn()) {

        /* Get settings */
        $intercomId = $this->settings['intercomId'];
        $company = $this->settings['company'];
        $name = craft()->userSession->name;
        $hash = $this->settings['hash'];
        $userGroupOptions = $this->settings['userGroups'] ? $this->settings['userGroups'] : [];
        $user =  craft()->userSession->getUser();
        $email = $user->email;
        $userGroups = [];
        foreach ($user->groups as $group) {
          array_push($userGroups, $group->handle);
        }
        /**
         *  Check if user is in a group that is checked for support.
         *  Admins will always have access.
         *  */
        $showIntercomForUser = !$user->admin ? count(array_intersect($userGroups, $userGroupOptions)) !== 0 : true;
        $showIntercomOnFrontend = $this->settings['controlPanel'] ? craft()->request->isCpRequest() : true;
        /* Support secure_mode */
        if ($hash) {
          $emailHash = hash_hmac('sha256', $email, $hash);
        }
        $emailHashParam = isset($emailHash) ? "\n\tuser_hash: '{$emailHash}'," : null;

        /* Build JavaScript */
        $javascript = "
          window.intercomSettings = {
              app_id: '{$intercomId}',
              name: '{$name}',
              email: '{$email}',{$emailHashParam}
              company_id: '{$company}'
            };
            (function(){var w=window;var ic=w.Intercom;if(typeof ic==='function'){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/puws8gsr';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()";

          /* Include JavaScript */
          if ($intercomId && $showIntercomForUser && $showIntercomOnFrontend) {
              craft()->templates->includeJs($javascript);
          }
      }
    }

    /**
     * Returns the user-facing name.
     *
     * @return mixed
     */
    public function getName()
    {
         return Craft::t('Craft CP Intercom');
    }

    /**
     * Plugins can have descriptions of themselves displayed on the Plugins page by adding a getDescription() method
     * on the primary plugin class:
     *
     * @return mixed
     */
    public function getDescription()
    {
        return Craft::t('Enable Intercom in the Control Panel');
    }

    /**
     * Plugins can have links to their documentation on the Plugins page by adding a getDocumentationUrl() method on
     * the primary plugin class:
     *
     * @return string
     */
    public function getDocumentationUrl()
    {
        return 'https://github.com/netliferesearch/craftcpintercom/blob/master/README.md';
    }

    /**
     * Plugins can now take part in Craft’s update notifications, and display release notes on the Updates page, by
     * providing a JSON feed that describes new releases, and adding a getReleaseFeedUrl() method on the primary
     * plugin class.
     *
     * @return string
     */
    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/netliferesearch/craftcpintercom/master/releases.json';
    }

    /**
     * Returns the version number.
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.0.1';
    }

    /**
     * As of Craft 2.5, Craft no longer takes the whole site down every time a plugin’s version number changes, in
     * case there are any new migrations that need to be run. Instead plugins must explicitly tell Craft that they
     * have new migrations by returning a new (higher) schema version number with a getSchemaVersion() method on
     * their primary plugin class:
     *
     * @return string
     */
    public function getSchemaVersion()
    {
        return '1.0.1';
    }

    /**
     * Returns the developer’s name.
     *
     * @return string
     */
    public function getDeveloper()
    {
        return 'Knut Melvær';
    }

    /**
     * Returns the developer’s website URL.
     *
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'https://github.com/kmelve';
    }

    /**
     * Returns whether the plugin should get its own tab in the CP header.
     *
     * @return bool
     */
    public function hasCpSection()
    {
        return false;
    }

    /**
     * Called right before your plugin’s row gets stored in the plugins database table, and tables have been created
     * for it based on its records.
     */
    public function onBeforeInstall()
    {
    }

    /**
     * Called right after your plugin’s row has been stored in the plugins database table, and tables have been
     * created for it based on its records.
     */
    public function onAfterInstall()
    {

    }

    /**
     * Called right before your plugin’s record-based tables have been deleted, and its row in the plugins table
     * has been deleted.
     */
    public function onBeforeUninstall()
    {
    }

    /**
     * Called right after your plugin’s record-based tables have been deleted, and its row in the plugins table
     * has been deleted.
     */
    public function onAfterUninstall()
    {
    }

    /**
     * Defines the attributes that model your plugin’s available settings.
     *
     * @return array
     */
    protected function defineSettings()
    {
      $userGroups = craft()->userGroups->getAllGroups();
      $userGroupOptions = [];
      foreach ($userGroups as $group) {
        $userGroupOptions += array(
          $group->handle => 1
        );
      }
      // echo '<pre>';
      // var_dump($userGroupOptions);
      // die();
      return array(
          'intercomId' => array(AttributeType::String, 'label' => 'Intercom App ID', 'default' => ''),
          'company' => array(AttributeType::String, 'label' => 'Intercom Company Id', 'default' => 'A Company'),
          'hash' => array(AttributeType::String, 'label' => 'Intecom Secret Hash', 'default' => ''),
          'userGroups' => array(AttributeType::Mixed, 'label' => 'User group settings', 'default' => $userGroupOptions),
          'controlPanel' => array(AttributeType::Number, 'label' => 'Show only in Control Panel', 'default' => 'true')
      );
    }

    /**
     * Returns the HTML that displays your plugin’s settings.
     *
     * @return mixed
     */
    public function getSettingsHtml()
    {
       return craft()->templates->render('craftcpintercom/CraftCpIntercom_Settings', array(
           'settings' => $this->getSettings()
       ));
    }

    /**
     * If you need to do any processing on your settings’ post data before they’re saved to the database, you can
     * do it with the prepSettings() method:
     *
     * @param mixed $settings  The Widget's settings
     *
     * @return mixed
     */
    public function prepSettings($settings)
    {

        return $settings;
    }

}
