<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class TaxonomyTermCreateTest extends ExistingSiteBase {

  public function testUserCanCreateTerm(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    // 2) Create user with permission.
    $user = $this->createUser([
      'administer taxonomy',
      "create terms in {$vocab_id}",
    ]);
    $this->drupalLogin($user);

    // 3) Visit term creation form.
    $this->drupalGet("/admin/structure/taxonomy/manage/{$vocab_id}/add");
    $this->assertSession()->statusCodeEquals(200);

    // 4) Fill in term details.
    $term_name = 'Test Term ' . uniqid();

    $this->submitForm([
      'name[0][value]' => $term_name,
      'description[0][value]' => 'Test term description',
    ], 'Save');

    // 5) Verify success.
    $this->assertSession()->pageTextContains('Created new term');
    $this->assertSession()->pageTextContains($term_name);
  }

  public function testAnonymousCannotCreateTerm(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    // 2) Attempt to access term creation form as anonymous.
    $this->drupalGet("/admin/structure/taxonomy/manage/{$vocab_id}/add");

    // 3) Should get access denied.
    $this->assertSession()->statusCodeEquals(403);
  }

  public function testTermRequiresName(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    // 2) Create user and login.
    $user = $this->createUser(['administer taxonomy']);
    $this->drupalLogin($user);

    // 3) Visit term creation form.
    $this->drupalGet("/admin/structure/taxonomy/manage/{$vocab_id}/add");

    // 4) Submit without name.
    $this->submitForm([
      'description[0][value]' => 'Description without name',
    ], 'Save');

    // 5) Verify validation error.
    $this->assertSession()->pageTextContains('Name field is required');
  }

  public function testTermWithDescription(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    // 2) Create term with description.
    $term_name = 'Term with Description ' . uniqid();
    $term_description = 'Detailed term description ' . uniqid();

    $term = Term::create([
      'vid' => $vocab_id,
      'name' => $term_name,
      'description' => [
        'value' => $term_description,
        'format' => 'basic_html',
      ],
    ]);
    $term->save();

    // 3) Reload and verify.
    $term = Term::load($term->id());
    $this->assertEquals($term_name, $term->getName());
    $this->assertEquals($term_description, $term->getDescription());
  }

  public function testCreateMultipleTerms(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    // 2) Create multiple terms.
    $term1_name = 'Term Alpha ' . uniqid();
    $term2_name = 'Term Beta ' . uniqid();
    $term3_name = 'Term Gamma ' . uniqid();

    $term1 = Term::create(['vid' => $vocab_id, 'name' => $term1_name]);
    $term1->save();

    $term2 = Term::create(['vid' => $vocab_id, 'name' => $term2_name]);
    $term2->save();

    $term3 = Term::create(['vid' => $vocab_id, 'name' => $term3_name]);
    $term3->save();

    // 3) Create user and verify all appear in listing.
    $user = $this->createUser(['administer taxonomy']);
    $this->drupalLogin($user);

    $this->drupalGet("/admin/structure/taxonomy/manage/{$vocab_id}/overview");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($term1_name);
    $this->assertSession()->pageTextContains($term2_name);
    $this->assertSession()->pageTextContains($term3_name);
  }

}
