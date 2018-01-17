<?php

$lang = &$GLOBALS['TL_LANG']['tl_list_config_element'];

/**
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';
$lang['title'][0]  = 'Titel';
$lang['title'][1]  = 'Geben Sie hier einen Titel ein.';
$lang['type'][0]   = 'Typ';
$lang['type'][1]   = 'Wählen Sie hier den Typ des Elements aus.';

// image
$lang['imageSelectorField'][0] = 'Selektor-Feld';
$lang['imageSelectorField'][1] = 'Wählen Sie hier das Feld aus, das den boolschen Selektor für das Bild enthält.';
$lang['imageField'][0]         = 'Feld';
$lang['imageField'][1]         = 'Wählen Sie hier das Feld aus, das die Referenz zur Bilddatei enthält.';

/**
 * Legends
 */
$lang['title_type_legend'] = 'Titel & Typ';
$lang['config_legend']     = 'Konfiguration';

/**
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\ListBundle\Backend\ListConfigElement::TYPE_IMAGE => 'Bild'
];

/**
 * Buttons
 */
$lang['new']    = ['Neues Listenkonfigurations-Element', 'Listenkonfigurations-Element erstellen'];
$lang['edit']   = ['Listenkonfigurations-Element bearbeiten', 'Listenkonfigurations-Element ID %s bearbeiten'];
$lang['copy']   = ['Listenkonfigurations-Element duplizieren', 'Listenkonfigurations-Element ID %s duplizieren'];
$lang['delete'] = ['Listenkonfigurations-Element löschen', 'Listenkonfigurations-Element ID %s löschen'];
$lang['toggle'] = ['Listenkonfigurations-Element veröffentlichen', 'Listenkonfigurations-Element ID %s veröffentlichen/verstecken'];
$lang['show']   = ['Listenkonfigurations-Element Details', 'Listenkonfigurations-Element-Details ID %s anzeigen'];
