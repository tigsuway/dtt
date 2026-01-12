<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class ArticleAuthenticatedUserTest extends ExistingSiteBase {

  public function testAuthenticatedUserCanSeePublishedArticle(): void {
    // 1) Create a published article.
    $title = 'DTT Auth User Article ' . uniqid();
    $body_text = 'Authenticated content ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'body' => [
        'value' => $body_text,
        'format' => 'basic_html',
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $node->setPublished();
    $node->save();

    // 2) Create and login as authenticated user.
    $user = $this->createUser();
    $this->drupalLogin($user);

    // 3) Visit the article and verify access.
    $this->drupalGet($node->toUrl()->toString());

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextContains($body_text);
  }

}
