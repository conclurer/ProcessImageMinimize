<?php

class MinimizeDatabaseMigration extends Wire {

    protected $current;

    public function __construct($current) {
        $this->current = $current;
    }

    public function __get($i) {
        if ($i == 'current') return $this->$i;

        return parent::__get($i);
    }

    public function migrate() {
        $dir = str_replace(DIRECTORY_SEPARATOR.'lib', '', dirname(__FILE__));
        $migrations = glob($dir.DIRECTORY_SEPARATOR.'db'.DIRECTORY_SEPARATOR.'minimize.delta.*.sql');
        sort($migrations);
        foreach ($migrations as $migration) {
            $stripes = explode('.', $migration);
            $number = intval($stripes[count($stripes)-2]);
            if ($number <= $this->current) continue;

            # Run migration script
            try {
                $this->database->query(file_get_contents($migration))->execute();
                $this->current = $number;
                $this->message("minimize.pw migration #$number successful");
            } catch (Exception $e) {
                $this->error("minimize.pw migration #$number failed");
                return false;
            }
        }

        return true;
    }

}