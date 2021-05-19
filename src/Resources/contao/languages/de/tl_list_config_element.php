<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_list_config_element'];

/*
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';
$lang['title'][0] = 'Titel';
$lang['title'][1] = 'Geben Sie hier einen Titel ein.';
$lang['type'][0] = 'Typ';
$lang['type'][1] = 'Wählen Sie hier den Typ des Elements aus.';
$lang['templateVariable'][0] = 'Template-Variable';
$lang['templateVariable'][1] = 'Geben Sie hier den Namen für die Template-Variable ein. Unter diesem Namen wird das Objekt für das Template sichtbar gemacht.';

// image
$lang['imageSelectorField'][0] = 'Selektor-Feld';
$lang['imageSelectorField'][1] = 'Wählen Sie hier das Feld aus, das den boolschen Selektor für das Bild enthält.';
$lang['imageField'][0] = 'Feld';
$lang['imageField'][1] = 'Wählen Sie hier das Feld aus, das die Referenz zur Bilddatei enthält.';
$lang['placeholderImageMode'][0] = 'Platzhalterbildmodus';
$lang['placeholderImageMode'][1] = 'Wählen Sie diese Option, wenn Sie für den Fall, dass die ausgegebene Instanz kein Bild enthält, ein Platzhalterbild hinzufügen möchten.';
$lang['placeholderImage'][0] = 'Platzhalterbild';
$lang['placeholderImage'][1] = 'Wählen Sie hier ein Platzhalterbild aus.';
$lang['placeholderImageFemale'][0] = 'Platzhalterbild (weiblich)';
$lang['placeholderImageFemale'][1] = 'Wählen Sie hier ein Platzhalterbild für weibliche Instanzen aus.';
$lang['genderField'][0] = 'Geschlecht-Feld';
$lang['genderField'][1] = 'Wählen Sie hier das Feld aus, das das Geschlecht der Instanz enthält.';
$lang['placeholderImages'][0] = 'Platzhalterbilder';
$lang['placeholderImages'][1] = 'Wählen Sie hier Platzhalterbilder aus.';
$lang['openImageInLightbox'][0] = 'Großansicht des Bildes in einem neuen Fenster öffnen';
$lang['openImageInLightbox'][1] = 'Wählen Sie diese Option, um das Bild in einer Lightbox zu öffnen.';
$lang['lightboxId'][0] = 'Lightbox-ID';
$lang['lightboxId'][1] = 'Geben Sie hier bei Bedarf eine ID an, um die Bilder in einer Gallerie zu gruppieren. Geben Sie keine ID an, wird jedes Bild in seiner eigenen Lightbox geöffnet.';

// submission
$lang['submissionReader'][0] = 'Einsendungsleser';
$lang['submissionReader'][1] = 'Wählen Sie hier ein Modul vom Typ "Einsendungsleser" aus.';
$lang['emailField'][0] = 'E-Mail-Feld';
$lang['emailField'][1] = 'Wählen Sie hier das Feld aus, an das die E-Mail verschickt werden soll.';
$lang['submissionFormExplanation'] = 'Die E-Mail-Adresse, an die das Formular verschickt wird, finden Sie im Notification-Center-Token ##form_value_submission_form_email##.';
$lang['fieldDependentPlaceholderConfig'][0] = 'Platzhalterkonfiguration';
$lang['fieldDependentPlaceholderConfig'][1] = 'Definieren Sie hier die Platzhalterbilder für die jeweiligen Feldwerte';
$lang['fieldDependentPlaceholderConfig']['field'][0] = 'Feld';
$lang['fieldDependentPlaceholderConfig']['operator'][0] = 'Operator';
$lang['fieldDependentPlaceholderConfig']['value'][0] = 'Wert';
$lang['fieldDependentPlaceholderConfig']['placeholderImage'][0] = 'Bild';
$lang['imgSize'][0] = 'Bildgröße';
$lang['imgSize'][1] = 'Hier können Sie die Abmessungen des Bildes und den Skalierungsmodus festlegen.';
$lang['submissionFormTemplate'][0] = 'Template';
$lang['submissionFormTemplate'][1] = 'Wählen Sie hier das gewünschte Template aus.';
$lang['submissionDefaultValues'][0] = 'Standardwerte';
$lang['submissionDefaultValues'][1] = 'Wählen Sie die Standardwerte für das Formular.';
$lang['submissionDefaultValues']['submissionField'][0] = 'Einsendungsfeld';
$lang['submissionDefaultValues']['submissionField'][1] = 'Wählen Sie hier ein Feld aus dem DCA tl_submission.';
$lang['submissionDefaultValues']['entityField'][0] = 'Entität';
$lang['submissionDefaultValues']['entityField'][1] = 'Wählen Sie hier ein aus Ihrem Ziel-DCA.';

// related
$lang['relatedExplanation'] = 'Um ähnliche Instanzen auszugeben, müssen Sie vorab erst eine Modul des Typs "Liste" samt Listenkonfiguration und Filter erstellen. Die notwendigen Filter-Bedingungen für das Auffinden ähnlicher Instanzen werden durch dieses Listenkonfigurations-Element automatisch gesetzt. Der Filter in der Liste muss diese Filter-Konfigurationselemente also <strong>nicht</strong> setzen. In der Regel sollte sich in einem solchen Filter nur ein Filter-Element für die Prüfung des "Veröffentlicht"-Status befinden.';

$lang['relatedListModule'][0] = 'Listenmodul';
$lang['relatedListModule'][1] = 'Wählen Sie hier das Listenmodul aus, das die ähnlichen Instanzen ausgeben soll.';

$lang['relatedCriteriaExplanation'] = 'Bitte installieren Sie eine oder mehrere der folgenden Erweiterungen, um entsprechende Kriterien zu vergeben:
    <ul>
        <li>- <a href="https://github.com/codefog/tags-bundle" target="blank">codefog/tags-bundle</a></li>
        <li>- <a href="https://github.com/heimrichhannot/contao-categories-bundle" target="blank">heimrichhannot/contao-categories-bundle</a></li>
    </ul>';

$lang['relatedCriteria'][0] = 'Filterkriterien';
$lang['relatedCriteria'][1] = 'Wählen Sie hier die Kriterien aus, die zum Auffinden ähnlicher Instanzen verwendet werden sollen.';

// categories
$lang['categoriesField'][0] = 'Kategorien-Feld';
$lang['categoriesField'][1] = 'Wählen Sie hier ein Kategorien-Feld aus.';

// tags
$lang['tagsField'][0] = 'Tags-Feld';
$lang['tagsField'][1] = 'Wählen Sie hier ein Tags-Feld aus.';

$lang['tagsAddLink'][0] = 'Schlagworte als Filter-Links darstellen';
$lang['tagsAddLink'][1] = 'Wählen Sie diese Option, wenn die Schlagworte als Filter-Links dargestellt werden sollen. Auf der Zielseite sollte sich ein Modul vom Typ "Filter" bzw. "Liste" befinden.';

$lang['tagsFilter'][0] = 'Filter';
$lang['tagsFilter'][1] = 'Wählen Sie hier den Filter aus, der in der zu filternden Liste verknüpft ist.';

$lang['tagsFilterConfigElement'][0] = 'Betroffenes Filter-Element';
$lang['tagsFilterConfigElement'][1] = 'Wählen Sie hier das zu filternde Filter-Element aus.';

$lang['tagsJumpTo'][0] = 'Weiterleitungsseite';
$lang['tagsJumpTo'][1] = 'Wählen Sie hier die Seite aus, die den o.g. Filter nutzt.';

$lang['tagsTemplate'][0] = 'Template';
$lang['tagsTemplate'][1] = 'Wählen Sie hier das gewünschte Template aus.';

// video
$lang['videoField'][0] = 'Feld';
$lang['videoField'][1] = 'Wählen Sie hier das Feld aus, das die Referenz zur Bilddatei enthält.';
$lang['videoSize'][0] = 'Videogröße';
$lang['videoSize'][1] = 'Wählen Sie hier die Größe aus, in der das Video dargestellt werden soll.';
$lang['posterImageField'][0] = 'Vorschaubild';
$lang['posterImageField'][1] = 'Wählen Sie hier das Feld aus, das die Referenz zur Vorschaubild enthält.';
$lang['addAutoplay'][0] = 'autoplay';
$lang['addAutoplay'][1] = 'Wählen Sie hier, ob das Video automatisch gestartet werden soll.';
$lang['addLoop'][0] = 'loop';
$lang['addLoop'][1] = 'Wählen Sie hier, ob das Video in Dauerschleife abgespielt werden soll.';
$lang['addControls'][0] = 'controls';
$lang['addControls'][1] = 'Wählen Sie hier, ob die Bedienelemente des Videoplayers angezeigt werden sollen.';
$lang['addMuted'][0] = 'muted';
$lang['addMuted'][1] = 'Wählen Sie hier, ob das Video ohne ton gestartet werden soll.';
$lang['overrideTemplateContainerVariable'][0] = 'Template-Container-Variable überschreiben';
$lang['overrideTemplateContainerVariable'][1] = 'Wählen Sie diese Option um die Variable anzupassen, in die Elemente diesen Typs zusammengefasst werden.';
$lang['templateContainerVariable'][0] = 'Template-Container-Variable';
$lang['templateContainerVariable'][1] = 'Tragen Sie hier den Namen der Variable ein, in der die Elemente diesen Typs zusammengefasst werden.';
/*
 * Legends
 */
