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
        'sport_athletism' => 'Athlétisme',
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
        'sport_athletism' => 'Athletics',
    ]
];

// Petite variable $t pour raccourcir l'écriture dans nos autres fichiers
$t = $translations[$lang];
?>