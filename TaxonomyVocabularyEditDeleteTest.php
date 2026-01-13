<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\taxonomy\Entity\Vocabulary;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class TaxonomyVocabularyEditDeleteTest extends ExistingSiteBase {

  public function testUserCanEditVocabulary(): void {
    // 1) Create vocabulary programmatically.
    $vocab_id = 'test_vocab_' . uniqid();
    $original_name = 'Original Vocabulary ' . uniqid();

    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => $original_name,
      'description' => 'Original description',
    ]);
    $vocabulary->save();

    // 2) Create user and login.
    $user = $this->createUser(['administer taxonomy']);
    $this->drupalLogin($user);

    // 3) Visit edit form.
    $this->drupalGet("/admin/structure/taxonomy/manage/{$vocab_id}");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldValueEquals('name', $original_name);

    // 4) Update vocabulary.
    $new_name = 'Updated Vocabulary ' . uniqid();
    $new_description = 'Updated description';

    $this->submitForm([
      'name' => $new_name,
      'description' => $new_description,
    ], 'Save');

    // 5) Verify changes saved.
    $this->assertSession()->pageTextContains('Updated vocabulary');

    // 6) Reload and verify.
    $vocabulary = Vocabulary::load($vocab_id);
    $this->assertEquals($new_name, $vocabulary->label());
    $this->assertEquals($new_description, $vocabulary->getDescription());
  }

  public function testAnonymousCannotEditVocabulary(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    // 2) Attempt to access edit form as anonymous.
    $this->drupalGet("/admin/structure/taxonomy/manage/{$vocab_id}");

    // 3) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testUserCanDeleteVocabulary(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocab_name = 'Vocabulary to Delete ' . uniqid();

    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => $vocab_name,
    ]);
    $vocabulary->save();

    // 2) Create user and login.
    $user = $this->createUser(['administer taxonomy']);
    $this->drupalLogin($user);

    // 3) Visit delete form.
    $this->drupalGet("/admin/structure/taxonomy/manage/{$vocab_id}/delete");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Are you sure you want to delete');

    // 4) Confirm deletion.
    $this->submitForm([], 'Delete');

    // 5) Verify success message.
    $this->assertSession()->pageTextContains('Deleted vocabulary');

    // 6) Verify vocabulary no longer exists.
    $vocabulary = Vocabulary::load($vocab_id);
    $this->assertNull($vocabulary);
  }

  public function testAnonymousCannotDeleteVocabulary(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    // 2) Attempt to access delete form as anonymous.
    $this->drupalGet("/admin/structure/taxonomy/manage/{$vocab_id}/delete");

    // 3) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

}
