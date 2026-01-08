<?php
namespace App\Models;

require_once __DIR__ . '/../Services/DatabaseService.php';
use App\Services\DatabaseService;
use PDO;

class ImportConfiguration {
    private $db;
    public function __construct() {
        $this->db = DatabaseService::getInstance()->getConnection();
    }

    //pour sauvegarder la configuration de mapping
    public function save(string $name, array $mapping): bool {
        $sql = "INSERT INTO import_configurations (name, mapping_data) VALUES (:name, :mapping_data)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':name' => $name,
            ':mapping_data' => json_encode($mapping, JSON_UNESCAPED_UNICODE)
        ]);
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM import_configurations WHERE id= :id");
        $stmt->execute([':id' => $id]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if($config) {
            //transformer le json en tableau php utilisable
            $config['mapping_data'] = json_decode($config['mapping_data'], true);
        }
        return $config;
    }
}