<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

final class BlockContentDeleteTest extends ExistingSiteBase {

  public function testUserCanDeleteBlock(): void {
    // 1) Create user with delete permission.
    $user = $this->createUser([
      'delete any basic block content',
      'access block library',
    ]);
    $this->drupalLogin($user);

    // 2) Create a custom block.
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = \Drupal::entityTypeManager()->getStorage('block_content')->create([
      'type' => 'basic',
      'info' => 'Block to Delete ' . uniqid(),
      'body' => [
        'value' => 'This will be deleted',
        'format' => 'basic_html',
      ],
    ]);
    $block_content->save();

    // 3) Visit the delete form.
    $this->drupalGet($block_content->toUrl('delete-form')->toString());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Are you sure you want to delete');
    $this->assertSession()->buttonExists('Delete');

    // 4) Confirm deletion.
    $this->submitForm([], 'Delete');

    // 5) Verify success message.
    $this->assertSession()->pageTextContains('has been deleted');
  }

  public function testAnonymousCannotDeleteBlock(): void {
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

    // 2) Attempt to visit delete form as anonymous.
    $this->drupalGet($block_content->toUrl('delete-form')->toString());

    // 3) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testUserWithoutPermissionCannotDeleteBlock(): void {
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

    // 2) Create user without delete permission.
    $user = $this->createUser(['access block library']);
    $this->drupalLogin($user);

    // 3) Attempt to visit delete form.
    $this->drupalGet($block_content->toUrl('delete-form')->toString());

    // 4) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

}
