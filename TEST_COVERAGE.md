# Automated Test Coverage Documentation

## Overview
This document outlines the comprehensive test coverage for Drupal content types using Drupal Test Traits (DTT) framework. The test suite covers two primary content types: **Article** and **Basic Page**.

**Total Test Files:** 14
**Total Test Methods:** 18
**Framework:** Drupal Test Traits (ExistingSiteBase)
**Test Type:** Functional/Integration Tests

---

## Test Coverage Summary

### Coverage Areas
- ✅ Anonymous user access (view permissions)
- ✅ Authenticated user access
- ✅ Unpublished content access control
- ✅ Permission-based access (bypass node access)
- ✅ Edit form access control
- ✅ Delete form access control
- ✅ Form field validation
- ✅ CRUD operations through UI
- ✅ HTTP status code validation (200, 403)
- ✅ Content visibility assertions

---

## Article Content Type Tests

### 1. ArticleAnonymousViewTest.php
**Purpose:** Verify anonymous users can view published articles

**Test Methods:**
- `testAnonymousCanSeePublishedArticleTitleAndBody()`

**Coverage:**
- Creates published article with title and body
- Verifies anonymous access returns HTTP 200
- Asserts title is visible on page
- Asserts body content is visible on page

**Permissions Tested:** None (anonymous access)

---

### 2. ArticleUnpublishedAccessTest.php
**Purpose:** Verify anonymous users cannot view unpublished articles

**Test Methods:**
- `testAnonymousCannotSeeUnpublishedArticle()`

**Coverage:**
- Creates unpublished article
- Verifies anonymous access returns HTTP 403 (Forbidden)
- Asserts title is NOT visible on page
- Asserts body content is NOT visible on page

**Permissions Tested:** Access control for unpublished content

---

### 3. ArticleAuthenticatedUserTest.php
**Purpose:** Verify authenticated users can view published articles

**Test Methods:**
- `testAuthenticatedUserCanSeePublishedArticle()`

**Coverage:**
- Creates authenticated user and logs in
- Creates published article
- Verifies authenticated access returns HTTP 200
- Asserts title and body are visible

**Permissions Tested:** Authenticated user read access

---

### 4. ArticleWithPermissionsTest.php
**Purpose:** Verify users with special permissions can view unpublished content

**Test Methods:**
- `testUserWithBypassNodeAccessCanSeeUnpublishedArticle()`

**Coverage:**
- Creates user with 'bypass node access' permission
- Creates unpublished article
- Verifies privileged user can access unpublished content (HTTP 200)
- Asserts content visibility for privileged users

**Permissions Tested:**
- `bypass node access`

---

### 5. ArticleEditAccessTest.php
**Purpose:** Verify edit form access control

**Test Methods:**
- `testAnonymousCannotEditArticle()`
- `testUserWithEditOwnArticleCanEditTheirArticle()`

**Coverage:**
- Verifies anonymous users cannot access edit form (HTTP 403)
- Verifies users with 'edit own article content' can edit their articles
- Asserts edit form loads with expected fields (title field)

**Permissions Tested:**
- `create article content`
- `edit own article content`

---

### 6. ArticleDeleteAccessTest.php
**Purpose:** Verify delete form access control

**Test Methods:**
- `testAnonymousCannotDeleteArticle()`
- `testUserWithDeletePermissionCanDeleteArticle()`

**Coverage:**
- Verifies anonymous users cannot access delete form (HTTP 403)
- Verifies users with delete permissions can access delete form
- Asserts delete confirmation page displays correctly
- Asserts 'Delete' button exists

**Permissions Tested:**
- `create article content`
- `delete any article content`

---

### 7. ArticleFieldValidationTest.php
**Purpose:** Verify form validation and submission workflows

**Test Methods:**
- `testArticleRequiresTitleField()`
- `testArticleAcceptsValidData()`

**Coverage:**
- Verifies title field is required (validation error)
- Tests successful article creation through web form
- Verifies form submission redirects to created article
- Asserts success message appears

**Permissions Tested:**
- `create article content`

**Form Fields Tested:**
- `title[0][value]` (required)
- `body[0][value]` (optional)

---

## Basic Page Content Type Tests

### 8. BasicPageAnonymousViewTest.php
**Purpose:** Verify anonymous users can view published basic pages

**Test Methods:**
- `testAnonymousCanSeePublishedBasicPageTitleAndBody()`

**Coverage:**
- Creates published basic page with title and body
- Verifies anonymous access returns HTTP 200
- Asserts title is visible on page
- Asserts body content is visible on page

**Permissions Tested:** None (anonymous access)

---

### 9. BasicPageUnpublishedAccessTest.php
**Purpose:** Verify anonymous users cannot view unpublished basic pages

**Test Methods:**
- `testAnonymousCannotSeeUnpublishedBasicPage()`

