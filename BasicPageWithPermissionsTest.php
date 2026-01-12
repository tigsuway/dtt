<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\node\NodeInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class BasicPageWithPermissionsTest extends ExistingSiteBase {

  public function testUserWithBypassNodeAccessCanSeeUnpublishedPage(): void {
    // 1) Create an unpublished basic page.
    $title = 'DTT Bypass Access Page ' . uniqid();
    $body_text = 'Bypass access content ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'page',
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

    // 3) Visit unpublished page - should be accessible.
    $this->drupalGet($node->toUrl()->toString());

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextContains($body_text);
  }

}
