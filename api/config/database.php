<?php

/*
    
    private $host = "localhost" || "localhost";
    private $db   = "c2761701_pampadb" || "pampagodb";
    private $user = "c2761701_pampadb" || "root";
    private $pass = "VE61foweba" || "";
       $host = "localhost";
        $db = "pampamind";
        $user = "root";
        $pass = "";
    
*/


class Database
{
    public function connect()
    {
        
        /*
        $host = "localhost";
        $db = "c2761701_pampadb";
        $user = "c2761701_pampadb";
        $pass = "VE61foweba";*/
        
        
        $host = "localhost";
        $db = "pampamind";
        $user = "root";
        $pass = "";
       

        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8",
                $user,
                $pass
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
            exit();
        }
    }
}