**Coverage:**
- Creates unpublished basic page
- Verifies anonymous access returns HTTP 403 (Forbidden)
- Asserts title is NOT visible on page
- Asserts body content is NOT visible on page

**Permissions Tested:** Access control for unpublished content

---

### 10. BasicPageAuthenticatedUserTest.php
**Purpose:** Verify authenticated users can view published basic pages

**Test Methods:**
- `testAuthenticatedUserCanSeePublishedBasicPage()`

**Coverage:**
- Creates authenticated user and logs in
- Creates published basic page
- Verifies authenticated access returns HTTP 200
- Asserts title and body are visible

**Permissions Tested:** Authenticated user read access

---

### 11. BasicPageWithPermissionsTest.php
**Purpose:** Verify users with special permissions can view unpublished content

**Test Methods:**
- `testUserWithBypassNodeAccessCanSeeUnpublishedPage()`

**Coverage:**
- Creates user with 'bypass node access' permission
- Creates unpublished basic page
- Verifies privileged user can access unpublished content (HTTP 200)
- Asserts content visibility for privileged users

**Permissions Tested:**
- `bypass node access`

---

### 12. BasicPageEditAccessTest.php
**Purpose:** Verify edit form access control

**Test Methods:**
- `testAnonymousCannotEditBasicPage()`
- `testUserWithEditOwnPageCanEditTheirPage()`

**Coverage:**
- Verifies anonymous users cannot access edit form (HTTP 403)
- Verifies users with 'edit own page content' can edit their pages
- Asserts edit form loads with expected fields (title field)

**Permissions Tested:**
- `create page content`
- `edit own page content`

---

### 13. BasicPageDeleteAccessTest.php
**Purpose:** Verify delete form access control

**Test Methods:**
- `testAnonymousCannotDeleteBasicPage()`
- `testUserWithDeletePermissionCanDeletePage()`

**Coverage:**
- Verifies anonymous users cannot access delete form (HTTP 403)
- Verifies users with delete permissions can access delete form
- Asserts delete confirmation page displays correctly
- Asserts 'Delete' button exists

**Permissions Tested:**
- `create page content`
- `delete any page content`

---

### 14. BasicPageFieldValidationTest.php
**Purpose:** Verify form validation and submission workflows

**Test Methods:**
- `testBasicPageRequiresTitleField()`
- `testBasicPageAcceptsValidData()`

**Coverage:**
- Verifies title field is required (validation error)
- Tests successful page creation through web form
- Verifies form submission redirects to created page
- Asserts success message appears

**Permissions Tested:**
- `create page content`

**Form Fields Tested:**
- `title[0][value]` (required)
- `body[0][value]` (optional)

---

## Test Categories Matrix

| Category | Article Tests | Page Tests | Total |
|----------|--------------|------------|-------|
| View Access (Published) | 1 | 1 | 2 |
| View Access (Unpublished) | 1 | 1 | 2 |
| Authenticated Access | 1 | 1 | 2 |
| Permission-Based Access | 1 | 1 | 2 |
| Edit Access Control | 2 | 2 | 4 |
| Delete Access Control | 2 | 2 | 4 |
| Form Validation | 2 | 2 | 4 |
| **Total Test Methods** | **10** | **10** | **20** |

---

## Permissions Coverage

### Drupal Permissions Tested

| Permission | Purpose | Tests Using It |
|------------|---------|----------------|
| `bypass node access` | View all content regardless of status | ArticleWithPermissionsTest, BasicPageWithPermissionsTest |
| `create article content` | Create new articles | ArticleEditAccessTest, ArticleDeleteAccessTest, ArticleFieldValidationTest |
| `create page content` | Create new basic pages | BasicPageEditAccessTest, BasicPageDeleteAccessTest, BasicPageFieldValidationTest |
| `edit own article content` | Edit own articles | ArticleEditAccessTest |
| `edit own page content` | Edit own pages | BasicPageEditAccessTest |
| `delete any article content` | Delete any article | ArticleDeleteAccessTest |
| `delete any page content` | Delete any page | BasicPageDeleteAccessTest |

---

## HTTP Status Codes Tested

| Status Code | Meaning | Test Coverage |
|-------------|---------|---------------|
| 200 OK | Successful access | 10 tests |
| 403 Forbidden | Access denied | 6 tests |

---

## Common Test Patterns

### 1. Content Creation Pattern
```php
$node = $this->createNode([
  'type' => 'article' | 'page',
  'title' => $title,
  'body' => [
    'value' => $body_text,
    'format' => 'basic_html',
  ],
  'status' => NodeInterface::PUBLISHED | NodeInterface::NOT_PUBLISHED,
]);
```

### 2. User Creation and Login Pattern
```php
$user = $this->createUser(['permission1', 'permission2']);
$this->drupalLogin($user);
```

