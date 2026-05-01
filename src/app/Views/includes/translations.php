<?php
// On s'assure d'avoir la langue actuelle (fr par défaut)
$lang = $_SESSION['lang'] ?? 'fr';

// Le grand dictionnaire du site
$translations = [
    'fr' => [
        // Navigation (Header)
        'app_name' => 'ERP Événement Sportif',
        'nav_dashboard' => 'Tableau de bord',
        'nav_directory' => 'Annuaire',
        'nav_new_event' => '+ Nouvel Événement',
        
        // Page d'accueil (Index)
        'dash_welcome' => 'Bienvenue sur l\'ERP de gestion des événements sportifs.',
        'dash_empty_title' => 'Espace vide :',
        'dash_empty_desc' => 'Les futures cartes des événements apparaîtront ici.',

        // Formulaire (Nouvel Event)
        'form_page_title' => 'Créer un Nouvel Événement',
        'form_event_name' => 'Nom de l\'événement',
        'form_event_name_ph' => 'Saisir le nom de l\'événement',
        'form_sport_type' => 'Type de sport',
        'form_sport_select' => 'Sélectionner un sport',
        'form_desc' => 'Description',
        'form_desc_ph' => 'Fournir les détails et la description',
        'form_start_date' => 'Date de début',
        'form_end_date' => 'Date de fin',
        'form_location' => 'Lieu',
        'form_location_ph' => 'Stade ou adresse',
        'form_capacity' => 'Capacité',
        'form_capacity_ph' => 'Nombre max. de participants',
        'form_cancel' => 'Annuler',
        'form_save' => 'Enregistrer',
        
        // --- NOUVEAUX SPORTS (FR) ---
        'sport_football' => 'Football',
        'sport_rugby' => 'Rugby',
        'sport_athletism' => 'Athlétisme',
        'sport_basketball' => 'Basketball',
        'sport_tennis' => 'Tennis',
        'sport_swimming' => 'Natation',
        'sport_cycling' => 'Cyclisme',
        'sport_esport' => 'eSport',
        'sport_other' => 'Autre',
        
        // Annuaire
        'dir_title' => 'Annuaire des Ressources',
        'dir_import_btn' => 'Importer une base (CSV)',
        'dir_th_name' => 'Nom',
        'dir_th_role' => 'Rôle / Type',
        'dir_th_entity' => 'Club / Entité',
        'dir_th_email' => 'Contact',
        'dir_modal_title' => 'Importer des données',
        'dir_modal_desc' => 'Sélectionnez un fichier CSV ou SQL pour mettre à jour votre annuaire.',
        'dir_modal_file' => 'Fichier de données',
        'dir_modal_upload' => 'Lancer l\'importation',
    ],
    'en' => [
        // Navigation (Header)
        'app_name' => 'Sports Event Manager',
        'nav_dashboard' => 'Dashboard',
        'nav_directory' => 'Directory',
        'nav_new_event' => '+ New Event',
        
        // Page d'accueil (Index)
        'dash_welcome' => 'Welcome to the Sports Event Management ERP.',
        'dash_empty_title' => 'Empty space:',
        'dash_empty_desc' => 'Future event cards will appear here.',

        // Formulaire (Nouvel Event)
        'form_page_title' => 'Create New Sports Event',
        'form_event_name' => 'Event Name',
        'form_event_name_ph' => 'Enter event name',
        'form_sport_type' => 'Sport Type',
        'form_sport_select' => 'Select a sport',
        'form_desc' => 'Description',
        'form_desc_ph' => 'Provide event details and description',
        'form_start_date' => 'Start Date',
        'form_end_date' => 'End Date',
        'form_location' => 'Location',
        'form_location_ph' => 'Venue or address',
        'form_capacity' => 'Capacity',
        'form_capacity_ph' => 'Maximum participants',
        'form_cancel' => 'Cancel',
        'form_save' => 'Save Event',
        
        // --- NOUVEAUX SPORTS (EN) ---
        'sport_football' => 'Soccer / Football',
        'sport_rugby' => 'Rugby',
        'sport_athletism' => 'Athletics',
        'sport_basketball' => 'Basketball',
        'sport_tennis' => 'Tennis',
        'sport_swimming' => 'Swimming',
        'sport_cycling' => 'Cycling',
        'sport_esport' => 'eSports',
        'sport_other' => 'Other',
        
        // Directory
        'dir_title' => 'Resource Directory',
        'dir_import_btn' => 'Import Database (CSV)',
        'dir_th_name' => 'Name',
        'dir_th_role' => 'Role / Type',
        'dir_th_entity' => 'Club / Entity',
        'dir_th_email' => 'Contact',
        'dir_modal_title' => 'Import Data',
        'dir_modal_desc' => 'Select a CSV or SQL file to update your directory.',
        'dir_modal_file' => 'Data File',
        'dir_modal_upload' => 'Start Import',
    ]
];

// Petite variable $t pour raccourcir l'écriture dans nos autres fichiers
$t = $translations[$lang];
?>