<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

final class BlockContentLibraryAccessTest extends ExistingSiteBase {

  public function testUserWithPermissionCanAccessBlockLibrary(): void {
    // 1) Create user with access to block library.
    $user = $this->createUser(['access block library']);
    $this->drupalLogin($user);

    // 2) Visit the block content library.
    $this->drupalGet('/admin/content/block');

    // 3) Should have access.
    $this->assertSession()->statusCodeEquals(200);
    // Verify we're on the block admin page by checking the URL.
    $current_url = $this->getSession()->getCurrentUrl();
    $this->assertStringContainsString('/admin/content/block', $current_url);
  }

  public function testUserCannotAccessBlockLibraryWithoutPermission(): void {
    // 1) Create user without block library permission.
    $user = $this->createUser([]);
    $this->drupalLogin($user);

    // 2) Attempt to visit the block content library.
    $this->drupalGet('/admin/content/block');

    // 3) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

}