$lang['title_type_legend'] = 'Allgemeines';
$lang['config_legend'] = 'Konfiguration';
$lang['template_legend'] = 'Template';

/*
 * Reference
 */
$lang['reference'][\HeimrichHannot\ListBundle\ConfigElementType\ImageConfigElementType::TYPE] = 'Bild';
$lang['reference'][\HeimrichHannot\ListBundle\ConfigElementType\SubmissionFormConfigElementType::TYPE] = 'Einsendungsformular';
$lang['reference'][\HeimrichHannot\ListBundle\ConfigElementType\RelatedConfigElementType::getType()] = 'Ähnliche Instanzen';
$lang['reference'][\HeimrichHannot\ListBundle\ConfigElementType\TagsConfigElementType::getType()] = 'Schlagworte';

$lang['reference'][\HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_SIMPLE] = 'einfach';
$lang['reference'][\HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_GENDERED] = 'geschlechtsspezifisch';
$lang['reference'][\HeimrichHannot\ListBundle\Backend\ListConfigElement::PLACEHOLDER_IMAGE_MODE_RANDOM] = 'zufällig';
$lang['reference'][\HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer::RELATED_CRITERIUM_TAGS] = 'Schlagworte';
$lang['reference'][\HeimrichHannot\ListBundle\DataContainer\ListConfigElementContainer::RELATED_CRITERIUM_CATEGORIES] = 'Kategorien';

/*
 * Buttons
 */
$lang['new'] = ['Neues Listenkonfigurations-Element', 'Listenkonfigurations-Element erstellen'];
$lang['edit'] = ['Listenkonfigurations-Element bearbeiten', 'Listenkonfigurations-Element ID %s bearbeiten'];
$lang['copy'] = ['Listenkonfigurations-Element duplizieren', 'Listenkonfigurations-Element ID %s duplizieren'];
$lang['delete'] = ['Listenkonfigurations-Element löschen', 'Listenkonfigurations-Element ID %s löschen'];
$lang['toggle'] = [
    'Listenkonfigurations-Element veröffentlichen',
    'Listenkonfigurations-Element ID %s veröffentlichen/verstecken',
];
$lang['show'] = ['Listenkonfigurations-Element Details', 'Listenkonfigurations-Element-Details ID %s anzeigen'];
