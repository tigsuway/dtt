<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\taxonomy\Entity\Vocabulary;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class TaxonomyVocabularyCreateTest extends ExistingSiteBase {

  public function testUserCanCreateVocabulary(): void {
    // 1) Create user with permission to administer taxonomy.
    $user = $this->createUser(['administer taxonomy']);
    $this->drupalLogin($user);

    // 2) Visit the vocabulary creation form.
    $this->drupalGet('/admin/structure/taxonomy/add');
    $this->assertSession()->statusCodeEquals(200);

    // 3) Fill in the form.
    $vocab_name = 'Test Vocabulary ' . uniqid();
    $vocab_id = 'test_vocab_' . uniqid();

    $this->submitForm([
      'name' => $vocab_name,
      'vid' => $vocab_id,
      'description' => 'Test vocabulary description',
    ], 'Save');

    // 4) Verify success message.
    $this->assertSession()->pageTextContains('Created new vocabulary');

    // 5) Verify vocabulary appears in listing.
    $this->drupalGet('/admin/structure/taxonomy');
    $this->assertSession()->pageTextContains($vocab_name);
  }

  public function testAnonymousCannotCreateVocabulary(): void {
    // 1) Attempt to visit vocabulary creation form as anonymous.
    $this->drupalGet('/admin/structure/taxonomy/add');

    // 2) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testVocabularyRequiresName(): void {
    // 1) Create user with permission.
    $user = $this->createUser(['administer taxonomy']);
    $this->drupalLogin($user);

    // 2) Visit the vocabulary creation form.
    $this->drupalGet('/admin/structure/taxonomy/add');

    // 3) Submit without name (only machine name).
    $this->submitForm([
      'vid' => 'test_vocab_' . uniqid(),
      'description' => 'Description without name',
    ], 'Save');

    // 4) Verify validation error.
    $this->assertSession()->pageTextContains('Name field is required');
  }

  public function testVocabularyProgrammaticCreation(): void {
    // 1) Create vocabulary programmatically.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocab_name = 'Test Vocabulary ' . uniqid();

    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => $vocab_name,
      'description' => 'Programmatically created vocabulary',
    ]);
    $vocabulary->save();

    // 2) Verify vocabulary exists.
    $loaded_vocab = Vocabulary::load($vocab_id);
    $this->assertNotNull($loaded_vocab);
    $this->assertEquals($vocab_name, $loaded_vocab->label());
  }

}
