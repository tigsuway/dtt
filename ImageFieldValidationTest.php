<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\file\Entity\File;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class ImageFieldValidationTest extends ExistingSiteBase {

  public function testImageFieldAltTextStorage(): void {
    // 1) Create user with permission.
    $user = $this->createUser([
      'create article content',
      'access content',
    ]);
    $this->drupalLogin($user);

    // 2) Create a test image file.
    $image_data = $this->generateTestImage();

    /** @var \Drupal\file\FileInterface $file */
    $file = File::create([
      'uri' => 'public://test-alt-' . uniqid() . '.png',
      'status' => 1,
    ]);
    file_put_contents($file->getFileUri(), $image_data);
    $file->save();

    // 3) Create an article with image and alt text.
    $alt_text = 'Custom alt text for accessibility ' . uniqid();
    $title = 'Article with alt text ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => $alt_text,
      ],
      'status' => 1,
    ]);

    // 4) Reload and verify alt text is stored.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertEquals($alt_text, $node->field_image->alt);

    // 5) Visit the page and verify alt text in HTML.
    $this->drupalGet($node->toUrl()->toString());
    $this->assertSession()->elementExists('css', 'img[alt="' . $alt_text . '"]');
  }

  public function testImageFieldTitleTextStorage(): void {
    // 1) Create user with permission.
    $user = $this->createUser([
      'create article content',
      'access content',
    ]);
    $this->drupalLogin($user);

    // 2) Create a test image file.
    $image_data = $this->generateTestImage();

    /** @var \Drupal\file\FileInterface $file */
    $file = File::create([
      'uri' => 'public://test-title-' . uniqid() . '.png',
      'status' => 1,
    ]);
    file_put_contents($file->getFileUri(), $image_data);
    $file->save();

    // 3) Create an article with image and title text.
    $image_title = 'Image title text ' . uniqid();
    $node_title = 'Article with image title ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $node_title,
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Alt text',
        'title' => $image_title,
      ],
      'status' => 1,
    ]);

    // 4) Reload and verify title text is stored.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertEquals($image_title, $node->field_image->title);
  }

  public function testImageFileReference(): void {
    // 1) Create a test image file.
    $image_data = $this->generateTestImage();

    /** @var \Drupal\file\FileInterface $file */
    $file = File::create([
      'uri' => 'public://test-ref-' . uniqid() . '.png',
      'status' => 1,
    ]);
    file_put_contents($file->getFileUri(), $image_data);
    $file->save();

    $file_id = $file->id();

    // 2) Create an article referencing the file.
    $title = 'Article with file reference ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'field_image' => [
        'target_id' => $file_id,
        'alt' => 'Alt text',
      ],
      'status' => 1,
    ]);

    // 3) Verify the file reference is maintained.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertEquals($file_id, $node->field_image->target_id);

    // 4) Verify we can load the file through the reference.
    $referenced_file = $node->field_image->entity;
    $this->assertNotNull($referenced_file);
    $this->assertEquals($file_id, $referenced_file->id());
  }

  public function testMultipleImageUpload(): void {
    // Test that image field can handle empty values correctly.

    // 1) Create user with permission.
    $user = $this->createUser([
      'create article content',
      'access content',
    ]);
    $this->drupalLogin($user);

    // 2) Create an article without an image (field is optional).
    $title = 'Article without image ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'body' => ['value' => 'Body text'],
      'status' => 1,
    ]);

    // 3) Verify field is empty.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertTrue($node->field_image->isEmpty());

    // 4) Now add an image.
    $image_data = $this->generateTestImage();
    $file = File::create([
      'uri' => 'public://test-add-' . uniqid() . '.png',
      'status' => 1,
    ]);
    file_put_contents($file->getFileUri(), $image_data);
    $file->save();

    $node->field_image = [
      'target_id' => $file->id(),
      'alt' => 'Added alt text',
    ];
    $node->save();

    // 5) Verify image is now present.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertFalse($node->field_image->isEmpty());
    $this->assertEquals($file->id(), $node->field_image->target_id);
  }

  public function testImageFieldRendering(): void {
    // 1) Create user with permission.
    $user = $this->createUser([
      'create article content',
      'access content',
    ]);
    $this->drupalLogin($user);

    // 2) Create a test image.
    $image_data = $this->generateTestImage();
    $file = File::create([
      'uri' => 'public://test-render-' . uniqid() . '.png',
      'status' => 1,
    ]);
    file_put_contents($file->getFileUri(), $image_data);
    $file->save();

    // 3) Create article with image.
    $title = 'Article for rendering test ' . uniqid();
    $alt_text = 'Render test alt ' . uniqid();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => $alt_text,
      ],
      'status' => 1,
    ]);

    // 4) Visit the page.
    $this->drupalGet($node->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);

    // 5) Verify image is rendered with correct attributes.
    $this->assertSession()->elementExists('css', 'img[alt="' . $alt_text . '"]');

    // 6) Verify image src contains the file.
    $page = $this->getSession()->getPage();
    $img_element = $page->find('css', 'img[alt="' . $alt_text . '"]');
    $this->assertNotNull($img_element);

    $src = $img_element->getAttribute('src');
    $this->assertNotEmpty($src);
    $this->assertStringContainsString('test-render-', $src);
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
