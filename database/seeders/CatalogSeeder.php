<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run()
    {
        $items = [
            // --- 1. PAIN & FEVER (ANTALGIQUES / ANTIPYRETIQUES) ---
            ['n'=>'Doliprane', 'f'=>'Tablet', 's'=>'1000mg', 'q'=>1, 'fr'=>3, 'd'=>5],
            ['n'=>'Doliprane', 'f'=>'Tablet', 's'=>'500mg', 'q'=>1, 'fr'=>3, 'd'=>5],
            ['n'=>'Doliprane', 'f'=>'Syrup', 's'=>'2.4%', 'q'=>1, 'fr'=>3, 'd'=>5],
            ['n'=>'Doliprane', 'f'=>'Suppository', 's'=>'150mg', 'q'=>1, 'fr'=>2, 'd'=>3],
            ['n'=>'Efferalgan', 'f'=>'Tablet (Effervescent)', 's'=>'1g', 'q'=>1, 'fr'=>3, 'd'=>5],
            ['n'=>'Panadol', 'f'=>'Tablet', 's'=>'Extra', 'q'=>2, 'fr'=>3, 'd'=>5],
            ['n'=>'Aspro', 'f'=>'Sachet', 's'=>'500mg', 'q'=>1, 'fr'=>3, 'd'=>3],
            ['n'=>'Cofdal', 'f'=>'Tablet', 's'=>'-', 'q'=>1, 'fr'=>3, 'd'=>5],

            // --- 2. ANTIBIOTICS (ANTIBIOTIQUES) ---
            ['n'=>'Augmentin', 'f'=>'Tablet', 's'=>'1g/125mg', 'q'=>1, 'fr'=>2, 'd'=>7], // Standard Adult
            ['n'=>'Augmentin', 'f'=>'Sachet', 's'=>'1g', 'q'=>1, 'fr'=>2, 'd'=>7],
            ['n'=>'Augmentin Enfant', 'f'=>'Sachet', 's'=>'100mg/12.5mg', 'q'=>1, 'fr'=>3, 'd'=>7],
            ['n'=>'Amoxil', 'f'=>'Capsule', 's'=>'500mg', 'q'=>2, 'fr'=>2, 'd'=>7],
            ['n'=>'Amoxil', 'f'=>'Tablet', 's'=>'1g', 'q'=>1, 'fr'=>2, 'd'=>7],
            ['n'=>'Zeclar', 'f'=>'Tablet', 's'=>'500mg', 'q'=>1, 'fr'=>2, 'd'=>7],
            ['n'=>'Zinnat', 'f'=>'Tablet', 's'=>'500mg', 'q'=>1, 'fr'=>2, 'd'=>7],
            ['n'=>'Oflocet', 'f'=>'Tablet', 's'=>'200mg', 'q'=>1, 'fr'=>2, 'd'=>7],
            ['n'=>'Orelox', 'f'=>'Tablet', 's'=>'200mg', 'q'=>1, 'fr'=>2, 'd'=>5],
            ['n'=>'Flagyl', 'f'=>'Tablet', 's'=>'500mg', 'q'=>1, 'fr'=>3, 'd'=>7],
            ['n'=>'Rodogyl', 'f'=>'Tablet', 's'=>'-', 'q'=>2, 'fr'=>2, 'd'=>7], // Dental
            ['n'=>'Pyostacine', 'f'=>'Tablet', 's'=>'500mg', 'q'=>2, 'fr'=>2, 'd'=>8],
            ['n'=>'Bactrim', 'f'=>'Tablet', 's'=>'Forte', 'q'=>1, 'fr'=>2, 'd'=>5],

            // --- 3. ANTI-INFLAMMATORY (AINS & CORTICOIDES) ---
            ['n'=>'Voltarène', 'f'=>'Tablet', 's'=>'75mg LP', 'q'=>1, 'fr'=>1, 'd'=>7],
            ['n'=>'Voltarène', 'f'=>'Tablet', 's'=>'50mg', 'q'=>1, 'fr'=>2, 'd'=>5],
            ['n'=>'Voltarène', 'f'=>'Injection', 's'=>'75mg', 'q'=>1, 'fr'=>1, 'd'=>3],
            ['n'=>'Cataflam', 'f'=>'Tablet', 's'=>'50mg', 'q'=>1, 'fr'=>3, 'd'=>5],
            ['n'=>'Bi-Profenid', 'f'=>'Tablet', 's'=>'150mg', 'q'=>1, 'fr'=>2, 'd'=>5],
            ['n'=>'Antadys', 'f'=>'Tablet', 's'=>'100mg', 'q'=>1, 'fr'=>3, 'd'=>4], // Period pain
            ['n'=>'Apranax', 'f'=>'Tablet', 's'=>'550mg', 'q'=>1, 'fr'=>2, 'd'=>5],
            ['n'=>'Surgam', 'f'=>'Tablet', 's'=>'200mg', 'q'=>1, 'fr'=>2, 'd'=>7],
            ['n'=>'Solupred', 'f'=>'Tablet', 's'=>'20mg', 'q'=>2, 'fr'=>1, 'd'=>5],
            ['n'=>'Célestène', 'f'=>'Drops', 's'=>'0.05%', 'q'=>10, 'fr'=>1, 'd'=>3], // Drops for kids

            // --- 4. GASTRO (ESTOMAC) ---
            ['n'=>'Spasfon', 'f'=>'Tablet', 's'=>'80mg', 'q'=>2, 'fr'=>3, 'd'=>5],
            ['n'=>'Spasfon Lyoc', 'f'=>'Tablet', 's'=>'80mg', 'q'=>2, 'fr'=>3, 'd'=>3],
            ['n'=>'Omépragén', 'f'=>'Capsule', 's'=>'20mg', 'q'=>1, 'fr'=>1, 'd'=>14], // Morning
            ['n'=>'Mopral', 'f'=>'Capsule', 's'=>'20mg', 'q'=>1, 'fr'=>1, 'd'=>14],
            ['n'=>'Gaviscon', 'f'=>'Syrup', 's'=>'-', 'q'=>1, 'fr'=>3, 'd'=>7],
            ['n'=>'Gaviscon', 'f'=>'Sachet', 's'=>'-', 'q'=>1, 'fr'=>3, 'd'=>7],
            ['n'=>'Smecta', 'f'=>'Sachet', 's'=>'3g', 'q'=>1, 'fr'=>3, 'd'=>3],
            ['n'=>'Imodium', 'f'=>'Capsule', 's'=>'2mg', 'q'=>1, 'fr'=>2, 'd'=>3],
            ['n'=>'Motilium', 'f'=>'Tablet', 's'=>'10mg', 'q'=>1, 'fr'=>3, 'd'=>5],
            ['n'=>'Ercéfuryl', 'f'=>'Capsule', 's'=>'200mg', 'q'=>1, 'fr'=>2, 'd'=>5],
            ['n'=>'Flagyl', 'f'=>'Syrup', 's'=>'125mg', 'q'=>1, 'fr'=>3, 'd'=>7],

            // --- 5. RESPIRATORY / COLD (RHUME) ---
            ['n'=>'Rhinofébral', 'f'=>'Sachet', 's'=>'-', 'q'=>1, 'fr'=>3, 'd'=>4],
            ['n'=>'Rhumix', 'f'=>'Tablet', 's'=>'-', 'q'=>1, 'fr'=>3, 'd'=>5],
            ['n'=>'Humex Rhume', 'f'=>'Tablet', 's'=>'Day/Night', 'q'=>1, 'fr'=>3, 'd'=>4],
            ['n'=>'Pneumorel', 'f'=>'Syrup', 's'=>'0.2%', 'q'=>1, 'fr'=>3, 'd'=>5],
            ['n'=>'Drill', 'f'=>'Lozenge', 's'=>'-', 'q'=>1, 'fr'=>4, 'd'=>5],
            ['n'=>'Ventoline', 'f'=>'Spray', 's'=>'100µg', 'q'=>2, 'fr'=>3, 'd'=>7],
            ['n'=>'Ambroxol', 'f'=>'Syrup', 's'=>'0.6%', 'q'=>1, 'fr'=>3, 'd'=>7],
            ['n'=>'Aerius', 'f'=>'Tablet', 's'=>'5mg', 'q'=>1, 'fr'=>1, 'd'=>10], // Allergy
            ['n'=>'Zyrtec', 'f'=>'Tablet', 's'=>'10mg', 'q'=>1, 'fr'=>1, 'd'=>10],
            ['n'=>'Celestamine', 'f'=>'Tablet', 's'=>'-', 'q'=>1, 'fr'=>3, 'd'=>5],

            // --- 6. VITAMINS / MINERALS ---
            ['n'=>'Magnésium B6', 'f'=>'Tablet', 's'=>'-', 'q'=>2, 'fr'=>2, 'd'=>30],
            ['n'=>'Vitamine C Upsa', 'f'=>'Tablet (Effervescent)', 's'=>'1000mg', 'q'=>1, 'fr'=>1, 'd'=>10],
            ['n'=>'Berocca', 'f'=>'Tablet', 's'=>'-', 'q'=>1, 'fr'=>1, 'd'=>15],
            ['n'=>'Supradyn', 'f'=>'Tablet', 's'=>'Intense', 'q'=>1, 'fr'=>1, 'd'=>15],
            ['n'=>'Tardyferon', 'f'=>'Tablet', 's'=>'80mg', 'q'=>1, 'fr'=>1, 'd'=>60], // Iron
            ['n'=>'Fumafer', 'f'=>'Tablet', 's'=>'-', 'q'=>2, 'fr'=>2, 'd'=>60],
            ['n'=>'Uvimag B6', 'f'=>'Ampoule', 's'=>'-', 'q'=>2, 'fr'=>2, 'd'=>15],

            // --- 7. CARDIO / DIABETES (CHRONIC) ---
            ['n'=>'Kardégic', 'f'=>'Sachet', 's'=>'75mg', 'q'=>1, 'fr'=>1, 'd'=>90],
            ['n'=>'Kardégic', 'f'=>'Sachet', 's'=>'160mg', 'q'=>1, 'fr'=>1, 'd'=>90],
            ['n'=>'Tahor', 'f'=>'Tablet', 's'=>'10mg', 'q'=>1, 'fr'=>1, 'd'=>30],
            ['n'=>'Tahor', 'f'=>'Tablet', 's'=>'20mg', 'q'=>1, 'fr'=>1, 'd'=>30],
            ['n'=>'Glucophage', 'f'=>'Tablet', 's'=>'500mg', 'q'=>1, 'fr'=>3, 'd'=>90],
            ['n'=>'Glucophage', 'f'=>'Tablet', 's'=>'850mg', 'q'=>1, 'fr'=>2, 'd'=>90],
            ['n'=>'Glucophage', 'f'=>'Tablet', 's'=>'1000mg', 'q'=>1, 'fr'=>2, 'd'=>90],
            ['n'=>'Lasilix', 'f'=>'Tablet', 's'=>'40mg', 'q'=>1, 'fr'=>1, 'd'=>30],
            ['n'=>'Amlor', 'f'=>'Capsule', 's'=>'5mg', 'q'=>1, 'fr'=>1, 'd'=>30],
            ['n'=>'Triatec', 'f'=>'Tablet', 's'=>'5mg', 'q'=>1, 'fr'=>1, 'd'=>30],

            // --- 8. DERMATO / TOPICAL ---
            ['n'=>'Fucidine', 'f'=>'Cream', 's'=>'2%', 'q'=>1, 'fr'=>2, 'd'=>7],
            ['n'=>'Auréomycine', 'f'=>'Cream', 's'=>'3%', 'q'=>1, 'fr'=>2, 'd'=>5],
            ['n'=>'Mytosil', 'f'=>'Cream', 's'=>'-', 'q'=>1, 'fr'=>3, 'd'=>5],
            ['n'=>'Bépanthène', 'f'=>'Cream', 's'=>'5%', 'q'=>1, 'fr'=>3, 'd'=>7],
            ['n'=>'Bétadine', 'f'=>'Solution', 's'=>'Dermique', 'q'=>1, 'fr'=>2, 'd'=>5],
            ['n'=>'Locoid', 'f'=>'Cream', 's'=>'0.1%', 'q'=>1, 'fr'=>2, 'd'=>7],
            ['n'=>'Econazole', 'f'=>'Cream', 's'=>'1%', 'q'=>1, 'fr'=>2, 'd'=>14],

            // --- 9. LAB TESTS (ANALYSES) ---
            ['n'=>'NFS (Numération Formule Sanguine)', 't'=>'test'],
            ['n'=>'Bilan Lipidique (Cholestérol)', 't'=>'test'],
            ['n'=>'Glycémie à jeun', 't'=>'test'],
            ['n'=>'HbA1c (Hémoglobine Glyquée)', 't'=>'test'],
            ['n'=>'Créatinine', 't'=>'test'],
            ['n'=>'Urée', 't'=>'test'],
            ['n'=>'Transaminases (SGOT/SGPT)', 't'=>'test'],
            ['n'=>'ECBU (Examen Cytobactériologique des Urines)', 't'=>'test'],
            ['n'=>'VS (Vitesse de Sédimentation)', 't'=>'test'],
            ['n'=>'CRP (Protéine C Réactive)', 't'=>'test'],
            ['n'=>'Groupe Sanguin', 't'=>'test'],
            ['n'=>'TSH (Thyroïde)', 't'=>'test'],
            ['n'=>'Ferritine', 't'=>'test'],
            ['n'=>'Vitamine D', 't'=>'test'],
        ];

        foreach ($items as $item) {
            $type = $item['t'] ?? 'medicine';
            
            CatalogItem::firstOrCreate(
                [
                    'name' => $item['n'],
                    'strength' => $item['s'] ?? null,
                    'form' => $item['f'] ?? null,
                ],
                [
                    'type' => $type,
                    'clinic_id' => null, // Global System Item
                    'default_quantity' => $item['q'] ?? null,
                    'default_frequency' => $item['fr'] ?? null,
                    'default_duration' => $item['d'] ?? null,
                ]
            );
        }
    }
}