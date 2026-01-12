<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class ArticleUnpublishedAccessTest extends ExistingSiteBase {

  public function testAnonymousCannotSeeUnpublishedArticle(): void {
    // 1) Create an unpublished article.
    $title = 'DTT Unpublished Article ' . uniqid();
    $body_text = 'Unpublished content ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'body' => [
        'value' => $body_text,
        'format' => 'basic_html',
      ],
      // 2) Leave it unpublished.
      'status' => NodeInterface::NOT_PUBLISHED,
    ]);

    $node->setUnpublished();
    $node->save();

    // 3) Visit as anonymous user and expect access denied.
    $this->drupalGet($node->toUrl()->toString());

    // Anonymous users should get 403 Forbidden for unpublished content.
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->pageTextNotContains($title);
    $this->assertSession()->pageTextNotContains($body_text);
  }

}
