<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class ArticleDeleteAccessTest extends ExistingSiteBase {

  public function testAnonymousCannotDeleteArticle(): void {
    // 1) Create a published article.
    $title = 'DTT Delete Test Article ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'status' => NodeInterface::PUBLISHED,
    ]);

    // 2) Try to access delete form as anonymous user.
    $this->drupalGet($node->toUrl('delete-form')->toString());

    // Should be denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testUserWithDeletePermissionCanDeleteArticle(): void {
    // 1) Create user with delete permissions.
    $user = $this->createUser([
      'create article content',
      'delete any article content',
    ]);
    $this->drupalLogin($user);

    // 2) Create an article.
    $title = 'DTT Deletable Article ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
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
