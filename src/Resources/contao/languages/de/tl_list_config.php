<?php

$lang = &$GLOBALS['TL_LANG']['tl_list_config'];

/**
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';

// general
$lang['title'][0]            = 'Titel';
$lang['title'][1]            = 'Geben Sie hier bitte den Titel ein.';
$lang['parentListConfig'][0] = 'Eltern-Listenkonfiguration';
$lang['parentListConfig'][1] = 'Wählen Sie hier eine Listenkonfiguration aus, von der geerbt werden soll. Sie können dann punktuell einzelne Eigenschaften überschreiben.';

// filter
$lang['filter'][0] = 'Filter';
$lang['filter'][1] = 'Bitte wählen Sie hier bei Bedarf einen Filter aus.';

// config
$lang['manager'][0]              = 'Manager-Service';
$lang['manager'][1]              = 'Wählen Sie hier einen individuellen Manager-Service aus.';
$lang['list'][0]                 = 'List-Klasse';
$lang['list'][1]                 = 'Wählen Sie hier eine individuelle List-Klasse aus.';
$lang['item'][0]                 = 'Item-Klasse';
$lang['item'][1]                 = 'Wählen Sie hier eine individuelle Item-Klasse aus.';
$lang['limitFormattedFields'][0] = 'Formatierte Felder einschränken (Geschwindigkeit verbessern)';
$lang['limitFormattedFields'][1] = 'Wählen Sie diese Option, wenn nur bestimmte Felder auf Basis der Data-Containers-Konfiguration formatiert werden sollen möchten.';
$lang['formattedFields'][0]      = 'Formatierte Felder';
$lang['formattedFields'][1]      = 'Wählen Sie hier die zu formatierenden Felder aus.';
$lang['skipFirst'][0]            = 'Elemente überspringen';
$lang['skipFirst'][1]            = 'Hier legen Sie fest, wie viele Elemente übersprungen werden sollen.';
$lang['perPage'][0]              = 'Elemente pro Seite';
$lang['perPage'][1]              = 'Die Anzahl an Elementen pro Seite. Geben Sie 0 ein, um den automatischen Seitenumbruch zu deaktivieren.';
$lang['numberOfItems'][0]        = 'Gesamtzahl der Beiträge';
$lang['numberOfItems'][1]        = 'Hier können Sie die Gesamtzahl der Beiträge begrenzen. Geben Sie 0 ein, um alle anzuzeigen.';
$lang['showItemCount'][0]        = 'Ergebnisanzahl anzeigen';
$lang['showItemCount'][1]        = 'Klicken Sie hier, um die Anzahl der gefundenen Instanzen anzuzeigen.';
$lang['itemCountText'][0]        = 'Individueller Ergebnisanzahl-Text';
$lang['itemCountText'][1]        = 'Wählen Sie hier eine Symfony-Message aus.';
$lang['showNoItemsText'][0]      = '"Keine Ergebnisse"-Meldung anzeigen';
$lang['showNoItemsText'][1]      = 'Klicken Sie hier, um eine Meldung anzuzeigen, wenn keine Instanzen gefunden wurden.';
$lang['noItemsText'][0]          = 'Individueller "Keine Ergebnisse"-Text';
$lang['noItemsText'][1]          = 'Wählen Sie hier eine Symfony-Message aus.';
$lang['showInitialResults'][0]   = 'Initial Ergebnisse anzeigen';
$lang['showInitialResults'][1]   = 'Wählen Sie diese Option, wenn initial eine Ergebnisliste angezeigt werden soll.';
$lang['doNotRenderEmpty'][0]     = 'Nicht anzeigen, wenn keine Instanzen gefunden wurden';
$lang['doNotRenderEmpty'][1]     = 'Aktivieren Sie diese Option, wenn die Liste nicht angezeigt werden soll (kein Markup), wenn keine Instanzen gefunden werden.';
$lang['isTableList'][0]          = 'Als Tabelle ausgeben';
$lang['isTableList'][1]          = 'Wählen Sie diese Option, die Liste in Form einer Tabelle ausgegeben werden soll.';
$lang['hasHeader'][0]            = 'Kopfzeile ausgeben';
$lang['hasHeader'][1]            = 'Wählen Sie diese Option, wenn die Tabelle eine Kopfzeile haben soll.';
$lang['sortingHeader'][0]        = 'Sortierende Kopfzeile';
$lang['sortingHeader'][1]        = 'Wählen Sie diese Option, wenn die Tabelle eine Kopfzeile haben soll, die Links zum Sortieren enthält.';
$lang['tableFields'][0]          = 'Tabellenfelder';
$lang['tableFields'][1]          = 'Wählen Sie die Felder aus, die in der Tabelle ausgegeben werden sollen.';

// sorting
$lang['sortingMode'][0]      = 'Sortiermodus';
$lang['sortingMode'][1]      = 'Wählen Sie hier aus, ob Sie zur Sortierung ein Feld auswählen oder über eine Freitexteingabe sortieren möchten.';
$lang['sortingField'][0]     = 'Sortierfeld';
$lang['sortingField'][1]     = 'Wählen Sie hier ein Sortierfeld aus.';
$lang['sortingDirection'][0] = 'Sortierreihenfolge';
$lang['sortingDirection'][1] = 'Wählen Sie eine Reihenfolge für die Sortierung aus.';
$lang['sortingText'][0]      = 'Sortierung';
$lang['sortingText'][1]      = 'Geben Sie hier eine Sortierung ein (Beispiel: "myField1 ASC, myField2 DESC").';
$lang['sortingItems'][0]     = 'Manuelle Sortierung';
$lang['sortingItems'][1]     = 'Wählen Sie hier die manuelle Sortierung aus.';

// jump to
$lang['useAlias'][0]   = 'Alias-Feld verwenden';
$lang['useAlias'][1]   = 'Wählen Sie diese Option, wenn erzeugte URLs statt der ID der ausgegebenen Instanz deren Alias enthalten sollen.';
$lang['aliasField'][0] = 'Alias-Feld';
$lang['aliasField'][1] = 'Wählen Sie hier das zu verwendende Alias-Feld aus (Hinweis: Nur Felder mit inputType="text" sind erlaubt).';

$lang['addDetails'][0]                 = 'Details-Weiterleitung hinzufügen';
$lang['addDetails'][1]                 = 'Klicken Sie hier, um jedem Eintrag der Liste eine Weiterleitung zum Anzeigen von Details hinzuzufügen.';
$lang['jumpToDetails'][0]              = 'Weiterleitungsseite (Details; MUSS für die Einbeziehung in die Sitemap gesetzt sein!)';
$lang['jumpToDetails'][1]              = 'Wählen Sie hier die Seite aus, zu der weitergeleitet wird, wenn es eine Detailseite gibt.';
$lang['jumpToDetailsMultilingual']     = [
    0          => 'Sprachenabhängige Weiterleitungsseite',
    1          => 'Definieren Sie hier nur von der Standard-Weiterleitungsseite abweichende Seiten.',
    'language' => ['Sprache'],
    'jumpTo'   => ['Weiterleitungsseite']
];
$lang['jumpToOverviewMultilingual'][0] = 'Sprachenabhängige Übersichtsseite';
$lang['jumpToOverviewMultilingual'][1] = 'Wählen Sie hier Übersichtsseiten aus.';
$lang['addShare'][0]                   = 'Teilen-Weiterleitung hinzufügen';
$lang['addShare'][1]                   = 'Klicken Sie hier, um jedem Eintrag der Liste eine Weiterleitung zum Teilen des aktuellen Listeneintrags hinzuzufügen.';
$lang['jumpToShare'][0]                = 'Weiterleitungsseite (Teilen)';
$lang['jumpToShare'][1]                = 'Wählen Sie hier die Seite aus, zu der weitergeleitet wird, wenn ein Inhalt geteilt wurde.';
$lang['shareAutoItem'][0]              = 'Auto-Item für den Teilen-Link verwenden';
$lang['shareAutoItem'][1]              = 'Wählen Sie diese Option aus, um das Share Token als auto_item auszugeben.';
$lang['addOverview'][0]                = 'Link zur Übersichtsseite hinzufügen';
$lang['addOverview'][1]                = 'Wählen Sie diese Option aus, um der Liste einen Link zur Übersichtsseite hinzuzufügen.';
$lang['jumpToOverview'][0]             = 'Übersichtsseite';
$lang['jumpToOverview'][1]             = 'Wählen Sie hier eine Übersichtsseite aus.';
$lang['customJumpToOverviewLabel'][0]  = 'Label für "zur Übersicht" überschreiben';
$lang['customJumpToOverviewLabel'][1]  = '';
$lang['jumpToOverviewLabel'][0]        = 'Label für "zur Übersicht"';
$lang['jumpToOverviewLabel'][1]        = '';
$lang['useModal'][0]                   = 'Elemente im Modalfenstern anzeigen';
$lang['useModal'][1]                   = 'Wählen Sie diese Option, wenn die Elemente im Modalfenstern angezeigt werden sollen.';
$lang['useModalExplanation']           = 'Zum Aufruf der Leser-Elemente (also den Entitäten) ist es nötig, die Weiterleitungsseite mit einem Modal zu verknüpfen. Dazu legen Sie das Modal an, hinterlegen in diesem ein Leser-Modul als Inhaltselement und weisen es über "Modal verknüpfen" an Ihrer Weiterleitungsseite in der Seitenstruktur zu. Wenn Sie keine Weiterleitungsseite auswählen, wird die aktuelle Seite genutzt, auf der sich dieses Modul befindet.<br/><br/>Hinweis: Sollten in Ihrem Modulkontext mehrere Leser-Module eine Rolle spielen (bspw. zum Lesen und Bearbeiten der Entität), nutzen Sie dafür bitte heimrichhannot/contao-blocks.';

// misc
$lang['addAjaxPagination'][0]           = 'Ajax-Paginierung hinzufügen';
$lang['addAjaxPagination'][1]           = 'Wählen Sie diese Option, wenn eine Ajax-Paginierung genutzt werden soll. Dafür muss ein Wert > 0 in "Elemente pro Seite" gesetzt sein. Die Seitenzahlen werden durch einen einzelnen "Weiter"-Button ersetzt.';
$lang['ajaxPaginationTemplate'][0]      = 'Individuelles Template für die Ajax-Paginierung hinzufügen';
$lang['ajaxPaginationTemplate'][1]      = 'Wählen Sie diese Option, wenn für die Ajax-Paginierung ein individuelles Template genutzt werden soll.';
$lang['addInfiniteScroll'][0]           = 'Infinite Scroll hinzufügen';
$lang['addInfiniteScroll'][1]           = 'Wählen Sie diese Option, um die Ajax-Paginierung im UI-Muster "Infinite Scroll" umzusetzen.';
$lang['addMasonry'][0]                  = 'Masonry hinzufügen';
$lang['addMasonry'][1]                  = 'Wählen Sie diese Option, wenn das Masonry-JavaScript-Plugin auf die Liste angewendet werden soll.';
$lang['masonryStampContentElements'][0] = 'Fixierte Blöcke festlegen';
$lang['masonryStampContentElements'][1] = 'Hier können Sie Blöcke festlegen, die immer gerendert werden sollen. Die Position muss anschließend per CSS festgelegt werden (-> Responsive).';
$lang['stampBlock'][0]                  = 'Block';
$lang['stampBlock'][1]                  = 'Wählen Sie hier einen Block aus.';
$lang['addDcMultilingualSupport'][0]    = 'Support für DC_Multilingual hinzufügen';
$lang['addDcMultilingualSupport'][1]    = 'Wählen Sie diese Option, die verknüpfte Entität durch das Bundle "terminal42/contao-DC_Multilingual" übersetzbar ist.';
$lang['hideForListPreselect'][0]        = 'Für Listenvorauswahl ausblenden';
$lang['hideForListPreselect'][1]        = 'Wählen Sie diese Option, um diese Listenkonfiguration nicht für Listenvorauswahl-Elemente zu erlauben.';

// search
$lang['noSearch'][0]        = 'Nicht durchsuchen';
$lang['noSearch'][1]        = 'Diese Liste nicht in den Suchindex aufnehmen.';
$lang['doNotIndexItems'][0] = 'Item-Detailseiten nicht indizieren';
$lang['doNotIndexItems'][1] = 'Die Detailseiten der Items werden nicht in die Liste der durchsuchbaren Seiten aufgenommen.';

// template
$lang['listTemplate'][0]       = 'Listen-Template';
$lang['listTemplate'][1]       = 'Wählen Sie hier das Template aus, mit dem Liste gerendert werden sollen.';
$lang['itemTemplate'][0]       = 'Instanz-Template';
$lang['itemTemplate'][1]       = 'Wählen Sie hier das Template aus, mit dem die einzelnen Instanzen gerendert werden sollen.';
$lang['itemChoiceTemplate'][0] = 'Auswahloption-Template';
$lang['itemChoiceTemplate'][1] = 'Wählen Sie hier das Template aus, mit dem die einzelnen Instanzen in einer Auswahllisete gerendert werden sollen.';

/**
 * Legends
 */
