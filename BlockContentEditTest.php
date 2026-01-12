<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

final class BlockContentEditTest extends ExistingSiteBase {

  public function testUserCanEditOwnBlock(): void {
    // 1) Create user with edit permission.
    $user = $this->createUser([
      'create basic block content',
      'edit any basic block content',
      'access block library',
    ]);
    $this->drupalLogin($user);

    // 2) Create a custom block.
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = \Drupal::entityTypeManager()->getStorage('block_content')->create([
      'type' => 'basic',
      'info' => 'Original Block ' . uniqid(),
      'body' => [
        'value' => 'Original body content',
        'format' => 'basic_html',
      ],
    ]);
    $block_content->save();

    // 3) Visit the edit form.
    $this->drupalGet($block_content->toUrl('edit-form')->toString());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldExists('info[0][value]');

    // 4) Update the block.
    $new_description = 'Updated Block ' . uniqid();
    $new_body = 'Updated body content ' . uniqid();

    $this->submitForm([
      'info[0][value]' => $new_description,
      'body[0][value]' => $new_body,
    ], 'Save');

    // 5) Verify success message.
    $this->assertSession()->pageTextContains('basic');
    $this->assertSession()->pageTextContains($new_description);
    $this->assertSession()->pageTextContains('has been updated');
  }

  public function testAnonymousCannotEditBlock(): void {
    // 1) Create a block programmatically.
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = \Drupal::entityTypeManager()->getStorage('block_content')->create([
      'type' => 'basic',
      'info' => 'Test Block ' . uniqid(),
      'body' => [
        'value' => 'Test body',
        'format' => 'basic_html',
      ],
    ]);
    $block_content->save();

    // 2) Attempt to visit edit form as anonymous.
    $this->drupalGet($block_content->toUrl('edit-form')->toString());

    // 3) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testUserWithoutPermissionCannotEditBlock(): void {
    // 1) Create a block programmatically.
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = \Drupal::entityTypeManager()->getStorage('block_content')->create([
      'type' => 'basic',
      'info' => 'Test Block ' . uniqid(),
      'body' => [
        'value' => 'Test body',
        'format' => 'basic_html',
      ],
    ]);
    $block_content->save();

    // 2) Create user without edit permission.
    $user = $this->createUser(['access block library']);
    $this->drupalLogin($user);

    // 3) Attempt to visit edit form.
    $this->drupalGet($block_content->toUrl('edit-form')->toString());

    // 4) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

}
