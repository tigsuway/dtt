<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

final class MediaLibraryModuleToggleTest extends ExistingSiteBase {

  public function testEnableMediaLibraryModule(): void {
    // 1) Create a user with permission to administer modules.
    $user = $this->createUser(['administer modules', 'access media overview']);
    $this->drupalLogin($user);

    // 2) Ensure Media Library is initially uninstalled (if needed).
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller */
    $moduleInstaller = \Drupal::service('module_installer');

    if (\Drupal::moduleHandler()->moduleExists('media_library')) {
      $moduleInstaller->uninstall(['media_library']);
    }

    // Rebuild cache after uninstall.
    drupal_flush_all_caches();

    // 3) Verify module is not enabled.
    $this->assertFalse(
      \Drupal::moduleHandler()->moduleExists('media_library'),
      'Media Library module should not be enabled initially.'
    );

    // 4) Enable the Media Library module (this will also enable Media module as dependency).
    $moduleInstaller->install(['media_library']);

    // Rebuild cache after install.
    drupal_flush_all_caches();

    // 5) Verify module is now enabled.
    $this->assertTrue(
      \Drupal::moduleHandler()->moduleExists('media_library'),
      'Media Library module should be enabled after installation.'
    );

    // 6) Verify we can access the media library page.
    $this->drupalGet('/admin/content/media');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Media');
  }

  public function testDisableMediaLibraryModule(): void {
    // 1) Create a user with permission to administer modules.
    $user = $this->createUser(['administer modules', 'access media overview']);
    $this->drupalLogin($user);

    // 2) Ensure Media Library is initially installed.
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller */
    $moduleInstaller = \Drupal::service('module_installer');

    if (!\Drupal::moduleHandler()->moduleExists('media_library')) {
      $moduleInstaller->install(['media_library']);
      drupal_flush_all_caches();
    }

    // 3) Verify module is enabled.
    $this->assertTrue(
      \Drupal::moduleHandler()->moduleExists('media_library'),
      'Media Library module should be enabled initially.'
    );

    // 4) Disable the Media Library module.
    $moduleInstaller->uninstall(['media_library']);

    // Rebuild cache after uninstall.
    drupal_flush_all_caches();

    // 5) Verify module is now disabled.
    $this->assertFalse(
      \Drupal::moduleHandler()->moduleExists('media_library'),
      'Media Library module should be disabled after uninstallation.'
    );

    // 6) Verify we cannot access the media library page (Media module still enabled).
    // User should still be able to access /admin/content/media since Media module provides it.
    // Media Library module only adds the library widget functionality.
    $this->drupalGet('/admin/content/media');
    $this->assertSession()->statusCodeEquals(200);
  }

  public function testMediaLibraryModuleEnableViaUI(): void {
    // 1) Create a user with permission to administer modules.
    $user = $this->createUser(['administer modules']);
    $this->drupalLogin($user);

    // 2) Ensure Media Library is initially uninstalled.
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller */
    $moduleInstaller = \Drupal::service('module_installer');

    if (\Drupal::moduleHandler()->moduleExists('media_library')) {
      $moduleInstaller->uninstall(['media_library']);
      drupal_flush_all_caches();
    }

    // 3) Visit the modules page.
    $this->drupalGet('/admin/modules');
    $this->assertSession()->statusCodeEquals(200);

    // 4) Check that Media Library checkbox exists.
    $this->assertSession()->fieldExists('modules[media_library][enable]');

    // 5) Enable Media Library module via the form.
    $this->submitForm([
      'modules[media_library][enable]' => TRUE,
    ], 'Install');

    // 6) Confirm installation if confirmation page appears.
    // Check if we're on confirmation page by looking for the Continue button.
    $page = $this->getSession()->getPage();
    if ($page->findButton('Continue')) {
      $this->submitForm([], 'Continue');
    }

    // 7) Verify success message (could be singular or plural, "installed" or "enabled").
    $page_text = $this->getSession()->getPage()->getText();
    $has_success = (strpos($page_text, 'has been installed') !== false) ||
                   (strpos($page_text, 'have been installed') !== false) ||
                   (strpos($page_text, 'has been enabled') !== false) ||
                   (strpos($page_text, 'have been enabled') !== false);
    $this->assertTrue($has_success, 'Module installation success message not found');

    // 8) Verify module is now enabled.
    drupal_flush_all_caches();
    $this->assertTrue(
      \Drupal::moduleHandler()->moduleExists('media_library'),
      'Media Library module should be enabled after UI installation.'
    );
  }

  public function testMediaLibraryModuleDisableViaUI(): void {
    // 1) Create a user with permission to administer modules.
    $user = $this->createUser(['administer modules']);
    $this->drupalLogin($user);

    // 2) Ensure Media Library is initially installed.
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller */
    $moduleInstaller = \Drupal::service('module_installer');

    if (!\Drupal::moduleHandler()->moduleExists('media_library')) {
      $moduleInstaller->install(['media_library']);
      drupal_flush_all_caches();
    }

    // 3) Visit the uninstall page.
    $this->drupalGet('/admin/modules/uninstall');
    $this->assertSession()->statusCodeEquals(200);

    // 4) Check that Media Library uninstall checkbox exists.
    $this->assertSession()->fieldExists('uninstall[media_library]');

    // 5) Uninstall Media Library module via the form.
    $this->submitForm([
      'uninstall[media_library]' => TRUE,
    ], 'Uninstall');

    // 6) Confirm uninstallation on confirmation page.
    $this->assertSession()->pageTextContains('Confirm uninstall');
    $this->submitForm([], 'Uninstall');

    // 7) Verify success message.
    $this->assertSession()->pageTextContains('The selected modules have been uninstalled');

    // 8) Verify module is now disabled.
    drupal_flush_all_caches();
    $this->assertFalse(
      \Drupal::moduleHandler()->moduleExists('media_library'),
      'Media Library module should be disabled after UI uninstallation.'
    );
  }

}
