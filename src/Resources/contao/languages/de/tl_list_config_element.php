<?php

$lang = &$GLOBALS['TL_LANG']['tl_list_config_element'];

/**
 * Fields
 */
$lang['tstamp'][0]           = 'Änderungsdatum';
$lang['title'][0]            = 'Titel';
$lang['title'][1]            = 'Geben Sie hier einen Titel ein.';
$lang['type'][0]             = 'Typ';
$lang['type'][1]             = 'Wählen Sie hier den Typ des Elements aus.';
$lang['templateVariable'][0] = 'Template-Variable';
$lang['templateVariable'][1] = 'Geben Sie hier den Namen für die Template-Variable ein. Unter diesem Namen wird das Objekt für das Template sichtbar gemacht.';

// image
$lang['imageSelectorField'][0]                                  = 'Selektor-Feld';
$lang['imageSelectorField'][1]                                  = 'Wählen Sie hier das Feld aus, das den boolschen Selektor für das Bild enthält.';
$lang['imageField'][0]                                          = 'Feld';
$lang['imageField'][1]                                          = 'Wählen Sie hier das Feld aus, das die Referenz zur Bilddatei enthält.';
$lang['placeholderImageMode'][0]                                = 'Platzhalterbildmodus';
$lang['placeholderImageMode'][1]                                = 'Wählen Sie diese Option, wenn Sie für den Fall, dass die ausgegebene Instanz kein Bild enthält, ein Platzhalterbild hinzufügen möchten.';
$lang['placeholderImage'][0]                                    = 'Platzhalterbild';
$lang['placeholderImage'][1]                                    = 'Wählen Sie hier ein Platzhalterbild aus.';
$lang['placeholderImageFemale'][0]                              = 'Platzhalterbild (weiblich)';
$lang['placeholderImageFemale'][1]                              = 'Wählen Sie hier ein Platzhalterbild für weibliche Instanzen aus.';
$lang['genderField'][0]                                         = 'Geschlecht-Feld';
$lang['genderField'][1]                                         = 'Wählen Sie hier das Feld aus, das das Geschlecht der Instanz enthält.';
$lang['placeholderImages'][0]                                   = 'Platzhalterbilder';
$lang['placeholderImages'][1]                                   = 'Wählen Sie hier Platzhalterbilder aus.';
$lang['submissionReader'][0]                                    = 'Einsendungsleser';
$lang['submissionReader'][1]                                    = 'Wählen Sie hier ein Modul vom Typ "Einsendungsleser" aus.';
$lang['emailField'][0]                                          = 'E-Mail-Feld';
$lang['emailField'][1]                                          = 'Wählen Sie hier das Feld aus, an das die E-Mail verschickt werden soll.';
$lang['submissionFormExplanation']                              = 'Die E-Mail-Adresse, an die das Formular verschickt wird, finden Sie im Notification-Center-Token ##form_value_submission_form_email##.';
$lang['fieldDependentPlaceholderConfig'][0]                     = 'Platzhalterkonfiguration';
$lang['fieldDependentPlaceholderConfig'][1]                     = 'Definieren Sie hier die Platzhalterbilder für die jeweiligen Feldwerte';
$lang['fieldDependentPlaceholderConfig']['field'][0]            = 'Feld';
$lang['fieldDependentPlaceholderConfig']['operator'][0]         = 'Operator';
$lang['fieldDependentPlaceholderConfig']['value'][0]            = 'Wert';
$lang['fieldDependentPlaceholderConfig']['placeholderImage'][0] = 'Bild';


/**
 * Legends
 */
$lang['title_type_legend'] = 'Allgemeines';
$lang['config_legend']     = 'Konfiguration';
$lang['template_legend']   = 'Template';

/**
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\ListBundle\ConfigElementType\ImageConfigElementType::TYPE             => 'Bild',
    \HeimrichHannot\ListBundle\ConfigElementType\SubmissionFormConfigElementType::TYPE    => 'Einsendungsformular',
    \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE   => 'einfach',
    \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED => 'geschlechtsspezifisch',
    \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_RANDOM   => 'zufällig',
    \HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_FIELD    => 'feldabhängig',
];

/**
 * Buttons
 */
$lang['new']    = ['Neues Listenkonfigurations-Element', 'Listenkonfigurations-Element erstellen'];
$lang['edit']   = ['Listenkonfigurations-Element bearbeiten', 'Listenkonfigurations-Element ID %s bearbeiten'];
$lang['copy']   = ['Listenkonfigurations-Element duplizieren', 'Listenkonfigurations-Element ID %s duplizieren'];
$lang['delete'] = ['Listenkonfigurations-Element löschen', 'Listenkonfigurations-Element ID %s löschen'];
$lang['toggle'] = [
    'Listenkonfigurations-Element veröffentlichen',
    'Listenkonfigurations-Element ID %s veröffentlichen/verstecken'
];
$lang['show']   = ['Listenkonfigurations-Element Details', 'Listenkonfigurations-Element-Details ID %s anzeigen'];
