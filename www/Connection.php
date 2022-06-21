<?php 

class Connection
{
        
        public $pdo;

        // Simulate Environnement Variables
        private $database = 'mysql:dbname=mvcdocker2;host=database';
        private $username = 'root';
        private $password = 'password';

        public function __construct()
        {
            

            try {
                $this->pdo = new PDO($this->database, $this->username, $this->password);
                $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
                $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            }
            catch (PDOException $e) {
                die($e->getMessage());
            }
        }

}