$lang['general_legend']        = 'Allgemeine Einstellungen';
$lang['entity_legend']         = 'Entität';
$lang['config_legend']         = 'Konfiguration';
$lang['filter_legend']         = 'Filter';
$lang['overrideFilter_legend'] = 'Filter';
$lang['sorting_legend']        = 'Sortierung';
$lang['jumpto_legend']         = 'Weiterleitung';
$lang['preselect_legend']      = 'Vorauswahl-Einstellungen';
$lang['misc_legend']           = 'Verschiedenes';
$lang['search_legend']         = 'Sucheinstellungen';
$lang['template_legend']       = 'Template';

/**
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_FIELD     => 'Feld',
    \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_TEXT      => 'Freitext',
    \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_RANDOM    => 'Zufällig',
    \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_MODE_MANUAL    => 'Manuell',
    \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTION_ASC  => 'Aufsteigend',
    \HeimrichHannot\ListBundle\Backend\ListConfig::SORTING_DIRECTION_DESC => 'Absteigend',
];

/**
 * Buttons
 */
$lang['new']        = ['Neue Listenkonfiguration', 'Listenkonfiguration erstellen'];
$lang['edit']       = ['Listenkonfiguration bearbeiten', 'Listenkonfiguration ID %s bearbeiten'];
$lang['editheader'] = ['Listenkonfiguration-Einstellungen bearbeiten', 'Listenkonfiguration-Einstellungen ID %s bearbeiten'];
$lang['copy']       = ['Listenkonfiguration duplizieren', 'Listenkonfiguration ID %s duplizieren'];
$lang['delete']     = ['Listenkonfiguration löschen', 'Listenkonfiguration ID %s löschen'];
$lang['toggle']     = ['Listenkonfiguration veröffentlichen', 'Listenkonfiguration ID %s veröffentlichen/verstecken'];
$lang['show']       = ['Listenkonfiguration Details', 'Listenkonfiguration-Details ID %s anzeigen'];
$lang['editFilter'] = ['Filter bearbeiten', 'Den Filter ID %s bearbeiten'];
