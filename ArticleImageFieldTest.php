<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\file\Entity\File;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class ArticleImageFieldTest extends ExistingSiteBase {

  public function testArticleWithImageFieldCanBeCreated(): void {
    // 1) Create user with permission to create articles.
    $user = $this->createUser([
      'create article content',
      'access content',
    ]);
    $this->drupalLogin($user);

    // 2) Create a test image file.
    $image_data = $this->generateTestImage();

    /** @var \Drupal\file\FileInterface $file */
    $file = File::create([
      'uri' => 'public://test-image-' . uniqid() . '.png',
      'status' => 1,
    ]);
    file_put_contents($file->getFileUri(), $image_data);
    $file->save();

    // 3) Create an article with the image.
    $title = 'Article with Image ' . uniqid();
    $body_text = 'Article body content ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'body' => [
        'value' => $body_text,
        'format' => 'basic_html',
      ],
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Test image alt text',
        'title' => 'Test image title',
      ],
      'status' => 1,
    ]);

    // 4) Visit the article page.
    $this->drupalGet($node->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);

    // 5) Verify title and body are visible.
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextContains($body_text);

    // 6) Verify image is present (check for img tag with alt text).
    $this->assertSession()->elementExists('css', 'img[alt="Test image alt text"]');
  }

  public function testArticleImageFieldIsOptional(): void {
    // 1) Create user with permission to create articles.
    $user = $this->createUser([
      'create article content',
      'access content',
    ]);
    $this->drupalLogin($user);

    // 2) Create an article without an image.
    $title = 'Article without Image ' . uniqid();
    $body_text = 'Article body content ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'body' => [
        'value' => $body_text,
        'format' => 'basic_html',
      ],
      'status' => 1,
    ]);

    // 3) Visit the article page.
    $this->drupalGet($node->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);

    // 4) Verify article displays correctly without image.
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextContains($body_text);
  }

  public function testArticleImageFieldViaUI(): void {
    // 1) Create user with permission to create articles.
    $user = $this->createUser([
      'create article content',
      'access content',
    ]);
    $this->drupalLogin($user);

    // 2) Visit the article creation form.
    $this->drupalGet('/node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // 3) Verify image field exists on the form.
    $this->assertSession()->fieldExists('files[field_image_0]');

    // 4) Fill in title and body (skip image upload for simplicity).
    $title = 'Article via UI ' . uniqid();
    $body = 'Body content via UI ' . uniqid();

    $this->submitForm([
      'title[0][value]' => $title,
      'body[0][value]' => $body,
    ], 'Save');

    // 5) Verify success.
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextContains('has been created');
  }

  /**
   * Generate a minimal PNG image for testing.
   *
   * @return string
   *   Binary PNG data.
   */
  private function generateTestImage(): string {
    // Create a minimal 1x1 pixel PNG image.
    $image = imagecreate(1, 1);
    imagecolorallocate($image, 255, 255, 255);

    ob_start();
    imagepng($image);
    $data = ob_get_clean();
    imagedestroy($image);

    return $data;
  }

}
