<?php

namespace Drupal\Tests\tmgmt_extension_suit\Functional;

/**
 * Class CheckEntityChangesTest.
 *
 * @group tmgmt_extension_suit
 */
class CheckEntityChangesTest extends TmgmtExtensionSuitTestBase {

  /**
   * Test "Track changes of the translatable entities" feature is turned on.
   */
  public function testTrackTranslatableEntityChanges() {
    $this->requestTranslation([1], 'fr', 1);

    // Submit node edit form without changes.
    // Expectations:
    // 1. Hash is not changed.
    // 2. Job is not added to upload queue.
    $oldHash = $this->getNodeHash(1, 1);
    $this->drupalPostForm("node/1/edit", [], t('Save'));
    $newHash = $this->getNodeHash(1, 1);
    $this->assertEqual($oldHash, $newHash);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 0);

    // Submit node edit form with updated title.
    // Expectations:
    // 1. Hash is changed.
    // 2. Job is added to upload queue.
    $this->drupalPostForm("node/1/edit", [
      'title[0][value]' => 'New node test title',
    ], t('Save'));
    $newHash = $this->getNodeHash(1, 1);
    $this->assertNotEqual($oldHash, $newHash);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 1);
  }

  /**
   * Test "Track changes of the translatable entities" feature is turned on.
   *
   * Works only for plugins which implement ExtendedTranslatorPluginInterface
   * interface.
   */
  public function testTrackTranslatableEntityChangesWorksOnlyForExtendedPlugins() {
    $this->requestTranslation([1], 'fr', 1, 'local');

    // Submit node edit form without changes.
    // Expectations:
    // 1. Hash is not changed.
    // 2. Job is not added to upload queue.
    $oldHash = $this->getNodeHash(1, 1);
    $this->drupalPostForm("node/1/edit", [], t('Save'));
    $newHash = $this->getNodeHash(1, 1);
    $this->assertEqual($oldHash, $newHash);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 0);

    // Submit node edit form with updated title.
    // Expectations:
    // 1. Hash is not changed.
    // 2. Job is not added to upload queue.
    $this->drupalPostForm("node/1/edit", [
      'title[0][value]' => 'New node test title',
    ], t('Save'));
    $newHash = $this->getNodeHash(1, 1);
    $this->assertEqual($oldHash, $newHash);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 0);
  }

  /**
   * Test "Track changes of the translatable entities" feature is turned off.
   */
  public function testDoNotTrackTranslatableEntityChanges() {
    $this->requestTranslation([1], 'fr', 1);

    // Disable tracking feature.
    $this->drupalPostForm('admin/tmgmt/extension-settings', [
      'do_track_changes' => FALSE,
    ], t('Save configuration'));

    // Submit node edit form without changes.
    // Expectations:
    // 1. Hash is not changed.
    // 2. Job is not added to upload queue.
    $oldHash = $this->getNodeHash(1, 1);
    $this->drupalPostForm("node/1/edit", [], t('Save'));
    $newHash = $this->getNodeHash(1, 1);
    $this->assertEqual($oldHash, $newHash);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 0);

    // Submit node edit form with updated title.
    // Expectations:
    // 1. Hash is not changed.
    // 2. Job is not added to upload queue.
    $this->drupalPostForm("node/1/edit", [
      'title[0][value]' => 'New node test title',
    ], t('Save'));
    $newHash = $this->getNodeHash(1, 1);
    $this->assertEqual($oldHash, $newHash);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 0);
  }

  /**
   * Test "Track changes of the translatable entities" feature is turned on.
   *
   * But languages are not selected.
   */
  public function testDoNotTrackTranslatableEntityChangesIfNoLanguagesSelected() {
    $this->requestTranslation([1], 'fr', 1);

    // Disable tracking feature.
    $this->drupalPostForm('admin/tmgmt/extension-settings', [
      'do_track_changes' => TRUE,
      'tes_local_test_de' => 0,
      'tes_local_test_fr' => 0,
    ], t('Save configuration'));

    // Submit node edit form without changes.
    // Expectations:
    // 1. Hash is not changed.
    // 2. Job is not added to upload queue.
    $oldHash = $this->getNodeHash(1, 1);
    $this->drupalPostForm("node/1/edit", [], t('Save'));
    $newHash = $this->getNodeHash(1, 1);
    $this->assertEqual($oldHash, $newHash);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 0);

    // Submit node edit form with updated title.
    // Expectations:
    // 1. Hash is not changed.
    // 2. Job is not added to upload queue.
    $this->drupalPostForm("node/1/edit", [
      'title[0][value]' => 'New node test title',
    ], t('Save'));
    $newHash = $this->getNodeHash(1, 1);
    $this->assertEqual($oldHash, $newHash);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 0);
  }

  /**
   * Test "Track changes of the translatable entities" feature is turned on.
   *
   * All languages selected.
   */
  public function testTrackTranslatableEntityChangesAllLanguagesSelected() {
    $this->requestTranslation([1], 'fr', 1);
    $this->requestTranslation([1], 'de', 2);

    // Submit node edit form without changes.
    // Expectations:
    // 1. Hash is not changed.
    // 2. Job is not added to upload queue.
    $oldHash1 = $this->getNodeHash(1, 1);
    $oldHash2 = $this->getNodeHash(1, 2);
    $this->drupalPostForm("node/1/edit", [], t('Save'));
    $newHash1 = $this->getNodeHash(1, 1);
    $newHash2 = $this->getNodeHash(1, 2);
    $this->assertEqual($oldHash1, $newHash1);
    $this->assertEqual($oldHash2, $newHash2);

    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 0);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 2), 0);

    // Submit node edit form with updated title.
    // Expectations:
    // 1. Hash is changed.
    // 2. Jobs are added to upload queue.
    $this->drupalPostForm("node/1/edit", [
      'title[0][value]' => 'New node test title',
    ], t('Save'));
    $newHash1 = $this->getNodeHash(1, 1);
    $newHash2 = $this->getNodeHash(1, 2);
    $this->assertNotEqual($oldHash1, $newHash1);
    $this->assertNotEqual($oldHash2, $newHash2);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 1);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 2), 1);
  }

  /**
   * Test "Track changes of the translatable entities" feature is turned on.
   *
   * Not all languages selected.
   */
  public function testTrackTranslatableEntityChangesNotAllLanguagesSelected() {
    $this->requestTranslation([1], 'fr', 1);
    $this->requestTranslation([1], 'de', 2);

    $this->drupalPostForm('admin/tmgmt/extension-settings', [
      'do_track_changes' => TRUE,
      'tes_local_test_de' => 0,
      'tes_local_test_fr' => 1,
    ], t('Save configuration'));

    // Submit node edit form without changes.
    // Expectations:
    // 1. Hash is not changed.
    // 2. Job is not added to upload queue.
    $oldHash1 = $this->getNodeHash(1, 1);
    $oldHash2 = $this->getNodeHash(1, 2);
    $this->drupalPostForm("node/1/edit", [], t('Save'));
    $newHash1 = $this->getNodeHash(1, 1);
    $newHash2 = $this->getNodeHash(1, 2);
    $this->assertEqual($oldHash1, $newHash1);
    $this->assertEqual($oldHash2, $newHash2);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 0);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 2), 0);

    // Submit node edit form with updated title.
    // Expectations:
    // 1. Hash is not changed.
    // 2. Job is not added to upload queue.
    $this->drupalPostForm("node/1/edit", [
      'title[0][value]' => 'New node test title',
    ], t('Save'));
    $newHash1 = $this->getNodeHash(1, 1);
    $newHash2 = $this->getNodeHash(1, 2);
    $this->assertNotEqual($oldHash1, $newHash1);
    $this->assertEqual($oldHash2, $newHash2);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 1), 1);
    $this->assertEqual($this->isItemAddedToQueue('tmgmt_extension_suit_upload', 2), 0);
  }

}
