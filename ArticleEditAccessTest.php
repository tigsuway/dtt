<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class ArticleEditAccessTest extends ExistingSiteBase {

  public function testAnonymousCannotEditArticle(): void {
    // 1) Create a published article.
    $title = 'DTT Edit Test Article ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'status' => NodeInterface::PUBLISHED,
    ]);

    // 2) Try to access edit form as anonymous user.
    $this->drupalGet($node->toUrl('edit-form')->toString());

    // Should be denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testUserWithEditOwnArticleCanEditTheirArticle(): void {
    // 1) Create user with specific permissions.
    $user = $this->createUser([
      'create article content',
      'edit own article content',
    ]);
    $this->drupalLogin($user);

    // 2) Create an article as this user.
    $title = 'DTT User Own Article ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
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
