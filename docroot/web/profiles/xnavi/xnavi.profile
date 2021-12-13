<?php

/**
 * Enables modules and site configuration for a x.Navi site installation
 */

use Drupal\Core\Extension\Dependency;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Installer\InstallerKernel;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form()
 * 
 * Allows the profile to alter the site configuration form
 */

function xnavi_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
    // Add a value as example that one can choose an arbitrary site name.
    $form['site_information']['site_name']['#placeholder'] = t('x.Navi Framework');
}

/**
 * Implements hook_install_tasks().
 */
function xnavi_install_tasks(&$install_state) {
  $tasks = [];

  if(empty($install_state['config_install_path'])) {
      

      $tasks['xnavi_module_configure_form'] = [
          'display_name' => t('x.Navi-Module auswählen'),
          'type' => 'form',
          'function' => 'Drupal\xnavi\Installer\Form\ModuleConfigureForm',
      ];

      $tasks['xnavi_module_install'] = [
          'display_name' => t('Installiere x.Navi-Module'),
          'type' => 'batch',
      ];

      $tasks['xnavi_module_message'] = [
        'display_name' => t('Hinweis'),
        'type' => 'form',
        'function' => 'Drupal\xnavi\Installer\Form\MessageForm',
      ];
    }
    
    $tasks['xnavi_finish_installation'] = [
        'display_name' => t('Installation beenden'),
    ];

    
    return $tasks;
}

/**
 * Installs the x.Navi modules in a batch.
 *
 * @param array $install_state
 *   The install state.
 *
 * @return array
 *   A batch array to execute.
 */
function xnavi_module_install(array &$install_state) {
    //print_r($install_state);
    return $install_state['xnavi_install_batch'] ?? [];
}


  
/**
   * Finish x.Navi installation process.
   *
   * @param array $install_state
   *   The install state.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
function xnavi_finish_installation(array &$install_state) {

    //Set front page
    if(Drupal::moduleHandler()->moduleExists('node')) {
      Drupal::configFactory()
        ->getEditable('system.site')
        ->set('page.front', '/node')
        ->save(TRUE);
    }

    // Assign user 1 the "administrator" role.
    $user = User::load(1);
    $user->roles[] = 'administrator';
    $user->save();


}

  /**
   * Implements hook_modules_installed().
  */
  function xnavi_modules_installed($modules) {
    if (!InstallerKernel::installationAttempted() && !Drupal::isConfigSyncing()) {
      /** @var \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList */
      $moduleExtensionList = \Drupal::service('extension.list.module');
      $xnavi_features = array_filter($moduleExtensionList->getList(), function (Extension $module) {
        return $module->info['package'] === 'x.Navi';
      });
  
      foreach ($xnavi_features as $id => $extension) {
  
        $dependencies = array_map(function ($dependency) {
          return Dependency::createFromString($dependency)->getName();
        }, $extension->info['dependencies']);
  
        if (!in_array($id, $modules) && !empty(array_intersect($modules, $dependencies))) {
          \Drupal::messenger()->addWarning(t('To get the full x.Navi experience, we recommend to install the @module module. See all supported optional modules at <a href="/admin/modules/extend-xnavi">x.Navi modules</a>.', ['@module' => $extension->info['name']]));
        }
      }
    }
  }





