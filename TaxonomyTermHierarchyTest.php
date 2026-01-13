<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class TaxonomyTermHierarchyTest extends ExistingSiteBase {

  public function testCreateChildTerm(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    // 2) Create parent term.
    $parent_name = 'Parent Term ' . uniqid();
    $parent = Term::create([
      'vid' => $vocab_id,
      'name' => $parent_name,
    ]);
    $parent->save();

    // 3) Create child term.
    $child_name = 'Child Term ' . uniqid();
    $child = Term::create([
      'vid' => $vocab_id,
      'name' => $child_name,
      'parent' => [$parent->id()],
    ]);
    $child->save();

    // 4) Reload and verify parent-child relationship.
    $child = Term::load($child->id());
    $parent_ids = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadParents($child->id());

    $this->assertNotEmpty($parent_ids);
    $this->assertArrayHasKey($parent->id(), $parent_ids);
  }

  public function testNestedTermHierarchy(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    // 2) Create grandparent term.
    $grandparent = Term::create([
      'vid' => $vocab_id,
      'name' => 'Grandparent ' . uniqid(),
    ]);
    $grandparent->save();

    // 3) Create parent term.
    $parent = Term::create([
      'vid' => $vocab_id,
      'name' => 'Parent ' . uniqid(),
      'parent' => [$grandparent->id()],
    ]);
    $parent->save();

    // 4) Create child term.
    $child = Term::create([
      'vid' => $vocab_id,
      'name' => 'Child ' . uniqid(),
      'parent' => [$parent->id()],
    ]);
    $child->save();

    // 5) Verify all relationships.
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

    $child_parents = $storage->loadParents($child->id());
    $this->assertArrayHasKey($parent->id(), $child_parents);

    $parent_parents = $storage->loadParents($parent->id());
    $this->assertArrayHasKey($grandparent->id(), $parent_parents);
  }

  public function testTermHierarchyDisplay(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    // 2) Create parent and child.
    $parent_name = 'Parent ' . uniqid();
    $parent = Term::create([
      'vid' => $vocab_id,
      'name' => $parent_name,
    ]);
    $parent->save();

    $child_name = 'Child ' . uniqid();
    $child = Term::create([
      'vid' => $vocab_id,
      'name' => $child_name,
      'parent' => [$parent->id()],
    ]);
    $child->save();

    // 3) Create user and visit term listing.
    $user = $this->createUser(['administer taxonomy']);
    $this->drupalLogin($user);

    $this->drupalGet("/admin/structure/taxonomy/manage/{$vocab_id}/overview");
    $this->assertSession()->statusCodeEquals(200);

    // 4) Verify both terms appear.
    $this->assertSession()->pageTextContains($parent_name);
    $this->assertSession()->pageTextContains($child_name);
  }

  public function testMoveTermInHierarchy(): void {
    // 1) Create vocabulary.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    // 2) Create two parent terms.
    $parent1 = Term::create([
      'vid' => $vocab_id,
      'name' => 'Parent 1 ' . uniqid(),
    ]);
    $parent1->save();

    $parent2 = Term::create([
      'vid' => $vocab_id,
      'name' => 'Parent 2 ' . uniqid(),
    ]);
    $parent2->save();

    // 3) Create child under parent 1.
    $child = Term::create([
      'vid' => $vocab_id,
      'name' => 'Child ' . uniqid(),
      'parent' => [$parent1->id()],
    ]);
    $child->save();

    // 4) Verify initial relationship.
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $parents = $storage->loadParents($child->id());
    $this->assertArrayHasKey($parent1->id(), $parents);

    // 5) Move child to parent 2.
    $child->parent = [$parent2->id()];
    $child->save();

    // 6) Verify new relationship.
    $parents = $storage->loadParents($child->id());
    $this->assertArrayHasKey($parent2->id(), $parents);
    $this->assertArrayNotHasKey($parent1->id(), $parents);
  }

}
