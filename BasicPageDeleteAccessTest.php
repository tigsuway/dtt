<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class BasicPageDeleteAccessTest extends ExistingSiteBase {

  public function testAnonymousCannotDeleteBasicPage(): void {
    // 1) Create a published basic page.
    $title = 'DTT Delete Test Page ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'page',
      'title' => $title,
      'status' => NodeInterface::PUBLISHED,
    ]);

    // 2) Try to access delete form as anonymous user.
    $this->drupalGet($node->toUrl('delete-form')->toString());

    // Should be denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testUserWithDeletePermissionCanDeletePage(): void {
    // 1) Create user with delete permissions.
    $user = $this->createUser([
      'create page content',
      'delete any page content',
    ]);
    $this->drupalLogin($user);

    // 2) Create a page.
    $title = 'DTT Deletable Page ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'page',
      'title' => $title,
      'status' => NodeInterface::PUBLISHED,
    ]);

    // 3) User should be able to access delete form.
    $this->drupalGet($node->toUrl('delete-form')->toString());

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Are you sure you want to delete');
    $this->assertSession()->buttonExists('Delete');
  }

}
