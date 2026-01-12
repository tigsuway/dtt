<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class ArticleWithPermissionsTest extends ExistingSiteBase {

  public function testUserWithBypassNodeAccessCanSeeUnpublishedArticle(): void {
    // 1) Create an unpublished article.
    $title = 'DTT Bypass Access Article ' . uniqid();
    $body_text = 'Bypass access content ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'body' => [
        'value' => $body_text,
        'format' => 'basic_html',
      ],
      'status' => NodeInterface::NOT_PUBLISHED,
    ]);

    $node->setUnpublished();
    $node->save();

    // 2) Create user with 'bypass node access' permission.
    $user = $this->createUser(['bypass node access']);
    $this->drupalLogin($user);

    // 3) Visit unpublished article - should be accessible.
    $this->drupalGet($node->toUrl()->toString());

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextContains($body_text);
  }

}
