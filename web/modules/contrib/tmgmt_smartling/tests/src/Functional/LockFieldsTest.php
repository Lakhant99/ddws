<?php

namespace Drupal\Tests\tmgmt_smartling\Functional;

/**
 * Lock fields tests.
 *
 * @group tmgmt_smartling
 */
class LockFieldsTest extends SmartlingTestBase {

  /**
   * Test field locking.
   *
   * 1. Translate node into FR and DE.
   * 2. Lock title for FR.
   * 3. Lock body for DE.
   * 4. Download updated translations.
   * 5. FR: only body updated.
   * 6. DE: only title updated.
   * 7. Unlock all fields for FR.
   * 8. Unlock all fields for DE.
   * 9. Download updated translations.
   * 10. FR and DE are updated as well.
   */
  public function testFieldLockingLogic() {
    if (!empty($this->smartlingPluginProviderSettings)) {
      $this->drupalPostForm('/admin/tmgmt/translators/manage/smartling', [
        'auto_accept' => $this->smartlingPluginProviderSettings['auto_accept'],
        'settings[project_id]' => $this->smartlingPluginProviderSettings['settings[project_id]'],
        'settings[user_id]' => $this->smartlingPluginProviderSettings['settings[user_id]'],
        'settings[token_secret]' => $this->smartlingPluginProviderSettings['settings[token_secret]'],
        'settings[contextUsername]' => $this->smartlingPluginProviderSettings['settings[contextUsername]'],
        'settings[retrieval_type]' => $this->smartlingPluginProviderSettings['settings[retrieval_type]'],
      ], 'Save');

      $this->drupalPostForm('admin/tmgmt/extension-settings', [
        'do_track_changes' => FALSE,
      ], t('Save configuration'));

      $this->drupalPostForm(
        "node/add/translatable_node", [
          'title[0][value]' => 'test title',
          'body[0][value]' => 'test body',
          'langcode[0][value]' => 'en',
        ], t('Save')
      );

      $nid = 4;

      // 1. Translate node into FR and DE.
      $this->drupalPostForm('/admin/tmgmt/sources', [
        "items[$nid]" => $nid,
      ], t('Request translation'));

      $this->drupalPostForm(NULL, [
        'target_language' => 'fr',
        'settings[create_new_job_tab][name]' => 'Drupal TMGMT connector test FR ' . mt_rand(),
        'settings[create_new_job_tab][due_date][date]' => '2020-12-12',
        'settings[create_new_job_tab][due_date][time]' => '12:12',
        'settings[create_new_job_tab][authorize]' => TRUE,
        'settings[smartling_users_time_zone]' => 'Europe/Kiev',
      ], t('Submit to provider'));

      $this->drupalPostForm('/admin/tmgmt/sources', [
        "items[$nid]" => $nid,
      ], t('Request translation'));

      $this->drupalPostForm(NULL, [
        'target_language' => 'de',
        'settings[create_new_job_tab][name]' => 'Drupal TMGMT connector test DE ' . mt_rand(),
        'settings[create_new_job_tab][due_date][date]' => '2020-12-12',
        'settings[create_new_job_tab][due_date][time]' => '12:12',
        'settings[create_new_job_tab][authorize]' => TRUE,
        'settings[smartling_users_time_zone]' => 'Europe/Kiev',
      ], t('Submit to provider'));

      $this->drupalPostForm("admin/tmgmt/jobs/1", [], t('Download'));
      $this->drupalPostForm("admin/tmgmt/jobs/2", [], t('Download'));

      $this->drupalGet("node/$nid/edit");
      $this->assertNoText('Smartling management');
      $this->assertNoText('Lock fields');
      $this->assertResponse(200);

      $this->drupalGet("fr/node/$nid/edit");
      $this->assertText('Smartling management');
      $this->assertText('Lock fields');
      $this->assertNoFieldChecked('locked_fields[title]');
      $this->assertNoFieldChecked('locked_fields[body]');
      $this->assertResponse(200);

      $this->drupalGet("de/node/$nid/edit");
      $this->assertText('Smartling management');
      $this->assertText('Lock fields');
      $this->assertNoFieldChecked('locked_fields[title]');
      $this->assertNoFieldChecked('locked_fields[body]');
      $this->assertResponse(200);

      // 2. Lock title for FR.
      $this->drupalPostForm(
        "fr/node/$nid/edit", [
          'title[0][value]' => 'Manually edited title',
          'locked_fields[title]' => 'title',
        ], t('Save (this translation)')
      );

      $this->drupalGet("fr/node/$nid/edit");
      $this->assertFieldChecked('locked_fields[title]');
      $this->assertNoFieldChecked('locked_fields[body]');
      $this->assertResponse(200);

      // 3. Lock body for DE.
      $this->drupalPostForm(
        "de/node/$nid/edit", [
          'body[0][value]' => 'Manually edited body',
          'locked_fields[body]' => 'body',
        ], t('Save (this translation)')
      );

      $this->drupalGet("de/node/$nid/edit");
      $this->assertNoFieldChecked('locked_fields[title]');
      $this->assertFieldChecked('locked_fields[body]');
      $this->assertResponse(200);

      // 4. Download updated translations.
      $this->drupalPostForm(
        "node/$nid/edit", [
          'title[0][value]' => 'test title new',
          'body[0][value]' => 'test body new',
        ], t('Save (this translation)')
      );

      $this->drupalPostForm('/admin/tmgmt/sources', [
        "items[$nid]" => $nid,
      ], t('Request translation'));

      $this->drupalPostForm(NULL, [
        'target_language' => 'fr',
        'settings[create_new_job_tab][name]' => 'Drupal TMGMT connector test FR ' . mt_rand(),
        'settings[create_new_job_tab][due_date][date]' => '2020-12-12',
        'settings[create_new_job_tab][due_date][time]' => '12:12',
        'settings[create_new_job_tab][authorize]' => TRUE,
        'settings[smartling_users_time_zone]' => 'Europe/Kiev',
      ], t('Submit to provider'));

      $this->drupalPostForm('/admin/tmgmt/sources', [
        "items[$nid]" => $nid,
      ], t('Request translation'));

      $this->drupalPostForm(NULL, [
        'target_language' => 'de',
        'settings[create_new_job_tab][name]' => 'Drupal TMGMT connector test DE ' . mt_rand(),
        'settings[create_new_job_tab][due_date][date]' => '2020-12-12',
        'settings[create_new_job_tab][due_date][time]' => '12:12',
        'settings[create_new_job_tab][authorize]' => TRUE,
        'settings[smartling_users_time_zone]' => 'Europe/Kiev',
      ], t('Submit to provider'));

      $this->drupalPostForm("admin/tmgmt/jobs/3", [], t('Download'));
      $this->drupalPostForm("admin/tmgmt/jobs/4", [], t('Download'));

      // 5. FR: only body updated.
      $this->drupalGet("fr/node/$nid");
      $this->assertResponse(200);
      $this->assertText('Manually edited title');
      $this->assertText('[t~ést ~bódý ~ñéw]');

      // 6. DE: only title updated.
      $this->drupalGet("de/node/$nid");
      $this->assertResponse(200);
      $this->assertText('[t~ést t~ítlé ~ñéw]');
      $this->assertText('Manually edited body');

      // 7. Unlock all fields for FR.
      $this->drupalPostForm(
        "fr/node/$nid/edit", [
          'locked_fields[title]' => FALSE,
          'locked_fields[body]' => FALSE,
        ], t('Save (this translation)')
      );

      // 8. Unlock all fields for DE.
      $this->drupalPostForm(
        "de/node/$nid/edit", [
            'locked_fields[title]' => FALSE,
            'locked_fields[body]' => FALSE,
          ], t('Save (this translation)')
      );

      // 9. Download updated translations.
      $this->drupalPostForm(
        "node/$nid/edit", [
          'title[0][value]' => 'test title brand new',
          'body[0][value]' => 'test body brand new',
        ], t('Save (this translation)')
      );

      $this->drupalPostForm('/admin/tmgmt/sources', [
        "items[$nid]" => $nid,
      ], t('Request translation'));

      $this->drupalPostForm(NULL, [
        'target_language' => 'fr',
        'settings[create_new_job_tab][name]' => 'Drupal TMGMT connector test FR ' . mt_rand(),
        'settings[create_new_job_tab][due_date][date]' => '2020-12-12',
        'settings[create_new_job_tab][due_date][time]' => '12:12',
        'settings[create_new_job_tab][authorize]' => TRUE,
        'settings[smartling_users_time_zone]' => 'Europe/Kiev',
      ], t('Submit to provider'));

      $this->drupalPostForm('/admin/tmgmt/sources', [
        "items[$nid]" => $nid,
      ], t('Request translation'));

      $this->drupalPostForm(NULL, [
        'target_language' => 'de',
        'settings[create_new_job_tab][name]' => 'Drupal TMGMT connector test DE ' . mt_rand(),
        'settings[create_new_job_tab][due_date][date]' => '2020-12-12',
        'settings[create_new_job_tab][due_date][time]' => '12:12',
        'settings[create_new_job_tab][authorize]' => TRUE,
        'settings[smartling_users_time_zone]' => 'Europe/Kiev',
      ], t('Submit to provider'));

      $this->drupalPostForm("admin/tmgmt/jobs/5", [], t('Download'));
      $this->drupalPostForm("admin/tmgmt/jobs/6", [], t('Download'));

      // 10. FR and DE are updated as well.
      $this->drupalGet("fr/node/$nid");
      $this->assertResponse(200);
      $this->assertText('[t~ést t~ítlé ~bráñ~d ñéw]');
      $this->assertText('[t~ést b~ódý b~ráñd ~ñéw]');

      $this->drupalGet("de/node/$nid");
      $this->assertResponse(200);
      $this->assertText('[t~ést t~ítlé ~bráñ~d ñéw]');
      $this->assertText('[t~ést b~ódý b~ráñd ~ñéw]');
    }
    else {
      $this->fail("Smartling settings file for simpletests not found.");
    }
  }
}
