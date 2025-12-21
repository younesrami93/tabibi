<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use App\Models\MedicalService;
use Illuminate\Database\Seeder;

class MedicalServiceSeeder extends Seeder
{
    public function run()
    {

        $services = [
            // --- CONSULTATIONS ---
            [
                'code' => 'C',
                'name' => 'Consultation de Médecine Générale',
                'description' => 'Consultation standard au cabinet',
                'price' => 150.00,
            ],
            [
                'code' => 'CS',
                'name' => 'Consultation de Spécialiste',
                'description' => 'Consultation spécialisée',
                'price' => 250.00, // Vary by specialty, average
            ],
            [
                'code' => 'V',
                'name' => 'Visite à Domicile (Jour)',
                'description' => 'Déplacement du médecin au domicile du patient',
                'price' => 300.00,
            ],
            [
                'code' => 'VN',
                'name' => 'Visite à Domicile (Nuit/Urgence)',
                'description' => 'Visite de nuit ou week-end',
                'price' => 500.00,
            ],

            // --- IMAGING / RADIOLOGY (Often in cabinet) ---
            [
                'code' => 'ECO',
                'name' => 'Échographie Abdominale',
                'description' => 'Exploration des organes abdominaux',
                'price' => 300.00,
            ],
            [
                'code' => 'ECO-P',
                'name' => 'Échographie Pelvienne',
                'description' => 'Exploration pelvienne',
                'price' => 300.00,
            ],
            [
                'code' => 'ECO-OBS',
                'name' => 'Échographie Obstétricale (Grossesse)',
                'description' => 'Suivi de grossesse (T1, T2, T3)',
                'price' => 250.00,
            ],
            [
                'code' => 'ECG',
                'name' => 'Électrocardiogramme (ECG)',
                'description' => 'Enregistrement de l\'activité cardiaque',
                'price' => 150.00,
            ],

            // --- PETITE CHIRURGIE / SOINS ---
            [
                'code' => 'INJ',
                'name' => 'Injection (IM/IV)',
                'description' => 'Acte d\'injection simple (produit non fourni)',
                'price' => 50.00,
            ],
            [
                'code' => 'PANS',
                'name' => 'Pansement Simple',
                'description' => 'Nettoyage et pansement de plaie superficielle',
                'price' => 80.00,
            ],
            [
                'code' => 'SUT',
                'name' => 'Suture (Petite plaie)',
                'description' => 'Points de suture pour plaie simple',
                'price' => 200.00,
            ],
            [
                'code' => 'AER',
                'name' => 'Aérosolthérapie (Nébulisation)',
                'description' => 'Séance d\'aérosol pour crise d\'asthme',
                'price' => 100.00,
            ],
            [
                'code' => 'LAV-OR',
                'name' => 'Lavage d\'Oreille',
                'description' => 'Extraction de bouchon de cérumen',
                'price' => 150.00,
            ],
            [
                'code' => 'CIR',
                'name' => 'Circoncision (Anesthésie Locale)',
                'description' => 'Acte chirurgical (Cabinet)',
                'price' => 800.00,
            ],

            // --- SPECIALTY SPECIFIC (Cardio/Gyneco/etc) ---
            [
                'code' => 'FRO',
                'name' => 'Frottis Cervico-Vaginal (FCV)',
                'description' => 'Prélèvement gynécologique',
                'price' => 200.00,
            ],
            [
                'code' => 'MAPA',
                'name' => 'MAPA (Holter Tensionnel)',
                'description' => 'Mesure Ambulatoire de la Pression Artérielle (24h)',
                'price' => 500.00,
            ],
            [
                'code' => 'HOLT',
                'name' => 'Holter ECG',
                'description' => 'Enregistrement ECG 24h',
                'price' => 600.00,
            ],
            [
                'code' => 'SPIRO',
                'name' => 'Spirométrie',
                'description' => 'Exploration fonctionnelle respiratoire',
                'price' => 300.00,
            ],
        ];

        // Get all clinics to seed data for them
        // In a real SaaS, maybe you only seed for new clinics, but for dev we seed all

        foreach ($services as $service) {
            MedicalService::firstOrCreate(
                [
                    'clinic_id' => 1,
                    'name' => $service['name']
                ],
                [
                    'code' => $service['code'],
                    'description' => $service['description'],
                    'price' => $service['price'],
                ]
            );
        }
    }
}