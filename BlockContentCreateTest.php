<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

final class BlockContentCreateTest extends ExistingSiteBase {

  public function testUserCanCreateBasicBlock(): void {
    // 1) Create user with permission to create blocks.
    $user = $this->createUser([
      'create basic block content',
      'access block library',
    ]);
    $this->drupalLogin($user);

    // 2) Visit the block creation form.
    $this->drupalGet('/block/add/basic');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Add basic block');

    // 3) Fill in the form.
    $block_description = 'Test Block Description ' . uniqid();
    $block_body = 'Test Block Body ' . uniqid();

    $this->submitForm([
      'info[0][value]' => $block_description,
      'body[0][value]' => $block_body,
    ], 'Save');

    // 4) Verify success message.
    $this->assertSession()->pageTextContains('basic');
    $this->assertSession()->pageTextContains($block_description);
    $this->assertSession()->pageTextContains('has been created');

    // 5) Verify we can see it in the block library.
    $this->drupalGet('/admin/content/block');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($block_description);
  }

  public function testAnonymousCannotCreateBlock(): void {
    // 1) Attempt to visit the block creation form as anonymous.
    $this->drupalGet('/block/add/basic');

    // 2) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testBlockCreationRequiresDescription(): void {
    // 1) Create user with permission to create blocks.
    $user = $this->createUser([
      'create basic block content',
      'access block library',
    ]);
    $this->drupalLogin($user);

    // 2) Visit the block creation form.
    $this->drupalGet('/block/add/basic');

    // 3) Try to submit without description (info field is required).
    $this->submitForm([
      'body[0][value]' => 'Some body content',
    ], 'Save');

    // 4) Should show validation error.
    $this->assertSession()->pageTextContains('Block description field is required');
  }

}
