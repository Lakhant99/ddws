<?php

/**
 * @file
 * A migration to import program nodes.
 *
 */

class MigratePrograms extends Migration {
  public function __construct($arguments) {
    parent::__construct($arguments);
    $this->description = t('Import programs from a source CSV file.');
    // 1. Tell migrate how to identify the content.
    // 2. Tell migrate about the source.
    // 3. Tell migrate about the destination.
    // 4. Map the source to the destination.
    $this->map = new MigrateSQLMap(
    $this->machineName,
      array(
        'id' => array(
          'type' => 'int',
          'not null' => TRUE,
        ),
      ),
      MigrateDestinationNode::getKeySchema()
    );
    $this->destination = new MigrateDestinationNode(
      'program',
      MigrateDestinationNode::options('und', 'full_html')
    );

    // We need to tell migrate about the numeric index of each field:
    $csvcolumns = array(
      0 => array('id', 'ID'),
      1 => array('title', 'Title'),
      2 => array('body', 'Body'),
      3 => array('program_level', 'Program Level'),
      4 => array('program_type', 'Program Type'),
      5 => array('program_image', 'Program Image'),
      6 => array('program_image_alt', 'Program Image Alt'),
      7 => array('tags', 'Tags'),
    );
    $this->source = new MigrateSourceCSV(
      drupal_get_path('module', 'migrate_programs') . '/programs.csv',
      $csvcolumns,
      array('header_rows' => 1)
    );

    // Map the destination field to the source column.
    $this->addFieldMapping('title', 'title');
    $this->addFieldMapping('body', 'body');
    $this->addFieldMapping('uid')->defaultValue('1');
    $this->addFieldMapping('field_program_level', 'program_level');
    $this->addFieldMapping('field_program_type', 'program_type');
    $this->addFieldMapping('field_program_image', 'program_image');
    $this->addFieldMapping('field_program_image:source_dir')
      ->defaultValue(drupal_get_path('module', 'migrate_programs') . '/program-images');
    $this->addFieldMapping('field_program_image:alt', 'program_image_alt');
    $this->addFieldMapping('field_tags', 'tags');
    $this->addFieldMapping('field_tags:create_term')
      ->defaultValue(TRUE);
    $this->addFieldMapping('field_tags:ignore_case')
     ->defaultValue(TRUE);
  }
  public function prepareRow($row) {
    $row->tags = explode(',', $row->tags);
    $row->program_type = explode(',', $row->program_type);
  }
}
