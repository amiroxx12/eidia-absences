<?php

namespace App\Services;

use Exception;

class CsvImportService {

    // DICTIONNAIRE ULTIME (Anglais/Français/Massar)
    private const DICTIONNAIRE = [
        // --- 1. IDENTIFICATION ÉTUDIANT ---
        'cne'               => ['cne', 'code_massar', 'codemassar', 'identifiant', 'id_etudiant', 'matricule', 'massar_id'],
        'nom'               => ['nom', 'name', 'last_name', 'lastname', 'family_name', 'nom_famille', 'nom_eleve', 'student_name'],
        'prenom'            => ['prenom', 'first_name', 'firstname', 'given_name', 'prenom_eleve', 'prenom_etudiant'],
        'classe'            => ['classe', 'classroom', 'class', 'niveau', 'groupe', 'filiere', 'section', 'classe_etudiant'],
        
        // --- 2. CONTACTS ÉTUDIANT ---
        // On sépare bien l'email étudiant de l'email parent
        'email'             => ['email', 'mail', 'mail_etudiant', 'email_etudiant', 'courriel', 'adresse_email', 'e-mail'],
        'telephone'         => ['tel', 'telephone', 'phone', 'mobile', 'gsm', 'tel_etudiant', 'numero', 'cellulaire'],
        'adresse'           => ['adresse', 'address', 'lieu_residence', 'domicile', 'adresse_postale', 'rue', 'ville'],

        // --- 3. PARENTS / TUTEURS ---
        'nom_parent'        => ['nom_parent', 'nom_tuteur', 'tuteur', 'responsable', 'father_name', 'mother_name', 'parent_name'],
        'email_parent'      => ['email_parent', 'mail_parent', 'courriel_parent', 'email_tuteur', 'parent_email'],
        'telephone_parent'  => ['telephone_parent', 'tel_parent', 'tel_tuteur', 'numero_parent', 'parent_phone', 'gsm_parent'],
        'whatsapp_parent'   => ['whatsapp', 'whatsapp_parent', 'num_whatsapp'],
        'cin_parent' => ['cin', 'cin_parent', 'carte_nationale', 'national_id', 'id_parent'],

        // --- 4. ABSENCES ---
        'etudiant_cne'      => ['cne', 'code_massar', 'identifiant', 'id_etudiant', 'matricule'],
        'date_seance'       => ['date', 'date_absence', 'jour', 'absent_le', 'absence_date', 'date_abs', 'date_seance'],
        'heure_debut'       => ['heure', 'time', 'heure_debut', 'debut', 'start_time', 'creneau'],
        'matiere'           => ['matiere', 'subject', 'cours', 'module', 'discipline', 'course'],
        'motif'             => ['motif', 'raison', 'reason', 'justification', 'commentaire', 'observation', 'remarque']
    ];

    private function fixEncoding(string $filePath): void {
        $content = file_get_contents($filePath);
        $encoding = mb_detect_encoding($content, 'UTF-8, Windows-1252, ISO-8859-1', true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            file_put_contents($filePath, $content);
        }
    }

    private function detectDelimiter(string $filePath): string {
        $handle = fopen($filePath, 'r');
        if ($handle === false) return ';';
        $line = fgets($handle);
        fclose($handle); 
        if (!$line) return ';';
        return (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
    }

    private function normalizeString(string $texte): string {
        // 1. Minuscules + UTF8
        $texte = mb_strtolower($texte, 'UTF-8');
        
        // 2. Accents
        $unwanted = [
            'š'=>'s', 'ž'=>'z', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'ae', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n',
            'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u',
            'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        ];
        $texte = strtr($texte, $unwanted);

        // 3. Remplacer TOUS les types d'espaces (y compris insécables) par _
        $texte = preg_replace('/[\s\xA0\xC2\xA0]+/', '_', $texte);

        // 4. Garder uniquement alphanum et underscore
        $texte = preg_replace('/[^a-z0-9_]/', '', $texte);
        
        return trim($texte, '_');
    }

    public function analyzeHeaders(string $filePath, array $allowedDbFields = []): array {
        if (!file_exists($filePath)) throw new Exception("Fichier introuvable.");
        
        $this->fixEncoding($filePath);
        $delimiter = $this->detectDelimiter($filePath);
        
        $handle = fopen($filePath, 'r');
        // Sauter le BOM UTF-8 s'il existe (C'est souvent lui qui casse la première colonne "Code Massar")
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $csvHeaders = fgetcsv($handle, 0, $delimiter);
        fclose($handle);

        if (!$csvHeaders) throw new Exception("Fichier vide ou illisible.");

        $suggestedMapping = [];

        foreach ($csvHeaders as $index => $headerOriginal) {
            $headerNorm = $this->normalizeString($headerOriginal);
            
            $bestMatch = null;
            $bestScore = 0;

            foreach (self::DICTIONNAIRE as $champDb => $variantes) {
                // Si le contrôleur impose une liste (ex: Import Etudiant), on filtre
                if (!empty($allowedDbFields) && !in_array($champDb, $allowedDbFields)) {
                    continue;
                }

                // A. Match Exact
                if (in_array($headerNorm, $variantes)) {
                    $bestMatch = $champDb;
                    $bestScore = 100;
                    break; 
                }

                // B. Match Partiel (Levenshtein)
                if ($bestScore < 100) {
                    foreach ($variantes as $variante) {
                        // Si l'un contient l'autre (ex: "mail_etudiant" contient "mail")
                        if (strpos($headerNorm, $variante) !== false || strpos($variante, $headerNorm) !== false) {
                            $sim = 85; 
                        } else {
                            $dist = levenshtein($headerNorm, $variante);
                            $len = max(strlen($headerNorm), strlen($variante));
                            $sim = ($len > 0) ? (1 - $dist / $len) * 100 : 0;
                        }

                        if ($sim > 75 && $sim > $bestScore) {
                            $bestMatch = $champDb;
                            $bestScore = round($sim);
                        }
                    }
                }
            }
            
            // On associe si on a trouvé un match
            if ($bestMatch) {
                $suggestedMapping[$index] = [
                    'target_field' => $bestMatch,
                    'confidence'   => $bestScore,
                    'status'       => ($bestScore >= 80) ? 'auto' : 'manuel'
                ];
            }
        }

        return [
            'csv_headers' => $csvHeaders,
            'suggested_mapping' => $suggestedMapping,
            'delimiter' => $delimiter
        ];
    }

    public function importData(string $filePath, array $mapping, string $delimiter, $model): array {
        if (!file_exists($filePath)) return ['imported' => 0, 'doublons' => 0];

        $handle = fopen($filePath, 'r');
        // Sauter BOM et Headers
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);
        fgetcsv($handle, 0, $delimiter); 

        $stats = ['imported' => 0, 'doublons' => 0];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (array_filter($row) === []) continue;

            $dataToInsert = [];
            foreach ($mapping as $colIndex => $dbField) {
                if (!empty($dbField) && isset($row[$colIndex])) {
                    $val = trim($row[$colIndex]);
                    $dataToInsert[$dbField] = $val === '' ? null : $val;
                }
            }

            if (!empty($dataToInsert)) {
                try {
                    $result = $model->create($dataToInsert);
                    if ($result === true) $stats['imported']++;
                    elseif ($result === null) $stats['doublons']++;
                } catch (Exception $e) {
                    continue;
                }
            }
        }
        fclose($handle);
        return $stats;
    }
}