### 3. Navigation Pattern
```php
$this->drupalGet($node->toUrl()->toString());
$this->drupalGet($node->toUrl('edit-form')->toString());
$this->drupalGet($node->toUrl('delete-form')->toString());
```

### 4. Assertion Patterns
```php
// HTTP status
$this->assertSession()->statusCodeEquals(200);
$this->assertSession()->statusCodeEquals(403);

// Content visibility
$this->assertSession()->pageTextContains($text);
$this->assertSession()->pageTextNotContains($text);

// Form elements
$this->assertSession()->fieldExists('title[0][value]');
$this->assertSession()->buttonExists('Delete');
```

---

## Test Execution

### Running All Tests
```bash
vendor/bin/phpunit tests/src/ExistingSite/
```

### Running Article Tests Only
```bash
vendor/bin/phpunit tests/src/ExistingSite/Article*
```

### Running Basic Page Tests Only
```bash
vendor/bin/phpunit tests/src/ExistingSite/BasicPage*
```

### Running Specific Test File
```bash
vendor/bin/phpunit tests/src/ExistingSite/ArticleAnonymousViewTest.php
```

### Running Specific Test Method
```bash
vendor/bin/phpunit --filter testAnonymousCanSeePublishedArticleTitleAndBody
```

---

## Test Data Patterns

### Unique Identifiers
All tests use `uniqid()` to generate unique titles and content:
- Prevents collisions in existing site testing
- Allows multiple test runs without cleanup
- Makes debugging easier with identifiable test data

Example:
```php
$title = 'DTT Article ' . uniqid();
$body_text = 'Body content ' . uniqid();
```

---

## Coverage Gaps and Future Enhancements

### Current Gaps
- ❌ File/image field testing
- ❌ Taxonomy term association
- ❌ Multi-value field testing
- ❌ Revision/moderation workflow testing
- ❌ Translation/multilingual testing
- ❌ View mode rendering tests
- ❌ Comment functionality testing
- ❌ URL alias/path testing

### Potential Enhancements
- Add tests for custom fields (if present)
- Add tests for content moderation workflows
- Add tests for revision history
- Add performance/load testing
- Add accessibility testing
- Add cross-browser testing
- Add API endpoint testing (JSON:API, REST)

---

## Best Practices Followed

1. ✅ **Descriptive Test Names**: Each test method clearly describes what it tests
2. ✅ **Single Responsibility**: Each test method tests one specific scenario
3. ✅ **AAA Pattern**: Arrange, Act, Assert structure in all tests
4. ✅ **Explicit Assertions**: Clear expectations for each test
5. ✅ **Unique Test Data**: Using `uniqid()` to prevent data conflicts
6. ✅ **Proper Cleanup**: DTT handles cleanup automatically
7. ✅ **Comments**: Step-by-step comments in test methods
8. ✅ **Type Hints**: Using `@var` annotations for better IDE support
9. ✅ **Consistent Naming**: Following Drupal coding standards

---

## Dependencies

### Required Packages
- `weitzman/drupal-test-traits` - DTT framework
- `phpunit/phpunit` - Testing framework
- `drupal/core` - Drupal core (version depends on site)

### Configuration
Tests require proper PHPUnit configuration (phpunit.xml) with:
- Bootstrap file
- Environment variables (DB connection, site URL, etc.)
- Test suite definitions

---

## Maintenance Notes

### When to Update Tests
- When content type configuration changes
- When field definitions are modified
- When permissions are restructured
- When access control policies change
- When form validation rules change

### Regular Review
- Run full test suite before deployments
- Review failed tests immediately
- Update tests when features are added/removed
- Keep test data patterns consistent

---

## Test Environment Requirements

### Existing Site Setup
- Must have 'article' content type configured
- Must have 'page' content type configured
- Must have 'basic_html' text format available
- Must have standard Drupal permissions system
- Must have user authentication enabled

### Performance Considerations
- ExistingSite tests run against actual database
- Tests create real nodes (ensure cleanup)
- Tests may be slower than unit tests
- Consider running in isolated environment

---

## Summary

This comprehensive test suite provides strong coverage for core Drupal content type functionality across both Article and Basic Page content types. The tests verify:

- **Security**: Access control works correctly for different user roles
- **Functionality**: CRUD operations work as expected
- **Validation**: Form validation catches errors appropriately
- **User Experience**: Success/error messages display correctly
- **Permissions**: Drupal permission system functions properly

**Test Health:** 18 test methods across 14 test files
**Code Coverage:** View, Create, Edit, Delete operations
**User Coverage:** Anonymous, Authenticated, Privileged users
**Status Coverage:** Published and Unpublished content

---

*Last Updated: 2026-01-09*
*Framework: Drupal Test Traits (ExistingSiteBase)*
*Testing Approach: Functional/Integration Testing*
