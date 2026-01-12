<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

final class BlockContentAnonymousViewTest extends ExistingSiteBase {

  public function testAnonymousCannotAccessBlockLibrary(): void {
    // 1) Attempt to visit the block content library as anonymous user.
    $this->drupalGet('/admin/content/block');

    // 2) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testAnonymousCanSeeBlockContentOnPage(): void {
    // 1) Create a custom block using entity storage.
    $body_value = 'Block body content ' . uniqid();

    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->create([
        'type' => 'basic',
        'info' => 'Test Block ' . uniqid(),
        'body' => [
          'value' => $body_value,
          'format' => 'basic_html',
        ],
      ]);
    $block_content->save();

    // 2) Place the block in a region (e.g., 'content' region).
    /** @var \Drupal\block\Entity\Block $block */
    $block = \Drupal::entityTypeManager()
      ->getStorage('block')
      ->create([
        'id' => 'test_block_' . uniqid(),
        'plugin' => 'block_content:' . $block_content->uuid(),
        'region' => 'content',
        'theme' => \Drupal::config('system.theme')->get('default'),
        'weight' => 0,
        'settings' => [
          'label' => $block_content->label(),
          'label_display' => '0',
        ],
      ]);
    $block->save();

    // 3) Visit the front page as anonymous user.
    $this->drupalGet('<front>');

    // 4) Verify block content is visible.
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($body_value);
  }

}
