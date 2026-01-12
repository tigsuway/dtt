<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class BasicPageEditAccessTest extends ExistingSiteBase {

  public function testAnonymousCannotEditBasicPage(): void {
    // 1) Create a published basic page.
    $title = 'DTT Edit Test Page ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'page',
      'title' => $title,
      'status' => NodeInterface::PUBLISHED,
    ]);

    // 2) Try to access edit form as anonymous user.
    $this->drupalGet($node->toUrl('edit-form')->toString());

    // Should be denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testUserWithEditOwnPageCanEditTheirPage(): void {
    // 1) Create user with specific permissions.
    $user = $this->createUser([
      'create page content',
      'edit own page content',
    ]);
    $this->drupalLogin($user);

    // 2) Create a page as this user.
    $title = 'DTT User Own Page ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'page',
      'title' => $title,
      'uid' => $user->id(),
      'status' => NodeInterface::PUBLISHED,
    ]);

    // 3) User should be able to access their own edit form.
    $this->drupalGet($node->toUrl('edit-form')->toString());

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('title[0][value]');
  }

}
