<?php

use yii\db\Migration;

/**
 * Class m190522_145317_activity
 */
class m190522_145317_activity extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%activity}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(255)->notNull(),
            'message' => $this->text(),
            'created_by' => $this->integer(11)->null(),
            'created_at' => $this->integer(11)->notNull(),
            'action' => $this->string(255)->notNull(),
            'metadata' => $this->text(),
        ], $tableOptions);

        $this->createIndex('{{%idx-activity-action}}', '{{%activity}}', 'action');
        $this->createIndex('{{%idx-activity-type}}', '{{%activity}}', 'type');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('{{%activity}}');
        $this->dropIndex('{{%idx-activity-action}}', '{{%activity}}');
        $this->dropIndex('{{%idx-activity-type}}', '{{%activity}}');
        $this->dropTable('{{%activity}}');
    }

}
