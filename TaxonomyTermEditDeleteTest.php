<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class TaxonomyTermEditDeleteTest extends ExistingSiteBase {

  public function testUserCanEditTerm(): void {
    // 1) Create vocabulary and term.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    $original_name = 'Original Term ' . uniqid();
    $term = Term::create([
      'vid' => $vocab_id,
      'name' => $original_name,
    ]);
    $term->save();

    // 2) Create user and login.
    $user = $this->createUser(['administer taxonomy']);
    $this->drupalLogin($user);

    // 3) Visit edit form.
    $this->drupalGet($term->toUrl('edit-form')->toString());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldValueEquals('name[0][value]', $original_name);

    // 4) Update term.
    $new_name = 'Updated Term ' . uniqid();
    $new_description = 'Updated description ' . uniqid();

    $this->submitForm([
      'name[0][value]' => $new_name,
      'description[0][value]' => $new_description,
    ], 'Save');

    // 5) Verify changes saved.
    $this->assertSession()->pageTextContains('Updated term');

    // 6) Reload and verify.
    $term = Term::load($term->id());
    $this->assertEquals($new_name, $term->getName());
    $this->assertEquals($new_description, $term->getDescription());
  }

  public function testAnonymousCannotEditTerm(): void {
    // 1) Create vocabulary and term.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    $term = Term::create([
      'vid' => $vocab_id,
      'name' => 'Test Term',
    ]);
    $term->save();

    // 2) Attempt to access edit form as anonymous.
    $this->drupalGet($term->toUrl('edit-form')->toString());

    // 3) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testUserWithoutPermissionCannotEditTerm(): void {
    // 1) Create vocabulary and term.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    $term = Term::create([
      'vid' => $vocab_id,
      'name' => 'Test Term',
    ]);
    $term->save();

    // 2) Create user without edit permission.
    $user = $this->createUser(['access content']);
    $this->drupalLogin($user);

    // 3) Attempt to access edit form.
    $this->drupalGet($term->toUrl('edit-form')->toString());

    // 4) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testUserCanDeleteTerm(): void {
    // 1) Create vocabulary and term.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    $term_name = 'Term to Delete ' . uniqid();
    $term = Term::create([
      'vid' => $vocab_id,
      'name' => $term_name,
    ]);
    $term->save();
    $term_id = $term->id();

    // 2) Create user and login.
    $user = $this->createUser(['administer taxonomy']);
    $this->drupalLogin($user);

    // 3) Visit delete form.
    $this->drupalGet($term->toUrl('delete-form')->toString());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Are you sure you want to delete');

    // 4) Confirm deletion.
    $this->submitForm([], 'Delete');

    // 5) Verify success message.
    $this->assertSession()->pageTextContains('Deleted term');

    // 6) Verify term no longer exists.
    $term = Term::load($term_id);
    $this->assertNull($term);
  }

  public function testAnonymousCannotDeleteTerm(): void {
    // 1) Create vocabulary and term.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    $term = Term::create([
      'vid' => $vocab_id,
      'name' => 'Test Term',
    ]);
    $term->save();

    // 2) Attempt to access delete form as anonymous.
    $this->drupalGet($term->toUrl('delete-form')->toString());

    // 3) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

}
