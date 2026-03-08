<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Departement;
use App\Models\Poste;
use App\Models\TypeConge;
use App\Models\Formation;
use App\Models\Avantage;
use App\Models\NotificationSgrh;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Créer les rôles ──
        /* $roles = ['admin', 'rh', 'manager', 'employe'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ── Créer l'administrateur par défaut ──
        $admin = User::firstOrCreate(
            ['email' => 'admin@sgrh.com'],
            [
                'name'     => 'Administrateur',
                'password' => bcrypt('Admin@1234'),
                'is_active' => true,
            ]
        );

        $admin->assignRole('admin');

        // ── Créer un RH de test ──
        $rh = User::firstOrCreate(
            ['email' => 'rh@sgrh.com'],
            [
                'name'     => 'Responsable RH',
                'password' => bcrypt('Rh@1234'),
                'is_active' => true,
            ]
        );

        $rh->assignRole('rh');

        echo "✅ Rôles et utilisateurs de base créés.\n";
 */
        // ── Départements ──
        /* $depts = [
            ['nom' => 'Direction Générale',  'description' => 'Direction et pilotage stratégique'],
            ['nom' => 'Ressources Humaines', 'description' => 'Gestion du personnel'],
            ['nom' => 'Informatique',        'description' => 'Développement et infrastructure IT'],
            ['nom' => 'Finance',             'description' => 'Comptabilité et finances'],
            ['nom' => 'Commercial',          'description' => 'Ventes et relation client'],
        ];

        foreach ($depts as $dept) {
            Departement::firstOrCreate(['nom' => $dept['nom']], $dept);
        }

        // ── Postes ──
        $postes = [
            ['titre' => 'Développeur Full-Stack', 'departement_id' => 3, 'salaire_min' => 400000, 'salaire_max' => 800000],
            ['titre' => 'Chef de projet IT',      'departement_id' => 3, 'salaire_min' => 600000, 'salaire_max' => 1000000],
            ['titre' => 'Chargé RH',              'departement_id' => 2, 'salaire_min' => 350000, 'salaire_max' => 600000],
            ['titre' => 'Comptable',              'departement_id' => 4, 'salaire_min' => 350000, 'salaire_max' => 550000],
            ['titre' => 'Commercial',             'departement_id' => 5, 'salaire_min' => 300000, 'salaire_max' => 500000],
        ];

        foreach ($postes as $poste) {
            Poste::firstOrCreate(['titre' => $poste['titre']], $poste);
        }

        echo "✅ Départements et postes créés.\n"; */

        //Type de congé
        /* $typesConges = [
            ['libelle' => 'Congés payés',  'jours_annuels' => 25, 'couleur' => '#2e75b6', 'reportable' => true],
            ['libelle' => 'RTT',           'jours_annuels' => 12, 'couleur' => '#27ae60', 'reportable' => false],
            ['libelle' => 'Maladie',       'jours_annuels' => 15, 'couleur' => '#e74c3c', 'justificatif_requis' => true, 'couleur' => '#e74c3c'],
            ['libelle' => 'Maternité',     'jours_annuels' => 98, 'couleur' => '#9b59b6'],
            ['libelle' => 'Sans solde',    'jours_annuels' => 0,  'couleur' => '#95a5a6'],
            ['libelle' => 'Événement fam.','jours_annuels' => 5,  'couleur' => '#f39c12'],
        ];

        foreach ($typesConges as $type) {
            TypeConge::firstOrCreate(['libelle' => $type['libelle']], $type);
        }
        echo "✅ Types de congés créés.\n"; */


/*         $formations = [
            [
                'titre'       => 'Sécurité informatique & RGPD',
                'type'        => 'interne',
                'statut'      => 'planifiee',
                'date_debut'  => '2025-08-15',
                'date_fin'    => '2025-08-16',
                'duree_heures'=> 14,
                'places_max'  => 20,
                'cout'        => 0,
                'lieu'        => 'Salle de conférence A',
                'formateur'   => 'Équipe IT',
            ],
            [
                'titre'       => 'Leadership & Management d\'équipe',
                'type'        => 'externe',
                'statut'      => 'planifiee',
                'date_debut'  => '2025-09-03',
                'date_fin'    => '2025-09-05',
                'duree_heures'=> 21,
                'places_max'  => 15,
                'cout'        => 250000,
                'lieu'        => 'Hôtel Laïco, Bobo-Dioulasso',
                'formateur'   => 'Cabinet RH Pro',
            ],
            [
                'titre'       => 'Excel Avancé & Tableaux de bord',
                'type'        => 'elearning',
                'statut'      => 'en_cours',
                'date_debut'  => '2025-07-01',
                'date_fin'    => '2025-09-30',
                'duree_heures'=> 10,
                'places_max'  => null,
                'cout'        => 50000,
                'lien_elearning' => 'https://elearning.example.com/excel-avance',
                'formateur'   => 'Udemy Business',
            ],
        ];

        foreach ($formations as $f) {
            Formation::firstOrCreate(['titre' => $f['titre']], $f);
        }

        echo "✅ Formations créées.\n"; */

        /* $avantages = [
            ['libelle' => 'Voiture de fonction',    'valeur' => 80000,  'description' => 'Véhicule de service attribué'],
            ['libelle' => 'Logement de fonction',   'valeur' => 150000, 'description' => 'Logement pris en charge par l\'entreprise'],
            ['libelle' => 'Téléphone de service',   'valeur' => 20000,  'description' => 'Téléphone + forfait mobile'],
            ['libelle' => 'Ticket restaurant',      'valeur' => 30000,  'description' => 'Tickets repas mensuels'],
            ['libelle' => 'Allocation transport',   'valeur' => 25000,  'description' => 'Remboursement frais transport'],
        ];

        foreach ($avantages as $av) {
            Avantage::firstOrCreate(['libelle' => $av['libelle']], $av);
        }

        echo "✅ Avantages créés.\n"; */

        /* $admin = User::where('email', 'admin@sgrh.com')->first();

        if ($admin) {
            $notifs = [
                [
                    'titre'   => 'Bienvenue dans le SGRH',
                    'message' => 'Votre système de gestion des ressources humaines est opérationnel.',
                    'type'    => 'systeme',
                ],
                [
                    'titre'   => 'Nouvelle demande de congé',
                    'message' => 'Kader Traoré a soumis une demande de congé payé du 15 au 22 juillet.',
                    'type'    => 'conge',
                    'lien'    => '/conges',
                ],
                [
                    'titre'   => 'Contrat expirant',
                    'message' => 'Le contrat CDD de Aminata Coulibaly expire dans 15 jours.',
                    'type'    => 'alerte',
                    'lien'    => '/employes',
                ],
            ];

            foreach ($notifs as $n) {
                NotificationSgrh::firstOrCreate(
                    ['user_id' => $admin->id, 'titre' => $n['titre']],
                    $n
                );
            }
        }

        echo "✅ Notifications de test créées.\n"; */

        // ── Créer l'administrateur par défaut ──
        /* $employe = User::firstOrCreate(
            ['email' => 'employe@sgrh.com'],
            [
                'name'     => 'Employé Test',
                'password' => bcrypt('Employe@1234'),
                'is_active' => true,
            ]
        );

        $employe->assignRole('employe');

        $manager = User::firstOrCreate(
            ['email' => 'manager@sgrh.com'],
            [
                'name'     => 'Manager Test',
                'password' => bcrypt('Manager@1234'),
                'is_active' => true,
            ]
        );

        $manager->assignRole('manager');

        echo "✅ Employé et manager de test créés.\n"; */
    }
}
