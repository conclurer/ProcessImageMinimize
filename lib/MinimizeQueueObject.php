<?php

/**
 * Class MinimizeQueueObject
 *
 * @property WireDatabasePDO $database
 */
class MinimizeQueueObject extends WireData {

    const table = 'minimize_pw_objects';
    private static $objects = array();
    private static $defaultValues = array(
        'id' => 0,
        'path' => null,
        'status' => 0,
        'replaceFile' => false,
        'reference' => null,
        'created' => 0,
        'updated' => 0
    );

    public static function getById ($id) {
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
        if (count($changes) == 0) return;

        if ($this->data['id'] == 0) {
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

    public function exists () {
        return !($this->data['id'] == 0);
    }

} 