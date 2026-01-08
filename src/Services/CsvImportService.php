<?php

namespace App\Services;

use Exception;

class CsvImportService {

    /**
     * Détecte le délimiteur (; ou ,) en analysant la première ligne
     */
    private function detectDelimiter(string $filePath): string {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return ';'; // Valeur par défaut si échec
        }
        
        $line = fgets($handle); // Lire la première ligne
        fclose($handle); 
        
        if ($line === false) {
            return ';';
        }

        // Compare le nombre de ';' avec le nombre de ','
        return (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
    }

    /**
     * Analyse les en-têtes et suggère un mapping via Levenshtein
     */
    public function analyzeHeaders(string $filePath, array $dbColumns): array {
        if (!file_exists($filePath)) {
            throw new Exception("Le fichier n'existe pas");
        }
        
        // 1. Détection automatique du délimiteur
        $delimiter = $this->detectDelimiter($filePath);
        
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception("Impossible d'ouvrir le fichier.");
        }

        // 2. Lecture de la ligne d'en-tête
        $csvHeaders = fgetcsv($handle, 0, $delimiter);
        fclose($handle);

        if (!$csvHeaders) {
            throw new Exception("Impossible de lire les en-têtes du CSV ou fichier vide.");
        }

        // 3. Nettoyage des en-têtes (suppression BOM UTF-8 et espaces)
        $csvHeaders = array_map(function($h) {
            return trim(preg_replace('/\x{FEFF}/u', '', $h));
        }, $csvHeaders);

        // 4. Algorithme de suggestion (Levenshtein)
        $suggestedMapping = [];

        foreach ($csvHeaders as $index => $header) {
            $bestMatch = '';
            $shortestDistance = -1;

            foreach ($dbColumns as $dbCol) {
                // Comparaison insensible à la casse
                $lev = levenshtein(strtolower($header), strtolower($dbCol));

                // Correspondance exacte
                if ($lev == 0) {
                    $bestMatch = $dbCol;
                    $shortestDistance = 0;
                    break;
                }
                
                // Correspondance approchée (tolérance de 3 fautes max)
                if ($lev <= 3) {
                    if ($shortestDistance < 0 || $lev < $shortestDistance) {
                        $bestMatch = $dbCol;
                        $shortestDistance = $lev;
                    }
                }    
            }
            
            // Si on a trouvé une correspondance, on l'ajoute au tableau
            if ($bestMatch) {
                $suggestedMapping[$index] = $bestMatch;
            }
        }

        return [
            'csv_headers' => $csvHeaders,
            'suggested_mapping' => $suggestedMapping,
            'delimiter' => $delimiter
        ];
    }

    /**
     * Importe les données dans la base
     * @param string $filePath Chemin du fichier
     * @param array $mapping Tableau [index_csv => colonne_bdd]
     * @param string $delimiter Le délimiteur validé
     * @param object $model Instance du modèle (ex: Etudiant)
     */
    public function importData(string $filePath, array $mapping, string $delimiter, $model): int {
        if (!file_exists($filePath)) {
            return 0;
        }

        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 0, $delimiter); // Ignorer la première ligne (en-têtes)

        $count = 0;

        // Boucle sur chaque ligne du CSV
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

            $dataToInsert = [];
            
            foreach ($mapping as $colIndex => $dbField) {
                // On vérifie que le champ BDD n'est pas vide (colonne ignorée)
                // et que la donnée existe dans la ligne CSV
                if (!empty($dbField) && isset($row[$colIndex])) {
                    $val = trim($row[$colIndex]); // Nettoyage des espaces
                    
                    // On convertit les chaînes vides en NULL si nécessaire, sinon on garde la valeur
                    $dataToInsert[$dbField] = $val === '' ? null : $val;
                }
            }

            // Si on a des données à insérer
            if (!empty($dataToInsert)) {
                
                // Insertion via le modèle
                // Le modèle Etudiant renvoie true si succès, false si doublon (CNE)
                try {
                    if ($model->create($dataToInsert)) {
                        $count++;
                    }
                } catch (Exception $e) {
                    // On continue l'import même si une ligne échoue
                    continue;
                }
            }
        }

        fclose($handle);
        return $count;
    }
}