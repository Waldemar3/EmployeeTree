<?php namespace App;

abstract class Table
{
    protected $timestamp;
    protected $table;
    protected $pdo;

    protected $in;
    protected $limit;
    protected $where;

    protected static $relationships = [];

    function __construct(\PDO $pdo) {

        $this->pdo = $pdo;

        $this->table = strtolower(end(explode('\\', static::class)));
    	$this->timestamp = $_SERVER['REQUEST_TIME'];

        $relationships = "";

        foreach(self::$relationships as $foreignKey => $relationship){
            $relationships .= ", FOREIGN KEY ($foreignKey) REFERENCES ". $relationship[0]->table ." (". $relationship[1] .") ON DELETE CASCADE";
        }

        $this->pdo->exec(
            "create table if not exists ". $this->table ." (
                ". static::migrate() . $relationships ."
            )"
        );
    }

    protected function select(string $string){
        $query = "
            select ". $string ." from ". $this->table ."". $this->queryWhere() . $this->queryIn() . $this->queryLimit() .";
	    ";

        $stmnt = $this->pdo->prepare($query);
        $stmnt->execute($values);

        return $stmnt->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function insert(array $values){
        $query = "
            insert into ". $this->table ." 
            (". $this->insertArrayToString($values) .") 
            values
            (:". $this->insertArrayToMask($values) .")
	    ";
        $stmnt = $this->pdo->prepare($query);
        $stmnt->execute($values);

        return $this->pdo->lastInsertId();
    }

    protected function update(array $values){
        $query = "
            update ". $this->table ." set ". $this->updateArrayToMask($values) ."". $this->queryWhere() ."
	    ";
        $stmnt = $this->pdo->prepare($query);
        $stmnt->execute($values);

        return true;
    }

    protected function delete(){
        $query = "
            delete from ". $this->table ."". $this->queryWhere() ."
        ";
        $stmnt = $this->pdo->prepare($query);
        $stmnt->execute($values);

        return true;
    }

    protected function where(string $where){
        $this->where = $where;
        return $this;
    }
    protected function limit(int $limit){
        $this->limit = $limit;
        return $this;
    }
    protected function in(array $in){
        $this->in = $in;
        return $this;
    }

    public static function addForeignKey(Table $table, string $primaryKey, string $foreignKey): void {
        self::$relationships[$foreignKey] = [$table, $primaryKey];
    }

    protected function getTableById($id){
        return self::$relationships[$id][0];
    }

    private function queryWhere(){
        return !empty($this->where) ? " where ".$this->where : '';
    }

    private function queryLimit(){
        return !empty($this->limit) ? " limit ".$this->limit : '';
    }
    private function queryIn(){
        return !empty($this->in) ? " in (". implode(',', $this->in) .")" : '';
    }

    private function insertArrayToString($values){
        return implode(', ', array_keys($values));
    }
    private function insertArrayToMask($values){
        return implode(', :', array_keys($values));
    }
    private function updateArrayToMask($values){
        $masks = [];

        foreach ($values as $key => $_){
            $masks[] = $key."=:".$key;
        }

        return implode(',', $masks);
    }

    abstract protected function migrate(): string;
}