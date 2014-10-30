<?php

/**
 * Class MinimizeQueueObject
 *
 * @property WireDatabasePDO $database
 */
class MinimizeQueueObject extends WireData {

    const table = 'minimize_pw_objects';
    const StatusProcessing = '.mz-processing';
    const StatusReplaced = '.mz-replaced';
    const StatusExcluded = '.mz-excluded';

    private static $objects = array();
    private static $defaultValues = array(
        'id' => 0,
        'path' => null,
        'status' => 0,
        'replaceFile' => false,
        'reference' => null,
        'processResponse' => null,
        'created' => 0,
        'updated' => 0
    );

    public function __set($x, $y) {
        if ($x == 'path') {
            $y = str_replace($this->config->paths->files, '', $y);
        }

        parent::__set($x, $y);
    }

    public static function getById ($id) {
        if (isset(self::$objects[$id])) return self::$objects[$id];

        $instance = new self();
        $instance->id = $id;
        $instance->fetch();

        return $instance;
    }

    public static function getByReference ($reference) {
        $table = self::table;
        $instance = new self();

        $statement = wire('database')->prepare("SELECT id FROM $table WHERE reference = ? LIMIT 1");
        $statement->execute(array($reference));

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result !== false) {
            if (isset(self::$objects[$result['id']])) return self::$objects[$result['id']];

            $instance->id = $result['id'];
            $instance->fetch();
        }

        return $instance;
    }

    public static function getByPath ($path) {
        $table = self::table;
        $instance = new self();

        $statement = wire('database')->prepare("SELECT id FROM $table WHERE path = ? LIMIT 1");
        $statement->execute(array($path));

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result !== false) {
            if (isset(self::$objects[$result['id']])) return self::$objects[$result['id']];

            $instance->id = $result['id'];
            $instance->fetch();
        }

        return $instance;
    }

    public static function findByStatus ($status) {
        $table = self::table;
        $array = new WireArray();

        $statement = wire('database')->prepare("SELECT * FROM $table WHERE status = ?");
        $statement->execute(array($status));

        while (($set = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            if (isset(self::$objects[$set['id']])) {
                $array->add(self::$objects[$set['id']]);
                continue;
            }

            $instance = new self();
            $instance->data = $set;
            $array->add($instance);
        }

        return $array;
    }

    public function __construct() {
        $this->data = self::$defaultValues;

        $this->setTrackChanges(true);
    }

    public function save() {
        $table = self::table;
        $changes = $this->getChanges();
        $this->data['updated'] = time();
        if (count($changes) == 0) return;

        if ($this->data['id'] == 0) {
            $this->data['created'] = time();
            $dx = $this->data;
            unset($dx['id']);

            //Create database record
            $fields = implode(', ', array_keys($dx));
            $values = array();
            foreach (array_keys($dx) as $z) $values[] = '?';
            $values = implode(',', $values);
            $statement = $this->database->prepare("INSERT INTO $table ($fields) VALUES ($values)");
            $statement->execute($dx);

            $this->data['id'] = $this->database->lastInsertId();
            $this->resetTrackChanges(true);

            self::$objects[$this->data['id']] = $this;
        }
        else {
            $updateFields = array();
            $updateValues = array();
            foreach ($changes as $x) {
                if ($x == 'id') continue;
                $updateFields[] = "$x = ?";
                $updateValues[] = $this->data[$x];
            }
            $updateFields = implode(', ', $updateFields);

            $statement = $this->database->prepare("UPDATE $table SET $updateFields WHERE id = ?");
            $statement->execute(array_merge($updateValues, array($this->data['id'])));

            $this->resetTrackChanges(true);
        }
    }

    public function createStatusFlagFile ($status) {
        $pathInfo = pathinfo($this->data['path']);
        $flagFilePath = $this->config->paths->files.$pathInfo['dirname'].DIRECTORY_SEPARATOR.'mz'.DIRECTORY_SEPARATOR.$pathInfo['basename'].$status;
        try {
            touch($flagFilePath);
        } catch (Exception $e) {
            #todo log
        }
    }

    public function url() {
        $http = $this->config->https ? 'https://' : 'http://';

        return $http.$this->config->httpHost.$this->config->urls->files.$this->data['path'];
    }

    public function fetch() {
        $table = self::table;

        $statement = $this->database->prepare("SELECT * FROM $table WHERE id = ?");
        $statement->execute(array($this->data['id']));

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            $this->data = self::$defaultValues;
            return;
        }

        $this->data = $row;
        $this->resetTrackChanges(true);

        self::$objects[$this->data['id']] = $this;
    }

    public function delete() {
        $table = self::table;

        $statement = $this->database->prepare("DELETE FROM $table WHERE id = ?");
        $statement->execute(array($this->data['id']));

        $this->data['id'] = 0;
    }

    public function exists () {
        return !($this->data['id'] == 0);
    }

} 