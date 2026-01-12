<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class BasicPageAuthenticatedUserTest extends ExistingSiteBase {

  public function testAuthenticatedUserCanSeePublishedBasicPage(): void {
    // 1) Create a published basic page.
    $title = 'DTT Auth User Page ' . uniqid();
    $body_text = 'Authenticated content ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'page',
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

    // 3) Visit the page and verify access.
    $this->drupalGet($node->toUrl()->toString());

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextContains($body_text);
  }

}
