<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

final class ArticleFieldValidationTest extends ExistingSiteBase {

  public function testArticleRequiresTitleField(): void {
    // 1) Create user with article creation permission.
    $user = $this->createUser(['create article content']);
    $this->drupalLogin($user);

    // 2) Visit the article creation form.
    $this->drupalGet('/node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // 3) Try to submit without title (should fail validation).
    $this->submitForm([
      'body[0][value]' => 'Some body content',
    ], 'Save');

    // 4) Should show validation error.
    $this->assertSession()->pageTextContains('Title field is required');
  }

  public function testArticleAcceptsValidData(): void {
    // 1) Create user with article creation permission.
    $user = $this->createUser(['create article content']);
    $this->drupalLogin($user);

    // 2) Visit the article creation form.
    $this->drupalGet('/node/add/article');

    // 3) Submit with valid data.
    $title = 'DTT Valid Article ' . uniqid();
    $body = 'Valid body content ' . uniqid();

    $this->submitForm([
      'title[0][value]' => $title,
      'body[0][value]' => $body,
    ], 'Save');

    // 4) Should successfully create and redirect to the article.
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextContains($body);
    $this->assertSession()->pageTextContains('has been created');
  }

}
