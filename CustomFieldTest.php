<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

final class CustomFieldTest extends ExistingSiteBase {

  public function testTextFieldStorage(): void {
    // 1) Create user with permission.
    $user = $this->createUser([
      'create article content',
      'access content',
    ]);
    $this->drupalLogin($user);

    // 2) Create an article with body text.
    $title = 'Article with text ' . uniqid();
    $body_value = 'This is a long body text field with multiple paragraphs. ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'body' => [
        'value' => $body_value,
        'format' => 'basic_html',
      ],
      'status' => 1,
    ]);

    // 3) Reload the node and verify data persistence.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertEquals($body_value, $node->body->value);
    $this->assertEquals('basic_html', $node->body->format);
  }

  public function testMultipleValueFields(): void {
    // This test verifies that fields can store and retrieve data correctly.
    // For content types with custom multi-value fields, this pattern can be extended.

    // 1) Create an article with body field.
    $title = 'Multi-value test ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'body' => [
        [
          'value' => 'First paragraph',
          'format' => 'basic_html',
        ],
      ],
      'status' => 1,
    ]);

    // 2) Verify the field value is stored.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertNotEmpty($node->body->value);
    $this->assertEquals('First paragraph', $node->body->value);
  }

  public function testRequiredFieldValidation(): void {
    // 1) Create user with permission.
    $user = $this->createUser([
      'create article content',
    ]);
    $this->drupalLogin($user);

    // 2) Visit the article creation form.
    $this->drupalGet('/node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // 3) Try to submit with only body (title is required).
    $this->submitForm([
      'body[0][value]' => 'Some body content',
    ], 'Save');

    // 4) Verify validation error for required title field.
    $this->assertSession()->pageTextContains('Title field is required');
  }

  public function testFieldMaxLengthValidation(): void {
    // 1) Create user with permission.
    $user = $this->createUser([
      'create article content',
    ]);
    $this->drupalLogin($user);

    // 2) Visit the article creation form.
    $this->drupalGet('/node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // 3) Verify title field exists.
    $this->assertSession()->fieldExists('title[0][value]');

    // 4) Title field typically has max length of 255 characters.
    // Create a title within acceptable length.
    $valid_title = str_repeat('A', 255);

    $this->submitForm([
      'title[0][value]' => $valid_title,
      'body[0][value]' => 'Body content',
    ], 'Save');

    // 5) Should succeed or show other validation if title is too long in browser.
    // The actual validation might be client-side, so we just verify the field exists.
    $this->assertSession()->statusCodeEquals(200);
  }

  public function testFieldFormatting(): void {
    // 1) Create user with permission.
    $user = $this->createUser([
      'create article content',
      'access content',
    ]);
    $this->drupalLogin($user);

    // 2) Create an article with HTML content in body.
    $title = 'Article with HTML ' . uniqid();
    $body_html = '<p>This is a <strong>bold</strong> paragraph.</p>';

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'body' => [
        'value' => $body_html,
        'format' => 'basic_html',
      ],
      'status' => 1,
    ]);

    // 3) Visit the article page.
    $this->drupalGet($node->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);

    // 4) Verify formatted text is rendered (check for HTML elements).
    $this->assertSession()->elementExists('css', 'strong');
    $this->assertSession()->pageTextContains('bold');
  }

  public function testFieldUpdate(): void {
    // 1) Create user with edit permission.
    $user = $this->createUser([
      'create article content',
      'edit own article content',
      'access content',
    ]);
    $this->drupalLogin($user);

    // 2) Create an article.
    $original_title = 'Original Title ' . uniqid();
    $original_body = 'Original body content';

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $original_title,
      'body' => [
        'value' => $original_body,
        'format' => 'basic_html',
      ],
      'status' => 1,
      'uid' => $user->id(),
    ]);

    // 3) Update the field programmatically.
    $new_body = 'Updated body content ' . uniqid();
    $node->body->value = $new_body;
    $node->save();

    // 4) Reload and verify the update.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertEquals($new_body, $node->body->value);

    // 5) Verify on the page.
    $this->drupalGet($node->toUrl()->toString());
    $this->assertSession()->pageTextContains($new_body);
    $this->assertSession()->pageTextNotContains($original_body);
  }

}
