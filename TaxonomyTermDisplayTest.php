<?php

declare(strict_types=1);

namespace Drupal\Tests\MySite\ExistingSite;

use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use weitzman\DrupalTestTraits\ExistingSiteBase;

final class TaxonomyTermDisplayTest extends ExistingSiteBase {

  public function testTermDisplaysOnArticle(): void {
    // 1) Create vocabulary and term.
    $vocab_id = 'tags';
    $vocabulary = Vocabulary::load($vocab_id);
    if (!$vocabulary) {
      $vocabulary = Vocabulary::create([
        'vid' => $vocab_id,
        'name' => 'Tags',
      ]);
      $vocabulary->save();
    }

    $tag_name = 'Display Tag ' . uniqid();
    $tag = Term::create([
      'vid' => $vocab_id,
      'name' => $tag_name,
    ]);
    $tag->save();

    // 2) Create article with tag.
    $title = 'Article for Display ' . uniqid();
    $node = $this->createNode([
      'type' => 'article',
      'title' => $title,
      'field_tags' => [['target_id' => $tag->id()]],
      'status' => 1,
    ]);

    // 3) Visit article page.
    $this->drupalGet($node->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);

    // 4) Verify tag displays and is a link.
    $this->assertSession()->pageTextContains($tag_name);
    $this->assertSession()->linkExists($tag_name);
  }

  public function testTermLinkToTermPage(): void {
    // 1) Create vocabulary and term.
    $vocab_id = 'tags';
    $vocabulary = Vocabulary::load($vocab_id);
    if (!$vocabulary) {
      $vocabulary = Vocabulary::create([
        'vid' => $vocab_id,
        'name' => 'Tags',
      ]);
      $vocabulary->save();
    }

    $tag_name = 'Link Tag ' . uniqid();
    $tag = Term::create([
      'vid' => $vocab_id,
      'name' => $tag_name,
    ]);
    $tag->save();

    // 2) Create article with tag.
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'Article ' . uniqid(),
      'field_tags' => [['target_id' => $tag->id()]],
      'status' => 1,
    ]);

    // 3) Visit article and click tag link.
    $this->drupalGet($node->toUrl()->toString());
    $this->clickLink($tag_name);

    // 4) Verify we're on term page.
    $this->assertSession()->statusCodeEquals(200);
    $current_url = $this->getSession()->getCurrentUrl();
    $this->assertStringContainsString('/taxonomy/term/' . $tag->id(), $current_url);
  }

  public function testTermPageShowsReferencingContent(): void {
    // 1) Create vocabulary and term.
    $vocab_id = 'tags';
    $vocabulary = Vocabulary::load($vocab_id);
    if (!$vocabulary) {
      $vocabulary = Vocabulary::create([
        'vid' => $vocab_id,
        'name' => 'Tags',
      ]);
      $vocabulary->save();
    }

    $tag_name = 'Reference Tag ' . uniqid();
    $tag = Term::create([
      'vid' => $vocab_id,
      'name' => $tag_name,
    ]);
    $tag->save();

    // 2) Create multiple articles with this tag.
    $title1 = 'Article One ' . uniqid();
    $node1 = $this->createNode([
      'type' => 'article',
      'title' => $title1,
      'field_tags' => [['target_id' => $tag->id()]],
      'status' => 1,
    ]);

    $title2 = 'Article Two ' . uniqid();
    $node2 = $this->createNode([
      'type' => 'article',
      'title' => $title2,
      'field_tags' => [['target_id' => $tag->id()]],
      'status' => 1,
    ]);

    $title3 = 'Article Three ' . uniqid();
    $node3 = $this->createNode([
      'type' => 'article',
      'title' => $title3,
      'field_tags' => [['target_id' => $tag->id()]],
      'status' => 1,
    ]);

    // 3) Visit term page.
    $this->drupalGet('/taxonomy/term/' . $tag->id());
    $this->assertSession()->statusCodeEquals(200);

    // 4) Verify all articles are listed.
    $this->assertSession()->pageTextContains($title1);
    $this->assertSession()->pageTextContains($title2);
    $this->assertSession()->pageTextContains($title3);
  }

  public function testTermPageWithNoContent(): void {
    // 1) Create vocabulary and term.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    $tag_name = 'Empty Tag ' . uniqid();
    $tag = Term::create([
      'vid' => $vocab_id,
      'name' => $tag_name,
    ]);
    $tag->save();

    // 2) Visit term page (no content references this term).
    $this->drupalGet('/taxonomy/term/' . $tag->id());

    // 3) Verify page loads successfully.
    $this->assertSession()->statusCodeEquals(200);

    // 4) Verify term name displays.
    $this->assertSession()->pageTextContains($tag_name);
  }

  public function testAnonymousCanViewTermPage(): void {
    // 1) Create vocabulary and term.
    $vocab_id = 'test_vocab_' . uniqid();
    $vocabulary = Vocabulary::create([
      'vid' => $vocab_id,
      'name' => 'Test Vocabulary',
    ]);
    $vocabulary->save();

    $tag_name = 'Anonymous Tag ' . uniqid();
    $tag = Term::create([
      'vid' => $vocab_id,
      'name' => $tag_name,
    ]);
    $tag->save();

    // 2) Visit term page as anonymous user.
    $this->drupalGet('/taxonomy/term/' . $tag->id());

    // 3) Verify HTTP 200.
    $this->assertSession()->statusCodeEquals(200);

    // 4) Verify term name displays.
    $this->assertSession()->pageTextContains($tag_name);
  }

  public function testTermRenderingFormat(): void {
    // 1) Create vocabulary and term.
    $vocab_id = 'tags';
    $vocabulary = Vocabulary::load($vocab_id);
    if (!$vocabulary) {
      $vocabulary = Vocabulary::create([
        'vid' => $vocab_id,
        'name' => 'Tags',
      ]);
      $vocabulary->save();
    }

    $tag_name = 'Format Tag ' . uniqid();
    $tag = Term::create([
      'vid' => $vocab_id,
      'name' => $tag_name,
    ]);
    $tag->save();

    // 2) Create article with tag.
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'Article ' . uniqid(),
      'field_tags' => [['target_id' => $tag->id()]],
      'status' => 1,
    ]);

    // 3) Visit article page.
    $this->drupalGet($node->toUrl()->toString());
    $this->assertSession()->statusCodeEquals(200);

    // 4) Verify link element exists with proper href.
    $page = $this->getSession()->getPage();
    $link = $page->findLink($tag_name);
    $this->assertNotNull($link);

    $href = $link->getAttribute('href');
    $this->assertStringContainsString('/taxonomy/term/' . $tag->id(), $href);
  }

}
