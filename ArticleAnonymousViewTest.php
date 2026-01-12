<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class ArticleAnonymousViewTest extends ExistingSiteBase {

  public function testAnonymousCanSeePublishedArticleTitleAndBody(): void {
    // 1) Create an article with Title + Body.
    $title = 'DTT Article ' . uniqid();
    $body_text = 'Body content ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'body' => [
        'value' => $body_text,
        'format' => 'basic_html',
      ],
      // 2) Publish it.
      'status' => NodeInterface::PUBLISHED,
    ]);

    // Some stacks/content moderation setups still benefit from an explicit publish+save.
    $node->setPublished();
    $node->save();

    // 3) Visit as anonymous user and assert Title + Body are present.
    // ExistingSiteBase starts unauthenticated unless your test logs in.
    $this->drupalGet($node->toUrl()->toString());

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextContains($body_text);
  }

}
