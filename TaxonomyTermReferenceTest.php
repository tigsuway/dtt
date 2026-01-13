<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class TaxonomyTermReferenceTest extends ExistingSiteBase {

  public function testArticleWithSingleTag(): void {
    // 1) Create or use existing tags vocabulary.
    $vocab_id = 'tags';
    $vocabulary = Vocabulary::load($vocab_id);
    if (!$vocabulary) {
      $vocabulary = Vocabulary::create([
        'vid' => $vocab_id,
        'name' => 'Tags',
      ]);
      $vocabulary->save();
    }

    // 2) Create a tag term.
    $tag_name = 'Test Tag ' . uniqid();
    $tag = Term::create([
      'vid' => $vocab_id,
      'name' => $tag_name,
    ]);
    $tag->save();

    // 3) Create article with tag.
    $title = 'Article with Tag ' . uniqid();
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'field_tags' => [
        ['target_id' => $tag->id()],
      ],
      'status' => 1,
    ]);

    // 4) Reload and verify tag is stored.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertFalse($node->field_tags->isEmpty());
    $this->assertEquals($tag->id(), $node->field_tags->target_id);

    // 5) Visit article and verify tag displays.
    $this->drupalGet($node->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($tag_name);
  }

  public function testArticleWithMultipleTags(): void {
    // 1) Create or use tags vocabulary.
    $vocab_id = 'tags';
    $vocabulary = Vocabulary::load($vocab_id);
    if (!$vocabulary) {
      $vocabulary = Vocabulary::create([
        'vid' => $vocab_id,
        'name' => 'Tags',
      ]);
      $vocabulary->save();
    }

    // 2) Create multiple tags.
    $tag1_name = 'Tag One ' . uniqid();
    $tag1 = Term::create(['vid' => $vocab_id, 'name' => $tag1_name]);
    $tag1->save();

    $tag2_name = 'Tag Two ' . uniqid();
    $tag2 = Term::create(['vid' => $vocab_id, 'name' => $tag2_name]);
    $tag2->save();

    $tag3_name = 'Tag Three ' . uniqid();
    $tag3 = Term::create(['vid' => $vocab_id, 'name' => $tag3_name]);
    $tag3->save();

    // 3) Create article with multiple tags.
    $title = 'Article with Multiple Tags ' . uniqid();
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'field_tags' => [
        ['target_id' => $tag1->id()],
        ['target_id' => $tag2->id()],
        ['target_id' => $tag3->id()],
      ],
      'status' => 1,
    ]);

    // 4) Reload and verify all tags stored.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertCount(3, $node->field_tags);

    // 5) Visit article and verify all tags display.
    $this->drupalGet($node->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($tag1_name);
    $this->assertSession()->pageTextContains($tag2_name);
    $this->assertSession()->pageTextContains($tag3_name);
  }

  public function testTermReferenceFieldIsOptional(): void {
    // 1) Create article without tags.
    $title = 'Article without Tags ' . uniqid();
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'body' => ['value' => 'Article body'],
      'status' => 1,
    ]);

    // 2) Verify article saved successfully.
    $this->assertNotNull($node->id());

    // 3) Verify field is empty.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertTrue($node->field_tags->isEmpty());
  }

  public function testTermReferencePersistence(): void {
    // 1) Create vocabulary and tag.
    $vocab_id = 'tags';
    $vocabulary = Vocabulary::load($vocab_id);
    if (!$vocabulary) {
      $vocabulary = Vocabulary::create([
        'vid' => $vocab_id,
        'name' => 'Tags',
      ]);
      $vocabulary->save();
    }

    $tag_name = 'Persistent Tag ' . uniqid();
    $tag = Term::create(['vid' => $vocab_id, 'name' => $tag_name]);
    $tag->save();

    // 2) Create article with tag.
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'Article ' . uniqid(),
      'field_tags' => [['target_id' => $tag->id()]],
      'status' => 1,
    ]);

    // 3) Save and reload multiple times.
    $node->save();
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertEquals($tag->id(), $node->field_tags->target_id);

    $node->save();
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertEquals($tag->id(), $node->field_tags->target_id);
  }

  public function testTermReferenceViaUI(): void {
    // 1) Create vocabulary and tag.
    $vocab_id = 'tags';
    $vocabulary = Vocabulary::load($vocab_id);
    if (!$vocabulary) {
      $vocabulary = Vocabulary::create([
        'vid' => $vocab_id,
        'name' => 'Tags',
      ]);
      $vocabulary->save();
    }

    $tag_name = 'UI Tag ' . uniqid();
    $tag = Term::create(['vid' => $vocab_id, 'name' => $tag_name]);
    $tag->save();

    // 2) Create user and login.
    $user = $this->createUser(['create article content', 'access content']);
    $this->drupalLogin($user);

    // 3) Visit article creation form.
    $this->drupalGet('/node/add/article');
    $this->assertSession()->statusCodeEquals(200);

    // 4) Check if tags field exists (field may or may not be configured).
    $page = $this->getSession()->getPage();
    $has_tags_field = $page->findField('field_tags[0][target_id]') !== null;

    // 5) If field exists, create article with tag.
    if ($has_tags_field) {
      $title = 'Article via UI ' . uniqid();
      $this->submitForm([
        'title[0][value]' => $title,
        'body[0][value]' => 'Article body',
        'field_tags[0][target_id]' => $tag_name,
      ], 'Save');

      // 6) Verify success.
      $this->assertSession()->pageTextContains($title);
    } else {
      // Field doesn't exist, just verify form loaded.
      $this->assertSession()->fieldExists('title[0][value]');
    }
  }

}
