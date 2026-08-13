<?php

use yii\db\Migration;

/**
 * Handles adding image_path to table `{{%quotation_item}}`.
 */
class m260729_000001_add_image_path_to_quotation_item_table extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->addColumn(
      '{{%quotation_item}}',
      'image_path',
      $this->string(255)->null()->after('description'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropColumn('{{%quotation_item}}', 'image_path');
  }
}