<?php

    interface QueryBuilder
    {
        public function insert(string $table, array $columns): QueryBuilder;

        public function select(string $table, array $columns): QueryBuilder;

        public function where(string $column, string $value, string $operator = "="): QueryBuilder;

        public function rightJoin(string $table, string $fk, string $pk): QueryBuilder;
        
        public function limit(int $from, int $offset): QueryBuilder;

        public function getQuery(): string;
    }

    class MysqlBuilder implements QueryBuilder
    {
        private $query;

        private function reset()
        {
            $this->query = new \stdClass();
        }

        public function insert(string $table, array $columns): QueryBuilder
        {
            $this->reset();

            $this->query->base = "INSERT INTO " . $table . " (" . implode(", ", $columns) . ") VALUES (";

            for ($i = 0; $i < count($columns) ; $i++) { 

                if($i == 0) {
                    $this->query->base .= '?';
                } else {
                    $this->query->base .= ', ?';
                }
                
            }

            $this->query->base .= ')';

            return $this;

        }


        public function update(string $table, array $columns, string $jointure = ''): QueryBuilder
        {
            $this->reset();

            $update = [];
            foreach ($columns as $column)
            {
                $update[] = $column."=?";
            }
            if(isset($jointure)){
                $addJoin = $jointure;
            }

            $this->query->base = "UPDATE  " . $table . " ".$addJoin. "  SET " . implode(", ", $update) ;

            return $this;

        }


        public function select(string $table, array $columns): QueryBuilder
        {
            $this->reset();
            $this->query->base = "SELECT " . implode(", ", $columns) . " FROM " . $table;
            return $this;
        }

        public function where(string $column, string $value, string $operator = "="): QueryBuilder
        {
            $this->query->where[] = $column . $operator . $value;
            return $this;
        }

        public function rightJoin(string $table, string $fk, string $pk): QueryBuilder 
        {
            $this->query->join[] = " RIGHT JOIN " . $table . " ON " . $pk . " = " . $fk;
            return $this;
        }

        public function limit(int $from, int $offset): QueryBuilder
        {
            $this->query->limit = " LIMIT " . $from . ", " . $offset;
            return $this;
        }

        public function getQuery(): string
        {
            $query = $this->query;

            $sql = $query->base;

            if (!empty($query->join)) {
                $sql .= implode(' ', $query->join);
            }

            if (!empty($query->where)) {
                $sql .= " WHERE " . implode(' AND ', $query->where);
            }

            if (isset($query->limit)) {
                $sql .= $query->limit;
            }

            $sql .= ";";

            return $sql;
            
        }
    }


    class PostgreBuilder extends MysqlBuilder
    {   
        
        public function limit(int $from, int $offset): QueryBuilder
        {
            $this->query->limit = " LIMIT " . $from . " OFFSET " . $offset;
            return $this;
        }

    }

?>